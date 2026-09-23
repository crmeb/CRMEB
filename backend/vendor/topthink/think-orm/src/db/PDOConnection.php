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

use Closure;
use PDO;
use PDOStatement;
use think\db\exception\BindParamException;
use think\db\exception\DbEventException;
use think\db\exception\DbException;
use think\db\exception\DuplicateException;
use think\db\exception\PDOException;
use think\model\contract\Modelable as Model;

/**
 * 数据库连接基础类.
 *
 * @property PDO[] $links
 * @property PDO   $linkID
 * @property PDO   $linkRead
 * @property PDO   $linkWrite
 */
abstract class PDOConnection extends Connection
{
    /**
     * 数据库连接参数配置.
     *
     * @var array
     */
    protected $config = [
        // 数据库类型
        'type'            => '',
        // 服务器地址
        'hostname'        => '',
        // 数据库名
        'database'        => '',
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
        // 模型写入后自动读取主服务器
        'read_master'     => false,
        // 是否严格检查字段是否存在
        'fields_strict'   => true,
        // 开启字段缓存
        'fields_cache'    => false,
        // 监听SQL
        'trigger_sql'     => true,
        // Builder类
        'builder'         => '',
        // Query类
        'query'           => '',
        // 是否需要断线重连
        'break_reconnect' => false,
        // 断线标识字符串
        'break_match_str' => [],
        // 自动参数绑定
        'auto_param_bind' => true,
        // 数据库时区设置
        'timezone'        => '',
    ];

    /**
     * PDO操作实例.
     *
     * @var PDOStatement
     */
    protected $PDOStatement;

    /**
     * 当前SQL指令.
     *
     * @var string
     */
    protected $queryStr = '';

    /**
     * 事务指令数.
     *
     * @var int
     */
    protected $transTimes = 0;

    /**
     * 重连次数.
     *
     * @var int
     */
    protected $reConnectTimes = 0;

    /**
     * 查询结果类型.
     *
     * @var int
     */
    protected $fetchType = PDO::FETCH_ASSOC;

    /**
     * 字段属性大小写.
     *
     * @var int
     */
    protected $attrCase = PDO::CASE_LOWER;

    /**
     * 数据表信息.
     *
     * @var array
     */
    protected $info = [];

    /**
     * 查询开始时间.
     *
     * @var float
     */
    protected $queryStartTime;

    /**
     * PDO连接参数.
     *
     * @var array
     */
    protected $params = [
        PDO::ATTR_CASE              => PDO::CASE_NATURAL,
        PDO::ATTR_ERRMODE           => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS      => PDO::NULL_NATURAL,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_EMULATE_PREPARES  => false,
    ];

    /**
     * 参数绑定类型映射.
     *
     * @var array
     */
    protected $bindType = [
        'string'    => self::PARAM_STR,
        'str'       => self::PARAM_STR,
        'bigint'    => self::PARAM_STR,
        'set'       => self::PARAM_STR,
        'enum'      => self::PARAM_STR,
        'integer'   => self::PARAM_INT,
        'int'       => self::PARAM_INT,
        'boolean'   => self::PARAM_BOOL,
        'bool'      => self::PARAM_BOOL,
        'float'     => self::PARAM_FLOAT,
        'datetime'  => self::PARAM_STR,
        'date'      => self::PARAM_STR,
        'timestamp' => self::PARAM_STR,
    ];

    /**
     * 服务器断线标识字符.
     *
     * @var array
     */
    protected $breakMatchStr = [
        'server has gone away',
        'no connection to the server',
        'Lost connection',
        'is dead or not enabled',
        'Error while sending',
        'decryption failed or bad record mac',
        'server closed the connection unexpectedly',
        'SSL connection has been closed unexpectedly',
        'Error writing data to the connection',
        'Resource deadlock avoided',
        'failed with errno',
        'child connection forced to terminate due to client_idle_limit',
        'query_wait_timeout',
        'reset by peer',
        'Physical connection is not usable',
        'TCP Provider: Error code 0x68',
        'ORA-03114',
        'Packets out of order. Expected',
        'Adaptive Server connection failed',
        'Communication link failure',
        'connection is no longer usable',
        'Login timeout expired',
        'SQLSTATE[HY000] [2002] Connection refused',
        'running with the --read-only option so it cannot execute this statement',
        'The connection is broken and recovery is not possible. The connection is marked by the client driver as unrecoverable. No attempt was made to restore the connection.',
        'SQLSTATE[HY000] [2002] php_network_getaddresses: getaddrinfo failed: Try again',
        'SQLSTATE[HY000] [2002] php_network_getaddresses: getaddrinfo failed: Name or service not known',
        'SQLSTATE[HY000]: General error: 7 SSL SYSCALL error: EOF detected',
        'SQLSTATE[HY000] [2002] Connection timed out',
        'SSL: Connection timed out',
        'SQLSTATE[HY000]: General error: 1105 The last transaction was aborted due to Seamless Scaling. Please retry.',
    ];

    /**
     * 绑定参数.
     *
     * @var array
     */
    protected $bind = [];

    /**
     * 获取当前连接器类对应的Query类.
     *
     * @return string
     */
    public function getQueryClass(): string
    {
        return $this->getConfig('query') ?: Query::class;
    }

    /**
     * 获取当前连接器类对应的Builder类.
     *
     * @return string
     */
    public function getBuilderClass(): string
    {
        return $this->getConfig('builder') ?: '\\think\\db\\builder\\' . ucfirst($this->getConfig('type'));
    }

    /**
     * 解析pdo连接的dsn信息.
     *
     * @param array $config 连接信息
     *
     * @return string
     */
    abstract protected function parseDsn(array $config): string;

    /**
     * 取得数据表的字段信息.
     *
     * @param string $tableName 数据表名称
     *
     * @return array
     */
    abstract public function getFields(string $tableName): array;

    /**
     * 取得数据库的表信息.
     *
     * @param string $dbName 数据库名称
     *
     * @return array
     */
    abstract public function getTables(string $dbName = ''): array;

