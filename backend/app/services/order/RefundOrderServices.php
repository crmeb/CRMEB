<?php
declare(strict_types=1);

namespace app\services\order;

use app\dao\order\AfterSaleDao;
use app\dao\order\AfterSaleItemDao;
use app\dao\order\OrderCenterDao;
use app\dao\order\RefundOrderDao;
use app\model\order\OrderCenter;
use app\model\order\RefundOrder;
use app\services\pay\PayServices;
use crmeb\basic\BaseService;
use crmeb\trade\order\aftersale\AfterSaleRuleRegistry;
use think\facade\Log;

/**
 * 退款聚合根服务：负责唯一退款意图、状态机、渠道提交/查询和本地效果恢复。
 */
class RefundOrderServices extends BaseService
{
    protected RefundOrderDao $refundDao;
    protected AfterSaleDao $afterSaleDao;
    protected AfterSaleItemDao $itemDao;
    protected OrderCenterDao $centerDao;
    protected PayServices $payServices;

    protected function initialize(): void
    {
        $this->refundDao = $this->app->make(RefundOrderDao::class);
        $this->afterSaleDao = $this->app->make(AfterSaleDao::class);
        $this->itemDao = $this->app->make(AfterSaleItemDao::class);
        $this->centerDao = $this->app->make(OrderCenterDao::class);
        $this->payServices = $this->app->make(PayServices::class);
    }

    /**
     * 创建或读取唯一退款意图，并立即推进一次。
     */
    public function request(int $afterSaleId, string $amount, array $context = []): array
    {
        if (!is_numeric($amount)) {
            throw new \RuntimeException('退款金额格式错误');
        }
        $amount = bcadd((string)$amount, '0', 2);
        $refund = $this->transaction(function () use ($afterSaleId, $amount, $context) {
            $afterSale = $this->afterSaleDao->getByIdForUpdate($afterSaleId);
            if (!$afterSale) {
                throw new \RuntimeException('售后单不存在');
            }
            $order = $this->centerDao->getByIdForUpdate((int)$afterSale['order_center_id']);
            if (!$order) {
                throw new \RuntimeException('订单不存在，无法退款');
            }

            $existing = $this->refundDao->getByAfterSaleId($afterSaleId, true);
            if ($existing) {
                if (bccomp((string)$existing['amount'], $amount, 2) !== 0
                    || (string)$existing['refund_method'] !== (string)($context['refund_type'] ?? 'original')) {
                    throw new \RuntimeException('该售后单已创建退款意图，不能修改退款金额或退款方式');
                }
                return $existing;
            }

            $this->assertRefundable($afterSale->toArray(), $amount);
            if (bccomp($amount, '0.00', 2) > 0 && !in_array((int)$order['pay_status'], [
                OrderCenter::PAY_PAID,
                OrderCenter::PAY_PARTIAL_REFUND,
            ], true)) {
                throw new \RuntimeException('订单当前支付状态不可退款');
            }
            $reserved = $this->refundDao->sumReservedByOrderCenterId((int)$order['id']);
            if (bccomp(bcadd($reserved, $amount, 2), (string)$order['pay_price'], 2) > 0) {
                throw new \RuntimeException('累计退款金额不能超过订单实付金额');
            }

            $items = $this->itemDao->getByAfterSaleId($afterSaleId);
            if (!$items) {
                $items = $this->materializeLegacyItems($afterSale->toArray());
            }
            $handler = AfterSaleRuleRegistry::getProvider('product_type', (string)$afterSale['product_type']);
            if (!$handler) {
                throw new \RuntimeException('产品类型未注册售后退款处理器');
            }
            $plan = $this->normalizePlan(
                $handler->buildRefundPlan($afterSale->toArray(), $order->toArray(), $items, $context)
            );
            $frozenContext = $context;
            $frozenContext['plan'] = $plan;
            if (json_encode($frozenContext, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) === false) {
                throw new \RuntimeException('退款上下文无法持久化');
            }

            $refundNo = 'RF' . (string)$afterSale['after_sale_no'];
            $refund = $this->refundDao->save([
                'refund_no' => substr($refundNo, 0, 48),
                'after_sale_id' => $afterSaleId,
                'order_center_id' => (int)$order['id'],
                'order_no' => (string)$afterSale['order_no'],
                'uid' => (int)$afterSale['uid'],
                'amount' => $amount,
                'refund_integral' => (int)($afterSale['refund_integral'] ?? 0),
                'refund_method' => (string)($context['refund_type'] ?? 'original'),
                'pay_channel' => (string)($order['pay_type'] ?? ''),
                'state' => RefundOrder::STATE_PENDING,
                'channel_refund_id' => '',
                'channel_response' => [],
                'context' => $frozenContext,
                'attempts' => 0,
                'next_retry_time' => 0,
                'last_error' => '',
                'version' => 0,
                'channel_succeeded_at' => 0,
                'completed_at' => 0,
                'add_time' => time(),
                'update_time' => time(),
            ]);
            $this->afterSaleDao->update($afterSaleId, ['status' => 'refunding']);
            /** @var OrderCenterServices $centerServices */
            $centerServices = $this->app->make(OrderCenterServices::class);
            $centerServices->syncStatus((string)$afterSale['order_no'], OrderCenter::STATUS_REFUNDING, [
                'refund_status' => OrderCenter::REFUND_APPLYING,
            ]);
            return $refund;
        });

        return $this->process((int)$refund['id']);
    }

