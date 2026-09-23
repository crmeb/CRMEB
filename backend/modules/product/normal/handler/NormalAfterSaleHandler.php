<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace modules\product\normal\handler;

use crmeb\trade\order\aftersale\BaseAfterSaleHandler;
use app\dao\order\AfterSaleDao;
use modules\product\normal\dao\NormalOrderDao;
use modules\product\normal\dao\NormalProductDao;
use app\services\order\OrderCenterServices;

/**
 * 实物商品售后处理器
 * 同时提供售后规则定义和生命周期回调
 *
 * 职责范围：
 * - 售后规则：支持 5 种售后类型（仅退款/退货退款/换货/补发/维修）
 * - 售后申请：标记扩展表退款状态，冻结订单
 * - 审核拒绝：释放冻结状态
 * - 退款执行：更新扩展表和中台状态
 * - 售后取消：状态回滚
 *
 * 各步骤的物流信息由 after_sale 表统一管理，本类只负责与订单扩展表的联动
 */
class NormalAfterSaleHandler extends BaseAfterSaleHandler
{
    /**
     * @var NormalOrderDao
     */
    protected NormalOrderDao $orderDao;

    /**
     * @var OrderCenterServices
     */
    protected OrderCenterServices $centerServices;

    /**
     * 初始化
     */
    public function __construct()
    {
        $this->orderDao = app(NormalOrderDao::class);
        $this->centerServices = app(OrderCenterServices::class);
    }

    // ==================== 售后规则（getRule） ====================

    /**
     * 获取售后规则
     * 实物商品默认支持全部售后类型，保修期外移除维修
     * @param array $orderCenter 中台订单数据
     * @param array $sourceOrder 源表订单数据
     * @return array
     */
    public function getRule(array $orderCenter, array $sourceOrder): array
    {
        $availableTypes = ['refund_only', 'return_refund', 'exchange', 'resend', 'repair'];

        // 保修期检查：过保商品不支持维修
        $availableTypes = $this->applyWarrantyFilter($availableTypes, $orderCenter, $sourceOrder);

        return [
            'allow'           => true,
            'reject_reason'   => '',
            'available_types' => $availableTypes,
            'max_refund_num'  => 0,
            'deadline'        => 0,
        ];
    }

    /**
     * 根据保修期过滤售后类型
     * @param array $availableTypes
     * @param array $orderCenter
     * @param array $sourceOrder
     * @return array
     */
    protected function applyWarrantyFilter(array $availableTypes, array $orderCenter, array $sourceOrder): array
    {
        $productId = intval($sourceOrder['product_id'] ?? 0);
        if ($productId <= 0) {
            return $availableTypes;
        }

        try {
            /** @var NormalProductDao $productDao */
            $productDao = app(NormalProductDao::class);
            $productExt = $productDao->getByProductId($productId, ['warranty_days']);

            if (!$productExt) {
                return $availableTypes;
            }
            if (is_object($productExt)) {
                $productExt = $productExt->toArray();
            }

            $warrantyDays = intval($productExt['warranty_days'] ?? 0);
            if ($warrantyDays <= 0) {
                return $availableTypes;
            }

            $payTime = intval($orderCenter['pay_time'] ?? 0);
            if ($payTime <= 0) {
                return $availableTypes;
            }

            $warrantyDeadline = $payTime + $warrantyDays * 86400;
            if (time() > $warrantyDeadline) {
                return array_values(array_diff($availableTypes, ['repair']));
            }
        } catch (\Throwable $e) {
            // 扩展表查询失败时不做额外限制
        }

        return $availableTypes;
    }

    // ==================== 售后生命周期回调 ====================

    /**
     * 普通实物库存只在未发货或退货已入库时恢复。
     * @param array $afterSale 售后单
     * @param array $order 中台订单
     * @param array $items 售后明细
     * @param array $context 退款上下文
     * @return array
     */
    public function buildRefundPlan(array $afterSale, array $order, array $items, array $context = []): array
    {
        $plan = parent::buildRefundPlan($afterSale, $order, $items, $context);
        $ext = $this->orderDao->getOne(['order_no' => $order['order_no']]);
        $notShipped = $ext && in_array((string)($ext['status'] ?? ''), ['pending_payment', 'pending_shipped'], true)
            && (int)($order['delivery_time'] ?? 0) === 0;
        $returned = ($afterSale['type'] ?? '') === 'return_refund' && (int)($afterSale['receive_time'] ?? 0) > 0;
        $plan['restore_inventory'] = $notShipped || $returned;
        $plan['normal_order_effects'] = in_array((string)($order['activity_type'] ?? ''), ['', 'normal'], true);
        return $plan;
    }