    /**
     * 对返数据表字段信息进行大小写转换出来.
     *
     * @param array $info 字段信息
     *
     * @return array
     */
    public function fieldCase(array $info): array
    {
        // 字段大小写转换
        return match ($this->attrCase) {
            PDO::CASE_LOWER => array_change_key_case($info),
            PDO::CASE_UPPER => array_change_key_case($info, CASE_UPPER),
            PDO::CASE_NATURAL => $info,
            default => $info,
        };
    }

    /**
     * 获取字段类型.
     *
     * @param string $type 字段类型
     *
     * @return string
     */
    protected function getFieldType(string $type): string
    {
        // 将字段类型转换为小写以进行比较
        $type = strtolower($type);

        return match (true) {
            str_starts_with($type, 'set')           => 'set',
            str_starts_with($type, 'enum')          => 'enum',
            str_starts_with($type, 'bigint')        => 'bigint',
            str_contains($type, 'float') || str_contains($type, 'double') || 
            str_contains($type, 'real')             => 'float',
            str_contains($type, 'int') || str_contains($type, 'serial') ||
            str_contains($type, 'bit')              => 'int',
            str_contains($type, 'bool')             => 'bool',
            str_contains($type, 'json')             => 'json',
            str_starts_with($type, 'timestamp')     => 'timestamp',
            str_starts_with($type, 'datetime')      => 'datetime',
            str_starts_with($type, 'date')          => 'date',
            default                                 => 'string',
        };
    }

    /**
     * 获取字段绑定类型.
     *
     * @param string $type 字段类型
     *
     * @return int
     */
    public function getFieldBindType(string $type): int
    {
        return $this->bindType[$type] ?? self::PARAM_STR;
    }

    /**
     * 获取数据表信息缓存key.
     *
     * @param string $schema 数据表名称
     *
     * @return string
     */
    protected function getSchemaCacheKey(string $schema): string
    {
        $hostname = $this->getConfig('hostname');
        return (is_array($hostname) ? $hostname[0] : $hostname) . '_' . $this->getConfig('hostport') . '|' . $schema;
    }

    /**
     * @param string $tableName 数据表名称
     * @param bool   $force     强制从数据库获取
     *
     * @return array
     */
    public function getSchemaInfo(string $tableName, bool $force = false): array
    {
        $schema = str_contains($tableName, '.') ? $tableName : $this->getConfig('database') . '.' . $tableName;

        if (isset($this->info[$schema]) && !$force) {
            return $this->info[$schema];
        }

        // 读取字段缓存
        $cacheKey = $this->getSchemaCacheKey($schema);
        $info     = $this->getCachedSchemaInfo($cacheKey, $tableName, $force);

        $pk      = $info['_pk'] ?? null;
        $autoinc = $info['_autoinc'] ?? null;
        unset($info['_pk'], $info['_autoinc']);

        $bind = array_map(fn($val) => $this->getFieldBindType($val), $info);

        $this->info[$schema] = [
            'fields'  => array_keys($info),
            'type'    => $info,
            'bind'    => $bind,
            'pk'      => $pk,
            'autoinc' => $autoinc,
        ];

        return $this->info[$schema];
    }

    /**
     * @param string $cacheKey 缓存key
     * @param string $tableName 数据表名称
     * @param bool   $force     强制从数据库获取
     *
     * @return array
     */
    protected function getCachedSchemaInfo(string $cacheKey, string $tableName, bool $force): array
    {
        if ($this->config['fields_cache'] && !empty($this->cache) && !$force) {
            $info = $this->cache->get($cacheKey);
            if (!empty($info)) {
                if (is_object($info)) {
                    $info = get_object_vars($info);
                }
                return $info;
            }
        }

        $info = $this->getTableFieldsInfo($tableName);
        if (!empty($this->cache) && ($this->config['fields_cache'] || $force)) {
            $this->cache->set($cacheKey, $info);
        }

        return $info;
    }

    /**
     * 获取数据表信息.
     *
     * @param mixed  $tableName 数据表名 留空自动获取
     * @param string $fetch     获取信息类型 包括 fields type bind pk
     *
     * @return mixed
     */
    public function getTableInfo(array | string $tableName, string $fetch = '')
    {
        if (is_array($tableName)) {
            $tableName = key($tableName) ?: current($tableName);
        }

        if (str_contains($tableName, ',') || str_contains($tableName, ')')) {
            // 多表不获取字段信息
            return [];
        }

        [$tableName] = explode(' ', $tableName);

        $info = $this->getSchemaInfo($tableName);

        return $fetch && array_key_exists($fetch, $info) ? $info[$fetch] : $info;
    }

    /**
     * 获取数据表的字段信息.
     *
     * @param string $tableName 数据表名
     *
     * @return array
     */
    public function getTableFieldsInfo(string $tableName): array
    {
        $fields = $this->getFields($tableName);
        $info   = [];

        foreach ($fields as $key => $val) {
            // 记录字段类型
            $info[$key] = $this->getFieldType($val['type']);

            if (!empty($val['primary'])) {
                $pk[] = $key;
            }

            if (!empty($val['autoinc'])) {
                $autoinc = $key;
            }
        }

        if (isset($pk)) {
            // 设置主键
            $pk          = count($pk) > 1 ? $pk : $pk[0];
            $info['_pk'] = $pk;
        }

        if (isset($autoinc)) {
            $info['_autoinc'] = $autoinc;
        }

        return $info;
    }

    /**
     * 获取数据表的主键.
     *
     * @param mixed $tableName 数据表名
     *
     * @return string|array
     */
    public function getPk($tableName)
    {
        return $this->getTableInfo($tableName, 'pk');
    }

    /**
     * 获取数据表的自增主键.
     *
     * @param mixed $tableName 数据表名
     *
     * @return string|null
     */
    public function getAutoInc($tableName)
    {
        return $this->getTableInfo($tableName, 'autoinc');
    }

    /**
     * 获取数据表字段信息.
     *
     * @param mixed $tableName 数据表名
     *
     * @return array
     */
    public function getTableFields($tableName): array
    {
        return $this->getTableInfo($tableName, 'fields');
    }

