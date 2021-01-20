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

namespace FormBuilder\UI\Iview\Traits;


use FormBuilder\UI\Iview\Components\Input;

trait InputFactoryTrait
{
    /**
     * input输入框组件
     *
     * @param string $field
     * @param string $title
     * @param string $value
     * @param string $type
     * @return Input
     */
    public static function input($field, $title, $value = '', $type = Input::TYPE_TEXT)
    {
        $input = new Input($field, $title, (string)$value);
        return $input->type($type);
    }

    /**
     * text 类型输入框
     *
     * @param string $field
     * @param string $title
     * @param string $value
     * @return Input
     */
    public static function text($field, $title, $value = '')
    {
        return self::input($field, $title, $value);
    }

    /**
     * password 类型输入框
     *
     * @param string $field
     * @param string $title
     * @param string $value
     * @return Input
     */
    public static function password($field, $title, $value = '')
    {
        return self::input($field, $title, $value, Input::TYPE_PASSWORD);
    }

    /**
     * textarea 类型输入框
     *
     * @param string $field
     * @param string $title
     * @param string $value
     * @return Input
     */
    public static function textarea($field, $title, $value = '')
    {
        return self::input($field, $title, $value, Input::TYPE_TEXTAREA);
    }

    /**
     * url 类型输入框
     *
     * @param string $field
     * @param string $title
     * @param string $value
     * @return Input
     */
    public static function url($field, $title, $value = '')
    {
        return self::input($field, $title, $value, Input::TYPE_URL);
    }

    /**
     * email 类型输入框
     *
     * @param string $field
     * @param string $title
     * @param string $value
     * @return Input
     */
    public static function email($field, $title, $value = '')
    {
        return self::input($field, $title, $value, Input::TYPE_EMAIL);
    }

    /**
     * date 类型输入框
     *
     * @param string $field
     * @param string $title
     * @param string $value
     * @return Input
     */
    public static function idate($field, $title, $value = '')
    {
        return self::input($field, $title, $value, Input::TYPE_DATE);
    }
}