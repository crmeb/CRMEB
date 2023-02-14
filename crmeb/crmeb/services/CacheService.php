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

use think\cache\Driver;
use think\cache\TagSet;
use think\facade\Cache;
use think\facade\Cache as CacheStatic;
use think\facade\Config;

/**
 * CRMEB 缓存类
 * Class CacheService
 * @package crmeb\services
 */
class CacheService
{
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
        if ($expire == null) {
            if (self::$expire) {
                return (int)self::$expire;
            }
            $default = Config::get('cache.default');
            $expire = Config::get('cache.stores.' . $default . '.expire');
            if (!is_int($expire)) {
                $expire = (int)$expire;
            }
        }
        return self::$expire = $expire;
    }

    /**
     * 写入缓存
     * @param string $name 缓存名称
     * @param mixed $value 缓存值
     * @param int|null $expire 缓存时间，为0读取系统缓存时间
     */
    public static function set(string $name, $value, int $expire = null, string $tag = 'crmeb')
    {
        try {
            return Cache::tag($tag)->set($name, $value, $expire ?? self::getExpire($expire));
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * 如果不存在则写入缓存
     * @param string $name
     * @param mixed $default
     * @param int|null $expire
     * @param string $tag
     * @return mixed|string|null
     */
    public static function remember(string $name, $default = '', int $expire = null, string $tag = 'crmeb')
    {
        try {
            return Cache::tag($tag)->remember($name, $default, $expire ?? self::getExpire($expire));
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
     * 读取缓存
     * @param string $name
     * @param mixed $default
     * @return mixed|string
     */
    public static function get(string $name, $default = '')
    {
        return Cache::get($name) ?? $default;
    }

    /**
     * 删除缓存
     * @param string $name
     * @return bool
     */
    public static function delete(string $name)
    {
        return Cache::delete($name);
    }

    /**
     * 清空缓存池
     * @return bool
     */
    public static function clear(string $tag = 'crmeb')
    {
        return Cache::tag($tag)->clear();
    }

    /**
     * 检查缓存是否存在
     * @param string $key
     * @return bool
     */
    public static function has(string $key)
    {
        try {
            return Cache::has($key);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /** 以下三个方法仅开启redis之后才使用 */
    /**
     * 设置redis入库队列
     * @param string $unique
     * @param int $number
     * @param int $type
     * @param bool $isPush
     * @return false
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public static function setStock(string $unique, int $number, int $type = 1, bool $isPush = true)
    {
        if (Config::get('cache.default') == 'file') return true;
        if (!$unique || !$number) return false;
        $name = (self::$redisQueueKey[$type] ?? '') . '_' . $type . '_' . $unique;
        if ($isPush) {
            Cache::store('redis')->delete($name);
        }
        $data = [];
        for ($i = 1; $i <= $number; $i++) {
            $data[] = $i;
        }
        return Cache::store('redis')->lPush($name, ...$data);
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
        if (Config::get('cache.default') == 'file') return true;
        $name = (self::$redisQueueKey[$type] ?? '') . '_' . $type . '_' . $unique;
        if ($number) {
            return Cache::store('redis')->lLen($name) >= $number;
        } else {
            return Cache::store('redis')->lLen($name);
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
        if (Config::get('cache.default') == 'file') return true;
        if (!$unique || !$number) return false;
        $name = (self::$redisQueueKey[$type] ?? '') . '_' . $type . '_' . $unique;
        $res = true;
        if ($number > Cache::store('redis')->lLen($name)) {
            return false;
        }
        for ($i = 1; $i <= $number; $i++) {
            $res = $res && Cache::store('redis')->lPop($name);
        }
        return $res;
    }

    /**
     * 存入当前秒杀商品属性有序集合
     * @param $set_key
     * @param $score
     * @param $value
     * @return false
     */
    public static function zAdd($set_key, $score, $value)
    {
        if (Config::get('cache.default') == 'file') return true;
        try {
            return Cache::store('redis')->zAdd($set_key, $score, $value);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * 取消集合中的秒杀商品
     * @param $set_key
     * @param $value
     * @return false
     */
    public static function zRem($set_key, $value)
    {
        if (Config::get('cache.default') == 'file') return true;
        try {
            return Cache::store('redis')->zRem($set_key, $value);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * 检查锁
     * @param int $uid
     * @param int $timeout
     * @return bool
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2022/11/22
     */
    public static function setMutex(string $key, int $timeout = 10)
    {
        $curTime = time();
        $readMutexKey = "redis:mutex:{$key}";
        $mutexRes = self::redisHandler()->handler()->setnx($readMutexKey, $curTime + $timeout);
        if ($mutexRes) {
            return true;
        }
        //就算意外退出，下次进来也会检查key，防止死锁
        $time = self::redisHandler()->handler()->get($readMutexKey);
        if ($curTime > $time) {
            self::redisHandler()->handler()->del($readMutexKey);
            return self::redisHandler()->handler()->setnx($readMutexKey, $curTime + $timeout);
        }
        return false;
    }

    /**
     * Redis缓存句柄
     *
     * @return Driver|TagSet
     */
    public static function redisHandler($type = null)
    {
        if ($type) {
            return CacheStatic::store('redis')->tag($type);
        } else {
            return CacheStatic::store('redis');
        }
    }

    /**
     * 删除锁
     * @param $uid
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2022/11/22
     */
    public static function delMutex(string $key)
    {
        $readMutexKey = "redis:mutex:{$key}";
        self::redisHandler()->handler()->del($readMutexKey);
    }
}