    /**
     * 获取数据表字段类型.
     *
     * @param mixed  $tableName 数据表名
     * @param string $field     字段名
     *
     * @return array|string
     */
    public function getFieldsType($tableName, ?string $field = null)
    {
        $result = $this->getTableInfo($tableName, 'type');

        if ($field && isset($result[$field])) {
            return $result[$field];
        }

        return $result;
    }

    /**
     * 获取数据表绑定信息.
     *
     * @param mixed $tableName 数据表名
     *
     * @return array
     */
    public function getFieldsBind($tableName): array
    {
        return $this->getTableInfo($tableName, 'bind');
    }

    /**
     * 连接数据库方法.
     *
     * @param array      $config         连接参数
     * @param int        $linkNum        连接序号
     * @param array|bool $autoConnection 是否自动连接主数据库（用于分布式）
     *
     * @throws PDOException
     *
     * @return PDO
     */
    public function connect(array $config = [], $linkNum = 0, $autoConnection = false): PDO
    {
        if (isset($this->links[$linkNum])) {
            return $this->links[$linkNum];
        }

        if (empty($config)) {
            $config = $this->config;
        } else {
            $config = array_merge($this->config, $config);
        }

        // 连接参数
        if (isset($config['params']) && is_array($config['params'])) {
            $params = $config['params'] + $this->params;
        } else {
            $params = $this->params;
        }

        // 记录当前字段属性大小写设置
        $this->attrCase = $params[PDO::ATTR_CASE];

        if (!empty($config['break_match_str'])) {
            $this->breakMatchStr = array_merge($this->breakMatchStr, (array) $config['break_match_str']);
        }

        try {
            if (empty($config['dsn'])) {
                $config['dsn'] = $this->parseDsn($config);
            }

            $startTime = microtime(true);

            $this->links[$linkNum] = $this->createPdo($config['dsn'], $config['username'], $config['password'], $params);

            // SQL监控
            if (!empty($config['trigger_sql'])) {
                $this->trigger('CONNECT:[ UseTime:' . number_format(microtime(true) - $startTime, 6) . 's ] ' . $config['dsn']);
            }

            // 设置数据库时区
            $this->setTimezone($this->links[$linkNum], $config['timezone']);

            $this->db->trigger('after_connect', $this->links[$linkNum]);
            return $this->links[$linkNum];
        } catch (\PDOException $e) {
            if ($autoConnection) {
                $this->db->log($e->getMessage(), 'error');

                return $this->connect($autoConnection, $linkNum);
            } else {
                throw $e;
            }
        }
    }

    /**
     * 视图查询.
     *
     * @param array $args
     *
     * @return BaseQuery
     */
    public function view(...$args)
    {
        return $this->newQuery()->view(...$args);
    }

    /**
     * 创建PDO实例.
     *
     * @param $dsn
     * @param $username
     * @param $password
     * @param $params
     *
     * @return PDO
     */
    protected function createPdo($dsn, $username, $password, $params)
    {
        return new PDO($dsn, $username, $password, $params);
    }

    /**
     * 释放查询结果.
     */
    public function free(): void
    {
        $this->PDOStatement = null;
    }

    /**
     * 获取PDO对象
     *
     * @return PDO|false
     */
    public function getPdo()
    {
        if (!$this->linkID) {
            return false;
        }

        return $this->linkID;
    }

    /**
     * 执行查询 使用生成器返回数据.
     *
     * @param BaseQuery  $query     查询对象
     * @param string     $sql       sql指令
     * @param Model|null $model     模型对象实例
     *
     * @throws DbException
     *
     * @return \Generator
     */
    public function getCursor(BaseQuery $query, string $sql, $model = null)
    {
        $this->queryPDOStatement($query, $sql);

        // 返回结果集
        while ($result = $this->PDOStatement->fetch($this->fetchType)) {
            if ($model) {
                yield $model->newInstance($result);
            } else {
                yield $result;
            }
        }
    }

    /**
     * 执行查询 返回数据集.
     *
     * @param string $sql    sql指令
     * @param array  $bind   参数绑定
     * @param bool   $master 主库读取
     *
     * @throws DbException
     *
     * @return array
     */
    public function query(string $sql, array $bind = [], bool $master = false): array
    {
        $this->getPDOStatement($sql, $bind, $master);
        return $this->getResult();
    }

    /**
     * 执行语句.
     *
     * @param string $sql  sql指令
     * @param array  $bind 参数绑定
     *
     * @throws DbException
     *
     * @return int
     */
    public function execute(string $sql, array $bind = []): int
    {
        $this->getPDOStatement($sql, $bind, true);
        return $this->PDOStatement->rowCount();
    }

    /**
     * 获取最近插入的ID.
     * @param string    $sequence 自增序列名
     *
     * @return mixed
     */
    public function getAutoID(?string $sequence = null)
    {
        try {
            $insertId = $this->linkID->lastInsertId($sequence);
        } catch (\Exception $e) {
            $insertId = '';
        }

        return $insertId;
    }

    /**
     * 执行查询 返回数据集.
     *
     * @param BaseQuery $query  查询对象
     * @param mixed     $sql    sql指令
     * @param bool      $master 主库读取
     *
     * @throws DbException
     *
     * @return array
     */
    protected function pdoQuery(BaseQuery $query, $sql, ?bool $master = null): array
    {
        // 分析查询表达式
        $query->parseOptions();
        $bind = $query->getBind();

        if ($query->getOption('cache')) {
            // 检查查询缓存
            $cacheItem = $this->parseCache($query, $query->getOption('cache'));
            if (!$query->getOption('force_cache')) {
                $key = $cacheItem->getKey();

                if ($this->cache->has($key)) {
                    $data = $this->cache->get($key);
                    if (null !== $data && is_array($data)) {
                        return $data;
                    }
                }
            }
        }

        if ($sql instanceof Closure) {
            $sql  = $sql($query);
            $bind = array_merge($bind, $query->getBind());
        }

        if (!isset($master)) {
            $master = (bool) $query->getOption('master');
        }

        $procedure = $query->getOption('procedure') || in_array(strtolower(substr(trim($sql), 0, 4)), ['call', 'exec']);

        $this->getPDOStatement($sql, $bind, $master, $procedure);

        $resultSet    = $this->getResult($procedure);
        $requireCache = $query->getOption('cache_always') || !empty($resultSet);

        if (isset($cacheItem) && $requireCache) {
            // 缓存数据集
            $cacheItem->set($resultSet);
            $this->cacheData($cacheItem);
        }

        return $resultSet;
    }

