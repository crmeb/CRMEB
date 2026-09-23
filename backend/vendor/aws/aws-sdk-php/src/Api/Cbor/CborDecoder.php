<?php
namespace Aws\Api\Cbor;

use Aws\Api\Cbor\Exception\CborException;

/**
 * Decodes Concise Binary Object Representation encoded strings
 * into PHP values according to RFC 8949
 *
 * https://www.rfc-editor.org/rfc/rfc8949.html
 *
 *  Supports Major types 0-7 including:
 *  - Type 0: Unsigned integers
 *  - Type 1: Negative integers
 *  - Type 2: Byte strings
 *  - Type 3: Text strings (UTF-8)
 *  - Type 4: Arrays
 *  - Type 5: Maps
 *  - Type 6: Tagged values (timestamps)
 *  - Type 7: Simple values (null, bool, float)
 *
 * @internal
 */
final class CborDecoder
{
    private int $offset;
    private int $length;

    /**
     * Decode CBOR binary data to PHP value
     *
     * @param string $data The CBOR-encoded binary data to decode
     * 
     * @return mixed The decoded PHP value (can be any type: int, string, array, bool, null, float)
     * @throws CborException If data is empty or malformed CBOR
     */
    public function decode(string $data): mixed
    {
        if ($data === '') {
            throw new CborException("No data to decode");
        }

        $this->offset = 0;
        $this->length = strlen($data);

        return $this->decodeValue($data);
    }

    /**
     * Decode multiple CBOR values from sequential binary data
     *
     * @param string $data The CBOR-encoded binary data containing multiple values
     * 
     * @return array Array of decoded PHP values in the order they appear in the data
     * @throws CborException If data is malformed CBOR
     */
    public function decodeAll(string $data): array
    {
        $this->length = strlen($data);
        $this->offset = 0;
        $values = [];

        while ($this->offset < $this->length) {
            $values[] = $this->decodeValue($data);
        }

        return $values;
    }

