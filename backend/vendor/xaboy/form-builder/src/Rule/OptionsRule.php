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

namespace FormBuilder\Rule;


use FormBuilder\Contract\OptionComponentInterface;

trait OptionsRule
{
    /**
     * @var array
     */
    protected $options = [];

    /**
     * 添加选项
     *
     * @param string $value
     * @param string $label
     * @param bool $disabled
     * @return $this
     */
    public function appendOption($value, $label, $disabled = false)
    {
        $label = (string)$label;
        $this->options[] = compact('value', 'label', 'disabled');
        return $this;
    }

    /**
     * 批量添加选项
     *
     * @param array $options
     * @return $this
     */
    public function appendOptions(array $options)
    {
        $this->options = array_merge($this->options, $options);
        return $this;
    }

    /**
     * 批量设置的选项
     *
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * 批量设置选项 支持匿名函数
     *
     * @param array|callable $options
     * @return $this
     */
    public function options($options)
    {
        return $this->setOptions(is_callable($options) ? $options($this) : $options);
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param $option
     * @return array
     */
    protected function parseOption($option)
    {
        return $option instanceof OptionComponentInterface ? $option->getOption() : $option;
    }

    /**
     * @return array
     */
    protected function parseOptions()
    {
        $options = [];
        foreach ($this->options as $option) {
            $options[] = $this->parseOption($option);
        }

        return $options;
    }

    public function parseOptionsRule()
    {
        return ['options' => $this->parseOptions()];
    }
}