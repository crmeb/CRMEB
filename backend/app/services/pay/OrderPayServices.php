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

namespace app\services\pay;

use app\services\PlatformDetector;
use app\services\user\MemberOrderServices;
use app\dao\order\OrderCenterDao;
use crmeb\exceptions\ApiException;
use crmeb\services\CacheService;
use crmeb\utils\Str;

/**
 * 订单发起支付
 * 平台判断统一由 PlatformDetector 处理，支付类型从 eb_pay_channel_platform 读取
 * Class OrderPayServices
 * @package app\services\pay
 */
class OrderPayServices
{
    /**
     * @var PayServices
     */
    protected $payServices;

    public function __construct(PayServices $services)
    {
        $this->payServices = $services;
    }

    /**
     * 发起支付前处理
     * @param array $orderInfo 订单信息
     * @param string $channelKey 支付方式标识（channel_key: weixin/alipay/allinpay 等）
     * @param array $options 调用方传入的额外参数
     * @return array
     */
    public function beforePay(array $orderInfo, string $channelKey, array $options = [])
    {
        if ($orderInfo['paid']) {
            throw new ApiException('订单已支付');
        }
        app()->make(PayRecordServices::class)->assertChannel((string)($orderInfo['order_id'] ?? $orderInfo['order_no']), $channelKey);
        // 线下收银订单字段兼容映射（eb_offline_receipt 无 pay_price/money 列）
        if (($orderInfo['type'] ?? 0) == 3) {
            $orderInfo['pay_price'] = $orderInfo['paid_price'] ?? 0;
            $orderInfo['money'] = $orderInfo['total_price'] ?? 0;
        }
        if ($orderInfo['pay_price'] <= 0) {
            throw new ApiException('无需支付');
        }

        // 自动识别当前平台
        $platform = $options['platform'] ?? PlatformDetector::detect();
        $options['platform'] = $platform;
        $options['payer_uid'] = (int)($options['payer_uid'] ?? $orderInfo['uid']);

        // 是否从微信端发起（用于通联/支付宝的特殊处理）
        $fromWechat = in_array($channelKey, [PayServices::WEIXIN_PAY, PayServices::ALLIN_PAY])
            && ($platform === 'wechat_mini' || $platform === 'wechat_mp');

        // 根据 channel_key 准备支付参数
        switch ($channelKey) {
            case PayServices::ALLIN_PAY:
                if ($fromWechat) {
                    $options['wechat'] = true;
                }
                break;
            case PayServices::ALIAPY_PAY:
                if ($fromWechat) {
                    $options['returnUrl'] = sys_config('site_url') . '/pages/goods/order_pay_status/index?order_id=' . $orderInfo['order_id'];
                }
                break;
        }

        // 构建订单描述
        $siteName = sys_config('site_name');
        if (isset($orderInfo['member_type'])) {
            // 会员订单
            $body = Str::substrUTf8($siteName . '--' . $orderInfo['member_type'], 20);
            $successAction = 'member';
            /** @var MemberOrderServices $memberOrderServices */
            $memberOrderServices = app()->make(MemberOrderServices::class);
            if (!in_array($channelKey, ['weixin', 'weixin_v3'], true)) $memberOrderServices->update($orderInfo['id'], ['pay_type' => $channelKey]);
        } elseif (($orderInfo['type'] ?? 0) == 3) {
            // 线下收银订单：订单号存 order_no 列，支付方式不落库（eb_offline_receipt 无 pay_type 列）
            $body = Str::substrUTf8($siteName . '--线下收银', 20);
            $successAction = 'offline_scan';
            $orderInfo['order_id'] = $orderInfo['order_no'];
        } else {
            // 从订单中台 extra_info.items 组装商品标题（旧 cart_info 表已废弃）
            /** @var OrderCenterDao $orderCenterDao */
            $orderCenterDao = app()->make(OrderCenterDao::class);
            $centerOrder = $orderCenterDao->get(['order_no' => $orderInfo['order_id']], ['extra_info']);
            $body = '';
            if ($centerOrder) {
                $extra = $centerOrder['extra_info'] ?? [];
                if (is_string($extra)) {
                    $extra = json_decode($extra, true) ?: [];
                }
                foreach ($extra['items'] ?? [] as $item) {
                    $name = $item['store_name'] ?? '';
                    if ($name !== '') {
                        $body .= $name . '|';
                    }
                }
                $body = $body !== '' ? substr($body, 0, -1) : '';
            }
            $body = Str::substrUTf8($siteName . '--' . $body, 20);
            $successAction = 'product';
            if (!in_array($channelKey, ['weixin', 'weixin_v3'], true)) $orderCenterDao->update($orderInfo['id'], ['pay_type' => $channelKey]);
        }

        if (!$body) {
            throw new ApiException('网站名称配置未填写,无法支付');
        }

        // 发起支付（PayServices 内部从 eb_pay_channel_platform 读取 pay_type，传入驱动）
        $jsConfig = $this->payServices->pay($channelKey, $orderInfo['order_id'], $orderInfo['pay_price'], $successAction, $body, $options);

        // 支付发起后处理返回参数
        $payInfo = $this->afterPay($orderInfo, $jsConfig, $channelKey, $platform);
        $statusType = $this->payServices->getPayStatusType($channelKey, $platform);

        return [
            'status' => $statusType,
            'payInfo' => $payInfo,
        ];
    }

    /**
     * 支付发起后处理返回参数
     * @param array $order 订单信息
     * @param mixed $jsConfig 支付返回数据
     * @param string $channelKey 支付方式标识
     * @param string $platform 当前端标识
     * @return array
     */
    public function afterPay(array $order, $jsConfig, string $channelKey, string $platform): array
    {
        $payKey = md5($order['order_id']);
        $payUrl = '';

        switch ($channelKey) {
            case PayServices::ALIAPY_PAY:
                CacheService::set($payKey, ['order_id' => $order['order_id'], 'other_pay_type' => false], 300);
                break;
            case PayServices::ALLIN_PAY:
                if ($platform === 'wechat_mini' || $platform === 'wechat_mp') {
                    $payUrl = $this->payServices->getChannelUrl(PayServices::ALLIN_PAY, 'h5_union_pay');
                }
                break;
            case PayServices::WEIXIN_PAY:
                if (isset($jsConfig['mweb_url'])) {
                    $jsConfig['h5_url'] = $jsConfig['mweb_url'];
                }
                break;
        }

        return [
            'jsConfig' => $jsConfig,
            'oid' => $order['id'],
            'order_id' => $order['order_id'],
            'pay_key' => $payKey,
            'pay_url' => $payUrl,
        ];
    }
}
