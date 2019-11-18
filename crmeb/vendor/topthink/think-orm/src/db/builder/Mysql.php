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

namespace think\db\builder;

use think\db\Builder;
use think\db\exception\DbException as Exception;
use think\db\Query;
use think\db\Raw;

/**
 * mysql数据库驱动
 */
class Mysql extends Builder
{
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
        'parseRegexp'      => ['REGEXP', 'NOT REGEXP'],
        'parseNull'        => ['NOT NULL', 'NULL'],
        'parseBetweenTime' => ['BETWEEN TIME', 'NOT BETWEEN TIME'],
        'parseTime'        => ['< TIME', '> TIME', '<= TIME', '>= TIME'],
        'parseExists'      => ['NOT EXISTS', 'EXISTS'],
        'parseColumn'      => ['COLUMN'],
        'parseFindInSet'   => ['FIND IN SET'],
    ];

    /**
     * SELECT SQL表达式
     * @var string
     */
    protected $selectSql = 'SELECT%DISTINCT%%EXTRA% %FIELD% FROM %TABLE%%PARTITION%%FORCE%%JOIN%%WHERE%%GROUP%%HAVING%%UNION%%ORDER%%LIMIT% %LOCK%%COMMENT%';

    /**
     * INSERT SQL表达式
     * @var string
     */
    protected $insertSql = '%INSERT%%EXTRA% INTO %TABLE%%PARTITION% SET %SET% %DUPLICATE%%COMMENT%';

    /**
     * INSERT ALL SQL表达式
     * @var string
     */
    protected $insertAllSql = '%INSERT%%EXTRA% INTO %TABLE%%PARTITION% (%FIELD%) VALUES %DATA% %DUPLICATE%%COMMENT%';

    /**
     * UPDATE SQL表达式
     * @var string
     */
    protected $updateSql = 'UPDATE%EXTRA% %TABLE%%PARTITION% %JOIN% SET %SET% %WHERE% %ORDER%%LIMIT% %LOCK%%COMMENT%';

    /**
     * DELETE SQL表达式
     * @var string
     */
    protected $deleteSql = 'DELETE%EXTRA% FROM %TABLE%%PARTITION%%USING%%JOIN%%WHERE%%ORDER%%LIMIT% %LOCK%%COMMENT%';

    /**
     * 生成查询SQL
     * @access public
     * @param  Query  $query  查询对象
     * @param  bool   $one    是否仅获取一个记录
     * @return string
     */
    public function select(Query $query, bool $one = false): string
    {
        $options = $query->getOptions();

        return str_replace(
            ['%TABLE%', '%PARTITION%', '%DISTINCT%', '%EXTRA%', '%FIELD%', '%JOIN%', '%WHERE%', '%GROUP%', '%HAVING%', '%ORDER%', '%LIMIT%', '%UNION%', '%LOCK%', '%COMMENT%', '%FORCE%'],
            [
                $this->parseTable($query, $options['table']),
                $this->parsePartition($query, $options['partition']),
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

        $set = [];
        foreach ($data as $key => $val) {
            $set[] = $key . ' = ' . $val;
        }

        return str_replace(
            ['%INSERT%', '%EXTRA%', '%TABLE%', '%PARTITION%', '%SET%', '%DUPLICATE%', '%COMMENT%'],
            [
                !empty($options['replace']) ? 'REPLACE' : 'INSERT',
                $this->parseExtra($query, $options['extra']),
                $this->parseTable($query, $options['table']),
                $this->parsePartition($query, $options['partition']),
                implode(' , ', $set),
                $this->parseDuplicate($query, $options['duplicate']),
                $this->parseComment($query, $options['comment']),
            ],
            $this->insertSql);
    }

    /**
     * 生成insertall SQL
     * @access public
     * @param  Query     $query   查询对象
     * @param  array     $dataSet 数据集
     * @param  bool      $replace 是否replace
     * @return string
     */
    public function insertAll(Query $query, array $dataSet, bool $replace = false): string
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

        foreach ($dataSet as $data) {
            $data = $this->parseData($query, $data, $allowFields, $bind);

            $values[] = '( ' . implode(',', array_values($data)) . ' )';

            if (!isset($insertFields)) {
                $insertFields = array_keys($data);
            }
        }

        foreach ($insertFields as $field) {
            $fields[] = $this->parseKey($query, $field);
        }

        return str_replace(
            ['%INSERT%', '%EXTRA%', '%TABLE%', '%PARTITION%', '%FIELD%', '%DATA%', '%DUPLICATE%', '%COMMENT%'],
            [
                $replace ? 'REPLACE' : 'INSERT',
                $this->parseExtra($query, $options['extra']),
                $this->parseTable($query, $options['table']),
                $this->parsePartition($query, $options['partition']),
                implode(' , ', $fields),
                implode(' , ', $values),
                $this->parseDuplicate($query, $options['duplicate']),
                $this->parseComment($query, $options['comment']),
            ],
            $this->insertAllSql);
    }

    /**
     * 生成update SQL
     * @access public
     * @param  Query     $query  查询对象
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
            ['%TABLE%', '%PARTITION%', '%EXTRA%', '%SET%', '%JOIN%', '%WHERE%', '%ORDER%', '%LIMIT%', '%LOCK%', '%COMMENT%'],
            [
                $this->parseTable($query, $options['table']),
                $this->parsePartition($query, $options['partition']),
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
     * @param  Query  $query  查询对象
     * @return string
     */
    public function delete(Query $query): string
    {
        $options = $query->getOptions();

        return str_replace(
            ['%TABLE%', '%PARTITION%', '%EXTRA%', '%USING%', '%JOIN%', '%WHERE%', '%ORDER%', '%LIMIT%', '%LOCK%', '%COMMENT%'],
            [
                $this->parseTable($query, $options['table']),
                $this->parsePartition($query, $options['partition']),
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

    /**
     * 正则查询
     * @access protected
     * @param  Query        $query        查询对象
     * @param  string       $key
     * @param  string       $exp
     * @param  mixed        $value
     * @param  string       $field
     * @return string
     */
    protected function parseRegexp(Query $query, string $key, string $exp, $value, string $field): string
    {
        if ($value instanceof Raw) {
            $value = $value->getValue();
        }

        return $key . ' ' . $exp . ' ' . $value;
    }

    /**
     * FIND_IN_SET 查询
     * @access protected
     * @param  Query        $query        查询对象
     * @param  string       $key
     * @param  string       $exp
     * @param  mixed        $value
     * @param  string       $field
     * @return string
     */
    protected function parseFindInSet(Query $query, string $key, string $exp, $value, string $field): string
    {
        if ($value instanceof Raw) {
            $value = $value->getValue();
        }

        return 'FIND_IN_SET(' . $value . ', ' . $key . ')';
    }

    /**
     * 字段和表名处理
     * @access public
     * @param  Query     $query 查询对象
     * @param  mixed     $key   字段名
     * @param  bool      $strict   严格检测
     * @return string
     */
    public function parseKey(Query $query, $key, bool $strict = false): string
    {
        if (is_int($key)) {
            return (string) $key;
        } elseif ($key instanceof Raw) {
            return $key->getValue();
        }

        $key = trim($key);

        if (strpos($key, '->') && false === strpos($key, '(')) {
            // JSON字段支持
            list($field, $name) = explode('->', $key, 2);
            return 'json_extract(' . $this->parseKey($query, $field) . ', \'$' . (strpos($name, '[') === 0 ? '' : '.') . str_replace('->', '.', $name) . '\')';
        } elseif (strpos($key, '.') && !preg_match('/[,\'\"\(\)`\s]/', $key)) {
            list($table, $key) = explode('.', $key, 2);

            $alias = $query->getOptions('alias');

            if ('__TABLE__' == $table) {
                $table = $query->getOptions('table');
                $table = is_array($table) ? array_shift($table) : $table;
            }

            if (isset($alias[$table])) {
                $table = $alias[$table];
            }
        }

        if ($strict && !preg_match('/^[\w\.\*]+$/', $key)) {
            throw new Exception('not support data:' . $key);
        }

        if ('*' != $key && !preg_match('/[,\'\"\*\(\)`.\s]/', $key)) {
            $key = '`' . $key . '`';
        }

        if (isset($table)) {
            if (strpos($table, '.')) {
                $table = str_replace('.', '`.`', $table);
            }

            $key = '`' . $table . '`.' . $key;
        }

        return $key;
    }

    /**
     * 随机排序
     * @access protected
     * @param  Query     $query        查询对象
     * @return string
     */
    protected function parseRand(Query $query): string
    {
        return 'rand()';
    }

    /**
     * Partition 分析
     * @access protected
     * @param  Query        $query    查询对象
     * @param  string|array $partition  分区
     * @return string
     */
    protected function parsePartition(Query $query, $partition): string
    {
        if ('' == $partition) {
            return '';
        }

        if (is_string($partition)) {
            $partition = explode(',', $partition);
        }

        return ' PARTITION (' . implode(' , ', $partition) . ') ';
    }

    /**
     * ON DUPLICATE KEY UPDATE 分析
     * @access protected
     * @param  Query  $query    查询对象
     * @param  mixed  $duplicate
     * @return string
     */
    protected function parseDuplicate(Query $query, $duplicate): string
    {
        if ('' == $duplicate) {
            return '';
        }

        if ($duplicate instanceof Raw) {
            return ' ON DUPLICATE KEY UPDATE ' . $duplicate->getValue() . ' ';
        }

        if (is_string($duplicate)) {
            $duplicate = explode(',', $duplicate);
        }

        $updates = [];
        foreach ($duplicate as $key => $val) {
            if (is_numeric($key)) {
                $val       = $this->parseKey($query, $val);
                $updates[] = $val . ' = VALUES(' . $val . ')';
            } elseif ($val instanceof Raw) {
                $updates[] = $this->parseKey($query, $key) . " = " . $val->getValue();
            } else {
                $name      = $query->bindValue($val, $query->getConnection()->getFieldBindType($key));
                $updates[] = $this->parseKey($query, $key) . " = :" . $name;
            }
        }

        return ' ON DUPLICATE KEY UPDATE ' . implode(' , ', $updates) . ' ';
    }
}