    /**
     * 推进一张退款单。可由同步请求、队列和恢复任务重复调用。
     */
    public function process(int $refundOrderId): array
    {
        $refund = $this->refundDao->get($refundOrderId);
        if (!$refund) {
            throw new \RuntimeException('退款单不存在');
        }
        $state = (string)$refund['state'];
        if ($state === RefundOrder::STATE_COMPLETED || $state === RefundOrder::STATE_MANUAL_REVIEW) {
            if ($state === RefundOrder::STATE_COMPLETED) {
                $this->app->make(RefundEffectServices::class)->deliverSuccess($refundOrderId);
            }
            return $refund->toArray();
        }
        if (in_array($state, [RefundOrder::STATE_CHANNEL_SUCCEEDED, RefundOrder::STATE_LOCAL_FAILED, RefundOrder::STATE_APPLYING], true)) {
            return $this->applyLocalEffects($refund->toArray());
        }

        $plan = (array)($refund['context']['plan'] ?? []);
        $channelMode = (string)($plan['channel_mode'] ?? 'auto');
        if ($channelMode === 'manual') {
            $this->refundDao->update($refundOrderId, [
                'state' => RefundOrder::STATE_MANUAL_REVIEW,
                'last_error' => '产品退款方案要求人工处理支付渠道',
                'update_time' => time(),
            ]);
            return $this->refundDao->get($refundOrderId)->toArray();
        }

        if (in_array($state, [RefundOrder::STATE_PENDING, RefundOrder::STATE_RETRYABLE_FAILED], true)) {
            $this->prepareProductEffects($refund->toArray());
            $refund = $this->refundDao->get($refundOrderId);
            $state = (string)$refund['state'];
        }

        $isLocal = $channelMode === 'local'
            || $refund['refund_method'] === 'balance'
            || $refund['pay_channel'] === PayServices::YUE_PAY
            || bccomp((string)$refund['amount'], '0.00', 2) === 0;
        if ($isLocal) {
            $this->markChannelSucceeded($refundOrderId, ['status' => PayServices::REFUND_SUCCESS]);
            return $this->applyLocalEffects($this->refundDao->get($refundOrderId)->toArray());
        }

        if (!in_array((string)$refund['pay_channel'], [
            PayServices::WEIXIN_PAY,
            PayServices::ALIAPY_PAY,
            PayServices::ALLIN_PAY,
            'weixin_v3',
        ], true)) {
            $this->refundDao->update($refundOrderId, [
                'state' => RefundOrder::STATE_MANUAL_REVIEW,
                'last_error' => '支付方式不支持自动原路退款：' . $refund['pay_channel'],
                'update_time' => time(),
            ]);
            return $this->refundDao->get($refundOrderId)->toArray();
        }

        $action = $this->claimChannelAction($refundOrderId);
        if ($action === 'wait') {
            return $this->refundDao->get($refundOrderId)->toArray();
        }
        $refund = $this->refundDao->get($refundOrderId)->toArray();
        $order = $this->centerDao->get((int)$refund['order_center_id']);
        if (!$order) {
            throw new \RuntimeException('订单不存在，无法推进退款');
        }
        $orderArray = $order->toArray();
        if ($action === 'submit') {
            $result = $this->payServices->submitRefund(
                (string)$refund['pay_channel'],
                (string)$refund['order_no'],
                (string)$refund['refund_no'],
                (string)$refund['amount'],
                (string)$orderArray['pay_price'],
                $orderArray
            );
        } else {
            $result = $this->payServices->queryRefundStatus(
                (string)$refund['pay_channel'],
                (string)$refund['order_no'],
                (string)$refund['refund_no'],
                ['order_info' => $orderArray]
            );
        }

        $this->persistChannelResult($refundOrderId, $result);
        $updated = $this->refundDao->get($refundOrderId);
        if ($updated && $updated['state'] === RefundOrder::STATE_CHANNEL_SUCCEEDED) {
            return $this->applyLocalEffects($updated->toArray());
        }
        return $updated ? $updated->toArray() : $refund;
    }

