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

use MongoDB\Driver\Command;
use MongoDB\Driver\Cursor;
use MongoDB\Driver\Exception\AuthenticationException;
use MongoDB\Driver\Exception\ConnectionException;
use MongoDB\Driver\Exception\InvalidArgumentException;
use MongoDB\Driver\Exception\RuntimeException;
use MongoDB\Driver\ReadPreference;
use MongoDB\Driver\WriteConcern;
use think\db\exception\DbException as Exception;
use think\Paginator;

class Mongo extends BaseQuery
{
    /**
     * 当前数据库连接对象
     *
     * @var \think\db\connector\Mongo
     */
    protected $connection;

    /**
     * 执行指令 返回数据集.
     *
     * @param Command        $command        指令
     * @param string         $dbName
     * @param ReadPreference $readPreference readPreference
     * @param string|array   $typeMap        指定返回的typeMap
     *
     * @throws AuthenticationException
     * @throws InvalidArgumentException
     * @throws ConnectionException
     * @throws RuntimeException
     *
     * @return mixed
     */
    public function command(Command $command, string $dbName = '', ?ReadPreference $readPreference = null, $typeMap = null)
    {
        return $this->connection->command($command, $dbName, $readPreference, $typeMap);
    }

    /**
     * 执行command.
     *
     * @param string|array|object $command 指令
     * @param mixed               $extra   额外参数
     * @param string              $db      数据库名
     *
     * @return array
     */
    public function cmd($command, $extra = null, string $db = ''): array
    {
        $this->parseOptions();

        return $this->connection->cmd($this, $command, $extra, $db);
    }

    /**
     * 指定distinct查询.
     *
     * @param string $field 字段名
     *
     * @return array
     */
    public function getDistinct(string $field)
    {
        $result = $this->cmd('distinct', $field);

        return $result[0]['values'];
    }

    /**
     * 获取数据库的所有collection.
     *
     * @param string $db 数据库名称 留空为当前数据库
     *
     * @throws Exception
     */
    public function listCollections(string $db = '')
    {
        $cursor = $this->cmd('listCollections', null, $db);
        $result = [];
        foreach ($cursor as $collection) {
            $result[] = $collection['name'];
        }

        return $result;
    }

    /**
     * COUNT查询.
     *
     * @param string $field 字段名
     *
     * @return int
     */
    public function count(?string $field = null): int
    {
        $result = $this->cmd('count');

        return $result[0]['n'];
    }

    /**
     * 聚合查询.
     *
     * @param string $aggregate 聚合指令
     * @param string $field     字段名
     * @param bool   $force     强制转为数字类型
     *
     * @return mixed
     */
    public function aggregate(string $aggregate, $field, bool $force = false, bool $one = false)
    {
        $result = $this->cmd('aggregate', [strtolower($aggregate), $field]);
        $value  = $result[0]['aggregate'] ?? 0;

        if ($force) {
            $value += 0;
        }

        return $value;
    }

    /**
     * 多聚合操作.
     *
     * @param array $aggregate 聚合指令, 可以聚合多个参数, 如 ['sum' => 'field1', 'avg' => 'field2']
     * @param array $groupBy   类似mysql里面的group字段, 可以传入多个字段, 如 ['field_a', 'field_b', 'field_c']
     *
     * @return array 查询结果
     */
    public function multiAggregate(array $aggregate, array $groupBy): array
    {
        $result = $this->cmd('multiAggregate', [$aggregate, $groupBy]);

        foreach ($result as &$row) {
            if (isset($row['_id']) && !empty($row['_id'])) {
                foreach ($row['_id'] as $k => $v) {
                    $row[$k] = $v;
                }
                unset($row['_id']);
            }
        }

        return $result;
    }

    /**
     * 字段值增长
     *
     * @param string    $field 字段名
     * @param float|int $step  增长值
     *
     * @return $this
     */
    public function inc(string $field, float|int $step = 1)
    {
        $this->options['data'][$field] = ['$inc', $step];

        return $this;
    }

    /**
     * 字段值减少.
     *
     * @param string    $field 字段名
     * @param float|int $step  减少值
     *
     * @return $this
     */
    public function dec(string $field, float|int $step = 1)
    {
        return $this->inc($field, -1 * $step);
    }

