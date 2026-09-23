<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace modules\product\virtual\pipeline;

use crmeb\trade\order\create\AbstractOrderCreatePipeline;

/**
 * 网盘商品下单管道
 * 网盘商品无需物流，步骤比实物少
 * 支付后自动发货，无发货/收货环节
 * 适配层：实际下单流程由本模块 OrderCreateServices 处理（已脱离旧服务）
 */
class OrderCreatePipeline extends AbstractOrderCreatePipeline
{
    /**
     * 定义管道步骤
     * 网盘商品不需要物流，支付后自动发货
     * @return array
     */
    protected function defineSteps(): array
    {
        return [
            'validateCart',   // 1. 校验购物车
            'checkStock',     // 2. 校验库存
            'computePrice',   // 3. 计算价格（SKU价/优惠，无邮费）
            'createOrder',    // 4. 创建订单
            'autoSend',       // 5. 自动发货（网盘商品专属）
            'saveCartInfo',   // 6. 保存商品快照
            'clearCart',      // 7. 清理购物车
        ];
    }

    // ===== 以下方法待迁移 =====

    /**
     * 自动发货
     * 网盘商品支付后自动发货，发送下载链接
     */
    public function autoSend(string $orderNo, array $cartInfo): void
    {
        // TODO: 迁移网盘商品自动发货逻辑
        // 1. 获取商品下载链接
        // 2. 发送给用户（站内信/模板消息）
        // 3. 更新订单状态为已完成
    }
}
