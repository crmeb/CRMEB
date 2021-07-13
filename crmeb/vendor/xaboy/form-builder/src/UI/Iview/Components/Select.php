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


use FormBuilder\Driver\FormOptionsComponent;
use FormBuilder\Factory\Iview;

/**
 * 选择器组件
 * Class Select
 *
 * @method $this multiple(bool $bool) 是否支持多选, 默认为false
 * @method $this disabled(bool $bool) 是否禁用, 默认为false
 * @method $this clearable(bool $bool) 是否可以清空选项，只在单选时有效, 默认为false
 * @method $this filterable(bool $bool) 是否支持搜索, 默认为false
 * @method $this size(string $size) 选择框大小，可选值为large、small、default或者不填
 * @method $this placeholder(string $placeholder) 选择框默认文字
 * @method $this transfer(string $transfer) 是否将弹层放置于 body 内，在 Tabs、带有 fixed 的 Table 列内使用时，建议添加此属性，它将不受父级样式影响，从而达到更好的效果, 默认为false
 * @method $this placement(string $placement) 弹窗的展开方向，可选值为 bottom 和 top, 默认为bottom
 * @method $this notFoundText(string $text) 当下拉列表为空时显示的内容, 默认为 无匹配数据
 *
 */
class Select extends FormOptionsComponent
{
    protected $selectComponent = true;

    protected $defaultProps = [
        'multiple' => false
    ];

    protected static $propsRule = [
        'multiple' => 'bool',
        'disabled' => 'bool',
        'clearable' => 'bool',
        'filterable' => 'bool',
        'size' => 'string',
        'placeholder' => 'string',
        'transfer' => 'string',
        'placement' => 'string',
        'notFoundText' => 'string',
    ];

    public function createValidate()
    {
        if ($this->props['multiple'] == true)
            return Iview::validateArr();
        else
            return Iview::validateStr();
    }

    public function createValidateNum()
    {
        return Iview::validateNum();
    }

    public function requiredNum($message = null)
    {
        if (is_null($message)) $message = $this->getPlaceHolder();
        return $this->appendValidate($this->createValidateNum()->message($message)->required());
    }

}