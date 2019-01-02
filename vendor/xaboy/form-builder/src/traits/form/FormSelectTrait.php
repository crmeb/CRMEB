<?php
/**
 * FormBuilder表单生成器
 * Author: xaboy
 * Github: https://github.com/xaboy/form-builder
 */

namespace FormBuilder\traits\form;


use FormBuilder\components\Select;

/**
 * Class FormSelectTrait
 *
 * @package FormBuilder\traits\form
 */
trait FormSelectTrait
{
    /**
     * 下拉选择组件
     *
     * @param        $field
     * @param        $title
     * @param string $value
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
     * @param       $field
     * @param       $title
     * @param array $value
     * @return Select
     */
    public static function selectMultiple($field, $title, array $value = [])
    {
        return self::select($field, $title, $value);
    }

    /**
     * 单选
     *
     * @param        $field
     * @param        $title
     * @param string $value
     * @return Select
     */
    public static function selectOne($field, $title, $value = '')
    {
        return self::select($field, $title, (string)$value);
    }
}