<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace think\db\connector;

use Closure;
use MongoDB\BSON\ObjectID;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Command;
use MongoDB\Driver\Cursor;
use MongoDB\Driver\Exception\AuthenticationException;
use MongoDB\Driver\Exception\BulkWriteException;
use MongoDB\Driver\Exception\ConnectionException;
use MongoDB\Driver\Exception\InvalidArgumentException;
use MongoDB\Driver\Exception\RuntimeException;
use MongoDB\Driver\Manager;
use MongoDB\Driver\Query as MongoQuery;
use MongoDB\Driver\ReadPreference;
use MongoDB\Driver\WriteConcern;
use think\db\BaseQuery;
use think\db\builder\Mongo as Builder;
use think\db\Connection;
use think\db\exception\DbException as Exception;
use think\db\Mongo as Query;

/**
 * Mongo数据库驱动
 */
class Mongo extends Connection
{

    // 查询数据类型
    protected $dbName  = '';
    protected $typeMap = 'array';
    protected $mongo; // MongoDb Object
    protected $cursor; // MongoCursor Object
    protected $session_uuid; // sessions会话列表当前会话数组key 随机生成
    protected $sessions = []; // 会话列表

    /** @var Builder */
    protected $builder;

    // 数据库连接参数配置
    protected $config = [
        // 数据库类型
        'type'            => '',
        // 服务器地址
        'hostname'        => '',
        // 数据库名
        'database'        => '',
        // 是否是复制集
        'is_replica_set'  => false,
        // 用户名
        'username'        => '',
        // 密码
        'password'        => '',
        // 端口
        'hostport'        => '',
        // 连接dsn
        'dsn'             => '',
        // 数据库连接参数
        'params'          => [],
        // 数据库编码默认采用utf8
        'charset'         => 'utf8',
        // 主键名
        'pk'              => '_id',
        // 主键类型
        'pk_type'         => 'ObjectID',
        // 数据库表前缀
        'prefix'          => '',
        // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
        'deploy'          => 0,
        // 数据库读写是否分离 主从式有效
        'rw_separate'     => false,
        // 读写分离后 主服务器数量
        'master_num'      => 1,
        // 指定从服务器序号
        'slave_no'        => '',
        // 是否严格检查字段是否存在
        'fields_strict'   => true,
        // 开启字段缓存
        'fields_cache'    => false,
        // 监听SQL
        'trigger_sql'     => true,
        // 自动写入时间戳字段
        'auto_timestamp'  => false,
        // 时间字段取出后的默认时间格式
        'datetime_format' => 'Y-m-d H:i:s',
        // 是否_id转换为id
        'pk_convert_id'   => false,
        // typeMap
        'type_map'        => ['root' => 'array', 'document' => 'array'],
    ];

    /**
     * 获取当前连接器类对应的Query类
     * @access public
     * @return string
     */
    public function getQueryClass(): string
    {
        return Query::class;
    }

    /**
     * 获取当前的builder实例对象
     * @access public
     * @return Builder
     */
    public function getBuilder()
    {
        return $this->builder;
    }

    /**
     * 获取当前连接器类对应的Builder类
     * @access public
     * @return string
     */
    public function getBuilderClass(): string
    {
        return Builder::class;
    }

    /**
     * 连接数据库方法
     * @access public
     * @param  array   $config 连接参数
     * @param  integer $linkNum 连接序号
     * @return Manager
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function connect(array $config = [], $linkNum = 0)
    {
        if (!isset($this->links[$linkNum])) {
            if (empty($config)) {
                $config = $this->config;
            } else {
                $config = array_merge($this->config, $config);
            }

            $this->dbName  = $config['database'];
            $this->typeMap = $config['type_map'];

            if ($config['pk_convert_id'] && '_id' == $config['pk']) {
                $this->config['pk'] = 'id';
            }

            if (empty($config['dsn'])) {
                $config['dsn'] = 'mongodb://' . ($config['username'] ? "{$config['username']}" : '') . ($config['password'] ? ":{$config['password']}@" : '') . $config['hostname'] . ($config['hostport'] ? ":{$config['hostport']}" : '');
            }

            $startTime = microtime(true);

            $this->links[$linkNum] = new Manager($config['dsn'], $config['params']);

            if (!empty($config['trigger_sql'])) {
                // 记录数据库连接信息
                $this->trigger('CONNECT:[ UseTime:' . number_format(microtime(true) - $startTime, 6) . 's ] ' . $config['dsn']);
            }

        }

        return $this->links[$linkNum];
    }

    /**
     * 获取Mongo Manager对象
     * @access public
     * @return Manager|null
     */
    public function getMongo()
    {
        return $this->mongo ?: null;
    }

