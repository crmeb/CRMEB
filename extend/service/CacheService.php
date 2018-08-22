<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2018/01/05
 */

namespace service;


use think\Cache;

class CacheService
{
    protected static $globalCacheName = '_cached_1515146130';


    public static function set($name, $value, $expire = 0)
    {
        return self::handler()->set($name,$value,$expire);
    }

    public static function get($name,$default = false)
    {
        return self::handler()->get($name,$default);
    }

    public static function rm($name)
    {
        return self::handler()->rm($name);
    }

    public static function handler()
    {
        return Cache::tag(self::$globalCacheName);
    }

    public static function clear()
    {
        return Cache::clear(self::$globalCacheName);
    }
}