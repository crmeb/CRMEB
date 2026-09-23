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
declare (strict_types=1);

namespace app\services\pay;

use crmeb\exceptions\ApiException;
use crmeb\services\CacheService;
use crmeb\services\driver\DriverRegistry;
use crmeb\services\pay\PayChannelConfig;

/**
 * 支付统一入口
 * 所有支付方式映射、配置均从 driver/pay/ 各子目录的 plugin.json 自动读取
 * 支付代码中不做任何平台判断（isH5/isApp 等）
 * Class PayServices
 * @package app\services\pay
 */
class PayServices
{
    public const REFUND_SUCCESS = 'SUCCESS';
    public const REFUND_PROCESSING = 'PROCESSING';
    public const REFUND_FAILED = 'FAILED';
    public const REFUND_UNKNOWN = 'UNKNOWN';
    //支付方式 channel_key 常量（供业务层引用，非硬编码逻辑）
    const WEIXIN_PAY = 'weixin';
    const YUE_PAY = 'yue';
    const OFFLINE_PAY = 'offline';
    const ALIAPY_PAY = 'alipay';
    const ALLIN_PAY = 'allinpay';
    const FRIEND = 'friend';
    const BANK = 'bank';

    /**
     * 获取支付方式名称映射
     * 从 eb_pay_channel 表动态读取，管理员新增/修改支付方式后自动生效
     * @return array channel_key => channel_name
     */
    public static function getPayType(): array
    {
        try {
            /** @var \app\model\driver\pay\PayChannel $model */
            $model = app()->make(\app\model\driver\pay\PayChannel::class);
            $list = $model->where('is_del', 0)
                ->where('status', 1)
                ->order('sort', 'desc')
                ->column('channel_name', 'channel_key');
            return $list ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    protected const CACHE_KEY = 'pay_channel_info';
    protected const CACHE_TTL = 3600;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @param string $key
     * @param $value
     * @return $this
     */
    public function setOption(string $key, $value)
    {
        $this->options[$key] = $value;
        return $this;
    }

    /**
     * @param array $value
     * @return $this
     */
    public function setOptions(array $value)
    {
        $this->options = $value;
        return $this;
    }

    /**
     * @param string $key
     * @param null $default
     * @return mixed|null
     */
    protected function getOption(string $key, $default = null)
    {
        return $this->options[$key] ?? $default;
    }

    /**
     * 发起支付
     * @param string $channelKey 支付方式标识（channel_key）
     * @param string $orderId
     * @param string $price
     * @param string $successAction
     * @param string $body
     * @param array $options 调用方传入的额外参数（如 openid, platform）
     * @return array|string
     */
    public function pay(string $channelKey, string $orderId, string $price, string $successAction, string $body, array $options = [])
    {
        try {
            $driverName = $this->resolveDriverName($channelKey);

            // 从 plugin.json 的 default_options 自动读取配置并合并
            $defaultOpts = $this->buildDefaultOptions($channelKey);
            $options = $options + $defaultOpts;

            // 从 eb_pay_channel_platform 读取当前端的支付类型，传入驱动
            // 驱动不再自行判断平台，由数据库配置决定
            $platform = $options['platform'] ?? \app\services\PlatformDetector::detect();
            $options['pay_type'] = $this->resolvePayType($channelKey, $platform);
            $options['platform'] = $platform;

            /** @var \crmeb\services\pay\Pay $pay */
            $pay = app()->make('\\crmeb\\services\\pay\\Pay', [$driverName]);

            if (in_array($channelKey, ['weixin', 'weixin_v3'], true)) {
                $options['payment_context'] = $pay->getPaymentContext($options);
                $options['payment_context']['payer_uid'] = (int)($options['payer_uid'] ?? 0);
                $records = app()->make(PayRecordServices::class);
                $previous = $records->find($orderId);
                $source = $records->source(strtolower($successAction), $orderId);
                $ttl = strtolower($successAction) === 'product' ? (int)ceil((float)sys_config('order_cancel_time', 0) * 3600) : 7200;
                $expires = (int)($previous['context']['expires_at'] ?? ((int)$source['add_time'] + ($ttl > 0 ? $ttl : 7200)));
                if ($expires <= time() + 60) throw new ApiException('订单支付时间已过期，请重新下单');
                $options['payment_context']['expires_at'] = $expires;
                $options['time_expire'] = date('YmdHis', $expires);
                $options['expire_time'] = $expires;
                app()->make(PayRecordServices::class)->prepare($orderId, strtolower($successAction), $price, $options['payment_context']);
                if (strtolower($successAction) === 'product') {
                    $this->saveV3PaymentContext($orderId, $options['payment_context']);
                }
            }
            try {
                $result = $pay->create($orderId, $price, $successAction, $body, '', $options);
                if (isset($options['payment_context']) && is_array($result)) {
                    if ($options['pay_type'] === 'JSAPI') $result['paymentMode'] = $options['payment_context']['mode'];
                    $urlKey = isset($result['mweb_url']) ? 'mweb_url' : (isset($result['h5_url']) ? 'h5_url' : '');
                    $returnUrl = (string)($options['quitUrl'] ?? '');
                    if ($urlKey !== '' && $returnUrl !== '') {
                        $target = parse_url($returnUrl);
                        $site = parse_url((string)sys_config('site_url'));
                        if ($target && $site && in_array($target['scheme'] ?? '', ['https', 'http'], true)
                            && ($target['host'] ?? '') === ($site['host'] ?? '') && ($target['port'] ?? null) === ($site['port'] ?? null)) {
                            $result[$urlKey] .= (str_contains($result[$urlKey], '?') ? '&' : '?')
                                . http_build_query(['redirect_url' => $returnUrl], '', '&', PHP_QUERY_RFC3986);
                        }
                    }
                }
                return $result;
            } finally {
                if (isset($options['payment_context'])) app()->make(PayRecordServices::class)->finishCreation($orderId);
            }

        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'api unauthorized rid') !== false) {
                throw new ApiException('请在微信支付配置中将小程序商户号选择改为商户号绑定');
            }
            throw new ApiException($e->getMessage());
        }
    }

