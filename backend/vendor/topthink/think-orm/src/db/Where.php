<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2025 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace think\db;

use ArrayAccess;

/**
 * 数组查询对象
 */
class Where implements ArrayAccess
{
    /**
     * 创建一个查询表达式.
     *
     * @param array $where   查询条件数组
     * @param bool  $enclose 是否增加括号
     */
    public function __construct(protected array $where = [], protected bool $enclose = false)
    {
    }

    /**
     * 设置是否添加括号.
     *
     * @param bool $enclose
     *
     * @return $this
     */
    public function enclose(bool $enclose = true)
    {
        $this->enclose = $enclose;

        return $this;
    }

    /**
     * 解析为Query对象可识别的查询条件数组.
     *
     * @return array
     */
    public function parse(): array
    {
        $where = [];

        foreach ($this->where as $key => $val) {
            if ($val instanceof Raw) {
                $where[] = [$key, 'exp', $val];
            } elseif (is_null($val)) {
                $where[] = [$key, 'NULL', ''];
            } elseif (is_array($val)) {
                $where[] = $this->parseItem($key, $val);
            } else {
                $where[] = [$key, '=', $val];
            }
        }

        return $this->enclose ? [$where] : $where;
    }

    /**
     * 分析查询表达式.
     *
     * @param string $field 查询字段
     * @param array  $where 查询条件
     *
     * @return array
     */
    protected function parseItem(string $field, array $where = []): array
    {
        $op = $where[0];
        $condition = $where[1] ?? null;

        if (is_array($op)) {
            // 同一字段多条件查询
            array_unshift($where, $field);
        } elseif (is_null($condition)) {
            if (is_string($op) && in_array(strtoupper($op), ['NULL', 'NOTNULL', 'NOT NULL'], true)) {
                // null查询
                $where = [$field, $op, ''];
            } elseif (is_null($op) || '=' == $op) {
                $where = [$field, 'NULL', ''];
            } elseif ('<>' == $op) {
                $where = [$field, 'NOTNULL', ''];
            } else {
                // 字段相等查询
                $where = [$field, '=', $op];
            }
        } else {
            $where = [$field, $op, $condition];
        }

        return $where;
    }

    /**
     * 修改器 设置数据对象的值
     *
     * @param string $name  名称
     * @param mixed  $value 值
     *
     * @return void
     */
    public function __set($name, $value)
    {
        $this->where[$name] = $value;
    }

    /**
     * 获取器 获取数据对象的值
     *
     * @param string $name 名称
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->where[$name] ?? null;
    }

    /**
     * 检测数据对象的值
     *
     * @param string $name 名称
     *
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->where[$name]);
    }

    /**
     * 销毁数据对象的值
     *
     * @param string $name 名称
     *
     * @return void
     */
    public function __unset($name)
    {
        unset($this->where[$name]);
    }

    // ArrayAccess
    public function offsetSet(mixed $name, mixed $value): void
    {
        $this->__set($name, $value);
    }

    public function offsetExists(mixed $name): bool
    {
        return $this->__isset($name);
    }

    public function offsetUnset(mixed $name): void
    {
        $this->__unset($name);
    }

    public function offsetGet(mixed $name): mixed
    {
        return $this->__get($name);
    }
}
