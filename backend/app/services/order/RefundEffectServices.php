<?php
declare(strict_types=1);

namespace app\services\order;

use app\dao\order\AfterSaleDao;
use app\dao\order\OrderCenterDao;
use app\dao\order\RefundEffectDao;
use app\dao\order\RefundOrderDao;
use app\model\order\OrderCenter;
use app\model\order\RefundOrder;
use app\services\pay\PayServices;
use app\services\statistic\CapitalFlowServices;
use app\services\user\UserBillServices;
use app\services\user\UserMoneyServices;
use app\services\user\UserServices;
use crmeb\basic\BaseService;
use crmeb\trade\order\aftersale\AfterSaleHandlerInterface;
use crmeb\trade\order\aftersale\RefundEffectContextInterface;
use crmeb\services\pay\PaymentDispatch;
use think\facade\Log;

/**
 * 退款本地效果执行器。
 *
 * 平台级账务由本服务维护；库存、权益和履约动作委托给产品售后处理器。
 */
class RefundEffectServices extends BaseService implements RefundEffectContextInterface
{
    /** @var RefundEffectDao */
    protected RefundEffectDao $effectDao;
    /** @var RefundOrderDao */
    protected RefundOrderDao $refundDao;
    /** @var AfterSaleDao */
    protected AfterSaleDao $afterSaleDao;
    /** @var int 当前退款效果上下文 */
    protected int $currentRefundOrderId = 0;

    /**
     * 初始化退款持久化依赖。
     * @return void
     */
    protected function initialize(): void
    {
        $this->effectDao = $this->app->make(RefundEffectDao::class);
        $this->refundDao = $this->app->make(RefundOrderDao::class);
        $this->afterSaleDao = $this->app->make(AfterSaleDao::class);
    }

    /**
     * 在退款事务内幂等执行一次本地效果。
     * @param string $effectType 效果类型
     * @param string $effectKey 业务键
     * @param array $payload 效果数据
     * @param callable $callback 执行动作
     * @return void
     */
    public function once(string $effectType, string $effectKey, array $payload, callable $callback): void
    {
        if ($this->currentRefundOrderId <= 0) {
            throw new \LogicException('退款效果上下文尚未初始化');
        }
        $effectType = trim($effectType);
        $effectKey = trim($effectKey);
        if ($effectType === '' || $effectKey === '') {
            throw new \InvalidArgumentException('退款效果类型和业务键不能为空');
        }

        $effect = $this->effectDao->getEffect($this->currentRefundOrderId, $effectType, $effectKey);
        if ($effect && ($effect['status'] ?? '') === 'completed') {
            return;
        }
        if (!$effect) {
            $effect = $this->effectDao->save([
                'refund_order_id' => $this->currentRefundOrderId,
                'effect_type' => $effectType,
                'effect_key' => $effectKey,
                'status' => 'processing',
                'payload' => $payload,
                'last_error' => '',
                'add_time' => time(),
                'update_time' => time(),
            ]);
        } else {
            $this->effectDao->update((int)$effect['id'], [
                'status' => 'processing',
                'payload' => $payload,
                'last_error' => '',
                'update_time' => time(),
            ]);
        }

        $callback();
        $this->effectDao->update((int)$effect['id'], [
            'status' => 'completed',
            'last_error' => '',
            'update_time' => time(),
        ]);
    }

    /**
     * 执行支付提交前的产品准备阶段。该阶段和效果日志同事务提交，崩溃后可安全重入。
     */
    public function prepare(
        array $refundOrder,
        array $afterSale,
        array $order,
        array $items,
        AfterSaleHandlerInterface $handler
    ): void {
        $this->transaction(function () use ($refundOrder, $afterSale, $order, $items, $handler): void {
            $locked = $this->refundDao->getByIdForUpdate((int)$refundOrder['id']);
            if (!$locked || !in_array($locked['state'], [RefundOrder::STATE_PENDING, RefundOrder::STATE_RETRYABLE_FAILED], true)) {
                return;
            }
            $this->currentRefundOrderId = (int)$locked['id'];
            $handler->beforeRefundSubmit($this, $locked->toArray(), $afterSale, $order, $items);
        });
    }

