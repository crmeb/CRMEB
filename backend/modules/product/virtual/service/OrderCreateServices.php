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

namespace modules\product\virtual\service;

use app\dao\order\OrderCenterDao;
use app\services\order\OrderCenterServices;
use app\services\order\OrderCenterUserServices;
use app\services\order\OrderComputedServices;
use app\services\order\StoreCartServices;
use app\services\user\UserBillServices;
use crmeb\basic\BaseService;
use crmeb\utils\SnowflakeIdGenerator;
use modules\product\virtual\Handler;

/**
 * 网盘商品下单服务
 * 不再依赖旧订单创建服务，独立完成：
 * 校验购物车 → 计算价格 → 生成订单号 → 扣库存/积分 → 写扩展表 → 写中台表 → 清购物车 → 触发事件
 */
class OrderCreateServices extends BaseService
{
    /**
     * @var OrderCenterServices
     */
    protected $centerServices;

    /**
     * @var OrderCenterDao
     */
    protected $centerDao;

    /**
     * @var Handler
     */
    protected $typeHandler;

    protected function initialize(): void
    {
        $this->centerServices = $this->app->make(OrderCenterServices::class);
        $this->centerDao = $this->app->make(OrderCenterDao::class);
        $this->typeHandler = $this->app->make(Handler::class);
    }

    /**
     * 创建订单
     * @param int $uid
     * @param string $key 缓存订单key
     * @param array $userInfo
     * @param string $payType
     * @param bool $useIntegral
     * @param int $couponId
     * @param string $mark
     * @return array
     */
    public function createOrder(
        int $uid,
        string $key,
        array $userInfo,
        string $payType = '',
        bool $useIntegral = false,
        int $couponId = 0,
        string $mark = ''
    ): array {
        // 1. 校验购物车
        $cartGroup = $this->validateCart($uid, $key);

        // 2. 价格计算（网盘商品无需地址，addressId 传 0）
        $priceData = $this->computePrice($uid, $userInfo, $cartGroup, $payType, $useIntegral, $couponId);

        // 3. 构建订单数据
        $orderInfo = $this->buildOrderInfo($uid, $userInfo, $cartGroup, $priceData, $payType, $couponId, $mark, $key);
        $cartInfo = $this->normalizeCartInfo($cartGroup['cartInfo'][0] ?? []);

        // 4. 事务写入
        $orderCenterId = 0;
        $orderNo = $this->transaction(function () use ($uid, $key, $orderInfo, $cartGroup, $cartInfo, $userInfo, $useIntegral, $priceData, $couponId, $payType, &$orderCenterId) {
            $orderNo = $orderInfo['order_no'];

            // 4.1 扣库存（网盘商品 SKU 用 id 作为唯一标识）
            $skuUnique = $cartInfo['product_attr_unique'] ?? ($cartInfo['productInfo']['attrInfo']['unique'] ?? '');
            $productId = (int)($cartInfo['product_id'] ?? 0);
            $cartNum = (int)($cartInfo['cart_num'] ?? 1);
            if ($productId > 0 && $cartNum > 0) {
                if (!$this->typeHandler->decStock($productId, $skuUnique, $cartNum)) {
                    throw new \RuntimeException('网盘商品库存不足或规格已失效');
                }
            }

            // 4.2 积分抵扣
            if ($useIntegral && !empty($priceData['usedIntegral'])) {
                $this->deductIntegral($uid, $priceData, $orderNo);
            }

            // 4.3 写网盘商品扩展表，并拿到 source_id
            $virtualOrderId = $this->typeHandler->createOrderExtend($orderNo, $cartInfo, [
                'pay_price'   => $priceData['pay_price'] ?? 0,
                'total_price' => $priceData['total_price'] ?? 0,
            ]);

            // 4.4 写中台订单表
            $this->centerServices->createRecord([
                'order_no'       => $orderNo,
                'source_type'    => 'virtual_order',
                'source_id'      => $virtualOrderId,
                'uid'            => $uid,
                'order_category' => 'goods',
                'product_type'   => 'virtual',
                'status_code'    => 'pending_payment',
                'pay_status'     => 0,
                'refund_status'  => 0,
                'total_price'    => $priceData['total_price'] ?? 0,
                'pay_price'      => $priceData['pay_price'] ?? 0,
                'pay_type'         => $payType,
                'coupon_price'     => (string) ($priceData['coupon_price'] ?? '0.00'),
                'deduction_price'  => (string) ($priceData['deduction_price'] ?? '0.00'),
                'use_integral_num' => (int) ($priceData['usedIntegral'] ?? 0),
                'member_discount'  => '0.00',
                'vip_discount'     => '0.00',
                'gift_price'       => '0.00',
                'title'            => $cartGroup['cartInfo'][0]['productInfo']['store_name'] ?? '',
                'image'            => $cartGroup['cartInfo'][0]['productInfo']['image'] ?? '',
                'total_num'        => $orderInfo['total_num'] ?? 1,
                // 金额字段已提升为实体列，extra_info 中不再双写
                'extra_info'       => [
                    'business_status' => [
                        'code' => 'pending_payment',
                        'text' => '待支付',
                        'detail' => '',
                        'extend' => new \stdClass(),
                    ],
                    'contact'               => null,
                    'items'                 => $this->buildOrderItems($cartInfo),
                ],
                'detail_route'   => '/plugins/virtual/pages/order-detail?order_no=' . $orderNo,
            ]);

            // 4.5 获取中台订单主键，供后续异步任务使用
            $centerRecord = $this->centerDao->get(['order_no' => $orderNo], ['id']);
            if ($centerRecord) {
                $orderCenterId = (int)($centerRecord['id'] ?? 0);
            }

            // 4.6 清理购物车
            $this->clearCart($uid, $orderInfo['cart_id'] ?? []);

            return $orderNo;
        });

        // 5. 订单创建后置事件
        event('OrderCreateAfter', [
            'order_no'     => $orderNo,
            'order_id'     => $orderCenterId,
            'cart_info'    => $cartInfo,
            'cart_items'   => $cartGroup['cartInfo'] ?? [$cartInfo],
            'price_data'   => $priceData,
            'uid'          => $uid,
            'key'          => $key,
            'product_type' => 'virtual',
        ]);

        return ['order_id' => $orderNo];
    }

