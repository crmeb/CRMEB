<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace crmeb\services;

use think\cache\driver\Redis;
use think\facade\Cache as CacheStatic;
use think\facade\Log;

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
        $expire = !is_null($expire) ? $expire : SystemConfigService::get('cache_config', null, true);
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
        return self::handler()->set($name, $value, $expire ?? self::getExpire($expire));
    }

    /**
     * 如果不存在则写入缓存
     * @param string $name
     * @param bool $default
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
    public static function handler(?string $cacheName = null)
    {
        return CacheStatic::tag($cacheName ?: self::$globalCacheName);
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
     * @param string $type
     * @return bool
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

}
