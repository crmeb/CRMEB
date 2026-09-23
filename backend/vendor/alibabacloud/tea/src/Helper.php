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

        return \JSON_ERROR_NONE == json_last_error();
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public static function isBytes($value)
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