    /**
     * 校验购物车
     * 从缓存读取结算页缓存的购物车数据（key 由 OrderCenterServices::cacheOrderInfo 生成）
     * @param int $uid
     * @param string $key 完整缓存 key
     * @return array
     */
    protected function validateCart(int $uid, string $key): array
    {
        if ($uid <= 0 || $key === '') {
            throw new \RuntimeException('订单已过期,请刷新当前页面');
        }

        /** @var OrderCenterUserServices $centerUserServices */
        $centerUserServices = $this->app->make(OrderCenterUserServices::class);
        $cartGroup = $centerUserServices->getCacheOrderInfo($uid, $key);

        if (!$cartGroup) {
            throw new \RuntimeException('订单已过期,请刷新当前页面');
        }

        if (is_string($cartGroup)) {
            $cartGroup = json_decode($cartGroup, true) ?: [];
        }

        // cacheOrderInfo 缓存字段为 priceData，computePrice 需要 priceGroup，做兼容
        if (!isset($cartGroup['priceGroup']) && isset($cartGroup['priceData'])) {
            $cartGroup['priceGroup'] = $cartGroup['priceData'];
        }

        if (empty($cartGroup['cartInfo']) || empty($cartGroup['priceGroup'])) {
            throw new \RuntimeException('订单商品信息有误');
        }

        return $cartGroup;
    }

    /**
     * 价格计算
     * @param int $uid
     * @param array $userInfo
     * @param array $cartGroup
     * @param string $payType
     * @param bool $useIntegral
     * @param int $couponId
     * @return array
     */
    protected function computePrice(int $uid, array $userInfo, array $cartGroup, string $payType, bool $useIntegral, int $couponId): array
    {
        $computedServices = $this->app->make(OrderComputedServices::class);
        $priceData = $computedServices->computedOrder($uid, $userInfo, $cartGroup, 0, $payType, $useIntegral, $couponId, true, 1);

        return [
            'total_price'      => $cartGroup['priceGroup']['totalPrice'] ?? 0,
            'promotion_price'  => $priceData['promotion_price'] ?? 0,
            'promotion_detail' => $priceData['promotion_detail'] ?? [],
            'pay_price'        => $priceData['pay_price'] ?? 0,
            'coupon_price'     => $priceData['coupon_price'] ?? 0,
            'pay_postage'      => $priceData['pay_postage'] ?? 0,
            'deduction_price'  => $priceData['deduction_price'] ?? 0,
            'usedIntegral'     => $priceData['usedIntegral'] ?? 0,
        ];
    }

