<?php
/**
 * FormBuilder表单生成器
 * Author: xaboy
 * Github: https://github.com/xaboy/form-builder
 */

namespace FormBuilder\components;


use FormBuilder\FormComponentDriver;
use FormBuilder\Helper;

/**
 * 滑块组件
 * Class Slider
 *
 * @package FormBuilder\components
 * @method $this min(float $min) 最小值, 默认 0
 * @method $this max(float $max) 最大值, 默认 100
 * @method $this step(float $step) 步长，取值建议能被（max - min）整除, 默认 1
 * @method $this disabled(Boolean $bool) 是否禁用滑块, 默认 false
 * @method $this range(Boolean $bool) 是否开启双滑块模式, 默认
 * @method $this showInput(Boolean $bool) 是否显示数字输入框，仅在单滑块模式下有效, 默认 false
 * @method $this showStops(Boolean $bool) 是否显示间断点，建议在 step 不密集时使用, 默认 false
 * @method $this showTip(String $tip) 提示的显示控制，可选值为 hover（悬停，默认）、always（总是可见）、never（不可见）
 * @method $this inputSize(String $size) 数字输入框的尺寸，可选值为large、small、default或者不填，仅在开启 show-input 时有效
 *
 */
class Slider extends FormComponentDriver
{
    /**
     * @var string
     */
    protected $name = 'slider';

    /**
     * @var array
     */
    protected $props = [
        'range' => false
    ];

    /**
     * @var array
     */
    protected static $propsRule = [
        'min' => 'float',
        'max' => 'float',
        'step' => 'float',
        'disabled' => 'boolean',
        'range' => 'boolean',
        'showInput' => 'boolean',
        'showStops' => 'boolean',
        'showTip' => 'string',
        'inputSize' => 'string',
    ];

    /**
     * @param $value
     * @return $this
     */
    public function value($value)
    {
        $this->value = $value;
        return $this;
    }

    public function getValidateHandler()
    {
        if ($this->props['range'] == true)
            return Validate::arr();
        else
            return Validate::num();
    }

    /**
     * @return array
     */
    public function build()
    {
        $value = $this->value;
        if ($this->props['range'] == true) {
            $value = is_array($value) ? $value : [0, (int)$value];
        } else {
            $value = (int)(is_array($value)
                ? (isset($value[0])
                    ? $value[0]
                    : 0)
                : $value);
        }

        return [
            'type' => $this->name,
            'field' => $this->field,
            'title' => $this->title,
            'value' => $value,
            'props' => (object)$this->props,
            'validate' => $this->validate,
            'col' => $this->col
        ];
    }
}