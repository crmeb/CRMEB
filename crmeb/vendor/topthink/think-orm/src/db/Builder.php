<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace think\db;

use Closure;
use PDO;
use think\Exception;

/**
 * Db Builder
 */
abstract class Builder
{
    /**
     * Connection对象
     * @var Connection
     */
    protected $connection;

    /**
     * 查询表达式映射
     * @var array
     */
    protected $exp = ['NOTLIKE' => 'NOT LIKE', 'NOTIN' => 'NOT IN', 'NOTBETWEEN' => 'NOT BETWEEN', 'NOTEXISTS' => 'NOT EXISTS', 'NOTNULL' => 'NOT NULL', 'NOTBETWEEN TIME' => 'NOT BETWEEN TIME'];

    /**
     * 查询表达式解析
     * @var array
     */
    protected $parser = [
        'parseCompare'     => ['=', '<>', '>', '>=', '<', '<='],
        'parseLike'        => ['LIKE', 'NOT LIKE'],
        'parseBetween'     => ['NOT BETWEEN', 'BETWEEN'],
        'parseIn'          => ['NOT IN', 'IN'],
        'parseExp'         => ['EXP'],
        'parseNull'        => ['NOT NULL', 'NULL'],
        'parseBetweenTime' => ['BETWEEN TIME', 'NOT BETWEEN TIME'],
        'parseTime'        => ['< TIME', '> TIME', '<= TIME', '>= TIME'],
        'parseExists'      => ['NOT EXISTS', 'EXISTS'],
        'parseColumn'      => ['COLUMN'],
    ];

    /**
     * SELECT SQL表达式
     * @var string
     */
    protected $selectSql = 'SELECT%DISTINCT%%EXTRA% %FIELD% FROM %TABLE%%FORCE%%JOIN%%WHERE%%GROUP%%HAVING%%UNION%%ORDER%%LIMIT% %LOCK%%COMMENT%';

    /**
     * INSERT SQL表达式
     * @var string
     */
    protected $insertSql = '%INSERT%%EXTRA% INTO %TABLE% (%FIELD%) VALUES (%DATA%) %COMMENT%';

    /**
     * INSERT ALL SQL表达式
     * @var string
     */
    protected $insertAllSql = '%INSERT%%EXTRA% INTO %TABLE% (%FIELD%) %DATA% %COMMENT%';

    /**
     * UPDATE SQL表达式
     * @var string
     */
    protected $updateSql = 'UPDATE%EXTRA% %TABLE% SET %SET%%JOIN%%WHERE%%ORDER%%LIMIT% %LOCK%%COMMENT%';

    /**
     * DELETE SQL表达式
     * @var string
     */
    protected $deleteSql = 'DELETE%EXTRA% FROM %TABLE%%USING%%JOIN%%WHERE%%ORDER%%LIMIT% %LOCK%%COMMENT%';

    /**
     * 架构函数
     * @access public
     * @param  Connection $connection 数据库连接对象实例
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * 获取当前的连接对象实例
     * @access public
     * @return Connection
     */
    public function getConnection(): Connection
    {
        return $this->connection;
    }

    /**
     * 注册查询表达式解析
     * @access public
     * @param  string    $name   解析方法
     * @param  array     $parser 匹配表达式数据
     * @return $this
     */
    public function bindParser(string $name, array $parser)
    {
        $this->parser[$name] = $parser;
        return $this;
    }

