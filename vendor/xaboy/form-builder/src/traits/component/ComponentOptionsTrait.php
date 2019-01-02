<?php
/**
 * FormBuilder表单生成器
 * Author: xaboy
 * Github: https://github.com/xaboy/form-builder
 */

namespace FormBuilder\traits\component;


use FormBuilder\components\Option;

/**
 * Class ComponentOptionsTrait
 *
 * @package FormBuilder\traits\component
 */
trait ComponentOptionsTrait
{
    /**
     * @var array
     */
    protected $options = [];

    /**
     * 设置的选项
     *
     * @param      $value
     * @param      $label
     * @param bool $disabled
     * @return $this
     * @throws \FormBuilder\exception\FormBuilderException
     */
    public function option($value, $label, $disabled = false)
    {
        $this->options[] = new Option($value, $label, $disabled);
        return $this;
    }


    /**
     * 批量设置的选项
     *
     * @param array $options
     * @param bool  $disabled
     * @return $this
     * @throws \FormBuilder\exception\FormBuilderException
     */
    public function options(array $options, $disabled = false)
    {
        $disabled = (bool)$disabled;
        foreach ($options as $option) {
            if ($option instanceof Option)
                $this->options[] = $option;
            else
                $this->option(
                    $option['value'],
                    $option['label'],
                    isset($option['disabled']) ? $option['disabled'] : $disabled
                );
        }
        return $this;
    }

    /**
     * 批量设置选项 支持匿名函数
     *
     * @param      $options
     * @param bool $disabled
     * @return $this
     */
    public function setOptions($options, $disabled = false)
    {
        if (is_callable($options))
            return $this->setOptions($options($this), $disabled);
        else if (is_array($options))
            return $this->options($options, $disabled);
        else
            return $this;
    }
}