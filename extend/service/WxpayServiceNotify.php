<?php
namespace service;

/**
 * 微信扫码支付回调
 * Class WxpayServiceNotify
 * @package service
 *
 * 调用实例
 *
 * $mchid = SystemConfig::getValue('pay_weixin_mchid');          //微信支付商户号 PartnerID 通过微信支付商户资料审核后邮件发送
    $appid = SystemConfig::getValue('pay_weixin_appid');  //公众号APPID 通过微信支付商户资料审核后邮件发送
    $apiKey = SystemConfig::getValue('pay_weixin_key');   //https://pay.weixin.qq.com 帐户设置-安全设置-API安全-API密钥-设置API密钥
    $wxPay = new WxpayServiceNotify($mchid,$appid,$apiKey);
    $result = $wxPay->notify();
    if($result){
    $data['is_pay'] = 1;
    $data['pay_time'] = time();
    $res = ThemeEnlistModel::edit($data,$result['out_trade_no'],'order_id');
    if($res)
    echo 'success';
    //完成你的逻辑
    //例如连接数据库，获取付款金额$result['cash_fee']，获取订单号$result['out_trade_no']，修改数据库中的订单状态等;
    }else{
    echo 'pay error';
    }
 */

class WxpayServiceNotify
{
    protected $mchid;
    protected $appid;
    protected $apiKey;
    public function __construct($mchid, $appid, $key)
    {
        $this->mchid = $mchid;
        $this->appid = $appid;
        $this->apiKey = $key;
    }
    public function notify()
    {
        $config = array(
            'mch_id' => $this->mchid,
            'appid' => $this->appid,
            'key' => $this->apiKey,
        );
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($postObj === false) {
            die('parse xml error');
        }
        if ($postObj->return_code != 'SUCCESS') {
            die($postObj->return_msg);
        }
        if ($postObj->result_code != 'SUCCESS') {
            die($postObj->err_code);
        }
        $arr = (array)$postObj;
        unset($arr['sign']);
        if (self::getSign($arr, $config['key']) == $postObj->sign) {
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