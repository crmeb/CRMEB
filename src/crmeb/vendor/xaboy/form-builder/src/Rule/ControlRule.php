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

namespace FormBuilder\Rule;


use FormBuilder\Util;

trait ControlRule
{
    /**
     * 根据组件的值显示不同的组件
     *
     * @var
     */
    protected $control = [];


    /**
     * @param array $control
     * @return $this
     */
    public function control(array $control)
    {
        $this->control = $control;
        return $this;
    }

    /**
     * @param string|int|float $value
     * @param array $rule
     * @return $this
     */
    public function appendControl($value, array $rule)
    {
        $this->control[] = compact('value', 'rule');
        return $this;
    }

    /**
     * @param array $controls
     * @return $this
     */
    public function appendControls(array $controls)
    {
        $this->control = array_merge($this->control, $controls);
        return $this;
    }

    public function getControl()
    {
        return $this->control;
    }

    /**
     * @return array
     */
    public function parseControlRule()
    {
        if (!count($this->control)) return [];
        $control = [];
        foreach ($this->control as $child) {
            foreach ($child['rule'] as $k => $rule) {
                $child['rule'][$k] = Util::isComponent($rule) ? $rule->build() : $rule;
            }
            $control[] = $child;
        }

        return compact('control');
    }
}