<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------

namespace think\migration\db;

use Phinx\Db\Adapter\AdapterInterface;
use Phinx\Db\Adapter\MysqlAdapter;

class Column extends \Phinx\Db\Table\Column
{
    protected $unique = false;

    public function setNullable()
    {
        return $this->setNull(true);
    }

    public function setUnsigned()
    {
        return $this->setSigned(false);
    }

    public function setUnique()
    {
        $this->unique = true;
        return $this;
    }

    public function getUnique()
    {
        return $this->unique;
    }

    public function isUnique()
    {
        return $this->getUnique();
    }

    public static function make($name, $type, $options = [])
    {
        $column = new self();
        $column->setName($name);
        $column->setType($type);
        $column->setOptions($options);
        return $column;
    }

    public static function bigInteger($name)
    {
        return self::make($name, AdapterInterface::PHINX_TYPE_BIG_INTEGER);
    }

    public static function binary($name)
    {
        return self::make($name, AdapterInterface::PHINX_TYPE_BLOB);
    }

    public static function boolean($name)
    {
        return self::make($name, AdapterInterface::PHINX_TYPE_BOOLEAN);
    }

    public static function char($name, $length = 255)
    {
        return self::make($name, AdapterInterface::PHINX_TYPE_CHAR, compact('length'));
    }

    public static function date($name)
    {
        return self::make($name, AdapterInterface::PHINX_TYPE_DATE);
    }

    public static function dateTime($name)
    {
        return self::make($name, AdapterInterface::PHINX_TYPE_DATETIME);
    }

    public static function decimal($name, $precision = 8, $scale = 2)
    {
        return self::make($name, AdapterInterface::PHINX_TYPE_DECIMAL, compact('precision', 'scale'));
    }

    public static function enum($name, array $values)
    {
        return self::make($name, AdapterInterface::PHINX_TYPE_ENUM, compact('values'));
    }

    public static function float($name)
    {
        return self::make($name, AdapterInterface::PHINX_TYPE_FLOAT);
    }

    public static function integer($name)
    {
        return self::make($name, AdapterInterface::PHINX_TYPE_INTEGER);
    }

    public static function json($name)
    {
        return self::make($name, AdapterInterface::PHINX_TYPE_JSON);
    }

    public static function jsonb($name)
    {
        return self::make($name, AdapterInterface::PHINX_TYPE_JSONB);
    }

    public static function longText($name)
    {
        return self::make($name, AdapterInterface::PHINX_TYPE_TEXT, ['length' => MysqlAdapter::TEXT_LONG]);
    }

    public static function mediumInteger($name)
    {
        return self::make($name, AdapterInterface::PHINX_TYPE_INTEGER, ['length' => MysqlAdapter::INT_MEDIUM]);
    }

    public static function mediumText($name)
    {
        return self::make($name, AdapterInterface::PHINX_TYPE_TEXT, ['length' => MysqlAdapter::TEXT_MEDIUM]);
    }

    public static function smallInteger($name)
    {
        return self::make($name, AdapterInterface::PHINX_TYPE_INTEGER, ['length' => MysqlAdapter::INT_SMALL]);
    }

    public static function string($name, $length = 255)
    {
        return self::make($name, AdapterInterface::PHINX_TYPE_STRING, compact('length'));
    }

    public static function text($name)
    {
        return self::make($name, AdapterInterface::PHINX_TYPE_TEXT);
    }

    public static function time($name)
    {
        return self::make($name, AdapterInterface::PHINX_TYPE_TIME);
    }

    public static function tinyInteger($name)
    {
        return self::make($name, AdapterInterface::PHINX_TYPE_INTEGER, ['length' => MysqlAdapter::INT_TINY]);
    }

    public static function unsignedInteger($name)
    {
        return self::integer($name)->setUnSigned();
    }

    public static function timestamp($name)
    {
        return self::make($name, AdapterInterface::PHINX_TYPE_TIMESTAMP, ['null' => true, 'default' => null]);
    }

    public static function uuid($name)
    {
        return self::make($name, AdapterInterface::PHINX_TYPE_UUID);
    }

}
