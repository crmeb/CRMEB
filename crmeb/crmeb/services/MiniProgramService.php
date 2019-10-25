<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/23
 */

namespace crmeb\services;

use crmeb\repositories\PaymentRepositories;
use EasyWeChat\Foundation\Application;
use EasyWeChat\Payment\Order;
use think\facade\Route as Url;
use crmeb\services\HookService;
use crmeb\interfaces\ProviderInterface;
use app\models\store\StoreOrder as StoreOrderRoutineModel;
use app\models\user\UserRecharge;

/**微信小程序接口
 * Class WechatMinService
 * @package service
 */
class MiniProgramService implements ProviderInterface
{
    private static $instance = null;

    public function register($config)
    {
        return ['mini_program',new self()];
    }

    public static function options()
    {
        $wechat = SystemConfigService::more(['site_url','routine_appId','routine_appsecret']);
        $payment = SystemConfigService::more(['pay_routine_mchid','pay_routine_key','pay_routine_client_cert','pay_routine_client_key','pay_weixin_open']);
        $config = [];
        $config['mini_program'] = [
            'app_id'=>isset($wechat['routine_appId']) ? trim($wechat['routine_appId']):'',
            'secret'=>isset($wechat['routine_appsecret']) ? trim($wechat['routine_appsecret']):'',
            'token'=>isset($wechat['wechat_token']) ? trim($wechat['wechat_token']):'',
            'aes_key'=> isset($wechat['wechat_encodingaeskey']) ? trim($wechat['wechat_encodingaeskey']):''
        ];
        $config['payment'] = [
            'app_id'=>isset($wechat['routine_appId']) ? trim($wechat['routine_appId']) :'',
            'merchant_id'=>trim($payment['pay_routine_mchid']),
            'key'=>trim($payment['pay_routine_key']),
            'cert_path'=>realpath('.'.$payment['pay_routine_client_cert']),
            'key_path'=>realpath('.'.$payment['pay_routine_client_key']),
            'notify_url'=>$wechat['site_url'].Url::buildUrl('/api/routine/notify')
        ];
        return $config;
    }
    public static function application($cache = false)
    {
        (self::$instance === null || $cache === true) && (self::$instance = new Application(self::options()));
        return self::$instance;
    }
    /**
     * 小程序接口
     * @return \EasyWeChat\MiniProgram\MiniProgram
     */
    public static function miniprogram()
    {
        return self::application()->mini_program;
    }

    /**
     * 获得用户信息 根据code 获取session_key
     * @param array|string $openid
     * @return $userInfo
     */
    public static function getUserInfo($code)
    {
        $userInfo = self::miniprogram()->sns->getSessionKey($code);
        return $userInfo;
    }

    /**
     * 加密数据解密
     * @param $sessionKey
     * @param $iv
     * @param $encryptData
     * @return $userInfo
     */
    public static function encryptor($sessionKey, $iv, $encryptData){
        return self::miniprogram()->encryptor->decryptData($sessionKey, $iv, $encryptData);
    }

    /**
     * 上传临时素材接口
     * @return \EasyWeChat\Material\Temporary
     */
    public static function materialTemporaryService()
    {
        return self::miniprogram()->material_temporary;
    }

    /**
     * 客服消息接口
     * @param null $to
     * @param null $message
     */
    public static function staffService()
    {
        return self::miniprogram()->staff;
    }

    /**
     * 微信小程序二维码生成接口
     * @return \EasyWeChat\QRCode\QRCode
     */
    public static function qrcodeService()
    {
        return self::miniprogram()->qrcode;
    }

    /**微信小程序二维码生成接口不限量永久
     * @param $scene
     * @param null $page
     * @param null $width
     * @param null $autoColor
     * @param array $lineColor
     * @return \Psr\Http\Message\StreamInterface
     */
    public static function appCodeUnlimitService($scene, $page = null, $width = 430, $autoColor = false, $lineColor = ['r' => 0, 'g' => 0, 'b' => 0])
    {
        return self::qrcodeService()->appCodeUnlimit($scene,$page,$width,$autoColor,$lineColor);
    }


    /**
     * 模板消息接口
     * @return \EasyWeChat\Notice\Notice
     */
    public static function noticeService()
    {
        return self::miniprogram()->notice;
    }

    /**发送小程序模版消息
     * @param $openid
     * @param $templateId
     * @param array $data
     * @param null $url
     * @param null $defaultColor
     * @return mixed
     */
    public static function sendTemplate($openid,$templateId,array $data,$form_id,$link = null,$defaultColor = null)
    {
        $notice = self::noticeService()->to($openid)->template($templateId)->formId($form_id)->andData($data);
        $message = [];
        if($link !== null) $message = ['page'=>$link];
        if($defaultColor !== null) $notice->defaultColor($defaultColor);
        return $notice->send($message);
    }


    /**
     * 支付
     * @return \EasyWeChat\Payment\Payment
     */
    public static function paymentService()
    {
        return self::application()->payment;
    }

    /**
     * 生成支付订单对象
     * @param $openid
     * @param $out_trade_no
     * @param $total_fee
     * @param $attach
     * @param $body
     * @param string $detail
     * @param string $trade_type
     * @param array $options
     * @return Order
     */
    protected static function paymentOrder($openid,$out_trade_no,$total_fee,$attach,$body,$detail='',$trade_type='JSAPI',$options = [])
    {
        $total_fee = bcmul($total_fee,100,0);
        $order = array_merge(compact('openid','out_trade_no','total_fee','attach','body','detail','trade_type'),$options);
        if($order['detail'] == '') unset($order['detail']);
        return new Order($order);
    }

