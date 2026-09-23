<?php

namespace DrSlump\Protobuf\Codec\Binary;

/**
 * Implements reading primitives for Protobuf binary streams
 *
 * Important: There are no checks in place for overflows, so you must
 * be aware of PHP's integer and floating point limits.
 *
 * @note Protobuf uses little-endian order
 */
class Reader
{
    /** @var resource */
    protected $_fd;

    /**
     * Create a new reader from a file descriptor or a string of bytes
     *
     * @param resource|string $fdOrString
     */
    public function __construct($fdOrString = NULL)
    {
        if (NULL !== $fdOrString) {
            $this->init($fdOrString);
        }
    }

    public function __destruct()
    {
        $this->_fd && fclose($this->_fd);
    }

    /**
     * Create a new reader from a file descriptor or a string of bytes
     *
     * @param resource|string $fdOrString
     */
    public function init($fdOrString)
    {
        if (is_resource($fdOrString)) {
            $this->_fd = $fdOrString;
        } else {
            // @todo Could this be faster by using a custom String wrapper?
            $this->_fd = fopen('data://text/plain,' . urlencode($fdOrString), 'rb');
        }
    }


    /**
     * Obtain a number of bytes from the string
     *
     * @throws \RuntimeException
     * @param int $length
     * @return string
     */
    public function read($length)
    {
        // Protect against 0 byte reads when an EOF
        if ($length < 1) return '';

        $bytes = fread($this->_fd, $length);
        if (FALSE === $bytes) {
            throw new \RuntimeException('Failed to read ' . $length . ' bytes');
        }

        return $bytes;
    }

    /**
     * Check if we have reached the end of the stream
     *
     * @return bool
     */
    public function eof()
    {
        return feof($this->_fd);
    }

    /**
     * Obtain the current position in the stream
     *
     * @return int
     */
    public function pos()
    {
        return ftell($this->_fd);
    }

    /**
     * Obtain a byte
     *
     * @return int
     */
    public function byte()
    {
        return ord($this->read(1));
    }

    /**
     * Decode a varint
     *
     * @return int
     */
    public function varint()
    {
        $result = $shift = 0;
        do {
            $byte = $this->byte();
            $result |= ($byte & 0x7f) << $shift;
            $shift += 7;
        } while ($byte > 0x7f);

        return $result;
    }

    /**
     * Decodes a zigzag integer of the given bits
     *
     * @param int $bits - Either 32 or 64
     */
    public function zigzag()
    {
        $number = $this->varint();
        return ($number >> 1) ^ (-($number & 1));
    }

    /**
     * Decode a fixed 32bit integer with sign
     *
     * @return int
     */
    public function sFixed32()
    {
        $bytes = $this->read(4);
        if ($this->isBigEndian()) {
            $bytes = strrev($bytes);
        }

        list(, $result) = unpack('l', $bytes);
        return $result;
    }

    /**
     * Decode a fixed 32bit integer without sign
     *
     * @return int
     */
    public function fixed32()
    {
        $bytes = $this->read(4);

        if (PHP_INT_SIZE < 8) {
            list(, $lo, $hi) = unpack('v*', $bytes);
            $result = $hi << 16 | $lo;
        } else {
            list(, $result) = unpack('V*', $bytes);
        }

        return $result;
    }

    /**
     * Decode a fixed 62bit integer with sign
     *
     * @return int
     */
    public function sFixed64()
    {
        $bytes = $this->read(8);

        list(, $lo0, $lo1, $hi0, $hi1) = unpack('v*', $bytes);
        return ($hi1 << 16 | $hi0) << 32 | ($lo1 << 16 | $lo0);
    }

    /**
     * Decode a fixed 62bit integer without sign
     *
     * @return int
     */
    public function fixed64()
    {
        return $this->sFixed64();
    }

    /**
     * Decode a 32bit float
     *
     * @return float
     */
    public function float()
    {
        $bytes = $this->read(4);
        if ($this->isBigEndian()) {
            $bytes = strrev($bytes);
        }

        list(, $result) = unpack('f', $bytes);
        return $result;
    }

    /**
     * Decode a 64bit double
     *
     * @return float
     */
    public function double()
    {
        $bytes = $this->read(8);
        if ($this->isBigEndian()) {
            $bytes = strrev($bytes);
        }

        list(, $result) = unpack('d', $bytes);
        return $result;
    }

    /**
     * Check if the current architecture is Big Endian
     *
     * @return bool
     */
    public function isBigEndian()
    {
        static $endianness;

        if (NULL === $endianness) {
            list(,$result) = unpack('L', pack('V', 1));
            $endianness = $result !== 1;
        }

        return $endianness;
    }
}
