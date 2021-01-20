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


use FormBuilder\UI\Elm\Components\Tree;
use FormBuilder\UI\Elm\Components\TreeData;

trait TreeFactoryTrait
{
    /**
     * 树形组件
     *
     * @param string $field
     * @param string $title
     * @param mixed $value
     * @param string $type
     * @return Tree
     */
    public static function tree($field, $title, $value = [], $type = Tree::TYPE_CHECKED)
    {
        $tree = new Tree($field, $title, $value);
        return $tree->type($type);
    }

    /**
     * 获取选中的值
     *
     * @param string $field
     * @param string $title
     * @param mixed $value
     * @return Tree
     */
    public static function treeSelected($field, $title, $value = [])
    {
        return self::tree($field, $title, $value, Tree::TYPE_SELECTED);
    }

    /**
     * 获取勾选的值
     *
     * @param string $field
     * @param string $title
     * @param mixed $value
     * @return Tree
     */
    public static function treeChecked($field, $title, $value = [])
    {
        return self::tree($field, $title, $value)->showCheckbox(true);
    }

    /**
     * 树形组件数据 date 类
     *
     * @param mixed $id
     * @param string $title
     * @param array $children
     * @return TreeData
     */
    public static function treeData($id, $title, array $children = [])
    {
        return new TreeData($id, $title, $children);
    }
}