<?php
/**
 * FormBuilder表单生成器
 * Author: xaboy
 * Github: https://github.com/xaboy/form-builder
 */

namespace FormBuilder\traits\form;


use FormBuilder\components\Rate;

/**
 * Class FormRateTrait
 *
 * @package FormBuilder\traits\form
 */
trait FormRateTrait
{
    /**
     * 评分选择组件
     *
     * @param        $field
     * @param        $title
     * @param  float $value
     * @return Rate
     */
    public static function rate($field, $title, $value = 0.0)
    {
        return new Rate($field, $title, (float)$value);
    }
}