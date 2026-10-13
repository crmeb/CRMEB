<?php
declare(strict_types=1);

namespace modules\product\virtual\handler;

use crmeb\trade\order\type\BaseOrderTypeAdapter;
use crmeb\trade\order\type\Button;
use crmeb\trade\order\type\OrderFlowNode;
use modules\product\virtual\model\VirtualOrderModel;

/**
 * 网盘商品订单流转定义
 *
 * 网盘商品：无需物流，支付后自动发货，进入待评价
 * 状态流程：pending_payment → pending_review → completed
 * 任意节点可触发：canceled（取消）/ refunded（退款）
 *
 * ═══════════════ 完整状态流转图 ═══════════════
 *
 *   pending_payment  待支付 ──────→ 中台 pending_payment
 *     │ 支付成功 → Event → Listener → Flow::advance('pending_review')
 *     ▼
 *   pending_review  待评价   ──────→ 中台 pending_review
 *     │ 评价完成 → OrderCenterServices::checkOrderOver()
 *     ▼
 *   completed       已完成   ──────→ 中台 completed
 *
 *   任意节点可触发：
 *     → canceled    已取消  ────→ 中台 canceled
 *     → after_sale  售后中  ────→ 中台 after_sale
 *     → refunded    已退款  ────→ 中台 refunded
 */
class VirtualOrderAdapter extends BaseOrderTypeAdapter
{
    // ======================== 扩展状态常量 ========================

    /** 待支付 — 订单刚创建 */
    public const EXT_STATUS_PENDING_PAYMENT = 'pending_payment';
    /** 待评价 — 资源已自动发放，等待用户评价 */
    public const EXT_STATUS_PENDING_REVIEW = 'pending_review';
    /** 已完成 — 用户完成评价 */
    public const EXT_STATUS_COMPLETED = 'completed';
    /** 已取消 — 订单已取消 */
    public const EXT_STATUS_CANCELED = 'canceled';
    /** 已退款 — 订单已退款 */
    public const EXT_STATUS_REFUNDED = 'refunded';

    // ======================== 1. 身份标识 ========================

    public static function getSourceType(): string
    {
        return 'virtual_order';
    }

    public static function getOrderCategory(): string
    {
        return 'goods';
    }

    public static function getOrderCategoryLabel(): string
    {
        return '商品订单';
    }

    public static function getModelClass(): string
    {
        return VirtualOrderModel::class;
    }

    // ======================== 2. 状态树（唯一数据源） ========================

    protected function defineStatusTree(): array
    {
        return [
            // ───── 待支付 ─────
            OrderFlowNode::make(self::EXT_STATUS_PENDING_PAYMENT)
                ->centerStatus(self::STATUS_PENDING_PAYMENT)
                ->label('待支付')
                ->adminButtons([
                    Button::make('取消订单', 'cancelOrder'),
                ])
                ->userButtons([
                    Button::make('取消订单', 'cancelOrder'),
                    Button::primary('去支付', 'payOrder'),
                ]),

            // ───── 待评价 ─────
            OrderFlowNode::make(self::EXT_STATUS_PENDING_REVIEW)
                ->centerStatus(self::STATUS_PENDING_REVIEW)
                ->label('待评价')
                ->adminButtons([])
                ->userButtons([
                    Button::primary('去评价', 'goComment'),
                ]),

            // ───── 已完成（终态） ─────
            OrderFlowNode::make(self::EXT_STATUS_COMPLETED)
                ->centerStatus(self::STATUS_COMPLETED)
                ->label('已完成')
                ->onEnter('onCompleted')
                ->adminButtons([])
                ->userButtons([
                    Button::make('申请售后', 'applyAfterSale'),
                    Button::primary('再次购买', 'repurchase'),
                ]),

            // ───── 已取消（终态） ─────
            OrderFlowNode::make(self::EXT_STATUS_CANCELED)
                ->centerStatus(self::STATUS_CANCELED)
                ->label('已取消')
                ->adminButtons([])
                ->userButtons([
                    Button::make('删除订单', 'deleteOrder'),
                    Button::primary('再次购买', 'repurchase'),
                ]),

            // ───── 售后中（由 resolveStatusCode 直接处理） ─────
            OrderFlowNode::make(self::STATUS_AFTER_SALE)
                ->centerStatus(self::STATUS_AFTER_SALE)
                ->label('退换/售后')
                ->adminButtons([
                    Button::primary('处理售后', 'handleAfterSale'),
                ])
                ->userButtons([
                    Button::primary('查看售后', 'viewAfterSale'),
                ]),

            // ───── 已退款（终态） ─────
            OrderFlowNode::make(self::STATUS_REFUNDED)
                ->centerStatus(self::STATUS_REFUNDED)
                ->label('已退款')
                ->adminButtons([])
                ->userButtons([
                    Button::make('删除订单', 'deleteOrder'),
                ]),
        ];
    }