    /**
     * 设置/获取当前操作的database
     * @access public
     * @param string $db db
     * @return string
     */
    public function db(string $db = null)
    {
        if (is_null($db)) {
            return $this->dbName;
        } else {
            $this->dbName = $db;
        }
    }

    /**
     * 执行查询但只返回Cursor对象
     * @access public
     * @param Query $query 查询对象
     * @return Cursor
     */
    public function cursor($query)
    {
        // 分析查询表达式
        $options = $query->parseOptions();

        // 生成MongoQuery对象
        $mongoQuery = $this->builder->select($query);

        $master = $query->getOptions('master') ? true : false;

        // 执行查询操作
        return $this->getCursor($query, $mongoQuery, $master);
    }

    /**
     * 执行查询并返回Cursor对象
     * @access public
     * @param BaseQuery          $query 查询对象
     * @param MongoQuery|Closure $mongoQuery Mongo查询对象
     * @param bool               $master 是否主库操作
     * @return Cursor
     * @throws AuthenticationException
     * @throws InvalidArgumentException
     * @throws ConnectionException
     * @throws RuntimeException
     */
    public function getCursor(BaseQuery $query, $mongoQuery, bool $master = false): Cursor
    {
        $this->initConnect($master);
        $this->db->updateQueryTimes();

        $options   = $query->getOptions();
        $namespace = $options['table'];

        if (false === strpos($namespace, '.')) {
            $namespace = $this->dbName . '.' . $namespace;
        }

        if (!empty($this->queryStr)) {
            // 记录执行指令
            $this->queryStr = 'db' . strstr($namespace, '.') . '.' . $this->queryStr;
        }

        if ($mongoQuery instanceof Closure) {
            $mongoQuery = $mongoQuery($query);
        }

        $readPreference       = $options['readPreference'] ?? null;
        $this->queryStartTime = microtime(true);

        if ($session = $this->getSession()) {
            $this->cursor = $this->mongo->executeQuery($namespace, $query, [
                'readPreference' => is_null($readPreference) ? new ReadPreference(ReadPreference::RP_PRIMARY) : $readPreference,
                'session'        => $session,
            ]);
        } else {
            $this->cursor = $this->mongo->executeQuery($namespace, $mongoQuery, $readPreference);
        }

        // SQL监控
        if (!empty($this->config['trigger_sql'])) {
            $this->trigger('', $master);
        }

        return $this->cursor;
    }

    /**
     * 执行查询 返回数据集
     * @access public
     * @param  MongoQuery $query 查询对象
     * @return mixed
     * @throws AuthenticationException
     * @throws InvalidArgumentException
     * @throws ConnectionException
     * @throws RuntimeException
     */
    public function query(MongoQuery $query)
    {
        return $this->mongoQuery($this->newQuery(), $query);
    }

    /**
     * 执行语句
     * @access public
     * @param  BulkWrite $bulk
     * @return int
     * @throws AuthenticationException
     * @throws InvalidArgumentException
     * @throws ConnectionException
     * @throws RuntimeException
     * @throws BulkWriteException
     */
    public function execute(BulkWrite $bulk)
    {
        return $this->mongoExecute($this->newQuery(), $bulk);
    }

    /**
     * 执行查询
     * @access protected
     * @param BaseQuery          $query 查询对象
     * @param MongoQuery|Closure $mongoQuery Mongo查询对象
     * @return array
     * @throws AuthenticationException
     * @throws InvalidArgumentException
     * @throws ConnectionException
     * @throws RuntimeException
     */
    protected function mongoQuery(BaseQuery $query, $mongoQuery): array
    {
        $options = $query->parseOptions();

        if ($query->getOptions('cache')) {
            // 检查查询缓存
            $cacheItem = $this->parseCache($query, $query->getOptions('cache'));
            $key       = $cacheItem->getKey();

            if ($this->cache->has($key)) {
                return $this->cache->get($key);
            }
        }

        if ($mongoQuery instanceof Closure) {
            $mongoQuery = $mongoQuery($query);
        }

        $master = $query->getOptions('master') ? true : false;
        $this->getCursor($query, $mongoQuery, $master);

        $resultSet = $this->getResult($options['typeMap']);

        if (isset($cacheItem) && $resultSet) {
            // 缓存数据集
            $cacheItem->set($resultSet);
            $this->cacheData($cacheItem);
        }

        return $resultSet;
    }