    /**
     * 接收驱动已核验的 V2/V3 退款通知，确认原支付身份和金额后复用幂等完成流程。
     * @param array $notify 解密后的退款核对字段
     * @param string $payChannel 可信驱动渠道
     * @return bool 本地处理完成才确认通知，否则由微信重试并由定时任务补偿
     */
    public function handleWechatRefundNotify(array $notify, string $payChannel = 'weixin_v3'): bool
    {
        try {
            if (!in_array($payChannel, [PayServices::WEIXIN_PAY, 'weixin_v3'], true)) {
                return false;
            }
            $mode = $payChannel === PayServices::WEIXIN_PAY ? 'v2' : 'v3';
            $refundOrderId = $this->transaction(function () use ($notify, $payChannel, $mode): int {
                $refundNo = (string)($notify['out_refund_no'] ?? '');
                if ($refundNo === '' || ($notify['refund_status'] ?? '') !== 'SUCCESS'
                    || empty($notify['refund_id'])) {
                    throw new \RuntimeException('退款成功通知缺少必要字段');
                }
                $refund = $this->refundDao->get(['refund_no' => $refundNo]);
                if (!$refund) {
                    throw new \RuntimeException('退款通知对应的退款单不存在');
                }
                $refund = $this->refundDao->getByIdForUpdate((int)$refund['id']);
                $order = $this->centerDao->get((int)$refund['order_center_id']);
                $amount = $notify['amount'] ?? [];
                if (!$order || $refund['pay_channel'] !== $payChannel
                    || $refund['refund_method'] !== 'original'
                    || (string)($notify['out_trade_no'] ?? '') !== (string)$refund['order_no']
                    || !isset($amount['refund'], $amount['total'])
                    || !is_int($amount['refund']) || !is_int($amount['total'])
                    || $amount['refund'] !== (int)bcmul((string)$refund['amount'], '100', 0)
                    || $amount['total'] !== (int)bcmul((string)$order['pay_price'], '100', 0)) {
                    throw new \RuntimeException('退款通知的渠道、订单或金额不一致');
                }
                $extra = $order['extra_info'] ?? [];
                $extra = is_string($extra) ? (json_decode($extra, true) ?: []) : $extra;
                $payment = $extra['payment'] ?? [];
                if (($payment['mode'] ?? $mode) !== $mode
                    || (!empty($payment['mch_id']) && $payment['mch_id'] !== ($notify['mchid'] ?? ''))
                    || (isset($payment['sub_mch_id']) && $payment['sub_mch_id'] !== ($notify['sub_mchid'] ?? ''))
                    || (!empty($refund['channel_refund_id']) && $refund['channel_refund_id'] !== $notify['refund_id'])) {
                    throw new \RuntimeException('退款通知与原支付商户或退款流水不一致');
                }
                // 服务商查单的 sub_appid 为可选字段；缺省时以已验签的子商户与原交易绑定合同。
                $notifyAppId = !empty($payment['sub_mch_id']) ? ($notify['sub_appid'] ?? '') : ($notify['appid'] ?? '');
                if ($mode === 'v2' && (
                    ($notify['payment_mode'] ?? '') !== 'v2'
                    || (!empty($payment['app_id']) && (empty($payment['sub_mch_id']) || $notifyAppId !== '')
                        && $payment['app_id'] !== $notifyAppId)
                    || (!empty($order['trade_no']) && $order['trade_no'] !== ($notify['transaction_id'] ?? ''))
                )) {
                    throw new \RuntimeException('V2退款通知与原支付应用或交易流水不一致');
                }
                $this->markChannelSucceeded((int)$refund['id'], [
                    'channel_refund_id' => $notify['refund_id'],
                    'response' => ['status' => 'SUCCESS', 'out_refund_no' => $refundNo, 'refund_id' => $notify['refund_id']],
                ]);
                return (int)$refund['id'];
            });
            return ($this->process($refundOrderId)['state'] ?? '') === RefundOrder::STATE_COMPLETED;
        } catch (\Throwable $e) {
            Log::error('[RefundNotify] 退款成功通知处理失败：' . $e->getMessage());
            return false;
        }
    }

