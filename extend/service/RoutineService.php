<?php
namespace service;
use think\Url;
use service\HttpService;


/**
 * 小程序支付
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/29 0029
 * Time: 上午 10:10
 */
class RoutineService{
    /**
     * @param $openid $openid   用户openid
     * @param $fee  $fee 金额
     * @param $out_trade_no $out_trade_no  订单号
     * @param $body $body  提示
     * @return mixed
     */
    public static function payRoutine($openid,$out_trade_no,$fee,$attach,$body){
        $payment = SystemConfigService::more(['site_url','routine_appId','routine_appsecret','pay_routine_mchid','pay_routine_client_cert','pay_routine_client_key','pay_routine_key','pay_weixin_open']);
        $config = array(
            'appid' => $payment['routine_appId'],
            'mch_id' => $payment['pay_routine_mchid'],
            'key' => $payment['pay_routine_key'],
        );
        $unified = array(
            'appid' => $config['appid'],
            'attach' => $attach,             //商家数据包，原样返回，如果填写中文，请注意转换为utf-8
            'body' => $body,
            'mch_id' => $config['mch_id'],
            'nonce_str' => self::nonce_str(),//随机字符串
            'notify_url' => $payment['site_url'].Url::build('routine/Routine/notify'),
            'openid' => $openid,
            'out_trade_no' => $out_trade_no,
            'spbill_create_ip' => self::get_server_ip()?:'127.0.0.1',//终端的ip
            'total_fee' => $fee*100,       //单位 转为分
            'trade_type' => 'JSAPI'//交易类型 默认
        );
        $unified['sign'] = self::getSign($unified, $config['key']);//签名
        $responseXml = HttpService::postRequest('https://api.mch.weixin.qq.com/pay/unifiedorder', self::arrayToXml($unified));
        $unifiedOrder = simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($unifiedOrder === false) {
            die('parse xml error');
        }
        if ($unifiedOrder->return_code != 'SUCCESS') {
            die($unifiedOrder->return_msg);
        }
        if ($unifiedOrder->result_code != 'SUCCESS') {
            die($unifiedOrder->err_code);
        }
        $time = time();
        $arr = array(
            "appId" => $unifiedOrder->appid,
            "timeStamp" => "$time",
            "nonceStr" => self::nonce_str(),//随机字符串
            "package" => "prepay_id=" . $unifiedOrder->prepay_id,
            "signType" => 'MD5',
        );
        $arr['paySign'] = self::getSign($arr, $config['key']);
        return $arr;
    }

    //随机32位字符串
    public static function nonce_str(){
        $result = '';
        $str = 'QWERTYUIOPASDFGHJKLZXVBNMqwertyuioplkjhgfdsamnbvcxz';
        for ($i=0;$i<32;$i++){
            $result .= $str[rand(0,48)];
        }
        return $result;
    }
    //数组转XML
    public static function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
        }
        $xml .= "</xml>";
        return $xml;
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

    //获取IP
    public static function get_server_ip() {
        if (isset($_SERVER)) {
            if($_SERVER['SERVER_ADDR']) {
                $server_ip = $_SERVER['SERVER_ADDR'];
            }
            else {
                $server_ip = $_SERVER['LOCAL_ADDR'];
            }
        }
        else {
            $server_ip = getenv('SERVER_ADDR');
        }
        return $server_ip;
    }
}