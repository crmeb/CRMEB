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
use app\dao\order\OrderCenterDao;
use app\services\order\OrderCenterServices;
use app\services\order\OrderCenterUserServices;
use app\services\order\OrderComputedServices;
use app\services\order\OrderInvoiceServices;
use app\services\order\StoreCartServices;
use app\services\user\UserAddressServices;
use app\services\user\UserBillServices;
use app\services\user\UserInvoiceServices;
use crmeb\utils\SnowflakeIdGenerator;
use modules\product\normal\Handler;

/**
 * 实物商品下单服务
 * 下单逻辑自旧订单创建服务内化而来，模块自治：
 * 校验购物车 → 价格计算 → 地址处理 → 构建订单 → 扣库存/积分 → 写扩展表 → 写中台表 → 清购物车 → 后置事件
 */
class OrderCreateServices extends BaseService
{
    /**
     * @var OrderCenterServices
     */
    protected $centerServices;

    /**
     * @var Handler
     */
    protected $typeHandler;

    /**
     * @var OrderCenterDao
     */
    protected $centerDao;

    /**
     * 初始化
     */
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
     * @param int $addressId
     * @param string $payType
     * @param bool $useIntegral
     * @param int $couponId
     * @param string $mark
     * @param int $shippingType 1快递 2自提
     * @param string $realName
     * @param string $phone
     * @param int $storeId
     * @param bool $news 是否立即购买
     * @param array $customForm 自定义表单
     * @param int $invoiceId 发票ID
     * @return array
     */
    public function createOrder(
        int $uid,
        string $key,
        array $userInfo,
        int $addressId = 0,
        string $payType = '',
        bool $useIntegral = false,
        int $couponId = 0,
        string $mark = '',
        int $shippingType = 1,
        string $realName = '',
        string $phone = '',
        int $storeId = 0,
        bool $news = false,
        array $customForm = [],
        int $invoiceId = 0
    ): array {
        // 1. 校验购物车
        $cartGroup = $this->validateCart($uid, $key);

        // 2. 地址校验
        $addressInfo = $this->validateAddress($uid, $addressId, $shippingType, $realName, $phone, $cartGroup);

        // 3. 价格计算
        $priceData = $this->computePrice($uid, $userInfo, $cartGroup, $addressId, $payType, $useIntegral, $couponId, $shippingType);

        // 4. 构建订单数据
        $orderInfo = $this->buildOrderInfo($uid, $userInfo, $addressInfo, $cartGroup, $priceData, $payType, $couponId, $mark, $shippingType, $key);

        // 5. 事务写入
        $orderNo = $orderInfo['order_no'];
        $cartInfo = $this->normalizeCartInfo($cartGroup['cartInfo'][0] ?? []);
        $orderCenterId = 0;
        $this->transaction(function () use ($uid, $orderInfo, $cartInfo, $cartGroup, $addressInfo, $useIntegral, $priceData, $orderNo, $payType, $shippingType, $storeId, $customForm, &$orderCenterId) {
            // 5.1 扣减合单中的每一个 SKU。库存不足必须抛错，让事务回滚，不能静默创建无法履约的订单。
            $stockItems = $cartGroup['cartInfo'] ?? [$cartInfo];
            foreach ($stockItems as $stockItem) {
                $stockItem = $this->normalizeCartInfo($stockItem);
                $skuUnique = (string)($stockItem['product_attr_unique'] ?? ($stockItem['productInfo']['attrInfo']['unique'] ?? ''));
                $productId = (int)($stockItem['product_id'] ?? $stockItem['productInfo']['id'] ?? 0);
                $cartNum = (int)($stockItem['cart_num'] ?? 1);
                if ($productId <= 0 || $cartNum <= 0 || !$this->typeHandler->decStock($productId, $skuUnique, $cartNum)) {
                    throw new \RuntimeException('商品库存不足或规格已失效');
                }
            }

            // 5.2 积分抵扣
            if ($useIntegral && !empty($priceData['usedIntegral'])) {
                $this->deductIntegral($uid, $priceData, $orderNo);
            }

            // 5.3 先写扩展表获取 source_id，避免唯一索引冲突
            $context = [
                'pay_price'      => $priceData['pay_price'] ?? 0,
                'total_price'    => $priceData['total_price'] ?? 0,
                'activity_type'  => $orderInfo['activity_type'] ?? '',
                'shipping_type'  => $shippingType,
            ];
            $extendId = $this->typeHandler->createOrderExtend($orderNo, $cartInfo, $context);

            // 生成核销码（自提订单使用，直接传中台表实体列）
            $verifyCode = ($shippingType === 2 && method_exists($this->typeHandler, 'generateVerifyCode'))
                ? $this->typeHandler->generateVerifyCode() : '';

            // 5.4 写中台订单表（使用扩展表ID作为source_id，确保唯一索引不冲突）
            $this->centerServices->createRecord([
                'order_no'       => $orderNo,
                'source_type'    => $this->typeHandler->getOrderSourceType(),
                'source_id'      => $extendId,
                'uid'            => $uid,
                'order_category' => 'goods',
                'product_type'   => $this->typeHandler->getTypeKey(),
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
                'store_id'         => (int)$storeId,
                'shipping_type'    => $shippingType,
                'verify_code'      => $verifyCode,
                'mark'             => $orderInfo['mark'] ?? '',
                'custom_form'      => $customForm,
                'extra_info'       => [
                    'business_status' => [
                        'code' => 'pending_payment',
                        'text' => '待支付',
                        'detail' => '',
                        'extend' => new \stdClass(),
                    ],
                    'delivery_type'         => $shippingType === 2 ? 'pickup' : ($shippingType === 3 ? 'door' : 'express'),
                    'delivery'              => [
                        'delivery_type' => $shippingType === 2 ? 'pickup' : ($shippingType === 3 ? 'door' : 'express'),
                    ],
                    // 金额字段已提升为实体列，extra_info 中不再双写
                    // 商品规格快照，供订单列表/详情页展示
                    'sku_info'              => $this->buildSkuInfo($cartGroup['cartInfo'][0] ?? []),
                    // v8.1.1 商品级售后：写入 items 快照（含 cart_id），供售后引擎加载商品列表
                    'items'                 => $this->buildOrderItems($cartGroup['cartInfo'] ?? []),
                    'contact' => [
                        'name'    => $addressInfo['real_name'] ?? $orderInfo['real_name'] ?? '',
                        'phone'   => $addressInfo['phone'] ?? $orderInfo['user_phone'] ?? '',
                        'address' => [
                            'province'    => $addressInfo['province'] ?? '',
                            'city'        => $addressInfo['city'] ?? '',
                            'district'    => $addressInfo['district'] ?? '',
                            'detail'      => $addressInfo['detail'] ?? '',
                            'postal_code' => $addressInfo['postal_code'] ?? '',
                        ],
                    ],
                ],
                // 普通商品订单详情统一由订单中台路由承接，避免模块写入过期插件路由。
                'detail_route'   => '/pages/center/order/detail/index?order_no=' . $orderNo,
            ]);

            // 5.5 获取中台订单主键，供自动取消等异步任务使用
            $centerRecord = $this->centerDao->get(['order_no' => $orderNo], ['id']);
            if ($centerRecord) {
                $orderCenterId = (int)($centerRecord['id'] ?? 0);
            }

            // 5.6 确认扩展表写入（幂等跳过，避免重复写入）
            $this->typeHandler->afterOrder($orderNo, $cartInfo, $context);

            // 5.7 清理购物车
            $this->clearCart($uid, $orderInfo['cart_id'] ?? []);
        });

        // 6. 发票校验与开具
        if ($invoiceId) {
            app()->make(UserInvoiceServices::class)->checkInvoice((int)$invoiceId, $uid);
            app()->make(OrderInvoiceServices::class)->makeUp($uid, $orderNo, (int)$invoiceId);
        }

        // 7. 订单创建后置事件（新中台格式）
        event('OrderCreateAfter', [
            'order_no'     => $orderNo,
            'order_id'     => $orderCenterId,
            'cart_info'    => $cartInfo,
            'cart_items'   => $cartGroup['cartInfo'] ?? [$cartInfo],
            'price_data'   => $priceData,
            'uid'          => $uid,
            'user_phone'   => $orderInfo['user_phone'] ?? '',
            'key'          => $key,
            'product_type' => $this->typeHandler->getTypeKey(),
        ]);

        // 8. 外部订单推送
        event('OutPush', ['order_create_push', ['order_id' => $orderCenterId]]);

        return ['order_id' => $orderNo];
    }

