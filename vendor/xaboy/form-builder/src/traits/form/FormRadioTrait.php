<?php
/**
 * FormBuilder表单生成器
 * Author: xaboy
 * Github: https://github.com/xaboy/form-builder
 */

namespace FormBuilder\traits\form;


use FormBuilder\components\Radio;

/**
 * Class FormRadioTrait
 *
 * @package FormBuilder\traits\form
 */
trait FormRadioTrait
{
    /**
     * 单选框组件
     *
     * @param        $field
     * @param        $title
     * @param string $value
     * @return Radio
     */
    public static function radio($field, $title, $value = '')
    {
        return new Radio($field, $title, (string)$value);
    }
}