<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/23
 */

namespace service;


use app\admin\model\system\SystemConfig;

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
        return SystemConfig::getValue($key);
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
        return SystemConfig::getAllConfig()?:[];
    }

}