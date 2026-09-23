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
declare (strict_types = 1);

namespace think\db\concern;

use Closure;
use think\db\BaseQuery;
use think\db\Raw;

trait WhereQuery
{
    /**
     * 指定AND查询条件.
     *
     * @param mixed $field     查询字段
     * @param mixed $op        查询表达式
     * @param mixed $condition 查询条件
     *
     * @return $this
     */
    public function where($field, $op = null, $condition = null)
    {
        if ($field instanceof self) {
            $this->parseQueryWhere($field);
            return $this;
        } elseif (true === $field || 1 === $field) {
            $this->options['where']['AND'][] = true;
            return $this;
        } elseif (empty($field)) {
            return $this;
        }

        $pk = $this->getPk();
        if ((is_null($condition) || '=' == $op) && is_string($pk) && $pk == $field) {
            $this->options['key'] = is_null($condition) ? $op : $condition;
        }

        $logic = 'AND';
        $param = func_get_args();
        array_shift($param);

        if (is_array($field) && !empty($field) && array_is_list($field)) {
            return $this->where(function ($query) use ($param, $condition, $op, $field, $logic) {
                return $query->parseWhereExp($logic, $field, $op, $condition, $param);
            });
        }

        return $this->parseWhereExp($logic, $field, $op, $condition, $param);
    }

    /**
     * 解析Query对象查询条件.
     *
     * @param BaseQuery $query 查询对象
     *
     * @return void
     */
    protected function parseQueryWhere(BaseQuery $query): void
    {
        $this->options['where'] = $query->getOption('where', []);

        $via = $query->getOption('via');
        if ($via) {
            foreach ($this->options['where'] as $logic => &$where) {
                foreach ($where as $key => &$val) {
                    if (is_array($val) && !str_contains($val[0], '.')) {
                        $val[0] = $via . '.' . $val[0];
                    }
                }
            }
        }

        $this->bind($query->getBind(false));
    }

    /**
     * 指定OR查询条件.
     *
     * @param mixed $field     查询字段
     * @param mixed $op        查询表达式
     * @param mixed $condition 查询条件
     *
     * @return $this
     */
    public function whereOr($field, $op = null, $condition = null)
    {
        $logic = 'OR';
        $param = func_get_args();
        array_shift($param);

        if (is_array($field) && !empty($field) && array_is_list($field)) {
            return $this->where(function ($query) use ($param, $condition, $op, $field, $logic) {
                return $query->parseWhereExp($logic, $field, $op, $condition, $param);
            });
        }

        return $this->parseWhereExp($logic, $field, $op, $condition, $param);
    }

    /**
     * 指定XOR查询条件.
     *
     * @param mixed $field     查询字段
     * @param mixed $op        查询表达式
     * @param mixed $condition 查询条件
     *
     * @return $this
     */
    public function whereXor($field, $op = null, $condition = null)
    {
        $logic = 'XOR';
        $param = func_get_args();
        array_shift($param);

        if (is_array($field) && !empty($field) && array_is_list($field)) {
            return $this->where(function ($query) use ($param, $condition, $op, $field, $logic) {
                return $query->parseWhereExp($logic, $field, $op, $condition, $param);
            });
        }

        return $this->parseWhereExp($logic, $field, $op, $condition, $param);
    }

    /**
     * 指定Null查询条件.
     *
     * @param mixed  $field 查询字段
     * @param string $logic 查询逻辑 and or xor
     *
     * @return $this
     */
    public function whereNull(string $field, string $logic = 'AND')
    {
        return $this->parseWhereExp($logic, $field, 'NULL', null, [], true);
    }

    /**
     * 指定NotNull查询条件.
     *
     * @param mixed  $field 查询字段
     * @param string $logic 查询逻辑 and or xor
     *
     * @return $this
     */
    public function whereNotNull(string $field, string $logic = 'AND')
    {
        return $this->parseWhereExp($logic, $field, 'NOTNULL', null, [], true);
    }

    /**
     * 指定Exists查询条件.
     *
     * @param mixed  $condition 查询条件
     * @param string $logic     查询逻辑 and or xor
     *
     * @return $this
     */
    public function whereExists($condition, string $logic = 'AND')
    {
        if (is_string($condition)) {
            $condition = new Raw($condition);
        }

        $this->options['where'][strtoupper($logic)][] = ['', 'EXISTS', $condition];

        return $this;
    }

