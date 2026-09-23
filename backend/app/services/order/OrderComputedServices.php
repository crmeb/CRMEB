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

namespace app\services\order;

use crmeb\basic\BaseService;
use app\dao\order\OrderCenterDao;
use app\services\pay\PayServices;
use app\services\driver\pay\PayChannelServices;
use app\services\product\product\StoreCategoryServices;
use app\services\user\member\MemberCardServices;
use app\services\user\UserBillServices;
use app\services\user\UserServices;
use crmeb\exceptions\ApiException;
use app\services\user\UserAddressServices;
use app\services\activity\coupon\StoreCouponUserServices;

/**
 * 订单计算金额
 * Class OrderComputedServices
 * @package app\services\order
 */
class OrderComputedServices extends BaseService
{
    /**
     * @var OrderCenterDao
     */
    protected $dao;

    /**
     * 额外参数
     * @var array
     */
    protected $paramData = [];

    /**
     * OrderComputedServices constructor.
     * @param OrderCenterDao $dao
     */
    public function __construct(OrderCenterDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 设置额外参数
     * @param array $paramData
     * @return $this
     */
    public function setParamData(array $paramData)
    {
        $this->paramData = $paramData;
        return $this;
    }

    /**
     * 计算订单金额
     * @param int $uid
     * @param string $key
     * @param array $cartGroup
     * @param int $addressId
     * @param string $payType
     * @param bool $useIntegral
     * @param int $couponId
     * @param bool $is_create
     * @param int $shipping_type
     * @return array
     */
    public function computedOrder(int $uid, array $userInfo = [], array $cartGroup = [], int $addressId = 0, string $payType = '', bool $useIntegral = false, int $couponId = 0, bool $isCreate = false, int $shippingType = 1, int $is_gift = 0)
    {
        $systemPayType = PayServices::getPayType();
        // 线下支付启停统一从 eb_pay_channel 读取，不再读取 system_config
        if (!app()->make(PayChannelServices::class)->getChannelStatus('offline')) unset($systemPayType['offline']);
        if (!$userInfo) {
            /** @var UserServices $userServices */
            $userServices = app()->make(UserServices::class);
            $userInfo = $userServices->getUserInfo($uid);
            if (!$userInfo) {
                throw new ApiException('用户不存在');
            }
        }
        $cartInfo = $cartGroup['cartInfo'];
        $priceGroup = $cartGroup['priceGroup'];
        $other = $cartGroup['other'];
        $payPrice = (float)$priceGroup['totalPrice'];
        $addr = $cartGroup['addr'] ?? [];
        $postage = $priceGroup;
        if (!$addr || $addr['id'] != $addressId) {
            /** @var UserAddressServices $addressServices */
            $addressServices = app()->make(UserAddressServices::class);
            $addr = $addressServices->getAddress($addressId) ?? [];
            if ($addr) {
                $addr = $addr->toArray();
            }
            //改变地址重新计算邮费
            $postage = [];
        }
        $combinationId = $this->paramData['combinationId'] ?? 0;
        $seckillId = $this->paramData['seckill_id'] ?? 0;
        $bargainId = $this->paramData['bargainId'] ?? 0;
        $isActivity = $combinationId || $seckillId || $bargainId;
        $pricingPolicy = $other['pricing_policy'] ?? [];
        if (!$isActivity) {
            //促销优惠段（价格链条位[20]）：营销插件通过 OrderPromotionCompute 事件写入优惠（见 applyPromotion）
            if (($pricingPolicy['promotion'] ?? true) === true) {
                [$payPrice, $promotionPrice, $promotionDetail] = $this->applyPromotion($uid, $userInfo, $cartInfo, (string)$payPrice, $isCreate);
            }
            //使用优惠劵
            if (($pricingPolicy['coupon'] ?? true) === true) {
                [$payPrice, $couponPrice] = $this->useCouponId($couponId, $uid, $cartInfo, $payPrice, $isCreate);
            }
            //使用积分
            $pricingPolicy = $other['pricing_policy'] ?? [];
            [$payPrice, $deductionPrice, $usedIntegral, $SurplusIntegral] = $this->useIntegral(
                $useIntegral && (($pricingPolicy['integral'] ?? true) === true),
                $userInfo, $payPrice, $other
            );
        }

        //计算邮费
        [$payPrice, $payPostage, $storePostageDiscount, $storeFreePostage, $isStoreFreePostage] = $this->computedPayPostage($shippingType, $payType, $cartInfo, $addr, $payPrice, $postage, $other, $userInfo, $is_gift);

        //赠送商品计算
        $giftPrice = $priceGroup['giftPrice'] ?? 0;
        $payPrice = bcadd($payPrice, $giftPrice, 2);

        $result = [
            'total_price' => $priceGroup['totalPrice'],
            'gift_price' => $giftPrice,
            'level_price' => $priceGroup['levelPrice'] ?? 0,
            'member_price' => $priceGroup['memberPrice'] ?? 0,
            //促销优惠段（价格链条位[20]）优惠总额与明细，营销插件经 OrderPromotionCompute 写回
            'promotion_price' => $promotionPrice ?? 0,
            'promotion_detail' => $promotionDetail ?? [],
            'pay_price' => $payPrice > 0 ? $payPrice : 0,
            'pay_postage' => $payPostage,
            'coupon_price' => $couponPrice ?? 0,
            'deduction_price' => $deductionPrice ?? 0,
            'usedIntegral' => $usedIntegral ?? 0,
            'SurplusIntegral' => $SurplusIntegral ?? 0,
            'storePostageDiscount' => $storePostageDiscount ?? 0,
            'isStoreFreePostage' => $isStoreFreePostage ?? false,
            'storeFreePostage' => $storeFreePostage ?? 0
        ];
        $this->paramData = [];
        return $result;
    }

    /**
     * 应用促销优惠段（价格链条位[20]，优先级见 PriceChainPriority::PROMOTION）
     *
     * 中台不实现具体营销玩法，仅提供 OrderPromotionCompute 事件扩展缝口：
     * 交易营销类插件（如满减、满赠、加价购）通过模块 event.php 监听该事件，
     * 以引用方式向 $event['promotion_price'] 累加写回优惠总额、向 $event['detail']
     * 写回明细（key 为插件标识）。插件未安装时无监听器，本段为空操作。
     *
     * @param int $uid 用户编号
     * @param mixed $userInfo 用户信息（数组或 Model）
     * @param array $cartInfo 购物车信息
     * @param string $totalPrice 商品总价（未叠加任何优惠）
     * @param bool $isCreate 是否创建订单态（false 为结算预览态）
     * @return array [叠加促销后的应付金额, 促销优惠总额, 促销明细]
     */
    protected function applyPromotion(int $uid, $userInfo, array $cartInfo, string $totalPrice, bool $isCreate): array
    {
        //用户信息统一转为数组，保证插件侧数据契约一致
        if (is_object($userInfo) && method_exists($userInfo, 'toArray')) {
            $userInfo = $userInfo->toArray();
        }
        $event = [
            'uid' => $uid,
            'user_info' => $userInfo,
            'cart_info' => $cartInfo,
            'total_price' => $totalPrice,
            'is_create' => $isCreate,
            //===== 以下为扩展方写回区域（引用传递），中台只读 =====
            //促销优惠总额：数值型，多个营销活动各自累加后写回
            'promotion_price' => 0,
            //促销明细：['插件标识' => ['title' => '名称', 'price' => 减免额, ...自定义字段]]
            'detail' => [],
        ];
        event('OrderPromotionCompute', [&$event]);
        //边界保护：优惠金额不得为负，且不得超过商品总价
        $promotionPrice = min(max((float)($event['promotion_price'] ?? 0), 0), (float)$totalPrice);
        $payPrice = bcsub($totalPrice, (string)$promotionPrice, 2);
        return [$payPrice, $promotionPrice, is_array($event['detail']) ? $event['detail'] : []];
    }

    /**
     * 使用优惠卷
     * @param int $couponId
     * @param int $uid
     * @param $cartInfo
     * @param $payPrice
     * @param bool $is_create
     */
    public function useCouponId(int $couponId, int $uid, $cartInfo, $payPrice, bool $isCreate)
    {
        //使用优惠劵
        $res1 = true;
        if ($couponId) {
            /** @var StoreCouponUserServices $couponServices */
            $couponServices = app()->make(StoreCouponUserServices::class);
            $couponInfo = $couponServices->getOne([['id', '=', $couponId], ['uid', '=', $uid], ['is_fail', '=', 0], ['status', '=', 0], ['start_time', '<', time()], ['end_time', '>', time()]], '*', ['issue']);
            if (!$couponInfo) {
                throw new ApiException('选择的优惠劵无效');
            }
            $type = $couponInfo['applicable_type'] ?? 0;
            $flag = false;
            $price = 0;
            $count = 0;
            switch ($type) {
                case 0:
                case 3:
                    foreach ($cartInfo as $cart) {
                        $price = bcadd($price, bcmul((string)$cart['truePrice'], (string)$cart['cart_num'], 2), 2);
                        $count++;
                    }
                    break;
                case 1://品类券
                    /** @var StoreCategoryServices $storeCategoryServices */
                    $storeCategoryServices = app()->make(StoreCategoryServices::class);
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
                $flag = true;
            }
            if (!$flag) {
                throw new ApiException('不满足优惠劵的使用条件');
            }
            if ($isCreate) {
                $res1 = $couponServices->useCoupon($couponId);
            }
            $couponPrice = $couponInfo['coupon_price'] > $price ? $price : $couponInfo['coupon_price'];
            $payPrice = (float)bcsub((string)$payPrice, (string)$couponPrice, 2);
        } else {
            $couponPrice = 0;
        }
        if (!$res1) {
            throw new ApiException('使用优惠劵失败');
        }
        return [$payPrice, $couponPrice];
    }

    /**
     * 使用积分
     * @param $useIntegral
     * @param $userInfo
     * @param $payPrice
     * @param $other
     * @return array
     */
    public function useIntegral(bool $useIntegral, $userInfo, string $payPrice, array $other)
    {
        /** @var UserBillServices $userBillServices */
        $userBillServices = app()->make(UserBillServices::class);
        // 可用积分
        $usable = bcsub((string)$userInfo['integral'], (string)$userBillServices->getBillSum(['uid' => $userInfo['uid'], 'is_frozen' => 1]), 0);

        $SurplusIntegral = $usable;
        $integralRatio = (float)($other['integralRatio'] ?? sys_config('integral_ratio', 0));
        if ($useIntegral && $userInfo['integral'] > 0 && $integralRatio > 0) {
            //积分抵扣上限
            $integralMaxNum = sys_config('integral_max_num', 200);
            if ($integralMaxNum > 0 && $usable > $integralMaxNum) {
                $integral = $integralMaxNum;
            } else {
                $integral = $usable;
            }
            $deductionPrice = (float)bcmul((string)$integral, (string)$integralRatio, 2);
            if ($deductionPrice < $payPrice) {
                $payPrice = bcsub((string)$payPrice, (string)$deductionPrice, 2);
                $usedIntegral = $integral;
            } else {
                $deductionPrice = $payPrice;
                $usedIntegral = (int)ceil(bcdiv((string)$payPrice, (string)$integralRatio, 2));
                $payPrice = 0;
            }
            $deductionPrice = $deductionPrice > 0 ? $deductionPrice : 0;
            $usedIntegral = $usedIntegral > 0 ? $usedIntegral : 0;
            $SurplusIntegral = (int)bcsub((string)$usable, $usedIntegral, 0);
        } else {
            $deductionPrice = 0;
            $usedIntegral = 0;
        }
        if ($payPrice <= 0) $payPrice = 0;
        return [$payPrice, $deductionPrice, $usedIntegral, $SurplusIntegral];
    }

    /**
     * 计算邮费
     * @param int $shipping_type
     * @param string $payType
     * @param array $cartInfo
     * @param array $addr
     * @param string $payPrice
     * @param array $other
     * @return array
     */
    public function computedPayPostage(int $shipping_type, string $payType, array $cartInfo, array $addr, string $payPrice, array $postage = [], array $other = [], $userInfo = [], $is_gift = 0)
    {
        $storePostageDiscount = 0;
        $storeFreePostage = $postage['storeFreePostage'] ?? 0;
        $isStoreFreePostage = false;
        if ($is_gift == 1) {
            $shipping_type = 0;
            $addr = [];
        }
        if (!$storeFreePostage) {
            $storeFreePostage = floatval(sys_config('store_free_postage')) ?: 0;//满额包邮金额
        }
        if (!$addr && !isset($addr['id']) || !$cartInfo) {
            $payPostage = 0;
        } else {
            //$shipping_type = 1 快递发货 $shipping_type = 2 门店自提
            if ($shipping_type == 2) {
                $store_self_mention = sys_config('store_self_mention') ?? 0;
                if (!$store_self_mention) $shipping_type = 1;
            }
            //门店自提 || （线下支付 && 线下支付包邮） 没有邮费支付
            if ($shipping_type === 2 || ($payType == 'offline' && ((isset($other['offlinePostage']) && $other['offlinePostage']) || sys_config('offline_postage')) == 1)) {
                $payPostage = 0;
            } else {
                if (!$postage || !isset($postage['storePostage']) || !isset($postage['storePostageDiscount'])) {
                    $postage = $this->getOrderPriceGroup($storeFreePostage, $cartInfo, $addr, $userInfo);
                }
                $payPostage = $postage['storePostage'];
                $storePostageDiscount = $postage['storePostageDiscount'];
                $isStoreFreePostage = $postage['isStoreFreePostage'] ?? false;

                $payPrice = (float)bcadd((string)$payPrice, (string)$payPostage, 2);
            }
        }
        return [$payPrice, $payPostage, $storePostageDiscount, $storeFreePostage, $isStoreFreePostage];
    }

    /**
     * 运费计算,总金额计算
     * @param $cartInfo
     * @param $addr
     * @param array $userInfo
     * @return array
     */
    public function getOrderPriceGroup($storeFreePostage, $cartInfo, $addr, $userInfo = [], $shipping_type = 1, $is_gift = 0)
    {
        $storePostage = 0;
        $storePostageDiscount = 0;
        $giftPrice = 0;
        $isStoreFreePostage = false;//是否满额包邮
        $sumPrice = $this->getOrderSumPrice($cartInfo, 'sum_price');//获取订单原总金额
        $totalPrice = $this->getOrderSumPrice($cartInfo, 'truePrice');//获取订单svip、用户等级优惠之后总金额
        $costPrice = $this->getOrderSumPrice($cartInfo, 'costPrice');//获取订单成本价
        $vipPrice = $this->getOrderSumPrice($cartInfo, 'vip_truePrice');//获取订单等级和付费会员总优惠金额
        $levelPrice = $this->getOrderSumPrice($cartInfo, 'level');//获取会员等级优惠
        $memberPrice = $this->getOrderSumPrice($cartInfo, 'member');//获取付费会员优惠

        // 判断商品包邮和固定运费
        // 直接读取 freight_config.express 节点（新双节点格式）
        // freight=1/2/3 作为下游运费模板计算的中间态保留，从 express.type 推导
        foreach ($cartInfo as $key => &$item) {
            $item['postage_price'] = 0;
            // 解析运费配置
            $freightConfig = $item['productInfo']['freight_config'] ?? [];
            // 兼容 JSON 字符串存储的历史数据
            if (is_string($freightConfig)) {
                $decodedFc = json_decode($freightConfig, true);
                $freightConfig = is_array($decodedFc) ? $decodedFc : [];
            }
            if (!empty($freightConfig) && is_array($freightConfig)) {
                // 读取 express 节点
                $expressConfig = $freightConfig['express'] ?? ['type' => 'template'];
                $expressType = (string)($expressConfig['type'] ?? 'template');
                // 映射为 freight 中间态：free=1(包邮) / fixed=2(固定运费) / template=3(运费模板)
                $item['productInfo']['freight'] = $expressType === 'template' ? 3 : ($expressType === 'fixed' ? 2 : 1);
                $item['productInfo']['postage'] = (float)($expressConfig['postage'] ?? 0);
                $item['productInfo']['temp_id'] = (int)($expressConfig['temp_id'] ?? 0);
            }
            // 兜底：旧购物车快照可能同时缺失 freight_config 与 freight 字段，默认按包邮处理，避免下游 Undefined array key 报错
            $item['productInfo']['freight'] = $item['productInfo']['freight'] ?? 1;
            $item['productInfo']['postage'] = $item['productInfo']['postage'] ?? 0;
            $item['productInfo']['temp_id'] = $item['productInfo']['temp_id'] ?? 0;
            if ($shipping_type == 1) {
                if ($item['productInfo']['freight'] == 2) {
                    // 固定运费
                    $item['postage_price'] = bcmul((string)$item['productInfo']['postage'], (string)$item['cart_num'], 2);
                    $item['origin_postage_price'] = bcmul((string)$item['productInfo']['postage'], (string)$item['cart_num'], 2);
                    $storePostage = bcadd((string)$storePostage, (string)$item['postage_price'], 2);
                } elseif ($item['productInfo']['freight'] == 1) {
                    // 包邮
                    $item['postage_price'] = 0;
                }
                if ($sumPrice >= $storeFreePostage) {
                    $item['postage_price'] = $item['origin_postage_price'] = $storePostage = 0;
                }
            }
            if ($is_gift == 1) {
                $item['postage_price'] = $item['origin_postage_price'] = $storePostage = 0;
                $giftPrice = bcadd((string)$giftPrice, (string)$item['productInfo']['gift_price'], 2);
                $addr = [];
            }
        }
        $postageArr = [];
        if (isset($cartInfo[0]['productInfo']['is_virtual']) && $cartInfo[0]['productInfo']['is_virtual'] == 1) {
            $storePostage = 0;
        } elseif ($cartInfo && $addr) {
            // $storeFreePostage > 0 时检查是否满额包邮，为 0 时表示未配置满额包邮，直接按运费模板计算
            if ($storeFreePostage > 0 && $sumPrice >= $storeFreePostage) {//如果总价大于等于满额包邮 邮费等于0
                $isStoreFreePostage = true;
                $storePostage = 0;
            } else {
                //按照运费模板计算每个运费模板下商品的件数/重量/体积以及总金额 按照首重倒序排列
                // freight=1 包邮 → 运费为0，跳过
                // freight=2 固定运费 → 已在前面计算过，跳过
                // freight=3 运费模板 → 需要计算
                // 注意：freight 字段已从 freight_config.express.type 映射而来
                $cityId = $addr['city_id'] ?? 0;
                $tempIds = [1];
                foreach ($cartInfo as $key_c => $item_c) {
                    if (isset($item_c['productInfo']['freight']) && $item_c['productInfo']['freight'] == 3) {
                        $tempIds[] = $item_c['productInfo']['temp_id'];
                    }
                }
                $tempIds = array_unique($tempIds);
                // 直接调用 app/ 层运费模板服务
                /** @var \app\services\shipping\ShippingTemplatesServices $shippingServices */
                $shippingServices = app()->make(\app\services\shipping\ShippingTemplatesServices::class);
                $temp = $shippingServices->getShippingColumn(['id' => $tempIds], 'type,appoint', 'id');
                /** @var \app\services\shipping\ShippingTemplatesRegionServices $regionServices */
                $regionServices = app()->make(\app\services\shipping\ShippingTemplatesRegionServices::class);
                $regions = $regionServices->getTempRegionList($tempIds, [$cityId, 0], 'temp_id,first,first_price,continue,continue_price', 'temp_id');
                $temp_num = [];
                foreach ($cartInfo as $cart) {
                    // freight=1 包邮 → 运费为0，跳过
                    // freight=2 固定运费 → 已在前面计算过，跳过
                    // freight=3 运费模板 → 计算运费
                    // 注意：freight 字段已从 freight_config.type 映射而来
                    if (isset($cart['productInfo']['freight']) && $cart['productInfo']['freight'] != 3) {
                        continue;
                    }
                    $tempId = $cart['productInfo']['temp_id'] ?? 1;
                    $type = $temp[$tempId]['type'] ?? $temp[1]['type'];
                    if ($type == 1) {
                        $num = $cart['cart_num'];
                    } elseif ($type == 2) {
                        $num = $cart['cart_num'] * $cart['productInfo']['attrInfo']['weight'];
                    } else {
                        $num = $cart['cart_num'] * $cart['productInfo']['attrInfo']['volume'];
                    }
                    $region = $regions[$tempId] ?? ($regions[1] ?? []);
                    if (!$region) continue;
                    if (!isset($temp_num[$tempId])) {
                        $temp_num[$tempId] = [
                            'number' => $num,
                            'type' => $type,
                            'price' => bcmul($cart['cart_num'], $cart['truePrice'], 2),
                            'first' => $region['first'],
                            'first_price' => $region['first_price'],
                            'continue' => $region['continue'],
                            'continue_price' => $region['continue_price'],
                            'temp_id' => $tempId
                        ];
                    } else {
                        $temp_num[$tempId]['number'] += $num;
                        $temp_num[$tempId]['price'] += bcmul($cart['cart_num'], $cart['truePrice'], 2);
                    }
                }
                // 直接调用 app/ 层运费模板包邮服务
                /** @var \app\services\shipping\ShippingTemplatesFreeServices $freeServices */
                $freeServices = app()->make(\app\services\shipping\ShippingTemplatesFreeServices::class);
                $freeList = $freeServices->isFreeList($tempIds, $addr['city_id'], 0, 'temp_id,number,price', 'temp_id');
                if ($freeList) {
                    foreach ($temp_num as $k => $v) {
                        if (isset($temp[$v['temp_id']]['appoint']) && $temp[$v['temp_id']]['appoint'] && isset($freeList[$v['temp_id']])) {
                            $free = $freeList[$v['temp_id']];
                            $condition = $free['number'] <= $v['number'];
                            if ($free['price'] <= $v['price'] && $condition) {
                                unset($temp_num[$k]);
                            }
                        }
                    }
                }
                //首件运费最大值
                $maxFirstPrice = $temp_num ? max(array_column($temp_num, 'first_price')) : 0;
                //初始运费为0
                $storePostage_arr = [];

                $i = 0;
                //循环运费数组
                foreach ($temp_num as $fk => $fv) {
                    //找到首件运费等于最大值
                    if ($fv['first_price'] == $maxFirstPrice) {
                        //每次循环设置初始值
                        $tempArr = $temp_num;
                        $Postage = 0;
                        //计算首件运费
                        if ($fv['number'] <= $fv['first']) {
                            $Postage = bcadd($Postage, $fv['first_price'], 2);
                        } else {
                            if ($fv['continue'] <= 0) {
                                $Postage = $Postage;
                            } else {
                                $Postage = bcadd(bcadd($Postage, $fv['first_price'], 2), bcmul(ceil(bcdiv(bcsub($fv['number'], $fv['first'], 2), $fv['continue'] ?? 0, 2)), $fv['continue_price'], 4), 2);
                            }
                        }
                        $postageArr[$i]['data'][$fk] = $Postage;

                        //删除计算过的首件数据
                        unset($tempArr[$fk]);
                        //循环计算剩余运费
                        foreach ($tempArr as $ck => $cv) {
                            if ($cv['continue'] <= 0) {
                                $Postage = $Postage;
                            } else {
                                $one_postage = bcmul(ceil(bcdiv($cv['number'], $cv['continue'] ?? 0, 2)), $cv['continue_price'], 2);
                                $Postage = bcadd($Postage, $one_postage, 2);
                                $postageArr[$i]['data'][$ck] = $one_postage;
                            }
                        }
                        $postageArr[$i]['sum'] = $Postage;
                        $storePostage_arr[] = $Postage;
                    }
                }
                if (count($storePostage_arr)) {
                    $maxStorePostage = max($storePostage_arr);
                    //获取运费计算中的最大值
                    $storePostage = bcadd((string)$storePostage, (string)$maxStorePostage, 2);
                }
            }
        }
        //会员邮费享受折扣
        if ($storePostage) {
            $express_rule_number = 100;
            if (!$userInfo) {
                /** @var UserServices $userService */
                $userService = app()->make(UserServices::class);
                $userInfo = $userService->getUserInfo($addr['uid']);
            }
            if ($userInfo && isset($userInfo['is_money_level']) && $userInfo['is_money_level'] > 0) {
                //看是否开启会员折扣奖励
                /** @var MemberCardServices $memberCardService */
                $memberCardService = app()->make(MemberCardServices::class);
                $express_rule_number = $memberCardService->isOpenMemberCard('express');
                $express_rule_number = $express_rule_number <= 0 ? 0 : $express_rule_number;
            }
            $discountRate = bcdiv($express_rule_number, 100, 4);
            $truePostageArr = [];
            foreach ($postageArr as $postitem) {
                if ($postitem['sum'] == ($maxStorePostage ?? 0)) {
                    $truePostageArr = $postitem['data'];
                    break;
                }
            }
            $cartAlready = [];
            foreach ($cartInfo as &$item) {
                // freight=1 包邮, freight=2 固定运费 → 跳过运费模板计算
                // freight=3 运费模板 → 需要计算
                if (isset($item['productInfo']['freight']) && in_array($item['productInfo']['freight'], [1, 2])) {
                    if ($item['productInfo']['freight'] == 2) {
                        // 固定运费应用折扣
                        $item['postage_price'] = sprintf("%.2f", bcmul($item['postage_price'], $discountRate, 6));
                    }
                    continue;
                }
                $tempId = $item['productInfo']['temp_id'] ?? 0;
                $tempPostage = $truePostageArr[$tempId] ?? 0;
                $tempNumber = $temp_num[$tempId]['number'] ?? 0;
                if (!$tempId || !$tempPostage) continue;
                $type = $temp_num[$tempId]['type'];


                if ($type == 1) {
                    $num = $item['cart_num'];
                } elseif ($type == 2) {
                    $num = $item['cart_num'] * $item['productInfo']['attrInfo']['weight'];
                } else {
                    $num = $item['cart_num'] * $item['productInfo']['attrInfo']['volume'];
                }
                if ((($cartAlready[$tempId]['number'] ?? 0) + $num) >= $tempNumber) {
                    $price = isset($cartAlready[$tempId]['price']) ? bcsub((string)$tempPostage, (string)$cartAlready[$tempId]['price'], 6) : $tempPostage;
                } else {
                    $price = bcmul((string)$tempPostage, bcdiv((string)$num, (string)$tempNumber, 6), 6);
                }
                $cartAlready[$tempId]['number'] = bcadd((string)($cartAlready[$tempId]['number'] ?? 0), (string)$num, 4);
                $cartAlready[$tempId]['price'] = bcadd((string)($cartAlready[$tempId]['price'] ?? 0.00), (string)$price, 4);

                if ($express_rule_number && $express_rule_number < 100) {
                    $price = bcmul($price, $discountRate, 4);
                }
                $item['postage_price'] = sprintf("%.2f", $price);
            }
            if ($express_rule_number && $express_rule_number < 100) {
                $storePostageDiscount = $storePostage;
                $storePostage = bcmul($storePostage, bcdiv($express_rule_number, 100, 4), 2);
                $storePostageDiscount = bcsub($storePostageDiscount, $storePostage, 2);
            } else {
                $storePostageDiscount = 0;
                $storePostage = $storePostage;
            }
        }
        return compact('storePostage', 'storeFreePostage', 'isStoreFreePostage', 'sumPrice', 'totalPrice', 'costPrice', 'vipPrice', 'storePostageDiscount', 'cartInfo', 'levelPrice', 'memberPrice', 'giftPrice');
    }

    /**
     * 获取某个字段总金额
     * @param $cartInfo
     * @param string $key
     * @param bool $is_unit
     * @return int|string
     */
    public function getOrderSumPrice($cartInfo, $key = 'truePrice', $is_unit = true)
    {
        $SumPrice = 0;
        foreach ($cartInfo as $cart) {
            if (isset($cart['cart_info'])) $cart = $cart['cart_info'];
            if ($is_unit) {
                if ($key == 'level' || $key == 'member') {
                    if ($cart['price_type'] == $key) {
                        $SumPrice = bcadd($SumPrice, bcmul($cart['cart_num'], $cart['vip_truePrice'], 2), 2);
                    }
                } else {
                    $SumPrice = bcadd($SumPrice, bcmul($cart['cart_num'], $cart[$key], 2), 2);
                }
            } else {
                $SumPrice = bcadd($SumPrice, $cart[$key], 2);
            }
        }
        return $SumPrice;
    }
}
