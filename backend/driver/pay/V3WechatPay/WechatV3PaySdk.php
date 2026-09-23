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

namespace driver\pay\V3WechatPay;

use crmeb\exceptions\PayException;
use crmeb\services\CacheService;
use crmeb\services\easywechat\Application;
use think\facade\Db;

/**
 * 微信支付 V3 驱动专用 SDK —— 小程序 minipay 通道
 *
 * 本 SDK 仅提供 V3 协议（$this->instance->v3pay）未覆盖的「小程序新版支付」链路，
 * 供本驱动包 Driver.php 在 wechat_mini + pay_new_weixin_open 场景下调用。
 *
 * 其余支付能力（nativePay / appPay / jsapiPay / h5Pay /
 * handleNotify / merchantPay 等）均由 V3 协议（v3pay）直接处理，不在本 SDK 内实现。
 *
 * ⚠️ 与 driver/pay/WechatPay/WechatPaySdk.php 的关系：
 * - 那是 V2 协议的完整 SDK（支持 mini/mp/app 三场景）；
 * - 本类是 V3 驱动的补充 SDK，仅 minipay 一条链路；
 * - 两者协议不同、职责不同，**不是同源副本，不需同步修改**。
 */
class WechatV3PaySdk
{
    /**
     * Application 实例（mini 场景单例）
     * @var Application|null
     */
    protected static $instance;

    /**
     * 小程序实例对应的配置摘要，避免常驻进程复用其他商户配置。
     * @var string
     */
    protected static string $instanceKey = '';

    /**
     * 从 eb_pay_channel 读取本驱动支付插件配置
     *
     * 架构豁免说明：支付驱动插件化后配置统一存储于 eb_pay_channel.config_fields
     * （app\services\driver\pay\PayChannelServices 负责读写），本类位于 driver 第三方
     * 驱动底座，driver 层无 DAO 体系，故此处 Db 直查为合规做法（同本类 getPemPath 模式），
     * 禁止"修复"为 app 层 DAO
     * @return array{status:int, config:array<string,string>}
     */
    protected static function channelConfig(): array
    {
        try {
            $channel = Db::name('pay_channel')->where('channel_key', 'weixin_v3')->where('is_del', 0)->find();
        } catch (\Throwable $e) {
            return ['status' => 0, 'config' => []];
        }
        $config = [];
        if ($channel && !empty($channel['config_fields'])) {
            // Db 直查返回 text 列原始 JSON 字符串，需解码（模型 cast 在 driver 层不生效）
            $configFields = is_string($channel['config_fields']) ? json_decode($channel['config_fields'], true) : $channel['config_fields'];
            if (is_array($configFields)) {
                foreach ($configFields as $field) {
                    if (isset($field['field'])) {
                        $config[$field['field']] = $field['value'] ?? '';
                    }
                }
            }
        }
        return [
            'status' => (int)($channel['status'] ?? 0),
            'config' => $config,
        ];
    }

    /**
     * 读取小程序场景支付配置
     * @param array $context 原支付信息
     * @return array
     */
    public static function options(array $context = []): array
    {
        // V3 渠道自身配置（商户号、各端 AppID 与小程序 AppSecret）
        $channelConfig = static::channelConfig();
        $payment = $channelConfig['config'];
        // site_url 属系统公用配置（站点信息），允许 driver 直接 sys_config 读取
        $siteUrl = sys_config('site_url');

        // 小程序场景配置
        $wechat = [
            'site_url' => $siteUrl,
            'routine_appId' => $payment['routine_appId'] ?? '',
            'routine_appsecret' => $payment['routine_appsecret'] ?? '',
        ];
        $appId = trim($wechat['routine_appId'] ?? '');
        $appsecret = trim($wechat['routine_appsecret'] ?? '');

        if ($appId === '') {
            throw new PayException('微信支付V3小程序需要配置小程序AppID');
        }
        if ($appsecret === '') {
            throw new PayException('微信支付V3新小程序支付需要配置小程序AppSecret');
        }
        if (!empty($context['app_id']) && $context['app_id'] !== $appId) {
            throw new PayException('原支付小程序AppID与当前V3配置不一致');
        }

        $config = [
            'mini_program' => [
                'app_id' => $appId,
                'secret' => $appsecret,
            ],
        ];
        // shop/pay 使用原订单商户号，不受当前商户号模式开关影响。
        $merchantId = trim((string)($context['mch_id'] ?? $payment['pay_new_weixin_mchid'] ?? ''));
        if ($merchantId === '') {
            throw new PayException('微信支付V3新小程序支付需要配置小程序支付商户号');
        }
        $config['payment'] = [
            'app_id' => $appId,
            'merchant_id' => $merchantId,
        ];
        return $config;
    }

