<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);
namespace think\db;

use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Command;
use MongoDB\Driver\Cursor;
use MongoDB\Driver\Exception\AuthenticationException;
use MongoDB\Driver\Exception\BulkWriteException;
use MongoDB\Driver\Exception\ConnectionException;
use MongoDB\Driver\Exception\InvalidArgumentException;
use MongoDB\Driver\Exception\RuntimeException;
use MongoDB\Driver\Query as MongoQuery;
use MongoDB\Driver\ReadPreference;
use MongoDB\Driver\WriteConcern;
use think\Collection;
use think\db\connector\Mongo as Connection;
use think\db\Query as BaseQuery;
use think\Exception;

class Mongo extends BaseQuery
{
    /**
     * 执行查询 返回数据集
     * @access public
     * @param  MongoQuery     $query 查询对象
     * @return mixed
     * @throws AuthenticationException
     * @throws InvalidArgumentException
     * @throws ConnectionException
     * @throws RuntimeException
     */
    public function mongoQuery(MongoQuery $query)
    {
        return $this->connection->mongoQuery($this, $query);
    }

    /**
     * 执行指令 返回数据集
     * @access public
     * @param  Command        $command 指令
     * @param  string         $dbName
     * @param  ReadPreference $readPreference readPreference
     * @param  string|array   $typeMap 指定返回的typeMap
     * @return mixed
     * @throws AuthenticationException
     * @throws InvalidArgumentException
     * @throws ConnectionException
     * @throws RuntimeException
     */
    public function command(Command $command, string $dbName = '', ReadPreference $readPreference = null, $typeMap = null)
    {
        return $this->connection->command($command, $dbName, $readPreference, $typeMap);
    }

    /**
     * 执行语句
     * @access public
     * @param  BulkWrite     $bulk
     * @return int
     * @throws AuthenticationException
     * @throws InvalidArgumentException
     * @throws ConnectionException
     * @throws RuntimeException
     * @throws BulkWriteException
     */
    public function mongoExecute(BulkWrite $bulk)
    {
        return $this->connection->mongoExecute($this, $bulk);
    }

    /**
     * 执行command
     * @access public
     * @param  string|array|object $command 指令
     * @param  mixed               $extra 额外参数
     * @param  string              $db 数据库名
     * @return array
     */
    public function cmd($command, $extra = null, string $db = ''): array
    {
        return $this->connection->cmd($this, $command, $extra, $db);
    }

    /**
     * 指定distinct查询
     * @access public
     * @param  string $field 字段名
     * @return array
     */
    public function getDistinct(string $field)
    {
        $result = $this->cmd('distinct', $field);
        return $result[0]['values'];
    }

    /**
     * 获取数据库的所有collection
     * @access public
     * @param  string  $db 数据库名称 留空为当前数据库
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
     * COUNT查询
     * @access public
     * @return integer
     */
    public function count(string $field = null): int
    {
        $this->parseOptions();

        $result = $this->cmd('count');

        return $result[0]['n'];
    }

    /**
     * 聚合查询
     * @access public
     * @param  string $aggregate 聚合指令
     * @param  string $field     字段名
     * @param  bool   $force   强制转为数字类型
     * @return mixed
     */
    public function aggregate(string $aggregate, $field, bool $force = false)
    {
        $this->parseOptions();

        $result = $this->cmd('aggregate', [strtolower($aggregate), $field]);

        $value = $result[0]['aggregate'] ?? 0;

        if ($force) {
            $value += 0;
        }

        return $value;
    }

