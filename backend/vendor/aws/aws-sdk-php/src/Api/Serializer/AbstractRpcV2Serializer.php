<?php
namespace Aws\Api\Serializer;

use Aws\Api\Service;

use Aws\Api\Shape;
use Aws\Api\StructureShape;
use Aws\CommandInterface;
use Aws\EndpointV2\EndpointV2SerializerTrait;
use Aws\EndpointV2\Ruleset\RulesetEndpoint;
use DateTimeInterface;
use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;

/**
 * Base implementation for Smithy RPC V2 protocol serializers.
 *
 * Implementers MUST override the defaultHeader property to represent
 * protocol-specific default header values:
 *   self::HEADER_SMITHY_PROTOCOL => static::SMITHY_PROTOCOL,
 *   self::HEADER_CONTENT_TYPE => static::DEFAULT_CONTENT_TYPE,
 *   self::HEADER_ACCEPT => static::DEFAULT_ACCEPT
 *
 * Implementers must also implement `serialize()`, `resolveBlob()`, and `resolveTimestamp()
 * according to their respective protocol specifications.
 *
 * @internal
 */
abstract class AbstractRpcV2Serializer
{
    protected const HEADER_SMITHY_PROTOCOL = 'Smithy-Protocol';
    protected const HEADER_CONTENT_TYPE = 'Content-Type';
    protected const HEADER_ACCEPT = 'Accept';

    /** @var array  */
    protected static array $defaultHeaders;

    /** @var Service */
    private Service $api;

    /** @var string|Uri */
    private string|Uri $endpoint;

    /** @var bool */
    private bool $isUseEndpointV2;

    use EndpointV2SerializerTrait;

    /**
     * @param Service $api Service API description
     * @param string $endpoint Endpoint to connect to
     */
    public function __construct(Service $api, string|Uri $endpoint)
    {
        $this->api = $api;
        $this->endpoint = Psr7\Utils::uriFor($endpoint);
    }

    /**
     * @param CommandInterface $command Command to serialize into a request.
     * @param mixed|null $endpoint
     *
     * @return RequestInterface
     */
    public function __invoke(
        CommandInterface $command,
        mixed $endpoint = null
    )
    {
        $commandArgs = $command->toArray();
        $commandName = $command->getName();
        $operation = $this->api->getOperation($commandName);
        $headers = static::$defaultHeaders;

        // Operations with no defined input type must not contain bodies
        // Content-Type must not be set
        if ($operation['input'] !== null) {
            $body = $this->serialize($operation->getInput(), $commandArgs);
            $headers['Content-Length'] = (string) strlen($body);
        } else {
            unset($headers['Content-Type']);
        }

        if ($endpoint instanceof RulesetEndpoint) {
            $this->isUseEndpointV2 = true;
            $this->setEndpointV2RequestOptions($endpoint, $headers);
            $this->endpoint = $endpoint->getUrl();
        }

        $requestTarget = $this->buildRequestTarget(
            $commandName,
            $operation['http']['requestUri'] ?? ''
        );
        $uri = new Uri($this->endpoint . $requestTarget);

        return new Request(
            $operation['http']['method'],
            $uri,
            $headers,
            $body ?? null
        );
    }

    /**
     * @param StructureShape $inputShape
     * @param array $commandArgs
     *
     * @return string
     */
    abstract public function serialize(
        StructureShape $inputShape,
        array $commandArgs
    ): string;

    /**
     * Resolves arguments for blob shapes present in the request arguments
     * into a protocol-specific format.
     *
     * @param mixed $value
     *
     * @return array
     */
    abstract protected function resolveBlob(mixed $value): array;

    /**
     * Resolves arguments for timestamp shapes present in the request arguments
     * into a protocol-specific format.
     *
     * @param mixed $value
     *
     * @return array
     */
    abstract protected function resolveTimestamp(
        int|float|string|DateTimeInterface $value
    ): array;

    /**
     * Resolves input shape fields that are present in the request arguments
     *
     * @param Shape $shape
     * @param mixed $value
     *
     * @return mixed
     */
    protected function resolveInputShape(Shape $shape, mixed $value): mixed
    {
        switch ($shape->getType()) {
            case 'structure':
                $data = [];
                foreach ($value as $k => $v) {
                    if ($v !== null && $shape->hasMember($k)) {
                        $valueShape = $shape->getMember($k);
                        $data[$valueShape['locationName'] ?: $k]
                            = $this->resolveInputShape($valueShape, $v);
                    }
                }

                return $data;

            case 'list':
                $items = $shape->getMember();
                foreach ($value as $k => $v) {
                    $value[$k] = $this->resolveInputShape($items, $v);
                }

                return $value;

            case 'map':
                $values = $shape->getValue();
                foreach ($value as $k => $v) {
                    $value[$k] = $this->resolveInputShape($values, $v);
                }

                return $value;

            case 'timestamp':
                return $this->resolveTimestamp($value);

            case 'string':
                return (string) $value;

            case 'integer':
            case 'long':
                return (int) $value;

            case 'double':
            case 'float':
                return (float) $value;

            case 'blob':
                return $this->resolveBlob($value);

            default:
                return $value;
        }
    }

    /**
     * Builds request URI absolute path
     *
     * @param string $commandName
     * @param string $requestUri
     *
     * @return string
     */
    private function buildRequestTarget(
        string $commandName,
        string $requestUri
    ): string
    {
        $requestUri = str_ends_with($requestUri, '/')
            ? $requestUri
            : $requestUri . '/';
        $targetPrefix = $this->api->getMetadata('targetPrefix');

        return "{$requestUri}service/{$targetPrefix}/operation/{$commandName}";
    }
}
