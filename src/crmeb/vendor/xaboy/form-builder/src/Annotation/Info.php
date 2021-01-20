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
final class Info implements AnnotationInterface
{
    /**
     * @var string
     */
    public $info = '';

    public function parseRule($rule)
    {
        $rule['info'] = $this->info;

        return $rule;
    }

    public function parseComponent($component)
    {
        $component->info($this->info);

        return $component;
    }
}