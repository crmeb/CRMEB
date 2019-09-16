<?php
/**
 * FormBuilder表单生成器
 * Author: xaboy
 * Github: https://github.com/xaboy/form-builder
 */

namespace FormBuilder\traits\component;


use FormBuilder\exception\FormBuilderException;
use FormBuilder\Helper;

/**
 * Class CallPropsTrait
 *
 * @package FormBuilder\traits\component
 */
trait CallPropsTrait
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
            if (static::$propsRule[$name] == '') {
                $this->props[$name] = $arguments[0];
            } else if (is_array(static::$propsRule[$name])) {
                $this->props[static::$propsRule[$name][1]] = Helper::toType(
                    $arguments[0],
                    static::$propsRule[$name][0]
                );
            } else {
                $this->props[$name] = Helper::toType(
                    $arguments[0],
                    static::$propsRule[$name]
                );
            }
            return $this;
        } else {
            throw new FormBuilderException($name . '方法不存在');
        }
    }
}