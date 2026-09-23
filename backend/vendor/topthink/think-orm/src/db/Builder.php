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

namespace think\db;

use BackedEnum;
use Closure;
use Stringable;
use think\db\BaseQuery as Query;
use think\db\exception\DbException as Exception;
use UnitEnum;

/**
 * Db Builder.
 */
class Builder extends BaseBuilder
{

    /**
     * 数据分析.
     *
     * @param Query $query  查询对象
     * @param array $data   数据
     * @param array $fields 字段信息
     * @param array $bind   参数绑定
     *
     * @return array
     */
    protected function parseData(Query $query, array $data = [], array $fields = [], array $bind = []): array
    {
        if (empty($data)) {
            return [];
        }

        $options = $query->getOptions();

        // 获取绑定信息
        if (empty($bind)) {
            $bind = $query->getFieldsBindType();
        }

        if (empty($fields)) {
            if (empty($options['field']) || '*' == $options['field']) {
                $fields = array_keys($bind);
            } else {
                $fields = $options['field'];
            }
        }

        $result = [];

        foreach ($data as $key => $val) {
            $item = $this->parseKey($query, $key, true);
            if ($val instanceof UnitEnum) {
                $val = $this->parseEnum($val);
            } elseif ($val instanceof Raw) {
                $result[$item] = $this->parseRaw($query, $val);
                continue;
            } elseif (is_null($val) && in_array($key, $fields, true)) {
                $result[$item] = 'NULL';
                continue;
            } elseif (!is_scalar($val) && (in_array($key, (array) $query->getOption('json')) || 'json' == $query->getFieldType($key))) {
                $val = json_encode($val);
            }

            if (str_contains($key, '->')) {
                [$key, $name] = explode('->', $key, 2);
                $item         = $this->parseKey($query, $key);

                $result[$item . '->' . $name] = 'json_set(' . $item . ', \'$.' . $name . '\', ' . $this->parseDataBind($query, $key . '->' . $name, $val, $bind) . ')';
            } elseif (!str_contains($key, '.') && !in_array($key, $fields, true)) {
                if ($options['strict']) {
                    throw new Exception('fields not exists:[' . $key . ']');
                }
            } elseif ($val instanceof Express) {
                if ($val->getLazyTime() && in_array($val->getType(), ['+', '-'])) {
                    $step = $query->lazyWrite($key, $val->getType() == '+' ? 'inc' : 'dec', $val->getStep(), $val->getLazyTime());
                    if (false === $step) {
                        continue;
                    }
                    $result[$item] = $item . ' + ' . $step;
                } else {
                    $result[$item] = $item . $this->parseExpress($query, $val);
                }
            } elseif (is_array($val) && !empty($val) && is_string($val[0])) {
                if (in_array(strtoupper($val[0]), ['INC', 'DEC'])) {
                    $result[$item] = match (strtoupper($val[0])) {
                        'INC' => $item . ' + ' . floatval($val[1]),
                        'DEC' => $item . ' - ' . floatval($val[1]),
                    };
                }
            } elseif (is_scalar($val)) {
                // 过滤非标量数据
                if (!$query->isAutoBind() && Connection::PARAM_STR == $bind[$key]) {
                    $val = '\'' . $val . '\'';
                }
                $result[$item] = !$query->isAutoBind() ? $val : $this->parseDataBind($query, $key, $val, $bind);
            }
        }

        return $result;
    }

    /**
     * 数据绑定处理.
     *
     * @param Query  $query 查询对象
     * @param string $key   字段名
     * @param mixed  $data  数据
     * @param array  $bind  绑定数据
     *
     * @return string
     */
    protected function parseDataBind(Query $query, string $key, $data, array $bind = []): string
    {
        if ($data instanceof Raw) {
            return $this->parseRaw($query, $data);
        }

        $name = $query->bindValue($data, $bind[$key] ?? Connection::PARAM_STR);

        return ':' . $name;
    }

    /**
     * 字段名分析.
     *
     * @param Query $query  查询对象
     * @param mixed $key    字段名
     * @param bool  $strict 严格检测
     *
     * @return string
     */
    public function parseKey(Query $query, string | int | Raw $key, bool $strict = false): string
    {
        return $key;
    }

    /**
     * 查询额外参数分析.
     *
     * @param Query  $query 查询对象
     * @param string $extra 额外参数
     *
     * @return string
     */
    protected function parseExtra(Query $query, string $extra): string
    {
        return preg_match('/^[\w]+$/i', $extra) ? ' ' . strtoupper($extra) : '';
    }

