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
 *
 * @package FormBuilder\traits\form
 */
trait FormDatePickerTrait
{
    /**
     * 日期组件
     *
     * @param        $field
     * @param        $title
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
     * 单选日期
     *
     * @param        $field
     * @param        $title
     * @param string $value
     * @return DatePicker
     */
    public static function date($field, $title, $value = '')
    {
        return self::datePicker($field, $title, (string)$value, DatePicker::TYPE_DATE);
    }

    /**
     * 多选日期
     *
     * @param       $field
     * @param       $title
     * @param array $value
     * @return DatePicker
     */
    public static function dateMultiple($field, $title, array $value)
    {
        $date = self::datePicker($field, $title, $value, DatePicker::TYPE_DATE);
        return $date->multiple();
    }

    /**
     * 日期区间选择
     *
     * @param        $field
     * @param        $title
     * @param string $startDate
     * @param string $endDate
     * @return DatePicker
     */
    public static function dateRange($field, $title, $startDate = '', $endDate = '')
    {
        return self::datePicker($field, $title, [(string)$startDate, (string)$endDate], DatePicker::TYPE_DATE_RANGE);
    }

    /**
     * 单选日期时间
     *
     * @param        $field
     * @param        $title
     * @param string $value
     * @return DatePicker
     */
    public static function dateTime($field, $title, $value = '')
    {
        return self::datePicker($field, $title, (string)$value, DatePicker::TYPE_DATE_TIME);
    }

    /**
     * 日期时间区间选择
     *
     * @param        $field
     * @param        $title
     * @param string $startDate
     * @param string $endDate
     * @return DatePicker
     */
    public static function dateTimeRange($field, $title, $startDate = '', $endDate = '')
    {
        return self::datePicker($field, $title, [(string)$startDate, (string)$endDate], DatePicker::TYPE_DATE_TIME_RANGE);
    }

    /**
     * 选择年
     *
     * @param        $field
     * @param        $title
     * @param string $value
     * @return DatePicker
     */
    public static function year($field, $title, $value = '')
    {
        return self::datePicker($field, $title, (string)$value, DatePicker::TYPE_YEAR);
    }

    /**
     * 选择月
     *
     * @param        $field
     * @param        $title
     * @param string $value
     * @return DatePicker
     */
    public static function month($field, $title, $value = '')
    {
        return self::datePicker($field, $title, (string)$value, DatePicker::TYPE_MONTH);
    }
}