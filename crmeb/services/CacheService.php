<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2018/01/05
 */

namespace crmeb\services;


use think\Cache;
use think\facade\Cache as CacheStatic;

class CacheService
{
    protected static $globalCacheName = '_cached_1515146130';


    public static function set($name, $value, $expire = 60)
    {
        return self::handler()->set($name,$value,$expire);
    }

    public static function get($name,$default = false)
    {
        return self::handler()->remember($name,$default);
    }

    public static function rm($name)
    {
        return self::handler()->clear($name);
    }

    public static function handler()
    {
        return CacheStatic::tag(self::$globalCacheName);
    }

    public static function clear()
    {
        return Cache::clear(self::$globalCacheName);
    }
}