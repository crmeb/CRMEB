<?php

namespace Songshenzong\Support;

/**
 * Class Time
 *
 * @package Songshenzong\Support
 */
class Time
{

    /**
     * @param int|string $begin_time
     * @param int|string $end_time
     *
     * @return array
     */
    public static function dates($begin_time, $end_time = null)
    {
        if ($end_time === null) {
            $end_time = time();
        }

        // Y-m-d to timestamp
        if (!is_int($begin_time)) {
            $begin_time = strtotime($begin_time);
        }

        // Y-m-d to timestamp
        if (!is_int($end_time)) {
            $end_time = strtotime($end_time);
        }

        $dates = [];

        for ($start = $begin_time; $start <= $end_time; $start += 86400) {
            $dates[] = date('Y-m-d', $start);
        }

        return $dates;
    }

    /**
     * Format Time.
     *
     * @param string $time_string
     *
     * @return false|string
     */
    public static function formatTime($time_string)
    {
        $time  = time() - strtotime($time_string);
        $time1 = time() - strtotime('today');
        if ($time < 60) {
            $str = '刚刚';
        } elseif ($time < 3600) {
            $min = floor($time / 60);
            $str = $min . '分钟前';
        } elseif ($time < 24 * 3600) {
            $min = floor($time / (60 * 60));
            $str = $min . '小时前';
        } elseif ($time > $time1 && $time < 7 * 24 * 3600) {
            $min = floor($time / (3600 * 24));
            $str = $min . '天前';
        } else {
            $str = date('n月j日 H:i', strtotime($time_string));
        }

        return $str;
    }
}
