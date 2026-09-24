<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace crmeb\utils;

use FormBuilder\Factory\Elm as Form;

/**
 * Form Builder
 * Class FormBuilder
 * @package crmeb\utils
 */
class FormBuilder extends Form
{

    public static function setOptions($call){
        if (is_array($call)) {
            return $call;
        }else{
            return  $call();
        }

    }

    /**
     * 富文本编辑器（form-create 自定义组件）
     *
     * 前端 SchemaForm 已注册 type=SchemaEditor 的自定义组件（适配系统 wangEditor），
     * 经包内 CustomComponent 输出规则，值与提交行为与普通字段一致。
     *
     * @param string $field 字段名
     * @param string $title 字段标题
     * @param string $value 富文本 HTML 默认值
     * @param array  $ai    AI 写作默认提示词配置（可选）：prompt 默认提示词、
     *                      system_prompt 默认系统提示词、show_system_prompt 是否显示系统提示词编辑区
     * @return \FormBuilder\Driver\CustomComponent
     */
    public static function editor($field, $title, $value = '', array $ai = [])
    {
        $component = self::createComponent('SchemaEditor')
            ->field($field)
            ->title($title)
            ->value((string)$value);
        // AI 写作默认提示词写入组件 props 随规则下发（前端 SchemaEditor 接收后透传给公共弹窗 AiModal）
        $props = [];
        if (!empty($ai['prompt'])) {
            $props['aiPrompt'] = (string)$ai['prompt'];
        }
        if (!empty($ai['system_prompt'])) {
            $props['aiSystemPrompt'] = (string)$ai['system_prompt'];
        }
        if (!empty($ai['show_system_prompt'])) {
            $props['aiShowSystemPrompt'] = true;
        }
        if ($props) {
            $component->props($props);
        }
        return $component;
    }

    /**
     * 优惠券标签选择器（form-create 自定义组件）
     *
     * 前端 SchemaForm 已注册 type=SchemaCoupon 的自定义组件：
     * el-tag 标签展示已选优惠券（可删除），弹窗打开系统选择优惠券页回填
     * （/admin/marketing.Coupon/list.html，弹窗页脚确定/取消触发回填）。
     * 值为对象数组 [{id, title, coupon_price, use_min_price}]，
     * 发券消费端（userFirstSubGiveCoupon）已兼容该格式。
     *
     * @param string $field 字段名
     * @param string $title 字段标题
     * @param array $value 已选优惠券对象数组
     * @return \FormBuilder\Driver\CustomComponent
     */
    public static function couponSelect($field, $title, $value = [])
    {
        return self::createComponent('SchemaCoupon')
            ->field($field)
            ->title($title)
            ->value((array)$value)
            ->props(['field' => $field]);
    }

    /**
     * 多列勾选组（form-create 自定义组件 · 契约组件）
     *
     * 前端 SchemaForm 已注册 type=SchemaCheckboxGroup 的自定义组件。
     * ⚠️ 契约组件：前端实现被 schema 驱动页面与已编译插件 UMD 多方引用，
     * 改前端组件只能向后兼容扩展（新增可选 props、默认行为不变，红线见
     * .crmeb/rules/前端规范.md 第一节），并同步本方法注释。已支持能力：
     * - 网格布局多列展示（columns），支持全选联动与半选态；
     * - 选项 title 悬停多行提示；选项 group 归并分组渲染（分组标题全选/清空 + 已选计数）；
     * - 选项 disabled=true 单项禁用；props 传 disabled=true 整组禁用；
     * - props 传 max=N 限制最多勾选 N 项（默认 0 不限）。
     *
     * @param string $field 字段名
     * @param string $title 字段标题
     * @param array $value 已勾选值数组
     * @param array $options 选项列表 [{label, value[, group][, title][, disabled]}]
     * @param array $props 组件 props 覆盖：columns 每行列数（默认 2）、selectAll 是否显示全选（默认 true）、disabled 整组禁用、max 最多可勾选数（默认 0 不限）
     * @return \FormBuilder\Driver\CustomComponent
     */
    public static function checkboxGroup($field, $title, array $value = [], array $options = [], array $props = [])
    {
        return self::createComponent('SchemaCheckboxGroup')
            ->field($field)
            ->title($title)
            ->value($value)
            ->props(array_merge([
                'field' => $field,
                'options' => array_values($options),
                'columns' => 2,
                'selectAll' => true,
            ], $props));
    }

}
