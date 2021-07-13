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

use Psr\SimpleCache\CacheInterface;
use think\DbManager;

/**
 * 数据库连接基础类
 */
abstract class Connection implements ConnectionInterface
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
     * @var DbManager
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
     * @var CacheInterface
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
     * 创建查询对象
     */
    public function newQuery()
    {
        $class = $this->getQueryClass();

        /** @var BaseQuery $query */
        $query = new $class($this);

        $timeRule = $this->db->getConfig('time_query_rule');
        if (!empty($timeRule)) {
            $query->timeRule($timeRule);
        }

        return $query;
    }

    /**
     * 指定表名开始查询
     * @param $table
     * @return BaseQuery
     */
    public function table($table)
    {
        return $this->newQuery()->table($table);
    }

    /**
     * 指定表名开始查询(不带前缀)
     * @param $name
     * @return BaseQuery
     */
    public function name($name)
    {
        return $this->newQuery()->name($name);
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
     * @param CacheInterface $cache
     * @return void
     */
    public function setCache(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * 获取当前的缓存对象
     * @access public
     * @return CacheInterface|null
     */
    public function getCache()
    {
        return $this->cache;
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
     * 数据库SQL监控
     * @access protected
     * @param string $sql    执行的SQL语句 留空自动获取
     * @param bool   $master  主从标记
     * @return void
     */
    protected function trigger(string $sql = '', bool $master = false): void
    {
        $listen = $this->db->getListen();

        if (!empty($listen)) {
            $runtime = number_format((microtime(true) - $this->queryStartTime), 6);
            $sql     = $sql ?: $this->getLastsql();

            if (empty($this->config['deploy'])) {
                $master = null;
            }

            foreach ($listen as $callback) {
                if (is_callable($callback)) {
                    $callback($sql, $runtime, $master);
                }
            }
        }
    }

    /**
     * 缓存数据
     * @access protected
     * @param CacheItem $cacheItem 缓存Item
     */
    protected function cacheData(CacheItem $cacheItem)
    {
        if ($cacheItem->getTag() && method_exists($this->cache, 'tag')) {
            $this->cache->tag($cacheItem->getTag())->set($cacheItem->getKey(), $cacheItem->get(), $cacheItem->getExpire());
        } else {
            $this->cache->set($cacheItem->getKey(), $cacheItem->get(), $cacheItem->getExpire());
        }
    }

    /**
     * 分析缓存Key
     * @access protected
     * @param BaseQuery $query 查询对象
     * @param string    $method 查询方法
     * @return string
     */
    protected function getCacheKey(BaseQuery $query, string $method = ''): string
    {
        if (!empty($query->getOptions('key')) && empty($method)) {
            $key = 'think:' . $this->getConfig('database') . '.' . $query->getTable() . '|' . $query->getOptions('key');
        } else {
            $key = $query->getQueryGuid();
        }

        return $key;
    }

    /**
     * 分析缓存
     * @access protected
     * @param BaseQuery $query 查询对象
     * @param array     $cache 缓存信息
     * @param string    $method 查询方法
     * @return CacheItem
     */
    protected function parseCache(BaseQuery $query, array $cache, string $method = ''): CacheItem
    {
        [$key, $expire, $tag] = $cache;

        if ($key instanceof CacheItem) {
            $cacheItem = $key;
        } else {
            if (true === $key) {
                $key = $this->getCacheKey($query, $method);
            }

            $cacheItem = new CacheItem($key);
            $cacheItem->expire($expire);
            $cacheItem->tag($tag);
        }

        return $cacheItem;
    }

    /**
     * 获取返回或者影响的记录数
     * @access public
     * @return integer
     */
    public function getNumRows(): int
    {
        return $this->numRows;
    }

    /**
     * 析构方法
     * @access public
     */
    public function __destruct()
    {
        // 关闭连接
        $this->close();
    }
}