    /**
     * 指定NotExists查询条件.
     *
     * @param mixed  $condition 查询条件
     * @param string $logic     查询逻辑 and or xor
     *
     * @return $this
     */
    public function whereNotExists($condition, string $logic = 'AND')
    {
        if (is_string($condition)) {
            $condition = new Raw($condition);
        }

        $this->options['where'][strtoupper($logic)][] = ['', 'NOT EXISTS', $condition];

        return $this;
    }

    /**
     * 指定In查询条件.
     *
     * @param mixed  $field     查询字段
     * @param mixed  $condition 查询条件
     * @param string $logic     查询逻辑 and or xor
     *
     * @return $this
     */
    public function whereIn(string $field, $condition, string $logic = 'AND')
    {
        return $this->parseWhereExp($logic, $field, 'IN', $condition, [], true);
    }

    /**
     * 指定NotIn查询条件.
     *
     * @param mixed  $field     查询字段
     * @param mixed  $condition 查询条件
     * @param string $logic     查询逻辑 and or xor
     *
     * @return $this
     */
    public function whereNotIn(string $field, $condition, string $logic = 'AND')
    {
        return $this->parseWhereExp($logic, $field, 'NOT IN', $condition, [], true);
    }

    /**
     * 指定Like查询条件.
     *
     * @param mixed  $field     查询字段
     * @param mixed  $condition 查询条件
     * @param string $logic     查询逻辑 and or xor
     *
     * @return $this
     */
    public function whereLike(string $field, $condition, string $logic = 'AND')
    {
        return $this->parseWhereExp($logic, $field, 'LIKE', $condition, [], true);
    }

    /**
     * 指定NotLike查询条件.
     *
     * @param mixed  $field     查询字段
     * @param mixed  $condition 查询条件
     * @param string $logic     查询逻辑 and or xor
     *
     * @return $this
     */
    public function whereNotLike(string $field, $condition, string $logic = 'AND')
    {
        return $this->parseWhereExp($logic, $field, 'NOT LIKE', $condition, [], true);
    }

    /**
     * 指定Between查询条件.
     *
     * @param mixed  $field     查询字段
     * @param mixed  $condition 查询条件
     * @param string $logic     查询逻辑 and or xor
     *
     * @return $this
     */
    public function whereBetween(string $field, $condition, string $logic = 'AND')
    {
        return $this->parseWhereExp($logic, $field, 'BETWEEN', $condition, [], true);
    }

    /**
     * 指定NotBetween查询条件.
     *
     * @param mixed  $field     查询字段
     * @param mixed  $condition 查询条件
     * @param string $logic     查询逻辑 and or xor
     *
     * @return $this
     */
    public function whereNotBetween(string $field, $condition, string $logic = 'AND')
    {
        return $this->parseWhereExp($logic, $field, 'NOT BETWEEN', $condition, [], true);
    }

    /**
     * 指定FIND_IN_SET查询条件.
     *
     * @param mixed  $field     查询字段
     * @param mixed  $condition 查询条件
     * @param string $logic     查询逻辑 and or xor
     *
     * @return $this
     */
    public function whereFindInSet(string $field, $condition, string $logic = 'AND')
    {
        return $this->parseWhereExp($logic, $field, 'FIND IN SET', $condition, [], true);
    }

    /**
     * 指定json_contains查询条件.
     *
     * @param mixed  $field     查询字段
     * @param mixed  $condition 查询条件
     * @param string $logic     查询逻辑 and or xor
     *
     * @return $this
     */
    public function whereJsonContains(string $field, $condition, string $logic = 'AND')
    {
        $value = is_null($condition) ? 'NULL' : '\'' . json_encode($condition) . '\'';

        if (str_contains($field, '->')) {
            [$field, $path] = explode('->', $field, 2);
            return $this->whereRaw('json_contains(' . $field . ', ' . $value . ', \'$.'. str_replace('->', '.', $path) . '\')', [], $logic);
        }
        return $this->whereRaw('json_contains(' . $field . ', ' . $value . ')', [], $logic);
    }

    public function whereOrJsonContains(string $field, $condition)
    {
        return $this->whereJsonContains($field, $condition, 'OR');
    }

    /**
     * 比较两个字段.
     *
     * @param string $field1   查询字段
     * @param string $operator 比较操作符
     * @param string $field2   比较字段
     * @param string $logic    查询逻辑 and or xor
     *
     * @return $this
     */
    public function whereColumn(string $field1, string $operator, ?string $field2 = null, string $logic = 'AND')
    {
        if (is_null($field2)) {
            $field2   = $operator;
            $operator = '=';
        }

        return $this->parseWhereExp($logic, $field1, 'COLUMN', [$operator, $field2], [], true);
    }

