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

namespace FormBuilder\Annotation\Validate;


use FormBuilder\Contract\AnnotationInterface;

abstract class ValidateAnnotation implements AnnotationInterface
{

    /**
     * @var string
     */
    public $message;

    /**
     * @var string
     */
    public $type = 'string';

    /**
     * @var string
     */
    public $trigger = 'change';


    public function tidyValidate($rule)
    {
        if (!isset($rule['validate']) || !is_array($rule['validate'])) {
            $rule['validate'] = [];
        }
        return $rule;
    }
}