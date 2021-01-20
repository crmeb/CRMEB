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
 * 时间选择器组件
 * Class TimePicker
 *
 * @method $this type(string $type) 显示类型，可选值为 time、timerange
 * @method $this format(string $format) 展示的时间格式, 默认为HH:mm:ss
 * @method $this placement(string $placement) 时间选择器出现的位置，可选值为top, top-start, top-end, bottom, bottom-start, bottom-end, left, left-start, left-end, right, right-start, right-end, 默认为bottom-start
 * @method $this placeholder(string $placeholder) 占位文本
 * @method $this confirm(bool $bool) 是否显示底部控制栏, 默认为false
 * @method $this size(string $size) 尺寸，可选值为large、small、default或者不设置
 * @method $this disabled(bool $bool) 是否禁用选择器
 * @method $this clearable(bool $bool) 是否显示清除按钮
 * @method $this readonly(bool $bool) 完全只读，开启后不会弹出选择器，只在没有设置 open 属性下生效
 * @method $this editable(bool $bool) 文本框是否可以输入, 默认为false
 * @method $this transfer(bool $bool) 是否将弹层放置于 body 内，在 Tabs、带有 fixed 的 Table 列内使用时，建议添加此属性，它将不受父级样式影响，从而达到更好的效果, 默认为false
 *
 */
class TimePicker extends FormComponent
{
    /**
     * 时间选择
     */
    const TYPE_TIME = 'time';
    /**
     * 时间区间选择
     */
    const TYPE_TIME_RANGE = 'timerange';


    protected $selectComponent = true;

    protected $defaultProps = [
        'type' => self::TYPE_TIME,
        'editable' => false,
        'confirm' => true
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
        'transfer' => 'bool',
    ];

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

    protected function isRange()
    {
        return strtolower($this->props['type']) === 'timerange';
    }

    public function createValidate()
    {
        if ($this->isRange())
            return Iview::validateArr();
        else
            return Iview::validateStr();
    }

    public function required($message = null)
    {
        if (is_null($message)) $message = $this->getPlaceHolder();
        $validate = $this->createValidate();
        if ($this->isRange()) {
            $required = ['required' => true, 'message' => $message];
            $validate->fields([
                '0' => $required,
                '1' => $required
            ]);
            return $this;
        }

        $this->appendValidate($validate->message($message)->required());
        return $this;
    }

}