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

namespace FormBuilder\UI\Elm\Components;


use FormBuilder\Driver\CustomComponent;

/**
 * Class Button
 *
 * @method $this size(string $size) 尺寸, 可选值: medium / small / mini
 * @method $this type(string $type) 类型, 可选值: primary / success / warning / danger / info / text
 * @method $this plain(bool $plain) 是否朴素按钮, 默认值: false
 * @method $this round(bool $round) 是否圆角按钮, 默认值: false
 * @method $this circle(bool $circle) 是否圆形按钮, 默认值: false
 * @method $this loading(bool $loading) 是否加载中状态, 默认值: false
 * @method $this disabled(bool $disabled) 是否禁用状态, 默认值: false
 * @method $this icon(string $icon) 图标类名
 * @method $this autofocus(bool $autofocus) 是否默认聚焦, 默认值: false
 * @method $this nativeType(string $nativeType) 原生 type 属性, 可选值: button / submit / reset, 默认值: button
 *
 * @method $this show(bool $show) 是否显示, 默认显示
 * @method $this innerText(string $innerText) 按钮文字提示
 */
class Button extends CustomComponent
{
    protected static $propsRule = [
        'size' => 'string',
        'type' => 'string',
        'plain' => 'bool',
        'round' => 'bool',
        'circle' => 'bool',
        'loading' => 'bool',
        'disabled' => 'bool',
        'icon' => 'string',
        'autofocus' => 'bool',
        'nativeType' => 'string',
        'innerText' => 'string',
        'show' => 'bool'
    ];
}