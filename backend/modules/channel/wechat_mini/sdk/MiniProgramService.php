<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace modules\channel\wechat_mini\sdk;

use app\services\order\OrderTakeServices;
use app\services\pay\PayServices;
use app\services\pay\PayRecordServices;
use app\services\system\SystemPemServices;
use crmeb\exceptions\AdminException;
use crmeb\services\easywechat\Application;
use crmeb\services\pay\PayChannelConfig;
use crmeb\services\wechat\Support;
use think\facade\Event;
use think\facade\Log;
use think\Response;

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
        '300023' => '房间状态 拦截',
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
        $wechat = sys_config_more(['wechat_app_appsecret', 'wechat_app_appid', 'site_url', 'routine_appId', 'routine_appsecret', 'routine_token', 'routine_encodingaeskey']);
        // 支付配置统一存储于 eb_pay_channel.config_fields，不再读取 system_config
        $payment = PayChannelConfig::get('weixin');
        $config = [];
        if (request()->isApp()) {
            $appId = isset($wechat['wechat_app_appid']) ? trim($wechat['wechat_app_appid']) : '';
            $appsecret = isset($wechat['wechat_app_appsecret']) ? trim($wechat['wechat_app_appsecret']) : '';
        } else {
            $appId = isset($wechat['routine_appId']) ? trim($wechat['routine_appId']) : '';
            $appsecret = isset($wechat['routine_appsecret']) ? trim($wechat['routine_appsecret']) : '';
        }
        $config = [
            'token' => isset($wechat['routine_token']) ? trim($wechat['routine_token']) : '',
            'aes_key' => isset($wechat['routine_encodingaeskey']) ? trim($wechat['routine_encodingaeskey']) : '',
        ];
        $config['mini_program'] = [
            'app_id' => $appId,
            'secret' => $appsecret,
            'token' => isset($wechat['routine_token']) ? trim($wechat['routine_token']) : '',
            'aes_key' => isset($wechat['routine_encodingaeskey']) ? trim($wechat['routine_encodingaeskey']) : ''
        ];
        $config['payment'] = [
            'app_id' => $appId,
            'merchant_id' => empty($payment['pay_new_weixin_open']) ? trim($payment['pay_weixin_mchid'] ?? '') : trim($payment['pay_new_weixin_mchid'] ?? ''),
            'key' => trim($payment['pay_weixin_key'] ?? ''),
            'cert_path' => self::getPemPath('pay_weixin_client_cert'),
            'key_path' => self::getPemPath('pay_weixin_client_key'),
            'notify_url' => trim($wechat['site_url'] ?? '') . '/api/pay/notify/weixin'
        ];
