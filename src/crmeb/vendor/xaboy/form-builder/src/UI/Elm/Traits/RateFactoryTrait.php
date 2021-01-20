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

namespace FormBuilder\UI\Elm\Traits;


use FormBuilder\UI\Elm\Components\Rate;

trait RateFactoryTrait
{
    /**
     * 评分选择组件
     *
     * @param string $field
     * @param string $title
     * @param int|float $value
     * @return Rate
     */
    public static function rate($field, $title, $value = 0)
    {
        return new Rate($field, $title, (float)$value);
    }
}