<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace app\api\controller\v1\order;

use app\services\activity\coupon\StoreCouponUserServices;
use app\dao\order\OrderCenterDao;
use app\model\order\OrderCenter;
use app\services\order\OrderCenterUserServices;
use app\services\order\OrderCenterServices;
use app\services\order\OrderComputedServices;
use app\services\order\OrderOutServices;
use app\services\product\CenterProductServices;
use app\services\system\store\SystemStoreServices;
use app\services\system\SystemUserLevelServices;
use app\services\user\UserBillServices;
use app\services\user\UserAddressServices;
use app\services\user\UserServices;
use app\services\user\member\MemberCardServices;
use app\services\pay\PayServices;
use app\services\pay\PayRecordServices;
use app\services\pay\YuePayServices;
use app\services\pay\OrderPayServices;
use app\services\pay\PayCredentialService;
use app\services\driver\pay\PayChannelServices;
use app\services\order\OrderSuccessServices;
use crmeb\trade\product\ProductTypeRegistry;
use crmeb\trade\mode\TradeModeRegistry;
use app\services\activity\coupon\StoreCouponIssueServices;
use app\services\order\StoreCartServices;
use app\services\order\OrderInvoiceServices;
use app\services\order\AfterSaleServices;
use app\services\order\OrderTakeServices;
use app\services\product\product\StoreProductReplyServices;
use app\services\shipping\ExpressServices;
use crmeb\services\CacheService;
use think\facade\Log;

/**
 * 中台订单用户端控制器
 * 用户端：统一订单列表、订单确认、统一下单
 */
class CenterOrderController
{
    /**
     * 我的订单列表
     * 从 eb_order_center 统一中台表读取
     */
    public function list(OrderCenterUserServices $services)
    {
        $uid = (int) request()->uid();
        // 状态筛选：接受语义化状态（pending_payment/pending_delivery/pending_use/pending_receipt/pending_comment/after_sale），
        // 映射到实际 status_code，前端无需维护状态映射表
        $semanticStatus = (string) request()->param('status', '');
        $where = [
            'order_category' => request()->param('order_category', ''),
            'status_code'    => $this->mapSemanticStatus($semanticStatus),
            'product_type'   => request()->param('product_type', ''),
        ];
        // 待发货/待收货/待使用：均为 STATUS_PROCESSING，需按产品类型与发货状态细分
        // 待发货=实物业态(normal)未发货；待收货=实物业态已发货未收货；待使用=非实物业态(virtual/cardkey/hotel/ticket 等)
        if ($semanticStatus === 'pending_delivery') {
            $where['product_type'] = 'normal';
            $where['delivery_time'] = 0;
        } elseif ($semanticStatus === 'pending_receipt') {
            $where['product_type'] = 'normal';
            $where['delivery_time_ne'] = 0;
            $where['take_time'] = 0;
        } elseif ($semanticStatus === 'pending_use') {
            $where['product_type_ne'] = 'normal';
        }
        $page = (int) request()->param('page', 1);
        $limit = (int) request()->param('limit', 10);
        return app('json')->success($services->getList($uid, $where, $page, $limit));
    }

    /**
     * 语义化状态映射到实际 status_code
     * 前端传入语义化状态标识，后端映射到中台 status_code，支持逗号分隔多状态（如售后=refunding,refunded）
     * 待发货(pending_delivery)/待收货(pending_receipt)/待使用(pending_use) 均映射到 STATUS_PROCESSING，
     * 发货状态与产品类型过滤由 list 方法处理，此处不承担
     * @param string $status 语义化状态标识
     * @return string 返回 status_code，空表示不筛选（全部）
     */
    protected function mapSemanticStatus(string $status): string
    {
        $map = [
            'pending_payment'  => OrderCenter::STATUS_PENDING_PAYMENT,
            'pending_receipt'  => OrderCenter::STATUS_PROCESSING,
            // 待发货/待收货/待使用：均为进行中状态，产品类型与发货状态过滤交由 list 方法处理
            'pending_delivery' => OrderCenter::STATUS_PROCESSING,
            'pending_use'      => OrderCenter::STATUS_PROCESSING,
            // 待评价：履约完成待评价（评价后流转 completed；与统计接口 grouped.pending_comment 口径一致）
            'pending_comment'  => OrderCenter::STATUS_PENDING_REVIEW,
            // 售后：退款中 + 已退款
            'after_sale'       => OrderCenter::STATUS_REFUNDING . ',' . OrderCenter::STATUS_REFUNDED,
        ];
        return $map[$status] ?? '';
    }

    /**
     * 我的订单统计
     * 获取当前用户各状态订单数量和总金额
     */
    public function stat(OrderCenterUserServices $services)
    {
        $uid = (int) request()->uid();
        return app('json')->success($services->getUserStatistics($uid));
    }

    /**
     * 订单详情
     * v8.1 最终形态：接口只返回 {page_config: {sections, extensions}}，渲染数据直填在 sections.props
     * @param OrderCenterUserServices $services
     * @param string $orderNo 订单号
     * @return mixed
     */
    public function detail(OrderCenterUserServices $services, string $orderNo)
    {
        $uid = (int) request()->uid();
        try {
            $ownedId = $services->getOwnedOrderIdByNo($orderNo, $uid);
            if ($ownedId === null) {
                return app('json')->fail('订单不存在');
            }
            // 详情首屏不同步请求第三方物流：只读轨迹缓存，未命中由前端走 order/express 异步补齐
            $result = $services->getDetailWithConfig($ownedId, false);
            // 移动端订单详情不返回 timeline section，避免接口数据冗余
            if (!empty($result['page_config']['sections'])) {
                $result['page_config']['sections'] = array_values(
                    array_filter($result['page_config']['sections'], function($s) {
                        return $s['key'] !== 'timeline';
                    })
                );
            }
            // 送礼订单按查看者裁剪：购买者隐藏收货信息/物流/相关按钮，领取人保留只读物流能力
            $result = $services->applyGiftViewerPolicy($result, $uid);
            return app('json')->success($result);
        } catch (\Throwable $e) {
            return app('json')->fail($e->getMessage());
        }
    }

    /**
     * 订单确认（展示商品信息、优惠券、积分、价格）
     * 从中心商品直接购买，绕过购物车
     */
    public function confirm()
    {
        try {
            $uid = (int) request()->uid();
            // 购物车模式：携带 cartId 时走多商品合并下单确认流程
            $cartId = (string) request()->param('cartId', '');
            if ($cartId) {
                return $this->cartConfirm($uid);
            }
            $productId = (int) request()->param('product_id', 0);
            $skuUnique = (string) request()->param('sku_unique', '');
            $productType = (string) request()->param('product_type', 'normal');
            $activityType = (string) request()->param('activity_type', 'normal');
            $activityId = (int) request()->param('activity_id', 0);
            $num = (int) request()->param('num', 1);

            if (!$productId) {
                return app('json')->fail('商品ID不能为空');
            }

            // 1. 获取交易模式处理器并校验交易规则
            /** @var TradeModeRegistry $tradeModeRegistry */
            $tradeModeRegistry = app()->make(TradeModeRegistry::class);
            $tradeModeHandler = $tradeModeRegistry->get($activityType);
            if ($activityType !== 'normal' && $activityId > 0) {
                // 交易模式 Handler 统一校验：验证活动资格、限购规则
                $tradeModeHandler->beforeCreateOrder($productId, $skuUnique, $num, $activityId, $uid);
                $tradeModeHandler->validateOrder($productId, $skuUnique, $num, $activityId, $uid);
            }

            // 2. 获取基础数据并构建购物车信息
            [$cartInfo, $priceData, $productInfo, $skuInfo, $registry]
                = $this->buildOrderData($uid, $productId, $skuUnique, $productType, $num, $activityType, $activityId);
            // 获取产品类型处理器（用于获取配送方式、自定义表单等）
            $handler = $registry->get($productType);
            $pricingPolicy = method_exists($handler, 'getPricingPolicy') ? $handler->getPricingPolicy() : [];

            // 3. 缓存订单信息，返回 orderKey
            /** @var OrderCenterUserServices $orderServices */
            $orderServices = app()->make(OrderCenterUserServices::class);
            $orderKey = $orderServices->cacheOrderInfo($uid, $cartInfo, $priceData, [
                'product_type'  => $productType,
                'activity_type' => $activityType,
                'activity_id'   => $activityId,
                'order_mode'    => 'direct',
                'integralRatio' => sys_config('integral_ratio', 0),
                'pricing_policy' => $pricingPolicy,
            ], 600);

            // 3. 获取用户信息
            /** @var UserServices $userServices */
            $userServices = app()->make(UserServices::class);
            $userInfo = $userServices->getUserInfo($uid);
            if (!$userInfo) {
                return app('json')->fail('用户信息不存在');
            }
            if (is_object($userInfo)) $userInfo = $userInfo->toArray();
            // 检查用户 VIP 等级
            $levelServices = app()->make(\app\services\user\UserLevelServices::class);
            $userLevel = $levelServices->getUerLevelInfoByUid($uid);
            $userInfo['vip'] = $userLevel !== false;
            if ($userInfo['vip']) {
                $userInfo['vip_id'] = $userLevel['id'] ?? 0;
                $userInfo['discount'] = $userLevel['discount'] ?? 0;
            }

            // 4. 获取用户可用积分
            /** @var UserBillServices $userBillServices */
            $userBillServices = app()->make(UserBillServices::class);
            $usableIntegral = bcsub(
                (string) ($userInfo['integral'] ?? 0),
                (string) $userBillServices->getBillSum(['uid' => $uid, 'is_frozen' => 1]),
                0
            );
            // 检查积分是否开启
            $integralRatio = sys_config('integral_ratio', 0);
            $integralOpen = (float) $integralRatio > 0 || $usableIntegral > 0;

            // 活动订单不走优惠券自动匹配，标记前置（优惠券列表构建时使用）
            $isActivityOrder = $activityType !== 'normal' && $activityId > 0;

            // 5. 获取用户可用优惠券
            $couponList = [];
            try {
                /** @var StoreCouponUserServices $couponServices */
                $couponServices = app()->make(StoreCouponUserServices::class);
                $cartGroup = ['valid' => $cartInfo];
                $couponList = $couponServices->getUsableCouponList($uid, $cartGroup);
            } catch (\Throwable $e) {
                // 优惠券列表获取失败不影响主流程
            }
            // 5.1 用户尚无可用券时，自动领取整单最优优惠券并入列表（预览即真实领取，与 computed/create 自动用券同口径）
            if (!$isActivityOrder && empty($couponList)) {
                try {
                    $autoCoupon = app()->make(OrderComputedServices::class)->autoMatchCoupon($uid, $cartInfo, true);
                    if (!empty($autoCoupon['coupon_user_id'])) {
                        $autoRow = $couponServices->get((int)$autoCoupon['coupon_user_id'], ['*'], ['issue']);
                        if ($autoRow) {
                            $autoRow = $autoRow->toArray();
                            // 行结构与 getUsableCouponList 对齐（前端优惠券选择器字段）
                            $autoRow['title'] = $autoRow['coupon_title'];
                            $autoRow['type'] = $autoRow['applicable_type'] ?? 0;
                            $autoRow['use_min_price'] = floatval($autoRow['use_min_price'] ?? 0);
                            $autoRow['coupon_price'] = floatval($autoRow['coupon_price'] ?? 0);
                            $autoRow['start_time'] = $autoRow['start_time'] ? date('Y/m/d', $autoRow['start_time']) : date('Y/m/d', $autoRow['add_time']);
                            $autoRow['add_time'] = date('Y/m/d', (int)$autoRow['add_time']);
                            $autoRow['end_time'] = date('Y/m/d', (int)$autoRow['end_time']);
                            $autoRow['is_use'] = 0;
                            $couponList[] = $autoRow;
                        }
                    }
                } catch (\Throwable $e) {
                    // 自动领券失败不影响确认页主流程
                }
            }

            // 6. 获取商品详情扩展数据（自定义表单、商品参数）
            $detailExtend = $handler->getDetailExtend($productId);

            // 转换自定义表单格式，适配前端期望结构
            $customForm = $detailExtend['custom_form'] ?? [];
            $formattedCustomForm = [];
            foreach ($customForm as $key => $item) {
                $formattedCustomForm[] = [
                    'label' => $item['title'] ?? '',
                    'field' => 'custom_field_' . $key,
                    'type' => $item['label'] === 'text' ? 'input' : ($item['label'] === 'textarea' ? 'textarea' : $item['label']),
                    'required' => !empty($item['status']),
                ];
            }

            // 功能开关配置，由后端控制前端展示哪些功能
            // 活动订单默认禁用优惠券和积分抵扣，具体由交易模式处理器控制
            $activityAllowedPayTypes = [];
            $activityExtraInfo = [];
            $freePayType = '';
            $payRedirectUrl = '';
            // 活动订单信息
            if ($isActivityOrder) {
                $activityAllowedPayTypes = $tradeModeHandler->getAllowedPayTypes($activityId);
                $activityExtraInfo = $tradeModeHandler->getOrderExtraInfo($activityId);
                // 免费支付场景（如积分兑换）：下发支付方式标识与跳转地址，前端不再硬编码
                if ($tradeModeHandler->isFreePay($activityId)) {
                    $freePayType = $tradeModeHandler->getFreePayType($activityId);
                    $payRedirectUrl = $tradeModeHandler->getPayRedirectUrl($activityId);
                }
            }
            // 从商品中心表读取商品级物流配置
            // 从产品信息中读取配送能力
            $expressShipping = $productInfo['express_shipping'] ?? false;
            $storePickup = $productInfo['store_pickup'] ?? false;
            $doorService = $productInfo['door_service'] ?? false;
            // 上门费用：从商品 freight_config.door_service.postage 读取
            $doorServicePostage = (float)($productInfo['door_service_postage'] ?? 0);
            // 功能开关
            $features = [
                'express_shipping' => $expressShipping,  // 是否支持快递配送
                'store_pickup'     => $storePickup,      // 是否支持到店自提
                'door_service'     => $doorService,      // 是否支持上门服务
                'door_service_postage' => $doorServicePostage, // 上门服务费用（元）
                'remark'        => true,                                          // 买家留言，与配送方式无关
                'invoice'       => (bool)sys_config('invoice_func_status', 0),    // 发票，与商品类型无关
                'integral'      => !$isActivityOrder && $integralOpen,              // 积分抵扣，与商品类型无关
                'coupon'        => !$isActivityOrder && !empty($couponList),        // 优惠券，与商品类型无关
                'custom_form'   => !empty($formattedCustomForm),                    // 自定义表单，与商品类型无关
                'params_list'   => !empty($detailExtend['params_list'] ?? []),        // 商品参数，与商品类型无关
                'visitor_form'  => !empty($detailExtend['visitor_form_config'] ?? []),  // 游客信息表单（门票专属，按数量倍增）
            ];

            $data = [
                'order_key'      => $orderKey,// 订单号
                'product_type'   => $productType,// 商品类型
                'activity_type'  => $activityType,// 活动类型
                'activity_id'    => $activityId,// 活动ID
                'features'       => $features,// 功能开关配置
                'cartInfo'       => $cartInfo,// 购物车信息
                'total_price'    => $priceData['totalPrice'],// 订单总金额
                'pay_price'      => $priceData['payPrice'],// 应付金额
                'pay_postage'    => $priceData['payPostage'],// 配送费
                'priceGroup'     => [
                    'totalPrice'          => $priceData['totalPrice'],// 订单总金额
                    'payPostage'          => $priceData['payPostage'],// 配送费
                    'postageDiscount'     => 0,// 配送费优惠
                    'levelPrice'          => $priceData['levelPrice'] ?? 0,// 等级优惠
                    'memberPrice'         => $priceData['memberPrice'] ?? 0,// 付费会员优惠
                    'couponPrice'         => 0,// 优惠券优惠
                    'deductionPrice'      => 0,// 积分抵扣
                    'giftPrice'           => 0,// 礼品优惠
                ],
                'userInfo'        => $userInfo,// 用户信息
                'usable_integral' => $usableIntegral,// 可用积分
                'integral_open'   => $integralOpen,// 是否开启积分抵扣
                'integral_ratio'  => $integralRatio,// 积分抵扣比例
                'coupon_list'     => $couponList,// 优惠券列表
                'custom_form'     => $formattedCustomForm,// 自定义表单
                'params_list'     => $detailExtend['params_list'] ?? [],// 商品参数
                'visitor_form_config' => $detailExtend['visitor_form_config'] ?? [],// 游客信息表单配置（门票专属）
                // 活动订单额外信息
                'activity_info'   => $isActivityOrder ? array_merge(
                    $activityExtraInfo,// 活动订单额外信息
                    [
                        'allowed_pay_types' => $activityAllowedPayTypes,// 允许支付方式
                        'free_pay_type'     => $freePayType,// 免费支付方式标识（空表示需走收银台）
                        'pay_redirect_url'  => $payRedirectUrl,// 免费支付完成后跳转地址（含 {order_no} 占位符）
                    ]
                ) : [],
            ];

            return app('json')->success($data);
        } catch (\Throwable $e) {
            return app('json')->fail($e->getMessage());
        }
    }

