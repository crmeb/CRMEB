<?php
/**
 * FormBuilder表单生成器
 * Author: xaboy
 * Github: https://github.com/xaboy/form-builder
 */

namespace FormBuilder\traits\form;


use FormBuilder\components\Validate;

trait FormValidateTrait
{
    /**
     * string 类型验证器
     *
     * @return Validate
     */
    public static function validateStr()
    {
        return Validate::str();
    }

    /**
     * input 组件验证器
     *
     * @return Validate
     */
    public static function validateInput()
    {
        return Validate::str(Validate::TRIGGER_BLUR);
    }

    /**
     * array 类型验证器
     *
     * @return Validate
     */
    public static function validateArr()
    {
        return Validate::arr();
    }

    /**
     * number 类型验证器
     *
     * @return Validate
     */
    public static function validateNum()
    {
        return Validate::num();
    }

    /**
     * inputNumber 组件验证器
     *
     * @return Validate
     */
    public static function validateNumInput()
    {
        return Validate::num(Validate::TRIGGER_BLUR);
    }

    /**
     * date 类型验证器
     *
     * @return Validate
     */
    public static function validateDate()
    {
        return Validate::date();
    }

    /**
     * frame 组件验证器
     *
     * @return Validate
     */
    public static function validateFrame()
    {
        return self::ValidateArr();
    }
}