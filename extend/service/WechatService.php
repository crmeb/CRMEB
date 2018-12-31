<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/23
 */

namespace service;

use app\admin\model\wechat\WechatMessage;
use behavior\wechat\MessageBehavior;
use behavior\wechat\PaymentBehavior;
use EasyWeChat\Foundation\Application;
use EasyWeChat\Message\Article;
use EasyWeChat\Message\Image;
use EasyWeChat\Message\Material;
use EasyWeChat\Message\News;
use EasyWeChat\Message\Text;
use EasyWeChat\Message\Video;
use EasyWeChat\Message\Voice;
use EasyWeChat\Payment\Order;
use EasyWeChat\Server\Guard;
use EasyWeChat\Support\XML;
use think\Url;

class WechatService
{
    private static $instance = null;

    public static function options()
    {
        $wechat = SystemConfigService::more(['wechat_appid','wechat_appsecret','wechat_token','wechat_encodingaeskey','wechat_encode']);
        $payment = SystemConfigService::more(['pay_weixin_mchid','pay_weixin_client_cert','pay_weixin_client_key','pay_weixin_key','pay_weixin_open']);
        $config = [
            'app_id'=>isset($wechat['wechat_appid']) ? $wechat['wechat_appid']:'',
            'secret'=>isset($wechat['wechat_appsecret']) ? $wechat['wechat_appsecret']:'',
            'token'=>isset($wechat['wechat_token']) ? $wechat['wechat_token']:'',
            'guzzle' => [
                'timeout' => 10.0, // 超时时间（秒）
            ],
        ];
        if((int)$wechat['wechat_encode']>0 && isset($wechat['wechat_encodingaeskey']) && !empty($wechat['wechat_encodingaeskey']))
            $config['aes_key'] =  $wechat['wechat_encodingaeskey'];
        if(isset($payment['pay_weixin_open']) && $payment['pay_weixin_open'] == 1){
            $config['payment'] = [
                'merchant_id'=>$payment['pay_weixin_mchid'],
                'key'=>$payment['pay_weixin_key'],
                'cert_path'=>realpath('.'.$payment['pay_weixin_client_cert']),
                'key_path'=>realpath('.'.$payment['pay_weixin_client_key']),
                'notify_url'=>SystemConfigService::get('site_url').Url::build('wap/Wechat/notify')
            ];
        }
        return $config;
    }



    public static function application($cache = false)
    {
        (self::$instance === null || $cache === true) && (self::$instance = new Application(self::options()));
        return self::$instance;
    }

    public static function serve()
    {
        $wechat = self::application(true);
        $server = $wechat->server;
        self::hook($server);
        $response = $server->serve();
        exit($response->getContent());
    }

    /**
     * 监听行为
     * @param Guard $server
     */
    private static function hook($server)
    {
        $server->setMessageHandler(function($message){
            $behavior = MessageBehavior::class;
            HookService::beforeListen('wechat_message',$message,null,true,$behavior);
            switch ($message->MsgType){
                case 'event':
                    switch (strtolower($message->Event)){
                        case 'subscribe':
                            if(isset($message->EventKey)){
                                $response = HookService::resultListen('wechat_event_scan_subscribe',$message,$message->EventKey,true,$behavior);
                            }else{
                                $response = HookService::resultListen('wechat_event_subscribe',$message,null,true,$behavior);
                            }
                            break;
                        case 'unsubscribe':
                            $response = HookService::resultListen('wechat_event_unsubscribe',$message,null,true,$behavior);
                            break;
                        case 'scan':
                            $response = HookService::resultListen('wechat_event_scan',$message,$message->EventKey,true,$behavior);
                            break;
                        case 'location':
                            $response = HookService::resultListen('wechat_event_location',$message,null,true,$behavior);
                            break;
                        case 'click':
                            $response = HookService::resultListen('wechat_event_click',$message,null,true,$behavior);
                            break;
                        case 'view':
                            $response = HookService::resultListen('wechat_event_view',$message,null,true,$behavior);
                            break;
                    }
                    break;
                case 'text':
                    $response = HookService::resultListen('wechat_message_text',$message,null,true,$behavior);
                    break;
                case 'image':
                    $response = HookService::resultListen('wechat_message_image',$message,null,true,$behavior);
                    break;
                case 'voice':
                    $response = HookService::resultListen('wechat_message_voice',$message,null,true,$behavior);
                    break;
                case 'video':
                    $response = HookService::resultListen('wechat_message_video',$message,null,true,$behavior);
                    break;
                case 'location':
                    $response = HookService::resultListen('wechat_message_location',$message,null,true,$behavior);
                    break;
                case 'link':
                    $response = HookService::resultListen('wechat_message_link',$message,null,true,$behavior);
                    break;
                // ... 其它消息
                default:
                    $response = HookService::resultListen('wechat_message_other',$message,null,true,$behavior);
                    break;
            }
            
            return $response;
        });
    }


