<?php

namespace Fastknife\Utils;

class MathUtils
{
    /**
     * 获取平均值
     * @param array $array
     * @return int
     */
    public static function avg(array $array): int
    {
        return intval(array_sum($array) / count($array));
    }
}