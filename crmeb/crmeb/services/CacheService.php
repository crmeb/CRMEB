<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace crmeb\services;

use think\facade\Cache as CacheStatic;
use think\facade\Config;

/**
 * crmeb 缓存类
 * Class CacheService
 * @package crmeb\services
 * @mixin \Redis
 */
class CacheService
{
    /**
     * 标签名
     * @var string
     */
    protected static $globalCacheName = '_cached_1515146130';

    /**
     * 缓存队列key
     * @var string[]
     */
    protected static $redisQueueKey = [
        0 => 'product',
        1 => 'seckill',
        2 => 'bargain',
        3 => 'combination',
        6 => 'advance'
    ];

    /**
     * 过期时间
     * @var int
     */
    protected static $expire;

    /**
     * 获取缓存过期时间
     * @param int|null $expire
     * @return int
     */
    protected static function getExpire(int $expire = null): int
    {
        if (self::$expire) {
            return (int)self::$expire;
        }
        $default = Config::get('cache.default');
        $expire = Config::get('cache.stores.' . $default . '.expire');
        if (!is_int($expire))
            $expire = (int)$expire;

        return self::$expire = $expire;
    }

    /**
     * 写入缓存
     * @param string $name 缓存名称
     * @param mixed $value 缓存值
     * @param int $expire 缓存时间，为0读取系统缓存时间
     * @return bool
     */
    public static function set(string $name, $value, int $expire = null): bool
    {
        try {
            return self::handler()->set($name, $value, $expire ?? self::getExpire($expire));
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * 如果不存在则写入缓存
     * @param string $name
     * @param bool $default
     * @param int|null $expire
     * @return mixed
     */
    public static function get(string $name, $default = false, int $expire = null)
    {
        try {
            return self::handler()->remember($name, $default, $expire ?? self::getExpire($expire));
        } catch (\Throwable $e) {
            try {
                if (is_callable($default)) {
                    return $default();
                } else {
                    return $default;
                }
            } catch (\Throwable $e) {
                return null;
            }
        }
    }

    /**
     * 删除缓存
     * @param string $name
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public static function delete(string $name)
    {
        return CacheStatic::delete($name);
    }

    /**
     * 缓存句柄
     *
     * @return \think\cache\TagSet|CacheStatic
     */
    public static function handler()
    {
        return CacheStatic::tag(self::$globalCacheName);
    }

    /**
     * 清空缓存池
     * @return bool
     */
    public static function clear()
    {
        return self::handler()->clear();
    }

    /**
     * Redis缓存句柄
     *
     * @return \think\cache\TagSet|CacheStatic
     */
    public static function redisHandler(string $type = null)
    {
        if ($type) {
            return CacheStatic::store('redis')->tag($type);
        } else {
            return CacheStatic::store('redis');
        }
    }

    /**
     * 放入令牌桶
     * @param string $key
     * @param array $value
     * @param null $expire
     * @param string $type
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public static function setTokenBucket(string $key, $value, $expire = null, string $type = 'admin')
    {
        try {
            $redisCahce = self::redisHandler($type);
            return $redisCahce->set($key, $value, $expire);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * 清除所有令牌桶
     * @param string $type
     * @return bool
     */
    public static function clearTokenAll(string $type = 'admin')
    {
        try {
            return self::redisHandler($type)->clear();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * 清除令牌桶
     * @param string $type
     * @return bool
     */
    public static function clearToken(string $key)
    {
        try {
            return self::redisHandler()->delete($key);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * 查看令牌是否存在
     * @param string $key
     * @return bool
     */
    public static function hasToken(string $key)
    {
        try {
            return self::redisHandler()->has($key);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * 获取token令牌桶
     * @param string $key
     * @return mixed|null
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public static function getTokenBucket(string $key)
    {
        try {
            return self::redisHandler()->get($key, null);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * 获取指定分数区间的成员
     * @param $key
     * @param int $start
     * @param int $end
     * @param array $options
     * @return mixed
     */
    public static function zRangeByScore($key, $start = '-inf', $end = '+inf', array $options = [])
    {
        return self::redisHandler()->zRangeByScore($key, $start, $end, $options);
    }


    /**
     * 魔术方法
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return self::redisHandler()->{$name}(...$arguments);
    }

    /**
     * 魔术方法
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return self::redisHandler()->{$name}(...$arguments);
    }

    /**
     * 设置redis入库队列
     * @param string $unique
     * @param int $number
     * @param int $type
     * @param bool $isPush true :重置 false：累加
     * @return bool
     */
    public static function setStock(string $unique, int $number, int $type = 1, bool $isPush = true)
    {
        if (!$unique || !$number) return false;
        $name = (self::$redisQueueKey[$type] ?? '') . '_' . $type . '_' . $unique;
        /** @var self $cache */
        $cache = self::redisHandler();
        $res = true;
        if ($isPush) {
            $cache->del($name);
        }
        $data = [];
        for ($i = 1; $i <= $number; $i++) {
            $data[] = $i;
        }
        return $res && $cache->lPush($name, ...$data);
    }

    /**
     * 是否有库存|返回库存
     * @param string $unique
     * @param int $number
     * @param int $type
     * @return bool
     */
    public static function checkStock(string $unique, int $number = 0, int $type = 1)
    {
        $name = (self::$redisQueueKey[$type] ?? '') . '_' . $type . '_' . $unique;
        if ($number) {
            return self::redisHandler()->lLen($name) >= $number;
        } else {
            return self::redisHandler()->lLen($name);
        }
    }

    /**
     * 弹出redis队列中的库存条数
     * @param string $unique
     * @param int $number
     * @param int $type
     * @return bool
     */
    public static function popStock(string $unique, int $number, int $type = 1)
    {
        if (!$unique || !$number) return false;
        $name = (self::$redisQueueKey[$type] ?? '') . '_' . $type . '_' . $unique;
        /** @var self $cache */
        $cache = self::redisHandler();
        $res = true;
        if ($number > $cache->lLen($name)) {
            return false;
        }
        for ($i = 1; $i <= $number; $i++) {
            $res = $res && $cache->lPop($name);
        }

        return $res;
    }
}
