<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace modules\product\virtual\service;

use modules\product\virtual\dao\VirtualOrderDao;

/**
 * 网盘商品退款服务
 * 退款处理业务逻辑
 * 网盘商品退款规则：已完成不可退
 */
class RefundServices
{
    /**
     * 订单扩展表 DAO
     */
    protected VirtualOrderDao $dao;

    public function __construct(VirtualOrderDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 退款校验
     * 网盘商品退款规则：
     * - 已完成不可退（网盘商品支付后自动发货完成）
     * - 已取消、已退款不可退
     * @param string $orderNo
     * @return bool
     */
    public function canRefund(string $orderNo): bool
    {
        $order = $this->dao->getOne(['order_no' => $orderNo]);
        if (!$order) {
            return false;
        }
        $status = $order['status'] ?? '';
        return !in_array($status, ['canceled', 'refunded', 'completed']);
    }

    /**
     * 执行退款
     * 网盘商品退款需要：
     * 1. 回退优惠券
     * 2. 回退积分
     * 3. 原路退款
     * @param string $orderNo
     * @param float $refundAmount
     * @param array $context
     * @return void
     */
    public function refund(string $orderNo, float $refundAmount, array $context = []): void
    {
        $this->dao->update(['order_no' => $orderNo], [
            'status' => 'refunded',
        ]);

        // 资金退款与本地资源回滚（积分/优惠券/库存/佣金/原路退款）已由售后引擎统一执行：
        // AfterSaleServices::refund → RefundExecutorServices::execute，此处仅同步虚拟产品扩展表状态
    }
}