    /**
     * 执行查询但只返回PDOStatement对象
     *
     * @param BaseQuery $query 查询对象
     *
     * @throws DbException
     *
     * @return \PDOStatement
     */
    public function pdo(BaseQuery $query): PDOStatement
    {
        // 生成查询SQL
        $sql = $this->builder->select($query);

        return $this->queryPDOStatement($query, $sql);
    }

    /**
     * 执行查询但只返回PDOStatement对象
     *
     * @param string $sql       sql指令
     * @param array  $bind      参数绑定
     * @param bool   $master    是否在主服务器读操作
     * @param bool   $procedure 是否为存储过程调用
     *
     * @throws DbException
     *
     * @return PDOStatement
     */
    public function getPDOStatement(string $sql, array $bind = [], bool $master = false, bool $procedure = false): PDOStatement
    {
        try {
            $this->initConnect($this->readMaster ?: $master);
            // 记录SQL语句
            $this->queryStr = $sql;
            $this->bind     = $bind;

            $this->queryStartTime = microtime(true);

            // 预处理
            $this->PDOStatement = $this->linkID->prepare($sql);

            // 参数绑定
            if ($procedure) {
                $this->bindParam($bind);
            } else {
                $this->bindValue($bind);
            }

            // 执行查询
            $this->PDOStatement->execute();

            // SQL监控
            if (!empty($this->config['trigger_sql'])) {
                $this->trigger('', $master);
            }

            $this->reConnectTimes = 0;

            return $this->PDOStatement;
        } catch (\Throwable  | \Exception $e) {
            if ($this->transTimes > 0) {
                // 事务活动中时不应该进行重试，应直接中断执行，防止造成污染。
                if ($this->isBreak($e)) {
                    // 尝试对事务计数进行重置
                    $this->transTimes = 0;
                }
            } else {
                if ($this->reConnectTimes < 4 && $this->isBreak($e)) {
                    $this->reConnectTimes++;

                    return $this->close()->getPDOStatement($sql, $bind, $master, $procedure);
                }
            }

            if ($e instanceof \PDOException) {
                if (str_contains($e->getMessage(),'1062 Duplicate entry')) {
                    throw new DuplicateException($e, $this->config, $this->getLastsql());
                }
                throw new PDOException($e, $this->config, $this->getLastsql());
            } else {
                throw $e;
            }
        }
    }

    /**
     * 执行语句.
     *
     * @param BaseQuery $query  查询对象
     * @param string    $sql    sql指令
     * @param bool      $origin 是否原生查询
     *
     * @throws DbException
     *
     * @return int
     */
    protected function pdoExecute(BaseQuery $query, string $sql, bool $origin = false): int
    {
        if ($origin) {
            $query->parseOptions();
        }

        $this->queryPDOStatement($query->master(true), $sql);

        if (!$origin && !empty($this->config['deploy']) && !empty($this->config['read_master'])) {
            $this->readMaster = true;
        }

        $this->numRows = $this->PDOStatement->rowCount();

        if ($query->getOption('cache')) {
            // 清理缓存数据
            $cacheItem = $this->parseCache($query, $query->getOption('cache'));
            $key       = $cacheItem->getKey();
            $tag       = $cacheItem->getTag();

            if (isset($key) && $this->cache->has($key)) {
                $this->cache->delete($key);
            } elseif (!empty($tag) && method_exists($this->cache, 'tag')) {
                $this->cache->tag($tag)->clear();
            }
        }

        return $this->numRows;
    }

    /**
     * @param BaseQuery $query
     * @param string    $sql
     *
     * @throws DbException
     *
     * @return PDOStatement
     */
    protected function queryPDOStatement(BaseQuery $query, string $sql): PDOStatement
    {
        $options   = $query->getOptions();
        $bind      = $query->getBind();
        $master    = !empty($options['master']);
        $procedure = !empty($options['procedure']) || in_array(strtolower(substr(trim($sql), 0, 4)), ['call', 'exec']);

        return $this->getPDOStatement($sql, $bind, $master, $procedure);
    }

    /**
     * 查找单条记录.
     *
     * @param BaseQuery $query 查询对象
     *
     * @throws DbException
     *
     * @return array
     */
    public function find(BaseQuery $query): array
    {
        // 事件回调
        try {
            $this->db->trigger('before_find', $query);
        } catch (DbEventException $e) {
            return [];
        }

        // 执行查询
        $resultSet = $this->pdoQuery($query, function ($query) {
            return $this->builder->select($query, true);
        });

        return $resultSet[0] ?? [];
    }

    /**
     * 使用游标查询记录.
     *
     * @param BaseQuery $query 查询对象
     * @param bool $unbuffered 是否开启无缓冲查询（仅限mysql）
     *
     * @return \Generator
     */
    public function cursor(BaseQuery $query, bool $unbuffered = false)
    {
        // 分析查询表达式
        $options = $query->parseOptions();

        // 生成查询SQL
        $sql = $this->builder->select($query);
        
        // 检查是否需要无缓冲查询（仅对MySQL且支持该方法时生效）
        if ($unbuffered && method_exists($this, 'cursorUnbuffered')) {
            return $this->cursorUnbuffered($query, $sql);
        }

        // 执行查询操作
        return $this->getCursor($query, $sql, $query->getModel());
    }

