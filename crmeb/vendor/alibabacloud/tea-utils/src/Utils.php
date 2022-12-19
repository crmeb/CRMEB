<?php

namespace AlibabaCloud\Tea\Utils;

use AlibabaCloud\Tea\Model;
use GuzzleHttp\Psr7\Stream;
use Psr\Http\Message\StreamInterface;

class Utils
{
    private static $defaultUserAgent = '';

    /**
     * Convert a string(utf8) to bytes.
     *
     * @param string $string
     *
     * @return array the return bytes
     */
    public static function toBytes($string)
    {
        $bytes = [];
        for ($i = 0; $i < \strlen($string); ++$i) {
            $bytes[] = \ord($string[$i]);
        }

        return $bytes;
    }

    /**
     * Convert a bytes to string(utf8).
     *
     * @param array $bytes
     *
     * @return string the return string
     */
    public static function toString($bytes)
    {
        $str = '';
        foreach ($bytes as $ch) {
            $str .= \chr($ch);
        }

        return $str;
    }

    /**
     * Parse it by JSON format.
     *
     * @param string $jsonString
     *
     * @return array the parsed result
     */
    public static function parseJSON($jsonString)
    {
        return json_decode($jsonString, true);
    }

    /**
     * Read data from a readable stream, and compose it to a bytes.
     *
     * @param StreamInterface $stream the readable stream
     *
     * @return array the bytes result
     */
    public static function readAsBytes($stream)
    {
        $str = self::readAsString($stream);

        return self::toBytes($str);
    }

    /**
     * Read data from a readable stream, and compose it to a string.
     *
     * @param StreamInterface $stream the readable stream
     *
     * @return string the string result
     */
    public static function readAsString($stream)
    {
        if ($stream->isSeekable()) {
            $stream->rewind();
        }

        return $stream->getContents();
    }

    /**
     * Read data from a readable stream, and parse it by JSON format.
     *
     * @param StreamInterface $stream the readable stream
     *
     * @return array the parsed result
     */
    public static function readAsJSON($stream)
    {
        return self::parseJSON(self::readAsString($stream));
    }

    /**
     * Generate a nonce string.
     *
     * @return string the nonce string
     */
    public static function getNonce()
    {
        return md5(uniqid() . uniqid(md5(microtime(true)), true));
    }

    /**
     * Get an UTC format string by current date, e.g. 'Thu, 06 Feb 2020 07:32:54 GMT'.
     *
     * @return string the UTC format string
     */
    public static function getDateUTCString()
    {
        return gmdate('D, d M Y H:i:s T');
    }

    /**
     * If not set the real, use default value.
     *
     * @param string $real
     * @param string $default
     *
     * @return string
     */
    public static function defaultString($real, $default = '')
    {
        return null === $real ? $default : $real;
    }

    /**
     * If not set the real, use default value.
     *
     * @param int $real
     * @param int $default
     *
     * @return int the return number
     */
    public static function defaultNumber($real, $default = 0)
    {
        if (null === $real) {
            return $default;
        }

        return (int) $real;
    }

    /**
     * Format a map to form string, like a=a%20b%20c.
     *
     * @param array|object $query
     *
     * @return string the form string
     */
    public static function toFormString($query)
    {
        if (null === $query) {
            return '';
        }

        if (\is_object($query)) {
            $query = json_decode(self::toJSONString($query), true);
        }

        return str_replace('+', '%20', http_build_query($query));
    }

    /**
     * If not set the real, use default value.
     *
     * @param array|Model $object
     *
     * @return string the return string
     */
    public static function toJSONString($object)
    {
        if (is_string($object)) {
            return $object;
        }

        if ($object instanceof Model) {
            $object = $object->toMap();
        }

        return json_encode($object);
    }

    /**
     * Check the string is empty?
     *
     * @param string $val
     *
     * @return bool if string is null or zero length, return true
     *
     * @deprecated
     */
    public static function _empty($val)
    {
        return empty($val);
    }

    /**
     * Check the string is empty?
     *
     * @param string $val
     *
     * @return bool if string is null or zero length, return true
     *
     * @deprecated
     */
    public static function emptyWithSuffix($val)
    {
        return empty($val);
    }

    /**
     * Check the string is empty?
     *
     * @param string $val
     *
     * @return bool if string is null or zero length, return true
     */
    public static function empty_($val)
    {
        return empty($val);
    }

    /**
     * Check one string equals another one?
     *
     * @param int $left
     * @param int $right
     *
     * @return bool if equals, return true
     */
    public static function equalString($left, $right)
    {
        return $left === $right;
    }

    /**
     * Check one number equals another one?
     *
     * @param int $left
     * @param int $right
     *
     * @return bool if equals, return true
     */
    public static function equalNumber($left, $right)
    {
        return $left === $right;
    }

    /**
     * Check one value is unset.
     *
     * @param mixed $value
     *
     * @return bool if unset, return true
     */
    public static function isUnset(&$value = null)
    {
        return !isset($value) || null === $value;
    }

    /**
     * Stringify the value of map.
     *
     * @param array $map
     *
     * @return array the new stringified map
     */
    public static function stringifyMapValue($map)
    {
        if (null === $map) {
            return [];
        }
        foreach ($map as &$node) {
            if (is_numeric($node)) {
                $node = (string) $node;
            } elseif (null === $node) {
                $node = '';
            } elseif (\is_bool($node)) {
                $node = true === $node ? 'true' : 'false';
            } elseif (\is_object($node)) {
                $node = json_decode(json_encode($node), true);
            }
        }

        return $map;
    }

    /**
     * Anyify the value of map.
     *
     * @param array $m
     *
     * @return array the new anyfied map
     */
    public static function anyifyMapValue($m)
    {
        return $m;
    }

