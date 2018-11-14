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
    public static function validateStr()
    {
        return Validate::str();
    }

    public static function validateInput()
    {
        return Validate::str(Validate::TRIGGER_BLUR);
    }

    public static function validateArr()
    {
        return Validate::arr();
    }

    public static function validateNum()
    {
        return Validate::num();
    }

    public static function validateNumInput()
    {
        return Validate::num(Validate::TRIGGER_BLUR);
    }

    public static function validateDate()
    {
        return Validate::date();
    }

    public static function validateFrame()
    {
        return self::ValidateArr();
    }
}