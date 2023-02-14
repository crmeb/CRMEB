<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace crmeb\services\app;

use app\services\message\wechat\MessageServices;
use app\services\order\StoreOrderServices;
use app\services\wechat\WechatMessageServices;
use app\services\wechat\WechatReplyServices;
use crmeb\exceptions\AdminException;
use app\services\pay\PayNotifyServices;
use crmeb\exceptions\ApiException;
use crmeb\services\easywechat\Application;
use EasyWeChat\Message\Article;
use EasyWeChat\Message\Image;
use EasyWeChat\Message\Material;
use EasyWeChat\Message\News;
use EasyWeChat\Message\Text;
use EasyWeChat\Message\Video;
use EasyWeChat\Message\Voice;
use EasyWeChat\Payment\Order;
use EasyWeChat\Server\Guard;
use Symfony\Component\HttpFoundation\Request;
use think\facade\Event;
use think\facade\Log;
use think\Response;
use crmeb\utils\Hook;
use think\facade\Cache;
use crmeb\services\SystemConfigService;

/**
 * 微信公众号
 * Class WechatService
 * @package crmeb\services\app
 */
class WechatService
{
    /**
     * @var Application
     */
    protected static $instance;

    /**
     * @return array
     */
    public static function options()
    {
        $wechat = SystemConfigService::more(['wechat_appid', 'wechat_app_appid', 'wechat_app_appsecret', 'wechat_appsecret', 'wechat_token', 'wechat_encodingaeskey', 'wechat_encode']);
        $payment = SystemConfigService::more(['pay_weixin_mchid', 'pay_weixin_client_cert', 'pay_weixin_client_key', 'pay_weixin_key', 'pay_weixin_open']);
        if (request()->isApp()) {
            $appId = isset($wechat['wechat_app_appid']) ? trim($wechat['wechat_app_appid']) : '';
            $appsecret = isset($wechat['wechat_app_appsecret']) ? trim($wechat['wechat_app_appsecret']) : '';
        } else {
            $appId = isset($wechat['wechat_appid']) ? trim($wechat['wechat_appid']) : '';
            $appsecret = isset($wechat['wechat_appsecret']) ? trim($wechat['wechat_appsecret']) : '';
        }
        $config = [
            'app_id' => $appId,
            'secret' => $appsecret,
            'token' => isset($wechat['wechat_token']) ? trim($wechat['wechat_token']) : '',
            'guzzle' => [
                'timeout' => 10.0, // 超时时间（秒）
                'verify' => false
            ],
        ];
        if (isset($wechat['wechat_encode']) && (int)$wechat['wechat_encode'] > 0 && isset($wechat['wechat_encodingaeskey']) && !empty($wechat['wechat_encodingaeskey']))
            $config['aes_key'] = $wechat['wechat_encodingaeskey'];
        if (isset($payment['pay_weixin_open']) && $payment['pay_weixin_open'] == 1) {
            $config['payment'] = [
                'app_id' => $appId,
                'merchant_id' => trim($payment['pay_weixin_mchid']),
                'key' => trim($payment['pay_weixin_key']),
                'cert_path' => substr(public_path(parse_url($payment['pay_weixin_client_cert'])['path']), 0, strlen(public_path(parse_url($payment['pay_weixin_client_cert'])['path'])) - 1),
                'key_path' => substr(public_path(parse_url($payment['pay_weixin_client_key'])['path']), 0, strlen(public_path(parse_url($payment['pay_weixin_client_key'])['path'])) - 1),
                'notify_url' => trim(sys_config('site_url')) . '/api/wechat/notify'
            ];
        }
        return $config;
    }

    /**
     * @param bool $cache
     * @return Application
     */
    public static function application($cache = false)
    {
        (self::$instance === null || $cache === true) && (self::$instance = new Application(self::options()));
        return self::$instance;
    }

    /**
     * @return Response
     * @throws \EasyWeChat\Server\BadRequestException
     */
    public static function serve(): Response
    {
        $wechat = self::application(true);
        $server = $wechat->server;
        self::hook($server);
        $response = $server->serve();
        return response($response->getContent());
    }

