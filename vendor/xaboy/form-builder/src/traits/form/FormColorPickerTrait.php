<?php
/**
 * FormBuilder表单生成器
 * Author: xaboy
 * Github: https://github.com/xaboy/form-builder
 */

namespace FormBuilder\traits\form;


use FormBuilder\components\ColorPicker;

/**
 * Class FormColorPickerTrait
 *
 * @package FormBuilder\traits\form
 */
trait FormColorPickerTrait
{
    /**
     * 颜色选择组件
     *
     * @param        $field
     * @param        $title
     * @param string $value
     * @return ColorPicker
     */
    public static function color($field, $title, $value = '')
    {
        return new ColorPicker($field, $title, (string)$value);
    }
}