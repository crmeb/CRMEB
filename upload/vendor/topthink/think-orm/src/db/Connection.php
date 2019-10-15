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

use think\CacheManager;
use think\cache\CacheItem;
use think\DbManager;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;
use think\exception\PDOException;

/**
 * 数据库连接基础类
 */
abstract class Connection
{

    /**
     * 当前SQL指令
     * @var string
     */
    protected $queryStr = '';

    /**
     * 返回或者影响记录数
     * @var int
     */
    protected $numRows = 0;

    /**
     * 事务指令数
     * @var int
     */
    protected $transTimes = 0;

    /**
     * 错误信息
     * @var string
     */
    protected $error = '';

    /**
     * 数据库连接ID 支持多个连接
     * @var array
     */
    protected $links = [];

    /**
     * 当前连接ID
     * @var object
     */
    protected $linkID;

    /**
     * 当前读连接ID
     * @var object
     */
    protected $linkRead;

    /**
     * 当前写连接ID
     * @var object
     */
    protected $linkWrite;

    /**
     * 数据表信息
     * @var array
     */
    protected $info = [];

    /**
     * 查询开始时间
     * @var float
     */
    protected $queryStartTime;

    /**
     * Builder对象
     * @var Builder
     */
    protected $builder;

    /**
     * Db对象
     * @var Db
     */
    protected $db;

    /**
     * 是否读取主库
     * @var bool
     */
    protected $readMaster = false;

    /**
     * 数据库连接参数配置
     * @var array
     */
    protected $config = [];

    /**
     * 缓存对象
     * @var Cache
     */
    protected $cache;

    /**
     * 架构函数 读取数据库配置信息
     * @access public
     * @param array $config 数据库配置数组
     */
    public function __construct(array $config = [])
    {
        if (!empty($config)) {
            $this->config = array_merge($this->config, $config);
        }

        // 创建Builder对象
        $class = $this->getBuilderClass();

        $this->builder = new $class($this);

        // 执行初始化操作
        $this->initialize();
    }

    /**
     * 初始化
     * @access protected
     * @return void
     */
    protected function initialize()
    {
    }

    /**
     * 获取当前连接器类对应的Query类
     * @access public
     * @return string
     */
    abstract public function getQueryClass(): string;

    /**
     * 获取当前连接器类对应的Builder类
     * @access public
     * @return string
     */
    abstract public function getBuilderClass(): string;

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
     * 设置当前的数据库Db对象
     * @access public
     * @param DbManager $db
     * @return void
     */
    public function setDb(DbManager $db)
    {
        $this->db = $db;
    }

    /**
     * 设置当前的缓存对象
     * @access public
     * @param CacheManager $cache
     * @return void
     */
    public function setCache(CacheManager $cache)
    {
        $this->cache = $cache;
    }

    /**
     * 获取数据库的配置参数
     * @access public
     * @param string $config 配置名称
     * @return mixed
     */
    public function getConfig(string $config = '')
    {
        if ('' === $config) {
            return $this->config;
        }
        return $this->config[$config] ?? null;
    }

    /**
     * 设置数据库的配置参数
     * @access public
     * @param array $config 配置
     * @return void
     */
    public function setConfig(array $config)
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * 连接数据库方法
     * @access public
     * @param array   $config  接参数
     * @param integer $linkNum 连接序号
     * @return mixed
     * @throws Exception
     */
    abstract public function connect(array $config = [], $linkNum = 0);

    /**
     * 释放查询结果
     * @access public
     */
    abstract public function free();

    /**
     * 查找单条记录
     * @access public
     * @param BaseQuery $query 查询对象
     * @return array
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws DataNotFoundException
     */
    abstract public function find(BaseQuery $query): array;

    /**
     * 使用游标查询记录
     * @access public
     * @param BaseQuery $query 查询对象
     * @return \Generator
     */
    abstract public function cursor(BaseQuery $query);

    /**
     * 查找记录
     * @access public
     * @param BaseQuery $query 查询对象
     * @return array
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws DataNotFoundException
     */
    abstract public function select(BaseQuery $query): array;

    /**
     * 插入记录
     * @access public
     * @param BaseQuery   $query        查询对象
     * @param boolean $getLastInsID 返回自增主键
     * @return mixed
     */
    abstract public function insert(BaseQuery $query, bool $getLastInsID = false);

    /**
     * 批量插入记录
     * @access public
     * @param BaseQuery   $query   查询对象
     * @param mixed   $dataSet 数据集
     * @return integer
     * @throws \Exception
     * @throws \Throwable
     */
    abstract public function insertAll(BaseQuery $query, array $dataSet = []): int;

    /**
     * 更新记录
     * @access public
     * @param BaseQuery $query 查询对象
     * @return integer
     * @throws Exception
     * @throws PDOException
     */
    abstract public function update(BaseQuery $query): int;

