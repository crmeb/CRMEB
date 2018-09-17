<?php
namespace service;
use think\Url;

/**
 * 小程序支付
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/29 0029
 * Time: 上午 10:10
 */
class RoutineService{

    public static function options(){
        $payment = SystemConfigService::more(['site_url','routine_appId','routine_appsecret','pay_routine_mchid','pay_routine_client_cert','pay_routine_client_key','pay_routine_key','pay_weixin_open']);
        return $payment;
    }

    /**
     * @param $openid $openid   用户openid
     * @param $fee  $fee 金额
     * @param $out_trade_no $out_trade_no  订单号
     * @param $body $body  提示
     * @return mixed
     */
    public static function payRoutine($openid,$out_trade_no,$fee,$attach,$body){
        $appid =        self::options()['routine_appId'] ? self::options()['routine_appId'] : '';//如果是公众号 就是公众号的appid
        $mch_id =       self::options()['pay_routine_mchid'] ? self::options()['pay_routine_mchid'] : '';
        $nonce_str =    self::nonce_str();//随机字符串
        $spbill_create_ip = self::get_server_ip();
        $total_fee =    $fee*100;//因为充值金额最小是1 而且单位为分 如果是充值1元所以这里需要*100
        $trade_type = 'JSAPI';//交易类型 默认
        $notify_url = SystemConfigService::get('site_url').Url::build('routine/Routine/notify');
        $post['appid'] = $appid;
        $post['attach'] = $attach;
        $post['body'] = $body;
        $post['mch_id'] = $mch_id;
        $post['nonce_str'] = $nonce_str;//随机字符串
        $post['notify_url'] = $notify_url;
        $post['openid'] = $openid;
        $post['out_trade_no'] = $out_trade_no;
        $post['spbill_create_ip'] = $spbill_create_ip;//终端的ip
        $post['total_fee'] = $total_fee;//总金额
        $post['trade_type'] = $trade_type;
        $sign = self::sign($post);//签名
        $post_xml = '<xml>
           <appid>'.$appid.'</appid>
           <attach>'.$attach.'</attach>
           <body>'.$body.'</body>
           <mch_id>'.$mch_id.'</mch_id>
           <nonce_str>'.$nonce_str.'</nonce_str>
           <notify_url>'.$notify_url.'</notify_url>
           <openid>'.$openid.'</openid>
           <out_trade_no>'.$out_trade_no.'</out_trade_no>
           <spbill_create_ip>'.$spbill_create_ip.'</spbill_create_ip>
           <total_fee>'.$total_fee.'</total_fee>
           <trade_type>'.$trade_type.'</trade_type>
           <sign>'.$sign.'</sign>
        </xml> ';
//        dump($post_xml);
        //统一接口prepay_id
        $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
        $xml = self::http_request($url,$post_xml);
        $array = self::xml($xml);//全要大写
        if($array['RETURN_CODE'] == 'SUCCESS' && $array['RESULT_CODE'] == 'SUCCESS'){
            $time = time();
            $tmp='';//临时数组用于签名
            $tmp['appId'] = $appid;
            $tmp['nonceStr'] = $nonce_str;
            $tmp['package'] = 'prepay_id='.$array['PREPAY_ID'];
            $tmp['signType'] = 'MD5';
            $tmp['timeStamp'] = "$time";

            $data['state'] = 1;
            $data['timeStamp'] = "$time";//时间戳
            $data['nonceStr'] = $nonce_str;//随机字符串
            $data['signType'] = 'MD5';//签名算法，暂支持 MD5
            $data['package'] = 'prepay_id='.$array['PREPAY_ID'];//统一下单接口返回的 prepay_id 参数值，提交格式如：prepay_id=*
            $data['paySign'] = self::sign($tmp);//签名,具体签名方案参见微信公众号支付帮助文档;
            $data['out_trade_no'] = $out_trade_no;
        }else{
            $data['state'] = 0;
            $data['text'] = "错误";
            $data['RETURN_CODE'] = $array['RETURN_CODE'];
            $data['RETURN_MSG'] = $array['RETURN_MSG'];
        }
        return $data;
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

    public static function order_number($openid){
        return md5($openid.time().rand(10,99));//32位
    }

    //签名 $data要先排好顺序
    public static function sign($data){
        $stringA = '';
        foreach ($data as $key=>$value){
            if(!$value) continue;
            if($stringA) $stringA .= '&'.$key."=".$value;
            else $stringA = $key."=".$value;
        }
        $wx_key = self::options()['pay_routine_key'] ? self::options()['pay_routine_key'] : '';//申请支付后有给予一个商户账号和密码，登陆后自己设置key
        $stringSignTemp = $stringA.'&key='.$wx_key;//申请支付后有给予一个商户账号和密码，登陆后自己设置key
//        dump($stringSignTemp);
        return strtoupper(md5($stringSignTemp));
    }

    //curl请求
    public static function http_request($url,$data = null,$headers=array())
    {
        $curl = curl_init();
        if( count($headers) >= 1 ){
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($curl, CURLOPT_URL, $url);

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);

        if (!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }

    //获取xml
    public static function xml($xml){
        $p = xml_parser_create();
        xml_parse_into_struct($p, $xml, $vals, $index);
        xml_parser_free($p);
        $data = "";
        foreach ($index as $key=>$value) {
            if($key == 'xml' || $key == 'XML') continue;
            $tag = $vals[$value[0]]['tag'];
            $value = $vals[$value[0]]['value'];
            $data[$tag] = $value;
        }
        return $data;
    }

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