    /**
     * field分析.
     *
     * @param Query $query  查询对象
     * @param array $fields 字段名
     *
     * @return string
     */
    protected function parseField(Query $query, array $fields): string
    {
        if (empty($fields)) {
            return '*';
        }

        // 支持 'field1' => 'field2' 这样的字段别名定义
        $array = array_map(function ($field, $key) use ($query) {
            if ($field instanceof Raw) {
                return $this->parseRaw($query, $field);
            } elseif (!is_numeric($key)) {
                // 字段别名定义
                return $this->parseKey($query, $key) . ' AS ' . $this->parseKey($query, $field, true);
            } else {
                return $this->parseKey($query, $field);
            }
        }, $fields, array_keys($fields));

        return implode(',', $array);
    }

    /**
     * table分析.
     *
     * @param Query $query  查询对象
     * @param array|string $tables 表名
     *
     * @return string
     */
    protected function parseTable(Query $query, array | string $tables): string
    {
        $item    = [];
        $options = $query->getOptions();

        foreach ((array) $tables as $key => $table) {
            if ($table instanceof Raw) {
                $item[] = $this->parseRaw($query, $table);
            } elseif (!is_numeric($key)) {
                $item[] = $this->parseKey($query, $key) . ' ' . $this->parseKey($query, $table);
            } elseif (isset($options['alias'][$table])) {
                $item[] = $this->parseKey($query, $table) . ' ' . $this->parseKey($query, $options['alias'][$table]);
            } else {
                $item[] = $this->parseKey($query, $table);
            }
        }

        return implode(',', $item);
    }

    /**
     * where分析.
     *
     * @param Query $query 查询对象
     * @param array $where 查询条件
     *
     * @return string
     */
    protected function parseWhere(Query $query, array $where): string
    {
        $options  = $query->getOptions();
        $whereStr = $this->buildWhere($query, $where);

        if (!empty($options['soft_delete'])) {
            // 附加软删除条件
            [$field, $condition] = $options['soft_delete'];

            $binds    = $query->getFieldsBindType();
            $whereStr = $whereStr ? '( ' . $whereStr . ' ) AND ' : '';
            $whereStr = $whereStr . $this->parseWhereItem($query, $field, $condition, $binds);
        }

        return empty($whereStr) ? '' : ' WHERE ' . $whereStr;
    }

