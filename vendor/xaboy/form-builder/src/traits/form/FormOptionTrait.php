<?php
/**
 * FormBuilder表单生成器
 * Author: xaboy
 * Github: https://github.com/xaboy/form-builder
 */

namespace FormBuilder\traits\form;


use FormBuilder\components\Option;

/**
 * Class FormOptionTrait
 *
 * @package FormBuilder\traits\form
 */
trait FormOptionTrait
{
    /**
     * 获取选择类组件 option 类
     *
     * @param        $value
     * @param string $label
     * @param bool   $disabled
     * @return Option
     * @throws \FormBuilder\exception\FormBuilderException
     */
    public static function option($value, $label = '', $disabled = false)
    {
        return new Option($value, $label, $disabled);
    }
}