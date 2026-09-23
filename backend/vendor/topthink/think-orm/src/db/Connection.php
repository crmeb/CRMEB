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

use Psr\SimpleCache\CacheInterface;
use think\DbManager;

/**
 * 数据库连接基础类.
 */
abstract class Connection implements ConnectionInterface
{
    const PARAM_INT   = 1;
    const PARAM_STR   = 2;
    const PARAM_BOOL  = 5;
    const PARAM_FLOAT = 21;

    /**
     * 当前SQL指令.
     *
     * @var string
     */
    protected $queryStr = '';

    /**
     * 返回或者影响记录数.
     *
     * @var int
     */
    protected $numRows = 0;

    /**
     * 事务指令数.
     *
     * @var int
     */
    protected $transTimes = 0;

    /**
     * 错误信息.
     *
     * @var string
     */
    protected $error = '';

    /**
     * 数据库连接ID 支持多个连接.
     *
     * @var array
     */
    protected $links = [];

    /**
     * 当前连接ID.
     *
     * @var object
     */
    protected $linkID;

    /**
     * 当前读连接ID.
     *
     * @var object
     */
    protected $linkRead;

    /**
     * 当前写连接ID.
     *
     * @var object
     */
    protected $linkWrite;

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
     * Builder对象
     *
     * @var Builder
     */
    protected $builder;

    /**
     * Db对象
     *
     * @var DbManager
     */
    protected $db;

    /**
     * 是否读取主库.
     *
     * @var bool
     */
    protected $readMaster = false;

    /**
     * 数据库连接参数配置.
     *
     * @var array
     */
    protected $config = [];

    /**
     * 缓存对象
     *
     * @var CacheInterface
     */
    protected $cache;

    /**
     * 架构函数 读取数据库配置信息.
     *
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
     *
     * @return Builder
     */
    public function getBuilder()
    {
        return $this->builder;
    }

    /**
     * 创建查询对象
     */
    public function newQuery(): BaseQuery
    {
        $class = $this->getQueryClass();

        return new $class($this);
    }

    /**
     * 设置当前的数据库Db对象
     *
     * @param DbManager $db
     *
     * @return void
     */
    public function setDb(DbManager $db)
    {
        $this->db = $db;
    }

    /**
     * 设置当前的缓存对象
     *
     * @param CacheInterface $cache
     *
     * @return void
     */
    public function setCache(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * 获取当前的缓存对象
     *
     * @return CacheInterface|null
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * 获取数据库的配置参数.
     *
     * @param string $config 配置名称
     *
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
     * 数据库SQL监控.
     *
     * @param string $sql    执行的SQL语句 留空自动获取
     * @param bool   $master 主从标记
     *
     * @return void
     */
    protected function trigger(string $sql = '', bool $master = false): void
    {
        $listen = $this->db->getListen();
        if (empty($listen)) {
            $listen[] = function ($sql, $time, $master) {
                if (str_starts_with($sql, 'CONNECT:')) {
                    $this->db->log($sql);

                    return;
                }

                // 记录SQL
                if (is_bool($master)) {
                    // 分布式记录当前操作的主从
                    $master = $master ? 'master|' : 'slave|';
                } else {
                    $master = '';
                }

                $this->db->log($sql . ' [ ' . $master . 'RunTime:' . $time . 's ]');
            };
        }

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

    /**
     * 缓存数据.
     *
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
     * 分析缓存Key.
     *
     * @param BaseQuery $query  查询对象
     * @param string    $method 查询方法
     *
     * @return string
     */
    protected function getCacheKey(BaseQuery $query, string $method = ''): string
    {
        if (!empty($query->getOption('key')) && empty($method)) {
            $key = 'think_' . $this->getConfig('database') . '.' . var_export($query->getTable(), true) . '|' . var_export($query->getOption('key'), true);
        } else {
            $key = $query->getQueryGuid();
        }

        return $key;
    }

    /**
     * 分析缓存.
     *
     * @param BaseQuery $query  查询对象
     * @param array     $cache  缓存信息
     * @param string    $method 查询方法
     *
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
     * 获取返回或者影响的记录数.
     *
     * @return int
     */
    public function getNumRows(): int
    {
        return $this->numRows;
    }

    /**
     * 获取最终的SQL语句.
     *
     * @param string $sql  带参数绑定的sql语句
     * @param array  $bind 参数绑定列表
     *
     * @return string
     */
    public function getRealSql(string $sql, array $bind = []): string
    {
        foreach ($bind as $key => $val) {
            $value = strval(is_array($val) ? $val[0] : $val);
            $type  = is_array($val) ? $val[1] : self::PARAM_STR;

            if (self::PARAM_FLOAT == $type || self::PARAM_STR == $type) {
                $value = '\'' . addslashes($value) . '\'';
            } elseif (self::PARAM_INT == $type && '' === $value) {
                $value = '0';
            }

            // 判断占位符
            $sql = is_numeric($key) ?
            substr_replace($sql, $value, strpos($sql, '?'), 1) :
            str_replace(
                [':' . $key . ' ', ':' . $key . ',', ':' . $key . ')'],
                [$value . ' ', $value . ',', $value . ')'],
                $sql . ' ');
        }

        return rtrim($sql);
    }

    /**
     * 析构方法.
     */
    public function __destruct()
    {
        // 关闭连接
        $this->close();
    }

    public function __call($method, $args)
    {
        // 调用Query类方法
        return call_user_func_array([$this->newQuery(), $method], $args);
    }    
}