    /**
     * 主动查询一笔既有 V3 退款并补完本地效果，不重新提交退款。
     * @param string $refundNo 系统退款单号
     * @return array 当前退款状态及渠道查询结果
     */
    public function syncWechatRefund(string $refundNo): array
    {
        $refund = $this->refundDao->get(['refund_no' => $refundNo]);
        if (!$refund || $refund['pay_channel'] !== 'weixin_v3' || $refund['refund_method'] !== 'original') {
            throw new \RuntimeException('未找到对应的微信 V3 原路退款单');
        }
        $id = (int)$refund['id'];
        if (in_array($refund['state'], [RefundOrder::STATE_COMPLETED, RefundOrder::STATE_CHANNEL_SUCCEEDED,
            RefundOrder::STATE_LOCAL_FAILED, RefundOrder::STATE_APPLYING], true)) {
            return $this->process($id);
        }
        $order = $this->centerDao->get((int)$refund['order_center_id']);
        if (!$order) {
            throw new \RuntimeException('原订单不存在');
        }
        $result = $this->payServices->queryRefundStatus('weixin_v3', (string)$refund['order_no'],
            $refundNo, ['order_info' => $order->toArray()]);
        if (($result['status'] ?? '') !== PayServices::REFUND_SUCCESS) {
            return array_merge($refund->toArray(), [
                'query_status' => $result['status'] ?? PayServices::REFUND_UNKNOWN,
                'query_message' => $result['message'] ?? '',
            ]);
        }
        $this->markChannelSucceeded($id, $result);
        return $this->process($id);
    }

