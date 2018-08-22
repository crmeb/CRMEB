<?php
/**
 * FormBuilder表单生成器
 * Author: xaboy
 * Github: https://github.com/xaboy/form-builder
 */

namespace FormBuilder\traits\form;


use FormBuilder\components\DatePicker;

/**
 * Class FormDatePickerTrait
 * @package FormBuilder\traits\form
 */
trait FormDatePickerTrait
{
    /**
     * @param $field
     * @param $title
     * @param string $value
     * @param string $type
     * @return DatePicker
     */
    public static function datePicker($field, $title, $value = '', $type = DatePicker::TYPE_DATE)
    {
        $datePicker = new DatePicker($field, $title, $value);
        $datePicker->type($type);
        return $datePicker;
    }

    /**
     * @param $field
     * @param $title
     * @param string $value
     * @return DatePicker
     */
    public static function date($field, $title, $value = '')
    {
        return self::datePicker($field, $title, (string)$value, DatePicker::TYPE_DATE);
    }

    /**
     * @param $field
     * @param $title
     * @param string $startDate
     * @param string $endDate
     * @return DatePicker
     */
    public static function dateRange($field, $title, $startDate = '', $endDate = '')
    {
        return self::datePicker($field, $title, [(string)$startDate, (string)$endDate], DatePicker::TYPE_DATE_RANGE);
    }

    /**
     * @param $field
     * @param $title
     * @param string $value
     * @return DatePicker
     */
    public static function dateTime($field, $title, $value = '')
    {
        return self::datePicker($field, $title, (string)$value, DatePicker::TYPE_DATE_TIME);
    }

    /**
     * @param $field
     * @param $title
     * @param string $startDate
     * @param string $endDate
     * @return DatePicker
     */
    public static function dateTimeRange($field, $title, $startDate = '', $endDate = '')
    {
        return self::datePicker($field, $title, [(string)$startDate, (string)$endDate], DatePicker::TYPE_DATE_TIME_RANGE);
    }

    /**
     * @param $field
     * @param $title
     * @param string $value
     * @return DatePicker
     */
    public static function year($field, $title, $value = '')
    {
        return self::datePicker($field, $title, (string)$value, DatePicker::TYPE_YEAR);
    }

    /**
     * @param $field
     * @param $title
     * @param string $value
     * @return DatePicker
     */
    public static function month($field, $title, $value = '')
    {
        return self::datePicker($field, $title, (string)$value, DatePicker::TYPE_MONTH);
    }
}