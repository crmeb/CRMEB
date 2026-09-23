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
 * Class Tooltip
 * @method $this placement(string $placement) 提示框出现的位置，可选值为top, top-start, top-end, bottom, bottom-start, bottom-endleft, left-start, left-end, right, right-start, right-end
 * @method $this disabled(bool $disabled) 是否禁用
 * @method $this delay(float $delay) 延迟显示，单位毫秒
 * @method $this always(bool $always) 是否总是可见
 * @method $this theme(string $theme) 主题，可选值为 dark 或 light
 * @method $this maxWidth(string $maxWidth) 最大宽度，超出最大值后，文本将自动换行，并两端对齐
 * @method $this transfer(bool $transfer) 是否将弹层放置于 body 内，在 Tabs、带有 fixed 的 Table 列内使用时，建议添加此属性，它将不受父级样式影响，从而达到更好的效果
 * @method $this offset(float $offset) 出现位置的偏移量
 * @method $this options(array $options) 自定义 popper.js 的配置项
 */
class Tooltip extends CustomComponent
{
    protected $defaultProps = [
        'transfer' => true
    ];

    protected static $propsRule = [
        'placement' => 'string',
        'disabled ' => 'bool',
        'delay ' => 'float',
        'theme' => 'string',
        'maxWidth' => 'string',
        'wordWrap' => 'bool',
        'transfer' => 'bool',
        'offset' => 'float',
        'options' => 'array',
    ];
}