    /**
     * 分批恢复未决退款及已完成退款的成功事件。
     * @param int $limit 批次数量
     * @return array
     */
    public function recover(int $limit = 100): array
    {
        $result = ['processed' => 0, 'completed' => 0, 'manual_review' => 0, 'failed' => 0];
        foreach ($this->refundDao->getRecoverable(time(), $limit) as $refund) {
            try {
                $updated = $this->process((int)$refund['id']);
                $result['processed']++;
                if (($updated['state'] ?? '') === RefundOrder::STATE_COMPLETED) {
                    $result['completed']++;
                } elseif (($updated['state'] ?? '') === RefundOrder::STATE_MANUAL_REVIEW) {
                    $result['manual_review']++;
                }
            } catch (\Throwable $e) {
                $result['failed']++;
                Log::error('[RefundRecovery] 退款单 ' . ($refund['refund_no'] ?? $refund['id']) . ' 恢复失败：' . $e->getMessage());
            }
        }
        $this->app->make(RefundEffectServices::class)->recoverSuccessEvents($limit);
        return $result;
    }

    protected function claimChannelAction(int $refundOrderId): string
    {
        return $this->transaction(function () use ($refundOrderId): string {
            $locked = $this->refundDao->getByIdForUpdate($refundOrderId);
            if (!$locked) {
                throw new \RuntimeException('退款单不存在');
            }
            $state = (string)$locked['state'];
            if ($state === RefundOrder::STATE_SUBMITTING) {
                if ((int)$locked['update_time'] + 60 > time()) {
                    return 'wait';
                }
                $this->incrementQueryAttempt($locked);
                return 'query';
            }
            if (in_array($state, [RefundOrder::STATE_UNKNOWN, RefundOrder::STATE_CHANNEL_PROCESSING], true)) {
                $this->incrementQueryAttempt($locked);
                return 'query';
            }
            if (!in_array($state, [RefundOrder::STATE_PENDING, RefundOrder::STATE_RETRYABLE_FAILED], true)) {
                return 'wait';
            }
            $this->refundDao->update($refundOrderId, [
                'state' => RefundOrder::STATE_SUBMITTING,
                'attempts' => (int)$locked['attempts'] + 1,
                'next_retry_time' => 0,
                'last_error' => '',
                'update_time' => time(),
            ]);
            return 'submit';
        });
    }

    protected function incrementQueryAttempt($refund): void
    {
        $this->refundDao->update((int)$refund['id'], [
            'attempts' => (int)$refund['attempts'] + 1,
            'next_retry_time' => 0,
            'update_time' => time(),
        ]);
    }

    protected function persistChannelResult(int $refundOrderId, array $result): void
    {
        $this->transaction(function () use ($refundOrderId, $result): void {
            $current = $this->refundDao->getByIdForUpdate($refundOrderId);
            if (!$current || in_array($current['state'], [RefundOrder::STATE_CHANNEL_SUCCEEDED, RefundOrder::STATE_APPLYING, RefundOrder::STATE_LOCAL_FAILED, RefundOrder::STATE_COMPLETED], true)) {
                return;
            }
            $status = (string)($result['status'] ?? PayServices::REFUND_UNKNOWN);
            if ($status === PayServices::REFUND_SUCCESS) {
                $this->refundDao->update($refundOrderId, [
                    'state' => RefundOrder::STATE_CHANNEL_SUCCEEDED,
                    'channel_response' => (array)($result['response'] ?? []),
                    'channel_refund_id' => (string)($result['channel_refund_id'] ?? ''),
                    'channel_succeeded_at' => time(),
                    'next_retry_time' => 0,
                    'last_error' => '',
                    'update_time' => time(),
                ]);
                return;
            }
            $state = $status === PayServices::REFUND_PROCESSING
                ? RefundOrder::STATE_CHANNEL_PROCESSING
                : ($status === PayServices::REFUND_FAILED ? RefundOrder::STATE_RETRYABLE_FAILED : RefundOrder::STATE_UNKNOWN);
            $attempts = (int)($current['attempts'] ?? 0);
            $plan = (array)($current['context']['plan'] ?? []);
            if (($plan['allow_auto_recovery'] ?? true) !== true || $attempts >= 8) {
                $state = RefundOrder::STATE_MANUAL_REVIEW;
            }
            $this->refundDao->update($refundOrderId, [
                'state' => $state,
                'channel_response' => (array)($result['response'] ?? []),
                'channel_refund_id' => (string)($result['channel_refund_id'] ?? ''),
                'last_error' => substr((string)($result['message'] ?? ''), 0, 1000),
                'next_retry_time' => $state === RefundOrder::STATE_MANUAL_REVIEW ? 0 : time() + min(3600, 30 * max(1, $attempts)),
                'update_time' => time(),
            ]);
        });
    }

