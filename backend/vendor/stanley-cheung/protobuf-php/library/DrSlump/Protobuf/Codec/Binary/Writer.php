<?php

namespace DrSlump\Protobuf\Codec\Binary;

/**
 * Implements writing primitives for Protobuf binary streams
 *
 * @note Protobuf uses little-endian order
 */
class Writer
{
    /** @var resource */
    protected $_fd;


    public function __construct()
    {
        $this->_fd = fopen('php://memory', 'wb');
    }

    public function __destruct()
    {
        fclose($this->_fd);
    }

    /**
     * Get the current bytes in the stream
     *
     * @return string
     */
    public function getBytes()
    {
        fseek($this->_fd, 0, SEEK_SET);
        return stream_get_contents($this->_fd);
    }

    /**
     * Store the given bytes in the stream
     *
     * @throws \RuntimeException
     * @param string $bytes
     * @param int $length
     */
    public function write($bytes, $length = null)
    {
        if ($length === NULL) {
            $length = strlen($bytes);
        }

        $written = fwrite($this->_fd, $bytes, $length);
        if ($written !== $length) {
            throw new \RuntimeException('Failed to write ' . $length . ' bytes');
        }
    }

    /**
     * Store a single byte
     *
     * @param int $value
     */
    public function byte($value)
    {
        $this->write(chr($value), 1);
    }

    /**
     * Store an integer encoded as varint
     *
     * @throws \OutOfBoundsException
     * @param int $value
     */
    public function varint($value)
    {
        // Small values do not need to be encoded
        if ($value >= 0 && $value < 0x80) {
            $this->byte($value);
            return;
        }

        // Build an array of bytes with the encoded values
        if ($value > 0) {
            $values = array();
            while ($value > 0) {
                $values[] = 0x80 | ($value & 0x7f);
                $value = $value >> 7;
            }
        } else if (function_exists('gmp_init')) {
            $value = PHP_INT_SIZE < 8
                   ? gmp_and($value, '0x0ffffffffffffffff')
                   : sprintf('%u', $value);

            $values = $this->varint_gmp($value);
        } else if (PHP_INT_SIZE < 8) {
            throw new \OutOfBoundsException(
                "PHP versions compiled with 32bit integers can only support negative integer encoding with GMP extension ($value was given)"
            );
        } else if (function_exists('bccomp')) {
            $value = sprintf('%u', $value);
            $values = $this->varint_bc($value);
        } else {
            throw new \OutOfBoundsException("Varints of negative integers are only supported with GMP or BC big integers PHP extensions ($value was given)");
        }

        // Remove the MSB flag from the last byte
        $values[count($values)-1] &= 0x7f;

        // Convert the byte sized ints to actual bytes in a string
        //$bytes = implode('', array_map('chr', $values));
        $bytes = call_user_func_array('pack', array_merge(array('C*'), $values));;

        $this->write($bytes);
    }

    public function varint_gmp($value)
    {
        static $x00, $x7f, $x80;

        if (NULL === $x00) {
            $x00 = \gmp_init(0x00);
            $x7f = \gmp_init(0x7f);
            $x80 = \gmp_init(0x80);
        }

        $values = array();
        while (\gmp_cmp($value, $x00) > 0) {
            $values[] = \gmp_intval(\gmp_and($value, $x7f)) | 0x80;
            $value = \gmp_div_q($value, $x80);
        }

        return $values;
    }

    public function varint_bc($value)
    {
        $values = array();
        while (\bccomp($value, 0, 0) > 0) {
            // Get the last 7bits of the number
            $bin = '';
            $dec = $value;
            do {
                $rest = bcmod($dec, 2);
                $dec = bcdiv($dec, 2, 0);
                $bin = $rest . $bin;
            } while ($dec > 0 && strlen($bin) < 7);

            // Pack as a decimal and apply the flag
            $values[] = intval($bin, 2) | 0x80;

            $value = bcdiv($value, 0x80, 0);
        }

        return $values;
    }

    /**
     * Encodes an integer with zigzag
     *
     * @param int $value
     * @param int $base  Either 32 or 64 bits
     */
    public function zigzag($value, $base = 32)
    {
        $value = ($value << 1) ^ ($value >> $base-1);
        $this->varint($value);
    }

    /**
     * Encode an integer as a fixed of 32bits with sign
     *
     * @param int $value
     */
    public function sFixed32($value)
    {
        $bytes = pack('l*', $value);
        if ($this->isBigEndian()) {
            $bytes = strrev($bytes);
        }

        $this->write($bytes, 4);
    }

    /**
     * Encode an integer as a fixed of 32bits without sign
     *
     * @param int $value
     */
    public function fixed32($value)
    {
        $bytes = pack('V*', $value);
        $this->write($bytes, 4);
    }

    /**
     * Encode an integer as a fixed of 64bits with sign
     *
     * @param int $value
     */
    public function sFixed64($value)
    {
        if ($value >= 0) {
            $this->fixed64($value);
        } else if (function_exists('gmp_init')) {
            $this->sFixed64_gmp($value);
        } else if (function_exists('bcadd')) {
            $this->sFixed64_bc($value);
        } else {
            throw new \OutOfBoundsException("The signed Fixed64 type with negative integers is only supported with GMP or BC big integers PHP extensions ($value was given)");
        }
    }

    public function sFixed64_gmp($value)
    {
        static $xff, $x100;

        if (NULL === $xff) {
            $xff = gmp_init(0xff);
            $x100 = gmp_init(0x100);
        }

        $value = PHP_INT_SIZE < 8
               ? gmp_and($value, '0x0ffffffffffffffff')
               : gmp_init(sprintf('%u', $value));

        $bytes = '';
        for ($i=0; $i<8; $i++) {
            $bytes .= chr(gmp_intval(gmp_and($value, $xff)));
            $value = gmp_div_q($value, $x100);
        }

        $this->write($bytes);
    }

    public function sFixed64_bc($value)
    {
        if (PHP_INT_SIZE < 8) {
            throw new \OutOfBoundsException(
                "PHP versions compiled with 32bit integers can only support negative integer encoding with GMP extension ($value was given)"
            );
        }

        $value = sprintf('%u', $value);

        $bytes = '';
        for ($i=0; $i<8; $i++) {
            // Get the last 8bits of the number
            $bin = '';
            $dec = $value;
            do {
                $bin = bcmod($dec, 2) . $bin;
                $dec = bcdiv($dec, 2, 0);
            } while (strlen($bin) < 8);

            // Pack the byte
            $bytes .= chr(intval($bin, 2));

            $value = bcdiv($value, 0x100, 0);
        }

        $this->write($bytes);
    }

    /**
     * Encode an integer as a fixed of 64bits without sign
     *
     * @param int $value
     */
    public function fixed64($value)
    {
        $bytes = pack('V*', $value & 0xffffffff, $value / (0xffffffff+1));
        $this->write($bytes, 8);
    }

    /**
     * Encode a number as a 32bit float
     *
     * @param float $value
     */
    public function float($value)
    {
        $bytes = pack('f*', $value);
        if ($this->isBigEndian()) {
            $bytes = strrev($bytes);
        }
        $this->write($bytes, 4);
    }

    /**
     * Encode a number as a 64bit double
     *
     * @param float $value
     */
    public function double($value)
    {
        $bytes = pack('d*', $value);
        if ($this->isBigEndian()) {
            $bytes = strrev($bytes);
        }
        $this->write($bytes, 8);
    }

    /**
     * Checks if the current architecture is Big Endian
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
