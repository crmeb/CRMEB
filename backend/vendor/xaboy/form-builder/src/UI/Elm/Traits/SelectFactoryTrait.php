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


use FormBuilder\UI\Elm\Components\Select;

trait SelectFactoryTrait
{
    /**
     * 下拉选择组件
     *
     * @param string $field
     * @param string $title
     * @param mixed $value
     * @return Select
     */
    public static function select($field, $title, $value = '')
    {
        $multiple = is_array($value) ? true : false;
        $select = new Select($field, $title, $value);
        $select->multiple($multiple);
        return $select;
    }

    /**
     * 多选
     *
     * @param string $field
     * @param string $title
     * @param array $value
     * @return Select
     */
    public static function selectMultiple($field, $title, array $value = [])
    {
        return self::select($field, $title, $value);
    }
}