    /**
     * 保存 V3 原支付信息，保留订单扩展数据且不保存密钥。
     * @param string $orderNo 订单号
     * @param array $context 支付信息
     * @return void
     */
    protected function saveV3PaymentContext(string $orderNo, array $context): void
    {
        /** @var \app\dao\order\OrderCenterDao $dao */
        $dao = app()->make(\app\dao\order\OrderCenterDao::class);
        $order = $dao->get(['order_no' => $orderNo], ['id', 'pay_status', 'extra_info']);
        if (!$order || (int)$order['pay_status'] !== 0) {
            throw new ApiException('订单不存在或已支付');
        }
        $extra = $order['extra_info'] ?? [];
        if (is_string($extra)) {
            $extra = json_decode($extra, true) ?: [];
        }
        // 同一订单不能留下两种商户模式的有效支付参数，否则退款无法确定归属。
        $previous = $extra['payment'] ?? [];
        foreach (['channel_key', 'platform', 'mode', 'mch_id', 'sub_mch_id', 'app_id', 'openid'] as $key) {
            if (!empty($previous[$key]) && $previous[$key] !== ($context[$key] ?? '')) {
                throw new ApiException('该订单已发起其他商户或小程序模式的支付，请使用原支付配置');
            }
        }
        if ($previous === $context) {
            return;
        }
        $extra['payment'] = $context;
        if (!$dao->updateUnpaidPaymentContext((int)$order['id'], $order['extra_info'] ?? null,
            json_encode($extra, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR))) {
            throw new \RuntimeException('订单支付信息已变化，请重新发起支付');
        }
    }

