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

namespace FormBuilder\UI\Elm\Components;


use FormBuilder\Driver\FormComponent;
use FormBuilder\Factory\Elm;

/**
 * 颜色选择器组件
 * Class ColorPicker
 *
 * @method $this disabled(bool $disabled) 是否禁用, 默认值: false
 * @method $this size(string $size) 尺寸, 默认值: medium / small / mini
 * @method $this showAlpha(bool $showAlpha) 是否支持透明度选择, 默认值: false
 * @method $this colorFormat(string $colorFormat) 的格式, 可选值: hsl / hsv / hex / rgb, 默认值: hex（show-alpha 为 false）/ rgb（show-alpha 为 true）
 * @method $this popperClass(string $popperClass) ColorPicker 下拉框的类名
 * @method $this predefine(array $predefine) 预定义颜色
 */
class ColorPicker extends FormComponent
{
    protected $selectComponent = true;

    protected static $propsRule = [
        'disabled' => 'bool',
        'size' => 'string',
        'showAlpha' => 'bool',
        'colorFormat' => 'string',
        'popperClass' => 'string',
        'predefine' => 'array',
    ];

    public function createValidate()
    {
        return Elm::validateStr();
    }
}