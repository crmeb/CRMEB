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

namespace FormBuilder\UI\Iview\Components;


use FormBuilder\Driver\FormComponent;
use FormBuilder\Factory\Iview;

/**
 * 滑块组件
 * Class Slider
 *
 * @method $this min(float $min) 最小值, 默认 0
 * @method $this max(float $max) 最大值, 默认 100
 * @method $this step(float $step) 步长，取值建议能被（max - min）整除, 默认 1
 * @method $this disabled(bool $bool) 是否禁用滑块, 默认 false
 * @method $this range(bool $bool) 是否开启双滑块模式, 默认
 * @method $this showInput(bool $bool) 是否显示数字输入框，仅在单滑块模式下有效, 默认 false
 * @method $this showStops(bool $bool) 是否显示间断点，建议在 step 不密集时使用, 默认 false
 * @method $this showTip(string $tip) 提示的显示控制，可选值为 hover（悬停，默认）、always（总是可见）、never（不可见）
 * @method $this inputSize(string $size) 数字输入框的尺寸，可选值为large、small、default或者不填，仅在开启 show-input 时有效
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
        'step' => 'float',
        'disabled' => 'bool',
        'range' => 'bool',
        'showInput' => 'bool',
        'showStops' => 'bool',
        'showTip' => 'string',
        'inputSize' => 'string',
    ];

    public function createValidate()
    {
        if ($this->props['range'] == true)
            return Iview::validateArr();
        else
            return Iview::validateNum();
    }
}