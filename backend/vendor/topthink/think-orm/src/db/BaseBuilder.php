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
use think\db\BaseQuery as Query;
use think\db\exception\DbException as Exception;
use UnitEnum;

/**
 * Db Base Builder.
 */
abstract class BaseBuilder
{
    /**
     * Connection对象
     *
     * @var ConnectionInterface
     */
    protected $connection;

    /**
     * 查询表达式映射.
     *
     * @var array
     */
    protected $exp = ['NOTLIKE' => 'NOT LIKE', 'NOTIN' => 'NOT IN', 'NOTBETWEEN' => 'NOT BETWEEN', 'NOTEXISTS' => 'NOT EXISTS', 'NOTNULL' => 'NOT NULL', 'NOTBETWEEN TIME' => 'NOT BETWEEN TIME'];

    /**
     * 查询表达式解析.
     *
     * @var array
     */
    protected $parser = [
        'parseCompare'     => ['=', '<>', '>', '>=', '<', '<=', '&', '|', '^', '>>', '<<'],
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
     * SELECT SQL表达式.
     *
     * @var string
     */
    protected $selectSql = 'SELECT%DISTINCT%%EXTRA% %FIELD% FROM %TABLE%%FORCE%%JOIN%%WHERE%%GROUP%%HAVING%%UNION%%ORDER%%LIMIT% %LOCK%%COMMENT%';

    /**
     * INSERT SQL表达式.
     *
     * @var string
     */
    protected $insertSql = '%INSERT%%EXTRA% INTO %TABLE% (%FIELD%) VALUES (%DATA%) %COMMENT%';

    /**
     * INSERT ALL SQL表达式.
     *
     * @var string
     */
    protected $insertAllSql = '%INSERT%%EXTRA% INTO %TABLE% (%FIELD%) %DATA% %COMMENT%';

    /**
     * UPDATE SQL表达式.
     *
     * @var string
     */
    protected $updateSql = 'UPDATE%EXTRA% %TABLE% SET %SET%%JOIN%%WHERE%%ORDER%%LIMIT% %LOCK%%COMMENT%';

    /**
     * DELETE SQL表达式.
     *
     * @var string
     */
    protected $deleteSql = 'DELETE%EXTRA% FROM %TABLE%%USING%%JOIN%%WHERE%%ORDER%%LIMIT% %LOCK%%COMMENT%';

    /**
     * 架构函数.
     *
     * @param ConnectionInterface $connection 数据库连接对象实例
     */
    public function __construct(?ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     * 获取当前的连接对象实例.
     *
     * @return ConnectionInterface
     */
    public function getConnection(): ConnectionInterface
    {
        return $this->connection;
    }

    /**
     * 注册查询表达式解析.
     *
     * @param string $name   解析方法
     * @param array  $parser 匹配表达式数据
     *
     * @return $this
     */
    public function bindParser(string $name, array $parser)
    {
        $this->parser[$name] = $parser;

        return $this;
    }

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
    abstract protected function parseData(Query $query, array $data = [], array $fields = [], array $bind = []): array;

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
    abstract protected function parseDataBind(Query $query, string $key, $data, array $bind = []): string;

    /**
     * 字段名分析.
     *
     * @param Query $query  查询对象
     * @param mixed $key    字段名
     * @param bool  $strict 严格检测
     *
     * @return string
     */
    abstract public function parseKey(Query $query, string | int | Raw $key, bool $strict = false): string;

    /**
     * 查询额外参数分析.
     *
     * @param Query  $query 查询对象
     * @param string $extra 额外参数
     *
     * @return string
     */
    abstract protected function parseExtra(Query $query, string $extra): string;

    /**
     * field分析.
     *
     * @param Query $query  查询对象
     * @param array $fields 字段名
     *
     * @return string
     */
    abstract protected function parseField(Query $query, array $fields): string;

    /**
     * table分析.
     *
     * @param Query $query  查询对象
     * @param array|string $tables 表名
     *
     * @return string
     */
    abstract protected function parseTable(Query $query, array | string $tables): string;

    /**
     * where分析.
     *
     * @param Query $query 查询对象
     * @param array $where 查询条件
     *
     * @return string
     */
    abstract protected function parseWhere(Query $query, array $where): string;

    /**
     * 生成查询条件SQL.
     *
     * @param Query $query 查询对象
     * @param array $where 查询条件
     *
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
     * 不同字段使用相同查询条件（AND）.
     *
     * @param Query  $query 查询对象
     * @param string $logic Logic
     * @param array  $val   查询条件
     * @param array  $binds 参数绑定
     *
     * @return array
     */
    protected function parseWhereLogic(Query $query, string $logic, array $val, array $binds = []): array
    {
        $where = [];
        foreach ($val as $value) {
            if ($value instanceof Raw) {
                $where[] = ' ' . $logic . ' ( ' . $this->parseRaw($query, $value) . ' )';
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
                $whereClosureStr = $this->parseClosureWhere($query, $value, $logic);
                if ($whereClosureStr) {
                    $where[] = $whereClosureStr;
                }
            } elseif (is_array($field)) {
                $where[] = $this->parseMultiWhereField($query, $value, $field, $logic, $binds);
            } elseif ($field instanceof Raw) {
                $where[] = ' ' . $logic . ' ' . $this->parseWhereItem($query, $field, $value, $binds);
            } elseif (str_contains($field, '|')) {
                $where[] = $this->parseFieldsOr($query, $value, $field, $logic, $binds);
            } elseif (str_contains($field, '&')) {
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
     * 不同字段使用相同查询条件（AND）.
     *
     * @param Query  $query 查询对象
     * @param mixed  $value 查询条件
     * @param string $field 查询字段
     * @param string $logic Logic
     * @param array  $binds 参数绑定
     *
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
     * 不同字段使用相同查询条件（OR）.
     *
     * @param Query  $query 查询对象
     * @param array  $value 查询条件
     * @param string $field 查询字段
     * @param string $logic Logic
     * @param array  $binds 参数绑定
     *
     * @return string
     */
    protected function parseFieldsOr(Query $query, array $value, string $field, string $logic, array $binds): string
    {
        $item = [];

        foreach (explode('|', $field) as $k) {
            $item[] = $this->parseWhereItem($query, $k, $value, $binds);
        }

        return ' ' . $logic . ' ( ' . implode(' OR ', $item) . ' )';
    }

    /**
     * 闭包查询.
     *
     * @param Query   $query 查询对象
     * @param Closure $value 查询条件
     * @param string  $logic Logic
     *
     * @return string
     */
    protected function parseClosureWhere(Query $query, Closure $value, string $logic): string
    {
        $newQuery = $query->newQuery();
        $value($newQuery);
        $whereClosure = $this->buildWhere($newQuery, $newQuery->getOption('where') ?: []);

        if (!empty($whereClosure)) {
            $query->bind($newQuery->getBind(false));
            $where = ' ' . $logic . ' ( ' . $whereClosure . ' )';
        }

        return $where ?? '';
    }

    /**
     * 复合条件查询.
     *
     * @param Query  $query 查询对象
     * @param mixed  $value 查询条件
     * @param mixed  $field 查询字段
     * @param string $logic Logic
     * @param array  $binds 参数绑定
     *
     * @return string
     */
    protected function parseMultiWhereField(Query $query, array $value, $field, string $logic, array $binds): string
    {
        array_unshift($value, $field);

        $where = [];
        foreach ($value as $item) {
            $where[] = $this->parseWhereItem($query, array_shift($item), $item, $binds);
        }

        return ' ' . $logic . ' ( ' . implode(' AND ', $where) . ' )';
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
    abstract protected function parseWhereItem(Query $query, $field, array $val, array $binds = []): string;

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
    abstract protected function parseLike(Query $query, string $key, string $exp, $value, $field, int $bindType, string $logic): string;

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
     * 表达式查询.
     *
     * @param Query  $query    查询对象
     * @param string $key
     * @param string $exp
     * @param array  $value
     * @param string $field
     * @param int    $bindType
     *
     * @return string
     */
    protected function parseColumn(Query $query, string $key, $exp, array $value, string $field, int $bindType): string
    {
        // 字段比较查询
        [$op, $field] = $value;

        if (!in_array(trim($op), ['=', '<>', '>', '>=', '<', '<='])) {
            throw new Exception('where express error:' . var_export($value, true));
        }

        return '( ' . $key . ' ' . $op . ' ' . $this->parseKey($query, $field, true) . ' )';
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
    abstract protected function parseNull(Query $query, string $key, string $exp, $value, $field, int $bindType): string;

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
    abstract protected function parseBetween(Query $query, string $key, string $exp, array | string $value, $field, int $bindType): string;

    /**
     * Exists查询.
     *
     * @param Query  $query    查询对象
     * @param string $key
     * @param string $exp
     * @param Raw|Closure  $value
     * @param string $field
     * @param int    $bindType
     *
     * @return string
     */
    protected function parseExists(Query $query, string $key, string $exp, Raw | Closure $value, string $field, int $bindType): string
    {
        // EXISTS 查询
        if ($value instanceof Closure) {
            $value = $this->parseClosure($query, $value, false);
        } elseif ($value instanceof Raw) {
            $value = $this->parseRaw($query, $value);
        }

        return $exp . ' ( ' . $value . ' )';
    }

    /**
     * 时间比较查询.
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
    protected function parseTime(Query $query, string $key, string $exp, $value, $field, int $bindType): string
    {
        return $key . ' ' . substr($exp, 0, 2) . ' ' . $this->parseDateTime($query, $value, $field, $bindType);
    }

    /**
     * 大小比较查询.
     *
     * @param Query  $query    查询对象
     * @param string $key
     * @param string $exp
     * @param mixed  $value
     * @param string $field
     * @param int    $bindType
     * @param string $param
     *
     * @return string
     */
    protected function parseCompare(Query $query, string $key, string $exp, $value, $field, int $bindType, ?string $param = null): string
    {
        if (is_array($value)) {
            throw new Exception('where express error:' . $exp . var_export($value, true));
        }

        // 比较运算
        if ($value instanceof Closure) {
            $value = $this->parseClosure($query, $value);
        } elseif ($value instanceof Raw) {
            $value = $this->parseRaw($query, $value);
        } elseif ($value instanceof BackedEnum) {
            $value = $value->value;
        } elseif ($value instanceof UnitEnum) {
            $value = $value->name;
        }

        if ('=' == $exp && is_null($value)) {
            return $key . ' IS NULL';
        }

        if (is_null($param)) {
            return $key . ' ' . $exp . ' ' . $value;
        } else {
            return '( ' . $key . ' ' . $exp . ' ' . $value . ' ) ' . $param;
        }
    }

    /**
     * 时间范围查询.
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
    abstract protected function parseIn(Query $query, string $key, string $exp, $value, $field, int $bindType): string;

    /**
     * 闭包子查询.
     *
     * @param Query    $query 查询对象
     * @param Closure  $call
     * @param bool     $show
     *
     * @return string
     */
    protected function parseClosure(Query $query, Closure $call, bool $show = true): string
    {
        $newQuery = $query->newQuery()->removeOption();
        $call($newQuery);

        return $newQuery->buildSql($show);
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
    abstract protected function parseDateTime(Query $query, $value, string $key, int $bindType): string;

    /**
     * limit分析.
     *
     * @param Query $query 查询对象
     * @param mixed $limit
     *
     * @return string
     */
    abstract protected function parseLimit(Query $query, string $limit): string;

    /**
     * join分析.
     *
     * @param Query $query 查询对象
     * @param array $join
     *
     * @return string
     */
    abstract protected function parseJoin(Query $query, array $join): string;

    /**
     * order分析.
     *
     * @param Query $query 查询对象
     * @param array $order
     *
     * @return string
     */
    abstract protected function parseOrder(Query $query, array $order): string;

    /**
     * 分析Raw对象
     *
     * @param Query $query 查询对象
     * @param Raw   $raw   Raw对象
     *
     * @return string
     */
    abstract protected function parseRaw(Query $query, Raw $raw): string;

    /**
     * 随机排序.
     *
     * @param Query $query 查询对象
     *
     * @return string
     */
    abstract protected function parseRand(Query $query): string;

    /**
     * group分析.
     *
     * @param Query $query 查询对象
     * @param mixed $group
     *
     * @return string
     */
    abstract protected function parseGroup(Query $query, string | array $group): string;

    /**
     * having分析.
     *
     * @param Query  $query  查询对象
     * @param string $having
     *
     * @return string
     */
    abstract protected function parseHaving(Query $query, string $having): string;

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
    abstract protected function parseDistinct(Query $query, bool $distinct): string;

    /**
     * union分析.
     *
     * @param Query $query 查询对象
     * @param array $union
     *
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
     * index分析，可在操作链中指定需要强制使用的索引.
     *
     * @param Query $query 查询对象
     * @param mixed $index
     *
     * @return string
     */
    abstract protected function parseForce(Query $query, string | array $index): string;

    /**
     * 设置锁机制.
     *
     * @param Query       $query 查询对象
     * @param bool|string $lock
     *
     * @return string
     */
    abstract protected function parseLock(Query $query, bool | string $lock = false): string;

    /**
     * 生成查询SQL.
     *
     * @param Query $query 查询对象
     * @param bool  $one   是否仅获取一个记录
     *
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
                $this->parseField($query, $options['field'] ?? []),
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
            $this->selectSql
        );
    }

    /**
     * 生成Insert SQL.
     *
     * @param Query $query 查询对象
     *
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
            $this->insertSql
        );
    }

    /**
     * 生成insertall SQL.
     *
     * @param Query $query   查询对象
     * @param array $dataSet 数据集
     *
     * @return string
     */
    public function insertAll(Query $query, array $dataSet): string
    {
        $options = $query->getOptions();

        // 获取绑定信息
        $bind = $query->getFieldsBindType();

        // 获取合法的字段
        if (empty($options['field']) || '*' == $options['field']) {
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
            $this->insertAllSql
        );
    }

    /**
     * 生成slect insert SQL.
     *
     * @param Query  $query  查询对象
     * @param array  $fields 数据
     * @param string $table  数据表
     *
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
     * 生成update SQL.
     *
     * @param Query $query 查询对象
     *
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
            $this->updateSql
        );
    }

    /**
     * 生成delete SQL.
     *
     * @param Query $query 查询对象
     *
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
            $this->deleteSql
        );
    }
}
