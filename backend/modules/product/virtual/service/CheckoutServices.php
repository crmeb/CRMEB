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

/**
 * 网盘商品下单服务
 * 网盘商品无需物流，下单流程比实物简单（当前为空壳）
 * 相关能力已由 OrderCreateServices 内化（价格计算走 OrderComputedServices）
 */
class CheckoutServices
{
    /**
     * 校验下单要素
     * @param int $uid
     * @param array $cartIds
     * @param array $params
     * @return array
     */
    public function validate(int $uid, array $cartIds, array $params): array
    {
        // TODO: 迁移网盘商品下单校验逻辑
        return [];
    }

    /**
     * 计算订单价格
     * 网盘商品无邮费
     * @param int $uid
     * @param array $cartInfo
     * @param array $params
     * @return array
     */
    public function computePrice(int $uid, array $cartInfo, array $params): array
    {
        // TODO: 迁移 OrderComputedServices::computedOrder
        return [
            'total_price'     => 0,
            'pay_price'       => 0,
            'coupon_price'    => 0,
            'deduction_price' => 0,
            'pay_postage'     => 0,
            'used_integral'   => 0,
        ];
    }
}
