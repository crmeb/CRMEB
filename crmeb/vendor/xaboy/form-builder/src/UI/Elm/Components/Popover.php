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
 * Class Popover
 *
 * @method $this trigger(string $trigger) 触发方式, 可选值: click/focus/hover/manual, 默认值: click
 * @method $this popoverTitle(string $title) 标题
 * @method $this content(string $content) 显示的内容，也可以通过 slot 传入 DOM
 * @method $this width(string $width) 宽度, 默认值: 最小宽度 150px
 * @method $this placement(string $placement) 出现位置, 可选值: top/top-start/top-end/bottom/bottom-start/bottom-end/left/left-start/left-end/right/right-start/right-end, 默认值: bottom
 * @method $this disabled(boolean $disabled) Popover 是否可用, 默认值: false
 * @method $this offset(number $offset) 出现位置的偏移量
 * @method $this transition(string $transition) 定义渐变动画, 默认值: fade-in-linear
 * @method $this visibleArrow(boolean $visibleArrow) 是否显示 Tooltip 箭头，更多参数可见Vue-popper, 默认值: true
 * @method $this popperOptions(object $popperOptions) popper.js 的参数, 可选值: 参考 popper.js 文档, 默认值: {boundariesElement: 'body', gpuAcceleration: false}
 * @method $this popperClass(string $popperClass) 为 popper 添加类名
 * @method $this openDelay(number $openDelay) 触发方式为 hover 时的显示延迟，单位为毫秒
 * @method $this closeDelay(float $closeDelay) 触发方式为 hover 时的隐藏延迟，单位为毫秒, 默认值: 200
 * @method $this tabindex(float $tabindex) Popover 组件的 tabindex
 */
class Popover extends CustomComponent
{
    protected static $propsRule = [
        'trigger' => 'string',
        'title' => 'string',
        'content' => 'string',
        'width' => 'string',
        'placement' => 'string',
        'disabled' => 'boolean',
        'popoverTitle' => ['string', 'title'],
        'offset' => 'number',
        'transition' => 'string',
        'visibleArrow' => 'boolean',
        'popperOptions' => 'object',
        'popperClass' => 'string',
        'openDelay' => 'number',
        'closeDelay' => 'float',
        'tabindex' => 'float',
    ];
}