    /**
     * 删除记录
     * @access public
     * @param BaseQuery $query 查询对象
     * @return int
     * @throws Exception
     * @throws PDOException
     */
    abstract public function delete(BaseQuery $query): int;

    /**
     * 得到某个字段的值
     * @access public
     * @param BaseQuery  $query   查询对象
     * @param string $field   字段名
     * @param mixed  $default 默认值
     * @param bool   $one     返回一个值
     * @return mixed
     */
    abstract public function value(BaseQuery $query, string $field, $default = null);

    /**
     * 得到某个列的数组
     * @access public
     * @param BaseQuery  $query  查询对象
     * @param string $column 字段名 多个字段用逗号分隔
     * @param string $key    索引
     * @return array
     */
    abstract public function column(BaseQuery $query, string $column, string $key = ''): array;

    /**
     * 执行数据库事务
     * @access public
     * @param callable $callback 数据操作方法回调
     * @return mixed
     * @throws PDOException
     * @throws \Exception
     * @throws \Throwable
     */
    abstract public function transaction(callable $callback);

    /**
     * 启动事务
     * @access public
     * @return void
     * @throws \PDOException
     * @throws \Exception
     */
    abstract public function startTrans();

    /**
     * 用于非自动提交状态下面的查询提交
     * @access public
     * @return void
     * @throws PDOException
     */
    abstract public function commit();

    /**
     * 事务回滚
     * @access public
     * @return void
     * @throws PDOException
     */
    abstract public function rollback();

    /**
     * 关闭数据库（或者重新连接）
     * @access public
     * @return $this
     */
    abstract public function close();

    /**
     * 获取最近一次查询的sql语句
     * @access public
     * @return string
     */
    abstract public function getLastSql(): string;

    /**
     * 获取最近插入的ID
     * @access public
     * @param BaseQuery  $query  查询对象
     * @param string $sequence 自增序列名
     * @return mixed
     */
    abstract public function getLastInsID(BaseQuery $query, string $sequence = null);

    /**
     * 初始化数据库连接
     * @access protected
     * @param boolean $master 是否主服务器
     * @return void
     */
    abstract protected function initConnect(bool $master = true);

    /**
     * 记录SQL日志
     * @access protected
     * @param string $log  SQL日志信息
     * @param string $type 日志类型
     * @return void
     */
    protected function log($log, $type = 'sql')
    {
        if ($this->config['debug']) {
            $this->db->log($log, $type);
        }
    }

    /**
     * 缓存数据
     * @access protected
     * @param CacheItem $cacheItem 缓存Item
     */
    protected function cacheData(CacheItem $cacheItem)
    {
        if ($cacheItem->getTag()) {
            $this->cache->tag($cacheItem->getTag());
        }

        $this->cache->set($cacheItem->getKey(), $cacheItem->get(), $cacheItem->getExpire());
    }

    /**
     * 分析缓存
     * @access protected
     * @param BaseQuery $query 查询对象
     * @param array $cache 缓存信息
     * @return CacheItem
     */
    protected function parseCache(BaseQuery $query, array $cache): CacheItem
    {
        list($key, $expire, $tag) = $cache;

        if ($key instanceof CacheItem) {
            $cacheItem = $key;
        } else {
            if (true === $key) {
                if (!empty($query->getOptions('key'))) {
                    $key = 'think:' . $this->getConfig('database') . '.' . $query->getTable() . '|' . $query->getOptions('key');
                } else {
                    $key = md5($this->getConfig('database') . serialize($query->getOptions()));
                }
            }

            $cacheItem = new CacheItem($key);
            $cacheItem->expire($expire);
            $cacheItem->tag($tag);
        }

        return $cacheItem;
    }

    /**
     * 延时更新检查 返回false表示需要延时
     * 否则返回实际写入的数值
     * @access public
     * @param string  $type     自增或者自减
     * @param string  $guid     写入标识
     * @param float   $step     写入步进值
     * @param integer $lazyTime 延时时间(s)
     * @return false|integer
     */
    public function lazyWrite(string $type, string $guid, float $step, int $lazyTime)
    {
        if (!$this->cache) {
            return $step;
        }

        if (!$this->cache->has($guid . '_time')) {
            // 计时开始
            $this->cache->set($guid . '_time', time(), 0);
            $this->cache->$type($guid, $step);
        } elseif (time() > $this->cache->get($guid . '_time') + $lazyTime) {
            // 删除缓存
            $value = $this->cache->$type($guid, $step);
            $this->cache->delete($guid);
            $this->cache->delete($guid . '_time');
            return 0 === $value ? false : $value;
        } else {
            // 更新缓存
            $this->cache->$type($guid, $step);
        }

        return false;
    }

    /**
     * 析构方法
     * @access public
     */
    public function __destruct()
    {
        // 释放查询
        $this->free();

        // 关闭连接
        $this->close();
    }
}