    protected function markChannelSucceeded(int $refundOrderId, array $result): void
    {
        $this->transaction(function () use ($refundOrderId, $result): void {
            $current = $this->refundDao->getByIdForUpdate($refundOrderId);
            if (!$current || in_array($current['state'], [RefundOrder::STATE_APPLYING, RefundOrder::STATE_COMPLETED], true)) {
                return;
            }
            $this->refundDao->update($refundOrderId, [
                'state' => RefundOrder::STATE_CHANNEL_SUCCEEDED,
                'channel_response' => (array)($result['response'] ?? []),
                'channel_refund_id' => (string)($result['channel_refund_id'] ?? ''),
                'channel_succeeded_at' => time(),
                'next_retry_time' => 0,
                'last_error' => '',
                'update_time' => time(),
            ]);
        });
    }

    protected function applyLocalEffects(array $refund): array
    {
        $afterSale = $this->afterSaleDao->get((int)$refund['after_sale_id']);
        $order = $this->centerDao->get((int)$refund['order_center_id']);
        if (!$afterSale || !$order) {
            throw new \RuntimeException('退款关联的售后单或订单不存在');
        }
        $items = $this->itemDao->getByAfterSaleId((int)$refund['after_sale_id']);
        $handler = AfterSaleRuleRegistry::getProvider('product_type', (string)$afterSale['product_type']);
        if (!$handler) {
            throw new \RuntimeException('产品类型未注册售后退款处理器');
        }
        try {
            /** @var RefundEffectServices $effects */
            $effects = $this->app->make(RefundEffectServices::class);
            return $effects->apply($refund, $afterSale->toArray(), $order->toArray(), $items, $handler);
        } catch (\Throwable $e) {
            $this->refundDao->update((int)$refund['id'], [
                'state' => RefundOrder::STATE_LOCAL_FAILED,
                'last_error' => substr($e->getMessage(), 0, 1000),
                'next_retry_time' => time() + 60,
                'update_time' => time(),
            ]);
            throw new \RuntimeException('支付渠道退款已成功，本地业务处理中断，系统将自动恢复：' . $e->getMessage(), 0, $e);
        }
    }

