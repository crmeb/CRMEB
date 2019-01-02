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
 * 时间选择器组件
 * Class TimePicker
 *
 * @package FormBuilder\components
 * @method $this type(String $type) 显示类型，可选值为 time、timerange
 * @method $this format(String $format) 展示的时间格式, 默认为HH:mm:ss
 * @method $this placement(String $placement) 时间选择器出现的位置，可选值为top, top-start, top-end, bottom, bottom-start, bottom-end, left, left-start, left-end, right, right-start, right-end, 默认为bottom-start
 * @method $this placeholder(String $placeholder) 占位文本
 * @method $this confirm(Boolean $bool) 是否显示底部控制栏, 默认为false
 * @method $this size(String $size) 尺寸，可选值为large、small、default或者不设置
 * @method $this disabled(Boolean $bool) 是否禁用选择器
 * @method $this clearable(Boolean $bool) 是否显示清除按钮
 * @method $this readonly(Boolean $bool) 完全只读，开启后不会弹出选择器，只在没有设置 open 属性下生效
 * @method $this editable(Boolean $bool) 文本框是否可以输入, 默认为false
 * @method $this transfer(Boolean $bool) 是否将弹层放置于 body 内，在 Tabs、带有 fixed 的 Table 列内使用时，建议添加此属性，它将不受父级样式影响，从而达到更好的效果, 默认为false
 *
 */
class TimePicker extends FormComponentDriver
{
    /**
     * @var string
     */
    protected $name = 'timePicker';

    /**
     *
     */
    const TYPE_TIME = 'time';
    /**
     *
     */
    const TYPE_TIME_RANGE = 'timerange';

    /**
     * @var array
     */
    protected $props = [
        'type' => self::TYPE_TIME,
        'editable' => false,
        'confirm' => true
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
    ];

    /**
     *
     */
    protected function init()
    {
        $this->placeholder($this->getPlaceHolder());
    }

    /**
     * 下拉列表的时间间隔，数组的三项分别对应小时、分钟、秒。
     * 例如设置为 [1, 15] 时，分钟会显示：00、15、30、45。
     *
     * @param     $h
     * @param int $i
     * @param int $s
     * @return $this
     */
    public function steps($h, $i = 0, $s = 0)
    {
        $this->props['steps'] = [$h, $i, $s];
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
        if ($this->props['type'] == 'timerange')
            return Validate::arr();
        else
            return Validate::str();
    }


    public function required($message = null)
    {
        $message = $message ?: $this->getPlaceHolder();
        if ($this->props['type'] == 'timerange') {
            $this->validate()->fields([
                '0' => ['required' => true, 'message' => $message],
                '1' => ['required' => true, 'message' => $message]
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
        $value = $this->value;
        if (is_array($value) && count(array_filter($value)) == 0)
            $value = ['', ''];
        return [
            'type' => $this->name,
            'field' => $this->field,
            'title' => $this->title,
            'value' => $value,
            'props' => (object)$this->props,
            'validate' => $this->validate,
            'col' => $this->col
        ];
    }

}