    /**
     * 执行写操作
     * @access protected
     * @param BaseQuery $query
     * @param BulkWrite $bulk
     *
     * @return WriteResult
     * @throws AuthenticationException
     * @throws InvalidArgumentException
     * @throws ConnectionException
     * @throws RuntimeException
     * @throws BulkWriteException
     */
    protected function mongoExecute(BaseQuery $query, BulkWrite $bulk)
    {
        $this->initConnect(true);
        $this->db->updateQueryTimes();

        $options = $query->getOptions();

        $namespace = $options['table'];
        if (false === strpos($namespace, '.')) {
            $namespace = $this->dbName . '.' . $namespace;
        }

        if (!empty($this->queryStr)) {
            // 记录执行指令
            $this->queryStr = 'db' . strstr($namespace, '.') . '.' . $this->queryStr;
        }

        $writeConcern         = $options['writeConcern'] ?? null;
        $this->queryStartTime = microtime(true);

        if ($session = $this->getSession()) {
            $writeResult = $this->mongo->executeBulkWrite($namespace, $bulk, [
                'session'      => $session,
                'writeConcern' => is_null($writeConcern) ? new WriteConcern(1) : $writeConcern,
            ]);
        } else {
            $writeResult = $this->mongo->executeBulkWrite($namespace, $bulk, $writeConcern);
        }

        // SQL监控
        if (!empty($this->config['trigger_sql'])) {
            $this->trigger();
        }

        $this->numRows = $writeResult->getMatchedCount();

        if ($query->getOptions('cache')) {
            // 清理缓存数据
            $cacheItem = $this->parseCache($query, $query->getOptions('cache'));
            $key       = $cacheItem->getKey();
            $tag       = $cacheItem->getTag();

            if (isset($key) && $this->cache->has($key)) {
                $this->cache->delete($key);
            } elseif (!empty($tag) && method_exists($this->cache, 'tag')) {
                $this->cache->tag($tag)->clear();
            }
        }

        return $writeResult;
    }

    /**
     * 执行指令
     * @access public
     * @param  Command        $command 指令
     * @param  string         $dbName 当前数据库名
     * @param  ReadPreference $readPreference readPreference
     * @param  string|array   $typeMap 指定返回的typeMap
     * @param  bool           $master 是否主库操作
     * @return array
     * @throws AuthenticationException
     * @throws InvalidArgumentException
     * @throws ConnectionException
     * @throws RuntimeException
     */
    public function command(Command $command, string $dbName = '', ReadPreference $readPreference = null, $typeMap = null, bool $master = false): array
    {
        $this->initConnect($master);
        $this->db->updateQueryTimes();

        $this->queryStartTime = microtime(true);

        $dbName = $dbName ?: $this->dbName;

        if (!empty($this->queryStr)) {
            $this->queryStr = 'db.' . $this->queryStr;
        }

        if ($session = $this->getSession()) {
            $this->cursor = $this->mongo->executeCommand($dbName, $command, [
                'readPreference' => is_null($readPreference) ? new ReadPreference(ReadPreference::RP_PRIMARY) : $readPreference,
                'session'        => $session,
            ]);
        } else {
            $this->cursor = $this->mongo->executeCommand($dbName, $command, $readPreference);
        }

        // SQL监控
        if (!empty($this->config['trigger_sql'])) {
            $this->trigger('', $master);
        }

        return $this->getResult($typeMap);
    }