    /**
     * 查找记录.
     *
     * @param BaseQuery $query 查询对象
     *
     * @throws DbException
     *
     * @return array
     */
    public function select(BaseQuery $query): array
    {
        try {
            $this->db->trigger('before_select', $query);
        } catch (DbEventException $e) {
            return [];
        }

        // 执行查询操作
        return $this->pdoQuery($query, function ($query) {
            return $this->builder->select($query);
        });
    }

    /**
     * 插入记录.
     *
     * @param BaseQuery $query        查询对象
     * @param bool      $getLastInsID 返回自增主键
     *
     * @return mixed
     */
    public function insert(BaseQuery $query, bool $getLastInsID = false)
    {
        // 分析查询表达式
        $options = $query->parseOptions();

        // 生成SQL语句
        $sql = $this->builder->insert($query);

        // 执行操作
        $result = '' == $sql ? 0 : $this->pdoExecute($query, $sql);

        if ($result) {
            $sequence  = $options['sequence'] ?? null;
            $lastInsId = $this->getLastInsID($query, $sequence);

            $data = $options['data'];

            if ($lastInsId) {
                $pk = $query->getAutoInc();
                if ($pk && is_string($pk)) {
                    $data[$pk] = $lastInsId;
                }
            }

            $query->setOption('data', $data);

            $this->db->trigger('after_insert', $query);

            if ($getLastInsID && $lastInsId) {
                return $lastInsId;
            }
        }

        return $result;
    }

    /**
     * 批量插入记录.
     *
     * @param BaseQuery $query   查询对象
     * @param array     $dataSet 数据集
     *
     * @throws \Exception
     * @throws \Throwable
     *
     * @return int
     */
    public function insertAll(BaseQuery $query, array $dataSet = []): int
    {
        if (!is_array(reset($dataSet))) {
            return 0;
        }

        $options = $query->parseOptions();

        if (!empty($options['limit']) && is_numeric($options['limit'])) {
            $limit = (int) $options['limit'];
        } else {
            $limit = 0;
        }

        if (0 === $limit && count($dataSet) >= 5000) {
            $limit = 1000;
        }

        if ($limit) {
            // 分批写入 自动启动事务支持
            $this->startTrans();

            try {
                $array = array_chunk($dataSet, $limit, true);
                $count = 0;

                foreach ($array as $item) {
                    $sql = $this->builder->insertAll($query, $item);
                    $count += $this->pdoExecute($query, $sql);
                }

                // 提交事务
                $this->commit();
            } catch (\Exception  | \Throwable $e) {
                $this->rollback();

                throw $e;
            }

            return $count;
        }

        $sql = $this->builder->insertAll($query, $dataSet);

        return $this->pdoExecute($query, $sql);
    }

    /**
     * 批量插入记录.
     *
     * @param BaseQuery $query   查询对象
     * @param array     $keys 键值
     * @param array     $values 数据
     *
     * @throws \Exception
     * @throws \Throwable
     *
     * @return int
     */
    public function insertAllByKeys(BaseQuery $query, array $keys, array $values): int
    {
        $options = $query->parseOptions();

        if (!empty($options['limit']) && is_numeric($options['limit'])) {
            $limit = (int) $options['limit'];
        } else {
            $limit = 0;
        }

        if (0 === $limit && count($values) >= 5000) {
            $limit = 1000;
        }

        if ($limit) {
            // 分批写入 自动启动事务支持
            $this->startTrans();

            try {
                $array = array_chunk($values, $limit, true);
                $count = 0;

                foreach ($array as $item) {
                    $sql = $this->builder->insertAllByKeys($query, $keys, $item);
                    $count += $this->pdoExecute($query, $sql);
                }

                // 提交事务
                $this->commit();
            } catch (\Exception  | \Throwable $e) {
                $this->rollback();

                throw $e;
            }

            return $count;
        }

        $sql = $this->builder->insertAllByKeys($query, $keys, $values);

        return $this->pdoExecute($query, $sql);
    }

    /**
     * 通过Select方式插入记录.
     *
     * @param BaseQuery $query  查询对象
     * @param array     $fields 要插入的数据表字段名
     * @param string    $table  要插入的数据表名
     *
     * @throws PDOException
     *
     * @return int
     */
    public function selectInsert(BaseQuery $query, array $fields, string $table): int
    {
        // 分析查询表达式
        $query->parseOptions();

        $sql = $this->builder->selectInsert($query, $fields, $table);

        return $this->pdoExecute($query, $sql);
    }

    /**
     * 更新记录.
     *
     * @param BaseQuery $query 查询对象
     *
     * @throws PDOException
     *
     * @return int
     */
    public function update(BaseQuery $query): int
    {
        $query->parseOptions();

        // 生成UPDATE SQL语句
        $sql = $this->builder->update($query);

        // 执行操作
        $result = '' == $sql ? 0 : $this->pdoExecute($query, $sql);

        if ($result) {
            $this->db->trigger('after_update', $query);
        }

        return $result;
    }

    /**
     * 删除记录.
     *
     * @param BaseQuery $query 查询对象
     *
     * @throws PDOException
     *
     * @return int
     */
    public function delete(BaseQuery $query): int
    {
        // 分析查询表达式
        $query->parseOptions();

        // 生成删除SQL语句
        $sql = $this->builder->delete($query);

        // 执行操作
        $result = $this->pdoExecute($query, $sql);

        if ($result) {
            $this->db->trigger('after_delete', $query);
        }

        return $result;
    }

