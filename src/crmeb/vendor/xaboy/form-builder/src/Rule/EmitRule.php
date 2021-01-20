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


trait EmitRule
{
    /**
     * 组件模式下配置使用emit方式触发的事件名
     * @var array
     */
    protected $emit = [];

    /**
     * 自定义组件emit事件的前缀
     * @var
     */
    protected $emitPrefix;

    public function emit(array $emits)
    {
        $this->emit = array_merge($this->emit, array_map('strval', $emits));
        return $this;
    }

    public function appendEmit($emit)
    {
        $this->emit[] = (string)$emit;
        return $this;
    }

    public function emitPrefix($prefix)
    {
        $this->emitPrefix = (string)$prefix;
        return $prefix;
    }

    public function getEmit()
    {
        return $this->emit;
    }

    public function getEmitPrefix()
    {
        return $this->emitPrefix;
    }

    public function parseEmitRule()
    {
        $rule = [];
        if (count($this->emit))
            $rule['emit'] = $this->emit;
        if (!is_null($this->emitPrefix))
            $rule['emitPrefix'] = $this->emitPrefix;

        return $rule;
    }
}