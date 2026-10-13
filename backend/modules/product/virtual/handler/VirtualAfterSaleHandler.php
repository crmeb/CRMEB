<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace modules\product\virtual\handler;

use crmeb\trade\order\aftersale\BaseAfterSaleHandler;
use app\dao\order\AfterSaleDao;
use modules\product\virtual\dao\VirtualOrderDao;

/**
 * 网盘商品售后处理器
 * 提供售后规则定义与生命周期回调
 *
 * 规则：仅支持退款（refund_only），支付后 24 小时内可申请，资源已发放（待评价/已完成）后不支持
 */
class VirtualAfterSaleHandler extends BaseAfterSaleHandler
{
    /**
     * @var VirtualOrderDao
     */
    protected VirtualOrderDao $orderDao;

    /**
     * 初始化
     */
    public function __construct()
    {
        $this->orderDao = app(VirtualOrderDao::class);
    }

    // ==================== 售后规则 ====================

    /**
     * 获取售后规则
     * 网盘商品仅支持退款：未发放前可退，已发放（pending_review/completed）不可退
     * @param array $orderCenter 中台订单数据
     * @param array $sourceOrder 源表订单数据
     * @return array
     */
    public function getRule(array $orderCenter, array $sourceOrder): array
    {
        $statusCode = $orderCenter['status_code'] ?? '';
        $payStatus = $orderCenter['pay_status'] ?? 0;

        // 未支付订单不允许售后
        if ($payStatus != 1) {
            return [
                'allow'           => false,
                'reject_reason'   => '订单未支付',
                'available_types' => [],
                'max_refund_num'  => 0,
                'deadline'        => intval($orderCenter['add_time'] ?? 0) + 86400, // 24小时内截止
            ];
        }

        return [
            'allow'           => false,
            'reject_reason'   => '网盘商品支付后自动发放，不支持退款',
            'available_types' => [],
            'max_refund_num'  => 0,
            'deadline'        => intval($orderCenter['add_time'] ?? 0) + 86400,
        ];
    }

    // ==================== 售后生命周期回调 ====================

    /**
     * 查询售后单的 order_no
     * @param string $afterSaleNo 售后单号
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
     * 网盘商品无需联动扩展表（扩展表仅状态），仅标记退款状态防重复
     * @param string $afterSaleNo 售后单号
     * @param array $data 售后申请数据
     * @return void
     */
    public function afterApply(string $afterSaleNo, array $data): void
    {
        // 网盘商品售后期间锁定订单状态由中台 after_sale 状态承担，无需额外标记
    }

    /**
     * 审核拒绝后
     * 释放订单状态
     * @param string $afterSaleNo 售后单号
     * @param array $data 审核数据
     * @return void
     */
    public function afterAudit(string $afterSaleNo, array $data): void
    {
        // 审核拒绝后订单恢复正常流转，由中台状态机处理
    }

    /**
     * 退款执行后
     * 仅更新扩展表状态为已退款；中台订单状态由 AfterSaleServices::refund 统一更新
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

        $this->orderDao->update(['order_no' => $orderNo], [
            'status' => 'refunded',
        ]);
    }

    /**
     * 售后取消
     * 订单恢复正常流转
     * @param string $afterSaleNo 售后单号
     * @return void
     */
    public function afterCancel(string $afterSaleNo): void
    {
        // 订单状态由中台状态机恢复，无需额外操作
    }
}
