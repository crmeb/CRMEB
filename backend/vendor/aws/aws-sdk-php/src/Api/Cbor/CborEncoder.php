<?php
namespace Aws\Api\Cbor;

use Aws\Api\Cbor\Exception\CborException;

/**
 * Encodes PHP values to Concise Binary Object Representation according to RFC 8949
 * https://www.rfc-editor.org/rfc/rfc8949.html
 *
 * Supports Major types 0-7 including:
 * - Type 0: Unsigned integers
 * - Type 1: Negative integers
 * - Type 2: Byte strings (via ['__cbor_bytes' => $data] wrappers)
 * - Type 3: Text strings (UTF-8)
 * - Type 4: Arrays
 * - Type 5: Maps
 * - Type 6: Tagged values (timestamps)
 * - Type 7: Simple values (null, bool, float)
 *
 * @internal
 */
final class CborEncoder
{
    /**
     * Pre-encoded integers 0-23 (single byte) and common larger values
     * CBOR major type 0 (unsigned integer)
     */
    private const INT_CACHE = [
        0 => "\x00", 1 => "\x01", 2 => "\x02", 3 => "\x03",
        4 => "\x04", 5 => "\x05", 6 => "\x06", 7 => "\x07",
        8 => "\x08", 9 => "\x09", 10 => "\x0A", 11 => "\x0B",
        12 => "\x0C", 13 => "\x0D", 14 => "\x0E", 15 => "\x0F",
        16 => "\x10", 17 => "\x11", 18 => "\x12", 19 => "\x13",
        20 => "\x14", 21 => "\x15", 22 => "\x16", 23 => "\x17",
        24 => "\x18\x18", 25 => "\x18\x19", 26 => "\x18\x1A",
        32 => "\x18\x20", 50 => "\x18\x32", 64 => "\x18\x40",
        100 => "\x18\x64", 128 => "\x18\x80", 200 => "\x18\xC8",
        255 => "\x18\xFF", 256 => "\x19\x01\x00", 500 => "\x19\x01\xF4",
        1000 => "\x19\x03\xE8", 1023 => "\x19\x03\xFF",
    ];

    /**
     * Pre-encoded negative integers -1 to -24 and common larger values
     * CBOR major type 1 (negative integer)
     */
    private const NEG_CACHE = [
        -1 => "\x20", -2 => "\x21", -3 => "\x22", -4 => "\x23",
        -5 => "\x24", -10 => "\x29", -20 => "\x33", -24 => "\x37",
        -25 => "\x38\x18", -50 => "\x38\x31", -100 => "\x38\x63",
    ];

    /**
     * Encode a PHP value to CBOR binary string
     *
     * @param mixed $value The value to encode
     *
     * @return string
     */
    public function encode(mixed $value): string
    {
        return $this->encodeValue($value);
    }

    /**
     * Recursively encode a value to CBOR
     *
     * @param mixed $value Value to encode
     * @return string Encoded CBOR bytes
     */
    private function encodeValue(mixed $value): string
    {
        switch (gettype($value)) {
            case 'string':
                $len = strlen($value);
                if ($len < 24) {
                    return chr(0x60 | $len) . $value;
                }

                if ($len < 0x100) {
                    return "\x78" . chr($len) . $value;
                }

                return $this->encodeTextString($value);

            case 'array':
                if (isset($value['__cbor_timestamp'])) {
                    return "\xC1\xFB" . pack('E', $value['__cbor_timestamp']);
                }

                // Encode a byte string (major type 2)
                if (isset($value['__cbor_bytes'])) {
                    $bytes = $value['__cbor_bytes'];
                    $len = strlen($bytes);
                    if ($len < 24) {
                        return chr(0x40 | $len) . $bytes;
                    }

                    if ($len < 0x100) {
                        return "\x58" . chr($len) . $bytes;
                    }

                    if ($len < 0x10000) {
                        return "\x59" . pack('n', $len) . $bytes;
                    }

                    return "\x5A" . pack('N', $len) . $bytes;
                }

                if (array_is_list($value)) {
                    return $this->encodeArray($value);
                }

                return $this->encodeMap($value);

            case 'integer':
                if (isset(self::INT_CACHE[$value])) {
                    return self::INT_CACHE[$value];
                }

                if (isset(self::NEG_CACHE[$value])) {
                    return self::NEG_CACHE[$value];
                }

                // Fast path for positive integers
                // Major type 0: unsigned integer
                if ($value >= 0) {
                    if ($value < 24) {
                        return chr($value);
                    }

                    if ($value < 0x100) {
                        return "\x18" . chr($value);
                    }

                    if ($value < 0x10000) {
                        return "\x19" . pack('n', $value);
                    }

                    if ($value < 0x100000000) {
                        return "\x1A" . pack('N', $value);
                    }

                    return "\x1B" . pack('J', $value);
                }

                return $this->encodeInteger($value);

            case 'double':
                // Encode a float (major type 7, float 64)
                return "\xFB" . pack('E', $value);

            case 'boolean':
                // Encode a boolean (major type 7, simple)
                return $value ? "\xF5" : "\xF4";

            case 'NULL':
                // Encode null (major type 7, simple)
                return "\xF6";

            case 'object':
                throw new CborException("Cannot encode object of type: " . get_class($value));

            default:
                throw new CborException("Cannot encode value of type: " . gettype($value));
        }
    }

