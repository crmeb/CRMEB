<?php
/**
 * FormBuilder表单生成器
 * Author: xaboy
 * Github: https://github.com/xaboy/form-builder
 */

namespace FormBuilder\components;

use FormBuilder\interfaces\FormComponentInterFace;


class Validate implements FormComponentInterFace
{

    const TYPE_STRING = 'string';

    const TYPE_ARRAY = 'array';

    const TYPE_NUMBER = 'number';

    const TYPE_DATE = 'date';

    const TRIGGER_CHANGE = 'change';

    const TRIGGER_BLUR = 'blur';

    protected $validate = [];

    protected $type;

    protected $trigger;

    public function __construct($type, $trigger)
    {
        $this->type = $type;
        $this->trigger = $trigger;
    }

    public static function str($trigger = self::TRIGGER_CHANGE)
    {
        return new self(self::TYPE_STRING, $trigger);
    }

    public static function arr($trigger = self::TRIGGER_CHANGE)
    {
        return new self(self::TYPE_ARRAY, $trigger);
    }

    public static function num($trigger = self::TRIGGER_CHANGE)
    {
        return new self(self::TYPE_NUMBER, $trigger);
    }

    public static function date($trigger = self::TRIGGER_CHANGE)
    {
        return new self(self::TYPE_DATE, $trigger);
    }

    public function set($validate, $message = null)
    {
        $this->validate[] = $validate + [
                'trigger' => $this->trigger,
                'type' => $this->type,
                'message' => $message
            ];

        return $this;
    }

    public function fields(array $fields, $required = null, $message = null)
    {
        $data = [];
        if (!is_null($required))
            $data['required'] = $required;
        if (is_null($message))
            $data['message'] = $message;
        $data['fields'] = (object)$fields;

        return $this->set($data);
    }

    /**
     * 必须为链接
     *
     * @param  string|null $message
     * @return $this
     */
    public function url($message = null)
    {
        $this->set([
            'type' => 'url'
        ], $message);
        return $this;
    }

    /**
     * 必须为邮箱
     *
     * @param string|null $message
     * @return $this
     */
    public function email($message = null)
    {
        $this->set([
            'type' => 'email'
        ], $message);
        return $this;
    }

    /**
     * 必填
     *
     * @param string|null $message
     * @return $this
     */
    public function required($message = null)
    {
        $this->set([
            'required' => true,
        ], $message);
        return $this;
    }

    /**
     * 长度或值必须在这个范围内
     *
     * @param int         $min
     * @param int         $max
     * @param string|null $message
     * @return $this
     */
    public function range($min, $max, $message = null)
    {
        $this->set([
            'min' => (int)$min,
            'max' => (int)$max,
        ], $message);
        return $this;
    }

    /**
     * 长度或值必须大于这个值
     *
     * @param int         $min
     * @param string|null $message
     * @return $this
     */
    public function min($min, $message = null)
    {
        $this->set([
            'min' => (int)$min,
        ], $message);
        return $this;
    }

    /**
     * 长度或值必须小于这个值
     *
     * @param int         $max
     * @param string|null $message
     * @return $this
     */
    public function max($max, $message = null)
    {
        $this->set([
            'max' => (int)$max,
        ], $message);
        return $this;
    }

    /**
     * 长度或值必须等于这个值
     *
     * @param int         $length
     * @param string|null $message
     * @return $this
     */
    public function length($length, $message = null)
    {
        $this->set([
            'len' => (int)$length
        ], $message);
        return $this;
    }

    /**
     * 值必须在 list 中
     *
     * @param array       $list
     * @param string|null $message
     * @return $this
     */
    public function enum($list, $message = null)
    {
        $this->set([
            'type' => 'enum',
            'enum' => (array)$list
        ], $message);
        return $this;
    }

    public function build()
    {
        return $this->validate;
    }
}