    // ======================== 3. 状态推导 ========================

    /**
     * 推导统一状态码
     * 优先级：取消 > 退款完成 > 售后中 > 未支付 > 扩展状态映射
     */
    public function resolveStatusCode(array $sourceOrder): string
    {
        if (($sourceOrder['is_cancel'] ?? 0) == 1) {
            return self::STATUS_CANCELED;
        }
        $refundStatus = (int)($sourceOrder['refund_status'] ?? 0);
        if ($refundStatus === 2) {
            return self::STATUS_REFUNDED;
        }
        if ($refundStatus === 1) {
            return self::STATUS_AFTER_SALE;
        }
        $status = (string)($sourceOrder['status'] ?? '');
        // 扩展表不一定携带 paid 字段，资源已发放的状态均视为已支付
        $isPaid = (bool)($sourceOrder['paid'] ?? 0)
            || in_array($status, [self::EXT_STATUS_PENDING_REVIEW, self::EXT_STATUS_COMPLETED], true);
        if (!$isPaid) {
            return self::STATUS_PENDING_PAYMENT;
        }
        return $status === self::EXT_STATUS_PENDING_REVIEW
            ? self::STATUS_PENDING_REVIEW
            : self::STATUS_COMPLETED;
    }

    /**
     * 推导支付状态
     */
    public function resolvePayStatus(array $sourceOrder): int
    {
        $refundStatus = (int)($sourceOrder['refund_status'] ?? 0);
        if ($refundStatus === 2) {
            $refundPrice = (float)($sourceOrder['refund_price'] ?? 0);
            $payPrice    = (float)($sourceOrder['pay_price'] ?? 0);
            return $refundPrice >= $payPrice ? self::PAY_FULL_REFUND : self::PAY_PARTIAL_REFUND;
        }
        $status = (string)($sourceOrder['status'] ?? '');
        $isPaid = (bool)($sourceOrder['paid'] ?? 0)
            || in_array($status, [self::EXT_STATUS_PENDING_REVIEW, self::EXT_STATUS_COMPLETED], true);
        return $isPaid ? self::PAY_PAID : self::PAY_UNPAID;
    }

    /**
     * 推导退款状态
     */
    public function resolveRefundStatus(array $sourceOrder): int
    {
        return (int)($sourceOrder['refund_status'] ?? 0);
    }

    /**
     * 推导业务状态
     * 网盘商品通过统一状态码表达待评价/已完成，不额外注入业务 Badge
     */
    public function resolveBusinessStatus(array $sourceOrder): ?array
    {
        if (($sourceOrder['is_cancel'] ?? 0) == 1 || ($sourceOrder['refund_status'] ?? 0) >= 2) {
            return null;
        }
        if (($sourceOrder['paid'] ?? 0) == 0) {
            return null;
        }
        return null; // 无中间业务状态
    }

    // ======================== 4. 进度条 ========================