    /**
     * 得到某个字段的值
     *
     * @param BaseQuery $query   查询对象
     * @param string    $field   字段名
     * @param mixed     $default 默认值
     * @param bool      $one     返回一个值
     *
     * @return mixed
     */
    public function value(BaseQuery $query, string $field, $default = null, bool $one = true)
    {
        $options = $query->parseOptions();

        if (isset($options['field'])) {
            $query->removeOption('field');
        }

        if (isset($options['group'])) {
            $query->group('');
        }

        $query->setOption('field', (array) $field);

        if (!empty($options['cache'])) {
            $cacheItem = $this->parseCache($query, $options['cache'], 'value');
            if (empty($options['force_cache'])) {
                $key = $cacheItem->getKey();

                if ($this->cache->has($key)) {
                    $data = $this->cache->get($key);
                    if (null !== $data) {
                        return $data;
                    }
                }
            }
        }

        // 生成查询SQL
        $sql = $this->builder->select($query, $one);

        if (isset($options['field'])) {
            $query->setOption('field', $options['field']);
        } else {
            $query->removeOption('field');
        }

        if (isset($options['group'])) {
            $query->setOption('group', $options['group']);
        }

        // 执行查询操作
        $pdo = $this->getPDOStatement($sql, $query->getBind(), $options['master']);

        $result       = $pdo->fetchColumn();
        $result       = false !== $result ? $result : $default;
        $requireCache = $query->getOption('cache_always') || !empty($result);

        if (isset($cacheItem) && $requireCache) {
            // 缓存数据
            $cacheItem->set($result);
            $this->cacheData($cacheItem);
        }

        return $result;
    }

    /**
     * 得到某个字段的值
     *
     * @param BaseQuery  $query     查询对象
     * @param string     $aggregate 聚合方法
     * @param string|Raw $field     字段名
     * @param bool       $force     强制转为数字类型
     *
     * @return mixed
     */
    public function aggregate(BaseQuery $query, string $aggregate, string | Raw $field, bool $force = false)
    {
        if (is_string($field) && 0 === stripos($field, 'DISTINCT ')) {
            [$distinct, $field] = explode(' ', $field);
        }

        $field = $aggregate . '(' . (!empty($distinct) ? 'DISTINCT ' : '') . $this->builder->parseKey($query, $field, true) . ') AS think_' . strtolower($aggregate);

        $result = $this->value($query, $field, 0, false);

        return $force ? (float) $result : $result;
    }

    /**
     * 得到某个列的数组.
     *
     * @param BaseQuery    $query  查询对象
     * @param string|array $column 字段名 多个字段用逗号分隔
     * @param string       $key    索引
     *
     * @return array
     */
    public function column(BaseQuery $query, string | array $column, string $key = ''): array
    {
        $options = $query->parseOptions();

        if (isset($options['field'])) {
            $query->removeOption('field');
        }

        if (empty($key) || trim($key) === '') {
            $key = null;
        }

        if (is_string($column)) {
            $column = trim($column);
            if ('*' !== $column) {
                $column = array_map('trim', explode(',', $column));
            }
        } elseif (in_array('*', $column)) {
            $column = '*';
        }

        $field = $column;
        if ('*' !== $column && $key && !in_array($key, $column)) {
            $field[] = $key;
        }

        $query->setOption('field', (array) $field);

        if (!empty($options['cache'])) {
            // 判断查询缓存
            $cacheItem = $this->parseCache($query, $options['cache'], 'column');
            if (empty($options['force_cache'])) {
                $name = $cacheItem->getKey();

                if ($this->cache->has($name)) {
                    $data = $this->cache->get($name);
                    if (null !== $data) {
                        return $data;
                    }
                }
            }
        }

        // 生成查询SQL
        $sql = $this->builder->select($query);

        if (isset($options['field'])) {
            $query->setOption('field', $options['field']);
        } else {
            $query->removeOption('field');
        }

        // 执行查询操作
        $pdo       = $this->getPDOStatement($sql, $query->getBind(), $options['master']);
        $resultSet = $pdo->fetchAll(PDO::FETCH_ASSOC);

        if (is_string($key) && str_contains($key, '.')) {
            [$alias, $key] = explode('.', $key);
        }

        if (empty($resultSet)) {
            $result = [];
        } elseif ('*' !== $column && count($column) === 1) {
            $column = array_shift($column);
            if (str_contains($column, ' ')) {
                $column = substr(strrchr(trim($column), ' '), 1);
            }

            if (str_contains($column, '.')) {
                [$alias, $column] = explode('.', $column);
            }

            if (str_contains($column, '->')) {
                $column = $this->builder->parseKey($query, $column);
            }

            $result = array_column($resultSet, $column, $key);
        } elseif ($key) {
            $result = array_column($resultSet, null, $key);
        } else {
            $result = $resultSet;
        }

        $requireCache = $query->getOption('cache_always') || !empty($result);

        if (isset($cacheItem) && $requireCache) {
            // 缓存数据
            $cacheItem->set($result);
            $this->cacheData($cacheItem);
        }

        return $result;
    }

    /**
     * 参数绑定
     * 支持 ['name'=>'value','id'=>123] 对应命名占位符
     * 或者 ['value',123] 对应问号占位符.
     *
     * @param array $bind 要绑定的参数列表
     *
     * @throws BindParamException
     *
     * @return void
     */
    protected function bindValue(array $bind = []): void
    {
        foreach ($bind as $key => $val) {
            // 占位符
            $param = is_numeric($key) ? $key + 1 : ':' . $key;

            if (is_array($val)) {
                if (self::PARAM_INT == $val[1]) {
                    $val[0] = (int) $val[0];
                } elseif (self::PARAM_FLOAT == $val[1]) {
                    $val[0] = is_string($val[0]) ? (float) $val[0] : $val[0];
                    $val[1] = self::PARAM_STR;
                }

                $result = $this->PDOStatement->bindValue($param, $val[0], $val[1]);
            } else {
                $result = $this->PDOStatement->bindValue($param, $val);
            }

            if (!$result) {
                throw new BindParamException(
                    "Error occurred  when binding parameters '{$param}'",
                    $this->config,
                    $this->getLastsql(),
                    $bind
                );
            }
        }
    }

    /**
     * 存储过程的输入输出参数绑定.
     *
     * @param array $bind 要绑定的参数列表
     *
     * @throws BindParamException
     *
     * @return void
     */
    protected function bindParam(array $bind): void
    {
        foreach ($bind as $key => $val) {
            $param = is_numeric($key) ? $key + 1 : ':' . $key;

            if (is_array($val)) {
                array_unshift($val, $param);
                $result = call_user_func_array([$this->PDOStatement, 'bindParam'], $val);
            } else {
                $result = $this->PDOStatement->bindValue($param, $val);
            }

            if (!$result) {
                $param = array_shift($val);

                throw new BindParamException(
                    "Error occurred  when binding parameters '{$param}'",
                    $this->config,
                    $this->getLastsql(),
                    $bind
                );
            }
        }
    }