    /**
     * Encode an integer (major type 0 or 1)
     *
     * @param int $value
     * @return string
     */
    private function encodeInteger(int $value): string
    {
        if (isset(self::INT_CACHE[$value])) {
            return self::INT_CACHE[$value];
        }

        if (isset(self::NEG_CACHE[$value])) {
            return self::NEG_CACHE[$value];
        }

        if ($value >= 0) {
            // Major type 0: unsigned integer
            if ($value < 24) {
                return chr($value);
            }

            if ($value < 0x100) {
                return "\x18" . chr($value);
            }

            if ($value < 0x10000) {
                return "\x19" . pack('n', $value);
            }

            if ($value < 0x100000000) {
                return "\x1A" . pack('N', $value);
            }

            return "\x1B" . pack('J', $value);
        }

        // Major type 1: negative integer (-1 - n)
        $value = -1 - $value;
        if ($value < 24) {
            return chr(0x20 | $value);
        }

        if ($value < 0x100) {
            return "\x38" . chr($value);
        }

        if ($value < 0x10000) {
            return "\x39" . pack('n', $value);
        }

        if ($value < 0x100000000) {
            return "\x3A" . pack('N', $value);
        }

        return "\x3B" . pack('J', $value);
    }

    /**
     * Encode a text string (major type 3)
     *
     * @param string $value
     * @return string
     */
    private function encodeTextString(string $value): string
    {
        $len = strlen($value);

        if ($len < 24) {
            return chr(0x60 | $len) . $value;
        }

        if ($len < 0x100) {
            return "\x78" . chr($len) . $value;
        }

        if ($len < 0x10000) {
            return "\x79" . pack('n', $len) . $value;
        }

        if ($len < 0x100000000) {
            return "\x7A" . pack('N', $len) . $value;
        }

        return "\x7B" . pack('J', $len) . $value;
    }

    /**
     * Encode an array (major type 4)
     *
     * @param array $value
     * @return string
     */
    private function encodeArray(array $value): string
    {
        $count = count($value);

        if ($count < 24) {
            $result = chr(0x80 | $count);
        } elseif ($count < 0x100) {
            $result = "\x98" . chr($count);
        } elseif ($count < 0x10000) {
            $result = "\x99" . pack('n', $count);
        } elseif ($count < 0x100000000) {
            $result = "\x9A" . pack('N', $count);
        } else {
            $result = "\x9B" . pack('J', $count);
        }

        foreach ($value as $item) {
            $result .= $this->encodeValue($item);
        }

        return $result;
    }

    /**
     * Encode a map (major type 5)
     *
     * @param array $value
     * @return string
     */
    private function encodeMap(array $value): string
    {
        $count = count($value);

        if ($count < 24) {
            $result = chr(0xA0 | $count);
        } elseif ($count < 0x100) {
            $result = "\xB8" . chr($count);
        } elseif ($count < 0x10000) {
            $result = "\xB9" . pack('n', $count);
        } elseif ($count < 0x100000000) {
            $result = "\xBA" . pack('N', $count);
        } else {
            $result = "\xBB" . pack('J', $count);
        }

        foreach ($value as $k => $v) {
            if (is_int($k)) {
                $result .= $this->encodeInteger($k);
            } else {
                $len = strlen($k);
                if ($len < 24) {
                    $result .= chr(0x60 | $len) . $k;
                } elseif ($len < 0x100) {
                    $result .= "\x78" . chr($len) . $k;
                } else {
                    $result .= "\x79" . pack('n', $len) . $k;
                }
            }

            $result .= $this->encodeValue($v);
        }

        return $result;
    }

    /**
     * Create an empty map (major type 5 with 0 elements)
     *
     * @return string
     */
    public function encodeEmptyMap(): string
    {
        return "\xA0";
    }

    /**
     * Create an empty indefinite map (major type 5 indefinite length)
     *
     * @return string
     */
    public function encodeEmptyIndefiniteMap(): string
    {
        return "\xBF\xFF";
    }
}
