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
 * 日期选择器组件
 * Class DatePicker
 *
 * @method $this readonly(bool $readonly) 完全只读, 默认值: false
 * @method $this disabled(bool $disabled) 禁用, 默认值: false
 * @method $this editable(bool $editable) 文本框可输入, 默认值: true
 * @method $this clearable(bool $clearable) 是否显示清除按钮, 默认值: true
 * @method $this size(string $size) 输入框尺寸, 可选值: large, small, mini
 * @method $this placeholder(string $placeholder) 非范围选择时的占位内容
 * @method $this startPlaceholder(string $startPlaceholder) 范围选择时开始日期的占位内容
 * @method $this endPlaceholder(string $endPlaceholder) 范围选择时结束日期的占位内容
 * @method $this type(string $type) 显示类型, 可选值: year/month/date/dates/ week/datetime/datetimerange/ daterange/monthrange, 默认值: date
 * @method $this format(string $format) 显示在输入框中的格式, 可选值: 见日期格式, 默认值: yyyy-MM-dd
 * @method $this align(string $align) 对齐方式, 可选值: left, center, right, 默认值: left
 * @method $this popperClass(string $popperClass) DatePicker 下拉框的类名
 * @method $this pickerOptions(array $pickerOptions) 当前时间日期选择器特有的选项参考下表, 默认值: {
 * }
 * @method $this rangeSeparator(string $rangeSeparator) 选择范围时的分隔符, 默认值: '-'
 * @method $this defaultValue(string $defaultValue) 可选，选择器打开时默认显示的时间, 可选值: 可被new Date()解析
 * @method $this defaultTime(array $defaultTime) 范围选择时选中日期所使用的当日内具体时刻, 可选值: 数组，长度为 2，每项值为字符串，形如12:00:00，第一项指定开始日期的时刻，第二项指定结束日期的时刻，不指定会使用时刻 00:00:00
 * @method $this valueFormat(string $valueFormat) 可选，绑定值的格式。不指定则绑定值为 Date 对象, 可选值: 见日期格式
 * @method $this name(string $name) 原生属性
 * @method $this unlinkPanels(bool $unlinkPanels) 在范围选择器里取消两个日期面板之间的联动, 默认值: false
 * @method $this prefixIcon(string $prefixIcon) 自定义头部图标的类名, 默认值: el-icon-date
 * @method $this clearIcon(string $clearIcon) 自定义清空图标的类名, 默认值: el-icon-circle-close
 * @method $this validateEvent(bool $validateEvent) 输入时是否触发表单的校验, 默认值: true
 * @method $this timeArrowControl(bool $timeArrowControl) 是否使用箭头进行时间选择, 默认值: false
 *
 *
 */
class DatePicker extends FormComponent
{
    /**
     * 日期选择
     */
    const TYPE_DATE = 'date';

    const TYPE_DATES = 'dates';

    const TYPE_WEEK = 'week';

    /**
     * 日期区间选择
     */
    const TYPE_DATE_RANGE = 'daterange';

    const TYPE_MONTH_RANGE = 'monthrange';

    /**
     * 日期+时间选择
     */
    const TYPE_DATE_TIME = 'datetime';
    /**
     * 日期+时间区间选择
     */
    const TYPE_DATE_TIME_RANGE = 'datetimerange';
    /**
     * 年份选择
     */
    const TYPE_YEAR = 'year';
    /**
     * 月份选择
     */
    const TYPE_MONTH = 'month';


    protected $selectComponent = true;

    protected $defaultProps = [
        'type' => self::TYPE_DATE,
        'editable' => false
    ];

    protected static $propsRule = [
        'readonly' => 'bool',
        'disabled' => 'bool',
        'editable' => 'bool',
        'clearable' => 'bool',
        'size' => 'string',
        'placeholder' => 'string',
        'startPlaceholder' => 'string',
        'endPlaceholder' => 'string',
        'timeArrowControl' => 'bool',
        'type' => 'string',
        'format' => 'string',
        'align' => 'string',
        'popperClass' => 'string',
        'pickerOptions' => 'array',
        'rangeSeparator' => 'string',
        'defaultValue' => 'string',
        'defaultTime' => 'array',
        'valueFormat' => 'string',
        'name' => 'string',
        'unlinkPanels' => 'bool',
        'prefixIcon' => 'string',
        'clearIcon' => 'string',
        'validateEvent' => 'bool',
    ];

    protected function isRange()
    {
        return in_array(strtolower($this->props['type']), ['datetimerange', 'daterange', 'monthrange']);
    }

    protected function isMultiple()
    {
        return isset($this->props['type']) && strtolower($this->props['type']) == 'dates';
    }

    public function createValidate()
    {
        if ($this->isRange() || $this->isMultiple())
            return Elm::validateArr();
        else
            return Elm::validateDate();
    }

    public function required($message = null)
    {
        if (is_null($message)) $message = $this->getPlaceHolder();
        $validate = $this->createValidate();

        if ($this->isRange()) {
            $dateRequired = Elm::validateDate()->message($message)->required();
            $validate->fields([
                '0' => $dateRequired,
                '1' => $dateRequired
            ]);
        }

        $this->appendValidate($validate->message($message)->required());
        return $this;
    }
}