    /**
     * 将渠道已成功的退款应用到本地。
     */
    public function apply(
        array $refundOrder,
        array $afterSale,
        array $order,
        array $items,
        AfterSaleHandlerInterface $handler
    ): array {
        $result = $this->transaction(function () use ($refundOrder, $afterSale, $order, $items, $handler): array {
            // 与创建退款统一按售后 -> 中台订单 -> 退款单顺序锁定，不同售后共享中台订单锁。
            $lockedSale = $this->afterSaleDao->getByIdForUpdate((int)$afterSale['id']);
            $lockedCenter = $this->app->make(OrderCenterDao::class)->getByIdForUpdate((int)$refundOrder['order_center_id']);
            if (!$lockedSale || !$lockedCenter) {
                throw new \RuntimeException('退款关联的售后单或订单不存在');
            }
            $afterSale = $lockedSale->toArray();
            $order = $lockedCenter->toArray();
            $locked = $this->refundDao->getByIdForUpdate((int)$refundOrder['id']);
            if (!$locked) {
                throw new \RuntimeException('退款单不存在');
            }
            if ($locked['state'] === RefundOrder::STATE_COMPLETED) {
                return $locked->toArray();
            }
            if (!in_array($locked['state'], [
                RefundOrder::STATE_CHANNEL_SUCCEEDED,
                RefundOrder::STATE_LOCAL_FAILED,
                RefundOrder::STATE_APPLYING,
            ], true)) {
                throw new \RuntimeException('退款渠道尚未成功，不能执行本地效果');
            }

            $this->refundDao->update((int)$locked['id'], [
                'state' => RefundOrder::STATE_APPLYING,
                'last_error' => '',
                'update_time' => time(),
            ]);
            $this->currentRefundOrderId = (int)$locked['id'];
            $lockedArray = $locked->toArray();

            $projectedTotal = bcadd(
                $this->refundDao->sumCompletedByOrderCenterId((int)$locked['order_center_id']),
                (string)$locked['amount'], 2
            );
            $normalOrder = ($afterSale['product_type'] ?? '') === 'normal'
                && in_array((string)($order['activity_type'] ?? ''), ['', 'normal'], true);
            if ($normalOrder) {
                // 先收回赠送奖励，再返还抵扣积分，避免用本次返还的用户本金补扣旧赠积分。
                $this->app->make(OrderRefundRewardServices::class)->reconcile($order, $projectedTotal);
            }

            $this->applyPlatformEffects($lockedArray, $afterSale, $order);
            $handler->applyRefundEffects($this, $lockedArray, $afterSale, $order, $items);

            $now = time();
            // 后台手工填写的退款流水号优先；未填写时才回退渠道退款号/系统退款单号
            $refundContext = (array)($lockedArray['context'] ?? []);
            $manualTradeNo = trim((string)($refundContext['refund_trade_no'] ?? ''));
            $refundTradeNo = $manualTradeNo !== ''
                ? $manualTradeNo
                : (string)($locked['channel_refund_id'] ?: $locked['refund_no']);
            // 退款型售后终态为 refunded（已退款 tab）；换货/补发/维修走 complete() 才是 completed
            $this->afterSaleDao->update((int)$afterSale['id'], [
                'status' => 'refunded',
                'actual_refund_price' => (string)$locked['amount'],
                'refund_type' => (string)$locked['refund_method'],
                'refund_trade_no' => $refundTradeNo,
                'refund_time' => $now,
                'complete_time' => $now,
            ]);

            $handlerContext = $refundContext;
            $handlerContext['order_no'] = (string)$afterSale['order_no'];
            $handlerContext['refund_no'] = (string)$locked['refund_no'];
            $handlerContext['total_refund_amount'] = $projectedTotal;
            $handlerContext['is_full_refund'] = bccomp($projectedTotal, (string)($order['pay_price'] ?? '0.00'), 2) >= 0;
            $this->once('product_refund_finalize', (string)$afterSale['product_type'], [
                'after_sale_no' => (string)$afterSale['after_sale_no'],
            ], function () use ($handler, $afterSale, $locked, $handlerContext): void {
                $handler->afterRefund(
                    (string)$afterSale['after_sale_no'],
                    (float)$locked['amount'],
                    $handlerContext
                );
                $handler->afterComplete((string)$afterSale['after_sale_no']);
            });

            $this->refundDao->update((int)$locked['id'], [
                'state' => RefundOrder::STATE_COMPLETED,
                'completed_at' => $now,
                'next_retry_time' => 0,
                'last_error' => '',
                'update_time' => $now,
            ]);

            $total = $this->refundDao->sumCompletedByOrderCenterId((int)$locked['order_center_id']);
            $isFullRefund = bccomp($total, (string)($order['pay_price'] ?? '0.00'), 2) >= 0;
            $payStatus = $isFullRefund
                ? OrderCenter::PAY_FULL_REFUND
                : OrderCenter::PAY_PARTIAL_REFUND;
            $targetStatus = $isFullRefund
                ? OrderCenter::STATUS_REFUNDED
                : (string)($afterSale['before_status'] ?: OrderCenter::STATUS_PROCESSING);
            // 仅部分退款恢复售后前状态；全额退款必须保留已退款终态。
            if (!$isFullRefund && in_array($targetStatus, [OrderCenter::STATUS_AFTER_SALE, OrderCenter::STATUS_REFUNDING, OrderCenter::STATUS_REFUNDED], true)) {
                $targetStatus = OrderCenter::STATUS_PROCESSING;
            }
            /** @var OrderCenterServices $centerServices */
            $centerServices = $this->app->make(OrderCenterServices::class);
            $centerServices->syncStatus((string)$afterSale['order_no'], $targetStatus, [
                'refund_status' => OrderCenter::REFUND_SUCCESS,
                'refund_price' => $total,
                'refund_time' => $now,
                'pay_status' => $payStatus,
            ]);

            if ($normalOrder && $isFullRefund) {
                $this->once('coupon_restore', 'order:' . (int)$order['id'], [], function () use ($order): void {
                    $this->app->make(RefundExecutorServices::class)->rollbackCoupon($order);
                });
            }
            if ($normalOrder && !$this->effectDao->getEffect((int)$locked['id'], 'refund_success_event', 'success')) {
                $this->effectDao->save([
                    'refund_order_id' => (int)$locked['id'], 'effect_type' => 'refund_success_event',
                    'effect_key' => 'success', 'status' => 'pending', 'payload' => [],
                    'last_error' => '', 'add_time' => $now, 'update_time' => $now,
                ]);
            }

            $result = $this->refundDao->getByIdForUpdate((int)$locked['id']);
            return $result ? $result->toArray() : $lockedArray;
        });
        $this->deliverSuccess((int)$refundOrder['id']);
        return $result;
    }