    /**
     * Decodes a single CBOR value at the current offset
     *
     * @param string $data Reference to the CBOR data being decoded
     * 
     * @return mixed The decoded value
     * @throws CborException If unexpected end of data or invalid CBOR format
     */
    private function decodeValue(string &$data): mixed
    {
        $offset = $this->offset;
        $length = $this->length;

        if ($offset >= $length) {
            throw new CborException("Unexpected end of data");
        }

        $byte = ord($data[$offset++]);
        $majorType = $byte >> 5;
        $info = $byte & 0x1F;

        switch ($majorType) {
            case 0: // Unsigned integer
                if ($info < 24) {
                    $this->offset = $offset;

                    return $info;
                }

                switch ($info) {
                    case 24:
                        if ($offset >= $length) {
                            throw new CborException("Not enough data");
                        }

                        $this->offset = $offset + 1;

                        return ord($data[$offset]);

                    case 25:
                        if ($offset + 2 > $length) {
                            throw new CborException("Not enough data");
                        }

                        $this->offset = $offset + 2;

                        return (ord($data[$offset]) << 8) | ord($data[$offset + 1]);

                    case 26:
                        if ($offset + 4 > $length) {
                            throw new CborException("Not enough data");
                        }

                        $this->offset = $offset + 4;

                        return unpack('N', $data, $offset)[1];

                    case 27:
                        if ($offset + 8 > $length) {
                            throw new CborException("Not enough data");
                        }

                        $this->offset = $offset + 8;

                        return unpack('J', $data, $offset)[1];

                    default:
                        throw new CborException("Invalid additional info for integer: $info");
                }

            case 1: // Negative integer
                if ($info < 24) {
                    $this->offset = $offset;

                    return -1 - $info;
                }

                switch ($info) {
                    case 24:
                        if ($offset >= $length) {
                            throw new CborException("Not enough data");
                        }

                        $this->offset = $offset + 1;

                        return -1 - ord($data[$offset]);

                    case 25:
                        if ($offset + 2 > $length) {
                            throw new CborException("Not enough data");
                        }

                        $this->offset = $offset + 2;

                        return -1 - ((ord($data[$offset]) << 8) | ord($data[$offset + 1]));

                    case 26:
                        if ($offset + 4 > $length) {
                            throw new CborException("Not enough data");
                        }

                        $this->offset = $offset + 4;

                        return -1 - unpack('N', $data, $offset)[1];

                    case 27:
                        if ($offset + 8 > $length) {
                            throw new CborException("Not enough data");
                        }

                        $this->offset = $offset + 8;
                        $unsigned = unpack('J', $data, $offset)[1];

                        return ($unsigned === 9223372036854775807) ? PHP_INT_MIN : -1 - $unsigned;

                    default:
                        throw new CborException("Invalid additional info for integer: $info");
                }

            case 2: // Byte string
                if ($info < 24) {
                    $len = $info;
                } else {
                    switch ($info) {
                        case 24:
                            if ($offset >= $length) {
                                throw new CborException("Not enough data");
                            }

                            $len = ord($data[$offset++]);
                            break;

                        case 25:
                            if ($offset + 2 > $length) {
                                throw new CborException("Not enough data");
                            }

                            $len = (ord($data[$offset]) << 8) | ord($data[$offset + 1]);
                            $offset += 2;
                            break;

                        case 26:
                            if ($offset + 4 > $length) {
                                throw new CborException("Not enough data");
                            }

                            $len = unpack('N', $data, $offset)[1];
                            $offset += 4;
                            break;

                        case 27:
                            if ($offset + 8 > $length) {
                                throw new CborException("Not enough data");
                            }

                            $len = unpack('J', $data, $offset)[1];
                            $offset += 8;
                            break;

                        case 31:
                            $this->offset = $offset;

                            return $this->decodeIndefiniteString($data, 0x40);

                        default:
                            throw new CborException("Invalid additional info for byte string: $info");
                    }
                }

                if ($offset + $len > $length) {
                    throw new CborException("Not enough data");
                }

                $this->offset = $offset + $len;

                return substr($data, $offset, $len);

            case 3: // Text string
                if ($info < 24) {
                    $len = $info;
                } else {
                    switch ($info) {
                        case 24:
                            if ($offset >= $length) {
                                throw new CborException("Not enough data");
                            }

                            $len = ord($data[$offset++]);
                            break;

                        case 25:
                            if ($offset + 2 > $length) {
                                throw new CborException("Not enough data");
                            }

                            $len = (ord($data[$offset]) << 8) | ord($data[$offset + 1]);
                            $offset += 2;
                            break;

                        case 26:
                            if ($offset + 4 > $length) {
                                throw new CborException("Not enough data");
                            }

                            $len = unpack('N', $data, $offset)[1];
                            $offset += 4;
                            break;

                        case 27:
                            if ($offset + 8 > $length) {
                                throw new CborException("Not enough data");
                            }

                            $len = unpack('J', $data, $offset)[1];
                            $offset += 8;
                            break;

                        case 31:
                            $this->offset = $offset;

                            return $this->decodeIndefiniteString($data, 0x60);

                        default:
                            throw new CborException("Invalid additional info for text string: $info");
                    }
                }

                if ($offset + $len > $length) {
                    throw new CborException("Not enough data");
                }

                $this->offset = $offset + $len;

                return substr($data, $offset, $len);

            case 4: // Array
                if ($info < 24) {
                    $count = $info;
                } else {
                    switch ($info) {
                        case 24:
                            if ($offset >= $length) {
                                throw new CborException("Not enough data");
                            }

                            $count = ord($data[$offset++]);
                            break;

                        case 25:
                            if ($offset + 2 > $length) {
                                throw new CborException("Not enough data");
                            }

                            $count = (ord($data[$offset]) << 8) | ord($data[$offset + 1]);
                            $offset += 2;
                            break;

                        case 26:
                            if ($offset + 4 > $length) {
                                throw new CborException("Not enough data");
                            }

                            $count = unpack('N', $data, $offset)[1];
                            $offset += 4;
                            break;

                        case 27:
                            if ($offset + 8 > $length) {
                                throw new CborException("Not enough data");
                            }

                            $count = unpack('J', $data, $offset)[1];
                            $offset += 8;
                            break;

                        case 31:
                            $this->offset = $offset;

                            return $this->decodeIndefiniteArray($data);

                        default:
                            throw new CborException("Invalid additional info for array: $info");
                    }
                }

                $this->offset = $offset;
                $arr = [];

                for ($i = 0; $i < $count; $i++) {
                    $arr[] = $this->decodeValue($data);
                }

                return $arr;

            case 5: // Map
                if ($info < 24) {
                    $count = $info;
                } else {
                    switch ($info) {
                        case 24:
                            if ($offset >= $length) {
                                throw new CborException("Not enough data");
                            }

                            $count = ord($data[$offset++]);
                            break;

                        case 25:
                            if ($offset + 2 > $length) {
                                throw new CborException("Not enough data");
                            }

                            $count = (ord($data[$offset]) << 8) | ord($data[$offset + 1]);
                            $offset += 2;
                            break;

                        case 26:
                            if ($offset + 4 > $length) {
                                throw new CborException("Not enough data");
                            }

                            $count = unpack('N', $data, $offset)[1];
                            $offset += 4;
                            break;

                        case 27:
                            if ($offset + 8 > $length) {
                                throw new CborException("Not enough data");
                            }

                            $count = unpack('J', $data, $offset)[1];
                            $offset += 8;
                            break;

                        case 31:
                            $this->offset = $offset;

                            return $this->decodeIndefiniteMap($data);

                        default:
                            throw new CborException("Invalid additional info for map: $info");
                    }
                }

                $this->offset = $offset;
                $map = [];

                for ($i = 0; $i < $count; $i++) {
                    $key = $this->decodeValue($data);
                    $map[$key] = $this->decodeValue($data);
                }

                return $map;

            case 6: // Tag
                switch ($info) {
                    case 24:
                        $offset++;
                        break;

                    case 25:
                        $offset += 2;
                        break;

                    case 26:
                        $offset += 4;
                        break;

                    case 27:
                        $offset += 8;
                        break;
                }

                $this->offset = $offset;

                return $this->decodeValue($data);

            case 7: // Simple/float
                switch ($info) {
                    case 20:
                        $this->offset = $offset;

                        return false;

                    case 21:
                        $this->offset = $offset;

                        return true;

                    case 22:
                    case 23:
                        $this->offset = $offset;

                        return null;

                    case 25: // Half-precision float
                        if ($offset + 2 > $length) {
                            throw new CborException("Not enough data");
                        }

                        $this->offset = $offset + 2;
                        $half = (ord($data[$offset]) << 8) | ord($data[$offset + 1]);
                        $sign = ($half >> 15) & 0x01;
                        $exp = ($half >> 10) & 0x1F;
                        $mant = $half & 0x3FF;

                        if ($exp === 0) {
                            return $mant === 0
                                ? ($sign ? -0.0 : 0.0)
                                : ($sign ? -1 : 1) * pow(2, -14) * ($mant / 1024);
                        }

                        if ($exp === 31) {
                            return $mant === 0 ? ($sign ? -INF : INF) : NAN;
                        }

                        return (float) (($sign ? -1 : 1) * pow(2, $exp - 15) * (1 + $mant / 1024));

                    case 26: // Single-precision float
                        if ($offset + 4 > $length) {
                            throw new CborException("Not enough data");
                        }

                        $this->offset = $offset + 4;

                        return unpack('G', $data, $offset)[1];

                    case 27: // Double-precision float
                        if ($offset + 8 > $length) {
                            throw new CborException("Not enough data");
                        }

                        $this->offset = $offset + 8;

                        return unpack('E', $data, $offset)[1];

                    case 31:
                        throw new CborException("Unexpected break");

                    default:
                        throw new CborException("Unknown simple value: $info");
                }

            default:
                throw new CborException("Unknown major type: $majorType");
        }
    }

