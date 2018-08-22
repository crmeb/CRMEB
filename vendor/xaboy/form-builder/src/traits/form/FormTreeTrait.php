<?php
/**
 * FormBuilder表单生成器
 * Author: xaboy
 * Github: https://github.com/xaboy/form-builder
 */

namespace FormBuilder\traits\form;


use FormBuilder\components\Tree;
use FormBuilder\components\TreeData;

/**
 * Class FormTreeTrait
 * @package FormBuilder\traits\form
 */
trait FormTreeTrait
{
    /**
     * @param $field
     * @param $title
     * @param array $value
     * @param string $type
     * @return Tree
     */
    public static function tree($field, $title, $value = [], $type = Tree::TYPE_CHECKED)
    {
        return (new Tree($field, $title, $value))->type($type);
    }

    /**
     * @param $field
     * @param $title
     * @param array $value
     * @return Tree
     */
    public static function treeSelected($field, $title, $value = [])
    {
        return self::tree($field, $title, $value,Tree::TYPE_SELECTED);
    }

    /**
     * @param $field
     * @param $title
     * @param array $value
     * @return Tree
     */
    public static function treeChecked($field, $title, $value = [])
    {
        return self::tree($field, $title, $value)->showCheckbox(true);
    }

    /**
     * @param $id
     * @param $title
     * @param array $children
     * @return TreeData
     */
    public static function treeData($id, $title, array $children = [])
    {
        return new TreeData($id,$title,$children);
    }
}