    /**
     * 指定当前操作的Collection.
     *
     * @param string $table 表名
     *
     * @return $this
     */
    public function table($table)
    {
        $this->options['table'] = $table;

        return $this;
    }

    /**
     * table方法的别名.
     *
     * @param string $collection
     *
     * @return $this
     */
    public function collection(string $collection)
    {
        return $this->table($collection);
    }

    /**
     * 设置typeMap.
     *
     * @param string|array $typeMap
     *
     * @return $this
     */
    public function typeMap($typeMap)
    {
        $this->options['typeMap'] = $typeMap;

        return $this;
    }

    /**
     * awaitData.
     *
     * @param bool $awaitData
     *
     * @return $this
     */
    public function awaitData(bool $awaitData)
    {
        $this->options['awaitData'] = $awaitData;

        return $this;
    }

    /**
     * batchSize.
     *
     * @param int $batchSize
     *
     * @return $this
     */
    public function batchSize(int $batchSize)
    {
        $this->options['batchSize'] = $batchSize;

        return $this;
    }

    /**
     * exhaust.
     *
     * @param bool $exhaust
     *
     * @return $this
     */
    public function exhaust(bool $exhaust)
    {
        $this->options['exhaust'] = $exhaust;

        return $this;
    }

    /**
     * 设置modifiers.
     *
     * @param array $modifiers
     *
     * @return $this
     */
    public function modifiers(array $modifiers)
    {
        $this->options['modifiers'] = $modifiers;

        return $this;
    }

    /**
     * 设置noCursorTimeout.
     *
     * @param bool $noCursorTimeout
     *
     * @return $this
     */
    public function noCursorTimeout(bool $noCursorTimeout)
    {
        $this->options['noCursorTimeout'] = $noCursorTimeout;

        return $this;
    }

    /**
     * 设置oplogReplay.
     *
     * @param bool $oplogReplay
     *
     * @return $this
     */
    public function oplogReplay(bool $oplogReplay)
    {
        $this->options['oplogReplay'] = $oplogReplay;

        return $this;
    }

    /**
     * 设置partial.
     *
     * @param bool $partial
     *
     * @return $this
     */
    public function partial(bool $partial)
    {
        $this->options['partial'] = $partial;

        return $this;
    }

    /**
     * maxTimeMS.
     *
     * @param string $maxTimeMS
     *
     * @return $this
     */
    public function maxTimeMS(string $maxTimeMS)
    {
        $this->options['maxTimeMS'] = $maxTimeMS;

        return $this;
    }

    /**
     * collation.
     *
     * @param array $collation
     *
     * @return $this
     */
    public function collation(array $collation)
    {
        $this->options['collation'] = $collation;

        return $this;
    }

    /**
     * 设置是否REPLACE.
     *
     * @param bool $replace 是否使用REPLACE写入数据
     *
     * @return $this
     */
    public function replace(bool $replace = true)
    {
        return $this;
    }

    /**
     * 设置返回字段.
     *
     * @param mixed $field 字段信息
     *
     * @return $this
     */
    public function field($field)
    {
        if (empty($field) || '*' == $field) {
            return $this;
        }

        if (is_string($field)) {
            $field = array_map('trim', explode(',', $field));
        }

        $projection = [];
        foreach ($field as $key => $val) {
            if (is_numeric($key)) {
                $projection[$val] = 1;
            } else {
                $projection[$key] = $val;
            }
        }

        $this->options['projection'] = $projection;

        return $this;
    }

    /**
     * 指定要排除的查询字段.
     *
     * @param array|string $field 要排除的字段
     *
     * @return $this
     */
    public function withoutField($field)
    {
        if (empty($field) || '*' == $field) {
            return $this;
        }

        if (is_string($field)) {
            $field = array_map('trim', explode(',', $field));
        }

        $projection = [];
        foreach ($field as $key => $val) {
            if (is_numeric($key)) {
                $projection[$val] = 0;
            } else {
                $projection[$key] = $val;
            }
        }

        $this->options['projection'] = $projection;

        return $this;
    }

