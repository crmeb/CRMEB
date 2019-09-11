<?php
/**
 * FormBuilder表单生成器
 * Author: xaboy
 * Github: https://github.com/xaboy/form-builder
 */

namespace FormBuilder\components;


use FormBuilder\interfaces\FormComponentInterFace;
use FormBuilder\traits\component\CallPropsTrait;

/**
 * form表单样式
 * Class FormStyle
 *
 * @package FormBuilder\components
 * @method $this inline(Boolean $bool) 是否开启行内表单模式
 * @method $this labelPosition(String $labelPosition) 表单域标签的位置，可选值为 left、right、top
 * @method $this labelWidth(Number $labelWidth) 表单域标签的宽度，所有的 FormItem 都会继承 Form 组件的 label-width 的值
 * @method $this showMessage(Boolean $bool) 是否显示校验错误信息
 */
class FormStyle implements FormComponentInterFace
{
    use CallPropsTrait;

    /**
     * @var array
     */
    protected $props;

    /**
     * @var array
     */
    protected static $propsRule = [
        'inline' => 'boolean',
        'labelPosition' => 'string',
        'labelWidth' => 'number',
        'showMessage' => 'boolean',
    ];

    /**
     * FormStyle constructor.
     *
     * @param bool   $inline
     * @param string $labelPosition
     * @param int    $labelWidth
     * @param bool   $showMessage
     * @param string $autocomplete
     */
    public function __construct($inline = false, $labelPosition = 'right', $labelWidth = 125, $showMessage = true, $autocomplete = 'off')
    {
        $this->props = compact('inline', 'labelPosition', 'labelWidth', 'showMessage');
        $this->autocomplete($autocomplete);
    }

    /**
     * 原生的 autocomplete 属性，可选值为 true = off 或 false = on
     *
     * @param bool $bool
     * @return $this
     */
    public function autocomplete($bool = false)
    {
        $this->props['autocomplete'] = $bool === false ? 'on' : 'off';
        return $this;
    }

    /**
     * @return array
     */
    public function build()
    {
        return $this->props;
    }

}