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
use crmeb\services\pay\BasePay;
use crmeb\services\pay\PayInterface;
use EasyWeChat\Pay\Application as PayApplication;
use think\facade\Db;
use think\facade\Event;

/**
 * 微信支付v3
 * Class Driver
 * @package driver\pay\V3WechatPay
 */
class Driver extends BasePay implements PayInterface
{

    /**
     * 微信支付 V3 渠道标识，与本驱动 plugin.json 的 channel_key 保持一致。
     * 支付回调保存此标识，退款才能使用原 V3 驱动和商户配置。
     */
    const WEIXIN_PAY_CHANNEL_KEY = 'weixin_v3';

    /**
     * @var PayClient
     */
    protected $instance;

    /**
     * 各渠道 appid
     * @var array
     */
    protected array $appids = [];

    /**
     * 本次调用使用的 V3 渠道配置。
     * @var array
     */
    protected array $payment = [];

    /** @var string 客户端证书文件指纹 */
    protected string $clientFingerprint = '';

    /**
     * @param array $config
     * @return mixed|void
     */
    protected function initialize(array $config = [])
    {
        // 支付插件配置（微信支付已插件化，统一存储于 eb_pay_channel.config_fields）
        $payment = $this->payment = $this->channelConfig();

        // V3 渠道独立维护各端 AppID，不读取 V2 weixin 渠道配置。
        $wechatAppid = trim((string)($payment['wechat_appid'] ?? ''));
        $miniAppid = trim((string)($payment['routine_appId'] ?? ''));
        $appAppid = trim((string)($payment['wechat_app_appid'] ?? ''));

        // H5 下单必须携带已与商户号绑定的 AppID；公众号未配置时可使用小程序或移动应用 AppID。
        $h5Appid = $wechatAppid !== '' ? $wechatAppid : ($miniAppid !== '' ? $miniAppid : $appAppid);
        $this->appids = [
            'app' => $appAppid,
            'wechat' => $wechatAppid,
            'miniprog' => $miniAppid,
            'web' => $wechatAppid,
            'h5' => $h5Appid,
        ];
    }

    /**
     * 按需初始化普通 V3 客户端；shop/pay 仅使用小程序 access_token。
     * @param array $context 原支付信息
     * @return PayClient
     */
    protected function getPayClient(array $context = [])
    {
        $this->refreshPayment();
        $payment = $this->payment;
        if (!empty($context['mch_id']) && $context['mch_id'] !== trim((string)($payment['pay_weixin_mchid'] ?? ''))) {
            throw new PayException('原支付商户号与当前V3配置不一致，请恢复原商户配置后退款');
        }
        if (isset($context['sub_mch_id']) && $context['sub_mch_id'] !==
            (!empty($payment['mer_type']) ? trim($payment['pay_sub_merchant_id'] ?? '') : '')) {
            throw new PayException('原支付子商户号与当前V3配置不一致');
        }
        // 站点域名属系统公用配置（站点信息），允许 driver 直接 sys_config 读取
        $siteUrl = trim((string)sys_config('site_url'));
        $privateKeyPath = $this->getPemPath('pay_weixin_client_key');
        $this->assertPrivateKey($privateKeyPath);
        $certificatePath = $this->getPemPath('pay_weixin_client_cert');
        if ($certificatePath === '' || !is_file($certificatePath) || !is_readable($certificatePath)) {
            throw new PayException('微信支付商户证书文件不存在，请重新上传 apiclient_cert.pem');
        }

        $publicKeyId = trim((string)($payment['v3_pay_public_key'] ?? ''));
        $publicKeyPath = $publicKeyId !== '' ? $this->getPemPath('v3_pay_public_pem') : '';
        $fingerprint = hash('sha256', serialize([$payment, $siteUrl, hash_file('sha256', $privateKeyPath),
            hash_file('sha256', $certificatePath), is_file($publicKeyPath) ? hash_file('sha256', $publicKeyPath) : '']));
        if ($this->instance !== null && $this->clientFingerprint === $fingerprint) return $this->instance;

        $payConfig = [
            'mch_id' => $payment['pay_weixin_mchid'] ?? '',
            'secret_key' => $payment['pay_weixin_key_v3'] ?? '',
            'private_key' => $privateKeyPath,
            'certificate' => $certificatePath,
            'notify_url' => $siteUrl . '/api/pay/notify/v3wechat',
            'http' => [
                'timeout' => 30.0,
                'throw' => false,
            ],
        ];

        // 服务商模式
        if (!empty($payment['mer_type'])) {
            $payConfig['sub_mch_id'] = trim($payment['pay_sub_merchant_id'] ?? '');
            $payConfig['sp_appid'] = trim($payment['sp_appid'] ?? '');
        }

        // 平台公钥模式优先;证书模式自动下载平台证书(供回调验签与敏感字段加密)
        if ($publicKeyId !== '') {
            $payConfig['platform_certs'] = [$publicKeyId => $publicKeyPath];
            $application = new PayApplication($payConfig);
        } else {
            $platformCerts = PayClient::downloadPlatformCerts(new PayApplication($payConfig));
            if ($platformCerts) {
                $payConfig['platform_certs'] = $platformCerts;
            }
            $application = new PayApplication($payConfig);
        }

        $this->instance = new PayClient($application, $this->appids, $publicKeyId);
        $this->clientFingerprint = $fingerprint;
        return $this->instance;
    }