    /**
     * 监听行为(微信)
     * @param Guard $server
     * @throws \EasyWeChat\Core\Exceptions\InvalidArgumentException
     */
    private static function hook($server)
    {
        /** @var MessageServices $messageService */
        $messageService = app()->make(MessageServices::class);
        /** @var WechatReplyServices $wechatReplyService */
        $wechatReplyService = app()->make(WechatReplyServices::class);
        $server->setMessageHandler(function ($message) use ($messageService, $wechatReplyService) {
            /** @var WechatMessageServices $wechatMessage */
            $wechatMessage = app()->make(WechatMessageServices::class);
            $wechatMessage->wechatMessageBefore($message);
            switch ($message->MsgType) {
                case 'event':
                    switch (strtolower($message->Event)) {
                        case 'subscribe':
                            $response = $messageService->wechatEventSubscribe($message);
                            break;
                        case 'unsubscribe':
                            $messageService->wechatEventUnsubscribe($message);
                            break;
                        case 'scan':
                            $response = $messageService->wechatEventScan($message);
                            break;
                        case 'location':
                            $response = $messageService->wechatEventLocation($message);
                            break;
                        case 'click':
                            $response = $wechatReplyService->reply($message->EventKey);
                            break;
                        case 'view':
                            $response = $messageService->wechatEventView($message);
                            break;
                        case 'funds_order_pay':
                            if (($count = strpos($message['order_info']['trade_no'], '_')) !== false) {
                                $trade_no = substr($message['order_info']['trade_no'], $count + 1);
                            } else {
                                $trade_no = $message['order_info']['trade_no'];
                            }
                            $prefix = substr($trade_no, 0, 2);
                            //处理一下参数
                            switch ($prefix) {
                                case 'cp':
                                    $data['attach'] = 'Product';
                                    break;
                                case 'hy':
                                    $data['attach'] = 'Member';
                                    break;
                                case 'cz':
                                    $data['attach'] = 'UserRecharge';
                                    break;
                            }
                            $data['out_trade_no'] = $message['order_info']['trade_no'];
                            $data['transaction_id'] = $message['order_info']['transaction_id'];
                            $data['opneid'] = $message['FromUserName'];
                            if (Event::until('pay.notify', [$data])) {
                                $response = 'success';
                            } else {
                                $response = 'faild';
                            }
                            Log::error(['data' => $data, 'res' => $response, 'message' => $message]);
                            break;
                    }
                    break;
                case 'text':
                    $response = $wechatReplyService->reply($message->Content, $message->FromUserName);
                    break;
                case 'image':
                    $response = $messageService->wechatMessageImage($message);
                    break;
                case 'voice':
                    $response = $messageService->wechatMessageVoice($message);
                    break;
                case 'video':
                    $response = $messageService->wechatMessageVideo($message);
                    break;
                case 'location':
                    $response = $messageService->wechatMessageLocation($message);
                    break;
                case 'link':
                    $response = $messageService->wechatMessageLink($message);
                    break;
                // ... 其它消息
                default:
                    $response = $messageService->wechatMessageOther($message);
                    break;
            }

            return $response ?? false;
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
     * 网页授权
     * @return easywechat\oauth2\wechat\WechatOauth2Provider
     */
    public static function oauth2Service()
    {
        $request = app()->request;
        self::application()->oauth2->setRequest(new Request($request->get(), $request->post(), [], [], [], $request->server(), $request->getContent()));
        return self::application()->oauth2;
    }

    /**
     * 模板消息接口
     * @return \EasyWeChat\Notice\Notice
     */
    public static function noticeService()
    {
        return self::application()->notice;
    }

    public static function sendTemplate($openid, $templateId, array $data, $url = null, $defaultColor = null)
    {
        $notice = self::noticeService()->to($openid)->template($templateId)->andData($data);
        if ($url !== null) $notice->url($url);
        if ($defaultColor !== null) $notice->defaultColor($defaultColor);
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

    public static function downloadBill($day, $type = 'ALL')
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
     * 企业付款到零钱
     * @param string $openid openid
     * @param string $orderId 订单号
     * @param string $amount 金额
     * @param string $desc 说明
     */
    public static function merchantPay(string $openid, string $orderId, string $amount, string $desc)
    {
        $options = self::options();
        if (!isset($options['payment']['cert_path'])) {
            throw new ApiException(410088);
        }
        if (!$options['payment']['cert_path']) {
            throw new ApiException(410088);
        }
        $merchantPayData = [
            'partner_trade_no' => $orderId, //随机字符串作为订单号，跟红包和支付一个概念。
            'openid' => $openid, //收款人的openid
            'check_name' => 'NO_CHECK',  //文档中有三种校验实名的方法 NO_CHECK OPTION_CHECK FORCE_CHECK
            'amount' => (int)bcmul($amount, '100', 0),  //单位为分
            'desc' => $desc,
            'spbill_create_ip' => request()->ip(),  //发起交易的IP地址
        ];
        $result = self::application()->merchant_pay->send($merchantPayData);
        if ($result->return_code == 'SUCCESS' && $result->result_code != 'FAIL') {
            return true;
        } else {
            throw new ApiException(410089);
        }
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
    protected static function paymentOrder($openid, $out_trade_no, $total_fee, $attach, $body, $detail = '', $trade_type = 'JSAPI', $options = [])
    {
        $total_fee = bcmul($total_fee, 100, 0);
        $order = array_merge(compact('out_trade_no', 'total_fee', 'attach', 'body', 'detail', 'trade_type'), $options);
        if (!is_null($openid)) $order['openid'] = $openid;
        if ($order['detail'] == '') unset($order['detail']);
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
    public static function paymentPrepare($openid, $out_trade_no, $total_fee, $attach, $body, $detail = '', $trade_type = 'JSAPI', $options = [])
    {
        $key = 'pay_' . $out_trade_no;
        $result = Cache::get($key);
        if ($result) {
            return $result;
        } else {
            $order = self::paymentOrder($openid, $out_trade_no, $total_fee, $attach, $body, $detail, $trade_type, $options);
            $result = self::paymentService()->prepare($order);
            if ($result->return_code == 'SUCCESS' && $result->result_code == 'SUCCESS') {
                Cache::set($key, $result, 7000);
                return $result;
            } else {
                if ($result->return_code == 'FAIL') {
                    exception('微信支付错误返回：' . $result->return_msg);
                } else if (isset($result->err_code)) {
                    exception('微信支付错误返回：' . $result->err_code_des);
                } else {
                    exception('没有获取微信支付的预支付ID，请重新发起支付!');
                }
                exit;
            }
        }
    }

    /**
     * 获得下单ID 新小程序支付
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
    public static function newPaymentPrepare($openid, $out_trade_no, $total_fee, $attach, $body, $detail = '', $options = [])
    {
        $key = 'pay_' . $out_trade_no;
        $result = Cache::get($key);
        if ($result) {
            return $result;
        } else {
            $order = self::paymentOrder($openid, $out_trade_no, $total_fee, $attach, $body, $detail, $options);
            $result = self::application()->minipay->createorder($order);
            if ($result->return_code == 'SUCCESS' && $result->result_code == 'SUCCESS') {
                Cache::set($key, $result, 7000);
                return $result;
            } else {
                if ($result->return_code == 'FAIL') {
                    exception('微信支付错误返回：' . $result->return_msg);
                } else if (isset($result->err_code)) {
                    exception('微信支付错误返回：' . $result->err_code_des);
                } else {
                    exception('没有获取微信支付的预支付ID，请重新发起支付!');
                }
                exit;
            }
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
    public static function jsPay($openid, $out_trade_no, $total_fee, $attach, $body, $detail = '', $trade_type = 'JSAPI', $options = [])
    {
        $paymentPrepare = self::paymentPrepare($openid, $out_trade_no, $total_fee, $attach, $body, $detail, $trade_type, $options);
        return self::paymentService()->configForJSSDKPayment($paymentPrepare->prepay_id);
    }

    /**
     * 获得jsSdk支付参数  新小程序支付
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
    public static function newJsPay($openid, $out_trade_no, $total_fee, $attach, $body, $detail = '', $options = [])
    {
        $paymentPrepare = self::newPaymentPrepare($openid, $out_trade_no, $total_fee, $attach, $body, $detail, $options);
        return self::paymentService()->configForJSSDKPayment($paymentPrepare->prepay_id);
    }

    /**
     * 获得APP付参数
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
    public static function appPay($openid, $out_trade_no, $total_fee, $attach, $body, $detail = '', $trade_type = Order::APP, $options = [])
    {
        $paymentPrepare = self::paymentPrepare($openid, $out_trade_no, $total_fee, $attach, $body, $detail, $trade_type, $options);
        return self::paymentService()->configForAppPayment($paymentPrepare->prepay_id);
    }

    /**
     * 获得native支付参数
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
    public static function nativePay($openid, $out_trade_no, $total_fee, $attach, $body, $detail = '', $trade_type = 'NATIVE', $options = [])
    {
        $data = self::paymentPrepare($openid, $out_trade_no, $total_fee, $attach, $body, $detail, $trade_type, $options);
        if ($data) {
            $res['code_url'] = $data['code_url'];
            $res['invalid'] = time() + 60;
            $res['logo'] = sys_config('wap_login_logo');
        } else $res = [];
        return $res;
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
    public static function refund($orderNo, $refundNo, $totalFee, $refundFee = null, $opUserId = null, $refundReason = '', $type = 'out_trade_no', $refundAccount = 'REFUND_SOURCE_UNSETTLED_FUNDS')
    {
        $totalFee = floatval($totalFee);
        $refundFee = floatval($refundFee);
        if ($type == 'out_trade_no') {
            return self::paymentService()->refund($orderNo, $refundNo, $totalFee, $refundFee, $opUserId, $type, $refundAccount, $refundReason);
        } else {
            return self::paymentService()->refundByTransactionId($orderNo, $refundNo, $totalFee, $refundFee, $opUserId, $refundAccount, $refundReason);
        }
    }


    public static function payOrderRefund($orderNo, array $opt)
    {
        if (!isset($opt['pay_price'])) throw new AdminException(400730);
        $totalFee = floatval(bcmul($opt['pay_price'], 100, 0));
        $refundFee = isset($opt['refund_price']) ? floatval(bcmul($opt['refund_price'], 100, 0)) : null;
        $refundReason = $opt['desc'] ?? '';
        $refundNo = $opt['refund_id'] ?? $orderNo;
        $opUserId = $opt['op_user_id'] ?? null;
        $type = $opt['type'] ?? 'out_trade_no';
        /*仅针对老资金流商户使用
        REFUND_SOURCE_UNSETTLED_FUNDS---未结算资金退款（默认使用未结算资金退款）
        REFUND_SOURCE_RECHARGE_FUNDS---可用余额退款*/
        $refundAccount = $opt['refund_account'] ?? 'REFUND_SOURCE_UNSETTLED_FUNDS';
        try {
            $res = (self::refund($orderNo, $refundNo, $totalFee, $refundFee, $opUserId, $refundReason, $type, $refundAccount));
            if ($res->return_code == 'FAIL') throw new AdminException(400731, ['msg' => $res->return_msg]);
            if (isset($res->err_code)) throw new AdminException(400731, ['msg' => $res->err_code_des]);
        } catch (\Exception $e) {
            throw new AdminException($e->getMessage());
        }
        return true;
    }

    /**
     * 微信支付成功回调接口
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \EasyWeChat\Core\Exceptions\FaultException
     */
    public static function handleNotify()
    {
        return self::paymentService()->handleNotify(function ($notify, $successful) {
            if ($successful && isset($notify->out_trade_no)) {
                if (isset($notify->attach) && $notify->attach) {
                    if (($count = strpos($notify->out_trade_no, '_')) !== false) {
                        $notify->out_trade_no = substr($notify->out_trade_no, $count + 1);
                    }
                    return (new Hook(PayNotifyServices::class, 'wechat'))->listen($notify->attach, $notify->out_trade_no, $notify->transaction_id);
                }
                /** @var WechatMessageServices $wechatMessageService */
                $wechatMessageService = app()->make(WechatMessageServices::class);
                $wechatMessageService->setOnceMessage($notify, $notify->openid, 'payment_success', $notify->out_trade_no);
                return false;
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

    /**
     * 获取js的SDK
     * @param string $url
     * @return array|string
     */
    public static function jsSdk($url = '')
    {
        $apiList = ['openAddress', 'updateTimelineShareData', 'updateAppMessageShareData', 'onMenuShareTimeline', 'onMenuShareAppMessage', 'onMenuShareQQ', 'onMenuShareWeibo', 'onMenuShareQZone', 'startRecord', 'stopRecord', 'onVoiceRecordEnd', 'playVoice', 'pauseVoice', 'stopVoice', 'onVoicePlayEnd', 'uploadVoice', 'downloadVoice', 'chooseImage', 'previewImage', 'uploadImage', 'downloadImage', 'translateVoice', 'getNetworkType', 'openLocation', 'getLocation', 'hideOptionMenu', 'showOptionMenu', 'hideMenuItems', 'showMenuItems', 'hideAllNonBaseMenuItem', 'showAllNonBaseMenuItem', 'closeWindow', 'scanQRCode', 'chooseWXPay', 'openProductSpecificView', 'addCard', 'chooseCard', 'openCard'];
        $jsService = self::jsService();
        if ($url) $jsService->setUrl($url);
        try {
            return $jsService->config($apiList);
        } catch (\Exception $e) {
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
        return new Video(compact('media_id', 'title', 'description', 'thumb_media_id'));
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
        if (is_array($title)) {
            if (isset($title[0]) && is_array($title[0])) {
                $newsList = [];
                foreach ($title as $news) {
                    $newsList[] = self::newsMessage($news);
                }
                return $newsList;
            } else {
                $data = $title;
            }
        } else {
            $data = compact('title', 'description', 'url', 'image');
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
        $data = is_array($title) ? $title : compact('title', 'thumb_media_id', 'source_url', 'content', 'author', 'digest', 'show_cover_pic', 'need_open_comment', 'only_fans_can_comment');
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
        return new Material($type, $media_id);
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
        $userInfo = [];
        try {
            if (is_array($openid)) {
                $res = $userService->batchGet($openid);
                if (isset($res['user_info_list'])) {
                    $userInfo = $res['user_info_list'];
                } else {
                    throw new AdminException(400732);
                }
            } else {
                $userInfo = $userService->get($openid);
            }
        } catch (\Throwable $e) {
            throw new AdminException(self::getMessage($e->getMessage()));
        }
        return $userInfo;
    }


    /**
     * 获取用户列表
     * @param null $next_openid
     * @return array
     */
    public static function getUsersList($next_openid = null)
    {
        $userService = self::userService();
        $list = [];
        try {
            $res = $userService->lists($next_openid);
            $list['data'] = $res['data']['openid'] ?? [];
            $list['next_openid'] = $res['next_openid'] ?? null;
            return $list;
        } catch (\Exception $e) {
            throw new AdminException(self::getMessage($e->getMessage()));
        }
        return $list;
    }

    /**
     * 处理返回错误信息友好提示
     * @param string $message
     * @return array|mixed|string
     */
    public static function getMessage(string $message)
    {
        if (strstr($message, 'Request AccessToken fail') !== false) {
            $message = str_replace('Request AccessToken fail. response:', '', $message);
            $message = json_decode($message, true) ?: [];
            $errcode = $message['errcode'] ?? false;
            if ($errcode) {
                $message = $errcode;
            }
        }
        return $message;
    }

    /**
     * 设置模版消息行业
     */
    public static function setIndustry($industryOne, $industryTwo)
    {
        return self::application()->notice->setIndustry($industryOne, $industryTwo);
    }

    /**
     * 获得添加模版ID
     * @param $template_id_short
     */
    public static function addTemplateId($template_id_short)
    {
        try {
            return self::application()->notice->addTemplate($template_id_short);
        } catch (\Exception $e) {
            throw new AdminException(self::getMessage($e->getMessage()));
        }
    }

    /**
     * 获取模板列表
     * @return \EasyWeChat\Support\Collection
     */
    public static function getPrivateTemplates()
    {
        try {
            return self::application()->notice->getPrivateTemplates();
        } catch (\Exception $e) {
            throw new AdminException(self::getMessage($e->getMessage()));
        }
    }

    /*
     * 根据模版ID删除模版
     */
    public static function deleleTemplate($template_id)
    {
        try {
            return self::application()->notice->deletePrivateTemplate($template_id);
        } catch (\Exception $e) {
            throw new AdminException(self::getMessage($e->getMessage()));
        }

    }
}