    /**
     * where子单元分析.
     *
     * @param Query $query 查询对象
     * @param mixed $field 查询字段
     * @param array $val   查询条件
     * @param array $binds 参数绑定
     *
     * @return string
     */
    protected function parseWhereItem(Query $query, $field, array $val, array $binds = []): string
    {
        // 字段分析
        $key = $field ? $this->parseKey($query, $field, true) : '';

        [$exp, $value] = $val;

        // 检测操作符
        if (!is_string($exp)) {
            throw new Exception('where express error:' . var_export($exp, true));
        }

        $param = $val[2] ?? null;
        $exp   = strtoupper($exp);
        if (isset($this->exp[$exp])) {
            $exp = $this->exp[$exp];
        }

        if (is_string($field) && 'LIKE' != $exp) {
            $bindType = $binds[$field] ?? Connection::PARAM_STR;
        } else {
            $bindType = Connection::PARAM_STR;
        }

        if ($value instanceof Raw) {
        } elseif ($value instanceof Stringable) {
            // 对象数据写入
            $value = $value->__toString();
        } elseif ($value instanceof UnitEnum) {
            $value = $this->parseEnum($value);
        }

        if (is_scalar($value) && !in_array($exp, ['EXP', 'NOT NULL', 'NULL', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN']) && !str_contains($exp, 'TIME')) {
            if (is_string($value) && str_starts_with($value, ':') && $query->isBind(substr($value, 1))) {
            } else {
                $name  = $query->bindValue($value, $bindType);
                $value = ':' . $name;
            }
        }

        // 解析查询表达式
        foreach ($this->parser as $fun => $parse) {
            if (in_array($exp, $parse)) {
                return $this->$fun($query, $key, $exp, $value, $field, $bindType, $param);
            }
        }

        throw new Exception('where express error:' . $exp);
    }

    /**
     * 模糊查询.
     *
     * @param Query  $query    查询对象
     * @param string $key
     * @param string $exp
     * @param array  $value
     * @param string $field
     * @param int    $bindType
     * @param string $logic
     *
     * @return string
     */
    protected function parseLike(Query $query, string $key, string $exp, $value, $field, int $bindType, ?string $logic = null): string
    {
        // 模糊匹配
        $logic = $logic ?: 'AND';
        if (is_array($value)) {
            $array = [];
            foreach ($value as $item) {
                $name    = $query->bindValue($item, Connection::PARAM_STR);
                $array[] = $key . ' ' . $exp . ' :' . $name;
            }

            $whereStr = '(' . implode(' ' . strtoupper($logic) . ' ', $array) . ')';
        } else {
            $whereStr = $key . ' ' . $exp . ' ' . $value;
        }

        return $whereStr;
    }

    /**
     * 表达式查询.
     *
     * @param Query  $query    查询对象
     * @param string $key
     * @param string $exp
     * @param Raw    $value
     * @param string $field
     * @param int    $bindType
     *
     * @return string
     */
    protected function parseExp(Query $query, string $key, string $exp, Raw $value, string $field, int $bindType): string
    {
        // 表达式查询
        return '( ' . $key . ' ' . $this->parseRaw($query, $value) . ' )';
    }

    /**
     * Null查询.
     *
     * @param Query  $query    查询对象
     * @param string $key
     * @param string $exp
     * @param mixed  $value
     * @param string $field
     * @param int    $bindType
     *
     * @return string
     */
    protected function parseNull(Query $query, string $key, string $exp, $value, $field, int $bindType): string
    {
        // NULL 查询
        return $key . ' IS ' . $exp;
    }

    /**
     * 范围查询.
     *
     * @param Query  $query    查询对象
     * @param string $key
     * @param string $exp
     * @param mixed  $value
     * @param string $field
     * @param int    $bindType
     *
     * @return string
     */
    protected function parseBetween(Query $query, string $key, string $exp, array | string $value, $field, int $bindType): string
    {
        // BETWEEN 查询
        $data = is_array($value) ? $value : explode(',', $value);

        $min = $query->bindValue($data[0], $bindType);
        $max = $query->bindValue($data[1], $bindType);

        return $key . ' ' . $exp . ' :' . $min . ' AND :' . $max . ' ';
    }

    /**
     * 解析枚举类型值
     *
     * @param UnitEnum  $value
     *
     * @return mixed
     */
    protected function parseEnum(UnitEnum $value)
    {
        if ($value instanceof BackedEnum) {
            $value = $value->value;
        } else {
            $value = $value->name;
        }
        return $value;
    }

    /**
     * IN查询.
     *
     * @param Query  $query    查询对象
     * @param string $key
     * @param string $exp
     * @param mixed  $value
     * @param string $field
     * @param int    $bindType
     *
     * @return string
     */
    protected function parseIn(Query $query, string $key, string $exp, $value, $field, int $bindType): string
    {
        // IN 查询
        if ($value instanceof Closure) {
            $value = $this->parseClosure($query, $value, false);
        } elseif ($value instanceof Raw) {
            $value = $this->parseRaw($query, $value);
        } else {
            // 检查枚举类型
            if (is_subclass_of($value, UnitEnum::class, false)) {
                if (is_subclass_of($value, BackedEnum::class, false)) {
                    $value = array_column($value::cases(), 'value');
                } else {
                    $value = array_column($value::cases(), 'name');
                }
            } else {
                $value = is_array($value) ? $value : array_unique(explode(',', (string) $value));
            }

            if (count($value) === 0) {
                return 'IN' == $exp ? '0 = 1' : '1 = 1';
            }

            if ($query->isAutoBind()) {
                $array = [];
                foreach ($value as $v) {
                    if ($v instanceof UnitEnum) {
                        $v = $this->parseEnum($v);
                    }
                    $name    = $query->bindValue($v, $bindType);
                    $array[] = ':' . $name;
                }
                $value = implode(',', $array);
            } else{
                foreach ($value as &$v) {
                    if ($v instanceof UnitEnum) {
                        $v = $this->parseEnum($v);
                    }
                }
                if (Connection::PARAM_STR == $bindType) {
                    $value = '\'' . implode('\',\'', $value) . '\'';
                } else {
                    $value = implode(',', $value);
                }
            }

            if (!str_contains($value, ',')) {
                return $key . ('IN' == $exp ? ' = ' : ' <> ') . $value;
            }
        }

        return $key . ' ' . $exp . ' (' . $value . ')';
    }

    /**
     * 日期时间条件解析.
     *
     * @param Query  $query    查询对象
     * @param mixed  $value
     * @param string $key
     * @param int    $bindType
     *
     * @return string
     */
    protected function parseDateTime(Query $query, $value, string $key, int $bindType): string
    {
        $options = $query->getOptions();

        // 获取时间字段类型
        if (str_contains($key, '.')) {
            [$table, $key] = explode('.', $key);

            if (isset($options['alias']) && $pos = array_search($table, $options['alias'])) {
                $table = $pos;
            }
        } else {
            $table = $options['table'];
        }

        $type = $query->getFieldType($key);

        if ($type) {
            if (is_string($value)) {
                $value = strtotime($value) ?: $value;
            }

            if (is_int($value)) {
                if (preg_match('/(datetime|timestamp|string)/is', $type)) {
                    // 日期及时间戳类型
                    $value = date('Y-m-d H:i:s', $value);
                } elseif (preg_match('/(date)/is', $type)) {
                    // 日期及时间戳类型
                    $value = date('Y-m-d', $value);
                }
            }
        }

        $name = $query->bindValue($value, $bindType);

        return ':' . $name;
    }

    /**
     * limit分析.
     *
     * @param Query $query 查询对象
     * @param mixed $limit
     *
     * @return string
     */
    protected function parseLimit(Query $query, string $limit): string
    {
        return (!empty($limit) && !str_contains($limit, '(')) ? ' LIMIT ' . $limit . ' ' : '';
    }

    /**
     * join分析.
     *
     * @param Query $query 查询对象
     * @param array $join
     *
     * @return string
     */
    protected function parseJoin(Query $query, array $join): string
    {
        $joinStr = '';

        foreach ($join as $item) {
            [$table, $type, $on] = $item;

            if (str_contains($on, '=')) {
                [$val1, $val2] = explode('=', $on, 2);

                $condition = $this->parseKey($query, $val1) . '=' . $this->parseKey($query, $val2);
            } else {
                $condition = $on;
            }

            $table = $this->parseTable($query, $table);

            $joinStr .= ' ' . $type . ' JOIN ' . $table . ' ON ' . $condition;
        }

        return $joinStr;
    }

    /**
     * order分析.
     *
     * @param Query $query 查询对象
     * @param array $order
     *
     * @return string
     */
    protected function parseOrder(Query $query, array $order): string
    {
        $array = [];
        foreach ($order as $key => $val) {
            if ($val instanceof Raw) {
                $array[] = $this->parseRaw($query, $val);
            } elseif (is_array($val) && preg_match('/^[\w\.]+$/', $key)) {
                $array[] = $this->parseOrderField($query, $key, $val);
            } elseif ('[rand]' == $val) {
                $array[] = $this->parseRand($query);
            } elseif (is_string($val)) {
                if (is_numeric($key)) {
                    [$key, $sort] = explode(' ', str_contains($val, ' ') ? $val : $val . ' ');
                } else {
                    $sort = $val;
                }

                if (preg_match('/^[\w\-\>\.]+$/', $key)) {
                    $sort    = strtoupper($sort);
                    $sort    = in_array($sort, ['ASC', 'DESC'], true) ? ' ' . $sort : '';
                    $array[] = $this->parseKey($query, $key, true) . $sort;
                } else {
                    throw new Exception('order express error:' . $key);
                }
            }
        }

        return empty($array) ? '' : ' ORDER BY ' . implode(',', $array);
    }

    /**
     * 分析Raw对象
     *
     * @param Query $query 查询对象
     * @param Raw   $raw   Raw对象
     *
     * @return string
     */
    protected function parseRaw(Query $query, Raw $raw): string
    {
        $sql  = $raw->getValue();
        $bind = $raw->getBind();

        if ($bind) {
            $query->bindParams($sql, $bind);
        }

        return $sql;
    }

    /**
     * 分析Express对象
     *
     * @param Query $query 查询对象
     * @param Express  $express  Express对象
     *
     * @return string
     */
    protected function parseExpress(Query $query, Express $express): string
    {
        return $express->getValue();
    }

    /**
     * 随机排序.
     *
     * @param Query $query 查询对象
     *
     * @return string
     */
    protected function parseRand(Query $query): string
    {
        return '';
    }

    /**
     * orderField分析.
     *
     * @param Query  $query 查询对象
     * @param string $key
     * @param array  $val
     *
     * @return string
     */
    protected function parseOrderField(Query $query, string $key, array $val): string
    {
        if (isset($val['sort'])) {
            $sort = $val['sort'];
            unset($val['sort']);
        } else {
            $sort = '';
        }

        $sort = strtoupper($sort);
        $sort = in_array($sort, ['ASC', 'DESC'], true) ? ' ' . $sort : '';
        $bind = $query->getFieldsBindType();

        foreach ($val as $k => $item) {
            $val[$k] = $this->parseDataBind($query, $key, $item, $bind);
        }

        return 'field(' . $this->parseKey($query, $key, true) . ',' . implode(',', $val) . ')' . $sort;
    }

    /**
     * group分析.
     *
     * @param Query $query 查询对象
     * @param mixed $group
     *
     * @return string
     */
    protected function parseGroup(Query $query, string | array $group): string
    {
        if (empty($group)) {
            return '';
        }

        if (is_string($group)) {
            $group = explode(',', $group);
        }

        $val = [];
        foreach ($group as $key) {
            $val[] = $this->parseKey($query, $key);
        }

        return ' GROUP BY ' . implode(',', $val);
    }

    /**
     * having分析.
     *
     * @param Query  $query  查询对象
     * @param string $having
     *
     * @return string
     */
    protected function parseHaving(Query $query, string $having): string
    {
        return !empty($having) ? ' HAVING ' . $having : '';
    }

    /**
     * comment分析.
     *
     * @param Query  $query   查询对象
     * @param string $comment
     *
     * @return string
     */
    protected function parseComment(Query $query, string $comment): string
    {
        if (str_contains($comment, '*/')) {
            $comment = strstr($comment, '*/', true);
        }

        return !empty($comment) ? ' /* ' . $comment . ' */' : '';
    }

    /**
     * distinct分析.
     *
     * @param Query $query    查询对象
     * @param mixed $distinct
     *
     * @return string
     */
    protected function parseDistinct(Query $query, bool $distinct): string
    {
        return !empty($distinct) ? ' DISTINCT ' : '';
    }

    /**
     * index分析，可在操作链中指定需要强制使用的索引.
     *
     * @param Query $query 查询对象
     * @param mixed $index
     *
     * @return string
     */
    protected function parseForce(Query $query, string | array $index): string
    {
        if (empty($index)) {
            return '';
        }

        if (is_array($index)) {
            $index = implode(',', $index);
        }

        return sprintf(' FORCE INDEX ( %s ) ', $index);
    }

    /**
     * 设置锁机制.
     *
     * @param Query       $query 查询对象
     * @param bool|string $lock
     *
     * @return string
     */
    protected function parseLock(Query $query, bool | string $lock = false): string
    {
        if (is_bool($lock)) {
            return $lock ? ' FOR UPDATE ' : '';
        }

        if (is_string($lock) && !empty($lock)) {
            return ' ' . trim($lock) . ' ';
        }
        return '';
    }

    /**
     * 生成insertall SQL.
     *
     * @param Query $query   查询对象
     * @param  array $keys  字段名
     * @param  array $datas 数据
     *
     * @return string
     */
    public function insertAllByKeys(Query $query, array $keys, array $datas): string
    {
        $options = $query->getOptions();

        // 获取绑定信息
        $bind   = $query->getFieldsBindType();
        $fields = [];
        $values = [];

        foreach ($keys as $field) {
            $fields[] = $this->parseKey($query, $field);
        }

        foreach ($datas as $k => $data) {
            foreach ($data as $key => &$val) {
                if (!$query->isAutoBind()) {
                    $val = Connection::PARAM_STR == $bind[$keys[$key]] ? '\'' . $val . '\'' : $val;
                } else {
                    $val = $this->parseDataBind($query, $keys[$key], $val, $bind);
                }
            }

            $values[] = 'SELECT ' . implode(',', $data);
        }

        return str_replace(
            ['%INSERT%', '%TABLE%', '%EXTRA%', '%FIELD%', '%DATA%', '%COMMENT%'],
            [
                !empty($options['replace']) ? 'REPLACE' : 'INSERT',
                $this->parseTable($query, $options['table']),
                $this->parseExtra($query, $options['extra']),
                implode(' , ', $fields),
                implode(' UNION ALL ', $values),
                $this->parseComment($query, $options['comment']),
            ],
            $this->insertAllSql
        );
    }
}
