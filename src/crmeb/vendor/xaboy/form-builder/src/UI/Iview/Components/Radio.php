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
 * 单选框组件
 * Class Radio
 *
 * @method $this size(string $size) 单选框的尺寸，可选值为 large、small、default 或者不设置
 * @method $this vertical(bool $bool) 是否垂直排列，按钮样式下无效
 */
class Radio extends FormOptionsComponent
{
    protected $selectComponent = true;

    protected static $propsRule = [
        'size' => 'string',
        'vertical' => 'bool'
    ];

    public function createValidate()
    {
        return Iview::validateStr();
    }

    public function createValidateNum()
    {
        return Iview::validateNum();
    }

    public function requiredNum($message = '')
    {
        if (is_null($message)) $message = $this->getPlaceHolder();
        return $this->appendValidate($this->createValidateNum()->message($message)->required());
    }

    /**
     * 按钮样式
     *
     * @param bool $button
     * @return $this
     */
    public function button($button = true)
    {
        if ($button)
            $this->props['type'] = 'button';
        else
            unset($this->props['type']);
        return $this;
    }
}