    /**
     * 长驻进程按最新渠道配置更新应用身份并失效旧客户端。
     * @return void
     */
    protected function refreshPayment(): void
    {
        if ($this->channelConfig() !== $this->payment) {
            $this->instance = null;
            $this->initialize();
        }
    }

    /**
     * 获取实际支付模式及非密钥配置，供订单保存并在退款时复用。
     * @param array $options 支付参数
     * @return array
     */
    public function getPaymentContext(array $options): array
    {
        $this->refreshPayment();
        $payment = $this->payment;
        $platform = $options['platform'] ?? '';
        $miniShop = $platform === 'wechat_mini' && ($options['pay_type'] ?? '') === 'JSAPI'
            && !empty($payment['pay_new_weixin_open']);
        if ($miniShop && (empty($options['openid']) || empty($payment['routine_appId'])
            || empty($payment['routine_appsecret']) || empty($payment['pay_new_weixin_mchid']))) {
            throw new PayException('新版小程序支付需要配置AppID、AppSecret、商户号并提供支付者OpenID');
        }
        return [
            'channel_key' => self::WEIXIN_PAY_CHANNEL_KEY,
            'platform' => $platform,
            'mode' => $miniShop ? 'mini_shop' : 'v3',
            'mch_id' => trim((string)($payment[$miniShop ? 'pay_new_weixin_mchid' : 'pay_weixin_mchid'] ?? '')),
            'sub_mch_id' => !$miniShop && !empty($payment['mer_type']) ? trim($payment['pay_sub_merchant_id'] ?? '') : '',
            'app_id' => (string)($this->appids[$platform === 'wechat_mini' ? 'miniprog'
                : ($platform === 'app' ? 'app' : ($platform === 'h5' ? 'h5' : 'wechat'))] ?? ''),
            'openid' => (string)($options['openid'] ?? ''),
        ];
    }

