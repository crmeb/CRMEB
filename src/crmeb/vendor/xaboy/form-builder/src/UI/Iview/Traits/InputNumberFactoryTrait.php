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


use FormBuilder\UI\Iview\Components\InputNumber;

trait InputNumberFactoryTrait
{
    /**
     * 数字输入框组件
     *
     * @param string $field
     * @param string $title
     * @param null|number $value
     * @return InputNumber
     */
    public static function number($field, $title, $value = null)
    {
        return new InputNumber($field, $title, $value);
    }
}