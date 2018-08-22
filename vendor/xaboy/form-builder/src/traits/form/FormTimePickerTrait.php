<?php
/**
 * FormBuilder表单生成器
 * Author: xaboy
 * Github: https://github.com/xaboy/form-builder
 */

namespace FormBuilder\traits\form;


use FormBuilder\components\TimePicker;

/**
 * Class FormTimePickerTrait
 * @package FormBuilder\traits\form
 */
trait FormTimePickerTrait
{
    /**
     * @param $field
     * @param $title
     * @param string $value
     * @param string $type
     * @return TimePicker
     */
    public static function timePicker($field, $title, $value = '', $type = TimePicker::TYPE_TIME)
    {
        return (new TimePicker($field, $title, $value))->type($type);
    }

    /**
     * @param $field
     * @param $title
     * @param string $value
     * @return TimePicker
     */
    public static function time($field, $title, $value = '')
    {
        return self::timePicker($field, $title, (string)$value);
    }

    /**
     * @param $field
     * @param $title
     * @param string $startTime
     * @param string $endTime
     * @return TimePicker
     */
    public static function timeRange($field, $title, $startTime = '', $endTime = '')
    {
        return self::timePicker($field, $title, [(string)$startTime, (string)$endTime], TimePicker::TYPE_TIME_RANGE);
    }
}