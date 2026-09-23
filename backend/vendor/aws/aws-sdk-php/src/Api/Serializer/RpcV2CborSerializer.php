<?php
namespace Aws\Api\Serializer;

use Aws\Api\Cbor\CborEncoder;
use Aws\Api\Cbor\Exception\CborException;
use Aws\Api\Exception\RpcV2CborException;
use Aws\Api\Service;
use Aws\Api\StructureShape;
use DateTimeInterface;

/**
 * Serializes requests according to Smithy RPC-V2 CBOR protocol standards.
 *
 * https://smithy.io/2.0/additional-specs/protocols/smithy-rpc-v2.html
 *
 * @internal
 */
final class RpcV2CborSerializer extends AbstractRpcV2Serializer
{
    /** @var array|string[]  */
    protected static array $defaultHeaders = [
        self::HEADER_SMITHY_PROTOCOL => 'rpc-v2-cbor',
        self::HEADER_CONTENT_TYPE => 'application/cbor',
        self::HEADER_ACCEPT => 'application/cbor',
    ];

    /** @var CborEncoder  */
    private CborEncoder $encoder;

    /**
     * @param Service $api Service API description
     * @param string $endpoint Endpoint to connect to
     */
    public function __construct(Service $api, string $endpoint)
    {
        $this->encoder = new CborEncoder();
        parent::__construct($api, $endpoint);
    }

    /**
     * @param StructureShape $inputShape
     * @param array $commandArgs
     *
     * @return string
     * @throws RpcV2CborException
     */
    public function serialize(
        StructureShape $inputShape,
        array $commandArgs
    ): string
    {
        try {
            $resolvedInput = $this->resolveInputShape($inputShape, $commandArgs);
            return !empty($resolvedInput)
                ? $this->encoder->encode($resolvedInput)
                : $this->encoder->encodeEmptyIndefiniteMap();
        } catch (CborException $e) {
            throw new RpcV2CborException(
                'Unable to encode CBOR document ' . $inputShape->getName() . ': ' .
                $e->getMessage() . PHP_EOL
            );
        }
    }

    /**
     * Wraps blob values in order to be encoded properly into
     * byte strings.
     *
     * @param mixed $value
     *
     * @return string[]
     * @throws RpcV2CborException
     */
    protected function resolveBlob(mixed $value): array
    {
        if (is_resource($value)) {
            $value = stream_get_contents($value);
            if ($value === false) {
                throw new RpcV2CborException(
                    'Failed to read resource stream value during serialization',
                );
            }
        }

        // Wrapper to differentiate byte string values during encoding
        return ['__cbor_bytes' => (string) $value];
    }

    /**
     * Wraps timestamp values in order to be encoded properly into
     * value tag 1.
     *
     * @param mixed $value
     *
     * @return string[]
     * @throws RpcV2CborException
     */
    protected function resolveTimestamp(
        int|float|string|DateTimeInterface $value
    ): array
    {
        if (is_numeric($value)) {
            return ['__cbor_timestamp' => $value];
        }

        if ($value instanceof DateTimeInterface) {
            // Preserve milliseconds
            $micro = (int) $value->format('u');
            $value = $value->getTimestamp() + $micro / 1e6;
        } else {
            $timestamp = strtotime($value);
            if ($timestamp === false) {
                throw new RpcV2CborException(
                    'Request serialization failed: Invalid date/time: ' . $value,
                );
            }

            $value = $timestamp;
        }

        // Wrapper to differentiate timestamp values during encoding
        return ['__cbor_timestamp' => $value];
    }
}
