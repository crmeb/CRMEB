<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace modules\product\normal\listener;

use app\dao\product\CenterProductDao;
use app\services\order\OrderCenterServices;
use app\services\order\OrderLifecycleServices;
use app\dao\order\OrderCenterDao;
use modules\product\normal\handler\NormalOrderAdapter;

/**
 * 实物商品支付成功监听器
 * 支付成功后同步中台状态，推进扩展表状态为待发货，并累加商品销量
 */
class OrderPaidListener
{
    /**
     * 支付成功处理
     * @param array $orderInfo 订单信息
     * @return void
     */
    public function handle($event): void
    {
        [$orderInfo] = $event;
        $orderNo = $orderInfo['order_id'] ?? '';
        if (empty($orderNo)) {
            return;
        }

        // 仅处理 normal 类型的中台订单
        $centerDao = app(OrderCenterDao::class);
        $centerOrder = $centerDao->get(['order_no' => $orderNo], ['product_type', 'status_code', 'extra_info']);
        if (!$centerOrder || ($centerOrder['product_type'] ?? '') !== 'normal') {
            return;
        }

        // 幂等：扩展表已推进到待发货则不再重复执行
        /** @var \modules\product\normal\model\NormalOrderModel $normalOrderModel */
        $normalOrderModel = app(\modules\product\normal\model\NormalOrderModel::class);
        $extOrder = $normalOrderModel->where('order_no', $orderNo)->field('status,paid')->find();
        if ($extOrder && ($extOrder['status'] ?? '') !== NormalOrderAdapter::EXT_STATUS_PENDING_PAYMENT) {
            return;
        }

        // 1. 通用生命周期服务（写日志、送优惠券、开票、资金流水、打印、通知等）
        $lifecycleServices = app(OrderLifecycleServices::class);
        $lifecycleServices->handlePaySuccess($orderInfo);

        // 2. 推进扩展表状态：支付成功 → 待发货
        NormalOrderAdapter::instance()->advance($orderNo, NormalOrderAdapter::EXT_STATUS_PENDING_SHIPPED, [
            'paid'      => 1,
            'pay_price' => $orderInfo['pay_price'] ?? 0,
        ]);

        // 3. 累加商品销量（中台主表冗余字段，前后端列表读取；位于幂等闸之后不会重复累计）
        $this->incProductSales($centerOrder->toArray());
    }

    /**
     * 按订单商品快照累加主表销量
     * @param array $centerOrder 中台订单（含 extra_info）
     * @return void
     */
    protected function incProductSales(array $centerOrder): void
    {
        try {
            $items = $centerOrder['extra_info'] ?? [];
            if (is_string($items)) {
                $items = json_decode($items, true) ?: [];
            }
            $items = is_array($items) ? ($items['items'] ?? []) : [];
            $qtyByProduct = [];
            foreach ($items as $item) {
                $productId = (int)($item['product_id'] ?? 0);
                $num = (int)($item['cart_num'] ?? 0);
                if ($productId > 0 && $num > 0) {
                    $qtyByProduct[$productId] = ($qtyByProduct[$productId] ?? 0) + $num;
                }
            }
            /** @var CenterProductDao $centerProductDao */
            $centerProductDao = app(CenterProductDao::class);
            foreach ($qtyByProduct as $productId => $num) {
                $centerProductDao->incSales($productId, $num);
            }
        } catch (\Throwable $e) {
            // 销量为冗余统计字段，累加失败不影响支付主流程
            \think\facade\Log::warning('实物商品支付成功累加销量失败: ' . $e->getMessage());
        }
    }
}
