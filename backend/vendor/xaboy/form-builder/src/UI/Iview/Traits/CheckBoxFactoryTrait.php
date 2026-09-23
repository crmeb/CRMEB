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

namespace FormBuilder\UI\Iview\Traits;

use FormBuilder\UI\Iview\Components\Checkbox;

trait CheckBoxFactoryTrait
{
    /**
     * 多选框组件
     *
     * @param string $field
     * @param string $title
     * @param array $value
     * @return Checkbox
     */
    public static function checkbox($field, $title, array $value = [])
    {
        return new Checkbox($field, $title, $value);
    }
}