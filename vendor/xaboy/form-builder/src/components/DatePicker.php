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
 * 日期选择器组件
 * Class DatePicker
 *
 * @package FormBuilder\components
 * @method $this type(String $type) 显示类型，可选值为 date、daterange、datetime、datetimerange、year、month
 * @method $this format(String $format) 展示的日期格式, 默认为yyyy-MM-dd HH:mm:ss
 * @method $this placement(String $placement) 日期选择器出现的位置，可选值为top, top-start, top-end, bottom, bottom-start, bottom-end, left, left-start, left-end, right, right-start, right-end, 默认为bottom-start
 * @method $this placeholder(String $placeholder) 占位文本
 * @method $this confirm(Boolean $bool) 是否显示底部控制栏，开启后，选择完日期，选择器不会主动关闭，需用户确认后才可关闭, 默认为false
 * @method $this size(String $size) 尺寸，可选值为large、small、default或者不设置
 * @method $this disabled(Boolean $bool) 是否禁用选择器
 * @method $this clearable(Boolean $bool) 是否显示清除按钮
 * @method $this readonly(Boolean $bool) 完全只读，开启后不会弹出选择器，只在没有设置 open 属性下生效
 * @method $this editable(Boolean $bool) 文本框是否可以输入, 默认为false
 * @method $this transfer(Boolean $bool) 是否将弹层放置于 body 内，在 Tabs、带有 fixed 的 Table 列内使用时，建议添加此属性，它将不受父级样式影响，从而达到更好的效果, 默认为false
 * @method $this splitPanels(Boolean $bool) 开启后，左右面板不联动，仅在 daterange 和 datetimerange 下可用。
 * @method $this showWeekNumbers(Boolean $bool) 开启后，可以显示星期数。
 *
 */
class DatePicker extends FormComponentDriver
{
    /**
     * @var string
     */
    protected $name = 'datePicker';

    /**
     *
     */
    const TYPE_DATE = 'date';
    /**
     *
     */
    const TYPE_DATE_RANGE = 'daterange';
    /**
     *
     */
    const TYPE_DATE_TIME = 'datetime';
    /**
     *
     */
    const TYPE_DATE_TIME_RANGE = 'datetimerange';
    /**
     *
     */
    const TYPE_YEAR = 'year';
    /**
     *
     */
    const TYPE_MONTH = 'month';

    /**
     * @var array
     */
    protected $props = [
        'type' => self::TYPE_DATE,
        'editable' => false,
        'multiple' => false
    ];

    /**
     * @var array
     */
    protected static $propsRule = [
        'type' => 'string',
        'format' => 'string',
        'placement' => 'string',
        'placeholder' => 'string',
        'size' => 'string',
        'confirm' => 'boolean',
        'disabled' => 'boolean',
        'clearable' => 'boolean',
        'readonly' => 'boolean',
        'editable' => 'boolean',
        'transfer' => 'boolean',
        'splitPanels' => 'boolean',
        'showWeekNumbers' => 'boolean'
    ];

    /**
     *
     */
    protected function init()
    {
        $this->placeholder($this->getPlaceHolder());
    }

    /**
     * 开启后, 可以选择多个日期, 仅在 date 下可用, 默认为false
     *
     * @param bool $bool
     * @return $this
     */
    public function multiple($bool = true)
    {
        if ($this->props['type'] == 'date')
            $this->props['multiple'] = (bool)$bool;
        else
            $this->props['multiple'] = false;

        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function value($value)
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = Helper::getDate($v);
            }
        } else {
            $value = Helper::getDate($value);
        }
        $this->value = $value;
        return $this;
    }

    public function getValidateHandler()
    {
        if (in_array($this->props['type'], ['datetimerange', 'daterange']) || $this->props['multiple'])
            return Validate::arr();
        else
            return Validate::date();
    }

    public function required($message = null)
    {
        $message = $message ?: $this->getPlaceHolder();
        if (in_array($this->props['type'], ['datetimerange', 'daterange'])) {
            $this->validate()->fields([
                '0' => ['required' => true, 'type' => 'date', 'message' => $message],
                '1' => ['required' => true, 'type' => 'date', 'message' => $message]
            ], true, $message);
            return $this;
        } else
            return parent::required($message);
    }

    /**
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