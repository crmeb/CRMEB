<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace modules\product\virtual\listener;

use app\services\order\OrderCenterServices;
use app\dao\order\OrderCenterDao;
use modules\product\virtual\Handler;

/**
 * 网盘商品支付成功监听器
 * 支付成功后同步中台状态，并调用 Handler::afterPay 完成扩展表更新与自动履约。
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

        // 仅处理 virtual 类型的中台订单
        $centerDao = app(OrderCenterDao::class);
        $meta = $centerDao->get(['order_no' => $orderNo], ['product_type', 'status_code', 'pay_status']);
        if (!$meta || ($meta['product_type'] ?? '') !== 'virtual') {
            return;
        }

        $statusCode = $meta['status_code'] ?? '';
        // 幂等：资源已发放或订单已结束时不再重复处理
        if (in_array($statusCode, ['pending_review', 'completed', 'refunded', 'canceled'], true)) {
            return;
        }

        // 只接受待支付或处理中状态，其他异常状态跳过
        if (!in_array($statusCode, ['pending_payment', 'processing'], true)) {
            return;
        }

        // 加载中台订单（含商品快照），供履约与销量累加使用；get() 返回模型，统一转数组
        $center = $centerDao->get(['order_no' => $orderNo], ['uid', 'extra_info']);
        $center = $center ? $center->toArray() : [];

        // 中台表已经标记为已支付时，避免重复写入 pay_time
        $isCenterPaid = (int)($meta['pay_status'] ?? 0) === 1;

        // 使用事务保证中台表与扩展表状态一致（走服务层事务，规范调用方式）
        $centerServices = app(OrderCenterServices::class);
        $centerServices->transaction(function () use ($centerServices, $orderNo, $orderInfo, $isCenterPaid, $center) {

            // 1. 中台表尚未标记支付时，才更新支付基础字段
            if (!$isCenterPaid) {
                $centerServices->syncStatus($orderNo, 'processing', [
                    'pay_status' => 1,
                    'pay_type'   => $orderInfo['pay_type'] ?? 'weixin',
                    'pay_time'   => time(),
                ]);
            }

            // 2. 调用通用生命周期服务
            $lifecycleServices = app(\app\services\order\OrderLifecycleServices::class);
            $lifecycleServices->handlePaySuccess($orderInfo);

            // 3. 构建购物车信息并调用类型 Handler 完成履约（扩展表更新为 pending_review，并同步中台）
            $cartInfoList = $this->buildCartInfoList($center, $orderInfo);
            $handler = app(Handler::class);
            $handler->afterPay($orderNo, $cartInfoList);

            // 4. 累加商品销量（中台主表冗余字段；位于幂等闸与事务内，不会重复累计）
            $this->incProductSales($center);
        });
    }

    /**
     * 按订单商品快照累加主表销量
     * @param array $center 中台订单（含 extra_info）
     * @return void
     */
    protected function incProductSales(array $center): void
    {
        $extra = $center['extra_info'] ?? [];
        if (is_string($extra)) {
            $extra = json_decode($extra, true) ?: [];
        }
        $items = is_array($extra) ? ($extra['items'] ?? []) : [];
        $qtyByProduct = [];
        foreach ($items as $item) {
            $productId = (int)($item['product_id'] ?? 0);
            $num = (int)($item['cart_num'] ?? 0);
            if ($productId > 0 && $num > 0) {
                $qtyByProduct[$productId] = ($qtyByProduct[$productId] ?? 0) + $num;
            }
        }
        if (!$qtyByProduct) {
            return;
        }
        /** @var \app\dao\product\CenterProductDao $centerProductDao */
        $centerProductDao = app(\app\dao\product\CenterProductDao::class);
        foreach ($qtyByProduct as $productId => $num) {
            $centerProductDao->incSales($productId, $num);
        }
    }

    /**
     * 从中台订单商品快照构建 Handler 所需的 cartInfoList
     * 新架构商品快照存于 eb_order_center.extra_info.items，替代旧购物车明细表
     * @param array $center 中台订单（含 uid、extra_info）
     * @param array $orderInfo 订单信息
     * @return array
     */
    protected function buildCartInfoList(array $center, array $orderInfo): array
    {
        // 商品快照：下单时写入 extra_info.items（兼容 JSON 字符串存储）
        $extra = $center['extra_info'] ?? [];
        if (is_string($extra)) {
            $extra = json_decode($extra, true) ?: [];
        }
        $items = $extra['items'] ?? [];

        $cartInfoList = [];
        foreach ($items as $item) {
            // 无商品ID的快照行跳过，避免 Handler 侧无效处理
            if (empty($item['product_id'])) {
                continue;
            }
            $item['uid'] = $center['uid'] ?? ($orderInfo['uid'] ?? 0);
            $item['cart_num'] = $item['cart_num'] ?? 1;
            $cartInfoList[] = $item;
        }

        return $cartInfoList;
    }
}
