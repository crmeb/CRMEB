<?php
/**
 * Created by CRMEB.
 * User: 136327134@qq.com
 * Date: 2019/4/12 11:19
 */

namespace crmeb\services;

/*
 * Api 日志和系统字段整合
 * class ApiLogs
 * */

use think\Exception;
use think\Log;

class ApiLogs
{
    // +----------------------------------------------------------------------
    // | 缓存前缀配置区域
    // +----------------------------------------------------------------------
    //ACCESS_TOKEN缓存前缀
    const ACCESS_TOKEN_PREFIX='AccessToken:';
    //api info 缓存前缀
    const AB_API_INFO='eb_ApiInfo:';
    //未支付订单取消
    const ORDER_UNPAID_PAGE='order_unpaid_page';

    // +----------------------------------------------------------------------
    // | 缓存时间配置区域
    // +----------------------------------------------------------------------
    //缓存时间
    const  EXPIRE=86400;

    // +----------------------------------------------------------------------
    // | 系统预设字段明配置区域
    // +----------------------------------------------------------------------
    //access-token验证字段
    const ACCESS_TOKEN="access-token";
    //Api版本字段
    const API_VERSION='version';
    //用户token验证字段
    const USER_TOKEN='user-token';
    //系统预设日志
    protected static $logInfo=null;
    /*
     * 获取本类所有常量配置
     * @param string $code 常量名
     * @return array | string
     * */
    public static function getConstants($code='') {
        $oClass = new \ReflectionClass(__CLASS__);
        $stants=$oClass->getConstants();
        if($code) return isset($stants[$code]) ? $stants[$code] : '';
        else return $stants;
    }

    /*
     * 错误日志记录
     *
     * */
    public static function recodeErrorLog(Exception $exception)
    {
        $data=[
            'code'=>$exception->getCode(),
            'msg'=>$exception->getMessage(),
            'file'=>$exception->getFile(),
            'line'=>$exception->getLine(),
        ];
        $log="[{$data['code']}] {$data['msg']} [{$data['file']} : {$data['line']}]";
        self::writeLog($log,'e');
    }
    /*
     * 记录日志
     * $param string $contentlog 日志内容
     * $param string $typeLog 日志类型
     * $param string $dirLog 日志目录
     * */
    public static function writeLog($contentlog='',$typeLog='',$dirLog='ebapi')
    {
        Log::init([
            'type'   => 'File',
            'path'   => LOG_PATH.($dirLog ? $dirLog.'/' : '')
        ]);
        if($contentlog==='') $contentlog=self::$logInfo;
        if($contentlog===null) return false;
        if(is_array($contentlog)) $contentlog=var_export($contentlog,true);
        if(is_object($contentlog)) $contentlog=var_export($contentlog,true);
        switch (strtoupper($typeLog)){
            case 'SQL':case 'S':
                Log::sql($contentlog);
                break;
            case 'ERROR':case 'E':
                Log::error($contentlog);
                break;
            case 'INFO':case 'I':
                Log::info($contentlog);
                break;
            case 'NOTICE':case 'N':
                Log::notice($contentlog);
                break;
            case 'ALERT':case 'A':
                Log::alert($contentlog);
                break;
            case 'LOG':case 'L':
                Log::log($contentlog);
                break;
        }
    }

}