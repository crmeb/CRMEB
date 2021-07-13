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
final class ClassName implements AnnotationInterface
{

    /**
     * @var string
     */
    public $className = '';

    public function parseRule($rule)
    {
        $rule['className'] = $this->className;
        return $rule;
    }

    public function parseComponent($component)
    {
        $component->className($this->className);
        return $component;
    }
}