    /**
     * 获得数据集
     * @access protected
     * @param  string|array $typeMap 指定返回的typeMap
     * @return mixed
     */
    protected function getResult($typeMap = null): array
    {
        // 设置结果数据类型
        if (is_null($typeMap)) {
            $typeMap = $this->typeMap;
        }

        $typeMap = is_string($typeMap) ? ['root' => $typeMap] : $typeMap;

        $this->cursor->setTypeMap($typeMap);

        // 获取数据集
        $result = $this->cursor->toArray();

        if ($this->getConfig('pk_convert_id')) {
            // 转换ObjectID 字段
            foreach ($result as &$data) {
                $this->convertObjectID($data);
            }
        }

        $this->numRows = count($result);

        return $result;
    }

    /**
     * ObjectID处理
     * @access protected
     * @param  array $data 数据
     * @return void
     */
    protected function convertObjectID(array &$data): void
    {
        if (isset($data['_id']) && is_object($data['_id'])) {
            $data['id'] = $data['_id']->__toString();
            unset($data['_id']);
        }
    }

    /**
     * 数据库日志记录（仅供参考）
     * @access public
     * @param  string $type 类型
     * @param  mixed  $data 数据
     * @param  array  $options 参数
     * @return void
     */
    public function mongoLog(string $type, $data, array $options = [])
    {
        if (!$this->config['trigger_sql']) {
            return;
        }

        if (is_array($data)) {
            array_walk_recursive($data, function (&$value) {
                if ($value instanceof ObjectID) {
                    $value = $value->__toString();
                }
            });
        }

        switch (strtolower($type)) {
            case 'aggregate':
                $this->queryStr = 'runCommand(' . ($data ? json_encode($data) : '') . ');';
                break;
            case 'find':
                $this->queryStr = $type . '(' . ($data ? json_encode($data) : '') . ')';

                if (isset($options['sort'])) {
                    $this->queryStr .= '.sort(' . json_encode($options['sort']) . ')';
                }

                if (isset($options['skip'])) {
                    $this->queryStr .= '.skip(' . $options['skip'] . ')';
                }

                if (isset($options['limit'])) {
                    $this->queryStr .= '.limit(' . $options['limit'] . ')';
                }

                $this->queryStr .= ';';
                break;
            case 'insert':
            case 'remove':
                $this->queryStr = $type . '(' . ($data ? json_encode($data) : '') . ');';
                break;
            case 'update':
                $this->queryStr = $type . '(' . json_encode($options) . ',' . json_encode($data) . ');';
                break;
            case 'cmd':
                $this->queryStr = $data . '(' . json_encode($options) . ');';
                break;
        }

        $this->options = $options;
    }

    /**
     * 获取最近执行的指令
     * @access public
     * @return string
     */
    public function getLastSql(): string
    {
        return $this->queryStr;
    }

    /**
     * 关闭数据库
     * @access public
     */
    public function close()
    {
        $this->mongo     = null;
        $this->cursor    = null;
        $this->linkRead  = null;
        $this->linkWrite = null;
        $this->links     = [];
    }

    /**
     * 初始化数据库连接
     * @access protected
     * @param boolean $master 是否主服务器
     * @return void
     */
    protected function initConnect(bool $master = true): void
    {
        if (!empty($this->config['deploy'])) {
            // 采用分布式数据库
            if ($master) {
                if (!$this->linkWrite) {
                    $this->linkWrite = $this->multiConnect(true);
                }

                $this->mongo = $this->linkWrite;
            } else {
                if (!$this->linkRead) {
                    $this->linkRead = $this->multiConnect(false);
                }

                $this->mongo = $this->linkRead;
            }
        } elseif (!$this->mongo) {
            // 默认单数据库
            $this->mongo = $this->connect();
        }
    }

    /**
     * 连接分布式服务器
     * @access protected
     * @param  boolean $master 主服务器
     * @return Manager
     */
    protected function multiConnect(bool $master = false): Manager
    {
        $config = [];
        // 分布式数据库配置解析
        foreach (['username', 'password', 'hostname', 'hostport', 'database', 'dsn'] as $name) {
            $config[$name] = is_string($this->config[$name]) ? explode(',', $this->config[$name]) : $this->config[$name];
        }

        // 主服务器序号
        $m = floor(mt_rand(0, $this->config['master_num'] - 1));

        if ($this->config['rw_separate']) {
            // 主从式采用读写分离
            if ($master) // 主服务器写入
            {
                if ($this->config['is_replica_set']) {
                    return $this->replicaSetConnect();
                } else {
                    $r = $m;
                }
            } elseif (is_numeric($this->config['slave_no'])) {
                // 指定服务器读
                $r = $this->config['slave_no'];
            } else {
                // 读操作连接从服务器 每次随机连接的数据库
                $r = floor(mt_rand($this->config['master_num'], count($config['hostname']) - 1));
            }
        } else {
            // 读写操作不区分服务器 每次随机连接的数据库
            $r = floor(mt_rand(0, count($config['hostname']) - 1));
        }

        $dbConfig = [];

        foreach (['username', 'password', 'hostname', 'hostport', 'database', 'dsn'] as $name) {
            $dbConfig[$name] = $config[$name][$r] ?? $config[$name][0];
        }

        return $this->connect($dbConfig, $r);
    }