    /**
     * 多聚合操作
     *
     * @param  array $aggregate 聚合指令, 可以聚合多个参数, 如 ['sum' => 'field1', 'avg' => 'field2']
     * @param  array $groupBy 类似mysql里面的group字段, 可以传入多个字段, 如 ['field_a', 'field_b', 'field_c']
     * @return array 查询结果
     */
    public function multiAggregate(array $aggregate, array $groupBy): array
    {
        $this->parseOptions();

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
     * @access public
     * @param string  $field 字段名
     * @param float   $step  增长值
     * @param integer $lazyTime 延时时间(s)
     * @param string  $op inc/dec
     * @return $this
     */
    public function inc(string $field, float $step = 1, int $lazyTime = 0, string $op = 'inc')
    {
        if ($lazyTime > 0) {
            // 延迟写入
            $condition = $this->options['where'] ?? [];

            $guid = md5($this->getTable() . '_' . $field . '_' . serialize($condition));
            $step = $this->connection->lazyWrite($op, $guid, $step, $lazyTime);

            if (false === $step) {
                return $this;
            }
        }

        $this->data($field, ['$' . $op, $step]);

        return $this;
    }

    /**
     * 字段值减少
     * @access public
     * @param  string  $field 字段名
     * @param  float   $step  减少值
     * @param  integer $lazyTime 延时时间(s)
     * @return $this
     */
    public function dec(string $field, float $step = 1, int $lazyTime = 0)
    {
        return $this->inc($field, -1 * $step, $lazyTime);
    }

    /**
     * 指定当前操作的collection
     * @access public
     * @param  string $collection
     * @return $this
     */
    public function collection(string $collection)
    {
        return $this->table($collection);
    }

    /**
     * 设置typeMap
     * @access public
     * @param string|array $typeMap
     * @return $this
     */
    public function typeMap($typeMap)
    {
        $this->options['typeMap'] = $typeMap;
        return $this;
    }

    /**
     * awaitData
     * @access public
     * @param bool $awaitData
     * @return $this
     */
    public function awaitData(bool $awaitData)
    {
        $this->options['awaitData'] = $awaitData;
        return $this;
    }

    /**
     * batchSize
     * @access public
     * @param integer $batchSize
     * @return $this
     */
    public function batchSize(int $batchSize)
    {
        $this->options['batchSize'] = $batchSize;
        return $this;
    }

    /**
     * exhaust
     * @access public
     * @param bool $exhaust
     * @return $this
     */
    public function exhaust(bool $exhaust)
    {
        $this->options['exhaust'] = $exhaust;
        return $this;
    }

    /**
     * 设置modifiers
     * @access public
     * @param array $modifiers
     * @return $this
     */
    public function modifiers(array $modifiers)
    {
        $this->options['modifiers'] = $modifiers;
        return $this;
    }

    /**
     * 设置noCursorTimeout
     * @access public
     * @param bool $noCursorTimeout
     * @return $this
     */
    public function noCursorTimeout(bool $noCursorTimeout)
    {
        $this->options['noCursorTimeout'] = $noCursorTimeout;
        return $this;
    }

    /**
     * 设置oplogReplay
     * @access public
     * @param bool $oplogReplay
     * @return $this
     */
    public function oplogReplay(bool $oplogReplay)
    {
        $this->options['oplogReplay'] = $oplogReplay;
        return $this;
    }

    /**
     * 设置partial
     * @access public
     * @param bool $partial
     * @return $this
     */
    public function partial(bool $partial)
    {
        $this->options['partial'] = $partial;
        return $this;
    }

    /**
     * maxTimeMS
     * @access public
     * @param string $maxTimeMS
     * @return $this
     */
    public function maxTimeMS(string $maxTimeMS)
    {
        $this->options['maxTimeMS'] = $maxTimeMS;
        return $this;
    }

    /**
     * collation
     * @access public
     * @param array $collation
     * @return $this
     */
    public function collation(array $collation)
    {
        $this->options['collation'] = $collation;
        return $this;
    }

    /**
     * 获取执行的SQL语句而不进行实际的查询
     * @access public
     * @param bool $fetch 是否返回sql
     * @return $this|Fetch
     */
    public function fetchSql(bool $fetch = true)
    {
        $this->options['fetch_sql'] = $fetch;

        if ($fetch) {
            throw new Exception('Mongo not support fetchSql');
        }

        return $this;
    }

    /**
     * 设置返回字段
     * @access public
     * @param  mixed $field 字段信息
     * @param  bool  $except 是否排除
     * @return $this
     */
    public function field($field, bool $except = false)
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
                $projection[$val] = $except ? 0 : 1;
            } else {
                $projection[$key] = $val;
            }
        }

        $this->options['projection'] = $projection;

        return $this;
    }

    /**
     * 设置skip
     * @access public
     * @param integer $skip
     * @return $this
     */
    public function skip(int $skip)
    {
        $this->options['skip'] = $skip;
        return $this;
    }

    /**
     * 设置slaveOk
     * @access public
     * @param bool $slaveOk
     * @return $this
     */
    public function slaveOk(bool $slaveOk)
    {
        $this->options['slaveOk'] = $slaveOk;
        return $this;
    }

    /**
     * 指定查询数量
     * @access public
     * @param int $offset 起始位置
     * @param int $length 查询数量
     * @return $this
     */
    public function limit(int $offset, int $length = null)
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
     * 设置sort
     * @access public
     * @param  array|string $field
     * @param  string       $order
     * @return $this
     */
    public function order($field, string $order = '')
    {
        if (is_array($field)) {
            $this->options['sort'] = $field;
        } else {
            $this->options['sort'][$field] = 'asc' == strtolower($order) ? 1 : -1;
        }
        return $this;
    }

    /**
     * 设置tailable
     * @access public
     * @param  bool $tailable
     * @return $this
     */
    public function tailable(bool $tailable)
    {
        $this->options['tailable'] = $tailable;
        return $this;
    }

    /**
     * 设置writeConcern对象
     * @access public
     * @param  WriteConcern $writeConcern
     * @return $this
     */
    public function writeConcern(WriteConcern $writeConcern)
    {
        $this->options['writeConcern'] = $writeConcern;
        return $this;
    }

    /**
     * 获取当前数据表的主键
     * @access public
     * @return string|array
     */
    public function getPk()
    {
        return $this->pk ?: $this->connection->getConfig('pk');
    }

    /**
     * 执行查询但只返回Cursor对象
     * @access public
     * @return Cursor
     */
    public function getCursor(): Cursor
    {
        $this->parseOptions();

        return $this->connection->getCursor($this);
    }

    /**
     * 分析表达式（可用于查询或者写入操作）
     * @access public
     * @return array
     */
    public function parseOptions(): array
    {
        $options = $this->options;

        // 获取数据表
        if (empty($options['table'])) {
            $options['table'] = $this->getTable();
        }

        foreach (['where', 'data'] as $name) {
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

        if (!isset($options['projection'])) {
            $options['projection'] = [];
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
            list($page, $listRows) = $options['page'];
            $page                  = $page > 0 ? $page : 1;
            $listRows              = $listRows > 0 ? $listRows : (is_numeric($options['limit']) ? $options['limit'] : 20);
            $offset                = $listRows * ($page - 1);
            $options['skip']       = intval($offset);
            $options['limit']      = intval($listRows);
        }

        $this->options = $options;

        return $options;
    }

}
