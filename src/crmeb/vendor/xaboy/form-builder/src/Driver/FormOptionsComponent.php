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

namespace FormBuilder\Driver;


use FormBuilder\Contract\FormOptionsComponentInterface;
use FormBuilder\Rule\OptionsRule;

abstract class FormOptionsComponent extends FormComponent implements FormOptionsComponentInterface
{
    use OptionsRule;

    public function getRule()
    {
        $rule = parent::getRule();

        return array_merge($rule, $this->parseOptionsRule());
    }
}