    /**
     * 获取并校验小程序支付私钥。
     *
     * @return string
     */
    protected static function getPrivateKeyPath(): string
    {
        $path = self::getPemPath('pay_weixin_client_key');
        if ($path === '' || !is_file($path) || !is_readable($path)) {
            throw new PayException('微信支付商户私钥文件不存在，请重新上传未加密的 apiclient_key.pem');
        }
        $privateKey = @file_get_contents($path);
        if ($privateKey === false || trim($privateKey) === '' || @openssl_pkey_get_private($privateKey) === false) {
            throw new PayException('微信支付商户私钥无效，请重新上传未加密的 apiclient_key.pem');
        }
        return $path;
    }

    /**
     * 从 V3 支付渠道配置获取证书路径。
     *
     * V2 与 V3 使用相同的字段名，但证书路径属于各自 eb_pay_channel.config_fields，
     * 不能回退到全局 system_pem，否则会把 V2 证书带入 V3 签名。
     * @param string $name 证书配置名
     * @return string
     */
    public static function getPemPath(string $name): string
    {
        // 证书路径只从 V3 渠道读取，不读取 V2 或应用配置。
        $channelConfig = self::channelConfig();
        $path = trim((string)($channelConfig['config'][$name] ?? ''));
        // 未配置证书时直接返回空串，避免空值拼接出无效路径
        if ($path === '') {
            return '';
        }
        if (strstr($path, 'http://') || strstr($path, 'https://')) {
            $path = parse_url($path)['path'] ?? '';
        }
        $path = str_replace('\\', '/', trim($path));
        if ($path === '') {
            return '';
        }
        // 仅检查项目根目录内的绝对路径，避免把 /xxx.pem 这类上传 URL 直接交给 open_basedir。
        $rootPath = str_replace('\\', '/', rtrim(root_path(), '/\\')) . '/';
        $normalizedPath = str_replace('\\', '/', $path);
        if (str_starts_with($normalizedPath, $rootPath) && is_file($path) && is_readable($path)) {
            return $path;
        }
        $relativePath = ltrim($path, '/');
        if (str_starts_with($relativePath, 'uploads/')) {
            $relativePath = substr($relativePath, strlen('uploads/'));
        }
        $runtimePath = root_path('runtime/pem') . $relativePath;
        if (is_file($runtimePath) && is_readable($runtimePath)) {
            return $runtimePath;
        }
        $publicPath = public_path('uploads') . $relativePath;
        if (is_file($publicPath) && is_readable($publicPath)) {
            return $publicPath;
        }
        return '';
    }

    /**
     * 获取 Application 实例（mini 场景单例）
     * @param bool $cache 是否强制刷新缓存
     * @param array $context 原支付信息
     * @return Application
     */
    public static function application(bool $cache = false, array $context = []): Application
    {
        $options = static::options($context);
        $key = hash('sha256', json_encode($options, JSON_THROW_ON_ERROR));
        if (self::$instance === null || $cache || self::$instanceKey !== $key) {
            self::$instance = new Application($options);
            self::$instanceKey = $key;
        }
        return self::$instance;
    }

    /**
     * 生成小程序支付订单对象
     * @param string|null $openid
     * @param string $out_trade_no
     * @param mixed $total_fee
     * @param string $attach
     * @param string $body
     * @param string $detail
     * @param string $trade_type
     * @param array $options
     * @return array
     */
    protected static function paymentOrderMini($openid, $out_trade_no, $total_fee, $attach, $body, $detail = '', $trade_type = 'JSAPI', $options = [])
    {
        $total_fee = bcmul($total_fee, 100, 0);
        $order = compact('openid', 'out_trade_no', 'total_fee', 'attach', 'body', 'detail', 'trade_type')
            + array_intersect_key($options, ['expire_time' => true]);
        if ($order['detail'] === '') unset($order['detail']);
        return $order;
    }

    /**
     * 新版小程序下单（minipay 通道）
     * 返回 payment_params 并缓存
     * @param string|null $openid
     * @param string $out_trade_no
     * @param mixed $total_fee
     * @param string $attach
     * @param string $body
     * @param string $detail
     * @param array $options
     * @return array
     */
    public static function newPaymentPrepare($openid, $out_trade_no, $total_fee, $attach, $body, $detail = '', $options = [])
    {
        $context = $options['payment_context'] ?? [];
        $key = 'pay:v3:mini:' . $out_trade_no . ':' . hash('sha256', json_encode([
            $context, (string)$total_fee, $openid], JSON_THROW_ON_ERROR));
        $result = CacheService::get($key);
        if ($result) {
            return $result;
        }

        $order = self::paymentOrderMini($openid, $out_trade_no, $total_fee, $attach, $body, $detail, 'JSAPI', $options);
        $result = static::application(false, $context)->minipay->createorder($order);
        if (isset($result['errcode']) && (int)$result['errcode'] === 0 && !empty($result['payment_params'])) {
            CacheService::set($key, $result['payment_params'], 7000);
            return $result['payment_params'];
        } else {
            throw new PayException('微信支付错误返回：' . ($result['errmsg'] ?? '小程序支付响应无效'));
        }
    }

