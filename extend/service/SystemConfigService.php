<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/23
 */

namespace service;


use app\admin\model\system\SystemConfig;
use service\CacheService as Cache;

/** 获取系统配置服务类
 * Class SystemConfigService
 * @package service
 */
class SystemConfigService
{
    protected static $configList = null;

    /**获取系统配置
     * @param $key
     * @return mixed|null
     */
    public static function config($key)
    {
        if(self::$configList === null) self::$configList = self::getAll();
        return isset(self::$configList[$key]) ? self::$configList[$key] : null;
    }

    /**获取单个配置效率更高
     * @param $key
     * @return bool|mixed
     */
    public static function get($key)
    {
        $cacheName = 'config_'.$key;
        $config = Cache::get($cacheName);
        if($config) return $config;
        $config = SystemConfig::getValue($key);
        Cache::set($cacheName,$config);
        return $config;
    }

    /** 获取多个配置
     * @param $keys ',' 隔开
     * @return array
     */
    public static function more($keys,$update = false)
    {
        $keys = is_array($keys) ? implode(',',$keys) : $keys;
        $cacheName = 'more_'.$keys;
        $config = Cache::get($cacheName);
        if($config && !$update) return $config;
        $config = SystemConfig::getMore($keys);
        if(!$config) exception('对应的配置不存在!');
        Cache::set($cacheName,$config);
        return $config;
    }

    /**获取全部配置
     * @return array
     */
    public static function getAll()
    {
        $cacheName = 'config_all';
        $config = Cache::get($cacheName);
        if($config) return $config;
        $config = SystemConfig::getAllConfig()?:[];
        if(!$config) exception('对应的配置不存在!');
        Cache::set($cacheName,$config);
        return $config;
    }

}