    /**
     * 创建基于复制集的连接
     * @return Manager
     */
    public function replicaSetConnect(): Manager
    {
        $this->dbName  = $this->config['database'];
        $this->typeMap = $this->config['type_map'];

        $startTime = microtime(true);

        $this->config['params']['replicaSet'] = $this->config['database'];

        $manager = new Manager($this->buildUrl(), $this->config['params']);

        // 记录数据库连接信息
        if (!empty($config['trigger_sql'])) {
            $this->trigger('CONNECT:ReplicaSet[ UseTime:' . number_format(microtime(true) - $startTime, 6) . 's ] ' . $this->config['dsn']);
        }

        return $manager;
    }

    /**
     * 根据配置信息 生成适用于连接复制集的 URL
     * @return string
     */
    private function buildUrl(): string
    {
        $url = 'mongodb://' . ($this->config['username'] ? "{$this->config['username']}" : '') . ($this->config['password'] ? ":{$this->config['password']}@" : '');

        $hostList = is_string($this->config['hostname']) ? explode(',', $this->config['hostname']) : $this->config['hostname'];
        $portList = is_string($this->config['hostport']) ? explode(',', $this->config['hostport']) : $this->config['hostport'];

        for ($i = 0; $i < count($hostList); $i++) {
            $url = $url . $hostList[$i] . ':' . $portList[0] . ',';
        }

        return rtrim($url, ",") . '/';
    }

    /**
     * 插入记录
     * @access public
     * @param  BaseQuery $query 查询对象
     * @param  boolean   $getLastInsID 返回自增主键
     * @return mixed
     * @throws AuthenticationException
     * @throws InvalidArgumentException
     * @throws ConnectionException
     * @throws RuntimeException
     * @throws BulkWriteException
     */
    public function insert(BaseQuery $query, bool $getLastInsID = false)
    {
        // 分析查询表达式
        $options = $query->parseOptions();

        if (empty($options['data'])) {
            throw new Exception('miss data to insert');
        }

        // 生成bulk对象
        $bulk = $this->builder->insert($query);

        $writeResult = $this->mongoExecute($query, $bulk);
        $result      = $writeResult->getInsertedCount();

        if ($result) {
            $data      = $options['data'];
            $lastInsId = $this->getLastInsID($query);

            if ($lastInsId) {
                $pk        = $query->getPk();
                $data[$pk] = $lastInsId;
            }

            $query->setOption('data', $data);

            $this->db->trigger('after_insert', $query);

            if ($getLastInsID) {
                return $lastInsId;
            }
        }

        return $result;
    }

    /**
     * 获取最近插入的ID
     * @access public
     * @param BaseQuery $query 查询对象
     * @return mixed
     */
    public function getLastInsID(BaseQuery $query)
    {
        $id = $this->builder->getLastInsID();

        if (is_array($id)) {
            array_walk($id, function (&$item, $key) {
                if ($item instanceof ObjectID) {
                    $item = $item->__toString();
                }
            });
        } elseif ($id instanceof ObjectID) {
            $id = $id->__toString();
        }

        return $id;
    }

    /**
     * 批量插入记录
     * @access public
     * @param  BaseQuery $query 查询对象
     * @param  array     $dataSet 数据集
     * @return integer
     * @throws AuthenticationException
     * @throws InvalidArgumentException
     * @throws ConnectionException
     * @throws RuntimeException
     * @throws BulkWriteException
     */
    public function insertAll(BaseQuery $query, array $dataSet = []): int
    {
        // 分析查询表达式
        $query->parseOptions();

        if (!is_array(reset($dataSet))) {
            return 0;
        }

        // 生成bulkWrite对象
        $bulk = $this->builder->insertAll($query, $dataSet);

        $writeResult = $this->mongoExecute($query, $bulk);

        return $writeResult->getInsertedCount();
    }

