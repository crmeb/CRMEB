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

use crmeb\exceptions\AdminException;
use crmeb\services\SystemConfigService;
use app\services\pay\PayNotifyServices;
use crmeb\services\easywechat\Application;
use EasyWeChat\Payment\Order;
use think\facade\Log;
use think\facade\Route as Url;
use crmeb\utils\Hook;
use think\facade\Cache;

/**
 * 微信小程序接口
 * Class WechatMinService
 * @package service
 */
class MiniProgramService
{
    const MSG_CODE = [
        '1' => '未创建直播间',
        '1003' => '商品id不存在',
        '47001' => '入参格式不符合规范',
        '200002' => '入参错误',
        '300001' => '禁止创建/更新商品 或 禁止编辑&更新房间',
        '300002' => '名称长度不符合规则',
        '300006' => '图片上传失败',
        '300022' => '此房间号不存在',
        '300023' => '房间状态 拦截',
        '300024' => '商品不存在',
        '300025' => '商品审核未通过',
        '300026' => '房间商品数量已经满额',
        '300027' => '导入商品失败',
        '300028' => '房间名称违规',
        '300029' => '主播昵称违规',
        '300030' => '主播微信号不合法',
        '300031' => '直播间封面图不合规',
        '300032' => '直播间分享图违规',
        '300033' => '添加商品超过直播间上限',
        '300034' => '主播微信昵称长度不符合要求',
        '300035' => '主播微信号不存在',
        '300036' => '主播微信号未实名认证',
        '300037' => '购物直播频道封面图不合规',
        '300038' => '未在小程序管理后台配置客服',
        '9410000' => '直播间列表为空',
        '9410001' => '获取房间失败',
        '9410002' => '获取商品失败',
        '9410003' => '获取回放失败',
        '300003' => '价格输入不合规',
        '300004' => '商品名称存在违规违法内容',
        '300005' => '商品图片存在违规违法内容',
        '300007' => '线上小程序版本不存在该链接',
        '300008' => '添加商品失败',
        '300009' => '商品审核撤回失败',
        '300010' => '商品审核状态不对',
        '300011' => '操作非法',
        '300012' => '没有提审额度',
        '300013' => '提审失败',
        '300014' => '审核中，无法删除',
        '300017' => '商品未提审',
        '300018' => '图片尺寸不符合要求',
        '300021' => '商品添加成功，审核失败',
        '40001' => 'AppSecret错误或者AppSecret不属于这个小程序，请确认AppSecret 的正确性',
        '40002' => '请确保grant_type字段值为client_credential',
        '40013' => '不合法的AppID，请检查AppID的正确性，避免异常字符，注意大小写',
        '40125' => '小程序配置无效，请检查配置',
        '41002' => '缺少appid参数',
        '41004' => '缺少secret参数',
        '43104' => 'appid与openid不匹配',
        '48001' => '微信接口暂无权限，请先去获取',
        '-1' => '系统错误',
    ];
    /**
     * @var Application
     */
    protected static $instance;