    /**
     * 从 eb_pay_channel 读取本驱动支付插件配置
     *
     * 架构豁免说明：支付驱动插件化后配置统一存储于 eb_pay_channel.config_fields
     * （app\services\driver\pay\PayChannelServices 负责读写），本类位于 driver 第三方
     * 驱动底座，driver 层无 DAO 体系，故此处 Db 直查为合规做法（同本类 getPemPath 模式），
     * 禁止"修复"为 app 层 DAO
     * @return array<string,string>
     */
    protected function channelConfig(): array
    {
        try {
            $channel = Db::name('pay_channel')->where('channel_key', 'weixin_v3')->where('is_del', 0)->find();
        } catch (\Throwable $e) {
            return [];
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
        return $config;
    }

    /**
     * 从 V3 支付渠道配置获取证书路径。
     *
     * V2 与 V3 使用相同的字段名，但证书路径属于各自 eb_pay_channel.config_fields，
     * 不能回退到全局 system_pem，否则会把 V2 证书带入 V3 签名。
     * @param string $name
     * @return string
     */
    public function getPemPath(string $name): string
    {
        // 证书路径只从 V3 渠道读取，不读取 V2 或应用配置。
        $payment = $this->channelConfig();
        $path = trim((string)($payment[$name] ?? ''));
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
     * 校验微信支付商户私钥，避免把空路径或普通文本传给 openssl_sign。
     *
     * @param string $path
     * @return void
     */
    protected function assertPrivateKey(string $path): void
    {
        if ($path === '' || !is_file($path) || !is_readable($path)) {
            throw new PayException('微信支付商户私钥文件不存在，请重新上传未加密的 apiclient_key.pem');
        }

        $privateKey = @file_get_contents($path);
        if ($privateKey === false || trim($privateKey) === '' || @openssl_pkey_get_private($privateKey) === false) {
            throw new PayException('微信支付商户私钥无效，请重新上传未加密的 apiclient_key.pem');
        }
    }

    /**
     * 创建订单返回支付参数
     * @param string $orderId
     * @param string $totalFee
     * @param string $attach
     * @param string $body
     * @param string $detail
     * @param array $options
     * @return array|false|mixed|string
     */
    public function create(string $orderId, string $totalFee, string $attach, string $body, string $detail, array $options = [])
    {
        $this->authSetPayType($options);
        $options['payment_context'] = $this->getPaymentContext($options);
        if (($options['payment_context']['mode'] ?? '') !== 'mini_shop') {
            $this->getPayClient($options['payment_context'])->setExpireTime((int)($options['expire_time'] ?? 0));
        }

        switch ($this->payType) {
            case 'NATIVE':
                $res = $this->getPayClient()->nativePay($orderId, $totalFee, $body, $attach);
                $res['invalid'] = time() + 60;
                // 支付二维码中间 logo：站点公用配置，允许 driver 直接 sys_config 读取
                $res['logo'] = (string)sys_config('wap_login_logo');
                return $res;
            case 'APP':
                return $this->getPayClient()->appPay($orderId, $totalFee, $body, $attach);
            case 'JSAPI':
                if (empty($options['openid'])) {
                    throw new PayException('缺少openid');
                }
                if (($options['platform'] ?? '') === 'wechat_mini') {
                    if ($options['payment_context']['mode'] === 'mini_shop') {
                        return WechatV3PaySdk::newJsPay($options['openid'], $orderId, $totalFee, $attach, $body, $detail, $options);
                    }
                    return $this->getPayClient()->miniprogPay($options['openid'], $orderId, $totalFee, $body, $attach);
                }
                return $this->getPayClient()->jsapiPay($options['openid'], $orderId, $totalFee, $body, $attach);
            case 'h5':
                return $this->getPayClient()->h5Pay($orderId, $totalFee, $body, $attach);
            default:
                throw new PayException('微信支付:支付类型错误');
        }
    }

    /**
     * @param string $openid
     * @param string $orderId
     * @param string $amount
     * @param array $options
     * @return mixed
     */
    public function merchantPay(string $openid, string $orderId, string $amount, array $options = [])
    {
        return $this->getPayClient()->setType($options['type'])->batches(
            $orderId,
            $amount,
            $options['batch_name'],
            $options['batch_remark'],
            [
                [
                    'out_detail_no' => $orderId,
                    'transfer_amount' => $amount,
                    'transfer_remark' => $options['batch_remark'],
                    'openid' => $openid
                ]
            ]
        );
    }

    public function merchantPayNew($type, $order_id, $transfer_scene_id, $openid, $user_name, $transfer_amount, $transfer_remark, $notify_url, $user_recv_perception, $transfer_scene_report_infos)
    {
        return $this->getPayClient()->setType($type)->transferBills(
            $order_id,
            $transfer_scene_id,
            $openid,
            $user_name,
            $transfer_amount,
            $transfer_remark,
            $notify_url,
            $user_recv_perception,
            $transfer_scene_report_infos
        );
    }

    public function queryTransferBills($order_id)
    {
        return $this->getPayClient()->queryTransferBills((string)$order_id);
    }

    /**
     * 查询旧批次转账明细，受理状态不可作为到账成功。
     * @param string $orderNo 原转账单号
     * @return array
     */
    public function queryBatchTransfer(string $orderNo): array
    {
        return $this->getPayClient()->queryBatchTransfer($orderNo);
    }

    /**
     * 发起退款
     * @param string $outTradeNo
     * @param array $options
     * @return mixed
     */
    public function refund(string $outTradeNo, array $options = [])
    {
        $context = $options['payment_context'] ?? [];
        if (($context['mode'] ?? '') === 'mini_shop') {
            return WechatV3PaySdk::refund((string)($options['order_id'] ?? $outTradeNo), $options);
        }
        return $this->getPayClient($context)->refund($outTradeNo, $options);
    }

    /**
     * 查询退款
     * @param string $outTradeNo
     * @param string|null $outRequestNo
     * @param array $other
     * @return mixed
     */
    public function queryRefund(string $outTradeNo, ?string $outRequestNo = null, array $other = [])
    {
        $context = $other['payment_context'] ?? [];
        if (($context['mode'] ?? '') === 'mini_shop') {
            return WechatV3PaySdk::queryRefund((string)($other['order_id'] ?? ''), $outRequestNo ?? $outTradeNo, $context);
        }
        return $this->getPayClient($context)->queryRefund($outTradeNo);
    }

    /**
     * 查询原支付结果。
     * @param string $orderNo 原订单号
     * @param array $context 原支付信息
     * @return array
     */
    public function queryPayment(string $orderNo, array $context): array
    {
        return ($context['mode'] ?? '') === 'mini_shop' ? WechatV3PaySdk::getOrder($orderNo, $context)
            : $this->getPayClient($context)->queryOrder($orderNo);
    }

    /**
     * 关闭未支付订单。
     * @param string $orderNo 原订单号
     * @param array $context 原支付信息
     * @return bool
     */
    public function closePayment(string $orderNo, array $context): bool
    {
        if (($context['mode'] ?? '') === 'mini_shop') {
            $result = WechatV3PaySdk::getOrder($orderNo, $context);
            if (empty($result['transaction_id']) && !empty($context['expires_at'])
                && time() > (int)$context['expires_at'] + 60) return true;
            throw new PayException('新版小程序订单需等待支付过期并查询确认后取消');
        }
        return $this->getPayClient($context)->closeOrder($orderNo);
    }

    /**
     * @param string $type 回调类型（预留，V3统一处理）
     * @return mixed|\think\Response
     */
    public function handleNotify(string $type = '')
    {
        return $this->getPayClient()->handleNotify(function ($message, $eventType) {
            if ($eventType === 'REFUND.SUCCESS') {
                $merchantId = (string)($message['mchid'] ?? $message['sp_mchid'] ?? '');
                $subMerchantId = (string)($message['sub_mchid'] ?? '');
                $expectedSubMerchant = !empty($this->payment['mer_type'])
                    ? trim((string)($this->payment['pay_sub_merchant_id'] ?? '')) : '';
                if (($message['refund_status'] ?? '') !== 'SUCCESS'
                    || $merchantId === ''
                    || $merchantId !== trim((string)($this->payment['pay_weixin_mchid'] ?? ''))
                    || $subMerchantId !== $expectedSubMerchant) {
                    return false;
                }
                // 验签解密后只传递退款核对字段，不记录收款账户等个人信息。
                return Event::until('Notify', [[
                    'event_type' => $eventType,
                    'out_refund_no' => $message['out_refund_no'] ?? '',
                    'out_trade_no' => $message['out_trade_no'] ?? '',
                    'refund_id' => $message['refund_id'] ?? '',
                    'refund_status' => 'SUCCESS',
                    'mchid' => $merchantId,
                    'sub_mchid' => $subMerchantId,
                    'amount' => (array)($message['amount'] ?? []),
                ], self::WEIXIN_PAY_CHANNEL_KEY]);
            }
            if ($eventType === 'TRANSACTION.SUCCESS') {
                $merchantId = (string)($message['mchid'] ?? $message['sp_mchid'] ?? '');
                $subMerchantId = (string)($message['sub_mchid'] ?? '');
                $expectedSubMerchant = !empty($this->payment['mer_type'])
                    ? trim((string)($this->payment['pay_sub_merchant_id'] ?? '')) : '';
                if (($message['trade_state'] ?? '') !== 'SUCCESS' || $merchantId === ''
                    || $merchantId !== trim((string)($this->payment['pay_weixin_mchid'] ?? ''))
                    || $subMerchantId !== $expectedSubMerchant || empty($message['transaction_id'])) {
                    return false;
                }
                $data = [
                    'attach' => $message['attach'] ?? '',
                    'out_trade_no' => $message['out_trade_no'] ?? '',
                    'transaction_id' => $message['transaction_id'],
                    'payment_mode' => 'v3',
                    'appid' => $message['sub_appid'] ?? $message['appid'] ?? $message['sp_appid'] ?? '',
                    'openid' => $message['payer']['sub_openid'] ?? $message['payer']['openid'] ?? '',
                    'mchid' => $merchantId,
                    'sub_mchid' => $subMerchantId,
                    'amount' => (array)($message['amount'] ?? []),
                ];
                return Event::until('Notify', [$data, self::WEIXIN_PAY_CHANNEL_KEY]);
            }
            return true;
        });
    }

    public function handleTransferNotify()
    {
        return $this->getPayClient()->handleTransferNotify(function ($message, $eventType) {
            if ($eventType === 'MCHTRANSFER.BILL.FINISHED'
                || ($message['state'] ?? '') === 'SUCCESS'
                || ($message['transfer_state'] ?? '') === 'SUCCESS') {
                $data = [
                    'out_bill_no' => $message['out_bill_no'] ?? '',
                    'transfer_bill_no' => $message['transfer_bill_no'] ?? '',
                    'state' => $message['state'] ?? $message['transfer_state'] ?? '',
                    'fail_reason' => $message['fail_reason'] ?? '',
                    'mchid' => $message['mchid'] ?? '',
                    'appid' => $message['appid'] ?? '',
                    'openid' => $message['openid'] ?? '',
                    'transfer_amount' => $message['transfer_amount'] ?? null,
                ];
                return Event::until('Notify', [$data, self::WEIXIN_PAY_CHANNEL_KEY]);
            }
            return true;
        });
    }
}
