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

    public static $ProtectedKey = [
        'wechat_appid', 'wechat_appsecret', 'wechat_token', 'wechat_encodingaeskey', 'wechat_encode',
        'pay_weixin_mchid', 'pay_weixin_client_cert', 'pay_weixin_client_key', 'pay_weixin_key', 'pay_weixin_open',
        'routine_appId', 'routine_appsecret',
        'pay_routine_mchid', 'pay_routine_key', 'pay_routine_client_cert', 'pay_routine_client_key', 'pay_weixin_open'
    ];

    /**获取系统配置
     * @param $key
     * @return mixed|null
     */
    public static function config($key)
    {
        if (self::$configList === null) self::$configList = self::getAll();
        return isset(self::$configList[$key]) ? self::$configList[$key] : null;
    }

    /**获取单个配置效率更高
     * @param $key
     * @return bool|mixed
     */
    public static function get($key)
    {
        return SystemConfig::getConfigValue($key);
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