    /**
     * 更新记录
     * @access public
     * @param  BaseQuery $query 查询对象
     * @return int
     * @throws Exception
     * @throws AuthenticationException
     * @throws InvalidArgumentException
     * @throws ConnectionException
     * @throws RuntimeException
     * @throws BulkWriteException
     */
    public function update(BaseQuery $query): int
    {
        $query->parseOptions();

        // 生成bulkWrite对象
        $bulk = $this->builder->update($query);

        $writeResult = $this->mongoExecute($query, $bulk);

        $result = $writeResult->getModifiedCount();

        if ($result) {
            $this->db->trigger('after_update', $query);
        }

        return $result;
    }

    /**
     * 删除记录
     * @access public
     * @param  BaseQuery $query 查询对象
     * @return int
     * @throws Exception
     * @throws AuthenticationException
     * @throws InvalidArgumentException
     * @throws ConnectionException
     * @throws RuntimeException
     * @throws BulkWriteException
     */
    public function delete(BaseQuery $query): int
    {
        // 分析查询表达式
        $query->parseOptions();

        // 生成bulkWrite对象
        $bulk = $this->builder->delete($query);

        // 执行操作
        $writeResult = $this->mongoExecute($query, $bulk);

        $result = $writeResult->getDeletedCount();

        if ($result) {
            $this->db->trigger('after_delete', $query);
        }

        return $result;
    }

    /**
     * 查找记录
     * @access public
     * @param  BaseQuery $query 查询对象
     * @return array
     * @throws ModelNotFoundException
     * @throws DataNotFoundException
     * @throws AuthenticationException
     * @throws InvalidArgumentException
     * @throws ConnectionException
     * @throws RuntimeException
     */
    public function select(BaseQuery $query): array
    {
        $resultSet = $this->db->trigger('before_select', $query);

        if (!$resultSet) {
            $resultSet = $this->mongoQuery($query, function ($query) {
                return $this->builder->select($query);
            });
        }

        return $resultSet;
    }

    /**
     * 查找单条记录
     * @access public
     * @param  BaseQuery $query 查询对象
     * @return array
     * @throws ModelNotFoundException
     * @throws DataNotFoundException
     * @throws AuthenticationException
     * @throws InvalidArgumentException
     * @throws ConnectionException
     * @throws RuntimeException
     */
    public function find(BaseQuery $query): array
    {
        // 事件回调
        $result = $this->db->trigger('before_find', $query);

        if (!$result) {
            // 执行查询
            $resultSet = $this->mongoQuery($query, function ($query) {
                return $this->builder->select($query, true);
            });

            $result = $resultSet[0] ?? [];
        }

        return $result;
    }

    /**
     * 得到某个字段的值
     * @access public
     * @param  string $field 字段名
     * @param  mixed  $default 默认值
     * @return mixed
     */
    public function value(BaseQuery $query, string $field, $default = null)
    {
        $options = $query->parseOptions();

        if (isset($options['projection'])) {
            $query->removeOption('projection');
        }

        $query->setOption('projection', (array) $field);

        if (!empty($options['cache'])) {
            $cacheItem = $this->parseCache($query, $options['cache']);
            $key       = $cacheItem->getKey();

            if ($this->cache->has($key)) {
                return $this->cache->get($key);
            }
        }

        $mongoQuery = $this->builder->select($query, true);

        if (isset($options['projection'])) {
            $query->setOption('projection', $options['projection']);
        } else {
            $query->removeOption('projection');
        }

        // 执行查询操作
        $resultSet = $this->mongoQuery($query, $mongoQuery);

        if (!empty($resultSet)) {
            $data   = array_shift($resultSet);
            $result = $data[$field];
        } else {
            $result = false;
        }

        if (isset($cacheItem) && false !== $result) {
            // 缓存数据
            $cacheItem->set($result);
            $this->cacheData($cacheItem);
        }

        return false !== $result ? $result : $default;
    }