    /**
     * 在资金事务之外发送已完成退款事件；失败只保留待办，不改变退款成功状态。
     * @param int $refundId 退款单ID
     * @return void
     */
    public function deliverSuccess(int $refundId): void
    {
        $claimed = null;
        $token = '';
        try {
            $token = bin2hex(random_bytes(16));
            $claimed = $this->transaction(function () use ($refundId, $token): ?array {
                $refund = $this->refundDao->getByIdForUpdate($refundId);
                if (!$refund || $refund['state'] !== RefundOrder::STATE_COMPLETED) {
                    return null;
                }
                $effect = $this->effectDao->getEffect($refundId, 'refund_success_event', 'success');
                if (!$effect || $effect['status'] === 'completed'
                    || ($effect['status'] === 'processing' && (int)$effect['update_time'] + 300 > time())) {
                    return null;
                }
                $this->effectDao->update((int)$effect['id'], [
                    'status' => 'processing', 'payload' => ['delivery_token' => $token], 'update_time' => time(),
                ]);
                return ['effect_id' => (int)$effect['id'], 'refund' => $refund->toArray()];
            });
            if (!$claimed) {
                return;
            }
            $refund = $claimed['refund'];
            $order = $this->app->make(OrderCenterDao::class)->get((int)$refund['order_center_id']);
            if (!$order) {
                throw new \RuntimeException('退款成功事件的订单不存在');
            }
            $eventOrder = $order->toArray();
            $eventOrder['order_id'] = (string)$refund['order_no'];
            $eventOrder['refund_price'] = (string)$refund['amount'];
            PaymentDispatch::replay(fn () => event('OrderRefundSuccess', [
                $eventOrder,
                ['order_id' => (string)$refund['order_no'], 'refund_price' => (string)$refund['amount'],
                    'refund_no' => (string)$refund['refund_no'], 'refund_order_id' => $refundId],
            ]));
            $this->effectDao->finishSuccessEvent($claimed['effect_id'], $token, [
                'status' => 'completed', 'last_error' => '', 'update_time' => time(),
            ]);
        } catch (\Throwable $e) {
            // 数据库写回也可能失败；租约到期后恢复，不能回退已提交的资金状态。
            try {
                if ($claimed) {
                    $this->effectDao->finishSuccessEvent($claimed['effect_id'], $token, [
                        'status' => 'pending', 'last_error' => '事件发送失败：' . get_class($e), 'update_time' => time(),
                    ]);
                }
                Log::error('[RefundEvent] 成功事件待恢复 refund_id=' . $refundId . ' type=' . get_class($e));
            } catch (\Throwable $recoveryError) {
                Log::error('[RefundEvent] 待办写回失败 refund_id=' . $refundId . ' type=' . get_class($recoveryError));
            }
        }
    }

