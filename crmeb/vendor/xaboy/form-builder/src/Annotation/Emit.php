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

namespace FormBuilder\Annotation;

use FormBuilder\Contract\AnnotationInterface;

/**
 * @Annotation
 */
final class Emit implements AnnotationInterface
{
    /**
     * @var array
     */
    public $emit = [];

    /**
     * @var string
     */
    public $prefix = '';


    public function parseRule($rule)
    {
        $rule['emit'] = $this->emit;
        if ($this->prefix)
            $rule['emitPrefix'] = $this->prefix;

        return $rule;
    }

    public function parseComponent($component)
    {
        $component->emit($this->emit)->emitPrefix($this->prefix);
        return $component;
    }
}