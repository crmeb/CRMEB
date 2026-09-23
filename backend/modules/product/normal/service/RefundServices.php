<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace modules\product\normal\service;

use crmeb\basic\BaseService;
use app\model\order\OrderCenter;
use app\services\order\AfterSaleServices;
use app\services\order\OrderCenterServices;
use modules\product\normal\dao\NormalOrderDao;

/**
 * 实物商品退款服务
 *
 * 职责范围：
 * - canRefund：校验订单是否可申请退款（校验扩展表状态）
 * - refund：通过 AfterSaleServices 执行售后流程中的退款
 * - 售后申请（apply）和审核（audit）统一走 AfterSaleServices
 *
 * 与 AfterSaleServices 的关系：
 * - AfterSaleServices 负责 eb_after_sale 表的状态管理和生命周期分发
 * - RefundServices 负责实物商品扩展表（eb_pt_normal_order）的退款相关逻辑
 * - 调用方先调 AfterSaleServices 走售后流程，再调 RefundServices 执行退款操作
 */
class RefundServices extends BaseService
{
    /**
     * @var NormalOrderDao
     */
    protected $orderExtendDao;

    /**
     * @var OrderCenterServices
     */
    protected $centerServices;

    /**
     * @var AfterSaleServices
     */
    protected AfterSaleServices $afterSaleServices;

    /**
     * 初始化
     */
    protected function initialize(): void
    {
        $this->orderExtendDao = $this->app->make(NormalOrderDao::class);
        $this->centerServices = $this->app->make(OrderCenterServices::class);
        $this->afterSaleServices = $this->app->make(AfterSaleServices::class);
    }

    /**
     * 退款校验
     * 实物商品退款规则：
     * - 已取消、已退款、已完成不可退
     * @param string $orderNo
     * @return bool
     */
    public function canRefund(string $orderNo): bool
    {
        $order = $this->orderExtendDao->getOne(['order_no' => $orderNo]);
        if (!$order) {
            return false;
        }
        $status = $order['status'] ?? '';
        return !in_array($status, ['canceled', 'refunded', 'completed']);
    }

    /**
     * 执行退款
     * 更新扩拓展表和中台状态，后续内化退积分/退优惠券/退佣金/退库存
     * @param string $orderNo
     * @param float $refundAmount
     * @param array $context
     * @return void
     */
    public function refund(string $orderNo, float $refundAmount, array $context = []): void
    {
        // 1. 更新中台状态为售后中
        $this->centerServices->syncStatus($orderNo, OrderCenter::STATUS_AFTER_SALE);

        // 2. 更新扩展表状态
        $this->orderExtendDao->update(['order_no' => $orderNo], [
            'status'        => 'refunded',
            'refund_status' => 2,
            'refund_price'  => $refundAmount,
        ]);

        // 3. 更新中台最终状态：status_code=refunded + refund_status=已退款
        // 走售后流程时此处会与 AfterSaleServices::refund 重复（无害），保证旧退款路径中台状态正确
        $this->centerServices->syncStatus($orderNo, OrderCenter::STATUS_REFUNDED, [
            'refund_status' => OrderCenter::REFUND_SUCCESS,
        ]);

        // 4. TODO: 后续内化以下逻辑：
        // - equalSplit() 退款拆分（支持部分退款）
        // - integralAndCouponBack() 回退积分和优惠券
        // - regressionIntegral() 积分回退（收回赠送+返还使用）
        // - orderRefundBrokerageBack() 回退佣金
        // - regressionStock() 回退库存
        // - 原路退款（微信/余额/支付宝/通联）
    }

    /**
     * 用户申请售后
     * 委托 AfterSaleServices 走完整售后流程
     * @param array $data 申请数据：order_no, uid, type, reason, refund_price, evidence_images
     * @return array 创建的售后单数据
     */
    public function applyRefund(array $data): array
    {
        return $this->afterSaleServices->apply($data);
    }

    /**
     * 审核售后
     * 委托 AfterSaleServices 处理
     * @param int $afterSaleId
     * @param string $result passed/rejected
     * @param int $auditUid
     * @param string $rejectReason
     * @param string $auditRemark
     * @return void
     */
    public function auditRefund(int $afterSaleId, string $result, int $auditUid = 0, string $rejectReason = '', string $auditRemark = ''): void
    {
        $this->afterSaleServices->audit($afterSaleId, $result, $auditUid, $rejectReason, $auditRemark);
    }

    /**
     * 获取当前订单可用的售后类型
     * @param string $orderNo
     * @return array
     */
    public function getAvailableTypes(string $orderNo): array
    {
        return $this->afterSaleServices->getAvailableTypes($orderNo);
    }
}