    /**
     * 多客服消息转发
     * @param string $account
     * @return \EasyWeChat\Message\Transfer
     */
    public static function transfer($account = '')
    {
        $transfer = new \EasyWeChat\Message\Transfer();
        return empty($account) ? $transfer : $transfer->to($account);
    }


    /**
     * 上传永久素材接口
     * @return \EasyWeChat\Material\Material
     */
    public static function materialService()
    {
        return self::application()->material;
    }

    /**
     * 上传临时素材接口
     * @return \EasyWeChat\Material\Temporary
     */
    public static function materialTemporaryService()
    {
        return self::application()->material_temporary;
    }

    /**
     * 用户接口
     * @return \EasyWeChat\User\User
     */
    public static function userService()
    {
        return self::application()->user;
    }


    /**
     * 客服消息接口
     * @param null $to
     * @param null $message
     */
    public static function staffService()
    {
        return self::application()->staff;
    }

    /**
     * 微信公众号菜单接口
     * @return \EasyWeChat\Menu\Menu
     */
    public static function menuService()
    {
        return self::application()->menu;
    }

    /**
     * 微信二维码生成接口
     * @return \EasyWeChat\QRCode\QRCode
     */
    public static function qrcodeService()
    {
        return self::application()->qrcode;
    }

    /**
     * 短链接生成接口
     * @return \EasyWeChat\Url\Url
     */
    public static function urlService()
    {
        return self::application()->url;
    }

    /**
     * 用户授权
     * @return \Overtrue\Socialite\Providers\WeChatProvider
     */
    public static function oauthService()
    {
        return self::application()->oauth;
    }

    /**
     * 模板消息接口
     * @return \EasyWeChat\Notice\Notice
     */
    public static function noticeService()
    {
        return self::application()->notice;
    }

    public static function sendTemplate($openid,$templateId,array $data,$url = null,$defaultColor = null)
    {
        $notice = self::noticeService()->to($openid)->template($templateId)->andData($data);
        if($url !== null) $notice->url($url);
        if($defaultColor !== null) $notice->defaultColor($defaultColor);
        return $notice->send();
    }


    /**
     * 支付
     * @return \EasyWeChat\Payment\Payment
     */
    public static function paymentService()
    {
        return self::application()->payment;
    }

    public static function downloadBill($day,$type = 'ALL')
    {
//        $payment = self::paymentService();
//        $merchant = $payment->getMerchant();
//        $params = [
//            'appid' => $merchant->app_id,
//            'bill_date'=>$day,
//            'bill_type'=>strtoupper($type),
//            'mch_id'=> $merchant->merchant_id,
//            'nonce_str' => uniqid()
//        ];
//        $params['sign'] = \EasyWeChat\Payment\generate_sign($params, $merchant->key, 'md5');
//        $xml = XML::build($params);
//        dump(self::paymentService()->downloadBill($day)->getContents());
//        dump($payment->getHttp()->request('https://api.mch.weixin.qq.com/pay/downloadbill','POST',[
//            'body' => $xml,
//            'stream'=>true
//        ])->getBody()->getContents());
    }

    public static function userTagService()
    {
        return self::application()->user_tag;
    }

