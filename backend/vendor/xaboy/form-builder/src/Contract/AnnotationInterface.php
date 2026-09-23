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

namespace FormBuilder\Contract;


interface AnnotationInterface
{
    /**
     * @param array $rule
     * @return array
     */
    public function parseRule($rule);

    /**
     * @param CustomComponentInterface $component
     * @return CustomComponentInterface
     */
    public function parseComponent($component);
}