    /**
     * 从原订单提取 V3 退款信息，旧普通 V3 订单仍使用商户订单号。
     * @param array $orderInfo 原订单
     * @return array
     */
    protected function getV3PaymentContext(array $orderInfo): array
    {
        $extra = $orderInfo['extra_info'] ?? [];
        if (is_string($extra)) {
            $extra = json_decode($extra, true) ?: [];
        }
        $context = $extra['payment'] ?? [];
        return $context;
    }

    /**
     * 根据 channel_key 解析 Pay 管理器的驱动标识
     * 支持 version_switch 动态切换驱动版本（如微信 v2/v3）
     * @param string $channelKey
     * @return string
     */
    public function resolveDriverName(string $channelKey): string
    {
        $channels = $this->getChannelInfo();

        if (!isset($channels[$channelKey])) {
            throw new ApiException('不支持的支付方式: ' . $channelKey);
        }

        $channel = $channels[$channelKey];

        // 检查是否有版本切换配置
        $versionSwitch = $channel['version_switch'] ?? null;
        if ($versionSwitch && isset($versionSwitch['config_key'], $versionSwitch['compare_value'], $versionSwitch['target_driver'])) {
            if ((string)sys_config($versionSwitch['config_key']) === (string)$versionSwitch['compare_value']) {
                // 检查目标驱动是否存在
                if (isset($channels['_drivers'][$versionSwitch['target_driver']])) {
                    return $versionSwitch['target_driver'];
                }
            }
        }

        return $channel['driver_key'];
    }

    /**
     * 解析当前端的支付类型
     * 优先从 eb_pay_channel_platform 读取（管理员可覆盖），不存在则从 plugin.json 的 pay_type_map 读取默认值
     * @param string $channelKey 支付方式标识
     * @param string $platform 端标识
     * @return string 支付类型（JSAPI/APP/NATIVE/h5 等），无记录返回空字符串
     */
    public function resolvePayType(string $channelKey, string $platform): string
    {
        // 优先从数据库读取（管理员可覆盖）
        try {
            $record = \app\model\driver\pay\PayChannelPlatform::where('channel_key', $channelKey)
                ->where('platform', $platform)
                ->where('status', 1)
                ->find();

            if ($record) {
                return (string)$record['pay_type'];
            }
        } catch (\Exception $e) {
            // 表不存在时忽略
        }

        // 从 plugin.json 的 pay_type_map 读取默认值
        $channelInfo = $this->getChannelInfoByKey($channelKey);
        return $channelInfo['pay_type_map'][$platform] ?? '';
    }

    /**
     * 获取支付返回类型标识（从 plugin.json 动态读取，不做平台判断）
     * @param string $channelKey
     * @param string $platform 当前平台标识
     * @return string
     */
    public function getPayStatusType(string $channelKey, string $platform = ''): string
    {
        $info = $this->getChannelInfoByKey($channelKey);
        $statusType = $info['pay_status_type'] ?? ($channelKey . '_pay');

        // 字符串：直接返回
        if (is_string($statusType)) {
            return $statusType;
        }

        // 数组：按平台动态返回
        if (is_array($statusType)) {
            if ($platform && isset($statusType[$platform])) {
                return $statusType[$platform];
            }
            return $statusType['default'] ?? ($channelKey . '_pay');
        }

        return $channelKey . '_pay';
    }

    /**
     * 获取指定支付方式的扩展信息
     * @param string $channelKey
     * @return array
     */
    public function getChannelInfoByKey(string $channelKey): array
    {
        $channels = $this->getChannelInfo();
        return $channels[$channelKey] ?? [];
    }

    /**
     * 判断支付方式是否为第三方在线支付渠道（微信/支付宝/通联等）
     * 渠道集合由驱动注册中心扫描 driver/pay 插件动态获得，新增支付驱动即自动纳入，调用方无需硬编码渠道枚举
     * @param string $payType 支付方式标识
     * @return bool
     */
    public function isThirdPartyChannel(string $payType): bool
    {
        return $this->getChannelInfoByKey($payType) !== [];
    }

