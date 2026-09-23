<?php
namespace Aws\Api\ErrorParser;

use Aws\Api\Parser\AbstractParser;
use Aws\Api\StructureShape;
use Aws\CommandInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Base implementation for Smithy RPC V2 protocol error parsers.
 *
 * @internal
 */
abstract class AbstractRpcV2ErrorParser extends AbstractErrorParser
{
    private const HEADER_QUERY_ERROR = 'x-amzn-query-error';
    private const HEADER_ERROR_TYPE = 'x-amzn-errortype';
    private const HEADER_REQUEST_ID = 'x-amzn-requestid';

    /**
     * @param ResponseInterface $response
     * @param CommandInterface|null $command
     *
     * @return array
     */
    public function __invoke(
        ResponseInterface $response,
        ?CommandInterface $command = null
    ) {
        $response = AbstractParser::getResponseWithCachingStream($response);
        $data = $this->parseError($response);

        if (isset($data['parsed']['__type'])) {
            $data['message'] = $data['parsed']['message'] ?? null;
        }

        $this->populateShape($data, $response, $command);

        return $data;
    }

    /**
     * @param ResponseInterface $response
     * @param StructureShape $member
     *
     * @return array
     */
    abstract protected function payload(
        ResponseInterface $response,
        StructureShape $member
    ): array;

    /**
     * @param StreamInterface $body
     * @param ResponseInterface $response
     *
     * @return mixed
     */
    abstract protected function parseBody(
        StreamInterface $body,
        ResponseInterface $response
    ): mixed;

    /**
     * @param ResponseInterface $response
     *
     * @return array
     */
    private function parseError(ResponseInterface $response): array
    {
        $statusCode = (string) $response->getStatusCode();
        $errorCode = null;
        $errorType = null;

        if ($this->api?->getMetadata('awsQueryCompatible') !== null
            && $response->hasHeader(self::HEADER_QUERY_ERROR)
            && $awsQueryError = $this->parseQueryCompatibleHeader($response)
        ) {
            $errorCode = $awsQueryError['code'];
            $errorType = $awsQueryError['type'];
        }

        if (!$errorCode && $response->hasHeader(self::HEADER_ERROR_TYPE)) {
            $errorCode = $this->extractErrorCode(
                $response->getHeaderLine(self::HEADER_ERROR_TYPE)
            );
        }

        $parsedBody = null;
        $body = $response->getBody();
        if ($body->getSize()) {
            //TODO handle unseekable streams with CachingStream
            $parsedBody = array_change_key_case($this->parseBody($body, $response));
        }

        if (!$errorCode && $parsedBody) {
            $errorCode = $this->extractErrorCode(
                $parsedBody['code'] ?? $parsedBody['__type'] ?? ''
            );
        }

        return [
            'request_id' => $response->getHeaderLine(self::HEADER_REQUEST_ID),
            'code'       => $errorCode ?: null,
            'message'    => null,
            'type'       => $errorType ?? ($statusCode[0] === '4' ? 'client' : 'server'),
            'parsed'     => $parsedBody,
        ];
    }

    /**
     * Parse AWS Query Compatible error from header
     *
     * @param ResponseInterface $response
     *
     * @return array|null Returns ['code' => string, 'type' => string] or null
     */
    private function parseQueryCompatibleHeader(ResponseInterface $response): ?array
    {
        $parts = explode(';', $response->getHeaderLine(self::HEADER_QUERY_ERROR));
        if (count($parts) === 2 && $parts[0] && $parts[1]) {
            return [
                'code' => $parts[0],
                'type' => $parts[1],
            ];
        }

        return null;
    }

    /**
     * Extract error code from raw error string containing # and/or : delimiters
     *
     * @param string $rawErrorCode
     * @return string
     */
    private function extractErrorCode(string $rawErrorCode): string
    {
        // Handle format with both # and uri (e.g., "namespace#ErrorCode:http://foo-bar")
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
}