    /**
     * 购物车模式订单确认
     * 多商品合并下单：校验所有商品产品类型一致（不同产品类型提示分开下单），
     * 汇总价格并缓存，响应结构与单品直购模式保持一致
     * @param int $uid 用户ID
     * @return mixed
     */
    protected function cartConfirm(int $uid)
    {
        $cartId = (string) request()->param('cartId', '');
        $new = (bool) request()->param('new', false);
        $addressId = (int) request()->param('address_id', 0);
        $shippingType = (int) request()->param('shipping_type', 1);
        $isGift = (int) request()->param('is_gift', 0);

        if (!is_string($cartId) || !$cartId) {
            return app('json')->fail('请提交购买的商品');
        }

        // 获取收货地址（用于运费模板不送达校验；自提/礼品场景无需地址）
        $addr = [];
        if ($shippingType != 2 && $isGift != 1) {
            /** @var UserAddressServices $addressServices */
            $addressServices = app()->make(UserAddressServices::class);
            if ($addressId) {
                $addressInfo = $addressServices->getOne(['uid' => $uid, 'id' => $addressId, 'is_del' => 0]);
                if ($addressInfo) {
                    $addr = $addressInfo->toArray();
                }
            }
            // 未传地址或地址已失效时，回退取默认地址
            if (!$addr) {
                $defaultAddr = $addressServices->getUserDefaultAddress($uid);
                if ($defaultAddr) {
                    $addr = $defaultAddr->toArray();
                }
            }
        }

        // 获取购物车数据（含价格计算、有效/无效分离、不送达校验）
        /** @var StoreCartServices $cartServices */
        $cartServices = app()->make(StoreCartServices::class);
        $cartGroup = $cartServices->getUserProductCartListV1($uid, $cartId, $new, $addr, $shippingType, $isGift);
        $validCartInfo = $cartGroup['valid'] ?? [];
        if (empty($validCartInfo)) {
            return app('json')->fail('暂无可购买商品，请重新选择');
        }
        // 合单商品类型已在前置校验中确认一致，取首件类型获取对应处理器。
        $productType = (string)($validCartInfo[0]['product_type'] ?? $validCartInfo[0]['productInfo']['product_type'] ?? 'normal');

        // 校验所有商品产品类型一致：相同产品类型可合并下单，不同产品类型提示分开下单
        $productTypes = array_values(array_unique(array_map(function ($item) {
            return (string)($item['productInfo']['product_type'] ?? 'normal');
        }, $validCartInfo)));
        if (count($productTypes) > 1) {
            return app('json')->fail('不同产品类型的商品请分开下单');
        }
        $productType = $productTypes[0];

        // 计算价格组（含运费模板运费、会员运费折扣），与旧版确认页结构一致
        /** @var OrderComputedServices $computedServices */
        $computedServices = app()->make(OrderComputedServices::class);
        $storeFreePostage = (float)sys_config('store_free_postage') ?: 0;
        $userInfo = app()->make(UserServices::class)->getUserInfo($uid);
        if (!$userInfo) {
            return app('json')->fail('用户信息不存在');
        }
        if (is_object($userInfo)) $userInfo = $userInfo->toArray();
        $priceGroup = $computedServices->getOrderPriceGroup($storeFreePostage, $validCartInfo, $addr, $userInfo, $shippingType, $isGift);
        $validCartInfo = $priceGroup['cartInfo'] ?? $validCartInfo;

        // 组装与单品直购模式一致的 priceData，供 computed/create 读取
        // 送礼订单：应付价 = 商品总价 + 礼品附加费（无运费）
        $giftPrice = $priceGroup['giftPrice'] ?? 0;
        $priceData = [
            'totalPrice'         => $priceGroup['totalPrice'] ?? 0,
            'payPrice'           => bcadd(
                (string)bcadd((string)($priceGroup['totalPrice'] ?? 0), (string)($priceGroup['storePostage'] ?? 0), 2),
                (string)$giftPrice,
                2
            ),
            'payPostage'         => $priceGroup['storePostage'] ?? 0,
            'isStoreFreePostage' => $priceGroup['isStoreFreePostage'] ?? false,
            'storeFreePostage'   => $priceGroup['storeFreePostage'] ?? 0,
            'costPrice'          => $priceGroup['costPrice'] ?? 0,
            'giftPrice'          => $giftPrice,
            'levelPrice'         => $priceGroup['levelPrice'] ?? 0,
            'memberPrice'        => $priceGroup['memberPrice'] ?? 0,
        ];
        /** @var ProductTypeRegistry $productTypeRegistry */
        $productTypeRegistry = app()->make(ProductTypeRegistry::class);
        $handler = $productTypeRegistry->get($productType);

        // 缓存订单信息，返回 orderKey（create 阶段据此读取购物车数据）
        /** @var OrderCenterUserServices $orderServices */
        $orderServices = app()->make(OrderCenterUserServices::class);
        $orderKey = $orderServices->cacheOrderInfo($uid, $validCartInfo, $priceData, [
            'product_type'  => $productType,
            'activity_type' => 'normal',
            'activity_id'   => 0,
            'order_mode'    => 'cart',
            'cart_ids'      => $cartId,   // 下单成功后清除购物车
            'new'           => $new,      // 是否缓存key模式（立即购买）
            'deduction'     => $cartGroup['deduction'] ?? [],
            'integralRatio' => sys_config('integral_ratio', 0),
            'pricing_policy' => method_exists($handler, 'getPricingPolicy') ? $handler->getPricingPolicy() : [],
        ], 600);

        // 用户 VIP 等级信息
        $levelServices = app()->make(\app\services\user\UserLevelServices::class);
        $userLevel = $levelServices->getUerLevelInfoByUid($uid);
        $userInfo['vip'] = $userLevel !== false;
        if ($userInfo['vip']) {
            $userInfo['vip_id'] = $userLevel['id'] ?? 0;
            $userInfo['discount'] = $userLevel['discount'] ?? 0;
        }

        // 可用积分
        $usableIntegral = bcsub(
            (string)($userInfo['integral'] ?? 0),
            (string)app()->make(UserBillServices::class)->getBillSum(['uid' => $uid, 'is_frozen' => 1]),
            0
        );
        $integralRatio = sys_config('integral_ratio', 0);
        $integralOpen = (float)$integralRatio > 0 || $usableIntegral > 0;

        // 可用优惠券
        $couponList = [];
        try {
            $couponList = app()->make(StoreCouponUserServices::class)->getUsableCouponList($uid, $cartGroup);
        } catch (\Throwable $e) {
            // 优惠券列表获取失败不影响主流程
        }

        // 支付渠道状态
        /** @var PayChannelServices $payChannelServices */
        $payChannelServices = app()->make(PayChannelServices::class);

        // 门店自提是否开启（系统配置开启且存在门店）
        $storeSelfMention = (int)sys_config('store_self_mention');
        if ($storeSelfMention) {
            /** @var SystemStoreServices $storeServices */
            $storeServices = app()->make(SystemStoreServices::class);
            $storeCount = $storeServices->count(['type' => 0]);
            $storeSelfMention = $storeSelfMention && $storeCount;
        }

        // 发票开关
        $invoiceFunc = app()->make(\app\services\user\UserInvoiceServices::class)->invoiceFuncStatus();

        // 配送方式：合单需所有商品统一支持该方式，从商品 delivery_methods 聚合（与单品直购 confirm 的 express_shipping/store_pickup/door_service 语义一致）
        $expressShipping = true;
        $storePickupAll = true;
        $doorServiceAll = true;
        foreach ($validCartInfo as $item) {
            $methods = $item['productInfo']['delivery_methods'] ?? [];
            if (is_string($methods)) {
                $methods = json_decode($methods, true) ?: [];
            }
            $methods = is_array($methods) ? $methods : [];
            if (!in_array('express', $methods, true)) {
                $expressShipping = false;
            }
            if (!in_array('pickup', $methods, true)) {
                $storePickupAll = false;
            }
            if (!in_array('door_service', $methods, true)) {
                $doorServiceAll = false;
            }
        }

        // 上门费用：取第一个支持上门服务商品的上门费用（购物车模式下按首商品计费）
        $doorServicePostage = 0.00;
        foreach ($validCartInfo as $item) {
            if (!empty($item['productInfo']['door_service_postage'])) {
                $doorServicePostage = (float)$item['productInfo']['door_service_postage'];
                break;
            }
        }

        // 自定义表单：合单只能填一份表单，取主商品（首项）定义
        // 与 create 阶段取首项 productId 的逻辑保持一致，确保前后端字段映射统一
        $cartCustomForm = [];
        $cartVisitorFormConfig = [];
        try {
            /** @var ProductTypeRegistry $registry */
            $registry = app()->make(ProductTypeRegistry::class);
            $handler = $registry->get($productType);
            $cartProductId = (int)($validCartInfo[0]['product_id'] ?? $validCartInfo[0]['productInfo']['id'] ?? 0);
            if ($cartProductId && method_exists($handler, 'getDetailExtend')) {
                $detailExtend = $handler->getDetailExtend($cartProductId);
                // 转换自定义表单格式，适配前端期望结构（与单品直购 confirm 一致）
                $rawCustomForm = $detailExtend['custom_form'] ?? [];
                foreach ($rawCustomForm as $key => $item) {
                    $label = $item['label'] ?? '';
                    $cartCustomForm[] = [
                        'label'    => $item['title'] ?? '',
                        'field'    => 'custom_field_' . $key,
                        'type'     => $label === 'text' ? 'input' : ($label === 'textarea' ? 'textarea' : $label),
                        'required' => !empty($item['status']),
                    ];
                }
                // 游客信息表单配置（门票专属，取主商品定义）
                $cartVisitorFormConfig = $detailExtend['visitor_form_config'] ?? [];
            }
        } catch (\Throwable $e) {
            // 获取自定义表单失败不影响主流程
            $cartCustomForm = [];
            $cartVisitorFormConfig = [];
        }

        // 功能开关配置
        // 送礼订单：收货信息由领取人填写，关闭配送/自提/上门入口
        $features = [
            'express_shipping' => $isGift != 1 && $expressShipping,                 // 是否支持快递配送（所有商品均支持快递）
            'store_pickup'     => $isGift != 1 && (bool)$storeSelfMention && $storePickupAll,// 是否支持到店自提（系统开启且所有商品均支持自提）
            'door_service'     => $isGift != 1 && $doorServiceAll,                  // 是否支持上门服务（所有商品均支持上门）
            'door_service_postage' => $doorServicePostage,          // 上门服务费用（元）
            'remark'           => true,                             // 买家留言
            'invoice'          => (bool)sys_config('invoice_func_status', 0),// 发票
            'integral'         => $integralOpen,                    // 积分抵扣
            'coupon'           => !empty($couponList),              // 优惠券
            'custom_form'      => !empty($cartCustomForm),          // 自定义表单（取主商品定义）
            'params_list'      => false,                            // 商品参数（下单页不展示）
            'visitor_form'     => !empty($cartVisitorFormConfig),   // 游客信息表单（门票专属，取主商品定义）
            'gift'             => $isGift == 1,                     // 送礼订单标记
            'gift_mark'        => true,                             // 送礼留言输入
        ];

        $deduction = $cartGroup['deduction'] ?? [];
        $data = [
            'order_key'          => $orderKey,// 订单缓存key
            'product_type'       => $productType,// 商品类型
            'activity_type'      => 'normal',// 活动类型
            'activity_id'        => 0,// 活动ID
            'features'           => $features,// 功能开关配置
            'cartInfo'           => $validCartInfo,// 有效购物车商品（失效商品不参与合单展示与计价）
            'valid_count'        => count($validCartInfo),// 有效商品数量
            'total_price'        => $priceGroup['totalPrice'] ?? 0,// 订单总金额
            'pay_price'          => $priceData['payPrice'],// 应付金额
            'pay_postage'        => $priceGroup['storePostage'] ?? 0,// 配送费
            'priceGroup'         => [
                'totalPrice'      => $priceGroup['totalPrice'] ?? 0,// 订单总金额
                'payPostage'      => $priceGroup['storePostage'] ?? 0,// 配送费
                'postageDiscount' => $priceGroup['storePostageDiscount'] ?? 0,// 配送费优惠
                'levelPrice'      => $priceGroup['levelPrice'] ?? 0,// 等级优惠
                'memberPrice'     => $priceGroup['memberPrice'] ?? 0,// 付费会员优惠
                'couponPrice'     => 0,// 优惠券优惠
                'deductionPrice'  => 0,// 积分抵扣
                'giftPrice'       => $priceGroup['giftPrice'] ?? 0,// 礼品优惠
            ],
            'userInfo'           => $userInfo,// 用户信息
            'usable_integral'    => $usableIntegral,// 可用积分
            'integral_open'      => $integralOpen,// 是否开启积分抵扣
            'integral_ratio'     => $integralRatio,// 积分抵扣比例
            'integralRatio'      => $integralRatio,// 兼容旧字段名
            'coupon_list'        => $couponList,// 优惠券列表
            'custom_form'        => $cartCustomForm,// 自定义表单（取主商品定义）
            'params_list'        => [],// 商品参数（下单页不展示）
            'visitor_form_config' => $cartVisitorFormConfig,// 游客信息表单配置（门票专属，取主商品定义）
            'virtual_type'       => (($handler->getCapabilities()['order']['virtual'] ?? false) === true) ? 1 : 0,// 是否虚拟商品
            'store_self_mention' => (int)$storeSelfMention,// 门店自提开关
            'seckill_id'         => $deduction['seckill_id'] ?? 0,// 秒杀活动ID
            'combination_id'     => $deduction['combination_id'] ?? 0,// 拼团活动ID
            'bargain_id'         => $deduction['bargain_id'] ?? 0,// 砍价活动ID
            'advance_id'         => $deduction['advance_id'] ?? 0,// 预售活动ID
            'deduction'          => !empty(array_filter($deduction)),// 是否含活动商品
            'offlinePostage'     => sys_config('offline_postage'),// 线下配送费
            'offline_pay_status' => $payChannelServices->getChannelStatus('offline') ? 1 : 2,// 线下支付开关
            'yue_pay_status'     => $payChannelServices->getChannelStatus('yue') ? 1 : 2,// 余额支付渠道开关
            'pay_weixin_open'    => $payChannelServices->getChannelStatus('weixin'),// 微信支付开关
            'ali_pay_status'     => $payChannelServices->getChannelStatus('alipay'),// 支付宝开关
            'friend_pay_status'  => $payChannelServices->getChannelStatus('friend') ? 1 : 0,// 好友代付开关
            'invoice_func'       => $invoiceFunc['invoice_func'],// 发票功能开关
            'special_invoice'    => $invoiceFunc['special_invoice'],// 专票开关
            'activity_info'      => [],// 活动信息（购物车模式无活动）
        ];

        return app('json')->success($data);
    }

