<?php

namespace Songshenzong\Support;

use Closure;

/**
 * Class Env
 *
 * @package Songshenzong\Support
 */
class Env
{

    /**
     * Gets the value of an environment variable.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public static function env($key, $default = null)
    {
        $value = getenv($key);

        if ($value === false) {
            return self::value($default);
        }

        if (self::envSubstr($value)) {
            return substr($value, 1, -1);
        }

        return self::envConversion($value);
    }

    /**
     * @param string $key
     *
     * @return bool|mixed
     */
    public static function envNotEmpty($key)
    {
        $value = self::env($key, false);
        if ($value) {
            return $value;
        }

        return false;
    }

    /**
     * Return the default value of the given value.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public static function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }

    /**
     * @param $value
     *
     * @return bool
     */
    public static function envSubstr($value)
    {
        return ($valueLength = strlen($value)) > 1
               && strpos($value, '"') === 0
               && $value[$valueLength - 1] === '"';
    }

    /**
     * @param $value
     *
     * @return bool|string|null
     */
    public static function envConversion($value)
    {
        $key = strtolower($value);

        if ($key === 'null' || $key === '(null)') {
            return null;
        }

        $list = [
            'true'    => true,
            '(true)'  => true,
            'false'   => false,
            '(false)' => false,
            'empty'   => '',
            '(empty)' => '',
        ];

        return isset($list[$key]) ? $list[$key] : $value;
    }
}
