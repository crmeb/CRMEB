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


use FormBuilder\UI\Iview\Style\Col;
use FormBuilder\UI\Iview\Style\FormStyle;
use FormBuilder\UI\Iview\Style\Row;

trait FormStyleFactoryTrait
{

    /**
     * 组件布局规则类
     *
     * @param array $rule
     * @return Col
     */
    public static function col(array $rule = [])
    {
        return new Col($rule);
    }

    /**
     * 表格布局规则类
     *
     * @param $rule
     * @return Row
     */
    public static function row(array $rule = [])
    {
        return new Row($rule);
    }

    /**
     * 表格样式类
     *
     * @param array $rule
     * @return FormStyle
     */
    public static function style(array $rule = [])
    {
        return new FormStyle($rule);
    }
}