    /**
     * Decode indefinite-length string (byte or text)
     *
     * @param string $data Reference to the CBOR data being decoded
     * @param int $expectedMajor Expected major type (0x40 for byte string, 0x60 for text string)
     * 
     * @return string The concatenated string from all chunks
     * @throws CborException If invalid chunk format or unexpected end of data
     */
    private function decodeIndefiniteString(string &$data, int $expectedMajor): string
    {
        $chunks = [];

        while (true) {
            $offset = $this->offset;
            $length = $this->length;

            if ($offset >= $length) {
                throw new CborException("Unexpected end of data");
            }

            $byte = ord($data[$offset++]);

            if ($byte === 0xFF) {
                $this->offset = $offset;

                return implode('', $chunks);
            }

            if (($byte & 0xE0) !== $expectedMajor) {
                throw new CborException("Invalid chunk in indefinite string");
            }

            $info = $byte & 0x1F;

            if ($info === 31) {
                throw new CborException("Nested indefinite string");
            }

            if ($info < 24) {
                $len = $info;
            } else {
                switch ($info) {
                    case 24:
                        if ($offset >= $length) {
                            throw new CborException("Not enough data");
                        }

                        $len = ord($data[$offset++]);
                        break;

                    case 25:
                        if ($offset + 2 > $length) {
                            throw new CborException("Not enough data");
                        }

                        $len = (ord($data[$offset]) << 8) | ord($data[$offset + 1]);
                        $offset += 2;
                        break;

                    case 26:
                        if ($offset + 4 > $length) {
                            throw new CborException("Not enough data");
                        }

                        $len = unpack('N', $data, $offset)[1];
                        $offset += 4;
                        break;

                    case 27:
                        if ($offset + 8 > $length) {
                            throw new CborException("Not enough data");
                        }

                        $len = unpack('J', $data, $offset)[1];
                        $offset += 8;
                        break;

                    default:
                        throw new CborException("Invalid chunk length info: $info");
                }
            }

            if ($offset + $len > $length) {
                throw new CborException("Not enough data for chunk");
            }

            $chunks[] = substr($data, $offset, $len);
            $this->offset = $offset + $len;
        }
    }

    /**
     * Decode indefinite-length array
     *
     * @param string $data Reference to the CBOR data being decoded
     * 
     * @return array The decoded array elements
     * @throws CborException If unexpected end of data
     */
    private function decodeIndefiniteArray(string &$data): array
    {
        $result = [];

        while (true) {
            if ($this->offset >= $this->length) {
                throw new CborException("Unexpected end of data");
            }

            if (ord($data[$this->offset]) === 0xFF) {
                $this->offset++;

                return $result;
            }

            $result[] = $this->decodeValue($data);
        }
    }

    /**
     * Decode indefinite-length map
     *
     * @param string $data Reference to the CBOR data being decoded
     * 
     * @return array The decoded map as associative array
     * @throws CborException If unexpected end of data or odd number of items
     */
    private function decodeIndefiniteMap(string &$data): array
    {
        $result = [];

        while (true) {
            if ($this->offset >= $this->length) {
                throw new CborException("Unexpected end of data");
            }

            if (ord($data[$this->offset]) === 0xFF) {
                $this->offset++;

                return $result;
            }

            $key = $this->decodeValue($data);
            $result[$key] = $this->decodeValue($data);
        }
    }
}