    /**
     * 网盘商品订单生命周期进度条
     * 节点：提交订单 → 支付成功 → 待评价 → 订单完成
     */
    public function getProgress(array $detail): array
    {
        $statusCode       = $detail['status_code'] ?? '';
        $isCancel         = $statusCode === self::STATUS_CANCELED;
        $isRefunded       = $statusCode === self::STATUS_REFUNDED;
        $isAfterSale      = $statusCode === self::STATUS_AFTER_SALE;
        $isPendingReview  = $statusCode === self::STATUS_PENDING_REVIEW;
        $isCompleted      = $statusCode === self::STATUS_COMPLETED;
        $isPendingPayment = $statusCode === self::STATUS_PENDING_PAYMENT;

        $nodes = [
            ['title' => '提交订单', 'key' => 'submit', 'time_field' => 'add_time'],
            ['title' => '支付成功', 'key' => 'pay', 'time_field' => 'pay_time'],
            ['title' => '待评价', 'key' => 'review', 'time_field' => 'take_time'],
            ['title' => '订单完成', 'key' => 'complete', 'time_field' => ''],
        ];

        foreach ($nodes as $i => &$node) {
            $time = $node['time_field'] ? (int)($detail[$node['time_field']] ?? 0) : 0;
            $node['time']      = $time ? date('Y-m-d H:i:s', $time) : '';
            $node['completed'] = $time > 0;
            $node['active']    = false;
        }
        unset($node);

        // 已完成节点由状态码决定，不能仅凭 pay_time 判断
        // 待评价节点时间取自动发放时间；兼容历史订单没有 take_time 的情况，使用发货时间/支付时间兜底
        $reviewTime = (int)($detail['take_time'] ?? 0)
            ?: (int)($detail['delivery_time'] ?? 0)
            ?: (int)($detail['send_time'] ?? 0)
            ?: (int)($detail['pay_time'] ?? 0);
        $nodes[2]['time'] = $reviewTime ? date('Y-m-d H:i:s', $reviewTime) : '';
        $nodes[2]['completed'] = $isCompleted;
        $nodes[3]['completed'] = $isCompleted;
        if ($isCompleted) {
            $nodes[3]['time'] = $reviewTime ? date('Y-m-d H:i:s', $reviewTime) : '';
        }

        // 待支付状态：支付/完成节点不应显示已完成，避免脏数据误导
        if ($isPendingPayment) {
            $nodes[1]['completed'] = false;
            $nodes[1]['time']      = '';
            $nodes[2]['completed'] = false;
            $nodes[2]['time']      = '';
            $nodes[3]['completed'] = false;
            $nodes[3]['time']      = '';
        }

        $current = -1;
        if ($isCancel) {
            $current = 0;
        } elseif ($isRefunded || $isAfterSale) {
            $current = 1;
        } elseif ($isCompleted) {
            $current = 3;
        } elseif ($isPendingReview) {
            $current = 2;
        } elseif ($isPendingPayment) {
            // 待支付状态以状态码为准，不依赖时间字段
            $current = 0;
        } elseif ((int)($detail['pay_time'] ?? 0) > 0) {
            // 有支付时间但非完成态，停留在支付成功节点
            $current = 1;
        } elseif ((int)($detail['add_time'] ?? 0) > 0) {
            $current = 0;
        }

        if ($current < 0) {
            $current = 0;
            foreach ($nodes as $i => $node) {
                if ($node['completed']) {
                    $current = $i;
                }
            }
        }

        if (isset($nodes[$current])) {
            $nodes[$current]['active'] = true;
        }

        return [
            'current' => $current,
            'status'  => $statusCode,
            'nodes'   => $nodes,
        ];
    }

    // ======================== 5. 按钮逻辑 ========================

    public function getStatusButtons(string $statusCode, array $detail = []): array
    {
        // 网盘商品理论上不存在 processing 状态，但过渡期内部分旧数据/脏数据可能残留。
        // 已支付的旧 processing 订单按待评价处理，避免跳过评价。
        if ($statusCode === self::STATUS_PROCESSING) {
            $statusCode = self::STATUS_PENDING_REVIEW;
        }

        $node = $this->centerStatusMap[$statusCode][0] ?? null;
        if ($node) {
            return $node['user_buttons'] ?? [];
        }
        return $this->extStatusMap[$statusCode]['user_buttons'] ?? [];
    }

    /**
     * 评价完成后记录扩展表完成时间。
     * 状态推进由订单中台统一触发，这里只维护网盘扩展表自己的时间字段。
     */
    protected function onCompleted(string $orderNo, array $extra = []): void
    {
        app()->make(VirtualOrderModel::class)->db()
            ->where('order_no', $orderNo)
            ->where('complete_time', 0)
            ->update(['complete_time' => time()]);
    }
}
