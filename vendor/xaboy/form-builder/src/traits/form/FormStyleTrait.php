<?php
/**
 * FormBuilder表单生成器
 * Author: xaboy
 * Github: https://github.com/xaboy/form-builder
 */

namespace FormBuilder\traits\form;


use FormBuilder\components\Col;
use FormBuilder\components\FormStyle;
use FormBuilder\components\Row;

/**
 * Class FormStyleTrait
 *
 * @package FormBuilder\traits\form
 */
trait FormStyleTrait
{

    /**
     * 组件布局规则类
     *
     * @param int $span
     * @return Col
     */
    public static function col($span = 24)
    {
        return new Col($span);
    }

    /**
     * 表格布局规则类
     *
     * @param int    $gutter
     * @param string $type
     * @param string $align
     * @param string $justify
     * @param string $className
     * @return Row
     */
    public static function row($gutter = 0, $type = '', $align = '', $justify = '', $className = '')
    {
        return new Row($gutter, $type, $align, $justify, $className);
    }

    /**
     * 表格样式类
     *
     * @param bool   $inline
     * @param string $labelPosition
     * @param int    $labelWidth
     * @param bool   $showMessage
     * @param string $autocomplete
     * @return FormStyle
     */
    public static function style($inline = false, $labelPosition = 'right', $labelWidth = 125, $showMessage = true, $autocomplete = 'off')
    {
        return new FormStyle($inline, $labelPosition, $labelWidth, $showMessage, $autocomplete);
    }
}