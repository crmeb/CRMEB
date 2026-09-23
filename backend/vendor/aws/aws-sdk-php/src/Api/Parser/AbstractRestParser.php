<?php
namespace Aws\Api\Parser;

use Aws\Api\DateTimeResult;
use Aws\Api\Shape;
use Aws\Api\StructureShape;
use Aws\Result;
use Aws\CommandInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @internal
 */
abstract class AbstractRestParser extends AbstractParser
{
    use PayloadParserTrait;

    /**
     * Parses a payload from a response.
     *
     * @param ResponseInterface $response Response to parse.
     * @param StructureShape    $member   Member to parse
     * @param array             $result   Result value
     *
     * @return mixed
     */
    abstract protected function payload(
        ResponseInterface $response,
        StructureShape $member,
        array &$result
    );

    public function __invoke(
        CommandInterface $command,
        ResponseInterface $response
    ) {
        $output = $this->api->getOperation($command->getName())->getOutput();
        $result = [];

        if ($payload = $output['payload']) {
            $this->extractPayload($payload, $output, $response, $result);
        } else {
            $response = AbstractParser::getResponseWithCachingStream($response);

            if ($response->getBody()->getSize() === null) {
                $rawBody = AbstractParser::getBodyContents($response);
                $isEmpty = empty($rawBody);
            } else {
                $isEmpty = $response->getBody()->getSize() === 0;
            }

            if (!$isEmpty && count($output->getMembers()) > 0
            ) {
                // if no payload was found, then parse the contents of the body
                $this->payload($response, $output, $result);
            }
        }

        foreach ($output->getMembers() as $name => $member) {
            switch ($member['location']) {
                case 'header':
                    $this->extractHeader($name, $member, $response, $result);
                    break;
                case 'headers':
                    $this->extractHeaders($name, $member, $response, $result);
                    break;
                case 'statusCode':
                    $this->extractStatus($name, $response, $result);
                    break;
            }
        }

        return new Result($result);
    }

    private function extractPayload(
        $payload,
        StructureShape $output,
        ResponseInterface $response,
        array &$result
    ) {
        $member = $output->getMember($payload);
        $body = $response->getBody();
        if (!empty($member['eventstream'])) {
            $result[$payload] = new EventParsingIterator(
                $body,
                $member,
                $this
            );

            return;
        }

        $response = AbstractParser::getResponseWithCachingStream($response);

        if ($member instanceof StructureShape) {
            //Unions must have at least one member set to a non-null value
            // If the body is empty, we can assume it is unset
            if ($response->getBody()->getSize() === null) {
                $rawBody = AbstractParser::getBodyContents($response);
                $isEmpty = empty($rawBody);
            } else {
                $isEmpty = $response->getBody()->getSize() === 0;
            }

            if (!empty($member['union']) && $isEmpty) {
                return;
            }

            $result[$payload] = [];
            $this->payload($response, $member, $result[$payload]);
        } else {
            // Always set the payload to the body stream, regardless of content
            $result[$payload] = $body;
        }
    }

    /**
     * Extract a single header from the response into the result.
     */
    private function extractHeader(
        $name,
        Shape $shape,
        ResponseInterface $response,
        &$result
    ) {
        $value = $response->getHeaderLine($shape['locationName'] ?: $name);
        // Empty headers should not be deserialized
        if ($value === null || $value === '') {
            return;
        }

        switch ($shape->getType()) {
            case 'float':
            case 'double':
                $value = match ($value) {
                    'NaN', 'Infinity', '-Infinity' => $value,
                    default => (float) $value
                };
                break;
            case 'long':
            case 'integer':
                $value = (int) $value;
                break;
            case 'boolean':
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                break;
            case 'blob':
                $value = base64_decode($value);
                break;
            case 'timestamp':
                try {
                    $value = DateTimeResult::fromTimestamp(
                        $value,
                        !empty($shape['timestampFormat']) ? $shape['timestampFormat'] : null
                    );
                    break;
                } catch (\Exception $e) {
                    // If the value cannot be parsed, then do not add it to the
                    // output structure.
                    return;
                }
            case 'string':
                try {
                    if ($shape['jsonvalue']) {
                        $value = $this->parseJson(base64_decode($value), $response);
                    }

                    // If value is not set, do not add to output structure.
                    if (!isset($value)) {
                        return;
                    }
                    break;
                } catch (\Exception $e) {
                    //If the value cannot be parsed, then do not add it to the
                    //output structure.
                    return;
                }
            case 'list':
                $listMember = $shape->getMember();
                $type = $listMember->getType();

                // Only boolean lists require special handling
                // other types can be returned as-is
                if ($type !== 'boolean') {
                    break;
                }

                $items = array_map('trim', explode(',', $value));
                $value = array_map(
                    static fn($item) => filter_var($item, FILTER_VALIDATE_BOOLEAN),
                    $items
                );

                break;
        }

        $result[$name] = $value;
    }

    /**
     * Extract a map of headers with an optional prefix from the response.
     */
    private function extractHeaders(
        $name,
        Shape $shape,
        ResponseInterface $response,
        &$result
    ) {
        // Check if the headers are prefixed by a location name
        $result[$name] = [];
        $prefix = $shape['locationName'];
        $prefixLen = $prefix !== null ? strlen($prefix) : 0;

        foreach ($response->getHeaders() as $k => $values) {
            if (!$prefixLen) {
                $result[$name][$k] = implode(', ', $values);
            } elseif (stripos($k, $prefix) === 0) {
                $result[$name][substr($k, $prefixLen)] = implode(', ', $values);
            }
        }
    }

    /**
     * Places the status code of the response into the result array.
     */
    private function extractStatus(
        $name,
        ResponseInterface $response,
        array &$result
    ) {
        $result[$name] = (int) $response->getStatusCode();
    }
}