    /**
     * 判断支付方式是否为真实资金支付渠道（第三方在线 + 余额 + 线下）
     * 余额/线下为系统固有渠道（非驱动插件，用常量判定）；排除 0 元单等无资金流的支付方式
     * @param string $payType 支付方式标识
     * @return bool
     */
    public function isRealPaymentChannel(string $payType): bool
    {
        // 系统固有渠道：余额支付 / 线下支付，不随驱动扩展，常量判定合法
        if ($payType === self::YUE_PAY || $payType === self::OFFLINE_PAY) {
            return true;
        }
        // 第三方渠道：从驱动注册中心动态获取
        return $this->isThirdPartyChannel($payType);
    }

    /**
     * 根据驱动标识获取驱动信息
     * @param string $driverKey
     * @return array
     */
    public function getDriverInfoByKey(string $driverKey): array
    {
        $channels = $this->getChannelInfo();
        return $channels['_drivers'][$driverKey] ?? [];
    }

    /**
     * 根据 channel_key 获取驱动标识（用于退款/转账等场景）
     * 直接从 plugin.json 的 driver_key 读取，不做版本切换
     * @param string $channelKey
     * @return string
     */
    public function getDriverByChannelKey(string $channelKey): string
    {
        $info = $this->getChannelInfoByKey($channelKey);
        return $info['driver_key'] ?? $channelKey;
    }

    /**
     * 统一退款接口
     * 根据 channel_key 动态路由到对应支付驱动进行退款
     * @param string $channelKey 支付方式标识（channel_key）
     * @param string $orderId 订单号
     * @param array $refundData 退款数据（refund_price, pay_price, trade_no 等）
     * @param array $orderInfo 订单信息（用于特殊场景判断）
     * @return mixed
     */
    public function refund(string $channelKey, string $orderId, array $refundData, array $orderInfo = [])
    {
        try {
            $driverName = $this->resolveDriverName($channelKey);

            /** @var \crmeb\services\pay\Pay $pay */
            $pay = app()->make('\\crmeb\\services\\pay\\Pay', [$driverName]);

            $no = $orderId;
            if (!empty($refundData['trade_no'])) {
                $no = $refundData['trade_no'];
                $refundData['type'] = 'trade_no';
            }

            if (in_array($channelKey, ['weixin', 'weixin_v3'], true)) {
                $context = app()->make(PayRecordServices::class)->find($orderId)['context'] ?? $this->getV3PaymentContext($orderInfo);
                if ($context && ($context['channel_key'] ?? '') !== $channelKey) throw new ApiException('原支付渠道与退款渠道不一致');
                $refundData['payment_context'] = $context;
                $refundData['order_id'] = $orderId;
                if (($context['mode'] ?? '') === 'mini_shop') {
                    $no = $orderId;
                    $refundData['pay_new_weixin_open'] = true;
                    $refundData['open_id'] = $context['openid'] ?? '';
                    $refundData['trade_no'] = $context['transaction_id'] ?? '';
                    $refundData['refund_no'] = $refundData['refund_id'] ?? '';
                } elseif (!empty($context['transaction_id'])) {
                    $no = $context['transaction_id'];
                    $refundData['type'] = 'trade_no';
                }
            }

            if ($channelKey === 'weixin' && empty($refundData['payment_context'])) {
                if (!empty($orderInfo['is_channel']) && $orderInfo['is_channel'] == 1) {
                    if (!empty($orderInfo['trade_no'])) {
                        $refundData['trade_no'] = $orderInfo['trade_no'];
                        $refundData['type'] = 'trade_no';
                        $no = $orderInfo['trade_no'];
                    }
                    // 按实际支付渠道读取配置，V2 与 V3 不共享小程序商户号模式。
                    $refundData['pay_new_weixin_open'] = PayChannelConfig::get($channelKey)['pay_new_weixin_open'] ?? '';
                } else {
                    $refundData['wechat'] = true;
                }
            }

            if ($channelKey === 'alipay') {
                $refundData['totalAmount'] = $refundData['pay_price'] ?? '';
            }

            if ($channelKey === 'allinpay') {
                $refundData['order_id'] = $orderId;
                // 通联驱动的第一个参数会作为 reqsn，必须使用系统固定退款号。
                $no = (string)($refundData['refund_id'] ?? $orderId);
            }

            return $pay->refund($no, $refundData);

        } catch (\Exception $e) {
            throw new ApiException($e->getMessage());
        }
    }