    protected function prepareProductEffects(array $refund): void
    {
        $afterSale = $this->afterSaleDao->get((int)$refund['after_sale_id']);
        $order = $this->centerDao->get((int)$refund['order_center_id']);
        if (!$afterSale || !$order) {
            throw new \RuntimeException('退款关联的售后单或订单不存在');
        }
        $items = $this->itemDao->getByAfterSaleId((int)$refund['after_sale_id']);
        $handler = AfterSaleRuleRegistry::getProvider('product_type', (string)$afterSale['product_type']);
        if (!$handler) {
            throw new \RuntimeException('产品类型未注册售后退款处理器');
        }
        try {
            /** @var RefundEffectServices $effects */
            $effects = $this->app->make(RefundEffectServices::class);
            $effects->prepare($refund, $afterSale->toArray(), $order->toArray(), $items, $handler);
        } catch (\Throwable $e) {
            $attempts = (int)($refund['attempts'] ?? 0) + 1;
            $this->refundDao->update((int)$refund['id'], [
                'state' => $attempts >= 8 ? RefundOrder::STATE_MANUAL_REVIEW : (string)$refund['state'],
                'attempts' => $attempts,
                'last_error' => substr('产品退款准备失败：' . $e->getMessage(), 0, 1000),
                'next_retry_time' => $attempts >= 8 ? 0 : time() + min(3600, 30 * $attempts),
                'update_time' => time(),
            ]);
            throw new \RuntimeException('产品退款准备失败，尚未向支付渠道提交：' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * 校验售后退款状态、金额与实物退货收货凭据。
     * @param array $afterSale 售后单
     * @param string $amount 实退金额
     * @return void
     */
    protected function assertRefundable(array $afterSale, string $amount): void
    {
        $validStatus = [
            'return_refund' => ['receiving'],
            'refund_only' => ['audit_passed', 'receiving'],
            'exchange' => ['receiving'],
            'resend' => ['audit_passed'],
            'repair' => ['repair_complete'],
        ];
        $allowed = $validStatus[$afterSale['type']] ?? ['audit_passed'];
        if (!in_array($afterSale['status'], $allowed, true)) {
            throw new \RuntimeException('当前状态不可执行退款');
        }
        if (($afterSale['product_type'] ?? '') === 'normal'
            && ($afterSale['type'] ?? '') === 'return_refund'
            && (int)($afterSale['receive_time'] ?? 0) <= 0) {
            throw new \RuntimeException('请先确认收到退货再执行退款');
        }
        if (bccomp($amount, '0.00', 2) < 0 || ((int)($afterSale['refund_integral'] ?? 0) <= 0 && bccomp($amount, '0.00', 2) <= 0)) {
            throw new \RuntimeException('退款金额或退款积分必须大于零');
        }
        if (bccomp($amount, (string)($afterSale['refund_price'] ?? '0.00'), 2) > 0) {
            throw new \RuntimeException('实际退款金额不能超过售后申请金额');
        }
    }

    protected function normalizePlan(array $plan): array
    {
        $plan = array_merge([
            'channel_mode' => 'auto',
            'restore_inventory' => true,
            'allow_auto_recovery' => true,
        ], $plan);
        if (!in_array($plan['channel_mode'], ['auto', 'local', 'manual'], true)) {
            throw new \RuntimeException('产品退款方案 channel_mode 无效');
        }
        $plan['restore_inventory'] = (bool)$plan['restore_inventory'];
        $plan['allow_auto_recovery'] = (bool)$plan['allow_auto_recovery'];
        return $plan;
    }

    /**
     * 兼容迁移前已创建但尚未退款的售后单。
     */
    protected function materializeLegacyItems(array $afterSale): array
    {
        $snapshot = (array)($afterSale['product_snapshot'] ?? []);
        $sourceItems = $snapshot['items'] ?? $snapshot;
        $rows = [];
        foreach ((array)$sourceItems as $index => $item) {
            $cartId = (int)($item['cart_id'] ?? 0);
            $rows[] = [
                'after_sale_id' => (int)$afterSale['id'],
                'order_item_key' => $cartId > 0 ? 'cart:' . $cartId : 'legacy:' . $index,
                'cart_id' => $cartId,
                'product_id' => (int)($item['product_id'] ?? 0),
                'sku_unique' => (string)($item['unique'] ?? $item['attr_info']['unique'] ?? ''),
                'product_type' => (string)$afterSale['product_type'],
                'activity_type' => (string)($afterSale['activity_type'] ?? ''),
                'refund_num' => (int)($item['refund_num'] ?? $item['cart_num'] ?? 0),
                'refund_amount' => '0.00',
                'refund_integral' => 0,
                'snapshot' => $item,
                'add_time' => time(),
            ];
        }
        if (!$rows) {
            throw new \RuntimeException('售后商品明细缺失，无法安全执行退款');
        }
        $this->itemDao->saveAll($rows);
        return $this->itemDao->getByAfterSaleId((int)$afterSale['id']);
    }
}