    /**
     * Assert a value, if it is a boolean, return it, otherwise throws.
     *
     * @param mixed $value
     *
     * @return bool the boolean value
     */
    public static function assertAsBoolean($value)
    {
        if (\is_bool($value)) {
            return $value;
        }

        throw new \InvalidArgumentException('It is not a boolean value.');
    }

    /**
     * Assert a value, if it is a string, return it, otherwise throws.
     *
     * @param mixed $value
     *
     * @return string the string value
     */
    public static function assertAsString($value)
    {
        if (\is_string($value)) {
            return $value;
        }

        throw new \InvalidArgumentException('It is not a string value.');
    }

    private static function is_bytes($value)
    {
        if (!\is_array($value)) {
            return false;
        }
        $i = 0;
        foreach ($value as $k => $ord) {
            if ($k !== $i) {
                return false;
            }
            if (!\is_int($ord)) {
                return false;
            }
            if ($ord < 0 || $ord > 255) {
                return false;
            }
            ++$i;
        }

        return true;
    }

    /**
     * Assert a value, if it is a bytes, return it, otherwise throws.
     *
     * @param mixed $value
     *
     * @return bytes the bytes value
     */
    public static function assertAsBytes($value)
    {
        if (self::is_bytes($value)) {
            return $value;
        }

        throw new \InvalidArgumentException('It is not a bytes value.');
    }

    /**
     * Assert a value, if it is a number, return it, otherwise throws.
     *
     * @param mixed $value
     *
     * @return bool the number value
     */
    public static function assertAsNumber($value)
    {
        if (\is_numeric($value)) {
            return $value;
        }

        throw new \InvalidArgumentException('It is not a number value.');
    }

    /**
     * Assert a value, if it is a map, return it, otherwise throws.
     *
     * @param $any
     *
     * @return array the map value
     */
    public static function assertAsMap($any)
    {
        if (\is_array($any)) {
            return $any;
        }

        throw new \InvalidArgumentException('It is not a map value.');
    }

    public static function assertAsArray($any){
        if (\is_array($any)) {
            return $any;
        }

        throw new \InvalidArgumentException('It is not a array value.');
    }

    /**
     * Get user agent, if it userAgent is not null, splice it with defaultUserAgent and return, otherwise return
     * defaultUserAgent.
     *
     * @param string $userAgent
     *
     * @return string the string value
     */
    public static function getUserAgent($userAgent = '')
    {
        if (empty(self::$defaultUserAgent)) {
            self::$defaultUserAgent = sprintf('AlibabaCloud (%s; %s) PHP/%s Core/3.1 TeaDSL/1', PHP_OS, \PHP_SAPI, PHP_VERSION);
        }
        if (!empty($userAgent)) {
            return self::$defaultUserAgent . ' ' . $userAgent;
        }

        return self::$defaultUserAgent;
    }

    /**
     * If the code between 200 and 300, return true, or return false.
     *
     * @param int $code
     *
     * @return bool
     */
    public static function is2xx($code)
    {
        return $code >= 200 && $code < 300;
    }

    /**
     * If the code between 300 and 400, return true, or return false.
     *
     * @param int $code
     *
     * @return bool
     */
    public static function is3xx($code)
    {
        return $code >= 300 && $code < 400;
    }

    /**
     * If the code between 400 and 500, return true, or return false.
     *
     * @param int $code
     *
     * @return bool
     */
    public static function is4xx($code)
    {
        return $code >= 400 && $code < 500;
    }

    /**
     * If the code between 500 and 600, return true, or return false.
     *
     * @param int $code
     *
     * @return bool
     */
    public static function is5xx($code)
    {
        return $code >= 500 && $code < 600;
    }

    /**
     * Validate model.
     *
     * @param Model $model
     */
    public static function validateModel($model)
    {
        if (null !== $model) {
            $model->validate();
        }
    }

    /**
     * Model transforms to map[string]any.
     *
     * @param Model $model
     *
     * @return array
     */
    public static function toMap($model)
    {
        if (null === $model) {
            return [];
        }
        $map   = $model->toMap();
        $names = $model->getName();
        $vars  = get_object_vars($model);
        foreach ($vars as $k => $v) {
            if (false !== strpos($k, 'Shrink') && !isset($names[$k])) {
                // A field that has the suffix `Shrink` and is not a Model class property.
                $targetKey = ucfirst(substr($k, 0, \strlen($k) - 6));
                if (isset($map[$targetKey])) {
                    // $targetKey exists in $map.
                    $map[$targetKey] = $v;
                }
            }
        }

        return $map;
    }

    /**
     * Suspends the current thread for the specified number of milliseconds.
     *
     * @param int $millisecond
     */
    public static function sleep($millisecond)
    {
        usleep($millisecond * 1000);
    }

    /**
     * Transform input as array.
     *
     * @param mixed $input
     *
     * @return array
     */
    public static function toArray($input)
    {
        if (\is_array($input)) {
            foreach ($input as $k => &$v) {
                $v = self::toArray($v);
            }
        } elseif ($input instanceof Model) {
            $input = $input->toMap();
            foreach ($input as $k => &$v) {
                $v = self::toArray($v);
            }
        }

        return $input;
    }

    /**
     * Assert a value, if it is a readable, return it, otherwise throws.
     *
     * @param mixed $value
     *
     * @return Stream the readable value
     */
    public static function assertAsReadable($value)
    {
        if (\is_string($value)) {
            return new Stream(
                fopen('data://text/plain;base64,' .
                    base64_encode($value), 'r')
            );
        }
        if ($value instanceof Stream) {
            return $value;
        }

        throw new \InvalidArgumentException('It is not a stream value.');
    }
}