    /**
     * 得到某个列的数组
     * @access public
     * @param  string $field 字段名 多个字段用逗号分隔
     * @param  string $key 索引
     * @return array
     */
    public function column(BaseQuery $query, string $field, string $key = ''): array
    {
        $options = $query->parseOptions();

        if (isset($options['projection'])) {
            $query->removeOption('projection');
        }

        if ($key && '*' != $field) {
            $projection = $key . ',' . $field;
        } else {
            $projection = $field;
        }

        $query->field($projection);

        if (!empty($options['cache'])) {
            // 判断查询缓存
            $cacheItem = $this->parseCache($query, $options['cache']);
            $key       = $cacheItem->getKey();

            if ($this->cache->has($key)) {
                return $this->cache->get($key);
            }
        }

        $mongoQuery = $this->builder->select($query);

        if (isset($options['projection'])) {
            $query->setOption('projection', $options['projection']);
        } else {
            $query->removeOption('projection');
        }

        // 执行查询操作
        $resultSet = $this->mongoQuery($query, $mongoQuery);

        if (('*' == $field || strpos($field, ',')) && $key) {
            $result = array_column($resultSet, null, $key);
        } elseif (!empty($resultSet)) {
            $result = array_column($resultSet, $field, $key);
        } else {
            $result = [];
        }

        if (isset($cacheItem)) {
            // 缓存数据
            $cacheItem->set($result);
            $this->cacheData($cacheItem);
        }

        return $result;
    }

    /**
     * 执行command
     * @access public
     * @param  BaseQuery           $query      查询对象
     * @param  string|array|object $command 指令
     * @param  mixed               $extra 额外参数
     * @param  string              $db 数据库名
     * @return array
     */
    public function cmd(BaseQuery $query, $command, $extra = null, string $db = ''): array
    {
        if (is_array($command) || is_object($command)) {

            $this->mongoLog('cmd', 'cmd', $command);

            // 直接创建Command对象
            $command = new Command($command);
        } else {
            // 调用Builder封装的Command对象
            $command = $this->builder->$command($query, $extra);
        }

        return $this->command($command, $db);
    }

    /**
     * 获取数据库字段
     * @access public
     * @param mixed $tableName 数据表名
     * @return array
     */
    public function getTableFields($tableName): array
    {
        return [];
    }

    /**
     * 执行数据库事务
     * @access public
     * @param  callable $callback 数据操作方法回调
     * @return mixed
     * @throws PDOException
     * @throws \Exception
     * @throws \Throwable
     */
    public function transaction(callable $callback)
    {
        $this->startTrans();
        try {
            $result = null;
            if (is_callable($callback)) {
                $result = call_user_func_array($callback, [$this]);
            }
            $this->commit();
            return $result;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        } catch (\Throwable $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * 启动事务
     * @access public
     * @return void
     * @throws \PDOException
     * @throws \Exception
     */
    public function startTrans()
    {
        $this->initConnect(true);
        $this->session_uuid                  = uniqid();
        $this->sessions[$this->session_uuid] = $this->getMongo()->startSession();

        $this->sessions[$this->session_uuid]->startTransaction([]);
    }

    /**
     * 用于非自动提交状态下面的查询提交
     * @access public
     * @return void
     * @throws PDOException
     */
    public function commit()
    {
        if ($session = $this->getSession()) {
            $session->commitTransaction();
            $this->setLastSession();
        }
    }

    /**
     * 事务回滚
     * @access public
     * @return void
     * @throws PDOException
     */
    public function rollback()
    {
        if ($session = $this->getSession()) {
            $session->abortTransaction();
            $this->setLastSession();
        }
    }

    /**
     * 结束当前会话,设置上一个会话为当前会话
     * @author klinson <klinson@163.com>
     */
    protected function setLastSession()
    {
        if ($session = $this->getSession()) {
            $session->endSession();
            unset($this->sessions[$this->session_uuid]);
            if (empty($this->sessions)) {
                $this->session_uuid = null;
            } else {
                end($this->sessions);
                $this->session_uuid = key($this->sessions);
            }
        }
    }

    /**
     * 获取当前会话
     * @return \MongoDB\Driver\Session|null
     * @author klinson <klinson@163.com>
     */
    public function getSession()
    {
        return ($this->session_uuid && isset($this->sessions[$this->session_uuid]))
        ? $this->sessions[$this->session_uuid]
        : null;
    }
}
