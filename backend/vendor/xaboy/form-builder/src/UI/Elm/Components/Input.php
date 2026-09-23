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


use FormBuilder\Driver\FormComponent;
use FormBuilder\Factory\Elm;

/**
 * Input组件,支持类型text、password、textarea、url、email、date
 * Class Input
 *
 * @method $this type(string $type) 类型, 可选值: text，textarea 和其他 原生 input 的 type 值, 默认值: text
 * @method $this maxlength(float $maxlength) 原生属性，最大输入长度
 * @method $this minlength(float $minlength) 原生属性，最小输入长度
 * @method $this showWordLimit(bool $showWordLimit) 是否显示输入字数统计，只在 type = "text" 或 type = "textarea" 时有效, 默认值: false
 * @method $this placeholder(string $placeholder) 输入框占位文本
 * @method $this clearable(bool $clearable) 是否可清空, 默认值: false
 * @method $this showPassword(bool $showPassword) 是否显示切换密码图标, 默认值: false
 * @method $this disabled(bool $disabled) 禁用, 默认值: false
 * @method $this size(string $size) 输入框尺寸，只在 type!="textarea" 时有效, 可选值: medium / small / mini
 * @method $this prefixIcon(string $prefixIcon) 输入框头部图标
 * @method $this suffixIcon(string $suffixIcon) 输入框尾部图标
 * @method $this rows(float $rows) 输入框行数，只对 type = "textarea" 有效, 默认值: 2
 * @method $this autocomplete(string $autocomplete) 原生属性，自动补全, 可选值: on, off, 默认值: off
 * @method $this name(string $name) 原生属性
 * @method $this readonly(bool $readonly) 原生属性，是否只读, 默认值: false
 * @method $this max(string $max) 原生属性，设置最大值
 * @method $this min(string $min) 原生属性，设置最小值
 * @method $this step(string $step) 原生属性，设置输入字段的合法数字间隔
 * @method $this resize(string $resize) 控制是否能被用户缩放, 可选值: none, both, horizontal, vertical
 * @method $this autofocus(bool $autofocus) 原生属性，自动获取焦点, 可选值: true, false, 默认值: false
 * @method $this form(string $form) 原生属性
 * @method $this label(string $label) 输入框关联的label文字
 * @method $this tabindex(string $tabindex) 输入框的tabindex
 * @method $this validateEvent(bool $validateEvent) 输入时是否触发表单的校验, 默认值: true
 */
class Input extends FormComponent
{
    const TYPE_TEXT = 'text';

    const TYPE_PASSWORD = 'password';

    const TYPE_TEXTAREA = 'textarea';

    const TYPE_URL = 'url';

    const TYPE_EMAIL = 'email';

    const TYPE_DATE = 'date';


    protected $defaultProps = [
        'type' => self::TYPE_TEXT
    ];

    protected static $propsRule = [
        'type' => 'string',
        'maxlength' => 'float',
        'minlength' => 'float',
        'showWordLimit' => 'bool',
        'placeholder' => 'string',
        'clearable' => 'bool',
        'showPassword' => 'bool',
        'disabled' => 'bool',
        'size' => 'string',
        'prefixIcon' => 'string',
        'suffixIcon' => 'string',
        'rows' => 'float',
        'autocomplete' => 'string',
        'name' => 'string',
        'readonly' => 'bool',
        'max' => 'string',
        'min' => 'string',
        'step' => 'string',
        'resize' => 'string',
        'autofocus' => 'bool',
        'form' => 'string',
        'label' => 'string',
        'tabindex' => 'string',
        'validateEvent' => 'bool',
    ];

    /**
     * 自适应内容高度，仅在 textarea 类型下有效
     *
     * @param Bool|Number $minRows
     * @param null|Number $maxRows
     * @return $this
     */
    public function autoSize($minRows = false, $maxRows = null)
    {
        $this->props['autosize'] = $maxRows === null ? boolval($minRows) : compact('minRows', 'maxRows');
        return $this;
    }

    public function createValidate()
    {
        return Elm::validateStr();
    }
}