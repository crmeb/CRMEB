<?php
/**
 * FormBuilder表单生成器
 * Author: xaboy
 * Github: https://github.com/xaboy/form-builder
 */

namespace FormBuilder\components;


use FormBuilder\FormComponentDriver;
use FormBuilder\Helper;

/**
 * Input组件,支持类型text、password、textarea、url、email、date
 * Class Input
 *
 * @package FormBuilder\components
 * @method $this type(String $type) 输入框类型，可选值为 text、password、textarea、url、email、date;
 * @method $this size(String $size) 输入框尺寸，可选值为large、small、default或者不设置;
 * @method $this placeholder(String $placeholder) 占位文本
 * @method $this clearable(Boolean $bool) 是否显示清空按钮, 默认为false
 * @method $this disabled(Boolean $bool) 设置输入框为禁用状态, 默认为false
 * @method $this readonly(Boolean $bool) 设置输入框为只读, 默认为false
 * @method $this maxlength(int $length) 最大输入长度
 * @method $this icon(String $icon) 输入框尾部图标，仅在 text 类型下有效
 * @method $this rows(int $rows) 文本域默认行数，仅在 textarea 类型下有效, 默认为2
 * @method $this number(Boolean $bool) 将用户的输入转换为 Number 类型, 默认为false
 * @method $this autofocus(Boolean $bool) 自动获取焦点, 默认为false
 * @method $this autocomplete(Boolean $bool) 原生的自动完成功能, 默认为false
 * @method $this spellcheck(Boolean $bool) 原生的 spellcheck 属性, 默认为false
 * @method $this wrap(String $warp) 原生的 wrap 属性，可选值为 hard 和 soft, 默认为soft
 */
class Input extends FormComponentDriver
{
    /**
     * @var string
     */
    protected $name = 'input';

    /**
     * 组件类型
     */
    const TYPE_TEXT = 'text';
    /**
     *
     */
    const TYPE_PASSWORD = 'password';
    /**
     *
     */
    const TYPE_TEXTAREA = 'textarea';
    /**
     *
     */
    const TYPE_URL = 'url';
    /**
     *
     */
    const TYPE_EMAIL = 'email';
    /**
     *
     */
    const TYPE_DATE = 'date';

    /**
     * @var array
     */
    protected $props = [
        'type' => self::TYPE_TEXT
    ];

    /**
     * @var array
     */
    protected static $propsRule = [
        'type' => 'string',
        'size' => 'string',
        'placeholder' => 'string',
        'clearable' => 'boolean',
        'disabled' => 'boolean',
        'readonly' => 'boolean',
        'maxlength' => 'int',
        'icon' => 'string',
        'rows' => 'int',
        'number' => 'boolean',
        'autofocus' => 'boolean',
        'autocomplete' => 'boolean',
        'spellcheck' => 'boolean',
        'wrap' => 'string',
    ];

    /**
     *
     */
    protected function init()
    {
        $this->placeholder($this->getPlaceHolder());
    }

    protected function getPlaceHolder($pre = '请输入')
    {
        return parent::getPlaceHolder($pre);
    }

    public function getValidateHandler()
    {
        return Validate::str(Validate::TRIGGER_BLUR);
    }


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

    /**
     * 生成表单规则
     *
     * @return array
     */
    public function build()
    {
        return [
            'type' => $this->name,
            'field' => $this->field,
            'title' => $this->title,
            'value' => $this->value,
            'props' => (object)$this->props,
            'validate' => $this->validate,
            'col' => $this->col
        ];
    }
}