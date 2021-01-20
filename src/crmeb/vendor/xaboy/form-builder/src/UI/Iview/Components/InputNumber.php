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
 * 数字输入框组件
 * Class InputNumber
 *
 * @method $this max(float $max) 最大值
 * @method $this min(float $min) 最小值
 * @method $this step(float $step) 每次改变的步伐，可以是小数
 * @method $this size(string $size) 输入框尺寸，可选值为large、small、default或者不填
 * @method $this disabled(bool $bool) 设置禁用状态，默认为false
 * @method $this placeholder(string $placeholder) 占位文本
 * @method $this readonly(bool $bool) 是否设置为只读，默认为false
 * @method $this editable(bool $bool) 是否可编辑，默认为true
 * @method $this precision(int $precision) 数值精度
 */
class InputNumber extends FormComponent
{
    protected static $propsRule = [
        'max' => 'float',
        'min' => 'float',
        'step' => 'float',
        'disabled' => 'bool',
        'size' => 'string',
        'placeholder' => 'string',
        'readonly' => 'bool',
        'editable' => 'bool',
        'precision' => 'int',
    ];

    public function createValidate()
    {
        return Iview::validateNum();
    }
}