    /**
     * 新版小程序支付参数（minipay 通道）
     * 供 V3WechatPay/Driver 在 wechat_mini + pay_new_weixin_open 场景下调用
     * @param string|null $openid
     * @param string $out_trade_no
     * @param mixed $total_fee
     * @param string $attach
     * @param string $body
     * @param string $detail
     * @param array $options
     * @return array
     */
    public static function newJsPay($openid, $out_trade_no, $total_fee, $attach, $body, $detail = '', $options = [])
    {
        $config = self::newPaymentPrepare($openid, $out_trade_no, $total_fee, $attach, $body, $detail, $options);
        $config['timestamp'] = $config['timeStamp'];
        unset($config['timeStamp']);
        return $config;
    }

    /**
     * 使用原支付信息提交 shop/pay 退款；接口受理后仍需查询到账结果。
     * @param string $orderNo 商家交易单号
     * @param array $options 退款参数
     * @return array
     */
    public static function refund(string $orderNo, array $options): array
    {
        $context = $options['payment_context'] ?? [];
        $openid = (string)($context['openid'] ?? '');
        $refundNo = (string)($options['refund_id'] ?? '');
        if ($orderNo === '' || $openid === '' || !preg_match('/^[0-9A-Za-z_\-|*@]{6,32}$/D', $refundNo)) {
            throw new PayException('新版小程序退款缺少原订单号、支付者OpenID或有效的固定退款号');
        }
        $total = (int)bcmul((string)($options['pay_price'] ?? '0'), '100', 0);
        $amount = (int)bcmul((string)($options['refund_price'] ?? '0'), '100', 0);
        if ($amount <= 0 || $total <= 0 || $amount > $total) {
            throw new PayException('新版小程序退款金额无效');
        }
        $app = static::application(false, $context);
        $transactionId = (string)($context['transaction_id'] ?? '');
        if ($transactionId === '') {
            $order = static::getOrder($orderNo, $context);
            if ((int)($order['amount'] ?? 0) !== $total) {
                throw new PayException('新版小程序原支付金额与订单不一致');
            }
            $transactionId = (string)($order['transaction_id'] ?? '');
        }
        if ($transactionId === '') {
            throw new PayException('新版小程序退款缺少原微信支付单号');
        }
        $result = $app->minipay->refundorder([
            'openid' => $openid,
            'trade_no' => $orderNo,
            'transaction_id' => $transactionId,
            'refund_no' => $refundNo,
            'total_amount' => $total,
            'refund_amount' => $amount,
        ]);
        if (!isset($result['errcode']) || (int)$result['errcode'] !== 0) {
            throw new PayException($result['errmsg'] ?? '新版小程序退款响应无效');
        }
        return ['status' => 'PROCESSING', 'out_refund_no' => $refundNo];
    }

    /**
     * 按固定退款号查询订单退款记录。
     * @param string $orderNo 原商家交易单号
     * @param string $refundNo 固定退款号
     * @param array $context 原支付信息
     * @return array
     */
    public static function queryRefund(string $orderNo, string $refundNo, array $context): array
    {
        $order = static::getOrder($orderNo, $context);
        foreach ($order['refund_list'] ?? [] as $refund) {
            if (($refund['refund_no'] ?? '') === $refundNo) {
                return [
                    'status' => strtoupper((string)($refund['result'] ?? 'PROCESSING')),
                    'refund_id' => (string)($refund['refund_id'] ?? ''),
                    'out_refund_no' => $refundNo,
                    'message' => (string)($refund['fail_reason'] ?? ''),
                ];
            }
        }
        return ['status' => 'UNKNOWN', 'message' => '原订单尚未查到该退款号'];
    }

    /**
     * 查询并核对原订单归属，防止使用其他商户或订单的支付流水。
     * @param string $orderNo 原商家交易单号
     * @param array $context 原支付信息
     * @return array
     */
    public static function getOrder(string $orderNo, array $context): array
    {
        if ($orderNo === '') {
            throw new PayException('新版小程序退款查询缺少原订单号');
        }
        $result = static::application(false, $context)->minipay->getorder($orderNo);
        if (!isset($result['errcode']) || (int)$result['errcode'] !== 0) {
            throw new PayException($result['errmsg'] ?? '新版小程序订单查询响应无效');
        }
        $order = $result['order'] ?? [];
        if (($order['trade_no'] ?? '') !== $orderNo || ($order['mchid'] ?? '') !== ($context['mch_id'] ?? '')) {
            throw new PayException('新版小程序原订单或商户号不一致');
        }
        return $order;
    }
}
