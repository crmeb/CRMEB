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


use FormBuilder\Contract\OptionComponentInterface;

class Option implements OptionComponentInterface
{
    /**
     * @var array
     */
    protected $rule;

    /**
     * Option constructor.
     *
     * @param string|number $value
     * @param string $label
     * @param bool $disabled
     */
    public function __construct($value, $label = '', $disabled = null)
    {
        $this->rule = compact('label', 'value');
        if (!is_null($disabled))
            $this->disabled($disabled);
    }

    /**
     * @param bool $disabled
     * @return $this
     */
    public function disabled($disabled = true)
    {
        $this->rule['disabled'] = !!$disabled;
        return $this;
    }

    /**
     * @return array
     */
    public function getOption()
    {
        return $this->rule;
    }
}