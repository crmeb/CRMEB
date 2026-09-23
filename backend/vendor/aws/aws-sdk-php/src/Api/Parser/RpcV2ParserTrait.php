<?php
namespace Aws\Api\Parser;

use Aws\Api\Cbor\Exception\CborException;
use Aws\Api\DateTimeResult;
use Aws\Api\Parser\Exception\ParserException;
use Aws\Api\Shape;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Shared parsing logic for RPC V2 Parsers.
 *
 * @internal
 */
trait RpcV2ParserTrait
{
    /**
     * Resolves output shape fields that are present in the response
     *
     * @param Shape $shape
     * @param mixed $value
     *
     * @return mixed
     */
    protected function resolveOutputShape(Shape $shape, mixed $value): mixed
    {
        if ($value === null) {
            return $value;
        }

        switch ($shape['type']) {
            case 'structure':
                $target = [];
                foreach ($shape->getMembers() as $name => $member) {
                    $locationName = $member['locationName'] ?: $name;
                    if (isset($value[$locationName])) {
                        $target[$name] = $this->resolveOutputShape($member, $value[$locationName]);
                    }
                }
                return $target;

            case 'list':
                $target = [];
                foreach ($value as $v) {
                    $target[] = $this->resolveOutputShape($shape->getMember(), $v);
                }
                return $target;

            case 'map':
                $target = [];
                foreach ($value as $k => $v) {
                    if ($v !== null) {
                        $target[$k] = $this->resolveOutputShape($shape->getValue(), $v);
                    }
                }
                return $target;

            case 'timestamp':
                try {
                    $value = DateTimeResult::fromEpoch($value);
                } catch (\Exception $e) {
                    trigger_error(
                        'Unable to parse timestamp value for '
                        . $shape->getName()
                        . ': ' . $e->getMessage(),
                        E_USER_WARNING
                    );
                }

                return $value;

            default:
                return $value;
        }
    }

    /**
     * Parses CBOR-encoded response data from RPC V2 CBOR services.
     *
     * @param StreamInterface $stream
     * @param ResponseInterface $response
     *
     * @return mixed
     */
    protected function parseCbor(
        StreamInterface $stream,
        ResponseInterface $response
    ): mixed
    {
        try {
            $cborString = (string) $stream;
            return empty($cborString)
                ? null
                : $this->decoder->decode($cborString);
        } catch (CborException $e) {
            throw new ParserException(
                "Malformed Response: error parsing CBOR: {$e->getMessage()}",
                0,
                $e,
                ['response' => $response]
            );
        }
    }
}
