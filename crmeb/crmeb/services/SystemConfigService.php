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
        $confingFun = function () {
            return self::getAll();
        };
        try {
            self::$configList = CacheService::get(self::CACHE_SYSTEM, $confingFun);
        } catch (\Throwable $e) {
            try {
                self::$configList = $confingFun();
            } catch (\Exception $e) {
                self::$configList = [];
            }
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
        return self::$configList[$key] ?? null;
    }

    /**
     * 获取单个配置效率更高
     * @param $key
     * @param string $default
     * @param bool $isCaChe 是否获取缓存配置
     * @return bool|mixed|string
     */
    public static function get($key, $default = '', bool $isCaChe = false)
    {
        if ($isCaChe) {
            try {
                return SystemConfig::getConfigValue($key);
            } catch (\Throwable $e) {
                return $default;
            }
        }

        self::init();
        return self::$configList[$key] ?? $default;
    }

    /**
     * 获取多个配置
     * @param array $keys 示例 [['appid','1'],'appkey']
     * @param bool $isCaChe 是否获取缓存配置
     * @return array
     */
    public static function more(array $keys, bool $isCaChe = false)
    {
        self::init();
        $callable = function () use ($keys) {
            try {
                $list = SystemConfig::getMore($keys);
                return self::getDefaultValue($keys, $list);
            } catch (\Exception $e) {
                return self::getDefaultValue($keys);
            }
        };
        if ($isCaChe)
            return $callable();
        try {
            return self::getDefaultValue($keys, self::$configList);
        } catch (\Throwable $e) {
            return $callable();
        }
    }

    /**
     * 获取默认配置
     * @param array $keys
     * @return array
     */
    public static function getDefaultValue(array $keys, array $configList = [])
    {
        $value = [];
        foreach ($keys as $val) {
            if (is_array($val)) {
                $k = $val[0] ?? '';
                $v = $val[1] ?? '';
            } else {
                $k = $val;
                $v = '';
            }
            $value[$k] = $configList[$k] ?? $v;
        }
        return $value;
    }

    /**获取全部配置不缓存
     * @return array
     */
    public static function getAll()
    {
        return SystemConfig::getAllConfig() ?: [];
    }

}