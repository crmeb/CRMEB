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


use FormBuilder\Exception\FormBuilderException;

trait CallPropsRule
{
    /**
     * 设置组件属性
     *
     * @param $name
     * @param $arguments
     * @return $this
     * @throws FormBuilderException
     */
    public function __call($name, $arguments)
    {
        if (isset(static::$propsRule[$name])) {
            if (!isset($arguments[0])) return isset($this->props[$name]) ? $this->props[$name] : null;
            $value = $arguments[0];
            if (is_array(static::$propsRule[$name])) {
                settype($value, static::$propsRule[$name][0]);
                $name = static::$propsRule[$name][1];
            } else if (static::$propsRule[$name]) {
                settype($value, static::$propsRule[$name]);
            }
            $this->props[$name] = $value;
            return $this;
        } else {
            throw new FormBuilderException($name . '方法不存在');
        }
    }
}