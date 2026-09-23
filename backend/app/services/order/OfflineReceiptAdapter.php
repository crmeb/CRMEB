<?php
declare(strict_types=1);

namespace app\services\order;

use crmeb\trade\order\type\BaseOrderTypeAdapter;
use crmeb\trade\order\type\Button;
use crmeb\trade\order\type\OrderFlowNode;

/**
 * 线下收银订单流转定义
 * 实现线下收银收款单的状态管理和流转
 *
 * ═══════════════ 完整状态流转图 ═══════════════
 *
 *   pending_payment  待支付 ──────→ 中台 pending_payment
 *     │ 支付成功 → Event → Listener → Flow::advance('completed')
 *     ▼
 *   completed       已完成   ──────→ 中台 completed
 */
class OfflineReceiptAdapter extends BaseOrderTypeAdapter
{
    // ======================== 扩展状态常量 ========================

    /** 待支付 */
    public const EXT_STATUS_PENDING_PAYMENT = 'pending_payment';
    /** 已完成 */
    public const EXT_STATUS_COMPLETED = 'completed';

    // ======================== 1. 身份标识 ========================

    public static function getSourceType(): string
    {
        return 'offline_receipt';
    }

    public static function getOrderCategory(): string
    {
        return 'member';
    }

    public static function getOrderCategoryLabel(): string
    {
        return '会员订单';
    }

    /**
     * 线下收银订单使用 OrderCenter 自身作为模型
     */
    public static function getModelClass(): string
    {
        return \app\model\order\OrderCenter::class;
    }

    // ======================== 2. 状态树（唯一数据源） ========================

    protected function defineStatusTree(): array
    {
        return [
            // ───── 待支付 ─────
            OrderFlowNode::make(self::EXT_STATUS_PENDING_PAYMENT)
                ->centerStatus(self::STATUS_PENDING_PAYMENT)
                ->label('待支付')
                ->adminButtons([])
                ->userButtons([
                    Button::primary('去支付', 'payOrder'),
                ]),

            // ───── 已完成（终态） ─────
            OrderFlowNode::make(self::EXT_STATUS_COMPLETED)
                ->centerStatus(self::STATUS_COMPLETED)
                ->label('已完成')
                ->adminButtons([])
                ->userButtons([]),
        ];
    }

    // ======================== 3. 状态推导 ========================

    /**
     * 推导统一状态码
     */
    public function resolveStatusCode(array $sourceOrder): string
    {
        if (($sourceOrder['paid'] ?? 0) == 0) {
            return self::STATUS_PENDING_PAYMENT;
        }
        return self::STATUS_COMPLETED;
    }

    /**
     * 推导支付状态
     */
    public function resolvePayStatus(array $sourceOrder): int
    {
        return ($sourceOrder['paid'] ?? 0) ? self::PAY_PAID : self::PAY_UNPAID;
    }

    /**
     * 推导退款状态
     */
    public function resolveRefundStatus(array $sourceOrder): int
    {
        return self::REFUND_NONE;
    }

    /**
     * 推导业务状态
     */
    public function resolveBusinessStatus(array $sourceOrder): ?array
    {
        return null;
    }

    // ======================== 4. 按钮逻辑 ========================

    public function getStatusButtons(string $statusCode, array $detail = []): array
    {
        $node = $this->centerStatusMap[$statusCode][0] ?? null;
        if ($node) {
            return $node['user_buttons'] ?? [];
        }
        return $this->extStatusMap[$statusCode]['user_buttons'] ?? [];
    }
}
