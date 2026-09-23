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

namespace think;

use Closure;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use think\db\BaseQuery;
use think\db\ConnectionInterface;
use think\db\Query;
use think\db\Raw;

/**
 * Class DbManager.
 *
 * @mixin BaseQuery
 * @mixin Query
 */
class DbManager
{
    /**
     * 数据库连接实例.
     *
     * @var array
     */
    protected $instance = [];

    /**
     * 数据库配置.
     *
     * @var array
     */
    protected $config = [];

    /**
     * Event对象或者数组.
     *
     * @var array|object
     */
    protected $event;

    /**
     * SQL监听.
     *
     * @var array
     */
    protected $listen = [];

    /**
     * 查询次数.
     *
     * @var int
     */
    protected $queryTimes = 0;

    /**
     * 查询缓存对象
     *
     * @var CacheInterface
     */
    protected $cache;

    /**
     * 查询日志对象
     *
     * @var LoggerInterface
     */
    protected $log;

    /**
     * 架构函数.
     */
    public function __construct()
    {
        $this->modelMaker();
    }

    /**
     * 注入模型对象
     *
     * @return void
     */
    protected function modelMaker()
    {
        Model::maker(function (Model $model) {
            $model->setOption('db', $this);
            if (is_object($this->event)) {
                $model->setOption('event', $this->event);
            }
            $isAutoWriteTimestamp = $model->getAutoWriteTimestamp();

            if (is_null($isAutoWriteTimestamp)) {
                // 自动写入时间戳
                $model->isAutoWriteTimestamp($this->getConfig('auto_timestamp', true));
            }

            $dateFormat = $model->getDateFormat();

            if (is_null($dateFormat)) {
                // 设置时间戳格式
                $model->setDateFormat($this->getConfig('datetime_format', 'Y-m-d H:i:s'));
            }
        });
    }

    /**
     * 监听SQL.
     *
     * @return void
     */
    public function triggerSql(): void
    {
    }

    /**
     * 初始化配置参数.
     *
     * @param array $config 连接配置
     *
     * @return void
     */
    public function setConfig($config): void
    {
        $this->config = $config;
    }

    /**
     * 设置缓存对象
     *
     * @param CacheInterface $cache 缓存对象
     *
     * @return void
     */
    public function setCache(CacheInterface $cache): void
    {
        $this->cache = $cache;
    }

    /**
     * 设置日志对象
     *
     * @param LoggerInterface|Closure $log 日志对象
     *
     * @return void
     */
    public function setLog(LoggerInterface | Closure $log): void
    {
        $this->log = $log;
    }

    /**
     * 记录SQL日志.
     *
     * @param string $log  SQL日志信息
     * @param string $type 日志类型
     *
     * @return void
     */
    public function log(string $log, string $type = 'sql')
    {
        if ($this->log) {
            if ($this->log instanceof Closure) {
                call_user_func_array($this->log, [$type, $log]);
            } else {
                $this->log->log($type, $log);
            }
        }
    }

    /**
     * 获得查询日志（没有设置日志对象使用）.
     *
     * @deprecated
     * @param bool $clear 是否清空
     * @return array
     */
    public function getDbLog(bool $clear = false): array
    {
        return [];
    }

    /**
     * 获取配置参数.
     *
     * @param string $name    配置参数
     * @param mixed  $default 默认值
     *
     * @return mixed
     */
    public function getConfig(string $name = '', $default = null)
    {
        if ('' === $name) {
            return $this->config;
        }

        return $this->config[$name] ?? $default;
    }

    /**
     * 创建/切换数据库连接查询.
     *
     * @param string|array|null $name  连接配置信息
     * @param bool        $force 强制重新连接
     *
     * @return ConnectionInterface
     */
    public function connect(string|array|null $name = null, bool $force = false)
    {
        return $this->instance($name, $force);
    }

    /**
     * 创建数据库连接实例.
     *
     * @param string|array|null $name  连接标识
     * @param bool        $force 强制重新连接
     *
     * @return ConnectionInterface
     */
    protected function instance(string|array|null $name = null, bool $force = false): ConnectionInterface
    {
        if (empty($name)) {
            $name = $this->getConfig('default', 'mysql');
        }

        $key = is_array($name) ? md5(json_encode($name)) : $name;
        if ($force || !isset($this->instance[$key])) {
            $this->instance[$key] = $this->createConnection($name);
        }

        return $this->instance[$key];
    }

    /**
     * 获取连接配置.
     *
     * @param string $name
     *
     * @return array
     */
    protected function getConnectionConfig(string $name): array
    {
        $connections = $this->getConfig('connections');
        if (!isset($connections[$name])) {
            throw new InvalidArgumentException('Undefined db config:' . $name);
        }

        return $connections[$name];
    }

    /**
     * 创建连接.
     *
     * @param string|array $config
     *
     * @return ConnectionInterface
     */
    protected function createConnection(string|array $config): ConnectionInterface
    {
        $config = is_array($config) ? $config : $this->getConnectionConfig($config);

        $type = !empty($config['type']) ? $config['type'] : 'mysql';

        if (str_contains($type, '\\')) {
            $class = $type;
        } else {
            $class = '\\think\\db\\connector\\' . ucfirst($type);
        }

        /** @var ConnectionInterface $connection */
        $connection = new $class($config);
        $connection->setDb($this);

        if ($this->cache) {
            $connection->setCache($this->cache);
        }

        return $connection;
    }

    /**
     * 使用表达式设置数据.
     *
     * @param string $value 表达式
     *
     * @return Raw
     */
    public function raw(string $value, array $bind = []): Raw
    {
        return new Raw($value, $bind);
    }

    /**
     * 更新查询次数.
     * @deprecated
     * @return void
     */
    public function updateQueryTimes(): void
    {
    }

    /**
     * 重置查询次数.
     * @deprecated
     * @return void
     */
    public function clearQueryTimes(): void
    {
        $this->queryTimes = 0;
    }

    /**
     * 获得查询次数.
     * @deprecated
     * @return int
     */
    public function getQueryTimes(): int
    {
        return $this->queryTimes;
    }

    /**
     * 监听SQL执行.
     *
     * @param callable $callback 回调方法
     *
     * @return void
     */
    public function listen(callable $callback): void
    {
        $this->listen[] = $callback;
    }

    /**
     * 获取监听SQL执行.
     *
     * @return array
     */
    public function getListen(): array
    {
        return $this->listen;
    }

    /**
     * 获取所有连接实列.
     *
     * @return array
     */
    public function getInstance(): array
    {
        return $this->instance;
    }

    /**
     * 注册回调方法.
     *
     * @param string   $event    事件名
     * @param callable $callback 回调方法
     *
     * @return void
     */
    public function event(string $event, callable $callback): void
    {
        $this->event[$event][] = $callback;
    }

    /**
     * 触发事件.
     *
     * @param string $event  事件名
     * @param mixed  $params 传入参数
     *
     * @return mixed
     */
    public function trigger(string $event, $params = null)
    {
        if (isset($this->event[$event])) {
            foreach ($this->event[$event] as $callback) {
                call_user_func_array($callback, [$params]);
            }
        }
    }

    public function __call($method, $args)
    {
        return call_user_func_array([$this->connect(), $method], $args);
    }
}