//        if (Env::get('cache.driver', 'file') == 'redis') {
//            $cache = new \Doctrine\Common\Cache\RedisCache();
//            $cache->setRedis(\think\facade\CacheService::store('redis')->handler());
//            $config['cache'] = $cache;
//        }
        return $config;
    }

    public static function getPemPath(string $name)
    {
        $systemPemServices = app()->make(SystemPemServices::class);
        $path = $systemPemServices->getPemPath($name);
        if ($path) return $path;
        // 回退：从支付渠道配置读取证书路径（微信支付证书统一存储于 eb_pay_channel.config_fields）
        $payConfig = PayChannelConfig::get('weixin');
        $path = $payConfig[$name] ?? '';
        if (strstr($path, 'http://') || strstr($path, 'https://')) {
            $path = parse_url($path)['path'] ?? '';
        }
        $path = root_path('runtime/pem') . ltrim($path, '/');
        if (!file_exists($path)) {
            $path = public_path('uploads') . ltrim($path, '/');
        }
        return $path;
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
     * @return \EasyWeChat\MiniApp\Application
     */
    public static function miniprogram()
    {
        return self::application()->getMiniProgramApp();
    }

    /**
     * 获得用户信息 根据code 获取session_key
     * @param string $code
     * @return array
     */
    public static function getUserInfo($code)
    {
        try {
            return self::miniprogram()->getUtils()->codeToSession($code);
        } catch (\Throwable $e) {
            throw new AdminException($e->getMessage());
        }
    }

    /**
     * 加密数据解密
     * @param string $sessionKey
     * @param string $iv
     * @param string $encryptData
     * @return array
     */
    public static function encryptor($sessionKey, $iv, $encryptData)
    {
        return self::miniprogram()->getUtils()->decryptSession($sessionKey, $iv, $encryptData);
    }

    /**
     * 上传临时素材接口
     * @return MiniMedia
     */
    public static function materialTemporaryService()
    {
        return new MiniMedia(self::miniprogram()->getClient());
    }

    /**
     * 客服消息接口(发送客服消息)
     * @return \Symfony\Contracts\HttpClient\HttpClientInterface
     */
    public static function staffService()
    {
        return self::miniprogram()->getClient();
    }

    /**
     * 微信小程序二维码生成接口
     * @return \Symfony\Contracts\HttpClient\HttpClientInterface
     */
    public static function qrcodeService()
    {
        return self::miniprogram()->getClient();
    }

    /**微信小程序二维码生成接口不限量永久
     * @param string $scene
     * @param string|null $page
     * @param int $width
     * @param bool $autoColor
     * @param array $lineColor
     * @return \EasyWeChat\Kernel\HttpClient\Response 二进制响应,调用方 (string) 转换或 ->saveAs() 落盘
     */
    public static function appCodeUnlimitService($scene, $page = null, $width = 430, $autoColor = false, $lineColor = ['r' => 0, 'g' => 0, 'b' => 0])
    {
        return self::miniprogram()->getClient()->postJson('wxa/getwxacodeunlimit', array_filter([
            'scene' => $scene,
            'page' => $page,
            'width' => $width,
            'auto_color' => $autoColor,
            'line_color' => $lineColor,
        ], static fn($value) => $value !== null && $value !== ''));
    }


    /**
     * 模板消息接口(4.x 旧版模板消息已废弃,如需使用请通过 getClient() 调用)
     * @return \Symfony\Contracts\HttpClient\HttpClientInterface
     */
    public static function noticeService()
    {
        return self::miniprogram()->getClient();
    }

    /**
     * 订阅模板消息接口
     * @return \crmeb\services\easywechat\subscribe\ProgramSubscribe
     */
    public static function SubscribenoticeService()
    {
        return self::application()->subscribe();
    }

    /**
     * 发送订阅消息
     * @param string $touser 接收者（用户）的 openid
     * @param string $templateId 所需下发的订阅模板id
     * @param array $data 模板内容，格式形如 { "key1": { "value": any }, "key2": { "value": any } }
     * @param string $link 击模板卡片后的跳转页面，仅限本小程序内的页面。支持带参数,（示例index?foo=bar）。该字段不填则模板无跳转。
     * @return array
     */
    public static function sendSubscribeTemlate(string $touser, string $templateId, array $data, string $link = '')
    {
        return self::SubscribenoticeService()->to($touser)->template($templateId)->andData($data)->withUrl($link)->send();
    }

    /**
     * 添加订阅消息模板
     * @param string $tid
     * @param array $kidList
     * @param string $sceneDesc
     * @return mixed
     */
    public static function addSubscribeTemplate(string $tid, array $kidList, string $sceneDesc = '')
    {
        try {
            $res = self::SubscribenoticeService()->addTemplate($tid, $kidList, $sceneDesc);
            if (isset($res['priTmplId'])) {
                return $res['priTmplId'];
            } else {
                Log::error('添加订阅消息模板失败：' . json_encode($res, JSON_UNESCAPED_UNICODE));
            }
        } catch (\Throwable $e) {
            Log::error('添加订阅消息模板失败：' . $e->getMessage());
        }
        return false;
    }

    /**
     * 删除订阅消息模板
     * @param string $priTmplId
     * @return mixed
     */
    public static function delSubscribeTemplate(string $priTmplId)
    {
        try {
            $res = self::SubscribenoticeService()->delTemplate($priTmplId);
            if (isset($res['errcode']) && $res['errcode'] == 0) {
                return true;
            } else {
                Log::error('删除订阅消息模板失败：' . $res['errmsg']);
            }
        } catch (\Throwable $e) {
            Log::error('删除订阅消息模板失败：' . $e->getMessage());
        }
        return true;
    }


    /**
     * 获取模板标题的关键词列表
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
                //已经添加过有相同的
                if (isset($res['priTmplId'])) {
                    return $res['priTmplId'];
                }
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
     * 作为客服消息发送
     * @param string $to
     * @param mixed $message 数组(含 msgtype)或闭包
     * @return array
     */
    public static function staffTo($to, $message)
    {
        $msg = is_callable($message) ? $message() : $message;
        if (!is_array($msg)) {
            $msg = ['msgtype' => 'text', 'text' => ['content' => (string)$msg]];
        }
        $msg['touser'] = $to;
        $res = self::staffService()->postJson('cgi-bin/message/custom/send', $msg)->toArray(false);
        return is_array($res) ? $res : [];
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
            $res = self::application()->live()->getLiveInfo($page, $limit);
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
            $res = self::application()->live()->getLivePlayback($room_id, $page, $limit);
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
            $res = self::application()->live()->createRoom($data);
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
            $res = self::application()->live()->roomAddGoods($roomId, $ids);
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
            $res = self::application()->live()->getGoodsList($status, $page, $limit);
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
            $res = self::application()->live()->getGooodsInfo($goods_ids);
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
            $res = self::application()->live()->addGoods($coverImgUrl, $name, $priceType, $url, $price, $price2);
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
            $res = self::application()->live()->resetauditGoods($goodsId, $auditId);
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
            $res = self::application()->live()->auditGoods($goodsId);
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
            $res = self::application()->live()->deleteGoods($goodsId);
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
            $res = self::application()->live()->updateGoods($goodsId, $coverImgUrl, $name, $priceType, $url, $price, $price2);
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
            $res = self::application()->live()->getRoleList($role, $page, $limit, $keyword);
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


    /**
     * @return Response
     */
    public static function serve(): Response
    {
        $app = self::miniprogram();
        $app->setRequestFromSymfonyRequest(Support::symfonyRequest());
        $server = $app->getServer();
        self::hook($server);
        return Support::toThinkResponse($server->serve());
    }

    private static function hook($server)
    {
        $server->with(function ($message, \Closure $next) {
            switch ($message['MsgType']) {
                case 'event':
                    switch (strtolower($message['Event'])) {
                        case 'funds_order_pay':  // 小程序支付管理的
                            $order_info = $message['order_info'] ?? [];
                            if (($count = strpos($order_info['trade_no'] ?? '', '_')) !== false) {
                                $trade_no = substr($order_info['trade_no'], $count + 1);
                            } else {
                                $trade_no = $order_info['trade_no'] ?? '';
                            }
                            if (!app()->make(PayRecordServices::class)->syncPayment($trade_no,
                                (string)($order_info['transaction_id'] ?? ''), (string)($message['FromUserName'] ?? ''))) {
                                throw new \RuntimeException('小程序支付回调处理失败');
                            }
                            break;
                        case 'trade_manage_remind_access_api':  // 小程序完成账期授权时  小程序产生第一笔交易时 已产生交易但从未发货的小程序，每天一次
                            break;
                        case 'trade_manage_remind_shipping':   // 曾经发过货的小程序，订单超过48小时未发货时
                            break;
                        case 'trade_manage_order_settlement':     // 订单完成发货时  订单结算时
                            if (isset($message['confirm_receive_method'])) {  // 订单结算时
                                /** @var OrderTakeServices $OrderTakeServices */
                                $storeOrderTakeServices = app()->make(OrderTakeServices::class);
                                $storeOrderTakeServices->miniOrderTakeOrder($message['merchant_trade_no']);
                            }
                            break;
                    };
                    break;
            };
            return $next($message);
        });
    }

    public static function getUrlScheme($jumpWxa = [], $expireType = -1, $expireNum = 0)
    {
        try {
            $res = self::application()->scheme()->getUrlScheme($jumpWxa, $expireType, $expireNum);
            if (isset($res['errcode']) && $res['errcode'] == 0) {
                return $res['openlink'];
            } else {
                return '';
            }
        } catch (\Throwable $e) {
            return $e->getMessage();
        }
    }

    public static function getUrlLink($jumpWxa = [])
    {
        try {
            $res = self::application()->scheme()->getUrlLink($jumpWxa);
            if (isset($res['errcode']) && $res['errcode'] == 0) {
                return $res['url_link'];
            } else {
                return '';
            }
        } catch (\Throwable $e) {
            return $e->getMessage();
        }
    }
}
