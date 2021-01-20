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

namespace FormBuilder\UI\Elm\Style;


use FormBuilder\Contract\StyleInterface;
use FormBuilder\Rule\CallPropsRule;

/**
 * form表单样式
 * Class FormStyle
 *
 * @method $this inline(bool $inline) 行内表单模式, 默认值: false
 * @method $this labelPosition(string $labelPosition) 表单域标签的位置，如果值为 left 或者 right 时，则需要设置 label-width, 可选值: right/left/top, 默认值: right
 * @method $this labelWidth(string $labelWidth) 表单域标签的宽度，例如 '50px'。作为 Form 直接子元素的 form-item 会继承该值。支持 auto。
 * @method $this labelSuffix(string $labelSuffix) 表单域标签的后缀
 * @method $this hideRequiredAsterisk(bool $hideRequiredAsterisk) 是否显示必填字段的标签旁边的红色星号, 默认值: false
 * @method $this showMessage(bool $showMessage) 是否显示校验错误信息, 默认值: true
 * @method $this inlineMessage(bool $inlineMessage) 是否以行内形式展示校验信息, 默认值: false
 * @method $this statusIcon(bool $statusIcon) 是否在输入框中显示校验结果反馈图标, 默认值: false
 * @method $this validateOnRuleChange(bool $validateOnRuleChange) 是否在 rules 属性改变后立即触发一次验证, 默认值: true
 * @method $this size(string $size) 用于控制该表单内组件的尺寸, 可选值: medium / small / mini
 * @method $this disabled(bool $disabled) 是否禁用该表单内的所有组件。若设置为 true，则表单内组件上的 disabled 属性不再生效, 默认值: false
 */
class FormStyle implements StyleInterface
{
    use CallPropsRule;

    /**
     * @var array
     */
    protected $props;

    protected static $propsRule = [
        'inline' => 'bool',
        'labelPosition' => 'string',
        'labelWidth' => 'string',
        'labelSuffix' => 'string',
        'hideRequiredAsterisk' => 'bool',
        'showMessage' => 'bool',
        'inlineMessage' => 'bool',
        'statusIcon' => 'bool',
        'validateOnRuleChange' => 'bool',
        'size' => 'string',
        'disabled' => 'bool',
    ];

    /**
     * FormStyle constructor.
     * @param array $rule
     */
    public function __construct(array $rule = [])
    {
        $this->props = $rule;
    }

    /**
     * 设置表单 class
     * @param $class
     * @return $this
     */
    public function className($class)
    {
        $this->props['className'] = $class;
        return $this;
    }

    /**
     * @return object
     */
    public function getStyle()
    {
        return (object)$this->props;
    }

}