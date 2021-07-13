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

use FormBuilder\UI\Elm\Validate;

trait ValidateFactoryTrait
{
    public static function validateStr($trigger = Validate::TRIGGER_CHANGE)
    {
        return new Validate(Validate::TYPE_STRING, $trigger);
    }

    public static function validateArr($trigger = Validate::TRIGGER_CHANGE)
    {
        return new Validate(Validate::TYPE_ARRAY, $trigger);
    }

    public static function validateNum($trigger = Validate::TRIGGER_CHANGE)
    {
        return new Validate(Validate::TYPE_NUMBER, $trigger);
    }

    public static function validateDate($trigger = Validate::TRIGGER_CHANGE)
    {
        return new Validate(Validate::TYPE_DATE, $trigger);
    }

    public static function validateInt($trigger = Validate::TRIGGER_CHANGE)
    {
        return new Validate(Validate::TYPE_INTEGER, $trigger);
    }

    public static function validateFloat($trigger = Validate::TRIGGER_CHANGE)
    {
        return new Validate(Validate::TYPE_FLOAT, $trigger);
    }

    public static function validateObject($trigger = Validate::TRIGGER_CHANGE)
    {
        return new Validate(Validate::TYPE_OBJECT, $trigger);
    }

    public static function validateEmail($trigger = Validate::TRIGGER_CHANGE)
    {
        return new Validate(Validate::TYPE_EMAIL, $trigger);
    }

    public static function validateEnum($trigger = Validate::TRIGGER_CHANGE)
    {
        return new Validate(Validate::TYPE_ENUM, $trigger);
    }

    public static function validateUrl($trigger = Validate::TRIGGER_CHANGE)
    {
        return new Validate(Validate::TYPE_URL, $trigger);
    }

    public static function validateHex($trigger = Validate::TRIGGER_CHANGE)
    {
        return new Validate(Validate::TYPE_HEX, $trigger);
    }
}