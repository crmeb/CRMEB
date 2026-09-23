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

namespace FormBuilder\UI\Elm\Components;


use FormBuilder\Driver\FormComponent;
use FormBuilder\Exception\FormBuilderException;

/**
 * hidden组件
 * Class Hidden
 *
 */
class Hidden extends FormComponent
{
    /**
     * Hidden constructor.
     *
     * @param string $field
     * @param string $value
     */
    public function __construct($field, $value)
    {
        parent::__construct($field, '', $value);
    }

    /**
     * @return array
     */
    public function getRule()
    {
        return [
            'type' => $this->type,
            'field' => $this->field,
            'value' => $this->value
        ];
    }

    /**
     * @return void
     * @throws FormBuilderException
     */
    public function createValidate()
    {
        throw new FormBuilderException('hidden 组件不支持 createValidate 方法');
    }
}