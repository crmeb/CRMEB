<?php
/**
 * FormBuilder表单生成器
 * Author: xaboy
 * Github: https://github.com/xaboy/form-builder
 */

namespace FormBuilder;


use FormBuilder\exception\FormBuilderException;

class Helper
{
    public static function toType($var, $type = 'string')
    {
        if ($type === 'string') {
            return (string)$var;
        } else if ($type === 'array') {
            return is_array($var) ? $var : [$var];
        } else if ($type === 'boolean') {
            return (bool)$var;
        } else if ($type === 'float') {
            return (float)$var;
        } else if ($type === 'int') {
            return (int)$var;
        } else if ($type === 'null') {
            return null;
        }
        return $var;
    }

    public static function getVarType($var)
    {
        if (is_array($var)) return 'array';
        if (is_bool($var)) return 'boolean';
        if (is_float($var)) return 'float';
        if (is_int($var)) return 'integer';
        if (is_null($var)) return 'null';
        if (is_numeric($var)) return 'numeric';
        if (is_object($var)) return 'object';
        if (is_resource($var)) return 'resource';
        if (is_string($var)) return 'string';
        return "unknown type";
    }


    public static function verifyType($var, $verify, $title = '')
    {
        if (!is_array($verify)) $verify = [$verify];
        if (in_array('numeric', $verify)) {
            $verify[] = 'float';
            $verify[] = 'integer';
        }
        $type = self::getVarType($var);
        if (!in_array($type, $verify))
            throw new FormBuilderException($title . '类型需为' . implode(',', $verify));
    }

    public static function getDate($date)
    {
        return is_numeric($date) ? $date * 1000 : $date;
    }
}