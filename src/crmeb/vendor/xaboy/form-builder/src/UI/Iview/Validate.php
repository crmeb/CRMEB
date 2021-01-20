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

namespace FormBuilder\UI\Iview;


use FormBuilder\Contract\ValidateInterface;

class Validate implements ValidateInterface
{
    const TYPE_STRING = 'string';

    const TYPE_ARRAY = 'array';

    const TYPE_NUMBER = 'number';

    const TYPE_INTEGER = 'integer';

    const TYPE_FLOAT = 'float';

    const TYPE_OBJECT = 'object';

    const TYPE_ENUM = 'enum';

    const TYPE_URL = 'url';

    const TYPE_HEX = 'hex';

    const TYPE_EMAIL = 'email';

    const TYPE_DATE = 'date';

    const TRIGGER_CHANGE = 'change';

    const TRIGGER_BLUR = 'blur';

    const TRIGGER_SUBMIT = 'submit';

    protected $validate = [
        'fields' => []
    ];

    protected $type;

    protected $trigger;

    /**
     * Validate constructor.
     * @param string $type
     * @param string $trigger
     */
    public function __construct($type, $trigger = self::TRIGGER_CHANGE)
    {
        $this->type($type);
        $this->trigger($trigger);
    }

    /**
     * @param string $trigger
     * @return $this
     */
    public function trigger($trigger)
    {
        $this->trigger = (string)$trigger;
        return $this;
    }

    /**
     * @param $type
     * @return $this
     */
    public function type($type)
    {

        $this->type = (string)$type;
        return $this;
    }


    public function set($validate)
    {
        array_merge($this->validate, $validate);
        if (!is_array($validate['fields'])) $validate['fields'] = [];

        return $this;
    }

    public function fields(array $fields)
    {
        $this->validate['fields'] = array_merge($this->validate['fields'], $fields);
        return $this;
    }

    public function field($field, $validate)
    {
        $this->validate['fields'][$field] = $validate;
        return $this;
    }

    /**
     * 必填
     *
     * @return $this
     */
    public function required()
    {
        $this->validate['required'] = true;
        return $this;
    }

    /**
     * 长度或值必须在这个范围内
     *
     * @param int|float $min
     * @param int|float $max
     * @return $this
     */
    public function range($min, $max)
    {
        $this->validate['min'] = (float)$min;
        $this->validate['max'] = (float)$max;
        return $this;
    }

    /**
     * 长度或值必须大于这个值
     *
     * @param int|float $min
     * @return $this
     */
    public function min($min)
    {
        $this->validate['min'] = (float)$min;
        return $this;
    }

    /**
     * 长度或值必须小于这个值
     *
     * @param int|float $max
     * @return $this
     */
    public function max($max)
    {
        $this->validate['max'] = (float)$max;
        return $this;
    }

    /**
     * 长度或值必须等于这个值
     *
     * @param int $length
     * @return $this
     */
    public function length($length)
    {
        $this->validate['len'] = (int)$length;
        return $this;
    }

    /**
     * 值必须在 list 中
     *
     * @param array $list
     * @return $this
     */
    public function enum(array $list)
    {
        $this->validate['enum'] = $list;
        return $this;
    }

    /**
     * 错误信息
     * @param $message
     * @return $this
     */
    public function message($message)
    {
        $this->validate['message'] = (string)$message;
        return $this;
    }

    /**
     * 正则
     *
     * @param $pattern
     * @return $this
     */
    public function pattern($pattern)
    {
        $this->validate['pattern'] = (string)$pattern;
        return $this;
    }

    public function getValidate()
    {
        $validate = $this->validate;
        $validate['type'] = $this->type;
        $validate['trigger'] = $this->trigger;
        $fields = $validate['fields'];

        if (!($fieldCount = count($fields)) && count($validate) === 1)
            return [];

        if ($fieldCount) {
            foreach ($fields as $k => $field) {
                $fields[$k] = $field instanceof self ? $field->getValidate() : $field;
            }
            $validate['fields'] = (object)$fields;
        } else {
            unset($validate['fields']);
        }
        return $validate;
    }
}