    /**
     * 恢复已完成退款的成功事件，不再执行渠道退款。
     * @param int $limit 批次数量
     * @return void
     */
    public function recoverSuccessEvents(int $limit = 100): void
    {
        foreach ($this->effectDao->getPendingSuccessEvents(time(), $limit) as $row) {
            $this->deliverSuccess((int)$row['refund_order_id']);
        }
    }

    /**
     * 退款成功日志属于本地效果，通知重试时不重复记录。
     * @param int $refundId 退款单ID
     * @param callable $callback 本地日志写入
     * @return void
     */
    public function recordSuccessLog(int $refundId, callable $callback): void
    {
        $this->transaction(function () use ($refundId, $callback): void {
            $refund = $this->refundDao->getByIdForUpdate($refundId);
            if (!$refund || $refund['state'] !== RefundOrder::STATE_COMPLETED) {
                throw new \RuntimeException('退款尚未完成，不能写成功日志');
            }
            $this->currentRefundOrderId = $refundId;
            $this->once('refund_success_log', 'success', [], $callback);
        });
    }

    /**
     * 执行退款本金、抵扣积分和平台账务效果。
     * @param array $refundOrder 退款单
     * @param array $afterSale 售后单
     * @param array $order 中台订单
     * @return void
     */
    protected function applyPlatformEffects(array $refundOrder, array $afterSale, array $order): void
    {
        $amount = (string)$refundOrder['amount'];
        $uid = (int)$refundOrder['uid'];
        $orderCenterId = (int)$refundOrder['order_center_id'];
        $localBalance = $refundOrder['refund_method'] === 'balance' || $refundOrder['pay_channel'] === 'yue';
        if ($localBalance && bccomp($amount, '0.00', 2) > 0) {
            $this->once('balance_refund', 'uid:' . $uid, ['amount' => $amount], function () use ($uid, $amount, $orderCenterId): void {
                /** @var UserMoneyServices $moneyServices */
                $moneyServices = $this->app->make(UserMoneyServices::class);
                $moneyServices->moneyIncome('pay_product_refund', $uid, $amount, $orderCenterId);
            });
        }

        $refundIntegral = (int)($refundOrder['refund_integral'] ?? 0);
        if ($refundIntegral > 0) {
            $this->once('integral_refund', 'uid:' . $uid, ['integral' => $refundIntegral], function () use ($uid, $refundIntegral, $orderCenterId): void {
                /** @var UserBillServices $billServices */
                $billServices = $this->app->make(UserBillServices::class);
                // link_id 统一存中台订单ID（后台积分记录按它回查订单号展示在「关联订单」列），单号不写入备注
                $billServices->billIntegralIncome(
                    'pay_product_integral_back',
                    $uid,
                    $refundIntegral,
                    $orderCenterId,
                    '售后退款返还积分'
                );
            });
        }

        // 资金流水只记录真实资金流出：退回余额（refund_method=balance）或余额支付订单退款
        // 属于站内结转，钱没有离开平台，不记流水
        $flowPayType = (string)($order['pay_type'] ?? ($refundOrder['pay_channel'] ?? ''));
        /** @var PayServices $payServices */
        $payServices = $this->app->make(PayServices::class);
        if (!$localBalance && bccomp($amount, '0.00', 2) > 0 && $payServices->isCashChannel($flowPayType)) {
            $this->once('capital_flow', (string)$refundOrder['refund_no'], ['amount' => $amount], function () use ($order, $amount): void {
                /** @var UserServices $userServices */
                $userServices = $this->app->make(UserServices::class);
                $user = $userServices->get((int)($order['uid'] ?? 0));
                $flowOrder = $order;
                $flowOrder['nickname'] = $user['nickname'] ?? '';
                $flowOrder['phone'] = $user['phone'] ?? '';
                $flowOrder['refund_price'] = $amount;
                $flowOrder['order_id'] = $order['order_no'] ?? '';
                /** @var CapitalFlowServices $flowServices */
                $flowServices = $this->app->make(CapitalFlowServices::class);
                $flowServices->setFlow($flowOrder, 'refund');
            });
        }

        $this->once('invoice_refund_mark', 'order:' . $orderCenterId, [], function () use ($orderCenterId): void {
            /** @var OrderInvoiceServices $invoiceServices */
            $invoiceServices = $this->app->make(OrderInvoiceServices::class);
            $invoiceServices->update(['order_id' => $orderCenterId], ['is_refund' => 1]);
        });
    }
}
