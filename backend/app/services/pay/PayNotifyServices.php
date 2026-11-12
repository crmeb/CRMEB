<?php
declare(strict_types=1);

namespace app\services\pay;

use app\services\user\OfflineReceiptServices;
use app\services\user\MemberOrderServices;
use app\services\user\UserRechargeServices;
use app\services\order\OrderSuccessServices;
use crmeb\exceptions\PayException;
use think\facade\Log;

/**
 * 支付回调统一校验和事务分发。
 */
class PayNotifyServices
{
    /**
     * 商品支付通知。
     * @param string|null $order_id 订单号
     * @param string|null $trade_no 流水
     * @param string $payType 渠道
     * @param array $other 可信通知信息
     * @return bool
     */
    public function wechatProduct(?string $order_id = null, ?string $trade_no = null, string $payType = 'weixin', array $other = []): bool
    {
        return $this->process('product', $order_id, $trade_no, $payType, $other);
    }

    /**
     * 充值支付通知。
     * @param string|null $order_id 订单号
     * @param string|null $trade_no 流水
     * @param string $payType 渠道
     * @param array $other 可信通知信息
     * @return bool
     */
    public function wechatUserRecharge(?string $order_id = null, ?string $trade_no = null, string $payType = 'weixin', array $other = []): bool
    {
        return $this->process('user_recharge', $order_id, $trade_no, $payType, $other);
    }

    /**
     * 会员支付通知。
     * @param string|null $order_id 订单号
     * @param string|null $trade_no 流水
     * @param string $payType 渠道
     * @param array $other 可信通知信息
     * @return bool
     */
    public function wechatMember(?string $order_id = null, ?string $trade_no = null, string $payType = 'weixin', array $other = []): bool
    {
        return $this->process('member', $order_id, $trade_no, $payType, $other);
    }

    /**
     * 收银支付通知。
     * @param string|null $order_id 订单号
     * @param string|null $trade_no 流水
     * @param string $payType 渠道
     * @param array $other 可信通知信息
     * @return bool
     */
    public function wechatOfflineScan(?string $order_id = null, ?string $trade_no = null, string $payType = 'weixin', array $other = []): bool
    {
        return $this->process('offline_scan', $order_id, $trade_no, $payType, $other);
    }

    /**
     * 按支付合同上的业务把通知交给对应核心服务。
     * @param string $kind 钩子业务，日志用
     * @param string|null $orderNo 订单号
     * @param string|null $tradeNo 流水
     * @param string $channel 渠道
     * @param array $other 可信通知信息
     * @return bool
     */
    protected function process(string $kind, ?string $orderNo, ?string $tradeNo, string $channel, array $other): bool
    {
        try {
            if (!$orderNo || !$tradeNo) {
                return false;
            }
            return $this->paid($orderNo, $tradeNo, $channel, $other);
        } catch (\Throwable $e) {
            Log::error('[PayNotify] 支付处理失败 ' . json_encode([
                'order_no' => $orderNo,
                'trade_no' => $tradeNo,
                'kind' => $kind,
                'channel' => $channel,
                'exception' => get_class($e),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            return false;
        }
    }

    /**
     * 付款通知进入对应的核心服务。
     * @param string $orderNo 订单号
     * @param string $tradeNo 微信流水
     * @param string $channel 渠道
     * @param array $other 可信通知信息
     * @return bool
     */
    public function paid(string $orderNo, string $tradeNo, string $channel, array $other): bool
    {
        $record = app()->make(PayRecordServices::class)->find($orderNo);
        $other['trade_no'] = $tradeNo;
        $other['pay_type'] = $channel;
        switch ((string)($record['business_type'] ?? '')) {
            case 'product':
                return app()->make(OrderSuccessServices::class)->paySuccess(
                    ['order_no' => $orderNo, 'order_id' => $orderNo], $channel, $other
                );
            case 'member':
                return app()->make(MemberOrderServices::class)->paySuccess(['order_id' => $orderNo], $channel, $other);
            case 'user_recharge':
                return app()->make(UserRechargeServices::class)->rechargeSuccess($orderNo, $other);
            case 'offline_scan':
                return app()->make(OfflineReceiptServices::class)->paySuccess(['order_no' => $orderNo], $channel, $other);
            default:
                throw new PayException('支付业务不存在');
        }
    }
}