    public static function userGroupService()
    {
        return self::application()->user_group;
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
                HookService::listen('wechat_payment_prepare',$order,$result->prepay_id,false,PaymentBehavior::class);
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
                WechatMessage::setOnceMessage($notify,$notify->openid,'payment_success',$notify->out_trade_no);
                return HookService::listen('wechat_pay_success',$notify,null,true,PaymentBehavior::class);
            }
        });
    }

    /**
     * jsSdk
     * @return \EasyWeChat\Js\Js
     */
    public static function jsService()
    {
        return self::application()->js;
    }

    public static function jsSdk($url = '')
    {
        $apiList = ['onMenuShareTimeline', 'onMenuShareAppMessage', 'onMenuShareQQ', 'onMenuShareWeibo', 'onMenuShareQZone', 'startRecord', 'stopRecord', 'onVoiceRecordEnd', 'playVoice', 'pauseVoice', 'stopVoice', 'onVoicePlayEnd', 'uploadVoice', 'downloadVoice', 'chooseImage', 'previewImage', 'uploadImage', 'downloadImage', 'translateVoice', 'getNetworkType', 'openLocation', 'getLocation', 'hideOptionMenu', 'showOptionMenu', 'hideMenuItems', 'showMenuItems', 'hideAllNonBaseMenuItem', 'showAllNonBaseMenuItem', 'closeWindow', 'scanQRCode', 'chooseWXPay', 'openProductSpecificView', 'addCard', 'chooseCard', 'openCard'];
        $jsService = self::jsService();
        if($url) $jsService->setUrl($url);
        try{
            return $jsService->config($apiList);
        }catch (\Exception $e){
            return '{}';
        }

    }


    /**
     * 回复文本消息
     * @param string $content 文本内容
     * @return Text
     */
    public static function textMessage($content)
    {
        return new Text(compact('content'));
    }

    /**
     * 回复图片消息
     * @param string $media_id 媒体资源 ID
     * @return Image
     */
    public static function imageMessage($media_id)
    {
        return new Image(compact('media_id'));
    }

    /**
     * 回复视频消息
     * @param string $media_id 媒体资源 ID
     * @param string $title 标题
     * @param string $description 描述
     * @param null $thumb_media_id 封面资源 ID
     * @return Video
     */
    public static function videoMessage($media_id, $title = '', $description = '...', $thumb_media_id = null)
    {
        return new Video(compact('media_id','title','description','thumb_media_id'));
    }

    /**
     * 回复声音消息
     * @param string $media_id 媒体资源 ID
     * @return Voice
     */
    public static function voiceMessage($media_id)
    {
        return new Voice(compact('media_id'));
    }

    /**
     * 回复图文消息
     * @param string|array $title 标题
     * @param string $description 描述
     * @param string $url URL
     * @param string $image 图片链接
     */
    public static function newsMessage($title, $description = '...', $url = '', $image = '')
    {
        if(is_array($title)){
            if(isset($title[0]) && is_array($title[0])){
                $newsList = [];
                foreach ($title as $news){
                    $newsList[] = self::newsMessage($news);
                }
                return $newsList;
            }else{
                $data = $title;
            }
        }else{
            $data = compact('title','description','url','image');
        }
        return new News($data);
    }

    /**
     * 回复文章消息
     * @param string|array $title 标题
     * @param string $thumb_media_id 图文消息的封面图片素材id（必须是永久 media_ID）
     * @param string $source_url 图文消息的原文地址，即点击“阅读原文”后的URL
     * @param string $content 图文消息的具体内容，支持HTML标签，必须少于2万字符，小于1M，且此处会去除JS
     * @param string $author 作者
     * @param string $digest 图文消息的摘要，仅有单图文消息才有摘要，多图文此处为空
     * @param int $show_cover_pic 是否显示封面，0为false，即不显示，1为true，即显示
     * @param int $need_open_comment 是否打开评论，0不打开，1打开
     * @param int $only_fans_can_comment 是否粉丝才可评论，0所有人可评论，1粉丝才可评论
     * @return Article
     */
    public static function articleMessage($title, $thumb_media_id, $source_url, $content = '', $author = '', $digest = '', $show_cover_pic = 0, $need_open_comment = 0, $only_fans_can_comment = 1)
    {
        $data = is_array($title) ? $title : compact('title','thumb_media_id','source_url','content','author','digest','show_cover_pic','need_open_comment','only_fans_can_comment');
        return new Article($data);
    }

    /**
     * 回复素材消息
     * @param string $type [mpnews、 mpvideo、voice、image]
     * @param string $media_id 素材 ID
     * @return Material
     */
    public static function materialMessage($type, $media_id)
    {
        return new Material($type,$media_id);
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

    /**
     * 获得用户信息
     * @param array|string $openid
     * @return \EasyWeChat\Support\Collection
     */
    public static function getUserInfo($openid)
    {
        $userService = self::userService();
        $userInfo = is_array($openid) ? $userService->batchGet($openid) : $userService->get($openid);
        return $userInfo;
    }



}