<?php
namespace Aws\Api\ErrorParser;

use Aws\Api\Parser\AbstractParser;
use Aws\Api\Parser\PayloadParserTrait;
use Aws\Api\StructureShape;
use Psr\Http\Message\ResponseInterface;

/**
 * Provides basic JSON error parsing functionality.
 */
trait JsonParserTrait
{
    use PayloadParserTrait;

    private function genericHandler(ResponseInterface $response): array
    {
        $code = (string) $response->getStatusCode();
        $error_code = null;
        $error_type = null;

        // Parse error code and type for query compatible services
        if ($this->api
            && !is_null($this->api->getMetadata('awsQueryCompatible'))
            && $response->hasHeader('x-amzn-query-error')
        ) {
            $awsQueryError = $this->parseAwsQueryCompatibleHeader($response);
            if ($awsQueryError) {
                $error_code = $awsQueryError['code'];
                $error_type = $awsQueryError['type'];
            }
        }

        // Parse error code from X-Amzn-Errortype header
        if (!$error_code && $response->hasHeader('X-Amzn-Errortype')) {
            $error_code = $this->extractErrorCode(
                $response->getHeaderLine('X-Amzn-Errortype')
            );
        }

        $parsedBody = null;

        $rawBody = AbstractParser::getBodyContents($response);
        if (!empty($rawBody)) {
            $parsedBody = $this->parseJson($rawBody, $response);
        }

        // Parse error code from response body
        if (!$error_code && $parsedBody) {
            $error_code = $this->parseErrorFromBody($parsedBody);
        }

        if (!isset($error_type)) {
            $error_type = $code[0] == '4' ? 'client' : 'server';
        }

        return [
            'request_id'  => $response->getHeaderLine('x-amzn-requestid'),
            'code'        => $error_code ?? null,
            'message'     => null,
            'type'        => $error_type,
            'parsed'      => $parsedBody
        ];
    }

    /**
     * Parse AWS Query Compatible error from header
     *
     * @param ResponseInterface $response
     * @return array|null Returns ['code' => string, 'type' => string] or null
     */
    private function parseAwsQueryCompatibleHeader(ResponseInterface $response): ?array
    {
        $queryError = $response->getHeaderLine('x-amzn-query-error');
        $parts = explode(';', $queryError);

        if (count($parts) === 2 && $parts[0] && $parts[1]) {
            return [
                'code' => $parts[0],
                'type' => $parts[1]
            ];
        }

        return null;
    }

    /**
     * Parse error code from response body
     *
     * @param array|null $parsedBody
     * @return string|null
     */
    private function parseErrorFromBody(?array $parsedBody): ?string
    {
        if (!$parsedBody
            || (!isset($parsedBody['code']) && !isset($parsedBody['__type']))
        ) {
            return null;
        }

        $error_code = $parsedBody['code'] ?? $parsedBody['__type'];
        return $this->extractErrorCode($error_code);
    }

    /**
     * Extract error code from raw error string containing # and/or : delimiters
     *
     * @param string $rawErrorCode
     * @return string
     */
    private function extractErrorCode(string $rawErrorCode): string
    {
        // Handle format with both # and uri (e.g., "namespace#http://foo-bar")
        if (str_contains($rawErrorCode, ':') && str_contains($rawErrorCode, '#')) {
            $start = strpos($rawErrorCode, '#') + 1;
            $end = strpos($rawErrorCode, ':', $start);
            return substr($rawErrorCode, $start, $end - $start);
        }

        // Handle format with uri only : (e.g., "ErrorCode:http://foo-bar.com/baz")
        if (str_contains($rawErrorCode, ':')) {
            return substr($rawErrorCode, 0, strpos($rawErrorCode, ':'));
        }

        // Handle format with only # (e.g., "namespace#ErrorCode")
        if (str_contains($rawErrorCode, '#')) {
            return substr($rawErrorCode, strpos($rawErrorCode, '#') + 1);
        }

        return $rawErrorCode;
    }

    protected function payload(
        ResponseInterface $response,
        StructureShape $member
    ) {
        $rawBody = AbstractParser::getBodyContents($response);

        if (!empty($rawBody)) {
            $jsonBody = $this->parseJson($rawBody, $response);
        } else {
            $jsonBody = $rawBody;
        }

        return $this->parser->parse($member, $jsonBody);
    }
}
