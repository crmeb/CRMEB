<?php
/**
 * FormBuilder表单生成器
 * Author: xaboy
 * Github: https://github.com/xaboy/form-builder
 */

namespace FormBuilder\traits\form;


use FormBuilder\components\InputNumber;

/**
 * Class FormInputNumberTrait
 *
 * @package FormBuilder\traits\form
 */
trait FormInputNumberTrait
{

    /**
     * 数字输入框组件
     *
     * @param      $field
     * @param      $title
     * @param null $value
     * @return InputNumber
     */
    public static function number($field, $title, $value = null)
    {
        return new InputNumber($field, $title, $value);
    }
}