    /**
     * @return array
     */
    public static function options()
    {
        $wechat = SystemConfigService::more(['wechat_app_appsecret', 'wechat_app_appid', 'site_url', 'routine_appId', 'routine_appsecret']);
        $payment = SystemConfigService::more(['pay_weixin_mchid', 'pay_weixin_key', 'pay_weixin_client_cert', 'pay_weixin_client_key', 'pay_weixin_open', 'pay_new_weixin_open', 'pay_new_weixin_mchid']);
        $config = [];
        if (request()->isApp()) {
            $appId = isset($wechat['wechat_app_appid']) ? trim($wechat['wechat_app_appid']) : '';
            $appsecret = isset($wechat['wechat_app_appsecret']) ? trim($wechat['wechat_app_appsecret']) : '';
        } else {
            $appId = isset($wechat['routine_appId']) ? trim($wechat['routine_appId']) : '';
            $appsecret = isset($wechat['routine_appsecret']) ? trim($wechat['routine_appsecret']) : '';
        }
        $config['mini_program'] = [
            'app_id' => $appId,
            'secret' => $appsecret,
            'token' => isset($wechat['wechat_token']) ? trim($wechat['wechat_token']) : '',
            'aes_key' => isset($wechat['wechat_encodingaeskey']) ? trim($wechat['wechat_encodingaeskey']) : ''
        ];
        $config['payment'] = [
            'app_id' => $appId,
            'merchant_id' => empty($payment['pay_new_weixin_open']) ? trim($payment['pay_weixin_mchid']) : trim($payment['pay_new_weixin_mchid']),
            'key' => trim($payment['pay_weixin_key']),
            'cert_path' => substr(public_path(parse_url($payment['pay_weixin_client_cert'])['path']), 0, strlen(public_path(parse_url($payment['pay_weixin_client_cert'])['path'])) - 1),
            'key_path' => substr(public_path(parse_url($payment['pay_weixin_client_key'])['path']), 0, strlen(public_path(parse_url($payment['pay_weixin_client_key'])['path'])) - 1),
            'notify_url' => trim($wechat['site_url']) . Url::buildUrl('/api/routine/notify')
        ];
        return $config;
    }

