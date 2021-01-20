<?php

namespace AlibabaCloud\Tea;

class Helper
{
    /**
     * @param string   $content
     * @param string   $prefix
     * @param string   $end
     * @param string[] $filter
     *
     * @return string|string[]
     */
    public static function findFromString($content, $prefix, $end, $filter = ['"', ' '])
    {
        $len = mb_strlen($prefix);
        $pos = mb_strpos($content, $prefix);
        if (false === $pos) {
            return '';
        }
        $pos_end = mb_strpos($content, $end, $pos);
        $str     = mb_substr($content, $pos + $len, $pos_end - $pos - $len);

        return str_replace($filter, '', $str);
    }

    /**
     * @param string $str
     *
     * @return bool
     */
    public static function isJson($str)
    {
        json_decode($str);

        return JSON_ERROR_NONE == json_last_error();
    }

    /**
     * @return array
     */
    public static function merge(array $arrays)
    {
        $result = [];
        foreach ($arrays as $array) {
            foreach ($array as $key => $value) {
                if (\is_int($key)) {
                    $result[] = $value;

                    continue;
                }

                if (isset($result[$key]) && \is_array($result[$key])) {
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
}
