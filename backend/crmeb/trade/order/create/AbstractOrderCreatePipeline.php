<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace crmeb\trade\order\create;

use crmeb\trade\product\BaseProductTypeHandler;

/**
 * 订单创建管道抽象基类
 * 定义下单流程的公共步骤契约，子类按产品类型覆盖差异部分
 */
abstract class AbstractOrderCreatePipeline
{
    /**
     * 产品类型处理器
     * @var BaseProductTypeHandler
     */
    protected BaseProductTypeHandler $handler;

    /**
     * @param BaseProductTypeHandler $handler
     */
    public function __construct(BaseProductTypeHandler $handler)
    {
        $this->handler = $handler;
    }

    /**
     * 定义管道步骤
     * @return array
     */
    abstract protected function defineSteps(): array;

    /**
     * 获取管道步骤
     * @return array
     */
    public function getSteps(): array
    {
        return $this->defineSteps();
    }

    /**
     * 校验购物车
     * @param int $uid
     * @param array $cartIds
     * @return array
     */
    public function validateCart(int $uid, array $cartIds): array
    {
        return [];
    }

    /**
     * 校验库存
     * @param array $cartInfo
     * @return bool
     */
    public function checkStock(array $cartInfo): bool
    {
        return true;
    }

    /**
     * 计算价格
     * @param int $uid
     * @param array $cartInfo
     * @param array $params
     * @return array
     */
    public function computePrice(int $uid, array $cartInfo, array $params): array
    {
        return [
            'total_price'     => 0,
            'pay_price'       => 0,
            'coupon_price'    => 0,
            'deduction_price' => 0,
            'pay_postage'     => 0,
        ];
    }

    /**
     * 创建订单
     * @param array $orderInfo
     * @return array
     */
    public function createOrder(array $orderInfo): array
    {
        return $orderInfo;
    }

    /**
     * 保存商品快照
     * @param int $orderId
     * @param int $uid
     * @param array $cartInfo
     * @return void
     */
    public function saveCartInfo(int $orderId, int $uid, array $cartInfo): void
    {
    }

    /**
     * 清理购物车
     * @param int $uid
     * @param array $cartIds
     * @return void
     */
    public function clearCart(int $uid, array $cartIds): void
    {
    }
}