    /**
     * 设置skip.
     *
     * @param int $skip
     *
     * @return $this
     */
    public function skip(int $skip)
    {
        $this->options['skip'] = $skip;

        return $this;
    }

    /**
     * 设置slaveOk.
     *
     * @param bool $slaveOk
     *
     * @return $this
     */
    public function slaveOk(bool $slaveOk)
    {
        $this->options['slaveOk'] = $slaveOk;

        return $this;
    }

    /**
     * 指定查询数量.
     *
     * @param int $offset 起始位置
     * @param int $length 查询数量
     *
     * @return $this
     */
    public function limit(int $offset, ?int $length = null)
    {
        if (is_null($length)) {
            $length = $offset;
            $offset = 0;
        }

        $this->options['skip']  = $offset;
        $this->options['limit'] = $length;

        return $this;
    }

    /**
     * 设置sort.
     *
     * @param array|string $field
     * @param string       $order
     *
     * @return $this
     */
    public function order($field, string $order = '')
    {
        if (is_array($field)) {
            $this->options['sort'] = array_map(function ($val) {
                return 'asc' == strtolower($val) ? 1 : -1;
            }, $field);
        } else {
            $this->options['sort'][$field] = 'asc' == strtolower($order) ? 1 : -1;
        }

        return $this;
    }

    /**
     * 设置tailable.
     *
     * @param bool $tailable
     *
     * @return $this
     */
    public function tailable(bool $tailable)
    {
        $this->options['tailable'] = $tailable;

        return $this;
    }

    /**
     * 设置writeConcern对象
     *
     * @param WriteConcern $writeConcern
     *
     * @return $this
     */
    public function writeConcern(WriteConcern $writeConcern)
    {
        $this->options['writeConcern'] = $writeConcern;

        return $this;
    }

    /**
     * 获取当前数据表的主键.
     *
     * @return string|array
     */
    public function getPk()
    {
        return $this->pk ?: $this->connection->getConfig('pk');
    }

    /**
     * 执行查询但只返回Cursor对象
     *
     * @return Cursor
     */
    public function cursor(): Cursor
    {
        return $this->getCursor();
    }
    
    /**
     * 执行查询但只返回Cursor对象
     *
     * @return Cursor
     */
    public function getCursor(): Cursor
    {
        $this->parseOptions();

        return $this->connection->cursor($this);
    }

    /**
     * 分页查询.
     *
     * @param int|array $listRows 每页数量 数组表示配置参数
     * @param int|bool  $simple   是否简洁模式或者总记录数
     *
     * @throws Exception
     *
     * @return Paginator
     */
    public function paginate($listRows = null, $simple = false): Paginator
    {
        if (is_int($simple)) {
            $total  = $simple;
            $simple = false;
        }

        $defaultConfig = [
            'query'     => [], //url额外参数
            'fragment'  => '', //url锚点
            'var_page'  => 'page', //分页变量
            'list_rows' => 15, //每页数量
        ];

        if (is_array($listRows)) {
            $config   = array_merge($defaultConfig, $listRows);
            $listRows = intval($config['list_rows']);
        } else {
            $config   = $defaultConfig;
            $listRows = intval($listRows ?: $config['list_rows']);
        }

        $page = isset($config['page']) ? (int) $config['page'] : Paginator::getCurrentPage($config['var_page']);

        $page = max($page, 1);

        $config['path'] = $config['path'] ?? Paginator::getCurrentPath();

        if (!isset($total) && !$simple) {
            $options = $this->getOptions();

            unset($this->options['order'], $this->options['limit'], $this->options['page'], $this->options['field']);

            $total   = $this->count();
            $results = $this->options($options)->page($page, $listRows)->select();
        } elseif ($simple) {
            $results = $this->limit(($page - 1) * $listRows, $listRows + 1)->select();
            $total   = null;
        } else {
            $results = $this->page($page, $listRows)->select();
        }

        $this->removeOption('limit');
        $this->removeOption('page');

        return Paginator::make($results, $listRows, $page, $total, $simple, $config);
    }