    /**
     * 数据分析
     * @access protected
     * @param  Query     $query     查询对象
     * @param  array     $data      数据
     * @param  array     $fields    字段信息
     * @param  array     $bind      参数绑定
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
            if ('*' == $options['field']) {
                $fields = array_keys($bind);
            } else {
                $fields = $options['field'];
            }
        }

        $result = [];

        foreach ($data as $key => $val) {
            $item = $this->parseKey($query, $key, true);

            if ($val instanceof Raw) {
                $result[$item] = $val->getValue();
                continue;
            } elseif (!is_scalar($val) && (in_array($key, (array) $query->getOptions('json')) || 'json' == $query->getFieldType($key))) {
                $val = json_encode($val);
            }

            if (false !== strpos($key, '->')) {
                list($key, $name) = explode('->', $key, 2);
                $item             = $this->parseKey($query, $key);
                $result[$item]    = 'json_set(' . $item . ', \'$.' . $name . '\', ' . $this->parseDataBind($query, $key . '->' . $name, $val, $bind) . ')';
            } elseif (false === strpos($key, '.') && !in_array($key, $fields, true)) {
                if ($options['strict']) {
                    throw new Exception('fields not exists:[' . $key . ']');
                }
            } elseif (is_null($val)) {
                $result[$item] = 'NULL';
            } elseif (is_array($val) && !empty($val)) {
                switch (strtoupper($val[0])) {
                    case 'INC':
                        $result[$item] = $item . ' + ' . floatval($val[1]);
                        break;
                    case 'DEC':
                        $result[$item] = $item . ' - ' . floatval($val[1]);
                        break;
                }
            } elseif (is_scalar($val)) {
                // 过滤非标量数据
                $result[$item] = $this->parseDataBind($query, $key, $val, $bind);
            }
        }

        return $result;
    }

    /**
     * 数据绑定处理
     * @access protected
     * @param  Query     $query     查询对象
     * @param  string    $key       字段名
     * @param  mixed     $data      数据
     * @param  array     $bind      绑定数据
     * @return string
     */
    protected function parseDataBind(Query $query, string $key, $data, array $bind = []): string
    {
        if ($data instanceof Raw) {
            return $data->getValue();
        }

        $name = $query->bindValue($data, $bind[$key] ?? PDO::PARAM_STR);

        return ':' . $name;
    }

    /**
     * 字段名分析
     * @access public
     * @param  Query  $query    查询对象
     * @param  mixed  $key      字段名
     * @param  bool   $strict   严格检测
     * @return string
     */
    public function parseKey(Query $query, $key, bool $strict = false): string
    {
        return $key;
    }

    /**
     * 查询额外参数分析
     * @access protected
     * @param  Query  $query    查询对象
     * @param  string $extra    额外参数
     * @return string
     */
    protected function parseExtra(Query $query, string $extra): string
    {
        return preg_match('/^[\w]+$/i', $extra) ? ' ' . strtoupper($extra) : '';
    }

    /**
     * field分析
     * @access protected
     * @param  Query     $query     查询对象
     * @param  mixed     $fields    字段名
     * @return string
     */
    protected function parseField(Query $query, $fields): string
    {
        if (is_array($fields)) {
            // 支持 'field1'=>'field2' 这样的字段别名定义
            $array = [];

            foreach ($fields as $key => $field) {
                if ($field instanceof Raw) {
                    $array[] = $field->getValue();
                } elseif (!is_numeric($key)) {
                    $array[] = $this->parseKey($query, $key) . ' AS ' . $this->parseKey($query, $field, true);
                } else {
                    $array[] = $this->parseKey($query, $field);
                }
            }

            $fieldsStr = implode(',', $array);
        } else {
            $fieldsStr = '*';
        }

        return $fieldsStr;
    }

