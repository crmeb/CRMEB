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


use FormBuilder\UI\Iview\Components\ColorPicker;

trait ColorPickerFactoryTrait
{
    /**
     * 颜色选择组件
     *
     * @param string $field
     * @param string $title
     * @param string $value
     * @return ColorPicker
     */
    public static function color($field, $title, $value = '')
    {
        return new ColorPicker($field, $title, (string)$value);
    }
}