    /**
     * 获得数据集数组.
     *
     * @param bool $procedure 是否存储过程
     *
     * @return array
     */
    protected function getResult(bool $procedure = false): array
    {
        if ($procedure) {
            // 存储过程返回结果
            return $this->procedure();
        }

        $result = $this->PDOStatement->fetchAll($this->fetchType);

        $this->numRows = count($result);

        return $result;
    }

    /**
     * 获得存储过程数据集.
     *
     * @return array
     */
    protected function procedure(): array
    {
        $item = [];

        do {
            $result = $this->getResult();
            if (!empty($result)) {
                $item[] = $result;
            }
        } while ($this->PDOStatement->nextRowset());

        $this->numRows = count($item);

        return $item;
    }

    /**
     * 执行数据库事务
     *
     * @param callable $callback 数据操作方法回调
     *
     * @throws PDOException
     * @throws \Exception
     * @throws \Throwable
     *
     * @return mixed
     */
    public function transaction(callable $callback)
    {
        $this->startTrans();

        try {
            $result = $callback($this);

            $this->commit();

            return $result;
        } catch (\Exception  | \Throwable $e) {
            $this->rollback();

            throw $e;
        }
    }

    /**
     * 启动事务
     *
     * @throws \PDOException
     * @throws \Exception
     *
     * @return void
     */
    public function startTrans(): void
    {
        try {
            $this->initConnect(true);

            if (0 == $this->transTimes) {
                $this->linkID->beginTransaction();
            } elseif ($this->transTimes > 0 && $this->supportSavepoint() && $this->linkID->inTransaction()) {
                $this->linkID->exec(
                    $this->parseSavepoint('trans' . ($this->transTimes + 1))
                );
            }
            $this->transTimes++;
            $this->reConnectTimes = 0;
        } catch (\Throwable  | \Exception $e) {
            if (0 === $this->transTimes && $this->reConnectTimes < 4 && $this->isBreak($e)) {
                $this->reConnectTimes++;
                $this->close()->startTrans();
            } else {
                if ($this->isBreak($e)) {
                    // 尝试对事务计数进行重置
                    $this->transTimes = 0;
                }

                throw $e;
            }
        }
    }

    /**
     * 用于非自动提交状态下面的查询提交.
     *
     * @throws \PDOException
     *
     * @return void
     */
    public function commit(): void
    {
        $this->initConnect(true);
        $this->transTimes = max(0, $this->transTimes - 1);

        if (0 == $this->transTimes && $this->linkID->inTransaction()) {
            $this->linkID->commit();
        }
    }

    /**
     * 事务回滚.
     *
     * @throws \PDOException
     *
     * @return void
     */
    public function rollback(): void
    {
        $this->initConnect(true);
        $this->transTimes = max(0, $this->transTimes - 1);

        if ($this->linkID->inTransaction()) {
            if (0 == $this->transTimes) {
                $this->linkID->rollBack();
            } elseif ($this->transTimes > 0 && $this->supportSavepoint()) {
                $this->linkID->exec(
                    $this->parseSavepointRollBack('trans' . ($this->transTimes + 1))
                );
            }
        }
    }

    /**
     * 是否支持事务嵌套.
     *
     * @return bool
     */
    protected function supportSavepoint(): bool
    {
        return false;
    }

    /**
     * 生成定义保存点的SQL.
     *
     * @param string $name 标识
     *
     * @return string
     */
    protected function parseSavepoint(string $name): string
    {
        return 'SAVEPOINT ' . $name;
    }

    /**
     * 生成回滚到保存点的SQL.
     *
     * @param string $name 标识
     *
     * @return string
     */
    protected function parseSavepointRollBack(string $name): string
    {
        return 'ROLLBACK TO SAVEPOINT ' . $name;
    }

    /**
     * 批处理执行SQL语句
     * 批处理的指令都认为是execute操作.
     *
     * @param array     $sqlArray SQL批处理指令
     *
     * @return bool
     */
    public function batchQuery(array $sqlArray = []): bool
    {
        // 自动启动事务支持
        $this->startTrans();

        try {
            foreach ($sqlArray as $sql) {
                $this->execute($sql);
            }
            // 提交事务
            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();

            throw $e;
        }

        return true;
    }

    /**
     * 关闭数据库（或者重新连接）.
     *
     * @return $this
     */
    public function close()
    {
        $this->linkID     = null;
        $this->linkWrite  = null;
        $this->linkRead   = null;
        $this->links      = [];
        $this->transTimes = 0;

        $this->free();

        return $this;
    }

    /**
     * 是否断线
     *
     * @param \PDOException|\Exception $e 异常对象
     *
     * @return bool
     */
    protected function isBreak($e): bool
    {
        if (!$this->config['break_reconnect']) {
            return false;
        }

        $error = $e->getMessage();

        foreach ($this->breakMatchStr as $msg) {
            if (false !== stripos($error, $msg)) {
                return true;
            }
        }

        return false;
    }

    /**
     * 获取最近一次查询的sql语句.
     *
     * @return string
     */
    public function getLastSql(): string
    {
        return $this->getRealSql($this->queryStr, $this->bind);
    }

    /**
     * 获取最近插入的ID.
     *
     * @param BaseQuery $query    查询对象
     * @param string    $sequence 自增序列名
     *
     * @return mixed
     */
    public function getLastInsID(BaseQuery $query, ?string $sequence = null)
    {
        return $this->autoInsIDType($query, $this->getAutoID($sequence));
    }

