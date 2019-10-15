<?php
/**
 * FormBuilder表单生成器
 * Author: xaboy
 * Github: https://github.com/xaboy/form-builder
 */

namespace FormBuilder\traits\form;


use FormBuilder\components\Switches;

/**
 * Class FormSwitchesTrait
 *
 * @package FormBuilder\traits\form
 */
trait FormSwitchesTrait
{
    /**
     * 开关组件
     *
     * @param        $field
     * @param        $title
     * @param string $value
     * @return Switches
     */
    public static function switches($field, $title, $value = '0')
    {
        return new Switches($field, $title, $value);
    }
}