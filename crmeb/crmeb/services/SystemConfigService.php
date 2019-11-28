<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/23
 */

namespace crmeb\services;


use app\admin\model\system\SystemConfig;

/** 获取系统配置服务类
 * Class SystemConfigService
 * @package service
 */
class SystemConfigService
{
    protected static $configList = null;

    const CACHE_SYSTEM = 'system_config';

    /**
     * 初始化
     */
    protected static function init()
    {
        if(!(self::$configList = CacheService::get(self::CACHE_SYSTEM))){
            self::$configList = self::getAll();
            CacheService::set('system_config',self::$configList);
        }
    }

    /**获取系统配置
     * @param $key
     * @return mixed|null
     */
    public static function config($key)
    {
        self::init();
        if (self::$configList === null) self::$configList = self::getAll();
        return isset(self::$configList[$key]) ? self::$configList[$key] : null;
    }

    /**
     * 获取单个配置效率更高
     * @param $key
     * @param string $default
     * @param bool $isCaChe 是否获取缓存配置
     * @return bool|mixed|string
     */
    public static function get($key,$default = '',bool $isCaChe = false)
    {
        if($isCaChe){
            try{
                return SystemConfig::getConfigValue($key);
            }catch (\Throwable $e){
                return $default;
            }
        }

        self::init();
        return isset(self::$configList[$key]) ? self::$configList[$key] : $default;
    }

    /** 获取多个配置
     * @param $keys ',' 隔开
     * @return array
     */
    public static function more($keys)
    {
        return SystemConfig::getMore($keys);
    }

    /**获取全部配置
     * @return array
     */
    public static function getAll()
    {
        return SystemConfig::getAllConfig() ?: [];
    }

}