    /**
     * 获取最近插入的ID.
     *
     * @param BaseQuery $query    查询对象
     * @param string    $insertId 自增ID
     *
     * @return mixed
     */
    protected function autoInsIDType(BaseQuery $query, string $insertId)
    {
        $pk = $query->getAutoInc();

        if ($pk && is_string($pk)) {
            $type = $this->getFieldsBind($query->getTable())[$pk];

            if (self::PARAM_INT == $type) {
                $insertId = (int) $insertId;
            } elseif (self::PARAM_FLOAT == $type) {
                $insertId = (float) $insertId;
            }
        }

        return $insertId;
    }

    /**
     * 获取最近的错误信息.
     *
     * @return string
     */
    public function getError(): string
    {
        if ($this->PDOStatement) {
            $error = $this->PDOStatement->errorInfo();
            $error = $error[1] . ':' . $error[2];
        } else {
            $error = '';
        }

        if ('' != $this->queryStr) {
            $error .= "\n [ SQL语句 ] : " . $this->getLastsql();
        }

        return $error;
    }

    /**
     * 初始化数据库连接.
     *
     * @param bool $master 是否主服务器
     *
     * @return void
     */
    protected function initConnect(bool $master = true): void
    {
        if (!empty($this->config['deploy'])) {
            // 采用分布式数据库
            if ($master || $this->transTimes) {
                if (!$this->linkWrite) {
                    $this->linkWrite = $this->multiConnect(true);
                }

                $this->linkID = $this->linkWrite;
            } else {
                if (!$this->linkRead) {
                    $this->linkRead = $this->multiConnect(false);
                }

                $this->linkID = $this->linkRead;
            }
        } elseif (!$this->linkID) {
            // 默认单数据库
            $this->linkID = $this->connect();
        }
    }

    /**
     * 连接分布式服务器.
     *
     * @param bool $master 主服务器
     *
     * @return PDO
     */
    protected function multiConnect(bool $master = false): PDO
    {
        $config = [];

        // 分布式数据库配置解析
        foreach (['username', 'password', 'hostname', 'hostport', 'database', 'dsn', 'charset'] as $name) {
            $config[$name] = is_string($this->config[$name]) ? explode(',', $this->config[$name]) : $this->config[$name];
        }

        // 主服务器序号
        $m = floor(mt_rand(0, $this->config['master_num'] - 1));

        if ($this->config['rw_separate']) {
            // 主从式采用读写分离
            if ($master) {
                // 主服务器写入
                $r = $m;
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
        $dbMaster = false;

        if ($m != $r) {
            $dbMaster = [];
            foreach (['username', 'password', 'hostname', 'hostport', 'database', 'dsn', 'charset'] as $name) {
                $dbMaster[$name] = $config[$name][$m] ?? $config[$name][0];
            }
        }

        $dbConfig = [];

        foreach (['username', 'password', 'hostname', 'hostport', 'database', 'dsn', 'charset'] as $name) {
            $dbConfig[$name] = $config[$name][$r] ?? $config[$name][0];
        }

        return $this->connect($dbConfig, $r, $r == $m ? false : $dbMaster);
    }

    /**
     * 获取数据库的唯一标识.
     *
     * @param string $suffix 标识后缀
     *
     * @return string
     */
    public function getUniqueXid(string $suffix = ''): string
    {
        return $this->config['hostname'] . '_' . $this->config['database'] . $suffix;
    }

    /**
     * 执行数据库Xa事务
     *
     * @param callable $callback 数据操作方法回调
     * @param array    $dbs      多个查询对象或者连接对象
     *
     * @throws PDOException
     * @throws \Exception
     * @throws \Throwable
     *
     * @return mixed
     */
    public function transactionXa(callable $callback, array $dbs = [])
    {
        $xid = uniqid('xa');

        if (empty($dbs)) {
            $dbs[] = $this;
        }

        foreach ($dbs as $key => $db) {
            if ($db instanceof BaseQuery) {
                $db = $db->getConnection();

                $dbs[$key] = $db;
            }

            $db->startTransXa($db->getUniqueXid('_' . $xid));
        }

        try {
            $result = $callback($this);

            foreach ($dbs as $db) {
                $db->prepareXa($db->getUniqueXid('_' . $xid));
            }

            foreach ($dbs as $db) {
                $db->commitXa($db->getUniqueXid('_' . $xid));
            }

            return $result;
        } catch (\Exception  | \Throwable $e) {
            foreach ($dbs as $db) {
                $db->rollbackXa($db->getUniqueXid('_' . $xid));
            }

            throw $e;
        }
    }

    /**
     * 启动XA事务
     *
     * @param string $xid XA事务id
     *
     * @return void
     */
    public function startTransXa(string $xid): void
    {
    }

    /**
     * 预编译XA事务
     *
     * @param string $xid XA事务id
     *
     * @return void
     */
    public function prepareXa(string $xid): void
    {
    }

    /**
     * 提交XA事务
     *
     * @param string $xid XA事务id
     *
     * @return void
     */
    public function commitXa(string $xid): void
    {
    }

    /**
     * 回滚XA事务
     *
     * @param string $xid XA事务id
     *
     * @return void
     */
    public function rollbackXa(string $xid): void
    {
    }

    /**
     * 设置数据库时区.
     *
     * @param PDO   $pdo       PDO实例
     * @param string $timezone 时区名称，如 'Asia/Shanghai' 或 '+08:00'
     *
     * @return void
     */
    protected function setTimezone(PDO $pdo, string $timezone): void
    {
        if (empty($timezone)) {
            return;
        }

        try {
            $sql = $this->getSetTimezoneSql($timezone);
            if (!empty($sql)) {
                $pdo->exec($sql);
            }
        } catch (\Exception $e) {
            // 时区设置失败，记录日志但不中断连接
            $this->db->log('Set timezone failed: ' . $e->getMessage(), 'warning');
        }
    }

    /**
     * 获取设置时区的SQL语句.
     * 子类应重写此方法以提供数据库特定的时区设置SQL.
     *
     * @param string $timezone 时区名称
     *
     * @return string
     */
    protected function getSetTimezoneSql(string $timezone): string
    {
        // 默认实现，子类应重写此方法
        return '';
    }
}
