<?php
/**
 * FormBuilder表单生成器
 * Author: xaboy
 * Github: https://github.com/xaboy/form-builder
 */

namespace FormBuilder;

use FormBuilder\components\Col;
use FormBuilder\interfaces\FormComponentInterFace;
use FormBuilder\traits\component\CallPropsTrait;

/**
 * Class FormComponentDriver
 * @package FormBuilder
 */
abstract class FormComponentDriver implements FormComponentInterFace
{
    use CallPropsTrait;
    /**
     * 字段名
     * @var String
     */
    protected $field;

    /**
     * 字段昵称
     * @var String
     */
    protected $title;

    /**
     * 组件名称
     * @var String
     */
    protected $name;

    /**
     * 组件的规则
     * @var array
     */
    protected $props = [];

    /**
     * 字段的值
     * @var
     */
    protected $value = '';

    /**
     * 栅格规则
     * @var array
     */
    protected $col = [];

    /**
     * 字段验证规则
     * @var array
     */
    protected $validate = [];

    /**
     * 组件属性设置规则
     * @var array
     */
    protected static $propsRule = [];

    /**
     * FormComponentDriver constructor.
     * @param String $field 字段名
     * @param String $title 字段昵称
     * @param String $value 字段值
     */
    public function __construct($field, $title, $value = null)
    {
        $this->field = (string)$field;
        $this->title = (string)$title;
        static::value($value);
        static::init();
    }

    /**
     * 组件初始化
     */
    protected function init()
    {

    }

    /**
     * @param $span
     * @return $this
     */
    public function col($span)
    {
        if($span instanceof Col)
            $this->col = $span->build();
        else if(is_numeric($span))
            $this->col['span'] = $span;
        return $this;
    }


    /**
     * 批量设置组件的规则
     * @param array $props
     * @return $this
     */
    public function setProps(array $props = [])
    {
        foreach ($props as $k => $v) {
            $this->{$k}($v);
        }
        return $this;
    }

    /**
     * 获取组件的规则
     * @param $name
     * @return mixed|null
     */
    public function getProps($name)
    {
        return isset($this->props[$name]) ? $this->props[$name] : null;
    }

    /**
     * 设置组件的值
     * @param $value
     * @param string $default
     * @return $this
     */
    public function value($value)
    {
        if (is_null($value)) $value = '';
        $this->value = (string)$value;
        return $this;
    }

    /**
     * 获取组件的值
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * 获取组件的字段名
     * @return String
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * 设置组件的昵称
     * @return String
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param bool $required
     * @return $this
     */
    public function required($required = true)
    {
        $this->props['required'] = (bool)$required;
        return $this;
    }

    /**
     * 设置组件的值为必填
     * @param null $message
     * @return $this
     */
    protected function setRequired($message = '', $trigger = 'change', $type = null)
    {
        $validate = [
            'required' => true,
            'message' => $message,
            'trigger' => $trigger
        ];
        if ($type !== null) $validate['type'] = $type;
        $this->validate[] = $validate;
        return $this;
    }

    /**
     * 添加验证规则
     * @param array $validate
     * @return $this
     */
    public function validate(array $validate)
    {
        $this->validate = array_merge($this->validate,$validate);
        return $this;
    }

}