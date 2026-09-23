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
 * 日期选择器组件
 * Class DatePicker
 *
 * @method $this type(string $type) 显示类型，可选值为 date、daterange、datetime、datetimerange、year、month
 * @method $this format(string $format) 展示的日期格式, 默认为yyyy-MM-dd HH:mm:ss
 * @method $this placement(string $placement) 日期选择器出现的位置，可选值为top, top-start, top-end, bottom, bottom-start, bottom-end, left, left-start, left-end, right, right-start, right-end, 默认为bottom-start
 * @method $this placeholder(string $placeholder) 占位文本
 * @method $this confirm(bool $bool) 是否显示底部控制栏，开启后，选择完日期，选择器不会主动关闭，需用户确认后才可关闭, 默认为false
 * @method $this size(string $size) 尺寸，可选值为large、small、default或者不设置
 * @method $this disabled(bool $bool) 是否禁用选择器
 * @method $this clearable(bool $bool) 是否显示清除按钮
 * @method $this readonly(bool $bool) 完全只读，开启后不会弹出选择器，只在没有设置 open 属性下生效
 * @method $this editable(bool $bool) 文本框是否可以输入, 默认为false
 * @method $this multiple(bool $bool) 开启后, 可以选择多个日期, 仅在 date 下可用, 默认为false
 * @method $this transfer(bool $bool) 是否将弹层放置于 body 内，在 Tabs、带有 fixed 的 Table 列内使用时，建议添加此属性，它将不受父级样式影响，从而达到更好的效果, 默认为false
 * @method $this splitPanels(bool $bool) 开启后，左右面板不联动，仅在 daterange 和 datetimerange 下可用。
 * @method $this showWeekNumbers(bool $bool) 开启后，可以显示星期数。
 *
 */
class DatePicker extends FormComponent
{
    /**
     * 日期选择
     */
    const TYPE_DATE = 'date';
    /**
     * 日期区间选择
     */
    const TYPE_DATE_RANGE = 'daterange';
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
        'type' => 'string',
        'format' => 'string',
        'placement' => 'string',
        'placeholder' => 'string',
        'size' => 'string',
        'confirm' => 'bool',
        'disabled' => 'bool',
        'clearable' => 'bool',
        'readonly' => 'bool',
        'editable' => 'bool',
        'multiple' => 'bool',
        'transfer' => 'bool',
        'splitPanels' => 'bool',
        'showWeekNumbers' => 'bool'
    ];

    protected function isRange()
    {
        return in_array(strtolower($this->props['type']), ['datetimerange', 'daterange']);
    }

    protected function isMultiple()
    {
        return isset($this->props['multiple']) && $this->props['multiple'];
    }

    public function createValidate()
    {
        if ($this->isRange() || $this->isMultiple())
            return Iview::validateArr();
        else
            return Iview::validateDate();
    }

    public function required($message = null)
    {
        if (is_null($message)) $message = $this->getPlaceHolder();
        $validate = $this->createValidate();

        if ($this->isRange()) {
            $dateRequired = Iview::validateDate()->message($message)->required();
            $validate->fields([
                '0' => $dateRequired,
                '1' => $dateRequired
            ]);
        }

        $this->appendValidate($validate->message($message)->required());
        return $this;
    }
}