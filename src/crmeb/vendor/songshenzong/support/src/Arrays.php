<?php

namespace Songshenzong\Support;

use InvalidArgumentException;

/**
 * Class Arrays
 *
 * @package Songshenzong\Support
 */
class Arrays
{
    /**
     * @param array $arrays
     *
     * @return array
     */
    public static function merge(array $arrays)
    {
        $result = [];
        foreach ($arrays as $array) {
            foreach ($array as $key => $value) {
                if (is_int($key)) {
                    $result[] = $value;
                    continue;
                }

                if (isset($result[$key]) && is_array($result[$key])) {
                    $result[$key] = self::merge(
                        [$result[$key], $value]
                    );
                    continue;
                }

                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Returns item from array or $default if item is not set.
     *
     * @param array            $array
     * @param string|int|array $key one or more keys
     * @param null             $default
     *
     * @return mixed
     */
    public static function get(array $array, $key, $default = null)
    {
        foreach (is_array($key) ? $key : [$key] as $k) {
            if (is_array($array) && array_key_exists($k, $array)) {
                $array = $array[$k];
            } else {
                if (func_num_args() < 3) {
                    throw new InvalidArgumentException("Missing item '$k'.");
                }

                return $default;
            }
        }

        return $array;
    }

    /**
     * @param array $system_version
     */
    public static function versionCompare(array &$system_version)
    {
        for ($i = 0; $i < count($system_version) - 1; $i++) {
            for ($j = 0; $j < count($system_version) - 1 - $i; $j++) {
                if (version_compare($system_version[$j], $system_version[$j + 1], '<')) {
                    $tmp                    = $system_version[$j];
                    $system_version[$j]     = $system_version[$j + 1];
                    $system_version[$j + 1] = $tmp;
                }
            }
        }
    }
}
