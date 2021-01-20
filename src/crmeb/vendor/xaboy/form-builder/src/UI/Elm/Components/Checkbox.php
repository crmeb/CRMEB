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


use FormBuilder\Driver\FormOptionsComponent;
use FormBuilder\Factory\Elm;

/**
 * 复选框组件
 * Class Checkbox
 *
 * @method $this size(string $size) 多选框组尺寸，仅对按钮形式的 Checkbox 或带有边框的 Checkbox 有效, 可选值: medium / small / mini
 * @method $this disabled(bool $disabled) 是否禁用, 默认值: false
 * @method $this min(float $min) 可被勾选的 checkbox 的最小数量
 * @method $this max(float $max) 可被勾选的 checkbox 的最大数量
 * @method $this textColor(string $textColor) 按钮形式的 Checkbox 激活时的文本颜色, 默认值: #ffffff
 * @method $this fill(string $fill) 按钮形式的 Checkbox 激活时的填充色和边框色, 默认值: #409EFF
 * @method $this checked(bool $checked) 当前是否勾选(只有在checkbox-button时有效), 默认值: false
 */
class Checkbox extends FormOptionsComponent
{
    const TYPE_BUTTON = 'button';

    const TYPE_GROUP = 'group';

    protected $defaultValue = [];

    protected $selectComponent = true;

    protected static $propsRule = [
        'size' => 'string',
        'disabled' => 'bool',
        'min' => 'float',
        'max' => 'float',
        'textColor' => 'string',
        'fill' => 'string',
        'checked' => 'bool',
    ];

    /**
     * @param array $value
     * @return $this
     */
    public function value($value)
    {
        $this->value = (array)$value;
        return $this;
    }

    /**
     * @param bool $bool
     * @return $this
     */
    public function button($bool)
    {
        if ($bool)
            $this->props['type'] = 'button';
        else
            unset($this->props['type']);

        return $this;
    }

    public function createValidate()
    {
        return Elm::validateArr();
    }
}