    /**
     * 校验购物车
     * @param int $uid
     * @param string $key
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
     * 地址校验
     * @param int $uid
     * @param int $addressId
     * @param int $shippingType
     * @param string $realName
     * @param string $phone
     * @param array $cartGroup
     * @return array
     */
    protected function validateAddress(int $uid, int $addressId, int $shippingType, string $realName, string $phone, array $cartGroup): array
    {
        $isVirtual = !empty($cartGroup['cartInfo'][0]['productInfo']['is_virtual']);

        if ($shippingType == 1 && !$isVirtual) {
            if (!$addressId) {
                throw new \RuntimeException('请选择收货地址');
            }
            $addressServices = $this->app->make(UserAddressServices::class);
            $addressInfo = $addressServices->getOne(['uid' => $uid, 'id' => $addressId, 'is_del' => 0]);
            if (!$addressInfo) {
                throw new \RuntimeException('地址选择有误');
            }
            return $addressInfo->toArray();
        }

        if ((!$realName || !$phone) && !$isVirtual) {
            throw new \RuntimeException('请填写姓名和电话');
        }

        return [
            'real_name' => $realName,
            'phone'     => $phone,
            'province'  => '',
            'city'      => '',
            'district'  => '',
            'detail'    => '',
        ];
    }

    /**
     * 价格计算
     * @param int $uid
     * @param array $userInfo
     * @param array $cartGroup
     * @param int $addressId
     * @param string $payType
     * @param bool $useIntegral
     * @param int $couponId
     * @param int $shippingType
     * @return array
     */
    protected function computePrice(int $uid, array $userInfo, array $cartGroup, int $addressId, string $payType, bool $useIntegral, int $couponId, int $shippingType): array
    {
        $computedServices = $this->app->make(OrderComputedServices::class);
        $priceData = $computedServices->computedOrder($uid, $userInfo, $cartGroup, $addressId, $payType, $useIntegral, $couponId, true, $shippingType);

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
     * @param array $addressInfo
     * @param array $cartGroup
     * @param array $priceData
     * @param string $payType
     * @param int $couponId
     * @param string $mark
     * @param int $shippingType
     * @return array
     */
    protected function buildOrderInfo(int $uid, array $userInfo, array $addressInfo, array $cartGroup, array $priceData, string $payType, int $couponId, string $mark, int $shippingType, string $key): array
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
            'order_no'      => SnowflakeIdGenerator::generate('cp'),
            'unique'        => $key,
            'real_name'     => $addressInfo['real_name'] ?? '',
            'user_phone'    => $addressInfo['phone'] ?? '',
            'user_address'  => trim(($addressInfo['province'] ?? '') . ' ' . ($addressInfo['city'] ?? '') . ' ' . ($addressInfo['district'] ?? '') . ' ' . ($addressInfo['detail'] ?? '')),
            'cart_id'       => $cartIds,
            'total_num'     => $totalNum,
            'total_price'   => $priceData['total_price'],
            'pay_price'     => $priceData['pay_price'],
            'coupon_id'    => $couponId,
            'coupon_price'  => $priceData['coupon_price'],
            'pay_postage'   => $priceData['pay_postage'],
            'deduction_price' => $priceData['deduction_price'],
            'pay_type'      => $payType,
            'mark'          => htmlspecialchars($mark),
            'shipping_type' => $shippingType,
            'channel_type'  => $userInfo['user_type'] ?? '',
            'add_time'      => time(),
        ];
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
            \think\facade\Log::warning('[NormalOrderCreate] clearCart failed: ' . $e->getMessage());
        }
    }

    /**
     * 构建商品规格快照
     * 从购物车商品信息中提取 SKU 唯一码、规格名称、图片等，供订单列表/详情展示
     * @param array $cart 购物车单项
     * @return array 返回规格快照数组
     */
    protected function buildSkuInfo(array $cart): array
    {
        if (empty($cart)) {
            return [];
        }
        $attrInfo = $cart['productInfo']['attrInfo'] ?? [];
        if (empty($attrInfo)) {
            return [];
        }
        return [
            'unique'   => $attrInfo['unique'] ?? ($attrInfo['unique_code'] ?? ''),
            'suk'      => $attrInfo['suk'] ?? ($attrInfo['sku'] ?? ''),
            'image'    => $attrInfo['image'] ?? '',
            'price'    => $attrInfo['price'] ?? 0,
            'ot_price' => $attrInfo['ot_price'] ?? 0,
        ];
    }

    /**
     * 构建订单商品快照列表，存入 extra_info.items 供详情页和售后引擎使用
     * v8.1.1 商品级售后：包含 cart_id 字段，供按 cart_id 选择售后范围
     * 支持 multi-cart（多商品订单），遍历 cartInfo 数组构建 items 列表
     * @param array $cartInfoList 购物车商品数组
     * @return array
     */
    protected function buildOrderItems(array $cartInfoList): array
    {
        $items = [];
        foreach ($cartInfoList as $cart) {
            if (empty($cart) || empty($cart['productInfo'])) {
                continue;
            }
            $productInfo = $cart['productInfo'];
            $attrInfo = $productInfo['attrInfo'] ?? [];
            $items[] = [
                // cart_id 为商品级售后主键，从购物车数据继承
                'cart_id'      => (int)($cart['id'] ?? $cart['cart_id'] ?? 0),
                'product_id'   => $productInfo['id'] ?? 0,
                'image'        => $productInfo['image'] ?? '',
                'store_name'   => $productInfo['store_name'] ?? ($productInfo['title'] ?? ''),
                'price'        => $attrInfo['price'] ?? ($productInfo['price'] ?? '0.00'),
                'true_price'   => $cart['truePrice'] ?? ($attrInfo['price'] ?? $productInfo['price'] ?? '0.00'),
                'cart_num'     => $cart['cart_num'] ?? 1,
                'product_type' => $productInfo['product_type'] ?? 'normal',
                'attr_info'    => $attrInfo,
                'sku'          => $attrInfo['suk'] ?? '默认',
            ];
        }
        return $items;
    }

    /**
     * 规范化购物车信息，供 Handler::afterOrder 使用
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