    /**
     * 设置软删除字段及条件.
     *
     * @param string $field     查询字段
     * @param mixed  $condition 查询条件
     *
     * @return $this
     */
    public function useSoftDelete(string $field, $condition = null)
    {
        if ($field) {
            $this->options['soft_delete'] = [$field, $condition];
        }

        return $this;
    }

    /**
     * 包含软删除数据.
     *
     * @return $this
     */
    public function withTrashed()
    {
        if ($this->model) {
            $this->options['soft_delete'] = null;
        }

        return $this;
    }

    /**
     * 指定Exp查询条件.
     *
     * @param mixed  $field 查询字段
     * @param string $where 查询条件
     * @param array  $bind  参数绑定
     * @param string $logic 查询逻辑 and or xor
     *
     * @return $this
     */
    public function whereExp(string $field, string $where, array $bind = [], string $logic = 'AND')
    {
        $this->options['where'][$logic][] = [$field, 'EXP', new Raw($where, $bind)];

        return $this;
    }

    /**
     * 指定字段Raw查询.
     *
     * @param string $field     查询字段表达式
     * @param mixed  $op        查询表达式
     * @param string $condition 查询条件
     * @param string $logic     查询逻辑 and or xor
     *
     * @return $this
     */
    public function whereFieldRaw(string $field, $op, $condition = null, string $logic = 'AND')
    {
        if (is_null($condition)) {
            $condition = $op;
            $op        = '=';
        }

        $this->options['where'][$logic][] = [new Raw($field), $op, $condition];

        return $this;
    }

    /**
     * 指定表达式查询条件.
     *
     * @param string $where 查询条件
     * @param array  $bind  参数绑定
     * @param string $logic 查询逻辑 and or xor
     *
     * @return $this
     */
    public function whereRaw(string $where, array $bind = [], string $logic = 'AND')
    {
        $this->options['where'][$logic][] = new Raw($where, $bind);

        return $this;
    }

    /**
     * 指定表达式查询条件 OR.
     *
     * @param string $where 查询条件
     * @param array  $bind  参数绑定
     *
     * @return $this
     */
    public function whereOrRaw(string $where, array $bind = [])
    {
        return $this->whereRaw($where, $bind, 'OR');
    }

    /**
     * 分析查询表达式.
     *
     * @param string $logic     查询逻辑 and or xor
     * @param mixed  $field     查询字段
     * @param mixed  $op        查询表达式
     * @param mixed  $condition 查询条件
     * @param array  $param     查询参数
     * @param bool   $strict    严格模式
     *
     * @return $this
     */
    protected function parseWhereExp(string $logic, $field, $op, $condition, array $param = [], bool $strict = false): self
    {
        $logic = strtoupper($logic);

        // 字段映射
        $map   = $this->getOption('field_map', []);
        if (is_string($field) && isset($map[$field])) {
            $field = $map[$field];
        }

        // 处理 via
        if (is_string($field) && !empty($this->options['via']) && !str_contains($field, '.') && !str_contains($field, '->')) {
            $field = $this->options['via'] . '.' . $field;
        }

        // 严格模式查询
        if ($strict) {
            return $this->parseStrictWhere($field, $op, $condition, $logic);
        }

        // 处理批量查询
        if (is_array($field)) {
            return $this->parseArrayWhereItems($field, $logic);
        }

        // 处理闭包查询
        if ($field instanceof Closure) {
            $where = $field;
        } elseif (is_string($field)) {
            if ($condition instanceof Raw) {
            } elseif (preg_match('/[,=\<\'\"\(\s]/', $field)) {
                return $this->whereRaw($field, is_array($op) ? $op : [], $logic);
            } elseif (is_string($op) && strtolower($op) === 'exp' && !is_null($condition)) {
                $bind = isset($param[2]) && is_array($param[2]) ? $param[2] : [];
                return $this->whereExp($field, $condition, $bind, $logic);
            }

            $where = $this->parseWhereItem($logic, $field, $op, $condition, $param);
        }

        // 添加条件到查询选项
        if (!empty($where)) {
            $this->options['where'][$logic][] = $where;
        }

        return $this;
    }

    protected function parseStrictWhere($field, $op, $condition, string $logic): self
    {
        if ('=' === $op) {
            $where = $this->whereEq($field, $condition);
        } else {
            $where = [$field, $op, $condition, $logic];
        }

        $this->options['where'][$logic][] = $where;

        return $this;
    }

