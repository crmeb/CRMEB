<?php

namespace crmeb\utils;

use think\facade\Config;
use think\facade\Cache;

/**
 * Redis 操作
 * Class Redis
 * @package crmeb\utils
 * @mixin \Redis
 */
class Redis
{

    /**
     * 实例化本身
     * @var object
     */
    protected static $instance;

    /**
     * redis
     * @var \think\cache\Driver\Redis
     */
    protected $redis;

    /**
     * 配置项
     * @var array
     */
    protected $options = [
        'prefix' => '',
    ];

    /**
     * Redis constructor.
     */
    protected function __construct()
    {
        if (!extension_loaded('redis')) {
            throw new \BadFunctionCallException('not support: Redis');
        }

        $this->redis = Cache::store('redis');
    }

    /**
     * 实例化
     * @return Redis|object
     */
    public static function instance()
    {
        if (is_null(self::$instance)) self::$instance = new static();
        return self::$instance;
    }

    /**
     * 获取缓存前缀
     * @return mixed
     */
    public static function getPrefix()
    {
        return self::instance()->options['prefix'];
    }

    /**
     * 将多个数组出入到表头部
     * @param string $key
     * @param mixed ...$value
     * @return bool|int
     */
    public function push(string $key, ...$value)
    {
        return $this->redis->lPush($this->options['prefix'] . $key, ...$value);
    }

    /**
     * 移出并获取列表的第一个元素
     * @param string $key
     * @return string
     */
    public function pop(string $key)
    {
        return $this->redis->lPop($this->options['prefix'] . $key);
    }

    /**
     * 获取长度
     * @param string $key
     * @return int
     */
    public function len(string $key)
    {
        return $this->redis->lLen($this->options['prefix'] . $key);
    }

    /**
     * 获取数据
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        $func = function ($key) {
            return $this->options['prefix'] . $key;
        };
        if (is_array($key)) {
            $key = array_map($func, $key);
            return $this->redis->mget($key);
        } else {
            return $this->redis->get($this->options['prefix'] . $key);
        }
    }

    /**
     * 写入数据
     * @param string $key
     * @param $value
     * @param int $exprie
     * @return bool
     */
    public function set(string $key, $value, int $exprie = 0)
    {
        if ($exprie) {
            $res = $this->redis->setex($this->options['prefix'] . $key, $exprie, $value);
        } else {
            $res = $this->redis->set($this->options['prefix'] . $key, $value);
        }
        return $res;
    }

    /**
     * 静态调用
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        if (method_exists($instance->redis, $name)) {
            return self::instance()->redis->{$name}(...$arguments);
        }
    }
}