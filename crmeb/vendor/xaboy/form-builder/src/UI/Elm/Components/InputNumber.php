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
 * 数字输入框组件
 * Class InputNumber
 *
 * @method $this min(float $min) 设置计数器允许的最小值, 默认值: -Infinity
 * @method $this max(float $max) 设置计数器允许的最大值, 默认值: Infinity
 * @method $this step(float $step) 计数器步长, 默认值: 1
 * @method $this stepStrictly(float $stepStrictly) 是否只能输入 step 的倍数, 默认值: false
 * @method $this precision(float $precision) 数值精度
 * @method $this size(string $size) 计数器尺寸, 可选值: large, small
 * @method $this disabled(bool $disabled) 是否禁用计数器, 默认值: false
 * @method $this controls(bool $controls) 是否使用控制按钮, 默认值: true
 * @method $this controlsPosition(string $controlsPosition) 控制按钮位置, 可选值: right
 * @method $this name(string $name) 原生属性
 * @method $this label(string $label) 输入框关联的label文字
 * @method $this placeholder(string $placeholder) 输入框默认 placeholder
 */
class InputNumber extends FormComponent
{
    protected static $propsRule = [
        'min' => 'float',
        'max' => 'float',
        'step' => 'float',
        'stepStrictly' => 'float',
        'precision' => 'float',
        'size' => 'string',
        'disabled' => 'bool',
        'controls' => 'bool',
        'controlsPosition' => 'string',
        'name' => 'string',
        'label' => 'string',
        'placeholder' => 'string',
    ];

    public function createValidate()
    {
        return Elm::validateNum();
    }
}