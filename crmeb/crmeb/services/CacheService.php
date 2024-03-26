<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2023 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace crmeb\services;

use think\facade\Cache;
use think\facade\Config;
use think\cache\TagSet;

/**
 * CRMEB 缓存类
 * Class CacheService
 * @package crmeb\services
 */
class CacheService
{
    /**
     * 过期时间
     * @var int
     */
    protected static $expire;

    /**
     * 写入缓存
     * @param string $name 缓存名称
     * @param mixed $value 缓存值
     * @param int|null $expire 缓存时间，为0读取系统缓存时间
     */
    public static function set(string $name, $value, int $expire = 0, string $tag = 'crmeb')
    {
        try {
            return Cache::tag($tag)->set($name, $value, $expire);
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
    public static function remember(string $name, $default = '', int $expire = 0, string $tag = 'crmeb')
    {
        try {
            return Cache::tag($tag)->remember($name, $default, $expire);
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
     * 清空全部缓存
     * @return bool
     * @author 吴汐
     * @email 442384644@qq.com
     * @date 2023/12/19
     */
    public static function clearAll()
    {
        return Cache::clear();
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

    /**
     * 指定缓存类型
     * @param string $type
     * @param string $tag
     * @return TagSet
     */
    public static function store(string $type = 'file', string $tag = 'crmeb')
    {
        return Cache::store($type)->tag($tag);
    }

    /**
     * 检查锁
     * @param string $key
     * @param int $timeout
     * @return bool
     */
    public static function setMutex(string $key, int $timeout = 10): bool
    {
        $curTime = time();
        $readMutexKey = "redis:mutex:{$key}";
        $mutexRes = Cache::store('redis')->handler()->setnx($readMutexKey, $curTime + $timeout);
        if ($mutexRes) {
            return true;
        }
        //就算意外退出，下次进来也会检查key，防止死锁
        $time = Cache::store('redis')->handler()->get($readMutexKey);
        if ($curTime > $time) {
            Cache::store('redis')->handler()->del($readMutexKey);
            return Cache::store('redis')->handler()->setnx($readMutexKey, $curTime + $timeout);
        }
        return false;
    }

    /**
     * 删除锁
     * @param string $key
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2022/11/22
     */
    public static function delMutex(string $key)
    {
        $readMutexKey = "redis:mutex:{$key}";
        Cache::store('redis')->handler()->del($readMutexKey);
    }


    /**
     * 数据库锁
     * @param $key
     * @param $fn
     * @param int $ex
     * @return mixed
     * @author 吴汐
     * @email 442384644@qq.com
     * @date 2023/03/01
     */
    public static function lock($key, $fn, int $ex = 6)
    {
        if (Config::get('cache.default') == 'file') {
            return $fn();
        }
        return app()->make(LockService::class)->exec($key, $fn, $ex);
    }
}