    /**
     * 计算订单金额（切换优惠券/积分时重算）
     */
    public function computed()
    {
        try {
            $uid = (int) request()->uid();
            $key = (string) request()->param('key', '');
            $couponId = (int) request()->param('coupon_id', 0);
            $useIntegral = (bool) request()->param('use_integral', false);
            $addressId = (int) request()->param('address_id', 0);
            $deliveryType = $this->normalizeOrderDeliveryType((string) request()->param('delivery_type', ''));
            $storeId = (int) request()->param('store_id', 0);
            // 送礼订单：不选地址、不计运费，累加礼品附加费
            $isGift = (int) request()->param('is_gift', 0);

            if (!$key) {
                return app('json')->fail('参数错误');
            }

            /** @var OrderCenterUserServices $orderServices */
            $orderServices = app()->make(OrderCenterUserServices::class);
            $cartGroup = $orderServices->getCacheOrderInfo($uid, $key);
            if (!$cartGroup) {
                return app('json')->fail('订单已过期，请刷新页面');
            }
            // 价格能力以商品类型 Handler 的后端声明为准，不能信任客户端参数。
            $productType = (string)($cartGroup['other']['product_type'] ?? $cartGroup['product_type'] ?? 'normal');
            $handler = app()->make(ProductTypeRegistry::class)->get($productType);
            $pricingPolicy = method_exists($handler, 'getPricingPolicy') ? $handler->getPricingPolicy() : [];
            if (($pricingPolicy['integral'] ?? true) !== true) {
                $useIntegral = false;
            }
            if (($pricingPolicy['coupon'] ?? true) !== true) {
                $couponId = 0;
            }
            $isActivityOrder = (($cartGroup['activity_type'] ?? 'normal') !== 'normal')
                && (int)($cartGroup['activity_id'] ?? 0) > 0;
            $cartInfo = $cartGroup['cartInfo'] ?? [];

            // 重算金额必须使用当前用户上下文，积分抵扣和会员价格都依赖用户信息。
            /** @var UserServices $userServices */
            $userServices = app()->make(UserServices::class);
            $userInfo = $userServices->getUserInfo($uid);
            if (!$userInfo) {
                return app('json')->fail('用户信息不存在');
            }
            if (is_object($userInfo)) {
                $userInfo = $userInfo->toArray();
            }
            /** @var \app\services\user\UserLevelServices $levelServices */
            $levelServices = app()->make(\app\services\user\UserLevelServices::class);
            $userLevel = $levelServices->getUerLevelInfoByUid($uid);
            $userInfo['vip'] = $userLevel !== false;
            if ($userInfo['vip']) {
                $userInfo['vip_id'] = $userLevel['id'] ?? 0;
                $userInfo['discount'] = $userLevel['discount'] ?? 0;
            }

            // 普通商品预览也必须重新读取当前商品、SKU和用户价格，不能使用确认页旧快照。
            $shippingType = $deliveryType === 'pickup' ? 2 : ($deliveryType === 'door' ? 3 : 1);
            $hasCartActivity = false;
            foreach ($cartInfo as $cartItem) {
                if (!empty($cartItem['seckill_id'])
                    || !empty($cartItem['bargain_id'])
                    || !empty($cartItem['combination_id'])
                    || !empty($cartItem['advance_id'])) {
                    $hasCartActivity = true;
                    break;
                }
            }
            if ($productType === 'normal' && !$isActivityOrder && !$hasCartActivity) {
                // 先取当前地址用于重建，配送能力校验放到重建后，避免旧缓存的能力字段阻断当前商品。
                $rebuildAddressInfo = [];
                if ($deliveryType === 'delivery' && $addressId > 0) {
                    /** @var UserAddressServices $addressServices */
                    $addressServices = app()->make(UserAddressServices::class);
                    $rebuildAddressInfo = $addressServices->getOne([
                        'uid'    => $uid,
                        'id'     => $addressId,
                        'is_del' => 0,
                    ]);
                    if (is_object($rebuildAddressInfo)) {
                        $rebuildAddressInfo = $rebuildAddressInfo->toArray();
                    }
                    $rebuildAddressInfo = is_array($rebuildAddressInfo) ? $rebuildAddressInfo : [];
                }
                /** @var StoreCartServices $cartServices */
                $cartServices = app()->make(StoreCartServices::class);
                $freshCartGroup = $cartServices->rebuildNormalCheckoutCart(
                    $uid,
                    $cartInfo,
                    $rebuildAddressInfo,
                    $shippingType,
                    $isGift
                );
                $cartInfo = $freshCartGroup['valid'] ?? [];
                if (empty($cartInfo)) {
                    return app('json')->fail('商品已失效，请返回确认页刷新');
                }
                if (!empty($freshCartGroup['invalid'])) {
                    return app('json')->fail('商品库存或配送条件已变化，请返回确认页刷新');
                }
                // 送礼订单由领取人填收货信息，下单阶段不校验配送地址
                $deliveryContext = ['address_info' => [], 'store_info' => []];
                if ($isGift != 1) {
                    // 商品信息可能已变化，必须以重建后的商品能力再次校验配送方式。
                    $deliveryContext = $this->validateNormalOrderDelivery(
                        $cartInfo,
                        $deliveryType,
                        $uid,
                        $addressId,
                        $storeId,
                        [],
                        false
                    );
                }
                /** @var OrderComputedServices $computedServices */
                $computedServices = app()->make(OrderComputedServices::class);
                $priceGroup = $computedServices->getOrderPriceGroup(
                    (float)sys_config('store_free_postage') ?: 0,
                    $cartInfo,
                    $deliveryContext['address_info'],
                    $userInfo,
                    $shippingType,
                    $isGift
                );
                $cartGroup['cartInfo'] = $cartInfo;
                $cartGroup['priceData'] = [
                    'totalPrice'         => $priceGroup['totalPrice'] ?? 0,
                    'payPrice'           => bcadd((string)($priceGroup['totalPrice'] ?? 0), (string)($priceGroup['storePostage'] ?? 0), 2),
                    'payPostage'         => $priceGroup['storePostage'] ?? 0,
                    'isStoreFreePostage' => $priceGroup['isStoreFreePostage'] ?? false,
                    'storeFreePostage'   => $priceGroup['storeFreePostage'] ?? 0,
                    'costPrice'          => $priceGroup['costPrice'] ?? 0,
                    'giftPrice'          => $priceGroup['giftPrice'] ?? 0,
                    'levelPrice'         => $priceGroup['levelPrice'] ?? 0,
                    'memberPrice'        => $priceGroup['memberPrice'] ?? 0,
                ];
            }

            // 用户未选券时自动领取并使用整单最优优惠券（折扣对应真实领取的券，与商品详情到手价口径一致；失败降级为无券）
            if (!$couponId && !$isActivityOrder && ($pricingPolicy['coupon'] ?? true)) {
                /** @var OrderComputedServices $computedServices */
                $computedServices = app()->make(OrderComputedServices::class);
                $autoCoupon = $computedServices->autoMatchCoupon($uid, $cartInfo, true);
                if (!empty($autoCoupon['coupon_user_id'])) {
                    $couponId = (int)$autoCoupon['coupon_user_id'];
                }
            }

            // 手动校验并计算优惠券金额（不依赖 useCouponId 的 getOne 查询）
            $couponPrice = 0;
            $couponTitle = '';
            if ($couponId) {
                /** @var \app\services\activity\coupon\StoreCouponUserServices $couponServices */
                $couponServices = app()->make(\app\services\activity\coupon\StoreCouponUserServices::class);
                // 通过主键查询，预加载 issue 关联以获取 applicable_type
                $couponInfo = $couponServices->get($couponId, ['*'], ['issue']);
                if (!$couponInfo) {
                    return app('json')->fail('选择的优惠劵无效');
                }
                $couponTitle = (string)$couponInfo['coupon_title'];
                // 使用 getData 获取原始值，避免 Model 访问器转换（status→"未使用"、type→"后台发放"等）
                $rawStatus = $couponInfo->getData('status');
                $rawIsFail = $couponInfo->getData('is_fail');
                if ($rawIsFail != 0 || $rawStatus != 0) {
                    return app('json')->fail('选择的优惠劵无效');
                }
                if ($couponInfo['uid'] != $uid) {
                    return app('json')->fail('选择的优惠劵无效');
                }
                if ($couponInfo['start_time'] > time()) {
                    return app('json')->fail('选择的优惠劵无效');
                }
                if ($couponInfo['end_time'] < time()) {
                    return app('json')->fail('选择的优惠劵无效');
                }
                // 计算适用商品价格
                $price = '0';
                $count = 0;
                // 使用 getData 获取原始 type 值（避免 getTypeAttr 访问器将整数转为中文）
                $type = $couponInfo['applicable_type'] ?? $couponInfo->getData('type') ?? 0;
                switch ($type) {
                    case 0:
                    case 3:
                        foreach ($cartInfo as $cart) {
                            $price = bcadd($price, bcmul((string)$cart['truePrice'], (string)$cart['cart_num'], 2), 2);
                            $count++;
                        }
                        break;
                    case 1:
                        $storeCategoryServices = app()->make(\app\dao\product\product\StoreCategoryDao::class);
                        $coupon_category = explode(',', (string)$couponInfo['category_id']);
                        $category_ids = $storeCategoryServices->getAllById($coupon_category);
                        if ($category_ids) {
                            $cateIds = array_column($category_ids, 'id');
                            foreach ($cartInfo as $cart) {
                                if (isset($cart['productInfo']['cate_id']) && array_intersect(explode(',', $cart['productInfo']['cate_id']), $cateIds)) {
                                    $price = bcadd($price, bcmul((string)$cart['truePrice'], (string)$cart['cart_num'], 2), 2);
                                    $count++;
                                }
                            }
                        }
                        break;
                    case 2:
                        foreach ($cartInfo as $cart) {
                            if (isset($cart['product_id']) && in_array($cart['product_id'], explode(',', $couponInfo['product_id']))) {
                                $price = bcadd($price, bcmul((string)$cart['truePrice'], (string)$cart['cart_num'], 2), 2);
                                $count++;
                            }
                        }
                        break;
                }
                if ($count && $couponInfo['use_min_price'] <= $price) {
                    $couponPrice = min($couponInfo['coupon_price'], $price);
                } else {
                    return app('json')->fail('不满足优惠劵的使用条件');
                }
            }

            // 缓存数据使用 priceData 作为 key，但 computedOrder 需要 priceGroup，做结构归一化
            $modifiedGroup = $cartGroup;
            $modifiedGroup['priceGroup'] = $cartGroup['priceData'] ?? [];
            $modifiedGroup['other'] = [
                'integralRatio' => $cartGroup['integralRatio'] ?? sys_config('integral_ratio', 0),
                'pricing_policy' => $pricingPolicy,
            ];
            // 记录原始总价，供结果修正使用
            $originalTotalPrice = $modifiedGroup['priceGroup']['totalPrice'] ?? 0;

            // 将优惠券金额从总价中扣除，再传给 computedOrder（不传 coupon_id）
            // 这样积分抵扣在优惠后的价格上计算，顺序正确
            $modifiedGroup['priceGroup']['totalPrice'] = bcsub((string)$originalTotalPrice, (string)$couponPrice, 2);

            // 自提模式传入门店信息，使后续流程可获取门店相关数据
            if ($storeId) {
                $modifiedGroup['store_id'] = $storeId;
            }

            /** @var OrderComputedServices $computedServices */
            $computedServices = app()->make(OrderComputedServices::class);
            $result = $computedServices->computedOrder(
                $uid, $userInfo, $modifiedGroup, $addressId, '',
                $useIntegral, 0, false, $shippingType, $isGift
            );

            // 修正返回字段：total_price 用原始总价，coupon_price 用计算值
            $result['total_price'] = $originalTotalPrice;
            $result['coupon_price'] = $couponPrice;
            // 自动用券回显：前端据此展示已选中的优惠券
            $result['coupon_id'] = $couponId;
            $result['coupon_title'] = $couponTitle;

            // 自动领取发生在确认接口返回之后时，重新查询可用券并返回给前端。
            // 否则金额和标题已经更新，但优惠券选择弹窗仍使用确认阶段的旧列表。
            $result['coupon_list'] = [];
            if (!$isActivityOrder && ($pricingPolicy['coupon'] ?? true)) {
                try {
                    /** @var StoreCouponUserServices $couponServices */
                    $couponServices = app()->make(StoreCouponUserServices::class);
                    $result['coupon_list'] = $couponServices->getUsableCouponList($uid, ['valid' => $cartInfo]);
                } catch (\Throwable $e) {
                    // 优惠券列表刷新失败不影响金额计算和下单流程
                }
            }

            // 自提模式强制运费为 0；上门服务模式运费替换为商品上门费用（computedPayPostage 内部仍会检查
            // store_self_mention 系统配置，但产品级 store_pickup/door_service 开关已由 confirm 接口控制，
            // 前端发送 delivery_type='pickup'/'door' 即表示该商品支持到店自提/上门服务）
            if ($deliveryType === 'pickup') {
                // 如果 computedOrder 返回了运费，需从 pay_price 中扣除
                $originalPayPostage = $result['pay_postage'] ?? 0;
                $result['pay_postage'] = 0;
                if ($originalPayPostage > 0) {
                    $result['pay_price'] = bcsub((string)($result['pay_price'] ?? 0), (string)$originalPayPostage, 2);
                }
            } elseif ($deliveryType === 'door') {
                // 上门服务：运费 = 商品上门费用（实时从数据库读取，避免 confirm 缓存快照过期导致与 create 不一致）
                $doorPostage = 0.00;
                if (!empty($cartInfo[0]['product_id'])) {
                    try {
                        /** @var \app\services\product\CenterProductServices $centerServices */
                        $centerServices = app()->make(\app\services\product\CenterProductServices::class);
                        $doorInfo = $centerServices->getInfo((int)$cartInfo[0]['product_id']);
                        $doorProductInfo = $doorInfo['productInfo'] ?? $doorInfo;
                        $doorPostage = (float)($doorProductInfo['door_service_postage'] ?? 0);
                    } catch (\Throwable $e) {
                        // 读取失败时按免上门费处理，不影响主流程
                        $doorPostage = 0.00;
                    }
                }
                $originalPayPostage = $result['pay_postage'] ?? 0;
                $result['pay_postage'] = $doorPostage;
                // 扣除快递运费后按上门费用重算应付金额
                $basePrice = bcsub((string)($result['pay_price'] ?? 0), (string)$originalPayPostage, 2);
                $result['pay_price'] = bcadd($basePrice, (string)$doorPostage, 2);
            }

            return app('json')->success($result);
        } catch (\Throwable $e) {
            return app('json')->fail($e->getMessage());
        }
    }

