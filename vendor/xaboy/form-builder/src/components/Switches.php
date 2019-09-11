<?php
/**
 * FormBuilder表单生成器
 * Author: xaboy
 * Github: https://github.com/xaboy/form-builder
 */

namespace FormBuilder\components;

use FormBuilder\FormComponentDriver;

/**
 * 开关组件
 * Class Switches
 *
 * @package FormBuilder\components
 * @method $this size(String $size) 开关的尺寸，可选值为large、small、default或者不写。建议开关如果使用了2个汉字的文字，使用 large。
 * @method $this disabled(Boolean $bool) 禁用开关, 默认为false
 * @method $this trueValue(String $value) 选中时的值，默认为1
 * @method $this falseValue(String $value) 没有选中时的值，默认为0
 */
class Switches extends FormComponentDriver
{
    /**
     * @var string
     */
    protected $name = 'switch';

    /**
     * @var array
     */
    protected $slot = [];

    /**
     * @var array
     */
    protected $props = [
        'trueValue' => '1',
        'falseValue' => '0'
    ];

    /**
     * @var array
     */
    protected static $propsRule = [
        'size' => 'string',
        'disabled' => 'boolean',
        'trueValue' => 'string',
        'falseValue' => 'string'
    ];

    /**
     * 自定义显示打开时的内容
     *
     * @param $open
     * @return $this
     */
    public function openStr($open)
    {
        $this->slot['open'] = (string)$open;
        return $this;
    }

    /**
     * 自定义显示关闭时的内容
     *
     * @param $close
     * @return $this
     */
    public function closeStr($close)
    {
        $this->slot['close'] = (string)$close;
        return $this;
    }

    public function getValidateHandler()
    {
        return Validate::str();
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
            'value' => $this->value,
            'props' => (object)$this->props,
            'slot' => (object)$this->slot,
            'validate' => $this->validate,
            'col' => $this->col
        ];
    }

}