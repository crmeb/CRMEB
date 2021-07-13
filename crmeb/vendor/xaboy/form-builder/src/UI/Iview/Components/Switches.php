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
 * 开关组件
 * Class Switches
 *
 * @method $this size(string $size) 开关的尺寸，可选值为large、small、default或者不写。建议开关如果使用了2个汉字的文字，使用 large。
 * @method $this disabled(bool $bool) 禁用开关, 默认为false
 * @method $this trueValue(string $value) 选中时的值，默认为1
 * @method $this falseValue(string $value) 没有选中时的值，默认为0
 */
class Switches extends FormComponent
{
    protected $selectComponent = true;

    protected $defaultProps = [
        'trueValue' => '1',
        'falseValue' => '0',
        'slot' => []
    ];

    protected static $propsRule = [
        'size' => 'string',
        'disabled' => 'bool',
        'trueValue' => '',
        'falseValue' => ''
    ];

    public function getComponentName()
    {
        return 'switch';
    }

    /**
     * 自定义显示打开时的内容
     *
     * @param string $open
     * @return $this
     */
    public function openStr($open)
    {
        $this->props['slot']['open'] = (string)$open;
        return $this;
    }

    /**
     * 自定义显示关闭时的内容
     *
     * @param string $close
     * @return $this
     */
    public function closeStr($close)
    {
        $this->props['slot']['close'] = (string)$close;
        return $this;
    }

    /**
     * @return array
     */
    public function getRule()
    {
        if (isset($this->props['slot']) && !count($this->props['slot'])) unset($this->props['slot']);
        return parent::getRule();
    }

    public function createValidate()
    {
        return Iview::validateStr();
    }

    public function createValidateNum()
    {
        return Iview::validateNum();
    }

}