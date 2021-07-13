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


use FormBuilder\UI\Elm\Components\Group;

trait GroupFactoryTrait
{

    /**
     * 数组组件
     *
     * @param string $field
     * @param string $title
     * @param array $value
     * @return Group
     */
    public static function group($field, $title, $value = [])
    {
        return new Group($field, $title, $value);
    }
}