<?php

namespace AlibabaCloud\Tea;

class Model
{
    protected $_name     = [];
    protected $_required = [];

    public function __construct($config = [])
    {
        if (!empty($config)) {
            foreach ($config as $k => $v) {
                $this->{$k} = $v;
            }
        }
    }

    public function getName($name = null)
    {
        if (null === $name) {
            return $this->_name;
        }

        return isset($this->_name[$name]) ? $this->_name[$name] : $name;
    }

    public function toMap()
    {
        $map = get_object_vars($this);
        foreach ($map as $k => $m) {
            if (0 === strpos($k, '_')) {
                unset($map[$k]);
            }
        }
        $res = [];
        foreach ($map as $k => $v) {
            $name       = isset($this->_name[$k]) ? $this->_name[$k] : $k;
            $res[$name] = $v;
        }

        return $res;
    }

    public function validate()
    {
        $vars = get_object_vars($this);
        foreach ($vars as $k => $v) {
            if (isset($this->_required[$k]) && $this->_required[$k] && empty($v)) {
                throw new \InvalidArgumentException("{$k} is required.");
            }
        }
    }

    public static function validateRequired($fieldName, $field, $val = null)
    {
        if (true === $val && null === $field) {
            throw new \InvalidArgumentException($fieldName . ' is required');
        }
    }

    public static function validateMaxLength($fieldName, $field, $val = null)
    {
        if (null !== $field && \strlen($field) > (int) $val) {
            throw new \InvalidArgumentException($fieldName . ' is exceed max-length: ' . $val);
        }
    }

    public static function validateMinLength($fieldName, $field, $val = null)
    {
        if (null !== $field && \strlen($field) < (int) $val) {
            throw new \InvalidArgumentException($fieldName . ' is less than min-length: ' . $val);
        }
    }

    public static function validatePattern($fieldName, $field, $regex = '')
    {
        if (null !== $field && '' !== $field && !preg_match("/^{$regex}$/", $field)) {
            throw new \InvalidArgumentException($fieldName . ' is not match ' . $regex);
        }
    }

    public static function validateMaximum($fieldName, $field, $val)
    {
        if (null !== $field && $field > $val) {
            throw new \InvalidArgumentException($fieldName . ' cannot be greater than ' . $val);
        }
    }

    public static function validateMinimum($fieldName, $field, $val)
    {
        if (null !== $field && $field < $val) {
            throw new \InvalidArgumentException($fieldName . ' cannot be less than ' . $val);
        }
    }

    /**
     * @param array $map
     * @param Model $model
     *
     * @return mixed
     */
    public static function toModel($map, $model)
    {
        $names = $model->getName();
        $names = array_flip($names);
        foreach ($map as $key => $value) {
            $name           = isset($names[$key]) ? $names[$key] : $key;
            $model->{$name} = $value;
        }

        return $model;
    }
}
