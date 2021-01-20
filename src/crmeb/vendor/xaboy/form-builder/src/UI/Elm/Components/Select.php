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
 * 选择器组件
 * Class Select
 *
 * @method $this multiple(bool $multiple) 是否多选, 默认值: false
 * @method $this disabled(bool $disabled) 是否禁用, 默认值: false
 * @method $this valueKey(string $valueKey) 作为 value 唯一标识的键名，绑定值为对象类型时必填, 默认值: value
 * @method $this size(string $size) 输入框尺寸, 可选值: medium/small/mini
 * @method $this clearable(bool $clearable) 是否可以清空选项, 默认值: false
 * @method $this collapseTags(bool $collapseTags) 多选时是否将选中值按文字的形式展示, 默认值: false
 * @method $this multipleLimit(float $multipleLimit) 多选时用户最多可以选择的项目数，为 0 则不限制
 * @method $this name(string $name) select input 的 name 属性
 * @method $this autocomplete(string $autocomplete) select input 的 autocomplete 属性, 默认值: off
 * @method $this placeholder(string $placeholder) 占位符, 默认值: 请选择
 * @method $this filterable(bool $filterable) 是否可搜索, 默认值: false
 * @method $this allowCreate(bool $allowCreate) 是否允许用户创建新条目，需配合 filterable 使用, 默认值: false
 * @method $this remote(bool $remote) 是否为远程搜索, 默认值: false
 * @method $this loading(bool $loading) 是否正在从远程获取数据, 默认值: false
 * @method $this loadingText(string $loadingText) 远程加载时显示的文字, 默认值: 加载中
 * @method $this noMatchText(string $noMatchText) 搜索条件无匹配时显示的文字，也可以使用slot = "empty"设置, 默认值: 无匹配数据
 * @method $this noDataText(string $noDataText) 选项为空时显示的文字，也可以使用slot = "empty"设置, 默认值: 无数据
 * @method $this popperClass(string $popperClass) Select 下拉框的类名
 * @method $this reserveKeyword(bool $reserveKeyword) 多选且可搜索时，是否在选中一个选项后保留当前的搜索关键词, 默认值: false
 * @method $this defaultFirstOption(bool $defaultFirstOption) 在输入框按下回车，选择第一个匹配项。需配合 filterable 或 remote 使用, 默认值: false
 * @method $this popperAppendToBody(bool $popperAppendToBody) 是否将弹出框插入至 body 元素。在弹出框的定位出现问题时，可将该属性设置为 false, 默认值: true
 * @method $this automaticDropdown(bool $automaticDropdown) 对于不可搜索的 Select，是否在输入框获得焦点后自动弹出选项菜单, 默认值: false
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
        'valueKey' => 'string',
        'size' => 'string',
        'clearable' => 'bool',
        'collapseTags' => 'bool',
        'multipleLimit' => 'float',
        'name' => 'string',
        'autocomplete' => 'string',
        'placeholder' => 'string',
        'filterable' => 'bool',
        'allowCreate' => 'bool',
        'remote' => 'bool',
        'loading' => 'bool',
        'loadingText' => 'string',
        'noMatchText' => 'string',
        'noDataText' => 'string',
        'popperClass' => 'string',
        'reserveKeyword' => 'bool',
        'defaultFirstOption' => 'bool',
        'popperAppendToBody' => 'bool',
        'automaticDropdown' => 'bool',
    ];

    public function createValidate()
    {
        if ($this->props['multiple'] == true)
            return Elm::validateArr();
        else
            return Elm::validateStr();
    }

    public function createValidateNum()
    {
        return Elm::validateNum();
    }

    public function requiredNum($message = null)
    {
        if (is_null($message)) $message = $this->getPlaceHolder();
        return $this->appendValidate($this->createValidateNum()->message($message)->required());
    }

}