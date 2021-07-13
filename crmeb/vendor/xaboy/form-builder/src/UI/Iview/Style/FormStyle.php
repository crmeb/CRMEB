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

namespace FormBuilder\UI\Iview\Style;


use FormBuilder\Contract\StyleInterface;

/**
 * form表单样式
 * Class FormStyle
 */
class FormStyle implements StyleInterface
{
    /**
     * @var array
     */
    protected $rule;

    /**
     * FormStyle constructor.
     * @param array $rule
     */
    public function __construct(array $rule = [])
    {
        $this->rule = $rule;
    }

    /**
     * 是否开启行内表单模式
     *
     * @param bool $bool
     * @return $this
     */
    public function inline($bool)
    {
        $this->rule['inline'] = !!$bool;
        return $this;
    }

    /**
     * 设置表单 class
     * @param $class
     * @return $this
     */
    public function className($class)
    {
        $this->rule['className'] = $class;
        return $this;
    }

    /**
     * 表单域标签的位置，可选值为 left、right、top
     *
     * @param string $position
     * @return $this
     */
    public function labelPosition($position)
    {
        $this->rule['labelPosition'] = $position;
        return $this;
    }

    /**
     * 表单域标签的宽度，所有的 FormItem 都会继承 Form 组件的 label-width 的值
     *
     * @param $labelWidth
     * @return $this
     */
    public function labelWidth($labelWidth)
    {
        $this->rule['labelWidth'] = $labelWidth;
        return $this;
    }

    /**
     * 是否显示校验错误信息
     *
     * @param $showMessage
     * @return $this
     */
    public function showMessage($showMessage)
    {
        $this->rule['showMessage'] = !!$showMessage;
        return $this;
    }

    /**
     * 原生的 autocomplete 属性，可选值为 true = off 或 false = on
     *
     * @param bool $bool
     * @return $this
     */
    public function autocomplete($bool = false)
    {
        $this->rule['autocomplete'] = $bool === false ? 'on' : 'off';
        return $this;
    }

    /**
     * @return object
     */
    public function getStyle()
    {
        return (object)$this->rule;
    }

}