    /**
     * 初始化
     * @param bool $cache
     * @return Application
     */
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
        try {
            return self::miniprogram()->sns->getSessionKey($code);
        } catch (\Throwable $e) {
            throw new AdminException($e->getMessage());
        }
    }

    /**
     * 加密数据解密
     * @param $sessionKey
     * @param $iv
     * @param $encryptData
     * @return $userInfo
     */
    public static function encryptor($sessionKey, $iv, $encryptData)
    {
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
        return self::qrcodeService()->appCodeUnlimit($scene, $page, $width, $autoColor, $lineColor);
    }


    /**
     * 模板消息接口
     * @return \EasyWeChat\Notice\Notice
     */
    public static function noticeService()
    {
        return self::miniprogram()->notice;
    }

    /**
     * 订阅模板消息接口
     * @return \crmeb\services\subscribe\ProgramSubscribe
     */
    public static function SubscribenoticeService()
    {
        return self::miniprogram()->now_notice;
    }

    /**
     * 发送订阅消息
     * @param string $touser 接收者（用户）的 openid
     * @param string $templateId 所需下发的订阅模板id
     * @param array $data 模板内容，格式形如 { "key1": { "value": any }, "key2": { "value": any } }
     * @param string $link 击模板卡片后的跳转页面，仅限本小程序内的页面。支持带参数,（示例index?foo=bar）。该字段不填则模板无跳转。
     * @return \EasyWeChat\Support\Collection|null
     * @throws \EasyWeChat\Core\Exceptions\HttpException
     * @throws \EasyWeChat\Core\Exceptions\InvalidArgumentException
     */
    public static function sendSubscribeTemlate(string $touser, string $templateId, array $data, string $link = '')
    {
        return self::SubscribenoticeService()->to($touser)->template($templateId)->andData($data)->withUrl($link)->send();
    }

    /**
     * 添加订阅消息模版
     * @param string $tid
     * @param array $kidList
     * @param string $sceneDesc
     * @return mixed
     */
    public static function addSubscribeTemplate(string $tid, array $kidList, string $sceneDesc = '')
    {
        try {
            $res = self::SubscribenoticeService()->addTemplate($tid, $kidList, $sceneDesc);
            if (isset($res['errcode']) && $res['errcode'] == 0 && isset($res['priTmplId'])) {
                return $res['priTmplId'];
            } else {
                Log::error('添加订阅消息模版失败：' . $res['errmsg']);
            }
        } catch (\Throwable $e) {
            Log::error('添加订阅消息模版失败：' . $e->getMessage());
        }
        return true;
    }

    /**
     * 删除订阅消息模版
     * @param string $tid
     * @param array $kidList
     * @param string $sceneDesc
     * @return mixed
     */
    public static function delSubscribeTemplate(string $priTmplId)
    {
        try {
            $res = self::SubscribenoticeService()->delTemplate($priTmplId);
            if (isset($res['errcode']) && $res['errcode'] == 0) {
                return true;
            } else {
                Log::error('删除订阅消息模版失败：' . $res['errmsg']);
            }
        } catch (\Throwable $e) {
            Log::error('删除订阅消息模版失败：' . $e->getMessage());
        }
        return true;
    }


    /**
     * 获取模版标题的关键词列表
     * @param string $tid
     * @return mixed
     */
    public static function getSubscribeTemplateKeyWords(string $tid)
    {
        try {
            $res = self::SubscribenoticeService()->getPublicTemplateKeywords($tid);
            if (isset($res['errcode']) && $res['errcode'] == 0 && isset($res['data'])) {
                return $res['data'];
            } else {
                throw new AdminException($res['errmsg']);
            }
        } catch (\Throwable $e) {
            throw new AdminException($e);
        }
    }

    /**
     * 获取订阅消息列表
     * @return mixed
     */
    public static function getSubscribeTemplateList()
    {
        try {
            return self::SubscribenoticeService()->getTemplateList();
        } catch (\Exception $e) {
            throw new AdminException($e->getMessage());
        }
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
    protected static function paymentOrder($openid, $out_trade_no, $total_fee, $attach, $body, $detail = '', $trade_type = 'JSAPI', $options = [])
    {
        $total_fee = bcmul($total_fee, 100, 0);
        $order = array_merge(compact('openid', 'out_trade_no', 'total_fee', 'attach', 'body', 'detail', 'trade_type'), $options);
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
                Cache::set($key, $result->prepay_id, 7000);
                return $result->prepay_id;
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
    public static function newPaymentPrepare($openid, $out_trade_no, $total_fee, $attach, $body, $detail = '', $options = [])
    {
        $key = 'pay_' . $out_trade_no;
        $result = Cache::get($key);
        if ($result) {
            return $result;
        } else {
            $order = self::paymentOrder($openid, $out_trade_no, $total_fee, $attach, $body, $detail, $options);
            $result = self::application()->minipay->createorder($order);
            if ($result->errcode === 0) {
                Cache::set($key, $result->payment_params, 7000);
                return $result->payment_params;
            } else {
                exception('微信支付错误返回：' . '[' . $result->errcode . ']' . $result->errmsg);
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
        return self::paymentService()->configForJSSDKPayment(self::paymentPrepare($openid, $out_trade_no, $total_fee, $attach, $body, $detail, $trade_type, $options));
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
    public static function newJsPay($openid, $out_trade_no, $total_fee, $attach, $body, $detail = '', $options = [])
    {
        $config = self::newPaymentPrepare($openid, $out_trade_no, $total_fee, $attach, $body, $detail, $options);
        $config['timestamp'] = $config['timeStamp'];
        unset($config['timeStamp']);
        return $config;

    }

    /**
     * 获得App支付参数
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
        return self::paymentService()->configForAppPayment(self::paymentPrepare($openid, $out_trade_no, $total_fee, $attach, $body, $detail, $trade_type, $options));
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
    public static function miniRefund($orderNo, $totalFee, $refundFee = null, $opt)
    {
        $totalFee = floatval($totalFee);
        $refundFee = floatval($refundFee);

        $order = [
            'openid' => $opt['open_id'],
            'trade_no' => $opt['order_id'],
            'transaction_id' => $opt['trade_no'],
            'refund_no' => $opt['refund_no'],
            'total_amount' => $totalFee,
            'refund_amount' => $refundFee,
        ];
        return self::application()->minipay->refundorder($order);
    }

    /** 根据订单号退款
     * @param $orderNo
     * @param array $opt
     * @return bool
     */
    public static function payOrderRefund($orderNo, array $opt)
    {
        if (!isset($opt['pay_price'])) throw new AdminException(400730);
        if (sys_config('pay_weixin_client_key') == '' || sys_config('pay_weixin_client_cert') == '') throw new AdminException(400739);
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
                    (new Hook(PayNotifyServices::class, 'wechat'))->listen($notify->attach, $notify->out_trade_no, $notify->transaction_id);
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
        return $res;
    }


    /**
     * 获取直播列表
     * @param int $page
     * @param int $limit
     * @return array
     */
    public static function getLiveInfo(int $page = 1, int $limit = 10)
    {
        try {
            $res = self::miniprogram()->wechat_live->getLiveInfo($page, $limit);
            if (isset($res['errcode']) && $res['errcode'] == 0 && isset($res['room_info']) && $res['room_info']) {
                return $res['room_info'];
            } else {
                return [];
            }
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * 获取直播回放
     * @param int $room_id
     * @param int $page
     * @param int $limit
     * @return mixed
     */
    public static function getLivePlayback(int $room_id, int $page = 1, int $limit = 10)
    {
        try {
            $res = self::miniprogram()->wechat_live->getLivePlayback($room_id, $page, $limit);
            if (isset($res['errcode']) && $res['errcode'] == 0 && isset($res['live_replay'])) {
                return $res['live_replay'];
            } else {
                throw new AdminException($res['errmsg']);
            }
        } catch (\Throwable $e) {
            throw new AdminException(self::getValidMessgae($e));
        }
    }

    /**
     * 创建直播间
     * @param array $data
     * @return mixed
     */
    public static function createLiveRoom(array $data)
    {
        try {
            $res = self::miniprogram()->wechat_live->createRoom($data);
            if (isset($res['errcode']) && $res['errcode'] == 0 && isset($res['roomId'])) {
                unset($res['errcode']);
                return $res;
            } else {
                throw new AdminException($res['errmsg']);
            }
        } catch (\Throwable $e) {
            throw new AdminException(self::getValidMessgae($e));
        }
    }

    /**
     * 直播间添加商品
     * @param int $roomId
     * @param $ids
     * @return bool
     */
    public static function roomAddGoods(int $roomId, $ids)
    {
        try {
            $res = self::miniprogram()->wechat_live->roomAddGoods($roomId, $ids);
            if (isset($res['errcode']) && $res['errcode'] == 0) {
                return true;
            } else {
                throw new AdminException($res['errmsg']);
            }
        } catch (\Throwable $e) {
            throw new AdminException(self::getValidMessgae($e));
        }
    }

    /**
     * 获取商品列表
     * @param int $status
     * @param int $page
     * @param int $limit
     * @return mixed
     */
    public static function getGoodsList(int $status = 2, int $page = 1, int $limit = 10)
    {
        try {
            $res = self::miniprogram()->wechat_live->getGoodsList($status, $page, $limit);
            if (isset($res['errcode']) && $res['errcode'] == 0 && isset($res['goods'])) {
                return $res['goods'];
            } else {
                throw new AdminException($res['errmsg']);
            }
        } catch (\Throwable $e) {
            throw new AdminException(self::getValidMessgae($e));
        }
    }

    /**
     * 获取商品详情
     * @param $goods_ids
     * @return mixed
     */
    public static function getGooodsInfo($goods_ids)
    {
        try {
            $res = self::miniprogram()->wechat_live->getGooodsInfo($goods_ids);
            if (isset($res['errcode']) && $res['errcode'] == 0 && isset($res['goods'])) {
                return $res['goods'];
            } else {
                throw new AdminException($res['errmsg']);
            }
        } catch (\Throwable $e) {
            throw new AdminException(self::getValidMessgae($e));
        }
    }

    /**
     * 添加商品
     * @param string $coverImgUrl
     * @param string $name
     * @param int $priceType
     * @param string $url
     * @param $price
     * @param string $price2
     * @return mixed
     */
    public static function addGoods(string $coverImgUrl, string $name, int $priceType, string $url, $price, $price2 = '')
    {
        try {
            $res = self::miniprogram()->wechat_live->addGoods($coverImgUrl, $name, $priceType, $url, $price, $price2);
            if (isset($res['errcode']) && $res['errcode'] == 0 && isset($res['goodsId'])) {
                unset($res['errcode']);
                return $res;
            } else {
                throw new AdminException($res['errmsg']);
            }
        } catch (\Throwable $e) {
            throw new AdminException(self::getValidMessgae($e));
        }
    }

    /**
     * 商品撤回审核
     * @param int $goodsId
     * @param $auditId
     * @return bool
     */
    public static function resetauditGoods(int $goodsId, $auditId)
    {
        try {
            $res = self::miniprogram()->wechat_live->resetauditGoods($goodsId, $auditId);
            if (isset($res['errcode']) && $res['errcode'] == 0) {
                return true;
            } else {
                throw new AdminException($res['errmsg']);
            }
        } catch (\Throwable $e) {
            throw new AdminException(self::getValidMessgae($e));
        }
    }

    /**
     * 商品重新提交审核
     * @param int $goodsId
     * @return mixed
     */
    public static function auditGoods(int $goodsId)
    {
        try {
            $res = self::miniprogram()->wechat_live->auditGoods($goodsId);
            if (isset($res['errcode']) && $res['errcode'] == 0 && isset($res['auditId'])) {
                return $res['auditId'];
            } else {
                throw new AdminException($res['errmsg']);
            }
        } catch (\Throwable $e) {
            throw new AdminException(self::getValidMessgae($e));
        }
    }

    /**
     * 删除商品
     * @param int $goodsId
     * @return bool
     */
    public static function deleteGoods(int $goodsId)
    {
        try {
            $res = self::miniprogram()->wechat_live->deleteGoods($goodsId);
            if (isset($res['errcode']) && $res['errcode'] == 0) {
                return true;
            } else {
                throw new AdminException($res['errmsg']);
            }
        } catch (\Throwable $e) {
            throw new AdminException(self::getValidMessgae($e));
        }
    }

    /**
     * 更新商品
     * @param int $goodsId
     * @param string $coverImgUrl
     * @param string $name
     * @param int $priceType
     * @param string $url
     * @param $price
     * @param string $price2
     * @return bool
     */
    public static function updateGoods(int $goodsId, string $coverImgUrl, string $name, int $priceType, string $url, $price, $price2 = '')
    {
        try {
            $res = self::miniprogram()->wechat_live->updateGoods($goodsId, $coverImgUrl, $name, $priceType, $url, $price, $price2);
            if (isset($res['errcode']) && $res['errcode'] == 0) {
                return true;
            } else {
                throw new AdminException($res['errmsg']);
            }
        } catch (\Throwable $e) {
            throw new AdminException(self::getValidMessgae($e));
        }
    }

    /**
     * 更新商品
     * @param int $goodsId
     * @param string $coverImgUrl
     * @param string $name
     * @param int $priceType
     * @param string $url
     * @param $price
     * @param string $price2
     * @return bool
     */
    public static function getRoleList($role = 2, int $page = 0, int $limit = 30, $keyword = '')
    {
        try {
            $res = self::miniprogram()->wechat_live->getRoleList($role, $page, $limit, $keyword);
            if (isset($res['errcode']) && $res['errcode'] == 0 && isset($res['list'])) {
                return $res['list'];
            } else {
                throw new AdminException($res['errmsg']);
            }
        } catch (\Throwable $e) {
            throw new AdminException(self::getValidMessgae($e));
        }
    }

    public static function getValidMessgae(\Throwable $e)
    {
        $message = '';
        if (!isset(self::MSG_CODE[$e->getCode()]) && strstr($e->getMessage(), 'Request AccessToken fail') !== false) {
            $message = str_replace('Request AccessToken fail. response:', '', $e->getMessage());
            $message = json_decode($message, true) ?: [];
            $errcode = $message['errcode'] ?? false;
            if ($errcode) {
                $message = self::MSG_CODE[$errcode] ?? $message;
            }
        }
        return $message ?: self::MSG_CODE[$e->getCode()] ?? $e->getMessage();
    }
}
