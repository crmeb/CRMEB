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

use FormBuilder\UI\Iview\Validate as IViewValidate;

trait ValidateFactoryTrait
{
    public static function validateStr($trigger = IViewValidate::TRIGGER_CHANGE)
    {
        return new IViewValidate(IViewValidate::TYPE_STRING, $trigger);
    }

    public static function validateArr($trigger = IViewValidate::TRIGGER_CHANGE)
    {
        return new IViewValidate(IViewValidate::TYPE_ARRAY, $trigger);
    }

    public static function validateNum($trigger = IViewValidate::TRIGGER_CHANGE)
    {
        return new IViewValidate(IViewValidate::TYPE_NUMBER, $trigger);
    }

    public static function validateDate($trigger = IViewValidate::TRIGGER_CHANGE)
    {
        return new IViewValidate(IViewValidate::TYPE_DATE, $trigger);
    }

    public static function validateInt($trigger = IViewValidate::TRIGGER_CHANGE)
    {
        return new IViewValidate(IViewValidate::TYPE_INTEGER, $trigger);
    }

    public static function validateFloat($trigger = IViewValidate::TRIGGER_CHANGE)
    {
        return new IViewValidate(IViewValidate::TYPE_FLOAT, $trigger);
    }

    public static function validateObject($trigger = IViewValidate::TRIGGER_CHANGE)
    {
        return new IViewValidate(IViewValidate::TYPE_OBJECT, $trigger);
    }

    public static function validateEmail($trigger = IViewValidate::TRIGGER_CHANGE)
    {
        return new IViewValidate(IViewValidate::TYPE_EMAIL, $trigger);
    }

    public static function validateEnum($trigger = IViewValidate::TRIGGER_CHANGE)
    {
        return new IViewValidate(IViewValidate::TYPE_ENUM, $trigger);
    }

    public static function validateUrl($trigger = IViewValidate::TRIGGER_CHANGE)
    {
        return new IViewValidate(IViewValidate::TYPE_URL, $trigger);
    }

    public static function validateHex($trigger = IViewValidate::TRIGGER_CHANGE)
    {
        return new IViewValidate(IViewValidate::TYPE_HEX, $trigger);
    }
}