    /**
     * 重入时再次核对库存规则，兼容升级前已固化的退款计划。
     * @param \crmeb\trade\order\aftersale\RefundEffectContextInterface $effects 效果上下文
     * @param array $refundOrder 退款单
     * @param array $afterSale 售后单
     * @param array $order 中台订单
     * @param array $items 售后明细
     * @return void
     */
    public function applyRefundEffects(
        \crmeb\trade\order\aftersale\RefundEffectContextInterface $effects,
        array $refundOrder,
        array $afterSale,
        array $order,
        array $items
    ): void {
        $plan = $this->buildRefundPlan($afterSale, $order, $items);
        $refundOrder['context']['plan']['restore_inventory'] = $plan['restore_inventory']
            && ($refundOrder['context']['plan']['restore_inventory'] ?? true);
        parent::applyRefundEffects($effects, $refundOrder, $afterSale, $order, $items);
    }

    /**
     * 查询售后单的 order_no
     * @param string $afterSaleNo
     * @return string
     */
    protected function getOrderNo(string $afterSaleNo): string
    {
        try {
            /** @var AfterSaleDao $afterSaleDao */
            $afterSaleDao = app(AfterSaleDao::class);
            $record = $afterSaleDao->getByNo($afterSaleNo, 'order_no');
            return $record['order_no'] ?? '';
        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * 提交售后申请后
     * 实物商品：标记扩展表退款状态为申请中，防止重复申请
     * @param string $afterSaleNo 售后单号
     * @param array $data 售后申请数据
     * @return void
     */
    public function afterApply(string $afterSaleNo, array $data): void
    {
        $orderNo = $data['order_no'] ?? $this->getOrderNo($afterSaleNo);
        if (empty($orderNo)) {
            return;
        }

        // 标记扩展订单为退款申请中
        $this->orderDao->update(['order_no' => $orderNo], [
            'refund_status' => 1,
        ]);
    }

    /**
     * 审核通过/拒绝后
     * @param string $afterSaleNo 售后单号
     * @param array $data 审核数据
     * @return void
     */
    public function afterAudit(string $afterSaleNo, array $data): void
    {
        $orderNo = $data['order_no'] ?? $this->getOrderNo($afterSaleNo);
        if (empty($orderNo)) {
            return;
        }

        $result = $data['result'] ?? '';

        if ($result === 'reject') {
            // 审核拒绝：释放冻结状态
            $this->orderDao->update(['order_no' => $orderNo], [
                'refund_status' => 0,
            ]);
        }
        // 审核通过不修改 refund_status，等后续退款完成时再变更
    }

    /**
     * 退款执行后
     * 只更新实物商品扩展表；中台聚合状态由退款核心按累计退款额处理。
     * @param string $afterSaleNo 售后单号
     * @param float $amount 实际退款金额
     * @param array $context 退款上下文
     * @return void
     */
    public function afterRefund(string $afterSaleNo, float $amount, array $context): void
    {
        $orderNo = $context['order_no'] ?? $this->getOrderNo($afterSaleNo);
        if (empty($orderNo)) {
            return;
        }

        $isFullRefund = (bool)($context['is_full_refund'] ?? false);
        $update = [
            'refund_status' => 2,
            'refund_price' => (string)($context['total_refund_amount'] ?? $amount),
        ];
        if ($isFullRefund) {
            $update['status'] = 'refunded';
        }
        $this->orderDao->update(['order_no' => $orderNo], $update);
    }

    /**
     * 售后取消
     * 实物商品：释放冻结状态，恢复订单正常流转
     * @param string $afterSaleNo 售后单号
     * @return void
     */
    public function afterCancel(string $afterSaleNo): void
    {
        $orderNo = $this->getOrderNo($afterSaleNo);
        if (empty($orderNo)) {
            return;
        }

        // 取消售后：释放退款申请状态
        $this->orderDao->update(['order_no' => $orderNo], [
            'refund_status' => 0,
        ]);
    }
}
