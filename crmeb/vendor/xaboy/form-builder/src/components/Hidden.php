<?php
/**
 * FormBuilder表单生成器
 * Author: xaboy
 * Github: https://github.com/xaboy/form-builder
 */

namespace FormBuilder\components;


use FormBuilder\FormComponentDriver;

/**
 * hidden组件
 * Class Hidden
 *
 * @package FormBuilder\components
 */
class Hidden extends FormComponentDriver
{

    /**
     * @var string
     */
    protected $name = 'hidden';

    /**
     * Hidden constructor.
     *
     * @param String $field
     * @param String $value
     */
    public function __construct($field, $value)
    {
        $this->field = (string)$field;
        static::value($value);
    }

    protected function getValidateHandler()
    {

    }

    /**
     * @return array
     */
    public function build()
    {
        return [
            'type' => $this->name,
            'field' => $this->field,
            'value' => $this->value
        ];
    }
}