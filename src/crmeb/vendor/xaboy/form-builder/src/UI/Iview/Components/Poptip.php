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

namespace FormBuilder\UI\Iview\Components;


use FormBuilder\Driver\CustomComponent;

/**
 * Class Poptip
 * @method $this trigger(string $trigger) 触发方式，可选值为hover（悬停）click（点击）focus（聚焦）, 在 confirm 模式下，只有 click 有效
 * @method $this popperTitle(string $title) 显示的标题
 * @method $this placement(string $placement) 提示框出现的位置，可选值为top, top-start, top-end, bottom, bottom-start, bottom-endleft, left-start, left-end, right, right-start, right-end
 * @method $this width(string $width) 宽度，最小宽度为 150px，在 confirm 模式下，默认最大宽度为 300px
 * @method $this confirm(bool $confirm) 是否开启对话框模式
 * @method $this disabled(bool $disabled) 是否禁用
 * @method $this okText(string $okText) 确定按钮的文字，只在 confirm 模式下有效
 * @method $this cancelText(string $cancelText) 取消按钮的文字，只在 confirm 模式下有效
 * @method $this transfer(bool $transfer) 是否将弹层放置于 body 内，在 Tabs、带有 fixed 的 Table 列内使用时，建议添加此属性，它将不受父级样式影响，从而达到更好的效果
 * @method $this popperClass(string $popperClass) 给 Poptip 设置 class-name，在使用 transfer 时会很有用
 * @method $this wordWrap(bool $wordWrap) 开启后，超出指定宽度文本将自动换行，并两端对齐
 * @method $this padding(string $padding) 自定义间距值
 * @method $this offset(float $offset) 出现位置的偏移量
 * @method $this options(array $options) 自定义 popper.js 的配置项
 */
class Poptip extends CustomComponent
{
    protected $defaultProps = [
        'transfer' => true
    ];

    protected static $propsRule = [
        'trigger' => 'string',
        'placement' => 'string',
        'width' => 'string',
        'confirm' => 'bool',
        'disabled ' => 'bool',
        'okText ' => 'string',
        'cancelText' => 'string',
        'transfer' => 'bool',
        'popperClass' => 'string',
        'popperTitle' => ['string', 'title'],
        'wordWrap' => 'bool',
        'padding' => 'string',
        'offset' => 'float',
        'options' => 'array',
    ];
}