<?php
/**
 * FormBuilder表单生成器
 * Author: xaboy
 * Github: https://github.com/xaboy/form-builder
 */

namespace FormBuilder\components;


use FormBuilder\FormComponentDriver;

/**
 * 数字输入框组件
 * Class InputNumber
 *
 * @package FormBuilder\components
 * @method $this max(float $max) 最大值
 * @method $this min(float $min) 最小值
 * @method $this step(float $step) 每次改变的步伐，可以是小数
 * @method $this size(String $size) 输入框尺寸，可选值为large、small、default或者不填
 * @method $this disabled(Boolean $bool) 设置禁用状态，默认为false
 * @method $this placeholder(String $placeholder) 占位文本
 * @method $this readonly(Boolean $bool) 是否设置为只读，默认为false
 * @method $this editable(Boolean $bool) 是否可编辑，默认为true
 * @method $this precision(int $precision) 数值精度
 */
class InputNumber extends FormComponentDriver
{
    /**
     * @var string
     */
    protected $name = 'inputNumber';

    /**
     * @var array
     */
    protected static $propsRule = [
        'max' => 'float',
        'min' => 'float',
        'step' => 'float',
        'disabled' => 'boolean',
        'size' => 'string',
        'placeholder' => 'string',
        'readonly' => 'boolean',
        'editable' => 'boolean',
        'precision' => 'int',
    ];

    /**
     *
     */
    protected function init()
    {
        $this->placeholder($this->getPlaceHolder());
    }

    protected function getPlaceHolder($pre = '请输入')
    {
        return parent::getPlaceHolder($pre);
    }

    public function getValidateHandler()
    {
        return Validate::num(Validate::TRIGGER_BLUR);
    }

    /**
     * @return array
     */
    public function build()
    {
        return [
            'type' => $this->name,
            'field' => $this->field,
            'title' => $this->title,
            'value' => $this->value === '' ? '' : (float)$this->value,
            'props' => (object)$this->props,
            'validate' => $this->validate,
            'col' => $this->col
        ];
    }

}