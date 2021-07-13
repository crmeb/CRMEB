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
final class Col implements AnnotationInterface
{
    public $props = 24;

    protected function getCol()
    {
        if (is_integer($this->props))
            return ['span' => $this->props];
        else
            return $this->props;
    }

    public function parseRule($rule)
    {
        $rule['col'] = $this->getCol();

        return $rule;
    }

    public function parseComponent($component)
    {
        $component->col($this->getCol());

        return $component;
    }
}