    /**
     * 使用固定退款号提交退款，并将不同驱动的返回值标准化。
     * 驱动抛出的异常一律先视为 UNKNOWN；调用方必须查询原退款号，不能换号重提。
     */
    public function submitRefund(
        string $channelKey,
        string $orderId,
        string $refundNo,
        string $amount,
        string $payAmount,
        array $orderInfo = []
    ): array {
        try {
            $raw = $this->refund($channelKey, $orderId, [
                'refund_price' => $amount,
                'pay_price' => $payAmount,
                'refund_id' => $refundNo,
                'trade_no' => $orderInfo['trade_no'] ?? '',
            ], $orderInfo);
            return $this->normalizeRefundResult($raw, true);
        } catch (\Throwable $e) {
            return [
                'status' => self::REFUND_UNKNOWN,
                'channel_refund_id' => '',
                'response' => [],
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * 查询固定退款号的渠道状态。
     */
    public function queryRefundStatus(string $channelKey, string $orderId, string $refundNo, array $other = []): array
    {
        try {
            $driverName = $this->resolveDriverName($channelKey);
            if (in_array($channelKey, ['weixin', 'weixin_v3'], true)) {
                $other['payment_context'] = app()->make(PayRecordServices::class)->find($orderId)['context']
                    ?? $this->getV3PaymentContext($other['order_info'] ?? []);
                $other['order_id'] = $orderId;
                unset($other['order_info']);
            }
            /** @var \crmeb\services\pay\Pay $pay */
            $pay = app()->make('\\crmeb\\services\\pay\\Pay', [$driverName]);
            if ($channelKey === self::ALIAPY_PAY) {
                $raw = $pay->queryRefund($orderId, $refundNo, $other);
            } else {
                $raw = $pay->queryRefund($refundNo, $refundNo, ['type' => 'out_refund_no'] + $other);
            }
            if ($raw === null) {
                return [
                    'status' => self::REFUND_UNKNOWN,
                    'channel_refund_id' => '',
                    'response' => [],
                    'message' => '当前支付驱动未返回可判定的退款查询结果',
                ];
            }
            return $this->normalizeRefundResult($raw, false);
        } catch (\Throwable $e) {
            return [
                'status' => self::REFUND_UNKNOWN,
                'channel_refund_id' => '',
                'response' => [],
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * @param mixed $raw
     */
    protected function normalizeRefundResult($raw, bool $submit): array
    {
        if (is_object($raw)) {
            $raw = json_decode(json_encode($raw, JSON_UNESCAPED_UNICODE), true) ?: [];
        }
        $response = $this->sanitizeRefundResponse(is_array($raw) ? $raw : []);
        $candidate = strtoupper((string)(
            $response['status']
            ?? $response['refund_status']
            ?? $response['refundStatus']
            ?? $response['refund_status_0']
            ?? $response['trade_status']
            ?? ''
        ));
        if ($candidate === '' && (string)($response['code'] ?? '') === '10000') {
            if ($submit && ($response['fundChange'] ?? $response['fund_change'] ?? '') === 'Y') $candidate = 'SUCCESS';
            if (!$submit && bccomp((string)($response['refundAmount'] ?? $response['refund_amount'] ?? '0'), '0', 2) > 0) $candidate = 'SUCCESS';
        }
        if ($candidate === '' && isset($response['trxstatus'])) {
            $candidate = ['0000' => 'SUCCESS', '2000' => 'PROCESSING', '2008' => 'PROCESSING'][(string)$response['trxstatus']] ?? 'FAILED';
        }
        if ($submit && $candidate === '' && isset($response['result_code'])) {
            $candidate = strtoupper((string)$response['result_code']) === 'SUCCESS' ? 'PROCESSING' : 'FAILED';
        }

        if (in_array($candidate, ['SUCCESS', 'REFUND_SUCCESS', 'TRADE_SUCCESS'], true)) {
            $status = self::REFUND_SUCCESS;
        } elseif (in_array($candidate, ['PROCESSING', 'PENDING', 'CHANGE', 'NOTSURE'], true)) {
            $status = self::REFUND_PROCESSING;
        } elseif (in_array($candidate, ['CLOSED', 'ABNORMAL', 'FAILED', 'FAIL'], true)) {
            $status = self::REFUND_FAILED;
        } elseif ($submit) {
            // 没有明确渠道结果不能视为到账。
            $status = self::REFUND_UNKNOWN;
        } else {
            $status = self::REFUND_UNKNOWN;
        }

        return [
            'status' => $status,
            'channel_refund_id' => (string)($response['refund_id'] ?? $response['trade_no'] ?? ''),
            'response' => $response,
            'message' => (string)($response['message'] ?? $response['msg'] ?? ''),
        ];
    }

    protected function sanitizeRefundResponse(array $response): array
    {
        $sanitized = [];
        foreach ($response as $key => $value) {
            if (preg_match('/secret|password|private|token|certificate|api[_-]?key/i', (string)$key)) {
                $sanitized[$key] = '***';
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeRefundResponse($value);
            } elseif (is_scalar($value) || $value === null) {
                $sanitized[$key] = $value;
            }
        }
        return $sanitized;
    }

    /**
     * 动态检查支付方式是否可用
     * 根据 channel_key 从数据库获取支付渠道状态
     * @param string $payType 支付方式标识
     * @return bool
     */
    public function checkPayType(string $payType): bool
    {
        /** @var \app\services\driver\pay\PayChannelServices $payChannelServices */
        $payChannelServices = app()->make(\app\services\driver\pay\PayChannelServices::class);

        $channelInfo = $this->getChannelInfoByKey($payType);
        if (empty($channelInfo)) {
            return false;
        }

        $channelKey = $channelInfo['channel_key'] ?? $payType;

        $res = $payChannelServices->getChannelStatus($channelKey);

        return $res;
    }

    /**
     * 获取指定 platform 下当前启用的驱动标识
     * 用于提现/转账/回调等场景，需要知道当前使用的是哪个版本
     * @param string $platform 所属平台（wechat/alipay/allinpay）
     * @return string 驱动标识，无匹配返回空字符串
     */
    public function getActiveDriverByPlatform(string $platform): string
    {
        try {
            $record = \app\model\driver\pay\PayChannel::where('platform', $platform)
                ->where('status', 1)
                ->order('sort', 'desc')
                ->find();

            if ($record) {
                $info = $this->getChannelInfoByKey($record['channel_key']);
                return $info['driver_key'] ?? '';
            }
        } catch (\Exception $e) {
            // 表不存在时忽略
        }
        return '';
    }

    /**
     * 检查指定 platform 下当前启用的驱动是否具备某项能力
     * @param string $platform 所属平台（wechat/alipay/allinpay）
     * @param string $capability 能力标识（merchant_pay_new / query_transfer / transfer_notify 等）
     * @return bool
     */
    public function hasCapability(string $platform, string $capability): bool
    {
        $driverKey = $this->getActiveDriverByPlatform($platform);
        if (!$driverKey) {
            return false;
        }

        // 从 _drivers 中按 driver_key 找到对应的 info
        $channels = $this->getChannelInfo();
        $info = $channels['_drivers'][$driverKey] ?? [];
        $capabilities = $info['capabilities'] ?? [];

        return in_array($capability, $capabilities);
    }

    /**
     * 获取支付方式的特殊 URL
     * @param string $channelKey
     * @param string $urlKey
     * @return string
     */
    public function getChannelUrl(string $channelKey, string $urlKey): string
    {
        $info = $this->getChannelInfoByKey($channelKey);
        return $info['pay_urls'][$urlKey] ?? '';
    }

    /**
     * 获取所有支付方式名称映射
     * @return array
     */
    public function getPayTypeMap(): array
    {
        $channels = $this->getChannelInfo();
        $map = [];
        foreach ($channels as $key => $info) {
            if ($key[0] === '_') continue;
            $map[$key] = $info['name'] ?? $key;
        }
        return $map;
    }

    /**
     * 从 plugin.json 的 default_options 自动读取配置，构建支付默认参数
     * @param string $channelKey
     * @return array
     */
    protected function buildDefaultOptions(string $channelKey): array
    {
        $info = $this->getChannelInfoByKey($channelKey);
        $defaultOpts = $info['default_options'] ?? [];

        if (empty($defaultOpts)) {
            return [];
        }

        $options = [];
        $channelConfig = PayChannelConfig::get($channelKey);
        foreach ($defaultOpts as $configKey) {
            $value = array_key_exists($configKey, $channelConfig) ? $channelConfig[$configKey]
                : ($configKey === 'pay_new_weixin_open' ? false : sys_config($configKey));
            if ($value !== null) {
                // 布尔类型转换
                if ($value === '1' || $value === 'true') {
                    $options[$configKey] = true;
                } elseif ($value === '0' || $value === 'false') {
                    $options[$configKey] = false;
                } else {
                    $options[$configKey] = $value;
                }
            }
        }

        return $options;
    }

    /**
     * 处理支付回调（控制器只传 type，所有逻辑在此处理）
     * @param string $type 回调URL中的type参数（即channel_key）
     * @return string|\think\Response
     */
    public function handleNotify(string $type): string|\think\Response
    {
        // 1. 解析 channel_key（type 直接对应 channel_key）
        $channelKey = $this->resolveChannelKeyByType($type);

        // 2. 获取驱动标识（支持版本切换）
        $driverKey = $this->resolveDriverName($channelKey);

        // 3. 从支付渠道配置表获取所有配置值
        $configValues = $this->getChannelConfigValues($channelKey);

        // 4. 调用驱动的 handleNotify，传递原始 type 参数
        /** @var \crmeb\services\pay\Pay $pay */
        $pay = app()->make(\crmeb\services\pay\Pay::class, [$driverKey, $configValues]);
        $response = $pay->handleNotify($type);

        // 保留驱动的 HTTP 状态码与响应头，使验签/业务失败可被支付平台识别并重试。
        if ($response instanceof \think\Response) {
            return $response;
        }

        return (string) $response;
    }

    /**
     * 根据回调type解析channel_key
     * @param string $type
     * @return string
     */
    protected function resolveChannelKeyByType(string $type): string
    {
        // 直接匹配 channel_key
        $channels = $this->getChannelInfo();
        if (isset($channels[$type])) {
            return $type;
        }

        // 通联支付回调（allin开头）-> allinpay
        if (strpos($type, 'allin') === 0) {
            return 'allinpay';
        }

        // 微信V3回调:驱动 notify_url 历史上使用 v3wechat,渠道 key 为 weixin_v3,
        // 保持商户平台已配置的回调 URL 不变,在此做别名映射
        if ($type === 'v3wechat') {
            return 'weixin_v3';
        }

        throw new ApiException('不支持的回调类型: ' . $type);
    }

    /**
     * 从驱动注册中心获取所有支付扩展信息（含缓存）
     * @return array
     */
    protected function getChannelInfo(): array
    {
        $cached = CacheService::get(self::CACHE_KEY, null);
        if (is_array($cached) && $this->isValidChannelCache($cached)) {
            return $cached;
        }

        $channels = $this->buildChannelInfo();
        CacheService::set(self::CACHE_KEY, $channels, self::CACHE_TTL);
        return $channels;
    }

    /**
     * 校验支付渠道缓存是否包含可用的渠道映射。
     * 旧版本曾缓存空数组，不能把该值当作有效结果长期复用。
     * @param array $cached
     * @return bool
     */
    protected function isValidChannelCache(array $cached): bool
    {
        foreach ($cached as $key => $info) {
            if (is_string($key) && str_starts_with($key, '_')) {
                continue;
            }
            if (is_array($info) && !empty($info['channel_key']) && !empty($info['driver_key'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * 清除支付渠道信息缓存
     * @return bool
     */
    public function clearChannelInfoCache(): bool
    {
        return CacheService::delete(self::CACHE_KEY);
    }

    /**
     * 构建支付渠道信息
     * @return array
     */
    protected function buildChannelInfo(): array
    {
        $channels = [];

        // 历史安装数据通过 eb_pay_channel.driver 保存驱动目录名，作为清单字段缺失时的兜底关联。
        $driverChannels = [];
        try {
            $driverChannels = \app\model\driver\pay\PayChannel::where('is_del', 0)
                ->column('driver', 'channel_key');
        } catch (\Throwable $e) {
            // 支付渠道表不可用时仍允许已安装驱动通过清单元数据注册。
        }

        // 统一走驱动注册中心，并为旧安装记录补齐支付驱动清单中的扩展字段。
        foreach (DriverRegistry::scan('pay') as $driverKey => $info) {
            $dirName = (string)($info['dir_name'] ?? '');
            $metadata = PayChannelConfig::getDriverMetadata($dirName);
            foreach ($metadata as $key => $value) {
                if (!array_key_exists($key, $info) || $info[$key] === '' || $info[$key] === [] || $info[$key] === null) {
                    $info[$key] = $value;
                }
            }

            $channelKey = (string)($info['channel_key'] ?? '');
            if ($channelKey === '' && $dirName !== '') {
                foreach ($driverChannels as $candidateKey => $candidateDriver) {
                    if ((string)$candidateDriver === $dirName) {
                        $channelKey = (string)$candidateKey;
                        break;
                    }
                }
            }
            if ($channelKey === '') {
                continue;
            }

            $info['channel_key'] = $channelKey;
            $info['driver_key'] = (string)($info['driver_key'] ?? $driverKey);

            // 同一 channel_key 可能有多个驱动（如 weixin 有 v2 和 v3）
            if (!isset($channels[$channelKey])) {
                $channels[$channelKey] = $info;
            }

            // 以 driver_key 为索引记录所有驱动（用于 version_switch）
            $channels['_drivers'][$driverKey] = $info;
        }

        return $channels;
    }

    /**
     * 从支付渠道配置表获取所有配置值（优先从 config_fields，兼容从 system_config）
     * @param string $channelKey
     * @return array
     */
    protected function getChannelConfigValues(string $channelKey): array
    {
        try {
            /** @var PayChannelServices $payChannelServices */
            $payChannelServices = app()->make(PayChannelServices::class);
            return $payChannelServices->getChannelConfigValues($channelKey);
        } catch (\Exception $e) {
            // 表不存在时忽略，返回空数组
            return [];
        }
    }

    /**
     * 从支付渠道配置表获取单个配置值（优先从 config_fields，兼容从 system_config）
     * @param string $channelKey
     * @param string $configKey
     * @return mixed
     */
    protected function getConfigValue(string $channelKey, string $configKey)
    {
        $configValues = $this->getChannelConfigValues($channelKey);
        if (isset($configValues[$configKey])) {
            return $configValues[$configKey];
        }
        return sys_config($configKey);
    }
}
