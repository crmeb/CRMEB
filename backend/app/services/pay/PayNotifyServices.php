<?php
declare(strict_types=1);

namespace app\services\pay;

use app\model\order\OrderCenter;
use app\services\user\OfflineReceiptServices;
use app\services\user\MemberOrderServices;
use app\services\user\UserRechargeServices;
use app\services\order\OrderSuccessServices;
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
     * 原订单锁内验证并完成资金和履约，失败不提交已付状态。
     * @param string $kind 业务类型
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
            $records = app()->make(PayRecordServices::class);
            $result = $records->complete($orderNo, $kind, $tradeNo, function (array $order) use ($records, $kind, $orderNo, $tradeNo, $channel, $other) {
                $channel = $records->validate($order, $kind, $channel, $other);
                if ((int)($order[$kind === 'product' ? 'pay_status' : 'paid'] ?? 0) !== 0) return true;
                if ($kind === 'product' && in_array($order['status_code'] ?? '', [OrderCenter::STATUS_CANCELED, OrderCenter::STATUS_DELETED], true)) {
                    // 已实际扣款的取消订单不能复活，可靠保存原路退款补偿意图。
                    return $records->compensateCanceled($orderNo, $tradeNo);
                }
                $extra = $order['extra_info'] ?? [];
                if (is_string($extra)) $extra = json_decode($extra, true) ?: [];
                $order['payment_context'] = $records->find($orderNo)['context'] ?? $extra['payment'] ?? [];
                $order['payment_context']['transaction_id'] = $tradeNo;
                $order['pay_type'] = $channel;
                $data = ['trade_no' => $tradeNo, 'pay_type' => $channel];
                if ($kind === 'user_recharge') return app()->make(UserRechargeServices::class)->rechargeSuccess($orderNo, $data);
                if ($kind === 'member') return app()->make(MemberOrderServices::class)->paySuccess($order, $channel, $data);
                if ($kind === 'offline_scan') return app()->make(OfflineReceiptServices::class)->paySuccess($order, $channel, $data);
                $order['order_id'] = $order['order_no'];
                $order['paid'] = 0;
                $order['combination_id'] = $order['combination_id'] ?? 0;
                $order['real_name'] = $extra['contact']['name'] ?? '';
                return app()->make(OrderSuccessServices::class)->paySuccess($order, $channel, $data);
            });
            return $result;
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
}
