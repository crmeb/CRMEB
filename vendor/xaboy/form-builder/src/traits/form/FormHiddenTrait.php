<?php
/**
 * FormBuilder表单生成器
 * Author: xaboy
 * Github: https://github.com/xaboy/form-builder
 */

namespace FormBuilder\traits\form;


use FormBuilder\components\Hidden;

/**
 * Class FormHiddenTrait
 * @package FormBuilder\traits\form
 */
trait FormHiddenTrait
{
    /**
     * @param $field
     * @param $value
     * @return Hidden
     */
    public static function hidden($field, $value)
    {
        return new Hidden($field, $value);
    }
}