<?php
/**
 * FormBuilder表单生成器
 * Author: xaboy
 * Github: https://github.com/xaboy/form-builder
 */

namespace FormBuilder\traits\form;

use FormBuilder\components\Checkbox;

/**
 * Class FormCheckBoxTrait
 *
 * @package FormBuilder\traits\form
 */
trait FormCheckBoxTrait
{
    /**
     * 多选框组件
     *
     * @param       $field
     * @param       $title
     * @param array $value
     * @return Checkbox
     */
    public static function checkbox($field, $title, array $value = [])
    {
        return new Checkbox($field, $title, $value);
    }
}