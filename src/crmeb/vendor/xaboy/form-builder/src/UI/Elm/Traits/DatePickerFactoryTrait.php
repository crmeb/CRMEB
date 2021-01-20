<?php
/**
 * PHP表单生成器
 *
 * @package  FormBuilder
 * @author   xaboy <xaboy2005@qq.com>
 * @version  2.0
 * @license  MIT
 * @link     https://github.com/xaboy/form-builder
 * @document http://php.form-create.com
 */

namespace FormBuilder\UI\Elm\Traits;


use FormBuilder\UI\Elm\Components\DatePicker;

trait DatePickerFactoryTrait
{
    /**
     * 日期组件
     *
     * @param string $field
     * @param string $title
     * @param string $value
     * @param string $type
     * @return DatePicker
     */
    public static function datePicker($field, $title, $value = '', $type = DatePicker::TYPE_DATE)
    {
        $datePicker = new DatePicker($field, $title, $value);
        return $datePicker->type($type);
    }

    /**
     * 单选日期
     *
     * @param string $field
     * @param string $title
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
     * @param string $field
     * @param string $title
     * @param array $value
     * @return DatePicker
     */
    public static function dates($field, $title, array $value)
    {
        return self::datePicker($field, $title, $value, DatePicker::TYPE_DATES);
    }

    /**
     * 日期区间选择
     *
     * @param string $field
     * @param string $title
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

    /**
     * 月区间选择
     *
     * @param $field
     * @param $title
     * @param string $startDate
     * @param string $endDate
     * @return DatePicker
     */
    public static function monthRange($field, $title, $startDate = '', $endDate = '')
    {
        return self::datePicker($field, $title, [(string)$startDate, (string)$endDate], DatePicker::TYPE_MONTH_RANGE);
    }
}