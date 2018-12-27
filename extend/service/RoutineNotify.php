<?php
namespace service;

/**
 * 小程序支付异步通知
 * Class RoutineNotify
 * @package service
 */
class RoutineNotify
{
    public static function options(){
        $payment = SystemConfigService::more(['routine_appId','routine_appsecret','pay_routine_mchid','pay_routine_key']);
        return $payment;
    }
    public static function notify()
    {
        $config = self::options();
        $postStr = file_get_contents('php://input');
        $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($postObj === false) die('parse xml error');
        if ($postObj->return_code != 'SUCCESS') die($postObj->return_msg);
        if ($postObj->result_code != 'SUCCESS') die($postObj->err_code);
        $arr = (array)$postObj;
        unset($arr['sign']);
        if (self::getSign($arr, $config['pay_routine_key']) == $postObj->sign) {
            echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
            return $arr;
        }
    }
    /**
     * 获取签名
     */
    public static function getSign($params, $key)
    {
        ksort($params, SORT_STRING);
        $unSignParaString = self::formatQueryParaMap($params, false);
        $signStr = strtoupper(md5($unSignParaString . "&key=" . $key));
        return $signStr;
    }
    protected static function formatQueryParaMap($paraMap, $urlEncode = false)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if (null != $v && "null" != $v) {
                if ($urlEncode) {
                    $v = urlencode($v);
                }
                $buff .= $k . "=" . $v . "&";
            }
        }
        $reqPar = '';
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }
}