    /**
     * 获得下单ID
     * @param $openid
     * @param $out_trade_no
     * @param $total_fee
     * @param $attach
     * @param $body
     * @param string $detail
     * @param string $trade_type
     * @param array $options
     * @return mixed
     */
    public static function paymentPrepare($openid, $out_trade_no, $total_fee, $attach, $body, $detail='', $trade_type='JSAPI', $options = [])
    {
        $order = self::paymentOrder($openid,$out_trade_no,$total_fee,$attach,$body,$detail,$trade_type,$options);
        $result = self::paymentService()->prepare($order);
        if ($result->return_code == 'SUCCESS' && $result->result_code == 'SUCCESS'){
            try{
                PaymentRepositories::wechatPaymentPrepareProgram($order, $result->prepay_id);
            }catch (\Exception $e){}
            return $result->prepay_id;
        }else{
            if($result->return_code == 'FAIL'){
                exception('微信支付错误返回：'.$result->return_msg);
            }else if(isset($result->err_code)){
                exception('微信支付错误返回：'.$result->err_code_des);
            }else{
                exception('没有获取微信支付的预支付ID，请重新发起支付!');
            }
            exit;
        }

    }

    /**
     * 获得jsSdk支付参数
     * @param $openid
     * @param $out_trade_no
     * @param $total_fee
     * @param $attach
     * @param $body
     * @param string $detail
     * @param string $trade_type
     * @param array $options
     * @return array|string
     */
    public static function jsPay($openid, $out_trade_no, $total_fee, $attach, $body, $detail='', $trade_type='JSAPI', $options = [])
    {
        return self::paymentService()->configForJSSDKPayment(self::paymentPrepare($openid,$out_trade_no,$total_fee,$attach,$body,$detail,$trade_type,$options));
    }

    /**
     * 使用商户订单号退款
     * @param $orderNo
     * @param $refundNo
     * @param $totalFee
     * @param null $refundFee
     * @param null $opUserId
     * @param string $refundReason
     * @param string $type
     * @param string $refundAccount
     */
    public static function refund($orderNo, $refundNo, $totalFee, $refundFee = null, $opUserId = null, $refundReason = '' , $type = 'out_trade_no', $refundAccount = 'REFUND_SOURCE_UNSETTLED_FUNDS')
    {
        $totalFee = floatval($totalFee);
        $refundFee = floatval($refundFee);
        return self::paymentService()->refund($orderNo,$refundNo,$totalFee,$refundFee,$opUserId,$type,$refundAccount,$refundReason);
    }

    /** 根据订单号退款
     * @param $orderNo
     * @param array $opt
     * @return bool
     */
    public static function payOrderRefund($orderNo, array $opt)
    {
        if(!isset($opt['pay_price'])) exception('缺少pay_price');
        $totalFee = floatval(bcmul($opt['pay_price'],100,0));
        $refundFee = isset($opt['refund_price']) ? floatval(bcmul($opt['refund_price'],100,0)) : null;
        $refundReason = isset($opt['desc']) ? $opt['desc'] : '';
        $refundNo = isset($opt['refund_id']) ? $opt['refund_id'] : $orderNo;
        $opUserId = isset($opt['op_user_id']) ? $opt['op_user_id'] : null;
        $type = isset($opt['type']) ? $opt['type'] : 'out_trade_no';
        /*仅针对老资金流商户使用
        REFUND_SOURCE_UNSETTLED_FUNDS---未结算资金退款（默认使用未结算资金退款）
        REFUND_SOURCE_RECHARGE_FUNDS---可用余额退款*/
        $refundAccount = isset($opt['refund_account']) ? $opt['refund_account'] : 'REFUND_SOURCE_UNSETTLED_FUNDS';
        try{
            $res = (self::refund($orderNo,$refundNo,$totalFee,$refundFee,$opUserId,$refundReason,$type,$refundAccount));
            if($res->return_code == 'FAIL') exception('退款失败:'.$res->return_msg);
            if(isset($res->err_code)) exception('退款失败:'.$res->err_code_des);
        }catch (\Exception $e){
            exception($e->getMessage());
        }
        return true;
    }

    /**
     * 微信支付成功回调接口
     */
    public static function handleNotify()
    {
        self::paymentService()->handleNotify(function($notify, $successful){
            if($successful && isset($notify->out_trade_no)){
                if(isset($notify->attach) && $notify->attach){
                    if(strtolower($notify->attach) == 'productr'){//TODO 商品订单支付成功后
                        try{
                            if(StoreOrderRoutineModel::be(['order_id'=>$notify->out_trade_no,'paid'=>1])) return true;
                            return StoreOrderRoutineModel::paySuccess($notify->out_trade_no);
                        }catch (\Exception $e){
                            return false;
                        }
                    }else if(strtolower($notify->attach) == 'user_recharge'){
                        try{
                            if(UserRecharge::be(['order_id'=>$notify->out_trade_no,'paid'=>1])) return true;
                            return UserRecharge::rechargeSuccess($notify->out_trade_no);
                        }catch (\Exception $e){
                            return false;
                        }
                    }
                }
                return false;
            }
        });
    }

    /**
     * 作为客服消息发送
     * @param $to
     * @param $message
     * @return bool
     */
    public static function staffTo($to, $message)
    {
        $staff = self::staffService();
        $staff = is_callable($message) ? $staff->message($message()) : $staff->message($message);
        $res = $staff->to($to)->send();
        HookService::afterListen('wechat_staff_to',compact('to','message'),$res);
        return $res;
    }




}