    /**
     * 统一下单（中心商品直接购买，绕过购物车）
     * 接收商品ID和规格，直接创建订单并返回支付信息
     */
    public function create()
    {
        $stockDecrements = [];
        $stockHandler = null;
        $orderTransactionStarted = false;
        $orderServices = null;
        $orderCacheLockKey = '';
        $orderCacheLockAcquired = false;
        $orderCacheKeepLock = false;
        try {
            $uid = (int) request()->uid();
            $productId = (int) request()->param('product_id', 0);
            $skuUnique = (string) request()->param('sku_unique', '');
            $productType = (string) request()->param('product_type', 'normal');
            $activityType = (string) request()->param('activity_type', 'normal');
            $activityId = (int) request()->param('activity_id', 0);
            $formData = request()->param('order_data/a', []);
            $couponId = (int) request()->param('coupon_id', 0);
            $formDeliveryType = $this->normalizeOrderDeliveryType((string)($formData['delivery_type'] ?? ''));
            $formShippingType = $formDeliveryType === 'pickup' ? 2 : ($formDeliveryType === 'door' ? 3 : 1);
            $addressId = (int)($formData['address_id'] ?? 0);
            $storeId = (int)($formData['store_id'] ?? 0);
            // 送礼订单：地址由领取人填写，下单阶段不选地址、不计运费
            $isGift = (int)($formData['is_gift'] ?? request()->param('is_gift', 0));
            $giftMark = trim((string)($formData['gift_mark'] ?? request()->param('gift_mark', '')));
            $storeInfo = [];
            $validatedAddressInfo = [];
            $normalDeliveryValidated = false;
            // 创建订单必须携带 confirm 阶段返回的一次性凭证，避免绕过确认页及重复提交。
            $key = trim((string) request()->param('key', ''));
            if ($key === '') {
                return app('json')->fail('订单确认凭证不能为空，请返回确认页重新提交');
            }
            $requestProductId = $productId;
            $requestSkuUnique = $skuUnique;
            $requestNum = (int) request()->param('num', 0);
            $isCartMode = false;
            $shouldRepriceNormal = false;
            $cartIdsForRemove = '';
            $cartNewMode = false;

            /** @var OrderCenterUserServices $orderServices */
            $orderServices = app()->make(OrderCenterUserServices::class);
            $orderCacheLockKey = $orderServices->acquireOrderCacheLock($uid, $key);
            if ($orderCacheLockKey === null) {
                return app('json')->fail('订单已过期，请刷新页面');
            }
            if ($orderCacheLockKey === '') {
                return app('json')->fail('订单正在创建，请勿重复提交');
            }
            $orderCacheLockAcquired = true;

            $cartGroup = $orderServices->getCacheOrderInfo($uid, $key);
            if (!$cartGroup || empty($cartGroup['cartInfo'])) {
                return app('json')->fail('订单已过期，请刷新页面');
            }
            $cartInfo = $cartGroup['cartInfo'];
            $priceData = $cartGroup['priceData'] ?? [];
            $cacheMode = (string)($cartGroup['order_mode'] ?? '');
            // 兼容旧缓存：存在 cart_ids/new 才视为购物车，否则按直购缓存处理。
            $isCartMode = $cacheMode === 'cart'
                || ($cacheMode === '' && (array_key_exists('cart_ids', $cartGroup) || array_key_exists('new', $cartGroup)));
            $activityType = (string)($cartGroup['activity_type'] ?? 'normal');
            $activityId = (int)($cartGroup['activity_id'] ?? 0);
            $isActivityOrder = $activityType !== 'normal' && $activityId > 0;

            /** @var TradeModeRegistry $tradeModeRegistry */
            $tradeModeRegistry = app()->make(TradeModeRegistry::class);
            $tradeModeHandler = $tradeModeRegistry->get($activityType);

            // 校验确认缓存中的商品类型，避免客户端修改 product_type 绕过 Handler。
            $productTypes = array_values(array_unique(array_map(function ($item) {
                return (string)($item['productInfo']['product_type'] ?? 'normal');
            }, $cartInfo)));
            if (count($productTypes) > 1) {
                return app('json')->fail('不同产品类型的商品请分开下单');
            }
            $productType = (string)($cartGroup['product_type'] ?? ($productTypes[0] ?? 'normal'));
            if (!in_array($productType, $productTypes, true)) {
                $productType = $productTypes[0] ?? 'normal';
            }

            // 直购只能使用确认页缓存中的商品和规格，不能用请求参数替换订单主体。
            $cachedFirstItem = $cartInfo[0] ?? [];
            $productId = (int)($cachedFirstItem['product_id'] ?? $cachedFirstItem['productInfo']['id'] ?? 0);
            $skuUnique = (string)($cachedFirstItem['product_attr_unique']
                ?? $cachedFirstItem['productInfo']['attrInfo']['unique']
                ?? $skuUnique);
            $num = (int)($cachedFirstItem['cart_num'] ?? 1);
            if (!$productId) {
                return app('json')->fail('商品信息已失效，请返回确认页重新提交');
            }
            if (!$isCartMode) {
                if ($requestProductId > 0 && $requestProductId !== $productId) {
                    return app('json')->fail('商品信息已变化，请返回确认页重新提交');
                }
                if ($requestSkuUnique !== '' && $requestSkuUnique !== $skuUnique) {
                    return app('json')->fail('商品规格已变化，请返回确认页重新提交');
                }
                if ($requestNum > 0 && $requestNum !== $num) {
                    return app('json')->fail('购买数量已变化，请返回确认页重新提交');
                }
            }

            $skuInfo = [];
            /** @var ProductTypeRegistry $registry */
            $registry = app()->make(ProductTypeRegistry::class);
            $productInfo = $cachedFirstItem['productInfo'] ?? [];
            if ($isActivityOrder) {
                $cartSkuUnique = (string)($cachedFirstItem['product_attr_unique']
                    ?? $cachedFirstItem['productInfo']['attrInfo']['unique']
                    ?? $skuUnique);
                $tradeModeHandler->beforeCreateOrder($productId, $cartSkuUnique, $num, $activityId, $uid);
                $tradeModeHandler->validateOrder($productId, $cartSkuUnique, $num, $activityId, $uid);
            }

            // 记录下单成功后需清除的购物车ID（仅数据库购物车模式需要清除）。
            $cartIdsForRemove = (string)($cartGroup['cart_ids'] ?? '');
            $cartNewMode = (bool)($cartGroup['new'] ?? false);

            // 普通商品无活动时，创建订单前重新读取当前商品、SKU、库存和用户价格。
            // 购物车数据库模式从当前购物车读取，直购/立即购买模式从确认缓存重建。
            $hasCartActivity = false;
            foreach ($cartInfo as $cartItem) {
                if (!empty($cartItem['seckill_id'])
                    || !empty($cartItem['bargain_id'])
                    || !empty($cartItem['combination_id'])
                    || !empty($cartItem['advance_id'])) {
                    $hasCartActivity = true;
                    break;
                }
            }
            if ($productType === 'normal' && !$isActivityOrder && !$hasCartActivity) {
                $shouldRepriceNormal = true;
            }

            // 用户信息必须在最终核价前实时读取，会员价、运费折扣和积分余额都依赖当前状态。
            /** @var UserServices $userServices */
            $userServices = app()->make(UserServices::class);
            $userInfo = $userServices->getUserInfo($uid);
            if (!$userInfo) {
                return app('json')->fail('用户信息不存在');
            }
            if (is_object($userInfo)) {
                $userInfo = $userInfo->toArray();
            }

            // 普通商品创建订单前重新读取当前商品、SKU、库存和用户价格，不能信任确认页缓存。
            if ($shouldRepriceNormal) {
                /** @var StoreCartServices $cartServices */
                $cartServices = app()->make(StoreCartServices::class);
                if ($isCartMode && !$cartNewMode) {
                    if ($cartIdsForRemove === '') {
                        return app('json')->fail('购物车商品信息已失效，请返回确认页重新提交');
                    }
                    $freshCartGroup = $cartServices->getUserProductCartListV1(
                        $uid,
                        $cartIdsForRemove,
                        false,
                        [],
                        $formShippingType,
                        $isGift
                    );
                } else {
                    $freshCartGroup = $cartServices->rebuildNormalCheckoutCart(
                        $uid,
                        $cartInfo,
                        [],
                        $formShippingType,
                        $isGift
                    );
                }
                $cartInfo = $freshCartGroup['valid'] ?? [];
                if (empty($cartInfo)) {
                    return app('json')->fail('商品已失效，请返回确认页重新提交');
                }
                if (!empty($freshCartGroup['invalid'])) {
                    return app('json')->fail('库存不足或商品已不可配送，请返回确认页刷新');
                }
                $freshProductTypes = array_values(array_unique(array_map(function ($item) {
                    return (string)($item['productInfo']['product_type'] ?? 'normal');
                }, $cartInfo)));
                if (count($freshProductTypes) !== 1 || $freshProductTypes[0] !== 'normal') {
                    return app('json')->fail('商品类型已变化，请返回确认页重新提交');
                }
                $productId = (int)($cartInfo[0]['product_id'] ?? $cartInfo[0]['productInfo']['id'] ?? $productId);
                $skuUnique = (string)($cartInfo[0]['product_attr_unique']
                    ?? $cartInfo[0]['productInfo']['attrInfo']['unique']
                    ?? $skuUnique);
                $num = (int)($cartInfo[0]['cart_num'] ?? $num);
                $productInfo = $cartInfo[0]['productInfo'] ?? $productInfo;

                // 送礼订单由领取人填收货信息，下单阶段不校验配送地址
                if ($isGift == 1) {
                    $validatedAddressInfo = [];
                    $normalDeliveryValidated = true;
                } else {
                    // 重建后再次校验商品当前配送能力，并用当前地址计算运费。
                    $deliveryContext = $this->validateNormalOrderDelivery(
                        $cartInfo,
                        $formDeliveryType,
                        $uid,
                        $addressId,
                        $storeId,
                        is_array($formData['service'] ?? null) ? $formData['service'] : []
                    );
                    $validatedAddressInfo = $deliveryContext['address_info'];
                    $storeInfo = $deliveryContext['store_info'];
                    $normalDeliveryValidated = true;
                }

                /** @var OrderComputedServices $computedServices */
                $computedServices = app()->make(OrderComputedServices::class);
                $priceGroup = $computedServices->getOrderPriceGroup(
                    (float)sys_config('store_free_postage') ?: 0,
                    $cartInfo,
                    $validatedAddressInfo,
                    $userInfo,
                    $formShippingType,
                    $isGift
                );
                $cartInfo = $priceGroup['cartInfo'] ?? $cartInfo;
                $priceData = [
                    'totalPrice'         => $priceGroup['totalPrice'] ?? 0,
                    'payPrice'           => bcadd((string)($priceGroup['totalPrice'] ?? 0), (string)($priceGroup['storePostage'] ?? 0), 2),
                    'payPostage'         => $priceGroup['storePostage'] ?? 0,
                    'isStoreFreePostage' => $priceGroup['isStoreFreePostage'] ?? false,
                    'storeFreePostage'   => $priceGroup['storeFreePostage'] ?? 0,
                    'costPrice'          => $priceGroup['costPrice'] ?? 0,
                    'giftPrice'          => $priceGroup['giftPrice'] ?? 0,
                    'levelPrice'         => $priceGroup['levelPrice'] ?? 0,
                    'memberPrice'        => $priceGroup['memberPrice'] ?? 0,
                ];
            } elseif ($productType === 'normal' && !$isActivityOrder) {
                // 非普通重建路径（兼容旧活动/特殊缓存）仍必须校验配送信息；送礼订单除外
                if ($isGift == 1) {
                    $validatedAddressInfo = [];
                    $normalDeliveryValidated = true;
                } else {
                    $deliveryContext = $this->validateNormalOrderDelivery(
                        $cartInfo,
                        $formDeliveryType,
                        $uid,
                        $addressId,
                        $storeId,
                        is_array($formData['service'] ?? null) ? $formData['service'] : []
                    );
                    $validatedAddressInfo = $deliveryContext['address_info'];
                    $storeInfo = $deliveryContext['store_info'];
                    $normalDeliveryValidated = true;
                }
            }

            // 记录实时原始总价（优惠券抵扣不影响 total_price 记录）
            $originalTotalPrice = $priceData['totalPrice'] ?? 0;

            // 用户未选券时自动匹配整单最优优惠券（可领取的先领取，失败降级为无券；活动订单与关闭券能力的商品类型不参与）
            if (!$couponId && !$isActivityOrder) {
                $couponPolicyEnabled = true;
                $autoHandler = $registry->get($productType);
                if (method_exists($autoHandler, 'getPricingPolicy')) {
                    $couponPolicyEnabled = ($autoHandler->getPricingPolicy()['coupon'] ?? true) === true;
                }
                if ($couponPolicyEnabled) {
                    $autoCoupon = app()->make(OrderComputedServices::class)->autoMatchCoupon($uid, $cartInfo, true);
                    if (!empty($autoCoupon['coupon_user_id'])) {
                        $couponId = (int)$autoCoupon['coupon_user_id'];
                    }
                }
            }

            // 1.5 手动校验并计算优惠券金额
            // 活动订单默认不支持优惠券
            if ($couponId && !$isActivityOrder) {
                /** @var \app\services\activity\coupon\StoreCouponUserServices $couponServices */
                $couponServices = app()->make(\app\services\activity\coupon\StoreCouponUserServices::class);
                // 预加载 issue 关联以获取 applicable_type
                $couponInfo = $couponServices->get($couponId, ['*'], ['issue']);
                if (!$couponInfo || $couponInfo['uid'] != $uid || $couponInfo->getData('is_fail') != 0 || $couponInfo->getData('status') != 0 || $couponInfo['start_time'] > time() || $couponInfo['end_time'] < time()) {
                    return app('json')->fail('选择的优惠劵无效');
                }
                $validPrice = '0';
                $validCount = 0;
                // 使用 getData 获取原始 type 值（避免 getTypeAttr 访问器将整数转为中文）
                $type = $couponInfo['applicable_type'] ?? $couponInfo->getData('type') ?? 0;
                switch ($type) {
                    case 0:
                    case 3:
                        foreach ($cartInfo as $cart) {
                            $validPrice = bcadd($validPrice, bcmul((string)$cart['truePrice'], (string)$cart['cart_num'], 2), 2);
                            $validCount++;
                        }
                        break;
                    case 1:
                        $storeCategoryServices = app()->make(\app\dao\product\product\StoreCategoryDao::class);
                        $couponCategory = explode(',', (string)$couponInfo['category_id']);
                        $categoryIds = $storeCategoryServices->getAllById($couponCategory);
                        if ($categoryIds) {
                            $cateIds = array_column($categoryIds, 'id');
                            foreach ($cartInfo as $cart) {
                                if (isset($cart['productInfo']['cate_id']) && array_intersect(explode(',', $cart['productInfo']['cate_id']), $cateIds)) {
                                    $validPrice = bcadd($validPrice, bcmul((string)$cart['truePrice'], (string)$cart['cart_num'], 2), 2);
                                    $validCount++;
                                }
                            }
                        }
                        break;
                    case 2:
                        foreach ($cartInfo as $cart) {
                            if (isset($cart['product_id']) && in_array($cart['product_id'], explode(',', $couponInfo['product_id']))) {
                                $validPrice = bcadd($validPrice, bcmul((string)$cart['truePrice'], (string)$cart['cart_num'], 2), 2);
                                $validCount++;
                            }
                        }
                        break;
                }
                if ($validCount && $couponInfo['use_min_price'] <= $validPrice) {
                    $couponPriceVal = min($couponInfo['coupon_price'], $validPrice);
                    // 将优惠券金额从总价中扣除，使 computedOrder 在优惠后价格上计算积分
                    $priceData['totalPrice'] = bcsub((string)$priceData['totalPrice'], (string)$couponPriceVal, 2);
                } else {
                    return app('json')->fail('不满足优惠劵的使用条件');
                }
            }

            // 3.1 生成订单号（统一走订单中台）
            /** @var OrderCenterServices $createServices */
            $createServices = app()->make(OrderCenterServices::class);
            $orderNo = $createServices->getNewOrderId('cp');

            // 3.2 获取收货地址信息
            $realName = $formData['real_name'] ?? '';
            $userPhone = $formData['phone'] ?? '';
            $userAddress = '';
            if ($addressId) {
                /** @var \app\services\user\UserAddressServices $addressServices */
                $addressServices = app()->make(\app\services\user\UserAddressServices::class);
                $addressInfo = $validatedAddressInfo ?: $addressServices->getOne(['uid' => $uid, 'id' => $addressId, 'is_del' => 0]);
                if ($addressInfo) {
                    if (is_object($addressInfo)) {
                        $addressInfo = $addressInfo->toArray();
                    }
                    $realName = $addressInfo['real_name'] ?? $realName;
                    $userPhone = $addressInfo['phone'] ?? $userPhone;
                    $userAddress = implode(' ', array_filter([
                        $addressInfo['province'] ?? '',
                        $addressInfo['city'] ?? '',
                        $addressInfo['district'] ?? '',
                        $addressInfo['detail'] ?? '',
                    ]));
                }
            }

            $payType = request()->param('pay_type', '');

            // 活动订单强制使用指定支付方式
            $activityAllowedPayTypes = [];
            $isFreePay = false;
            if ($isActivityOrder) {
                $activityAllowedPayTypes = $tradeModeHandler->getAllowedPayTypes($activityId);
                $isFreePay = $tradeModeHandler->isFreePay($activityId);
            }
            if ($isActivityOrder && !empty($activityAllowedPayTypes) && !in_array($payType, $activityAllowedPayTypes, true)) {
                $payType = $activityAllowedPayTypes[0];
            }
            // 免费活动订单（如积分兑换）统一使用 free 支付方式
            if ($isActivityOrder && $isFreePay) {
                $payType = 'free';
            }

            // 从 Handler 获取订单源类型
            $handler = $registry->get($productType);
            $stockHandler = $handler;
            $sourceType = $handler->getOrderSourceType();

            // 转换自定义表单数据：将提交的 {"custom_field_0": "值"} 转为带原始标题的数组格式
            $submittedCustomForm = $formData['custom_form'] ?? [];
            if (!empty($submittedCustomForm) && is_array($submittedCustomForm) && !isset($submittedCustomForm[0])) {
                // 从产品定义获取字段标题映射
                $detailExtend = $handler->getDetailExtend($productId);
                $productCustomForm = $detailExtend['custom_form'] ?? [];
                $fieldTitleMap = [];
                // 循环变量不得使用 $key：外层 $key 是订单确认缓存凭证，覆盖后 consumeOrderCache 会因类型错误崩溃
                foreach ($productCustomForm as $fieldKey => $fieldItem) {
                    $fieldTitleMap['custom_field_' . $fieldKey] = $fieldItem['title'] ?? ('自定义字段' . ($fieldKey + 1));
                }
                $structured = [];
                foreach ($submittedCustomForm as $field => $value) {
                    $structured[] = [
                        'label' => $field,
                        'title' => $fieldTitleMap[$field] ?? $field,
                        'value' => $value,
                    ];
                }
                $formData['custom_form'] = $structured;
            }

            // 4. 计算最终价格（优惠券+积分抵扣+运费）
            // 活动订单默认不使用积分抵扣
            $useIntegral = !$isActivityOrder && (bool) request()->param('use_integral', false);
            // 构建与 computed() 相同的 cartGroup 结构
            $tmpCartGroup = [
                'cartInfo'  => $cartInfo,
                'priceData' => $priceData,
                'integralRatio' => sys_config('integral_ratio', 0),
            ];
            $tmpCartGroup['priceGroup'] = $tmpCartGroup['priceData'];
            $tmpCartGroup['other'] = [
                'integralRatio' => $tmpCartGroup['integralRatio'],
                'pricing_policy' => method_exists($handler, 'getPricingPolicy') ? $handler->getPricingPolicy() : [],
            ];
            // totalPrice 已在第 1.5 步按优惠券扣减，传给 computedOrder（couponId=0 避免重复扣）
            /** @var OrderComputedServices $computedServices */
            $computedServices = app()->make(OrderComputedServices::class);
            $computedResult = $computedServices->computedOrder(
                $uid, $userInfo, $tmpCartGroup, $addressId, $payType,
                $useIntegral, 0, false, $formShippingType, $isGift
            );
            $finalPayPrice = $computedResult['pay_price'] ?? 0;
            $finalDeductionPrice = $computedResult['deduction_price'] ?? 0;
            $finalUsedIntegral = $computedResult['usedIntegral'] ?? 0;

            // 自提模式强制运费为 0；上门服务模式运费替换为商品上门费用（同上 computed 方法的处理逻辑）
            $finalPayPostage = $computedResult['pay_postage'] ?? 0;
            if ($formDeliveryType === 'pickup') {
                if ($finalPayPostage > 0) {
                    $finalPayPrice = bcsub((string)$finalPayPrice, (string)$finalPayPostage, 2);
                }
                $finalPayPostage = 0;
            } elseif ($formDeliveryType === 'door') {
                // 上门服务：运费 = 商品上门费用（实时从数据库读取，避免缓存快照过期导致金额不一致）
                $doorPostage = 0.00;
                $doorProductId = (int)($cartInfo[0]['product_id'] ?? $productId ?? 0);
                if ($doorProductId) {
                    try {
                        /** @var \app\services\product\CenterProductServices $centerServices */
                        $centerServices = app()->make(\app\services\product\CenterProductServices::class);
                        $doorInfo = $centerServices->getInfo($doorProductId);
                        $doorProductInfo = $doorInfo['productInfo'] ?? $doorInfo;
                        $doorPostage = (float)($doorProductInfo['door_service_postage'] ?? 0);
                    } catch (\Throwable $e) {
                        // 读取失败时按免上门费处理，不影响主流程
                        $doorPostage = 0.00;
                    }
                }
                if ($finalPayPostage > 0) {
                    $finalPayPrice = bcsub((string)$finalPayPrice, (string)$finalPayPostage, 2);
                }
                $finalPayPrice = bcadd((string)$finalPayPrice, (string)$doorPostage, 2);
                $finalPayPostage = $doorPostage;
            }

            // 5. 先写产品类型扩展表，拿到扩展表记录 id 作为中台表的 source_id
            $cartInfo[0]['product_id'] = $cartInfo[0]['product_id'] ?? $productId;
            $cartInfo[0]['uid'] = $uid;

            // 5.0 透传 block_data（门票/酒店等 display_sections 选中数据）到 cartInfo[0]
            // 由前端 order_data.block_data 传入，Handler.createOrderExtend 从中解析 visit_date/session_id/sku_id
            $blockData = $formData['block_data'] ?? [];
            if (!empty($blockData)) {
                $cartInfo[0]['block_data'] = $blockData;
            }

            // 5.1 游客信息（门票专属）：校验数量并 merge 到 cartInfo[0]，由 Handler.createOrderExtend 存储
            // 校验规则：商品开启 need_id_card 时，visitors 数量必须等于购买总数量
            // 购物车合单模式：累加所有商品 cart_num（每张票对应一位游客）
            $visitors = $formData['visitors'] ?? [];
            if (method_exists($handler, 'getDetailExtend')) {
                $visitorFormConfig = $handler->getDetailExtend($productId)['visitor_form_config'] ?? [];
                if (!empty($visitorFormConfig)) {
                    $visitors = is_array($visitors) ? $visitors : [];
                    // 累加所有购物车项的 cart_num 作为总票数
                    $cartNum = 0;
                    foreach ($cartInfo as $cItem) {
                        $cartNum += (int)($cItem['cart_num'] ?? 0);
                    }
                    $cartNum = $cartNum > 0 ? $cartNum : 1;
                    if (count($visitors) !== $cartNum) {
                        return app('json')->fail('请完整填写游客信息');
                    }
                    // 校验每位游客必填字段（姓名 + 身份证）
                    foreach ($visitors as $idx => $visitor) {
                        $name = (string)($visitor['name'] ?? '');
                        $idCard = (string)($visitor['id_card'] ?? '');
                        if ($name === '' || $idCard === '') {
                            return app('json')->fail('请完整填写第' . ($idx + 1) . '位游客信息');
                        }
                    }
                    $cartInfo[0]['visitors'] = $visitors;
                    // 合单模式下 quantity 存总票数（与 visitors 数量一致）
                    $cartInfo[0]['cart_num'] = $cartNum;
                }
            }

            // 自提订单：校验门店并获取门店信息
            $verifyCode = '';
            if ($formDeliveryType === 'pickup') {
                if (!$normalDeliveryValidated) {
                    $storeId = (int)($formData['store_id'] ?? 0);
                    if ($storeId > 0) {
                        /** @var SystemStoreServices $storeServices */
                        $storeServices = app()->make(SystemStoreServices::class);
                        $storeInfo = $storeServices->getOne(['id' => $storeId, 'is_show' => 1, 'is_del' => 0]);
                        if (is_object($storeInfo)) {
                            $storeInfo = $storeInfo->toArray();
                        }
                        if (!$storeInfo || empty($storeInfo['id'])) {
                            return app('json')->fail('自提门店不存在或已停用');
                        }
                    }
                }
                // 生成核销码（8位随机数字）
                $verifyCode = str_pad((string)random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
            }

            // 上门服务订单：组装预约信息（联系人/电话/地址/预约时间），缺失时回退地址信息
            $doorServiceInfo = [];
            if ($formDeliveryType === 'door') {
                $serviceData = $formData['service'] ?? [];
                $doorServiceInfo = [
                    'contact_name'    => $serviceData['contact_name'] ?? $realName,
                    'contact_phone'   => $serviceData['contact_phone'] ?? $userPhone,
                    'address'         => $serviceData['address'] ?? $userAddress,
                    'appointment_time'=> $serviceData['appointment_time'] ?? '',
                ];
                if (empty($doorServiceInfo['contact_phone'])) {
                    return app('json')->fail('请填写上门服务联系电话');
                }
            }
            $deliveryType = $formDeliveryType === 'pickup' ? 'pickup' : ($formDeliveryType === 'door' ? 'door' : 'express');

            // 库存、产品扩展、中台订单须原子提交；产品钩子可能预留稀缺资源。
            \think\facade\Db::startTrans();
            $orderTransactionStarted = true;
            // 普通实物订单在创建时锁定库存。活动订单由活动 Handler 自己管理库存，不能重复扣减。
            if (!$isActivityOrder) {
                foreach ($cartInfo as $cartItem) {
                    $itemProductId = (int)($cartItem['product_id'] ?? $cartItem['productInfo']['id'] ?? 0);
                    $itemNum = (int)($cartItem['cart_num'] ?? 0);
                    $itemSkuUnique = (string)($cartItem['product_attr_unique']
                        ?? $cartItem['productInfo']['attrInfo']['unique']
                        ?? '');
                    if ($itemProductId <= 0 || $itemNum <= 0 || !$handler->decStock($itemProductId, $itemSkuUnique, $itemNum)) {
                        throw new \RuntimeException('商品库存不足或规格已失效');
                    }
                    $stockDecrements[] = [
                        'product_id' => $itemProductId,
                        'sku_unique' => $itemSkuUnique,
                        'num'        => $itemNum,
                    ];
                }
            }

            // 积分抵扣必须在订单事务内落流水并扣减用户积分，失败时回滚库存和订单。
            if ((int)$finalUsedIntegral > 0) {
                /** @var UserBillServices $userBillServices */
                $userBillServices = app()->make(UserBillServices::class);
                $userBillServices->billIntegralIncome('deduction', $uid, [
                    'number'         => (int)$finalUsedIntegral,
                    'deductionPrice' => $finalDeductionPrice,
                ], $orderNo);
            }

            // 优惠券在订单创建事务内消费，订单后续写入失败时随事务一起回滚。
            if ($couponId > 0 && !$isActivityOrder) {
                /** @var StoreCouponUserServices $couponServices */
                $couponServices = app()->make(StoreCouponUserServices::class);
                if (!$couponServices->useCoupon($couponId)) {
                    throw new \RuntimeException('优惠券已被使用或已失效，请重新选择');
                }
            }

            $extendId = 0;
            if (method_exists($handler, 'createOrderExtend')) {
                $extendId = $handler->createOrderExtend($orderNo, $cartInfo[0] ?? [], [
                    'cart_items'    => $cartInfo,
                    'store_id'      => $storeId,
                    'shipping_type' => $formShippingType,
                    'verify_code'   => $verifyCode,
                    // 上门服务预约信息，扩展订单表可据此展示/核销
                    'service'       => $doorServiceInfo,
                ]);
            }

            // 构建发票信息
            $invoiceInfo = [];
            if (!empty($formData['invoice_id'])) {
                /** @var \app\services\user\UserInvoiceServices $invoiceServices */
                $invoiceServices = app()->make(\app\services\user\UserInvoiceServices::class);
                // 发票必须属于当前用户且处于有效状态，不能只按 ID 查询。
                $invoice = $invoiceServices->checkInvoice((int)$formData['invoice_id'], $uid);
                $invoiceInfo = is_object($invoice) ? $invoice->toArray() : (array)$invoice;
            }

            // 6. 写中台订单表 eb_order_center
            /** @var OrderCenterUserServices $centerServices */
            $centerServices = app()->make(OrderCenterUserServices::class);

            // 组装活动相关扩展信息
            $activityExtraInfo = [];
            if ($isActivityOrder) {
                $orderExtraInfo = $tradeModeHandler->getOrderExtraInfo($activityId);
                $activityExtraInfo = array_merge(
                    $orderExtraInfo,
                    [
                        'activity_type' => $activityType,
                        'activity_id'   => $activityId,
                    ]
                );
            }

            if (!$centerServices->createRecord([
                'order_no'       => $orderNo,
                'source_type'    => $sourceType,
                'source_id'      => $extendId,
                'uid'            => $uid,
                'order_category' => 'goods',
                'product_type'   => $productType,
                'store_id'       => $storeId,
                'shipping_type'  => $formShippingType,
                'verify_code'    => $verifyCode,
                'activity_type'  => $activityType,
                'activity_id'    => $activityId,
                'status_code'    => 'pending_payment',
                'pay_status'     => 0,
                'refund_status'  => 0,
                'total_price'    => $originalTotalPrice,
                'pay_price'      => $finalPayPrice,
                'pay_postage'    => $finalPayPostage,
                'pay_type'         => $payType,
                'coupon_price'     => (string) ($couponPriceVal ?? $priceData['couponPrice'] ?? '0.00'),
                'deduction_price'  => (string) $finalDeductionPrice,
                'use_integral_num' => $finalUsedIntegral,
                'member_discount'  => (string)($priceData['levelPrice'] ?? $computedResult['level_price'] ?? '0.00'),
                'vip_discount'     => (string)($priceData['memberPrice'] ?? $computedResult['member_price'] ?? '0.00'),
                'gift_price'       => (string)($priceData['giftPrice'] ?? $computedResult['gift_price'] ?? '0.00'),
                'title'            => $cartInfo[0]['productInfo']['store_name'] ?? '',
                'image'            => $cartInfo[0]['productInfo']['image'] ?? '',
                // 多商品合单时聚合所有商品数量
                'total_num'        => array_sum(array_column($cartInfo, 'cart_num')) ?: 1,
                'mark'             => $formData['mark'] ?? '',
                'custom_form'      => $formData['custom_form'] ?? [],
                'invoice'          => $invoiceInfo,
                'extra_info'       => [
                    'business_status' => [
                        'code' => 'pending_payment',
                        'text' => '待支付',
                        'detail' => '',
                        'extend' => new \stdClass(),
                    ],
                    'delivery_type'         => $deliveryType,
                    'delivery'              => [
                        'delivery_type' => $deliveryType,
                    ],
                    // 金额字段已提升为实体列，extra_info 中不再双写
                    'sku_info'              => $this->buildSkuInfoForExtra($cartInfo[0] ?? []),
                    // 商品明细快照，供订单详情/退款页面使用
                    'items'                 => $this->buildOrderItemsForExtra($cartInfo),
                    // 商品ID，供订单详情页跳转商品详情使用
                    'product_id'            => $cartInfo[0]['product_id'] ?? $cartInfo[0]['productInfo']['id'] ?? 0,
                    // 上门服务订单：保存预约信息，供详情页显示；快递订单保存收货联系信息
                    'contact' => $formDeliveryType === 'door' ? [] : [
                        'name'    => $realName,
                        'phone'   => $userPhone,
                        'address' => [
                            'province'    => '',
                            'city'        => '',
                            'district'    => '',
                            'detail'      => $userAddress,
                            'postal_code' => '',
                        ],
                    ],
                    // 上门服务订单：预约信息（联系人/电话/地址/预约时间）
                    'service' => $formDeliveryType === 'door' ? $doorServiceInfo : [],
                    // 自提订单：保存门店信息，供详情页显示
                    'store_info' => $formDeliveryType === 'pickup' && !empty($storeInfo) ? [
                        'store_id'   => (int)$storeInfo['id'],
                        'store_name' => $storeInfo['name'] ?? '',
                        'store_phone' => $storeInfo['phone'] ?? '',
                        'address'     => $storeInfo['address'] ?? '',
                        'detailed_address' => $storeInfo['detailed_address'] ?? $storeInfo['address'] ?? '',
                    ] : [],
                    // 保存用户优惠券ID，取消或退款时用于恢复优惠券。
                    'coupon_id' => (!$isActivityOrder && $couponId > 0) ? $couponId : 0,
                    'activity_info' => $activityExtraInfo,
                ] + ($isGift == 1 ? [
                    // 送礼订单：留言与领取记录挂在 extra_info，领取时写入 gift.gift_uid
                    'gift_mark' => $giftMark,
                    'gift' => [
                        'gift_uid' => 0,
                        'gift_key' => '',
                    ],
                ] : []),
                'detail_route'   => $handler->getOrderDetailRoute($orderNo),
            ])) {
                throw new \RuntimeException('订单创建失败');
            }

            // 中台主键：开票申请与活动回调共用
            $centerOrderId = 0;
            try {
                $centerOrderId = (int)app()->make(OrderCenterDao::class)->value(['order_no' => $orderNo], 'id');
            } catch (\Throwable $e) {
                // 查询失败不影响主流程（活动回调另有兜底）
            }

            // 下单勾选发票：同步创建开票申请记录，供后台发票列表与支付回写 is_pay 使用
            if (!empty($invoiceInfo) && $centerOrderId > 0) {
                try {
                    app()->make(OrderInvoiceServices::class)->createFromOrder((int)$uid, $centerOrderId, $invoiceInfo, 0);
                } catch (\Throwable $e) {
                    \think\facade\Log::warning('下单创建开票申请失败: ' . $orderNo . ': ' . $e->getMessage());
                }
            }

            // 6.1 活动订单创建后回调
            if ($isActivityOrder) {
                $tradeModeHandler->afterOrderCreate($orderNo, $activityId, [
                    'order_no'    => $orderNo,
                    'id'          => $centerOrderId,
                    'uid'         => $uid,
                    'product_type'=> $productType,
                    'pay_price'   => $finalPayPrice,
                    'total_num'   => array_sum(array_column($cartInfo, 'cart_num')) ?: 1,
                    'cartInfo'    => $cartInfo,
                ]);
            }

            \think\facade\Db::commit();
            $orderTransactionStarted = false;
            $stockDecrements = [];

            // 订单事务成功后立即消费确认缓存，不能只依赖后置事件清理。
            // 消费失败时保留互斥锁直到超时，避免缓存残留导致重复创建订单。
            if ($key && $orderServices && !$orderServices->consumeOrderCache($uid, $key)) {
                $orderCacheKeepLock = true;
                Log::warning('订单确认缓存消费失败，已保留创建锁: ' . $key);
            }

            // 通用入口也必须调度超时取消，否则预留库存会被未支付订单永久占用。
            try {
                event('OrderCreateAfter', [
                    'order_no' => $orderNo,
                    'order_id' => (int)app()->make(OrderCenterDao::class)->value(['order_no' => $orderNo], 'id'),
                    'uid' => $uid,
                    'key' => $key,
                    'cart_info' => $cartInfo[0] ?? [],
                    'cart_items' => $cartInfo,
                    'price_data' => $computedResult,
                    'product_type' => $productType,
                ]);
            } catch (\Throwable $e) {
                \think\facade\Log::error('订单已创建，但后置任务调度失败: ' . $orderNo . ': ' . $e->getMessage());
            }

            // 6.2 购物车模式：下单成功后清除已下单的购物车记录（缓存/立即购买模式无需清除）
            if ($isCartMode && !$cartNewMode && $cartIdsForRemove) {
                try {
                    $cartIds = array_filter(array_map('intval', explode(',', $cartIdsForRemove)));
                    if ($cartIds) {
                        /** @var StoreCartServices $cartServices */
                        $cartServices = app()->make(StoreCartServices::class);
                        $cartServices->removeUserCart($uid, $cartIds);
                    }
                } catch (\Throwable $e) {
                    // 购物车清除失败不影响下单结果
                    \think\facade\Log::warning('下单成功后清除购物车失败: ' . $e->getMessage());
                }
            }

            // 7. 写入订单状态日志，供 detail 接口的时间线读取
            try {
                /** @var \app\dao\order\OrderLogDao $logDao */
                $logDao = app()->make(\app\dao\order\OrderLogDao::class);
                $logDao->save([
                    'order_no'      => $orderNo,
                    'uid'           => $uid,
                    'operator_id'   => $uid,
                    'operator_type' => 'user',
                    'from_status'   => '',
                    'to_status'     => 'pending_payment',
                    'content'       => '提交订单',
                    'add_time'      => time(),
                ]);
            } catch (\Throwable $e) {
                // 日志写入失败不影响主流程
                \think\facade\Log::warning('订单状态日志写入失败: ' . $e->getMessage());
            }

            return app('json')->success([
                'order_no'  => $orderNo,
                'pay_price' => $finalPayPrice,
                'order_id'  => $orderNo,
            ]);
        } catch (\Throwable $e) {
            if ($orderTransactionStarted) {
                \think\facade\Db::rollback();
                $stockDecrements = [];
            }
            // 仅补偿事务外的库存扣减，已回滚的事务不能重复加库存。
            if ($stockHandler && $stockDecrements) {
                foreach (array_reverse($stockDecrements) as $stock) {
                    try {
                        $stockHandler->incStock($stock['product_id'], $stock['sku_unique'], $stock['num']);
                    } catch (\Throwable $rollbackError) {
                        \think\facade\Log::error('CenterOrderController::create stock rollback failed: ' . $rollbackError->getMessage());
                    }
                }
            }
            \think\facade\Log::error('CenterOrderController::create error: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            return app('json')->fail($e->getMessage() . ' [' . $e->getFile() . ':' . $e->getLine() . ']');
        } finally {
            if ($orderCacheLockAcquired && !$orderCacheKeepLock && $orderServices) {
                $orderServices->releaseOrderCacheLock($orderCacheLockKey);
            }
        }
    }

    /**
     * 规范化订单配送方式。
     *
     * delivery 是用户端确认页使用的快递标识，express 作为历史兼容别名；
     * 其他值必须拒绝，不能默认降级为快递绕过商品配送能力校验。
     *
     * @param string $deliveryType 客户端配送方式
     * @return string 规范化后的 delivery/pickup/door
     * @throws \RuntimeException 配送方式不合法时抛出
     */
    private function normalizeOrderDeliveryType(string $deliveryType): string
    {
        $deliveryType = trim($deliveryType);
        if ($deliveryType === '' || $deliveryType === 'express') {
            return 'delivery';
        }
        if (!in_array($deliveryType, ['delivery', 'pickup', 'door'], true)) {
            throw new \RuntimeException('配送方式无效');
        }
        return $deliveryType;
    }

    /**
     * 强制校验普通商品订单的配送方式及其关联数据。
     *
     * @param array $cartInfo 当前商品购物车快照
     * @param string $deliveryType delivery/pickup/door
     * @param int $uid 用户ID
     * @param int $addressId 收货地址ID
     * @param int $storeId 自提门店ID
     * @param array $serviceData 上门服务信息
     * @param bool $validateService 是否校验上门服务表单（金额计算接口不包含该表单）
     * @return array{address_info: array, store_info: array}
     * @throws \RuntimeException 配送方式、地址、门店或服务信息不合法时抛出
     */
    private function validateNormalOrderDelivery(
        array $cartInfo,
        string $deliveryType,
        int $uid,
        int $addressId,
        int $storeId,
        array $serviceData = [],
        bool $validateService = true
    ): array {
        $deliveryMethod = [
            'delivery' => 'express',
            'pickup'   => 'pickup',
            'door'     => 'door_service',
        ][$deliveryType] ?? '';
        if ($deliveryMethod === '') {
            throw new \RuntimeException('配送方式无效');
        }

        if (empty($cartInfo)) {
            throw new \RuntimeException('订单商品信息已失效，请返回确认页重新提交');
        }
        if ($deliveryType !== 'pickup' && $storeId > 0) {
            throw new \RuntimeException('当前配送方式不能选择自提门店');
        }

        // 合单订单必须要求每一件商品都支持用户选择的配送方式。
        foreach ($cartInfo as $cartItem) {
            $productInfo = $cartItem['productInfo'] ?? [];
            $methods = $productInfo['delivery_methods'] ?? [];
            if (is_string($methods)) {
                $methods = json_decode($methods, true) ?: [];
            }
            $methods = is_array($methods) ? array_values(array_unique(array_filter($methods))) : [];
            if (!$methods) {
                if (!empty($productInfo['express_shipping'])) {
                    $methods[] = 'express';
                }
                if (!empty($productInfo['store_pickup'])) {
                    $methods[] = 'pickup';
                }
                if (!empty($productInfo['door_service'])) {
                    $methods[] = 'door_service';
                }
            }
            if (!in_array($deliveryMethod, $methods, true)) {
                throw new \RuntimeException('商品不支持当前配送方式');
            }
        }

        $addressInfo = [];
        $storeInfo = [];
        if ($deliveryType === 'delivery') {
            if ($addressId <= 0) {
                throw new \RuntimeException('请选择收货地址');
            }
            /** @var UserAddressServices $addressServices */
            $addressServices = app()->make(UserAddressServices::class);
            $addressInfo = $addressServices->getOne([
                'uid'    => $uid,
                'id'     => $addressId,
                'is_del' => 0,
            ]);
            if (!$addressInfo) {
                throw new \RuntimeException('收货地址不存在或已失效');
            }
            if (is_object($addressInfo)) {
                $addressInfo = $addressInfo->toArray();
            }
            $addressText = implode('', array_filter([
                $addressInfo['province'] ?? '',
                $addressInfo['city'] ?? '',
                $addressInfo['district'] ?? '',
                $addressInfo['detail'] ?? '',
            ]));
            if (empty($addressInfo['real_name']) || empty($addressInfo['phone']) || $addressText === '') {
                throw new \RuntimeException('收货地址信息不完整');
            }
        } elseif ($deliveryType === 'pickup') {
            if (!(int)sys_config('store_self_mention', 0)) {
                throw new \RuntimeException('当前未开启到店自提');
            }
            if ($storeId <= 0) {
                throw new \RuntimeException('请选择自提门店');
            }
            /** @var SystemStoreServices $storeServices */
            $storeServices = app()->make(SystemStoreServices::class);
            $storeInfo = $storeServices->getOne([
                'id'      => $storeId,
                'is_show' => 1,
                'is_del'  => 0,
            ]);
            if (!$storeInfo) {
                throw new \RuntimeException('自提门店不存在或已停用');
            }
            if (is_object($storeInfo)) {
                $storeInfo = $storeInfo->toArray();
            }
        } elseif ($validateService) {
            if (trim((string)($serviceData['contact_name'] ?? '')) === '') {
                throw new \RuntimeException('请填写上门服务联系人');
            }
            if (trim((string)($serviceData['contact_phone'] ?? '')) === '') {
                throw new \RuntimeException('请填写上门服务联系电话');
            }
            if (trim((string)($serviceData['address'] ?? '')) === '') {
                throw new \RuntimeException('请填写上门服务地址');
            }
            if (trim((string)($serviceData['appointment_time'] ?? '')) === '') {
                throw new \RuntimeException('请选择上门服务时间');
            }
        }

        return [
            'address_info' => is_array($addressInfo) ? $addressInfo : [],
            'store_info'   => is_array($storeInfo) ? $storeInfo : [],
        ];
    }

    /**
     * 构建中心商品下单的基础购物车数据和价格数据
     * 提取为私有方法，供 confirm 和 create 共用
     * @param int $uid 用户ID
     * @param int $productId 中台商品ID
     * @param string $skuUnique SKU唯一码
     * @param string $productType 产品类型
     * @param int $num 购买数量
     * @param string $activityType 活动类型
     * @param int $activityId 活动ID
     * @return array [cartInfo, priceData, productInfo, skuInfo, registry]
     */
    private function buildOrderData(int $uid, int $productId, string $skuUnique, string $productType, int $num, string $activityType = 'normal', int $activityId = 0): array
    {
        /** @var CenterProductServices $centerProductServices */
        $centerProductServices = app()->make(CenterProductServices::class);
        $productData = $centerProductServices->getInfo($productId);
        if (!$productData) {
            throw new \RuntimeException('商品不存在');
        }
        // CenterProductServices::getInfo 返回包含 tempList/cateList/productInfo 的数组，真实商品信息在 productInfo 键中
        $productInfo = $productData['productInfo'] ?? $productData;
        if (!$productInfo) {
            throw new \RuntimeException('商品不存在');
        }

        $num = max(1, $num);
        $minQty = max(1, (int)($productInfo['min_qty'] ?? 1));
        if ($num < $minQty) {
            throw new \RuntimeException('购买数量不能少于起购数量 ' . $minQty);
        }
        if (!empty($productInfo['is_limit']) && (int)($productInfo['limit_type'] ?? 0) === 1
            && $num > (int)($productInfo['limit_num'] ?? 0)) {
            throw new \RuntimeException('购买数量不能超过限购数量');
        }

        // 优先使用产品中心记录的实际 product_type（normal/virtual/cardkey），
        // 覆盖前端传入的 product_type 参数，确保不同类型的商品 SKU 能通过正确的 Handler 读取
        $actualProductType = (string)($productInfo['product_type'] ?? '');
        if ($actualProductType !== '') {
            $productType = $actualProductType;
        }

        /** @var ProductTypeRegistry $registry */
        $registry = app()->make(ProductTypeRegistry::class);
        $handler = $registry->get($productType);
        $shippingInfo = $handler->getShippingInfo($productId);
        $skuInfo = $handler->getSkuInfo($productId, $skuUnique);
        if (!$skuInfo) {
            throw new \RuntimeException('商品规格不可用');
        }
        if (!$activityId && (($handler->getCapabilities()['order']['virtual'] ?? false) !== true) && (int)($skuInfo['stock'] ?? 0) < $num) {
            throw new \RuntimeException('商品库存不足');
        }

        $truePrice = $skuInfo['price'] ?? $productInfo['price'] ?? 0;
        $defaultSkuInfo = array_merge([
            'weight' => 0,
            'volume' => 0,
        ], is_array($skuInfo) ? $skuInfo : []);
        // SKU 级别的重量/体积为 0 时，使用商品级别的值作为 fallback（供运费模板计算使用）
        // 优先使用 getShippingInfo() 从扩展表读取的值，其次使用 productInfo
        if (empty($defaultSkuInfo['weight'])) {
            $defaultSkuInfo['weight'] = $shippingInfo['weight'] ?? $productInfo['weight'] ?? 0;
        }
        if (empty($defaultSkuInfo['volume'])) {
            $defaultSkuInfo['volume'] = $shippingInfo['volume'] ?? $productInfo['volume'] ?? 0;
        }
        // 解析 suk JSON，构建可读的规格标签
        if (!empty($defaultSkuInfo['suk'])) {
            $sukParsed = json_decode($defaultSkuInfo['suk'], true);
            if (is_array($sukParsed)) {
                $defaultSkuInfo['suk'] = implode(' / ', array_values($sukParsed));
            }
        }

        $isVirtual = (($handler->getCapabilities()['order']['virtual'] ?? false) === true);

        // 从商品信息中读取是否支持快递配送（已在 getInfo 中解析 logistics）
        $productExpressShipping = $productInfo['express_shipping'] ?? false;

        // 处理图片URL
        $image = $productInfo['image'] ?? '';
        if ($image && !str_starts_with($image, 'http') && !str_starts_with($image, '//')) {
            $image = sys_config('site_url') . $image;
        }

        // 分类ID可能是数组（CenterProductServices::getInfo 已解析），转字符串避免 Array to string conversion
        $cateId = $productInfo['cate_id'] ?? '';
        if (is_array($cateId)) {
            $cateId = implode(',', $cateId);
        }

        $isActivityOrder = $activityType !== 'normal' && $activityId > 0;
        $activityPriceInfo = [];
        $activityIntegral = 0;
        if ($isActivityOrder) {
            /** @var TradeModeRegistry $tradeModeRegistry */
            $tradeModeRegistry = app()->make(TradeModeRegistry::class);
            $tradeModeHandler = $tradeModeRegistry->get($activityType);
            $activityPriceInfo = $tradeModeHandler->getActivityPrice($productId, $skuUnique, $activityId);
            // 活动价覆盖真实单价
            if (!empty($activityPriceInfo['unit_price'])) {
                $truePrice = $activityPriceInfo['unit_price'];
            }
            // 积分兑换场景记录积分数量
            $activityIntegral = (int) ($activityPriceInfo['currency_amount'] ?? 0);
        }

        $cartInfo = [[
            'id'                  => $productId,
            'product_id'          => $productId,
            'cart_num'            => $num,
            'truePrice'           => $truePrice,
            'trueStock'           => $activityPriceInfo['stock'] ?? $skuInfo['stock'] ?? 0,
            'costPrice'           => $skuInfo['cost'] ?? 0,
            'sum_price'           => $skuInfo['price'] ?? $productInfo['price'] ?? 0,
            'vip_truePrice'       => 0,
            'price_type'          => '',
            'product_attr_unique' => $skuInfo['unique'] ?? '',
            'type'                => 0,
            'seckill_id'          => 0,
            'bargain_id'          => 0,
            'combination_id'      => 0,
            'advance_id'          => 0,
            'activity_type'       => $activityType,
            'activity_id'         => $activityId,
            'activity_integral'   => $activityIntegral,
            'productInfo'         => array_merge($productInfo, [
                'weight'        => $shippingInfo['weight'],
                'volume'        => $shippingInfo['volume'],
                'give_integral' => '0',
                'presale'       => 0,
                'is_virtual'    => $isVirtual ? 1 : 0,
                'gift_price'    => '0.00',
                'store_name'    => $productInfo['name'] ?? '',
                'image'         => $image,
                'cate_id'       => (string)$cateId,
                'product_type'  => $productType,
                'attrInfo'      => $defaultSkuInfo,  // 运费模板按重量/体积计算时需要
            ]),
        ]];

        // 普通商品直购必须沿用购物车的会员价计算规则，确认页与创建订单使用同一用户价格。
        $pricingPolicy = method_exists($handler, 'getPricingPolicy') ? $handler->getPricingPolicy() : [];
        $memberDiscount = 0;
        $paidMemberDiscount = 0;
        $priceType = '';
        if (!$isActivityOrder && $productType === 'normal') {
            /** @var UserServices $userServices */
            $userServices = app()->make(UserServices::class);
            $userInfo = $userServices->getUserInfo($uid);
            if (!$userInfo) {
                throw new \RuntimeException('用户信息不存在');
            }
            if (is_object($userInfo)) {
                $userInfo = $userInfo->toArray();
            }

            $discount = 100;
            if (sys_config('member_func_status', 1)) {
                /** @var SystemUserLevelServices $systemLevelServices */
                $systemLevelServices = app()->make(SystemUserLevelServices::class);
                $discount = $systemLevelServices->value([
                    'id'      => $userInfo['level'] ?? 0,
                    'is_del'  => 0,
                    'is_show' => 1,
                ], 'discount') ?: 100;
            }
            /** @var MemberCardServices $memberCardServices */
            $memberCardServices = app()->make(MemberCardServices::class);
            $vipStatus = $memberCardServices->isOpenMemberCard('vip_price', false)
                && (int)($userInfo['is_money_level'] ?? 0) > 0;
            [$truePrice, $unitDiscount, $priceType] = $centerProductServices->setLevelPrice(
                $skuInfo['price'] ?? $productInfo['price'] ?? 0,
                $uid,
                $userInfo,
                $vipStatus,
                ($pricingPolicy['member_discount'] ?? true) ? $discount : 100,
                ($pricingPolicy['vip_discount'] ?? true) ? ($skuInfo['vip_price'] ?? 0) : 0,
                ($pricingPolicy['vip_discount'] ?? true) ? ($productInfo['is_vip'] ?? 0) : 0,
                true
            );
            $cartInfo[0]['truePrice'] = $truePrice;
            $cartInfo[0]['vip_truePrice'] = $unitDiscount;
            $cartInfo[0]['price_type'] = $priceType;
            $discountTotal = bcmul((string)$unitDiscount, (string)$num, 2);
            if ($priceType === 'level') {
                $memberDiscount = $discountTotal;
            } elseif ($priceType === 'member') {
                $paidMemberDiscount = $discountTotal;
            }
        }

        $payPostage = 0;
        // 不支持快递配送时，运费强制为0
        if ($productExpressShipping) {
            if ($shippingInfo['freight'] == 2 && $shippingInfo['postage'] > 0) {
                // 固定运费
                $payPostage = $shippingInfo['postage'] * $cartInfo[0]['cart_num'];
            } elseif ($shippingInfo['freight'] == 3) {
                // 运费模板：尝试使用用户默认地址计算运费
                try {
                    /** @var UserAddressServices $addressServices */
                    $addressServices = app()->make(UserAddressServices::class);
                    $addr = $addressServices->getUserDefaultAddress($uid);
                    if ($addr) {
                        $addr = $addr->toArray();
                        /** @var OrderComputedServices $computedServices */
                        $computedServices = app()->make(OrderComputedServices::class);
                        $priceGroup = $computedServices->getOrderPriceGroup(0, $cartInfo, $addr, []);
                        $payPostage = $priceGroup['storePostage'] ?? 0;
                    }
                } catch (\Throwable $e) {
                    // 地址不存在或计算失败，运费为0
                }
            }
        }

        $totalPrice = bcmul((string)$cartInfo[0]['truePrice'], (string)$cartInfo[0]['cart_num'], 2);
        $priceData = [
            'totalPrice'          => $totalPrice,
            'payPrice'            => bcadd($totalPrice, (string)$payPostage, 2),
            'payPostage'          => $payPostage,
            'isStoreFreePostage'  => false,
            'storeFreePostage'    => 0,
            'costPrice'           => bcmul((string)$cartInfo[0]['costPrice'], (string)$cartInfo[0]['cart_num'], 2),
            'giftPrice'           => 0,
            'activityIntegral'    => $activityIntegral,
            'levelPrice'          => $memberDiscount,
            'memberPrice'         => $paidMemberDiscount,
        ];

        return [$cartInfo, $priceData, $productInfo, $skuInfo, $registry];
    }

    /**
     * 构建商品规格快照，供订单 extra_info 使用
     * 从购物车商品信息中提取 SKU 唯一编码、规格名称、图片等字段
     * @param array $cart 购物车单项
     * @return array 返回规格快照，无规格时返回空数组
     */
    private function buildSkuInfoForExtra(array $cart): array
    {
        $attrInfo = $cart['productInfo']['attrInfo'] ?? [];
        if (empty($attrInfo)) {
            return [];
        }
        return [
            'unique'   => $attrInfo['unique'] ?? '',
            'suk'      => $attrInfo['suk'] ?? '',
            'image'    => $attrInfo['image'] ?? '',
            'price'    => $attrInfo['price'] ?? 0,
            'ot_price' => $attrInfo['ot_price'] ?? 0,
            'product_id' => $cart['product_id'] ?? $cart['productInfo']['id'] ?? 0,
        ];
    }

    /**
     * 构建商品明细快照，供订单 extra_info.items 使用
     * 格式兼容 loadOrderItems 和退款页面所需字段
     * @param array $cartInfo 购物车商品列表
     * @return array 返回商品明细快照列表
     */
    private function buildOrderItemsForExtra(array $cartInfo): array
    {
        $items = [];
        foreach ($cartInfo as $index => $item) {
            $items[] = [
                'cart_id'       => $item['id'] ?? ($index + 1),
                'product_id'    => $item['product_id'] ?? $item['productInfo']['id'] ?? 0,
                'image'         => $item['productInfo']['image'] ?? '',
                'store_name'    => $item['productInfo']['store_name'] ?? '',
                'price'         => $item['productInfo']['price'] ?? '0.00',
                'true_price'    => $item['truePrice'] ?? '0.00',
                // 商品级购买送积分快照（eb_product_center.give_integral），收货时按数量聚合发放
                'give_integral' => (float)($item['productInfo']['give_integral'] ?? 0),
                'cart_num'      => $item['cart_num'] ?? 1,
                'product_type'  => $item['productInfo']['product_type'] ?? '',
                'attr_info'     => $item['productInfo']['attrInfo'] ?? [],
                'sku'           => $item['productInfo']['attrInfo']['suk'] ?? '默认',
                'postage_price' => $item['postage_price'] ?? '0.00',
            ];
        }
        return $items;
    }

    /**
     * 订单支付
     * 从中台表读取订单，调用对应支付方式完成支付
     */
    public function pay(YuePayServices $yuePayServices, OrderPayServices $payServices)
    {
        [$uni, $paytype, $quitUrl, $type] = request()->postMore([
            ['uni', ''],
            ['paytype', ''],
            ['quitUrl', ''],
            ['type', 0]
        ], true);

        if (!$uni) return app('json')->fail('参数错误');

        $payLock = CacheService::get('PAY_LOCK_' . $uni);
        if ($payLock) return app('json')->fail('订单支付中，请勿重复支付');
        CacheService::set('PAY_LOCK_' . $uni, 'PAY_LOCK', 2);

        // 从中台表查询订单
        /** @var OrderCenterUserServices $centerServices */
        $centerServices = app()->make(OrderCenterUserServices::class);
        $centerOrder = $centerServices->getDetailByOrderNo($uni, false);
        if (!$centerOrder) {
            CacheService::delete('PAY_LOCK_' . $uni);
            return app('json')->fail('订单不存在');
        }

        if (in_array($centerOrder['status_code'] ?? '', [OrderCenter::STATUS_CANCELED, OrderCenter::STATUS_DELETED], true)) {
            return app('json')->fail('订单已经超过系统支付时间，无法支付，请重新下单');
        }
        if ($centerOrder['pay_status'] == 1) {
            return app('json')->fail('订单已支付');
        }

        $uid = $type == 1 ? (int)request()->uid() : $centerOrder['uid'];
        $orderNo = $centerOrder['order_no'] ?? $uni;

        // 构建兼容支付服务接口的订单数组
        $orderInfo = [
            'id'             => $centerOrder['id'] ?? 0,
            'order_id'       => $orderNo,
            'uid'            => $centerOrder['uid'],
            'pay_uid'        => $centerOrder['uid'],
            'pay_price'      => $centerOrder['pay_price'] ?? '0.00',
            'pay_postage'    => $centerOrder['pay_postage'] ?? '0.00',
            'paid'           => 0,
            'is_channel'     => request()->isApp() ? 0 : 1,
            'unique'         => $orderNo,
            'is_cancel'      => 0,
            'is_del'         => 0,
            'is_system_del'  => 0,
            'combination_id' => 0,
            'seckill_id'     => 0,
            'bargain_id'     => 0,
            'refund_status'  => 0,
            'real_name'      => $centerOrder['extra_info']['contact']['name'] ?? '',
            'storeName'      => $centerOrder['title'] ?? '',
        ];

        // 0元支付或免费活动订单
        $isActivityOrder = !empty($centerOrder['activity_type']) && $centerOrder['activity_type'] !== 'normal' && (int)($centerOrder['activity_id'] ?? 0) > 0;
        $activityType = $centerOrder['activity_type'] ?? 'normal';
        $activityId = (int)($centerOrder['activity_id'] ?? 0);
        $tradeModeHandler = null;
        if ($isActivityOrder) {
            /** @var TradeModeRegistry $tradeModeRegistry */
            $tradeModeRegistry = app()->make(TradeModeRegistry::class);
            $tradeModeHandler = $tradeModeRegistry->get($activityType);
        }

        $isFreePay = false;
        if ($isActivityOrder && $tradeModeHandler) {
            $isFreePay = $tradeModeHandler->isFreePay($activityId);
        }

        if (bcsub((string)$orderInfo['pay_price'], '0', 2) <= 0 || ($isActivityOrder && $isFreePay)) {
            // 活动订单在 0 元支付前先执行活动支付处理（如扣减积分）
            if ($isActivityOrder && $tradeModeHandler) {
                try {
                    $extraInfo = $centerOrder['extra_info'] ?? [];
                    $skuUnique = $extraInfo['sku_info']['unique'] ?? '';
                    $tradeModeHandler->beforeOrderPay($orderNo, $activityId, $centerOrder);
                    $tradeModeHandler->onOrderPay($orderNo, $activityId, array_merge($centerOrder, [
                        'sku_unique' => $skuUnique,
                    ]));
                } catch (\Throwable $e) {
                    CacheService::delete('PAY_LOCK_' . $uni);
                    return app('json')->status('pay_error', $e->getMessage());
                }
            }

            /** @var OrderSuccessServices $success */
            $success = app()->make(OrderSuccessServices::class);
            // 免费活动订单使用 free 支付方式记录，避免被记录为余额支付
            $freePayType = $isActivityOrder && $isFreePay ? 'free' : PayServices::YUE_PAY;
            $payPriceStatus = $success->zeroYuanPayment($orderInfo, $uid, $freePayType);
            if ($payPriceStatus) {
                // 0 元支付成功后触发活动支付成功回调（如扣减活动库存）
                if ($isActivityOrder && $tradeModeHandler) {
                    try {
                        $extraInfo = $centerOrder['extra_info'] ?? [];
                        $skuUnique = $extraInfo['sku_info']['unique'] ?? '';
                        $tradeModeHandler->afterOrderPay($orderNo, $activityId, array_merge($centerOrder, [
                            'sku_unique' => $skuUnique,
                        ]));
                    } catch (\Throwable $e) {
                        \think\facade\Log::warning('活动支付成功回调执行失败: ' . $e->getMessage());
                    }
                }
                return app('json')->status('success', '支付成功', ['order_id' => $orderNo, 'key' => $orderNo]);
            }

            // 0 元支付失败时，触发活动支付失败回调回滚已扣减资源（如积分）
            if ($isActivityOrder && $tradeModeHandler) {
                try {
                    $extraInfo = $centerOrder['extra_info'] ?? [];
                    $skuUnique = $extraInfo['sku_info']['unique'] ?? '';
                    $tradeModeHandler->onOrderPayFail($orderNo, $activityId, array_merge($centerOrder, [
                        'sku_unique' => $skuUnique,
                    ]));
                } catch (\Throwable $e) {
                    \think\facade\Log::warning('活动支付失败回调执行失败: ' . $e->getMessage());
                }
            }

            return app('json')->status('pay_error', '支付失败');
        }

        app()->make(PayRecordServices::class)->assertChannel($orderNo, (string)$paytype);
        switch ($paytype) {
            case PayServices::YUE_PAY:
                $pay = $yuePayServices->yueOrderPay($orderInfo, (int)request()->uid());
                if ($pay['status'] === true) {
                    return app('json')->status('success', '余额支付成功');
                }
                if (is_array($pay)) {
                    return app('json')->status($pay['status'], $pay['msg']);
                }
                return app('json')->status('pay_error', $pay);

            case PayServices::OFFLINE_PAY:
                /** @var OrderCenterServices $centerServices */
                $centerServices = app()->make(OrderCenterServices::class);
                if ($centerServices->setOrderTypePayOffline($orderNo)) {
                    return app('json')->status('success', '订单创建成功');
                }
                return app('json')->status('success', '支付失败');

            default:
                $payOptions = array_merge(PayCredentialService::getCredentials((int)$uid, $paytype), ['quitUrl' => $quitUrl, 'payer_uid' => (int)$uid]);
                $payInfo = $payServices->beforePay($orderInfo, $paytype, $payOptions);
                return app('json')->status($payInfo['status'], $payInfo['payInfo']);
        }
    }

    /**
     * 订单收银台
     * 返回支付渠道开关和订单基本信息
     */
    public function cashier($orderNo, $type = 'order')
    {
        if (!$orderNo) {
            return app('json')->fail('参数错误');
        }

        /** @var PayChannelServices $payChannelServices */
        $payChannelServices = app()->make(PayChannelServices::class);
        $data = [
            'ali_pay_status'    => $payChannelServices->getChannelStatus('alipay'),
            'wechat_pay_status' => $payChannelServices->getChannelStatus('weixin') || $payChannelServices->getChannelStatus('weixin_v3'),
            'offline_pay_status'=> $payChannelServices->getChannelStatus('offline'),
            'friend_pay_status' => $payChannelServices->getChannelStatus('friend'),
            'yue_pay_status'    => $payChannelServices->getChannelStatus('yue'),
        ];

        $data['order_id'] = $orderNo;
        $data['pay_price'] = '0';
        $data['now_money'] = app()->make(UserServices::class)->value(['uid' => (int)request()->uid()], 'now_money');

        switch ($type) {
            case 'order':
                /** @var OrderCenterUserServices $centerServices */
                $centerServices = app()->make(OrderCenterUserServices::class);
                $order = $centerServices->getDetailByOrderNo($orderNo, false);
                if (!$order) {
                    return app('json')->fail('您支付的订单不存在');
                }
                $orderCancelTime = sys_config('order_cancel_time', 0);
                $time = $orderCancelTime * 60 * 60 + ($order['add_time'] ?? time());

                if ($time < 0) {
                    $time = 0;
                }

                $data['pay_price'] = $order['pay_price'] ?? '0';
                $data['pay_postage'] = $order['pay_postage'] ?? '0';
                $data['offline_postage'] = (int)sys_config('offline_postage', 0);
                $data['invalid_time'] = $time;
                $data['oid'] = $order['id'] ?? 0;
                // 送礼订单：支付成功后跳支付结果页并展示送礼分享
                $payExtra = $order['extra_info'] ?? [];
                $payExtra = is_string($payExtra) ? (json_decode($payExtra, true) ?: []) : ($payExtra ?: []);
                $isGiftOrder = !empty($payExtra['gift']) || !empty($payExtra['gift_mark']) || (float)($order['gift_price'] ?? 0) > 0;
                $data['is_gift'] = $isGiftOrder ? 1 : 0;
                $data['gift_mark'] = (string)($payExtra['gift_mark'] ?? '');
                break;

            default:
                return app('json')->fail('暂不支持其他类型订单支付');
        }

        return app('json')->success($data);
    }

    /**
     * 商家寄件回调
     * @return \think\Response
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/6/12
     */
    public function callBack()
    {
        $orderDao = app()->make(OrderCenterDao::class);
        $data = request()->postMore([
            ['type', ''],
            ['data', ''],
        ]);
        $data['data'] = $this->decrypt($data['data'], sys_config('yihaotong_appsecret'));
        switch ($data['type']) {
            case 'detection'://检测回调地址
                return \json($data['data']);
                break;
            case 'order_success'://下单成功
                $update = [
                    'label' => $data['data']['label'] ?? '',
                ];
                //韵达会异步推送单号
                if (isset($data['kuaidinum'])) {
                    $update['delivery_id'] = $data['kuaidinum'];
                }
                if (isset($data['task_id'])) {
                    $orderDao->update(['kuaidi_task_id' => $data['task_id']], $update);
                }
                break;
            case 'order_take'://取件
                if (isset($data['data']['task_id'])) {
                    $orderInfo = $orderDao->get(['kuaidi_task_id' => $data['data']['task_id']]);
                    if (!$orderInfo) {
                        return app('json')->fail('订单不存在');
                    }
                    $orderDao->update(['kuaidi_task_id' => $data['data']['task_id']], [
                        'status' => 1,
                        'is_stock_up' => 0
                    ]);
                    // eb_store_order_status 表已删除，状态记录已移除
                }
                break;
            case 'order_cancel'://取消寄件
                if (isset($data['data']['task_id'])) {
                    $orderInfo = $orderDao->get(['kuaidi_task_id' => $data['data']['task_id']]);
                    if (!$orderInfo) {
                        return app('json')->fail('订单不存在');
                    }
                    if ($orderInfo->is_stock_up && $orderInfo->status == 0) {
                        $orderInfo->status = 0;
                        $orderInfo->is_stock_up = 0;
                        $orderInfo->kuaidi_task_id = '';
                        $orderInfo->kuaidi_order_id = '';
                        $orderInfo->express_dump = '';
                        $orderInfo->kuaidi_label = '';
                        $orderInfo->delivery_id = '';
                        $orderInfo->delivery_code = '';
                        $orderInfo->delivery_name = '';
                        $orderInfo->delivery_type = '';
                        $orderInfo->save();
                    } else {
                        Log::error('商家寄件自动回调，订单状态不正确：', [
                            'kuaidi_task_id' => $data['data']['task_id']
                        ]);
                    }
                }
                break;
            case 'success'://电子发票回调
                $oid = $orderDao->value(['order_id' => $data['data']['unique']], 'id');
                if ($oid) {
                    $invoiceServices = app()->make(OrderInvoiceServices::class);
                    $invoiceServices->update([
                        'category' => 'order',
                        'order_id' => $oid,
                    ], [
                        'unique' => $data['data']['unique'],
                        'invoice_type' => $data['data']['invoice_type'],
                        'invoice_num' => $data['data']['invoice_num'],
                        'invoice_serial_number' => $data['data']['invoice_serial_number'],
                        'is_invoice' => 1,
                        'invoice_time' => time()
                    ]);
                }
                break;
        }

        return app('json')->success();
    }

    /**
     * 解密商家寄件回调
     * @param string $encryptedData
     * @param string $key
     * @return false|string
     * @author: 吴汐
     * @email: 442384644@qq.com
     * @date: 2023/8/31
     */
    private function decrypt(string $encryptedData, string $key)
    {
        $key = substr($key, 0, 32);
        $decodedData = base64_decode($encryptedData);
        $iv = substr($decodedData, 0, 16);
        $encrypted = substr($decodedData, 16);
        $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        return json_decode($decrypted, true);
    }

    /**
     * 订单取消   未支付的订单回退积分,回退优惠券,回退库存
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function cancel()
    {
        [$id] = request()->postMore([['id', 0]], true);
        if (!$id) return app('json')->fail('参数错误');
        $orderCenterServices = app()->make(OrderCenterUserServices::class);
        if ($orderCenterServices->cancelOrder($id, (int)request()->uid()))
            return app('json')->success('取消成功');
        return app('json')->fail('取消失败');
    }

    /**
     * 获取确认订单页面是否展示快递配送和到店自提
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function checkShipping()
    {
        [$cartId, $new] = request()->postMore(['cartId', 'new'], true);
        $orderCenterServices = app()->make(OrderCenterUserServices::class);
        return app('json')->success($orderCenterServices->checkShipping((int)request()->uid(), $cartId, (bool)$new));
    }

    /**
     * 退款订单详情
     * @param $uni
     * @param string $cartId
     * @return mixed
     */
    public function refundDetail($uni, $cartId = '')
    {
        if (!strlen(trim($uni))) return app('json')->fail('参数错误');
        $orderCenterServices = app()->make(OrderCenterUserServices::class);
        $order = $orderCenterServices->getDetailByOrderNo($uni, false);
        if (!$order) return app('json')->fail('订单不存在');
        // 订单详情格式化（OrderOutServices 已中台化，tidyOrder 基于中台数据）
        $orderData = app()->make(OrderOutServices::class)->tidyOrder($order, true);
        foreach ($orderData['cartInfo'] ?? [] as $key => $cart) {
            $orderData['cartInfo'][$key]['one_postage_price'] = isset($cart['postage_price']) ? bcdiv($cart['postage_price'], $cart['cart_num'], 2) : 0;
            if ($cartId !== '') {
                if ($cart['id'] != $cartId) {
                    unset($orderData['cartInfo'][$key]);
                } else {
                    $orderData['total_num'] = $orderData['cartInfo'][$key]['cart_num'];
                    $orderData['pay_price'] = bcadd(bcmul($cart['truePrice'], $orderData['total_num'], 4), bcmul($orderData['total_num'], $orderData['cartInfo'][$key]['one_postage_price'], 4), 2);
                }
            }
        }
        $orderData['cartInfo'] = array_merge($orderData['cartInfo']);
        return app('json')->success($orderData);
    }

    /**
     * 订单退款理由
     * @return mixed
     */
    public function refundReason()
    {
        $reason = sys_config('stor_reason') ?: [];//退款理由
        $reason = str_replace("\r\n", "\n", $reason);//防止不兼容
        $reason = explode("\n", $reason);
        return app('json')->success($reason);
    }

    /**
     * 订单收货
     * @param OrderTakeServices $services
     * @param StoreCouponIssueServices $issueServices
     * @return mixed
     */
    public function take(OrderTakeServices $services, StoreCouponIssueServices $issueServices)
    {
        [$uni] = request()->postMore([
            ['uni', ''],
        ], true);
        if (!$uni) return app('json')->fail('参数错误');
        $order = $services->takeOrder($uni, (int)request()->uid());
        if ($order) {
            return app('json')->success('收货成功');
        } else
            return app('json')->fail('收货失败');
    }

    /**
     * 订单 查看物流
     * @param ExpressServices $expressServices
     * @param $uni
     * @param string $type
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function express(ExpressServices $expressServices, $uni, $type = '')
    {
        $orderCenterServices = app()->make(OrderCenterUserServices::class);
        // 售后退货/补发物流查询：$uni 为售后单号，校验归属后复用 service 层物流查询能力
        if ($type === 'aftersale' || $type === 'aftersale_resend') {
            $afterSaleServices = app()->make(AfterSaleServices::class);
            // 校验售后单归属当前用户，防止越权查询他人售后物流
            $afterSale = $afterSaleServices->getOwnedByNo($uni, (int)request()->uid());
            if (empty($afterSale)) {
                return app('json')->fail('售后单不存在');
            }
            // 复用 service 层物流查询（含快递公司名称→编码解析），return=退货物流 resend=补发物流
            $logisticsType = $type === 'aftersale_resend' ? 'resend' : 'return';
            try {
                $expressData = $afterSaleServices->queryExpress((int)$afterSale['id'], $logisticsType);
            } catch (\RuntimeException $e) {
                return app('json')->fail($e->getMessage());
            }
            // 从售后商品快照构造展示信息（物流页商品列表）
            $info = [];
            $snapshot = is_array($afterSale['product_snapshot']) ? $afterSale['product_snapshot'] : [];
            foreach ($snapshot['items'] ?? [] as $item) {
                $info[] = [
                    'cart_num'      => $item['cart_num'] ?? 1,
                    'truePrice'     => $item['true_price'] ?? '0.00',
                    'postage_price' => $item['postage_price'] ?? '0.00',
                    'productInfo'   => [
                        'image'      => $item['image'] ?? '',
                        'store_name' => $item['store_name'] ?? '',
                        'unit_name'  => $item['unit_name'] ?? '',
                    ],
                ];
            }
            // 快照为空时回退到订单中台读取商品列表（兼容早期未填充快照的售后单）
            if (empty($info) && !empty($afterSale['order_no'])) {
                try {
                    $sourceOrder = $orderCenterServices->getDetailByOrderNo($afterSale['order_no'], false);
                    foreach ($sourceOrder['order_items'] ?? [] as $item) {
                        $info[] = [
                            'cart_num'      => $item['cart_num'] ?? 1,
                            'truePrice'     => $item['true_price'] ?? $item['price'] ?? '0.00',
                            'postage_price' => $item['postage_price'] ?? '0.00',
                            'productInfo'   => [
                                'image'      => $item['image'] ?? '',
                                'store_name' => $item['store_name'] ?? '',
                                'unit_name'  => $item['unit_name'] ?? '',
                            ],
                        ];
                    }
                } catch (\Throwable $e) {
                    // 订单读取失败不阻断物流查询，商品列表留空
                }
            }
            $orderInfo = [
                'delivery_id'   => $expressData['delivery_id'],
                'delivery_name' => $expressData['delivery_name'],
                'delivery_code' => '',
                'delivery_type' => 'express',
                'cartInfo'      => $info,
            ];
            return app('json')->success([
                'order' => $orderInfo,
                'express' => [
                    'result' => ['list' => $expressData['result']]
                ]
            ]);
        }
        // type=refund 旧退款物流分支已下线，退货/补发物流统一走上方 aftersale 分支
            try {
                $ownedId = $orderCenterServices->getOwnedOrderIdByNo((string)$uni, (int)request()->uid());
                if ($ownedId === null) {
                    return app('json')->fail('订单不存在');
                }
                // 物流接口自身会查询轨迹，这里只取订单/配送信息，避免重复请求第三方
                $order = $orderCenterServices->getDetail($ownedId, false);
            } catch (\Throwable $e) {
                return app('json')->fail('订单不存在');
            }
            // 优先读取 extra_info.delivery；旧订单可能只在详情顶层保留配送字段，需回退以兼容历史数据。
            $delivery = is_array($order['extra_info']['delivery'] ?? null)
                ? $order['extra_info']['delivery']
                : [];
            foreach (['delivery_type', 'delivery_id', 'delivery_name', 'delivery_code'] as $deliveryField) {
                if (!empty($delivery[$deliveryField])) {
                    $order[$deliveryField] = $delivery[$deliveryField];
                } else {
                    $order[$deliveryField] = $order[$deliveryField] ?? '';
                }
            }
            // contact 已被 getDetail 提升到顶层并从 extra_info 移除，从 $order['contact'] 读取
            $contact = $order['contact'] ?? [];
            // user_address 已由 getDetail 自动格式化，无需重复处理
            $order['user_phone'] = $contact['phone'] ?? ($contact['user_phone'] ?? '');
            $order['real_name'] = $contact['name'] ?? ($contact['real_name'] ?? '');

            if ($order['delivery_type'] != 'express' || !$order['delivery_id']) return app('json')->fail('快递单号不存在');
            $express = $order['delivery_id'];
            $cacheName = $uni . $express;
            $orderInfo = [];
            $info = [];
            // 从 order_items（extra_info.items 快照）读取商品列表
            foreach ($order['order_items'] ?? [] as $item) {
                $info[] = [
                    'cart_num'      => $item['cart_num'] ?? 1,
                    'truePrice'     => $item['true_price'] ?? $item['price'] ?? '0.00',
                    'postage_price' => $item['postage_price'] ?? '0.00',
                    'productInfo'   => [
                        'image'      => $item['image'] ?? '',
                        'store_name' => $item['store_name'] ?? '',
                        'unit_name'  => $item['unit_name'] ?? '',
                        'suk'        => $item['sku'] ?? '',
                    ],
                ];
            }
            $orderInfo['delivery_id'] = $express;
            $orderInfo['delivery_name'] = $order['delivery_name'];
            $orderInfo['delivery_code'] = $order['delivery_code'];
            $orderInfo['delivery_type'] = $order['delivery_type'];
            $orderInfo['user_address'] = $order['user_address'];
            $orderInfo['user_mark'] = $order['mark'];
            $orderInfo['cartInfo'] = $info;
            $orderInfo['user_name'] = $order['real_name'];
            $orderInfo['user_phone'] = $order['user_phone'];
            $orderInfo['send_address'] = $order['user_address'];
            $address = explode(' ', $order['user_address']);
            $orderInfo['send_city'] = isset($address[0]) && in_array($address[0], ['北京', '上海', '天津', '重庆', '香港', '澳门', '台湾']) ? $address[0] : ($address[1] ?? '');
            $orderInfo['delivery_type'] = $order['delivery_type'] ?? 1;
            $orderInfo['user_city'] = $orderInfo['send_city'];
            $orderInfo['user_mark'] = $order['mark'] ?? '';
        return app('json')->success([
            'order' => $orderInfo,
            'express' => [
                'result' => ['list' => $expressServices->query($cacheName, $orderInfo['delivery_id'], $orderInfo['delivery_code'], $order['user_phone'])
                ]
            ]
        ]);
    }

    /**
     * 订单删除
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function del()
    {
        [$uni] = request()->postMore([
            ['uni', ''],
        ], true);
        if (!$uni) return app('json')->fail('参数错误');
        $orderCenterServices = app()->make(OrderCenterUserServices::class);
        $res = $orderCenterServices->removeOrder($uni, (int)request()->uid());
        if ($res) {
            return app('json')->success('删除成功');
        } else {
            return app('json')->fail('删除失败');
        }
    }

    /**
     * 订单 再次下单
     * @param StoreCartServices $services
     * @return mixed
     */
    public function again(StoreCartServices $services)
    {
        [$uni] = request()->postMore([
            ['uni', ''],
        ], true);
        $orderCenterServices = app()->make(OrderCenterUserServices::class);
        $cateId = $orderCenterServices->againOrder($services, $uni, (int)request()->uid());
        return app('json')->success(['cateId' => implode(',', $cateId)]);
    }

    /**
     * 订单商品信息（评价页商品展示，中台化）
     * @return mixed
     */
    public function product()
    {
        [$unique] = request()->postMore([['unique', '']], true);
        $item = $this->findOrderItemBySkuUnique((string)$unique);
        if (!$item) return app('json')->fail('商品不存在');
        // 订单号由辅助方法附带返回（_order_no）
        $cartProduct = [];
        $cartProduct['cart_num'] = (int)($item['cart_num'] ?? 1);
        $cartProduct['productInfo']['image'] = get_thumb_water($item['image'] ?? '');
        $cartProduct['productInfo']['price'] = $item['price'] ?? 0;
        $cartProduct['productInfo']['store_name'] = $item['store_name'] ?? '';
        if (!empty($item['attr_info'])) {
            $cartProduct['productInfo']['attrInfo']['product_id'] = $item['attr_info']['product_id'] ?? ($item['product_id'] ?? 0);
            $cartProduct['productInfo']['attrInfo']['suk'] = $item['sku'] ?? ($item['attr_info']['suk'] ?? '');
            $cartProduct['productInfo']['attrInfo']['price'] = $item['attr_info']['price'] ?? ($item['price'] ?? 0);
            $cartProduct['productInfo']['attrInfo']['image'] = get_thumb_water($item['attr_info']['image'] ?? ($item['image'] ?? ''));
        }
        $cartProduct['product_id'] = $item['product_id'] ?? 0;
        $cartProduct['combination_id'] = $item['combination_id'] ?? 0;
        $cartProduct['seckill_id'] = $item['seckill_id'] ?? 0;
        $cartProduct['bargain_id'] = $item['bargain_id'] ?? 0;
        // 订单号从中台快照附带返回
        $cartProduct['order_id'] = $item['_order_no'] ?? '';
        return app('json')->success($cartProduct);
    }

    /**
     * 判断用户是否可对订单执行用户端操作：下单人本人或送礼订单领取人
     * @param array $order 订单数据（含 uid / gift_uid / extra_info）
     * @param int $uid 当前用户 ID
     * @return bool
     */
    protected function canOperateOrder(array $order, int $uid): bool
    {
        if ((int)($order['uid'] ?? 0) === $uid) {
            return true;
        }
        $giftUid = (int)($order['gift_uid'] ?? 0);
        if ($giftUid === 0) {
            $extra = $order['extra_info'] ?? [];
            if (is_string($extra)) {
                $extra = json_decode($extra, true) ?: [];
            }
            $giftUid = (int)($extra['gift']['gift_uid'] ?? ($extra['gift_uid'] ?? 0));
        }
        return $giftUid > 0 && $giftUid === $uid;
    }

    /**
     * 按 SKU 唯一码从中台订单快照定位商品行
     * 查询订单中台的 extra_info.items，匹配 attr_info.unique（与订单评价 unique 同源）
     * @param string $unique SKU 唯一码
     * @param int|null $uid 限定用户（评价场景必传防越权，下单人或送礼领取人），null 不限定
     * @return array|null 商品行（附 _order_no/_center_id），未找到返回 null
     */
    protected function findOrderItemBySkuUnique(string $unique, ?int $uid = null): ?array
    {
        if ($unique === '') {
            return null;
        }
        // 不直接按 uid 过滤：送礼订单的领取人也需要能评价（领取人 uid 不等于订单 uid）
        $where = [['extra_info', 'like', '%' . $unique . '%']];
        $orders = app()->make(OrderCenterDao::class)->selectList($where, 'id, uid, gift_uid, order_no, extra_info', 0, 0);
        foreach ($orders as $order) {
            if ($uid !== null && !$this->canOperateOrder($order, $uid)) {
                continue;
            }
            $extra = is_string($order['extra_info']) ? json_decode($order['extra_info'], true) : ($order['extra_info'] ?? []);
            foreach ($extra['items'] ?? [] as $item) {
                if (($item['attr_info']['unique'] ?? '') === $unique) {
                    $item['_order_no'] = (string)$order['order_no'];
                    $item['_center_id'] = (int)$order['id'];
                    return $item;
                }
            }
        }
        return null;
    }

    /**
     * 按订单号+SKU unique 定位中台订单内的评价商品条目
     * 评价必须落到用户指定的那笔订单：同商品重复购买时各订单 unique 相同，仅凭 unique 反查会命中旧订单
     * @param string $orderNo 中台订单号
     * @param string $unique SKU 唯一码
     * @param int|null $uid 限定归属用户（下单人或送礼领取人）
     * @return array|null
     */
    protected function findOrderItemByOrderNo(string $orderNo, string $unique, ?int $uid = null): ?array
    {
        if ($orderNo === '' || $unique === '') {
            return null;
        }
        $order = app()->make(OrderCenterDao::class)->get(['order_no' => $orderNo]);
        if (!$order) {
            return null;
        }
        $order = is_object($order) ? $order->toArray() : (array)$order;
        if ($uid !== null && !$this->canOperateOrder($order, $uid)) {
            return null;
        }
        $extra = is_string($order['extra_info'] ?? null) ? json_decode($order['extra_info'], true) : ($order['extra_info'] ?? []);
        foreach ($extra['items'] ?? [] as $item) {
            if (($item['attr_info']['unique'] ?? '') === $unique) {
                $item['_order_no'] = (string)$order['order_no'];
                $item['_center_id'] = (int)$order['id'];
                return $item;
            }
        }
        return null;
    }

    /**
     * 订单评价（中台化）
     * @param StoreProductReplyServices $replyServices
     * @return \think\Response|void
     */
    public function comment(StoreProductReplyServices $replyServices)
    {
        // ==================== 1. 获取请求参数 ====================
        $group = request()->postMore([
            ['unique', ''],
            ['comment', ''],
            ['pics', ''],
            ['product_score', 5],
            ['service_score', 5]
        ]);

        // ==================== 2. 基础参数验证 ====================
        $unique = $group['unique'];
        if (!$unique) {
            return app('json')->fail('参数错误');
        }

        // ==================== 3. 获取用户信息 ====================
        $uid = (int)request()->uid();
        $user_info = request()->user();
        // StoreProductReplyServices::comment() 要求数组（取 nickname/avatar）
        if (is_object($user_info)) {
            $user_info = $user_info->toArray();
        }

        // ==================== 4. 从中台订单快照定位商品（unique 为 SKU 唯一码，限定本人订单防越权） ====================
        // 页面带订单号（uni）时按订单精确定位；同商品重复购买时 unique 跨订单重复，仅凭 unique 会命中旧订单
        $uni = (string)request()->param('uni', '');
        $item = $uni !== ''
            ? $this->findOrderItemByOrderNo($uni, (string)$unique, $uid)
            : $this->findOrderItemBySkuUnique((string)$unique, $uid);
        if (!$item) {
            return app('json')->fail('商品不存在');
        }

        // ==================== 5. 获取订单信息 ====================
        $orderInfo = app()->make(OrderCenterDao::class)->get($item['_center_id'] ?? 0);
        if (!$orderInfo) {
            return app('json')->fail('订单不存在');
        }
        $orderInfo = $orderInfo->toArray();

        // ==================== 6. 权限验证 ====================
        // 下单人本人或送礼订单领取人均可评价（领取人评价时 uid 为领取人）
        if (!$this->canOperateOrder($orderInfo, $uid)) {
            return app('json')->fail('无权评价该订单');
        }

        // ==================== 7. 调用评价服务（业务逻辑和事件触发都在Service中）===================
        try {
            $cartInfo = [
                'oid' => (int)$orderInfo['id'],
                'product_id' => (int)($item['product_id'] ?? 0),
                'cart_info' => [
                    'product_id' => (int)($item['product_id'] ?? 0),
                    'productInfo' => [
                        'attrInfo' => [
                            'suk' => $item['sku'] ?? ($item['attr_info']['suk'] ?? ''),
                        ],
                    ],
                ],
            ];
            $replyServices->comment($uid, $user_info, $cartInfo, $orderInfo, $group);
        } catch (\Exception $e) {
            return app('json')->fail($e->getMessage());
        }

        // ==================== 8. 检查是否有抽奖可跳转 ====================
        // 通过事件机制，由抽奖插件决定是否跳转抽奖页。若插件未安装，事件无监听者，默认 false
        $lotteryResult = new \stdClass();
        $lotteryResult->toLottery = false;
        event('OrderCommentLotteryCheck', [$uid, $orderInfo, $lotteryResult]);
        return app('json')->success(['to_lottery' => $lotteryResult->toLottery]);
    }

    /**
     * 代付订单详情
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function friendDetail()
    {
        [$orderId] = request()->getMore([
            ['order_id', '']
        ], true);
        $orderId = (int) $orderId;
        if ($orderId <= 0) {
            return app('json')->fail('订单参数错误');
        }
        $orderCenterServices = app()->make(OrderCenterUserServices::class);
        $info = $orderCenterServices->getFriendDetail($orderId, (int) request()->uid());
        return app('json')->success(compact('info'));
    }

    /**
     * 领取礼物
     * @param $oid
     * @return mixed
     */
    public function receiveGift($oid)
    {
        [$gift_key, $shipping_type, $name, $phone, $address_id, $store_id] = request()->postMore([
            ['gift_key', ''],
            ['shipping_type', 1],
            ['name', ''],
            ['phone', ''],
            ['address_id', 0],
            ['store_id', 0],
        ], true);
        if (!$oid) {
            return app('json')->fail('缺少参数');
        }
        if ($shipping_type == 1 && $address_id == 0) {
            return app('json')->fail('请选择收货地址');
        }
        $uid = (int)request()->uid();
        $orderCenterServices = app()->make(OrderCenterUserServices::class);
        $res = $orderCenterServices->receiveGift($uid, (int)$oid, $gift_key, (int)$shipping_type, $name, $phone, (int)$address_id, (int)$store_id);
        if ($res) {
            return app('json')->success('领取成功', ['status' => 1]);
        } else {
            return app('json')->success('该礼品已经被别人领取', ['status' => 0]);
        }
    }

    /**
     * 礼品详情
     * @param $oid
     * @return mixed
     */
    public function giftDetail($oid)
    {
        if (!$oid) {
            return app('json')->fail('缺少参数');
        }
        $orderCenterServices = app()->make(OrderCenterUserServices::class);
        // 路由参数为字符串，服务层签名要求 int
        return app('json')->success($orderCenterServices->giftDetail((int)$oid));
    }

    /**
     * 退款中间页订单商品列表（中台化）
     * 从 eb_order_center.extra_info.items 读取商品快照
     * @param $orderNo
     * @return mixed
     */
    public function refundCartInfo($orderNo)
    {
        [$cart_ids] = request()->postMore([
            ['cart_ids', []]
        ], true);

        // 从 eb_order_center.extra_info.items 读取（统一订单）
        try {
            /** @var OrderCenterUserServices $centerServices */
            $centerServices = app()->make(OrderCenterUserServices::class);
            $order = $centerServices->getDetailByOrderNo($orderNo, false);

            // 从 order_items 获取商品明细
            $orderItems = $order['order_items'] ?? [];

            // order_items 为空时，从 order_center 基本信息合成一个商品项（兜底）
            if (empty($orderItems)) {
                $orderItems = [[
                    'cart_id'       => 1,
                    'product_id'    => $order['extra_info']['product_id'] ?? 0,
                    'image'         => $order['image'] ?? '',
                    'store_name'    => $order['title'] ?? '商品',
                    'price'         => $order['pay_price'] ?? '0.00',
                    'true_price'    => $order['pay_price'] ?? '0.00',
                    'cart_num'      => $order['total_num'] ?? 1,
                    'product_type'  => $order['product_type'] ?? 'normal',
                    'attr_info'     => [],
                    'sku'           => '默认',
                    'postage_price' => $order['pay_postage'] ?? '0.00',
                ]];
            }

            // 格式化为前端 refundCartInfo 期望的格式（与 getRefundCartList 兼容）
            $list = [];
            foreach ($orderItems as $index => $item) {
                $cartId = (string)($item['cart_id'] ?? ($item['id'] ?? ($index + 1)));
                $productInfo = [
                    'image'      => $item['image'] ?? '',
                    'store_name' => $item['store_name'] ?? '',
                    'attrInfo'   => $item['attr_info'] ?? [],
                    'price'      => $item['price'] ?? '0.00',
                ];

                $cartInfo = [
                    'productInfo'  => $productInfo,
                    'truePrice'    => $item['true_price'] ?? $item['price'] ?? '0.00',
                    'postage_price' => $item['postage_price'] ?? '0.00',
                    'cart_num'     => $item['cart_num'] ?? 1,
                ];

                $list[] = [
                    // BIGINT cart_id 统一以字符串返回，避免前端 JSON number 精度丢失。
                    'id'             => $cartId,
                    'cart_id'        => $cartId,
                    'cart_num'       => $item['cart_num'] ?? 1,
                    'truePrice'      => $item['true_price'] ?? $item['price'] ?? '0.00',
                    'postage_price'  => $item['postage_price'] ?? '0.00',
                    'productInfo'    => $productInfo,
                    'product_id'     => $item['product_id'] ?? 0,
                    'cart_info'      => $cartInfo,
                    'surplus_num'    => $item['cart_num'] ?? 1,
                    'sku'            => $item['sku'] ?? '默认',
                ];
            }

            // 如果传了 cart_ids，应用数量覆盖
            if ($cart_ids) {
                $cartIdsMap = array_combine(array_column($cart_ids, 'cart_id'), $cart_ids);
                foreach ($list as &$item) {
                    if (isset($cartIdsMap[$item['cart_id']]['cart_num'])) {
                        $item['cart_num'] = $cartIdsMap[$item['cart_id']]['cart_num'];
                        $item['cart_info']['cart_num'] = $item['cart_num'];
                    }
                }
            }

            return app('json')->success($list);
        } catch (\Throwable $e) {
            Log::error('[refundCartInfo] 从中台读取退款商品列表失败: ' . $e->getMessage());
            return app('json')->success([]);
        }
    }

    /**
     * 获取退货商品列表
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function refundCartInfoList()
    {
        [$cart_ids, $id] = request()->postMore([
            ['cart_ids', []],
            ['id', 0],
        ], true);
        if (!$id) {
            return app('json')->fail('参数错误');
        }
        $orderCenterServices = app()->make(OrderCenterUserServices::class);
        return app('json')->success($orderCenterServices->refundCartInfoList((array)$cart_ids, (int)$id));
    }

    /**
     * 获取退款表单配置
     * 根据产品类型返回不同的退款表单字段配置
     * @param $orderNo
     * @return mixed
     */
    public function refundFormConfig($orderNo)
    {
        if (!$orderNo) {
            return app('json')->fail('参数错误');
        }
        $uid = (int)request()->uid();
        /** @var OrderCenterUserServices $centerServices */
        $centerServices = app()->make(OrderCenterUserServices::class);
        try {
            $order = $centerServices->getDetailByOrderNo($orderNo, false);
        } catch (\Throwable $e) {
            return app('json')->fail($e->getMessage());
        }
        // 校验订单归属当前用户
        if (!isset($order['uid']) || (int)$order['uid'] !== $uid) {
            return app('json')->fail('订单不存在');
        }
        $productType = $order['product_type'] ?? 'normal';
        $formConfig = $this->getRefundFormConfigByProductType($productType);
        return app('json')->success($formConfig);
    }

    /**
     * 用户申请退款
     * 退款统一走售后流程，委托 AfterSaleServices::apply（type=refund_only 仅退款）
     * 各产品类型 Handler 的 getRule 校验可用性，售后单写入 eb_after_sale
     * 路由保留以兼容旧前端调用，前端新版直接走 /api/after_sale/apply
     * @param $orderNo
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function applyRefund($orderNo)
    {
        $data = request()->postMore([
            ['text', ''],
            ['refund_reason_wap_img', ''],
            ['refund_reason_wap_explain', ''],
            ['refund_type', 1],
            ['refund_price', 0.00],
            ['cart_ids', []]
        ]);
        if ($data['text'] === '') return app('json')->fail('参数错误');

        // 从 order_center 中台表读取订单
        $uid = (int)request()->uid();
        /** @var OrderCenterUserServices $centerServices */
        $centerServices = app()->make(OrderCenterUserServices::class);
        try {
            $order = $centerServices->getDetailByOrderNo($orderNo, false);
        } catch (\Throwable $e) {
            return app('json')->fail('订单不存在');
        }
        if ($uid != $order['uid']) {
            return app('json')->fail('订单不存在');
        }

        // 旧参数 refund_reason_wap_img 为逗号分隔字符串，统一转为数组
        $images = [];
        if (!empty($data['refund_reason_wap_img'])) {
            $images = array_values(array_filter(explode(',', $data['refund_reason_wap_img'])));
        }

        // 委托 AfterSaleServices 走售后流程（refund_only 仅退款）
        // 售后规则由各产品类型 Handler 的 getRule 校验，中台状态联动由 AfterSaleServices 内部处理
        try {
            /** @var AfterSaleServices $afterSaleServices */
            $afterSaleServices = app()->make(AfterSaleServices::class);
            $afterSaleServices->apply([
                'order_no'        => $orderNo,
                'uid'             => $uid,
                'type'            => 'refund_only',
                'refund_price'    => (float)$data['refund_price'],
                'reason'          => $data['text'],
                'evidence_images' => $images,
            ]);
            return app('json')->success('申请成功');
        } catch (\RuntimeException $e) {
            return app('json')->fail($e->getMessage());
        } catch (\Throwable $e) {
            return app('json')->fail($e->getMessage());
        }
    }

    /**
     * 根据产品类型获取退款表单配置
     * @param string $productType
     * @return array
     */
    protected function getRefundFormConfigByProductType(string $productType): array
    {
        $baseFields = [
            [
                'type' => 'reason',
                'label' => '退款原因',
                'required' => true,
                'options' => [],
                'show' => true,
            ],
            [
                'type' => 'textarea',
                'label' => '备注说明',
                'placeholder' => '填写备注信息，100字以内',
                'maxlength' => 100,
                'required' => true,
                'show' => true,
            ],
        ];

        switch ($productType) {
            case 'normal':
                return [
                    'show_select_step' => true,
                    'fields' => array_merge([
                        [
                            'type' => 'refund_type',
                            'label' => '退款类型',
                            'required' => true,
                            'options' => ['仅退款', '退货并退款'],
                            'show' => true,
                        ],
                        [
                            'type' => 'refund_num',
                            'label' => '退货件数',
                            'required' => true,
                            'show' => true,
                        ],
                    ], $baseFields, [
                        [
                            'type' => 'image',
                            'label' => '上传图片',
                            'max_count' => 3,
                            'show' => true,
                        ],
                    ]),
                ];
            case 'virtual':
            case 'cardkey':
            case 'coupon':
                return [
                    'show_select_step' => false,
                    'fields' => $baseFields,
                ];
            default:
                return [
                    'show_select_step' => true,
                    'fields' => $baseFields,
                ];
        }
    }
}
