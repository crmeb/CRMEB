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
 * 滑块组件
 * Class Slider
 *
 * @method $this min(float $min) 最小值
 * @method $this max(float $max) 最大值, 默认值: 100
 * @method $this disabled(bool $disabled) 是否禁用, 默认值: false
 * @method $this step(float $step) 步长, 默认值: 1
 * @method $this showInput(bool $showInput) 是否显示输入框，仅在非范围选择时有效, 默认值: false
 * @method $this showInputControls(bool $showInputControls) 在显示输入框的情况下，是否显示输入框的控制按钮, 默认值: true
 * @method $this inputSize(string $inputSize) 输入框的尺寸, 可选值: large / medium / small / mini, 默认值: small
 * @method $this showStops(bool $showStops) 是否显示间断点, 默认值: false
 * @method $this showTooltip(bool $showTooltip) 是否显示 tooltip, 默认值: true
 * @method $this range(bool $range) 是否为范围选择, 默认值: false
 * @method $this vertical(bool $vertical) 是否竖向模式, 默认值: false
 * @method $this height(string $height) Slider 高度，竖向模式时必填
 * @method $this label(string $label) 屏幕阅读器标签
 * @method $this debounce(float $debounce) 输入时的去抖延迟，毫秒，仅在show-input等于true时有效, 默认值: 300
 * @method $this tooltipClass(string $tooltipClass) tooltip 的自定义类名
 * @method $this marks(array $marks) 标记， key 的类型必须为 number 且取值在闭区间 [min, max] 内，每个标记可以单独设置样式
 *
 */
class Slider extends FormComponent
{
    protected $selectComponent = true;

    protected $defaultProps = [
        'range' => false
    ];

    protected static $propsRule = [
        'min' => 'float',
        'max' => 'float',
        'disabled' => 'bool',
        'step' => 'float',
        'showInput' => 'bool',
        'showInputControls' => 'bool',
        'inputSize' => 'string',
        'showStops' => 'bool',
        'showTooltip' => 'bool',
        'range' => 'bool',
        'vertical' => 'bool',
        'height' => 'string',
        'label' => 'string',
        'debounce' => 'float',
        'tooltipClass' => 'string',
        'marks' => 'array',
    ];

    public function createValidate()
    {
        if ($this->props['range'] == true)
            return Elm::validateArr();
        else
            return Elm::validateNum();
    }
}