    /**
     * table分析
     * @access protected
     * @param  Query     $query     查询对象
     * @param  mixed     $tables    表名
     * @return string
     */
    protected function parseTable(Query $query, $tables): string
    {
        $item    = [];
        $options = $query->getOptions();

        foreach ((array) $tables as $key => $table) {
            if ($table instanceof Raw) {
                $item[] = $table->getValue();
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
     * where分析
     * @access protected
     * @param  Query     $query   查询对象
     * @param  mixed     $where   查询条件
     * @return string
     */
    protected function parseWhere(Query $query, array $where): string
    {
        $options  = $query->getOptions();
        $whereStr = $this->buildWhere($query, $where);

        if (!empty($options['soft_delete'])) {
            // 附加软删除条件
            list($field, $condition) = $options['soft_delete'];

            $binds    = $query->getFieldsBindType();
            $whereStr = $whereStr ? '( ' . $whereStr . ' ) AND ' : '';
            $whereStr = $whereStr . $this->parseWhereItem($query, $field, $condition, $binds);
        }

        return empty($whereStr) ? '' : ' WHERE ' . $whereStr;
    }

    /**
     * 生成查询条件SQL
     * @access public
     * @param  Query     $query     查询对象
     * @param  mixed     $where     查询条件
     * @return string
     */
    public function buildWhere(Query $query, array $where): string
    {
        if (empty($where)) {
            $where = [];
        }

        $whereStr = '';

        $binds = $query->getFieldsBindType();

        foreach ($where as $logic => $val) {
            $str = $this->parseWhereLogic($query, $logic, $val, $binds);

            $whereStr .= empty($whereStr) ? substr(implode(' ', $str), strlen($logic) + 1) : implode(' ', $str);
        }

        return $whereStr;
    }

    /**
     * 不同字段使用相同查询条件（AND）
     * @access protected
     * @param  Query  $query 查询对象
     * @param  string $logic Logic
     * @param  array  $val   查询条件
     * @param  array  $binds 参数绑定
     * @return array
     */
    protected function parseWhereLogic(Query $query, string $logic, array $val, array $binds = []): array
    {
        $where = [];
        foreach ($val as $value) {
            if ($value instanceof Raw) {
                $where[] = ' ' . $logic . ' ( ' . $value->getValue() . ' )';
                continue;
            }

            if (is_array($value)) {
                if (key($value) !== 0) {
                    throw new Exception('where express error:' . var_export($value, true));
                }
                $field = array_shift($value);
            } elseif (true === $value) {
                $where[] = ' ' . $logic . ' 1 ';
                continue;
            } elseif (!($value instanceof Closure)) {
                throw new Exception('where express error:' . var_export($value, true));
            }

            if ($value instanceof Closure) {
                // 使用闭包查询
                $where[] = $this->parseClousreWhere($query, $value, $logic);
            } elseif (is_array($field)) {
                $where[] = $this->parseMultiWhereField($query, $value, $field, $logic, $binds);
            } elseif ($field instanceof Raw) {
                $where[] = ' ' . $logic . ' ' . $this->parseWhereItem($query, $field, $value, $binds);
            } elseif (strpos($field, '|')) {
                $where[] = $this->parseFieldsOr($query, $value, $field, $logic, $binds);
            } elseif (strpos($field, '&')) {
                $where[] = $this->parseFieldsAnd($query, $value, $field, $logic, $binds);
            } else {
                // 对字段使用表达式查询
                $field   = is_string($field) ? $field : '';
                $where[] = ' ' . $logic . ' ' . $this->parseWhereItem($query, $field, $value, $binds);
            }
        }

        return $where;
    }

    /**
     * 不同字段使用相同查询条件（AND）
     * @access protected
     * @param  Query  $query 查询对象
     * @param  mixed  $value 查询条件
     * @param  string $field 查询字段
     * @param  string $logic Logic
     * @param  array  $binds 参数绑定
     * @return string
     */
    protected function parseFieldsAnd(Query $query, $value, string $field, string $logic, array $binds): string
    {
        $item = [];

        foreach (explode('&', $field) as $k) {
            $item[] = $this->parseWhereItem($query, $k, $value, $binds);
        }

        return ' ' . $logic . ' ( ' . implode(' AND ', $item) . ' )';
    }

    /**
     * 不同字段使用相同查询条件（OR）
     * @access protected
     * @param  Query  $query 查询对象
     * @param  mixed  $value 查询条件
     * @param  string $field 查询字段
     * @param  string $logic Logic
     * @param  array  $binds 参数绑定
     * @return string
     */
    protected function parseFieldsOr(Query $query, $value, string $field, string $logic, array $binds): string
    {
        $item = [];

        foreach (explode('|', $field) as $k) {
            $item[] = $this->parseWhereItem($query, $k, $value, $binds);
        }

        return ' ' . $logic . ' ( ' . implode(' OR ', $item) . ' )';
    }

    /**
     * 闭包查询
     * @access protected
     * @param  Query   $query 查询对象
     * @param  Closure $value 查询条件
     * @param  string  $logic Logic
     * @return string
     */
    protected function parseClousreWhere(Query $query, Closure $value, string $logic): string
    {
        $newQuery = $query->newQuery()->setConnection($this->connection);
        $value($newQuery);
        $whereClause = $this->buildWhere($query, $newQuery->getOptions('where') ?: []);

        if (!empty($whereClause)) {
            $where = ' ' . $logic . ' ( ' . $whereClause . ' )';
        }

        return $where ?? '';
    }

    /**
     * 复合条件查询
     * @access protected
     * @param  Query  $query 查询对象
     * @param  mixed  $value 查询条件
     * @param  mixed  $field 查询字段
     * @param  string $logic Logic
     * @param  array  $binds 参数绑定
     * @return string
     */
    protected function parseMultiWhereField(Query $query, $value, $field, string $logic, array $binds): string
    {
        array_unshift($value, $field);

        $where = [];
        foreach ($value as $item) {
            $where[] = $this->parseWhereItem($query, array_shift($item), $item, $binds);
        }

        return ' ' . $logic . ' ( ' . implode(' AND ', $where) . ' )';
    }

    /**
     * where子单元分析
     * @access protected
     * @param  Query $query 查询对象
     * @param  mixed $field 查询字段
     * @param  array $val   查询条件
     * @param  array $binds 参数绑定
     * @return string
     */
    protected function parseWhereItem(Query $query, $field, array $val, array $binds = []): string
    {
        // 字段分析
        $key = $field ? $this->parseKey($query, $field, true) : '';

        list($exp, $value) = $val;

        // 检测操作符
        if (!is_string($exp)) {
            throw new Exception('where express error:' . var_export($exp, true));
        }

        $exp = strtoupper($exp);
        if (isset($this->exp[$exp])) {
            $exp = $this->exp[$exp];
        }

        if (is_string($field) && 'LIKE' != $exp) {
            $bindType = $binds[$field] ?? PDO::PARAM_STR;
        } else {
            $bindType = PDO::PARAM_STR;
        }

        if ($value instanceof Raw) {

        } elseif (is_object($value) && method_exists($value, '__toString')) {
            // 对象数据写入
            $value = $value->__toString();
        }

        if (is_scalar($value) && !in_array($exp, ['EXP', 'NOT NULL', 'NULL', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN']) && strpos($exp, 'TIME') === false) {
            if (is_string($value) && 0 === strpos($value, ':') && $query->isBind(substr($value, 1))) {
            } else {
                $name  = $query->bindValue($value, $bindType);
                $value = ':' . $name;
            }
        }

        // 解析查询表达式
        foreach ($this->parser as $fun => $parse) {
            if (in_array($exp, $parse)) {
                return $this->$fun($query, $key, $exp, $value, $field, $bindType, $val[2] ?? 'AND');
            }
        }

        throw new Exception('where express error:' . $exp);
    }

    /**
     * 模糊查询
     * @access protected
     * @param  Query   $query   查询对象
     * @param  string  $key
     * @param  string  $exp
     * @param  array   $value
     * @param  string  $field
     * @param  integer $bindType
     * @param  string  $logic
     * @return string
     */
    protected function parseLike(Query $query, string $key, string $exp, $value, $field, int $bindType, string $logic): string
    {
        // 模糊匹配
        if (is_array($value)) {
            $array = [];
            foreach ($value as $item) {
                $name    = $query->bindValue($item, PDO::PARAM_STR);
                $array[] = $key . ' ' . $exp . ' :' . $name;
            }

            $whereStr = '(' . implode(' ' . strtoupper($logic) . ' ', $array) . ')';
        } else {
            $whereStr = $key . ' ' . $exp . ' ' . $value;
        }

        return $whereStr;
    }

    /**
     * 表达式查询
     * @access protected
     * @param  Query   $query   查询对象
     * @param  string  $key
     * @param  string  $exp
     * @param  array   $value
     * @param  string  $field
     * @param  integer $bindType
     * @return string
     */
    protected function parseExp(Query $query, string $key, string $exp, Raw $value, string $field, int $bindType): string
    {
        // 表达式查询
        return '( ' . $key . ' ' . $value->getValue() . ' )';
    }

    /**
     * 表达式查询
     * @access protected
     * @param  Query   $query   查询对象
     * @param  string  $key
     * @param  string  $exp
     * @param  array   $value
     * @param  string  $field
     * @param  integer $bindType
     * @return string
     */
    protected function parseColumn(Query $query, string $key, $exp, array $value, string $field, int $bindType): string
    {
        // 字段比较查询
        list($op, $field) = $value;

        if (!in_array(trim($op), ['=', '<>', '>', '>=', '<', '<='])) {
            throw new Exception('where express error:' . var_export($value, true));
        }

        return '( ' . $key . ' ' . $op . ' ' . $this->parseKey($query, $field, true) . ' )';
    }

    /**
     * Null查询
     * @access protected
     * @param  Query   $query   查询对象
     * @param  string  $key
     * @param  string  $exp
     * @param  mixed   $value
     * @param  string  $field
     * @param  integer $bindType
     * @return string
     */
    protected function parseNull(Query $query, string $key, string $exp, $value, $field, int $bindType): string
    {
        // NULL 查询
        return $key . ' IS ' . $exp;
    }

    /**
     * 范围查询
     * @access protected
     * @param  Query   $query   查询对象
     * @param  string  $key
     * @param  string  $exp
     * @param  mixed   $value
     * @param  string  $field
     * @param  integer $bindType
     * @return string
     */
    protected function parseBetween(Query $query, string $key, string $exp, $value, $field, int $bindType): string
    {
        // BETWEEN 查询
        $data = is_array($value) ? $value : explode(',', $value);

        $min = $query->bindValue($data[0], $bindType);
        $max = $query->bindValue($data[1], $bindType);

        return $key . ' ' . $exp . ' :' . $min . ' AND :' . $max . ' ';
    }

    /**
     * Exists查询
     * @access protected
     * @param  Query   $query   查询对象
     * @param  string  $key
     * @param  string  $exp
     * @param  mixed   $value
     * @param  string  $field
     * @param  integer $bindType
     * @return string
     */
    protected function parseExists(Query $query, string $key, string $exp, $value, string $field, int $bindType): string
    {
        // EXISTS 查询
        if ($value instanceof Closure) {
            $value = $this->parseClosure($query, $value, false);
        } elseif ($value instanceof Raw) {
            $value = $value->getValue();
        } else {
            throw new Exception('where express error:' . $value);
        }

        return $exp . ' ( ' . $value . ' )';
    }

    /**
     * 时间比较查询
     * @access protected
     * @param  Query   $query  查询对象
     * @param  string  $key
     * @param  string  $exp
     * @param  mixed   $value
     * @param  string  $field
     * @param  integer $bindType
     * @return string
     */
    protected function parseTime(Query $query, string $key, string $exp, $value, $field, int $bindType): string
    {
        return $key . ' ' . substr($exp, 0, 2) . ' ' . $this->parseDateTime($query, $value, $field, $bindType);
    }

    /**
     * 大小比较查询
     * @access protected
     * @param  Query   $query   查询对象
     * @param  string  $key
     * @param  string  $exp
     * @param  mixed   $value
     * @param  string  $field
     * @param  integer $bindType
     * @return string
     */
    protected function parseCompare(Query $query, string $key, string $exp, $value, $field, int $bindType): string
    {
        if (is_array($value)) {
            throw new Exception('where express error:' . $exp . var_export($value, true));
        }

        // 比较运算
        if ($value instanceof Closure) {
            $value = $this->parseClosure($query, $value);
        }

        if ('=' == $exp && is_null($value)) {
            return $key . ' IS NULL';
        }

        return $key . ' ' . $exp . ' ' . $value;
    }

    /**
     * 时间范围查询
     * @access protected
     * @param  Query   $query     查询对象
     * @param  string  $key
     * @param  string  $exp
     * @param  mixed   $value
     * @param  string  $field
     * @param  integer $bindType
     * @return string
     */
    protected function parseBetweenTime(Query $query, string $key, string $exp, $value, $field, int $bindType): string
    {
        if (is_string($value)) {
            $value = explode(',', $value);
        }

        return $key . ' ' . substr($exp, 0, -4)
        . $this->parseDateTime($query, $value[0], $field, $bindType)
        . ' AND '
        . $this->parseDateTime($query, $value[1], $field, $bindType);

    }

    /**
     * IN查询
     * @access protected
     * @param  Query   $query   查询对象
     * @param  string  $key
     * @param  string  $exp
     * @param  mixed   $value
     * @param  string  $field
     * @param  integer $bindType
     * @return string
     */
    protected function parseIn(Query $query, string $key, string $exp, $value, $field, int $bindType): string
    {
        // IN 查询
        if ($value instanceof Closure) {
            $value = $this->parseClosure($query, $value, false);
        } elseif ($value instanceof Raw) {
            $value = $value->getValue();
        } else {
            $value = array_unique(is_array($value) ? $value : explode(',', $value));
            $array = [];

            foreach ($value as $v) {
                $name    = $query->bindValue($v, $bindType);
                $array[] = ':' . $name;
            }

            if (count($array) == 1) {
                return $key . ('IN' == $exp ? ' = ' : ' <> ') . $array[0];
            } else {
                $zone  = implode(',', $array);
                $value = empty($zone) ? "''" : $zone;
            }
        }

        return $key . ' ' . $exp . ' (' . $value . ')';
    }

    /**
     * 闭包子查询
     * @access protected
     * @param  Query    $query 查询对象
     * @param  \Closure $call
     * @param  bool     $show
     * @return string
     */
    protected function parseClosure(Query $query, Closure $call, bool $show = true): string
    {
        $newQuery = $query->newQuery()->removeOption();
        $call($newQuery);

        return $newQuery->buildSql($show);
    }

    /**
     * 日期时间条件解析
     * @access protected
     * @param  Query   $query 查询对象
     * @param  mixed   $value
     * @param  string  $key
     * @param  integer $bindType
     * @return string
     */
    protected function parseDateTime(Query $query, $value, string $key, int $bindType): string
    {
        $options = $query->getOptions();

        // 获取时间字段类型
        if (strpos($key, '.')) {
            list($table, $key) = explode('.', $key);

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
                if (preg_match('/(datetime|timestamp)/is', $type)) {
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
     * limit分析
     * @access protected
     * @param  Query $query 查询对象
     * @param  mixed $limit
     * @return string
     */
    protected function parseLimit(Query $query, string $limit): string
    {
        return (!empty($limit) && false === strpos($limit, '(')) ? ' LIMIT ' . $limit . ' ' : '';
    }

    /**
     * join分析
     * @access protected
     * @param  Query $query 查询对象
     * @param  array $join
     * @return string
     */
    protected function parseJoin(Query $query, array $join): string
    {
        $joinStr = '';

        foreach ($join as $item) {
            list($table, $type, $on) = $item;

            if (strpos($on, '=')) {
                list($val1, $val2) = explode('=', $on, 2);

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
     * order分析
     * @access protected
     * @param  Query $query 查询对象
     * @param  array $order
     * @return string
     */
    protected function parseOrder(Query $query, array $order): string
    {
        $array = [];
        foreach ($order as $key => $val) {
            if ($val instanceof Raw) {
                $array[] = $val->getValue();
            } elseif (is_array($val) && preg_match('/^[\w\.]+$/', $key)) {
                $array[] = $this->parseOrderField($query, $key, $val);
            } elseif ('[rand]' == $val) {
                $array[] = $this->parseRand($query);
            } elseif (is_string($val)) {
                if (is_numeric($key)) {
                    list($key, $sort) = explode(' ', strpos($val, ' ') ? $val : $val . ' ');
                } else {
                    $sort = $val;
                }

                if (preg_match('/^[\w\.]+$/', $key)) {
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
     * 随机排序
     * @access protected
     * @param  Query $query 查询对象
     * @return string
     */
    protected function parseRand(Query $query): string
    {
        return '';
    }

    /**
     * orderField分析
     * @access protected
     * @param  Query  $query 查询对象
     * @param  string $key
     * @param  array  $val
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

        foreach ($val as $item) {
            $val[] = $this->parseDataBind($query, $key, $item, $bind);
        }

        return 'field(' . $this->parseKey($query, $key, true) . ',' . implode(',', $val) . ')' . $sort;
    }

    /**
     * group分析
     * @access protected
     * @param  Query $query 查询对象
     * @param  mixed $group
     * @return string
     */
    protected function parseGroup(Query $query, $group): string
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
     * having分析
     * @access protected
     * @param  Query  $query  查询对象
     * @param  string $having
     * @return string
     */
    protected function parseHaving(Query $query, string $having): string
    {
        return !empty($having) ? ' HAVING ' . $having : '';
    }

    /**
     * comment分析
     * @access protected
     * @param  Query  $query  查询对象
     * @param  string $comment
     * @return string
     */
    protected function parseComment(Query $query, string $comment): string
    {
        if (false !== strpos($comment, '*/')) {
            $comment = strstr($comment, '*/', true);
        }

        return !empty($comment) ? ' /* ' . $comment . ' */' : '';
    }

    /**
     * distinct分析
     * @access protected
     * @param  Query $query  查询对象
     * @param  mixed $distinct
     * @return string
     */
    protected function parseDistinct(Query $query, bool $distinct): string
    {
        return !empty($distinct) ? ' DISTINCT ' : '';
    }

    /**
     * union分析
     * @access protected
     * @param  Query $query 查询对象
     * @param  array $union
     * @return string
     */
    protected function parseUnion(Query $query, array $union): string
    {
        if (empty($union)) {
            return '';
        }

        $type = $union['type'];
        unset($union['type']);

        foreach ($union as $u) {
            if ($u instanceof Closure) {
                $sql[] = $type . ' ' . $this->parseClosure($query, $u);
            } elseif (is_string($u)) {
                $sql[] = $type . ' ( ' . $u . ' )';
            }
        }

        return ' ' . implode(' ', $sql);
    }

    /**
     * index分析，可在操作链中指定需要强制使用的索引
     * @access protected
     * @param  Query $query 查询对象
     * @param  mixed $index
     * @return string
     */
    protected function parseForce(Query $query, $index): string
    {
        if (empty($index)) {
            return '';
        }

        if (is_array($index)) {
            $index = join(',', $index);
        }

        return sprintf(" FORCE INDEX ( %s ) ", $index);
    }

    /**
     * 设置锁机制
     * @access protected
     * @param  Query       $query 查询对象
     * @param  bool|string $lock
     * @return string
     */
    protected function parseLock(Query $query, $lock = false): string
    {
        if (is_bool($lock)) {
            return $lock ? ' FOR UPDATE ' : '';
        }

        if (is_string($lock) && !empty($lock)) {
            return ' ' . trim($lock) . ' ';
        } else {
            return '';
        }
    }

    /**
     * 生成查询SQL
     * @access public
     * @param  Query $query 查询对象
     * @param  bool  $one   是否仅获取一个记录
     * @return string
     */
    public function select(Query $query, bool $one = false): string
    {
        $options = $query->getOptions();

        return str_replace(
            ['%TABLE%', '%DISTINCT%', '%EXTRA%', '%FIELD%', '%JOIN%', '%WHERE%', '%GROUP%', '%HAVING%', '%ORDER%', '%LIMIT%', '%UNION%', '%LOCK%', '%COMMENT%', '%FORCE%'],
            [
                $this->parseTable($query, $options['table']),
                $this->parseDistinct($query, $options['distinct']),
                $this->parseExtra($query, $options['extra']),
                $this->parseField($query, $options['field']),
                $this->parseJoin($query, $options['join']),
                $this->parseWhere($query, $options['where']),
                $this->parseGroup($query, $options['group']),
                $this->parseHaving($query, $options['having']),
                $this->parseOrder($query, $options['order']),
                $this->parseLimit($query, $one ? '1' : $options['limit']),
                $this->parseUnion($query, $options['union']),
                $this->parseLock($query, $options['lock']),
                $this->parseComment($query, $options['comment']),
                $this->parseForce($query, $options['force']),
            ],
            $this->selectSql);
    }

    /**
     * 生成Insert SQL
     * @access public
     * @param  Query $query 查询对象
     * @return string
     */
    public function insert(Query $query): string
    {
        $options = $query->getOptions();

        // 分析并处理数据
        $data = $this->parseData($query, $options['data']);
        if (empty($data)) {
            return '';
        }

        $fields = array_keys($data);
        $values = array_values($data);

        return str_replace(
            ['%INSERT%', '%TABLE%', '%EXTRA%', '%FIELD%', '%DATA%', '%COMMENT%'],
            [
                !empty($options['replace']) ? 'REPLACE' : 'INSERT',
                $this->parseTable($query, $options['table']),
                $this->parseExtra($query, $options['extra']),
                implode(' , ', $fields),
                implode(' , ', $values),
                $this->parseComment($query, $options['comment']),
            ],
            $this->insertSql);
    }

    /**
     * 生成insertall SQL
     * @access public
     * @param  Query $query   查询对象
     * @param  array $dataSet 数据集
     * @return string
     */
    public function insertAll(Query $query, array $dataSet): string
    {
        $options = $query->getOptions();

        // 获取绑定信息
        $bind = $query->getFieldsBindType();

        // 获取合法的字段
        if ('*' == $options['field']) {
            $allowFields = array_keys($bind);
        } else {
            $allowFields = $options['field'];
        }

        $fields = [];
        $values = [];

        foreach ($dataSet as $k => $data) {
            $data = $this->parseData($query, $data, $allowFields, $bind);

            $values[] = 'SELECT ' . implode(',', array_values($data));

            if (!isset($insertFields)) {
                $insertFields = array_keys($data);
            }
        }

        foreach ($insertFields as $field) {
            $fields[] = $this->parseKey($query, $field);
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
            $this->insertAllSql);
    }

    /**
     * 生成slect insert SQL
     * @access public
     * @param  Query  $query  查询对象
     * @param  array  $fields 数据
     * @param  string $table  数据表
     * @return string
     */
    public function selectInsert(Query $query, array $fields, string $table): string
    {
        foreach ($fields as &$field) {
            $field = $this->parseKey($query, $field, true);
        }

        return 'INSERT INTO ' . $this->parseTable($query, $table) . ' (' . implode(',', $fields) . ') ' . $this->select($query);
    }

    /**
     * 生成update SQL
     * @access public
     * @param  Query $query 查询对象
     * @return string
     */
    public function update(Query $query): string
    {
        $options = $query->getOptions();

        $data = $this->parseData($query, $options['data']);

        if (empty($data)) {
            return '';
        }

        $set = [];
        foreach ($data as $key => $val) {
            $set[] = $key . ' = ' . $val;
        }

        return str_replace(
            ['%TABLE%', '%EXTRA%', '%SET%', '%JOIN%', '%WHERE%', '%ORDER%', '%LIMIT%', '%LOCK%', '%COMMENT%'],
            [
                $this->parseTable($query, $options['table']),
                $this->parseExtra($query, $options['extra']),
                implode(' , ', $set),
                $this->parseJoin($query, $options['join']),
                $this->parseWhere($query, $options['where']),
                $this->parseOrder($query, $options['order']),
                $this->parseLimit($query, $options['limit']),
                $this->parseLock($query, $options['lock']),
                $this->parseComment($query, $options['comment']),
            ],
            $this->updateSql);
    }

    /**
     * 生成delete SQL
     * @access public
     * @param  Query $query 查询对象
     * @return string
     */
    public function delete(Query $query): string
    {
        $options = $query->getOptions();

        return str_replace(
            ['%TABLE%', '%EXTRA%', '%USING%', '%JOIN%', '%WHERE%', '%ORDER%', '%LIMIT%', '%LOCK%', '%COMMENT%'],
            [
                $this->parseTable($query, $options['table']),
                $this->parseExtra($query, $options['extra']),
                !empty($options['using']) ? ' USING ' . $this->parseTable($query, $options['using']) . ' ' : '',
                $this->parseJoin($query, $options['join']),
                $this->parseWhere($query, $options['where']),
                $this->parseOrder($query, $options['order']),
                $this->parseLimit($query, $options['limit']),
                $this->parseLock($query, $options['lock']),
                $this->parseComment($query, $options['comment']),
            ],
            $this->deleteSql);
    }
}
