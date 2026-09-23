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


use FormBuilder\Driver\FormComponent;
use FormBuilder\Factory\Iview;

/**
 * Input组件,支持类型text、password、textarea、url、email、date
 * Class Input
 *
 * @method $this type(string $type) 输入框类型，可选值为 text、password、textarea、url、email、date;
 * @method $this size(string $size) 输入框尺寸，可选值为large、small、default或者不设置;
 * @method $this placeholder(string $placeholder) 占位文本
 * @method $this clearable(bool $bool) 是否显示清空按钮, 默认为false
 * @method $this disabled(bool $bool) 设置输入框为禁用状态, 默认为false
 * @method $this readonly(bool $bool) 设置输入框为只读, 默认为false
 * @method $this maxlength(int $length) 最大输入长度
 * @method $this icon(string $icon) 输入框尾部图标，仅在 text 类型下有效
 * @method $this rows(int $rows) 文本域默认行数，仅在 textarea 类型下有效, 默认为2
 * @method $this number(bool $bool) 将用户的输入转换为 Number 类型, 默认为false
 * @method $this autofocus(bool $bool) 自动获取焦点, 默认为false
 * @method $this autocomplete(bool $bool) 原生的自动完成功能, 默认为false
 * @method $this spellcheck(bool $bool) 原生的 spellcheck 属性, 默认为false
 * @method $this wrap(string $warp) 原生的 wrap 属性，可选值为 hard 和 soft, 默认为soft
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
        'size' => 'string',
        'placeholder' => 'string',
        'clearable' => 'bool',
        'disabled' => 'bool',
        'readonly' => 'bool',
        'maxlength' => 'int',
        'icon' => 'string',
        'rows' => 'int',
        'number' => 'bool',
        'autofocus' => 'bool',
        'autocomplete' => 'bool',
        'spellcheck' => 'bool',
        'wrap' => 'string',
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
        return Iview::validateStr();
    }
}