    /**
     * 构建订单数据
     * @param int $uid
     * @param array $userInfo
     * @param array $cartGroup
     * @param array $priceData
     * @param string $payType
     * @param int $couponId
     * @param string $mark
     * @param string $key
     * @return array
     */
    protected function buildOrderInfo(int $uid, array $userInfo, array $cartGroup, array $priceData, string $payType, int $couponId, string $mark, string $key): array
    {
        $cartInfo = $cartGroup['cartInfo'];
        $cartIds = [];
        $totalNum = 0;
        foreach ($cartInfo as $cart) {
            $cartIds[] = $cart['id'];
            $totalNum += $cart['cart_num'];
        }

        return [
            'uid'           => $uid,
            'order_no'      => $this->getNewOrderId('cp'),
            'unique'        => $key,
            'real_name'     => '',
            'user_phone'    => '',
            'user_address'  => '',
            'cart_id'       => $cartIds,
            'total_num'     => $totalNum,
            'total_price'   => $priceData['total_price'],
            'pay_price'     => $priceData['pay_price'],
            'coupon_id'     => $couponId,
            'coupon_price'  => $priceData['coupon_price'],
            'pay_postage'   => $priceData['pay_postage'],
            'deduction_price' => $priceData['deduction_price'],
            'pay_type'      => $payType,
            'mark'          => htmlspecialchars($mark),
            'shipping_type' => 1,
            'channel_type'  => $userInfo['user_type'] ?? '',
            'add_time'      => time(),
        ];
    }

    /**
     * 生成订单号
     * 使用雪花算法（与中台 OrderCenterServices::getNewOrderId 保持一致）
     * @param string $prefix
     * @return string
     */
    protected function getNewOrderId(string $prefix = 'wx'): string
    {
        return SnowflakeIdGenerator::generate($prefix);
    }

    /**
     * 积分抵扣
     * @param int $uid
     * @param array $priceData
     * @param string $orderNo
     */
    protected function deductIntegral(int $uid, array $priceData, string $orderNo): void
    {
        $usedIntegral = (int)($priceData['usedIntegral'] ?? 0);
        if ($usedIntegral <= 0) {
            return;
        }

        /** @var UserBillServices $userBillServices */
        $userBillServices = $this->app->make(UserBillServices::class);
        $userBillServices->billIntegralIncome('deduction', $uid, [
            'number' => $usedIntegral,
            'deductionPrice' => $priceData['deduction_price'] ?? 0,
        ], $orderNo);
    }

    /**
     * 清理购物车
     * @param int $uid
     * @param array $cartIds
     */
    protected function clearCart(int $uid, array $cartIds): void
    {
        if (empty($cartIds)) {
            return;
        }

        try {
            /** @var StoreCartServices $cartServices */
            $cartServices = $this->app->make(StoreCartServices::class);
            $cartServices->removeUserCart($uid, $cartIds);
        } catch (\Throwable $e) {
            // 购物车清理失败不影响订单创建，记录日志即可
            \think\facade\Log::warning('[VirtualOrderCreate] clearCart failed: ' . $e->getMessage());
        }
    }

    /**
     * 构建订单商品快照，存入 extra_info.items 供详情页展示
     * @param array $cart
     * @return array
     */
    protected function buildOrderItems(array $cart): array
    {
        if (empty($cart) || empty($cart['productInfo'])) {
            return [];
        }

        $productInfo = $cart['productInfo'];
        $attrInfo = $productInfo['attrInfo'] ?? [];

        return [
            [
                // v8.1.1 cart_id 为商品级售后主键，从购物车数据继承
                'cart_id'      => (int)($cart['id'] ?? $cart['cart_id'] ?? 0),
                'product_id'   => $productInfo['id'] ?? 0,
                'image'        => $productInfo['image'] ?? '',
                'store_name'   => $productInfo['store_name'] ?? ($productInfo['title'] ?? ''),
                'price'        => $attrInfo['price'] ?? ($productInfo['price'] ?? '0.00'),
                'true_price'   => $cart['truePrice'] ?? ($attrInfo['price'] ?? $productInfo['price'] ?? '0.00'),
                'cart_num'     => $cart['cart_num'] ?? 1,
                'product_type' => $productInfo['product_type'] ?? 'virtual',
                'attr_info'    => $attrInfo,
                'sku'          => $attrInfo['suk'] ?? '默认',
            ],
        ];
    }

    /**
     * 规范化购物车信息，供 Handler 使用
     * @param array $cart
     * @return array
     */
    protected function normalizeCartInfo(array $cart): array
    {
        if (empty($cart)) {
            return [];
        }
        $cart['product_id'] = $cart['product_id'] ?? ($cart['productInfo']['id'] ?? 0);
        $cart['uid'] = $cart['uid'] ?? 0;
        $cart['cart_num'] = $cart['cart_num'] ?? 1;
        return $cart;
    }
}