    /**
     * 分析查询表达式.
     *
     * @param string $logic     查询逻辑 and or xor
     * @param mixed  $field     查询字段
     * @param mixed  $op        查询表达式
     * @param mixed  $condition 查询条件
     * @param array  $param     查询参数
     *
     * @return array
     */
    protected function parseWhereItem(string $logic, $field, $op, $condition, array $param = []): array
    {
        if (is_array($op)) {
            // 同一字段多条件查询
            array_unshift($param, $field);
            return $param;
        }

        if (is_string($field) && strpos($field, '->')) {
            [$relation, $attr] = explode('->', $field, 2);

            $type = $this->getFieldType($relation);
            if (is_null($type)) {
                // 自动关联查询
                $this->hasWhere($relation, [[$attr , is_null($condition) ? '=' : $op, $condition ?? $op]]);                    
                return [];
            }
        }

        if ($field && is_null($condition)) {
            if (is_string($op) && in_array(strtoupper($op), ['NULL', 'NOTNULL', 'NOT NULL'], true)) {
                // null查询
                $where = [$field, $op, ''];
            } elseif ('=' === $op || is_null($op)) {
                $where = [$field, 'NULL', ''];
            } elseif ('<>' === $op) {
                $where = [$field, 'NOTNULL', ''];
            } else {
                // 字段相等查询
                $where = $this->whereEq($field, $op);
            }
            return $where;
        }

        if (is_string($op) && in_array(strtoupper($op), ['EXISTS', 'NOT EXISTS', 'NOTEXISTS'], true)) {
            $where = [$field, $op, is_string($condition) ? new Raw($condition) : $condition];
        } else {
            $where = $field ? [$field, $op, $condition, $param[2] ?? null] : [];
        }

        return $where;
    }

    /**
     * 相等查询的主键处理.
     *
     * @param string $field 字段名
     * @param mixed  $value 字段值
     *
     * @return array
     */
    protected function whereEq(string $field, $value): array
    {
        if ($this->getPk() == $field) {
            $this->options['key'] = $value;
        }

        return [$field, '=', $value];
    }

    /**
     * 数组批量查询.
     *
     * @param array  $field 批量查询
     * @param string $logic 查询逻辑 and or xor
     *
     * @return $this
     */
    protected function parseArrayWhereItems(array $field, string $logic)
    {
        $where = [];
        foreach ($field as $key => $val) {
            if (is_int($key)) {
                $where[] = $val;
            } elseif ($val instanceof Raw) {
                $where[] = [$key, 'exp', $val];
            } else {
                $where[] = is_null($val) ? [$key, 'NULL', ''] : [$key, is_array($val) ? 'IN' : '=', $val];
            }
        }

        if (!empty($where)) {
            $this->options['where'][$logic] = isset($this->options['where'][$logic]) ?
            array_merge($this->options['where'][$logic], $where) : $where;
        }

        return $this;
    }

    /**
     * 去除某个查询条件.
     *
     * @param string $field 查询字段
     * @param string $logic 查询逻辑 and or xor
     *
     * @return $this
     */
    public function removeWhereField(string $field, string $logic = 'AND')
    {
        $logic = strtoupper($logic);

        if (isset($this->options['where'][$logic])) {
            foreach ($this->options['where'][$logic] as $key => $val) {
                if (is_array($val) && $val[0] == $field) {
                    unset($this->options['where'][$logic][$key]);
                }
            }
        }

        return $this;
    }

    /**
     * 条件查询.
     *
     * @param mixed         $condition 满足条件（支持闭包）
     * @param Closure|array $query     满足条件后执行的查询表达式（闭包或数组）
     * @param Closure|array $otherwise 不满足条件后执行
     *
     * @return $this
     */
    public function when($condition, Closure | array $query, Closure | array | null $otherwise = null)
    {
        // 处理条件为 Closure 的情况
        if ($condition instanceof Closure) {
            $condition = $condition($this);
        }

        // 根据条件决定执行哪个查询
        if ($condition) {
            $this->executeQuery($query, $condition);
        } elseif ($otherwise) {
            $this->executeQuery($otherwise, $condition);
        }

        return $this;
    }

    protected function executeQuery(Closure | array $query, $condition): void
    {
        if ($query instanceof Closure) {
            $query($this, $condition);
        } elseif (is_array($query)) {
            $this->where($query);
        }
    }
}
