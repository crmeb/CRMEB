<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2018/01/10
 */

namespace Api;


use service\HttpService;
use service\SystemConfigService;

class Express
{
    protected static $api = [
        'query'=>'http://jisukdcx.market.alicloudapi.com/express/query',
        'type'=>'http://jisukdcx.market.alicloudapi.com/express/type'
    ];

    public static function query($number,$type = 'auto')
    {
        $appCode = SystemConfigService::config('system_express_app_code');
        if(!$appCode) return false;
        //WIKI https://market.aliyun.com/products/57126001/cmapi011120.html?spm=5176.2020520132.101.4.TYdLsb#sku=yuncode512000008
        $res = HttpService::getRequest(self::$api['query'],compact('number','type'),['Authorization:APPCODE '.$appCode]);
        $result = json_decode($res,true)?:false;
        return $result;
    }

    public static function type()
    {
        $appCode = SystemConfigService::config('system_express_app_code');
        if(!$appCode) return false;
        $res = HttpService::getRequest(self::$api['type'],[],['Authorization:APPCODE '.$appCode]);
        $result = json_decode($res,true)?:false;
        return $result;
    }
}