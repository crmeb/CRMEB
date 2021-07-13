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
 * Class Button
 * @method $this type(string $type) 按钮类型，可选值为primary、ghost、dashed、text、info、success、warning、error或者不设置
 * @method $this size(string $size) 按钮大小，可选值为large、small、default或者不设置
 * @method $this long(bool $long) 开启后，按钮的长度为 100%
 * @method $this htmlType(string $type) 设置button原生的type，可选值为button、submit、reset
 * @method $this disabled(bool $disabled) 设置按钮为禁用状态
 * @method $this icon(string $icon) 设置按钮的图标类型
 * @method $this innerText(string $innerText) 按钮文字提示
 * @method $this loading(bool $loading) 设置按钮为加载中状态
 * @method $this show(bool $show) 是否显示, 默认显示
 */
class Button extends CustomComponent
{
    protected static $propsRule = [
        'type' => 'string',
        'size' => 'string',
        'long' => 'bool',
        'htmlType' => 'string',
        'disabled' => 'bool',
        'icon' => 'string',
        'innerText' => 'string',
        'loading' => 'bool',
        'show' => 'bool'
    ];

    /**
     * 按钮形状，可选值为circle或者不设置
     *
     * @param bool $isCircle
     * @return $this
     */
    public function shape($isCircle = true)
    {
        if ($isCircle)
            $this->props['shape'] = 'circle';
        else
            unset($this->props['shape']);
        return $this;
    }
}