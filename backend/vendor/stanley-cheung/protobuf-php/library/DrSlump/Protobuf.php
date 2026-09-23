<?php

namespace DrSlump;

use DrSlump\Protobuf;

class Protobuf
{
    const VERSION = '@package_version@';

    const RULE_OPTIONAL = 1;
    const RULE_REQUIRED = 2;
    const RULE_REPEATED = 3;
    const RULE_UNKNOWN  = -1;

    const TYPE_DOUBLE   = 1;
    const TYPE_FLOAT    = 2;
    const TYPE_INT64    = 3;
    const TYPE_UINT64   = 4;
    const TYPE_INT32    = 5;
    const TYPE_FIXED64  = 6;
    const TYPE_FIXED32  = 7;
    const TYPE_BOOL     = 8;
    const TYPE_STRING   = 9;
    const TYPE_GROUP    = 10;
    const TYPE_MESSAGE  = 11;
    const TYPE_BYTES    = 12;
    const TYPE_UINT32   = 13;
    const TYPE_ENUM     = 14;
    const TYPE_SFIXED32 = 15;
    const TYPE_SFIXED64 = 16;
    const TYPE_SINT32   = 17;
    const TYPE_SINT64   = 18;
    const TYPE_UNKNOWN  = -1;


    static protected $codecs = array();


    /**
     * Setup SPL autoloader for Protobuf library classes
     *
     * @static
     * @return void
     */
    static public function autoload()
    {
        spl_autoload_register(function($class){
            $prefix = __CLASS__ . '\\';
            if (strpos($class, $prefix) === 0) {
                // Remove vendor from name
                $class = substr($class, strlen(__NAMESPACE__)+1);
                // Convert namespace separator to directory ones
                $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
                // Prefix with this file's directory
                $class = __DIR__ . DIRECTORY_SEPARATOR . $class;

                include($class . '.php');
                return true;
            }

            return false;
        });
    }

    /**
     * Obtain an instance of the descriptor's registry
     *
     * @static
     * @return Protobuf\Registry
     */
    static public function getRegistry()
    {
        static $registry = NULL;

        if (NULL === $registry) {
            $registry = new Protobuf\Registry();
        }

        return $registry;
    }


    static public function getCodec($codec = null)
    {
        if ($codec instanceof Protobuf\CodecInterface) {
            return $codec;
        }

        // Bootstrap the library's default codec if none is available
        if (!isset(self::$codecs['default'])) {
            $default = new Protobuf\Codec\Binary();
            self::registerCodec('default', $default);
            self::registerCodec('binary', $default);
        }

        if (is_string($codec)) {
            $codec = strtolower($codec);
            if (!isset(self::$codecs[$codec])) {
                throw new Protobuf\Exception('No codec found by name "' . $codec . '"');
            }
            return self::$codecs[$codec];
        }

        return self::getCodec('default');
    }

    static public function setDefaultCodec($codec)
    {
        if (is_string($codec)) {
            $codec = self::getCodec($codec);
        }

        if ($codec instanceof Protobuf\CodecInterface) {
            self::registerCodec('default', $codec);
        } else {
            throw new Protobuf\Exception('Codec must implement DrSlump\Protobuf\CodecInterface');
        }
    }

    static public function registerCodec($name, Protobuf\CodecInterface $codec)
    {
        $name = strtolower($name);
        self::$codecs[$name] = $codec;
    }

    static public function unregisterCodec($name)
    {
        $name = strtolower($name);
        if (isset(self::$codecs[$name])) {
            unset(self::$codecs[$name]);
            return true;
        }
        return false;
    }

    /**
     * Encodes a message using the default codec
     *
     * @static
     * @param \DrSlump\Protobuf\Message $message
     * @return string
     */
    static public function encode(Protobuf\Message $message)
    {
        $codec = self::getCodec();
        return $codec->encode($message);
    }

    /**
     * @static
     * @param String|Message $message
     * @param String $data
     * @return \DrSlump\Protobuf\Message
     */
    static public function decode($message, $data)
    {
        if (is_string($message)) {
            $message = '\\' . ltrim($message, '\\');
            $message = new $message;
        }

        $codec = self::getCodec();
        return $codec->decode($message, $data);
    }
}
