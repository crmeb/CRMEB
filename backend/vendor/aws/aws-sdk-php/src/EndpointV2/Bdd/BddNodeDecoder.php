<?php

namespace Aws\EndpointV2\Bdd;

use Aws\Exception\UnresolvedEndpointException;

/**
 * Decodes the base64 `nodes` string shipped in the endpointBdd trait into a
 * flat array of signed 32-bit integers. Every node occupies three slots:
 * `[conditionIndex, highRef, lowRef]`.
 *
 * The flat representation is intentional — indexing into an int array is
 * cheaper per step than materialising a node object for every traversal,
 * and the evaluator hot loop references thousands of slots for a large BDD.
 *
 * @internal
 */
final class BddNodeDecoder
{
    private const BYTES_PER_NODE = 12;
    private const INT_32_MAX = 2147483647;
    private const INT_32_OFFSET = 4294967296;

    /**
     * Decodes `$encoded` and verifies that the byte count matches
     * `$expectedNodeCount`. Returns a flat int array of length
     * `3 * $expectedNodeCount`.
     *
     * @throws UnresolvedEndpointException when the payload is not valid base64
     *     or its length does not match the declared node count.
     */
    public static function decode(string $encoded, int $expectedNodeCount): array
    {
        $bytes = base64_decode($encoded, true);
        if ($bytes === false) {
            throw new UnresolvedEndpointException(
                'Endpoint BDD nodes are not valid base64.'
            );
        }

        $expectedBytes = $expectedNodeCount * self::BYTES_PER_NODE;
        if (strlen($bytes) !== $expectedBytes) {
            throw new UnresolvedEndpointException(sprintf(
                'Endpoint BDD node payload is %d bytes but %d were expected'
                . ' for %d nodes.',
                strlen($bytes),
                $expectedBytes,
                $expectedNodeCount
            ));
        }

        if ($expectedNodeCount === 0) {
            return [];
        }

        // unpack() with 'N' returns unsigned 32-bit big-endian ints. We fold
        // values above INT_32_MAX back into signed space to match the trait.
        $unsigned = unpack('N*', $bytes);
        $flat = [];
        foreach ($unsigned as $value) {
            $flat[] = $value > self::INT_32_MAX
                ? $value - self::INT_32_OFFSET
                : $value;
        }

        return $flat;
    }
}