    /**
     * 分批数据返回处理.
     *
     * @param int          $count    每次处理的数据数量
     * @param callable     $callback 处理回调方法
     * @param string|array $column   分批处理的字段名
     * @param string       $order    字段排序
     *
     * @throws Exception
     *
     * @return bool
     */
    public function chunk(int $count, callable $callback, $column = null, string $order = 'asc'): bool
    {
        $options = $this->getOptions();
        $column  = $column ?: $this->getPk();

        if (isset($options['order'])) {
            unset($options['order']);
        }

        if (is_array($column)) {
            $times = 1;
            $query = $this->options($options)->page($times, $count);
        } else {
            $query = $this->options($options)->limit($count);

            if (str_contains($column, '.')) {
                [$alias, $key] = explode('.', $column);
            } else {
                $key = $column;
                if ($key == '_id' && $this->connection->getConfig('pk_convert_id')) {
                    $key = 'id';
                }
            }
        }

        $resultSet = $query->order($column, $order)->select();

        while (count($resultSet) > 0) {
            if (false === call_user_func($callback, $resultSet)) {
                return false;
            }

            if (isset($times)) {
                $times++;
                $query = $this->options($options)->page($times, $count);
            } else {
                $end    = $resultSet->pop();
                $lastId = is_array($end) ? $end[$key] : $end->getData($key);

                $query = $this->options($options)
                    ->limit($count)
                    ->where($column, 'asc' == strtolower($order) ? '>' : '<', $lastId);
            }

            $resultSet = $query->order($column, $order)->select();
        }

        return true;
    }

    /**
     * 分析表达式（可用于查询或者写入操作）.
     *
     * @return array
     */
    public function parseOptions(): array
    {
        $options = $this->options;

        // 获取数据表
        if (empty($options['table'])) {
            $options['table'] = $this->getTable();
        }

        foreach (['where', 'data', 'projection', 'filter', 'json', 'with_attr', 'with_relation_attr'] as $name) {
            if (!isset($options[$name])) {
                $options[$name] = [];
            }
        }

        $modifiers = empty($options['modifiers']) ? [] : $options['modifiers'];
        if (isset($options['comment'])) {
            $modifiers['$comment'] = $options['comment'];
        }

        if (isset($options['maxTimeMS'])) {
            $modifiers['$maxTimeMS'] = $options['maxTimeMS'];
        }

        if (!empty($modifiers)) {
            $options['modifiers'] = $modifiers;
        }

        if (!isset($options['typeMap'])) {
            $options['typeMap'] = $this->getConfig('type_map');
        }

        if (!isset($options['limit'])) {
            $options['limit'] = 0;
        }

        foreach (['master', 'fetch_sql', 'fetch_cursor'] as $name) {
            if (!isset($options[$name])) {
                $options[$name] = false;
            }
        }

        if (isset($options['page'])) {
            // 根据页数计算limit
            [$page, $listRows] = $options['page'];

            $page             = $page > 0 ? $page : 1;
            $listRows         = $listRows > 0 ? $listRows : (is_numeric($options['limit']) ? $options['limit'] : 20);
            $offset           = $listRows * ($page - 1);
            $options['skip']  = intval($offset);
            $options['limit'] = intval($listRows);
        }

        $this->options = $options;

        return $options;
    }

    /**
     * 获取字段类型信息.
     *
     * @return array
     */
    public function getFieldsType(): array
    {
        if (!empty($this->options['field_type'])) {
            return $this->options['field_type'];
        }

        return [];
    }

    /**
     * 获取字段类型信息.
     *
     * @param string $field 字段名
     *
     * @return string|null
     */
    public function getFieldType(string $field)
    {
        $fieldType = $this->getFieldsType();

        return $fieldType[$field] ?? null;
    }

    /**
     * 获取字段类型
     * 
     * @return array
     */
    public function getType()
    {
        return $this->getFieldsType();
    }

    /**
     * 获取自增主键
     * 
     * @return string
     */
    public function getAutoInc()
    {
        return '';
    }

    /**
     * 设置自增主键
     * 
     * @param string $autoInc
     * @return static
     */
    public function autoInc(?string $autoInc)
    {
        return $this;
    }
}
