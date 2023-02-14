<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\services\order;

use app\services\BaseServices;
use app\dao\order\StoreOrderDao;
use app\services\pay\PayServices;
use app\services\product\product\StoreCategoryServices;
use app\services\user\member\MemberCardServices;
use app\services\user\UserBillServices;
use app\services\user\UserServices;
use crmeb\exceptions\ApiException;
use app\services\user\UserAddressServices;
use app\services\activity\coupon\StoreCouponUserServices;
use app\services\shipping\ShippingTemplatesFreeServices;
use app\services\shipping\ShippingTemplatesRegionServices;
use app\services\shipping\ShippingTemplatesServices;

/**
 * 订单计算金额
 * Class StoreOrderComputedServices
 * @package app\services\order
 */
class StoreOrderComputedServices extends BaseServices
{
    /**
     * 支付类型
     * @var string[]
     */
    public $payType = ['weixin' => '微信支付', 'yue' => '余额支付', 'offline' => '线下支付', 'pc' => 'pc'];

    /**
     * 额外参数
     * @var array
     */
    protected $paramData = [];

    /**
     * StoreOrderComputedServices constructor.
     * @param StoreOrderDao $dao
     */
    public function __construct(StoreOrderDao $dao)
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
    public function computedOrder(int $uid, array $userInfo = [], array $cartGroup, int $addressId, string $payType, bool $useIntegral = false, int $couponId = 0, bool $isCreate = false, int $shippingType = 1)
    {
        $offlinePayStatus = (int)sys_config('offline_pay_status') ?? (int)2;
        $systemPayType = PayServices::PAY_TYPE;
        if ($offlinePayStatus == 2) unset($systemPayType['offline']);
        if (strtolower($payType) != 'pc' && strtolower($payType) != 'friend') {
            if (!array_key_exists($payType, $systemPayType)) {
                throw new ApiException(410241);
            }
        }
        if (!$userInfo) {
            /** @var UserServices $userServices */
            $userServices = app()->make(UserServices::class);
            $userInfo = $userServices->getUserInfo($uid);
            if (!$userInfo) {
                throw new ApiException(410032);
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
        if (!$isActivity) {
            //使用优惠劵
            [$payPrice, $couponPrice] = $this->useCouponId($couponId, $uid, $cartInfo, $payPrice, $isCreate);
            //使用积分
            [$payPrice, $deductionPrice, $usedIntegral, $SurplusIntegral] = $this->useIntegral($useIntegral, $userInfo, $payPrice, $other);
        }

        //计算邮费
        [$payPrice, $payPostage, $storePostageDiscount, $storeFreePostage, $isStoreFreePostage] = $this->computedPayPostage($shippingType, $payType, $cartInfo, $addr, $payPrice, $postage, $other, $userInfo);

        $result = [
            'total_price' => $priceGroup['totalPrice'],
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
                throw new ApiException(410242);
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
                throw new ApiException(410243);
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
            throw new ApiException(410244);
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

        $SurplusIntegral = 0;
        if ($useIntegral && $userInfo['integral'] > 0) {
            //积分抵扣上限
            $integralMaxNum = sys_config('integral_max_num', 200);
            if ($integralMaxNum > 0 && $usable > $integralMaxNum) {
                $integral = $integralMaxNum;
            } else {
                $integral = $usable;
            }
            $deductionPrice = (float)bcmul((string)$integral, (string)$other['integralRatio'], 2);
            if ($deductionPrice < $payPrice) {
                $payPrice = bcsub((string)$payPrice, (string)$deductionPrice, 2);
                $usedIntegral = $integral;
            } else {
                $deductionPrice = $payPrice;
                $usedIntegral = (int)ceil(bcdiv((string)$payPrice, (string)$other['integralRatio'], 2));
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
    public function computedPayPostage(int $shipping_type, string $payType, array $cartInfo, array $addr, string $payPrice, array $postage = [], array $other, $userInfo = [])
    {
        $storePostageDiscount = 0;
        $storeFreePostage = $postage['storeFreePostage'] ?? 0;
        $isStoreFreePostage = false;
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
    public function getOrderPriceGroup($storeFreePostage, $cartInfo, $addr, $userInfo = [])
    {
        $sumPrice = $totalPrice = $costPrice = $vipPrice = 0;
        $storePostage = 0;
        $storePostageDiscount = 0;
        $isStoreFreePostage = false;//是否满额包邮
        $sumPrice = $this->getOrderSumPrice($cartInfo, 'sum_price');//获取订单原总金额
        $totalPrice = $this->getOrderSumPrice($cartInfo, 'truePrice');//获取订单svip、用户等级优惠之后总金额
        $costPrice = $this->getOrderSumPrice($cartInfo, 'costPrice');//获取订单成本价
        $vipPrice = $this->getOrderSumPrice($cartInfo, 'vip_truePrice');//获取订单等级和付费会员总优惠金额
        $levelPrice = $this->getOrderSumPrice($cartInfo, 'level');//获取会员等级优惠
        $memberPrice = $this->getOrderSumPrice($cartInfo, 'member');//获取付费会员优惠

        // 判断商品包邮和固定运费
        foreach ($cartInfo as $key => &$item) {
            $item['postage_price'] = 0;
            if ($item['productInfo']['freight'] == 1) {
                $item['postage_price'] = 0;
            } elseif ($item['productInfo']['freight'] == 2) {
                $item['postage_price'] = bcmul((string)$item['productInfo']['postage'], (string)$item['cart_num'], 2);
                $item['origin_postage_price'] = bcmul((string)$item['productInfo']['postage'], (string)$item['cart_num'], 2);
                $storePostage = bcadd((string)$storePostage, (string)$item['postage_price'], 2);
            }
        }
        $postageArr = [];
        if (isset($cartInfo[0]['productInfo']['is_virtual']) && $cartInfo[0]['productInfo']['is_virtual'] == 1) {
            $storePostage = 0;
        } elseif ($storeFreePostage && $cartInfo && $addr) {
            if ($sumPrice >= $storeFreePostage) {//如果总价大于等于满额包邮 邮费等于0
                $isStoreFreePostage = true;
                $storePostage = 0;
            } else {
                //按照运费模板计算每个运费模板下商品的件数/重量/体积以及总金额 按照首重倒序排列
                $cityId = $addr['city_id'] ?? 0;
                $tempIds[] = 1;
                foreach ($cartInfo as $key_c => $item_c) {
                    if (isset($item_c['productInfo']['freight']) && $item_c['productInfo']['freight'] == 3) {
                        $tempIds[] = $item_c['productInfo']['temp_id'];
                    }
                }
                $tempIds = array_unique($tempIds);
                /** @var ShippingTemplatesServices $shippServices */
                $shippServices = app()->make(ShippingTemplatesServices::class);
                $temp = $shippServices->getShippingColumn(['id' => $tempIds], 'type,appoint', 'id');
                /** @var ShippingTemplatesRegionServices $regionServices */
                $regionServices = app()->make(ShippingTemplatesRegionServices::class);
                $regions = $regionServices->getTempRegionList($tempIds, [$cityId, 0], 'temp_id,first,first_price,continue,continue_price', 'temp_id');
                $temp_num = [];
                foreach ($cartInfo as $cart) {
                    if (isset($cart['productInfo']['freight']) && in_array($cart['productInfo']['freight'], [1, 2])) {
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
                /** @var ShippingTemplatesFreeServices $freeServices */
                $freeServices = app()->make(ShippingTemplatesFreeServices::class);
                $freeList = $freeServices->isFreeList($tempIds, $addr['city_id'], 0, 'temp_id,number,price', 'temp_id');
                if ($freeList) {
                    foreach ($temp_num as $k => $v) {
                        if (isset($temp[$v['temp_id']]['appoint']) && $temp[$v['temp_id']]['appoint'] && isset($freeList[$v['temp_id']])) {
                            $free = $freeList[$v['temp_id']];
                            $condition = $v['type'] == 1 ? $free['number'] <= $v['number'] : $free['number'] >= $v['number'];
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
            $express_rule_number = 0;
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
                if (isset($item['productInfo']['freight']) && in_array($item['productInfo']['freight'], [1, 2])) {
                    if ($item['productInfo']['freight'] == 2) {
                        $item['postage_price'] = sprintf("%.2f", bcmul($item['postage_price'], $discountRate, 6));
                    }
                    continue;
                }
                $tempId = $item['productInfo']['temp_id'] ?? 0;
                $tempPostage = $truePostageArr[$tempId] ?? 0;
                $tempNumber = $temp_num[$tempId]['number'] ?? 0;
                if (!$tempId || !$tempPostage || !$tempNumber) continue;
                $type = $temp_num[$tempId]['type'];
                $cartNumber = $item['cart_num'];
                if ((($cartAlready[$tempId]['number'] ?? 0) + $cartNumber) >= $tempNumber) {
                    $price = isset($cartAlready[$tempId]['price']) ? bcsub((string)$tempPostage, (string)$cartAlready[$tempId]['price'], 6) : $tempPostage;
                } else {
                    $price = bcmul((string)$tempPostage, bcdiv((string)$cartNumber, (string)$tempNumber, 6), 6);
                }
                $cartAlready[$tempId]['number'] = bcadd((string)($cartNumber[$tempId]['number'] ?? 0), (string)$cartNumber, 4);
                $cartAlready[$tempId]['price'] = bcadd((string)($cartNumber[$tempId]['price'] ?? 0.00), (string)$price, 4);

                if ($express_rule_number && $express_rule_number < 100) {
                    $price = bcmul($price, $discountRate, 4);
                }
                if ($type == 2) {
                    $price = bcmul($price, $item['productInfo']['attrInfo']['weight'], 6);
                } elseif ($type == 3) {
                    $price = bcmul($price, $item['productInfo']['attrInfo']['volume'], 6);
                }
                $price = sprintf("%.2f", $price);
                $item['postage_price'] = $price;
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
        return compact('storePostage', 'storeFreePostage', 'isStoreFreePostage', 'sumPrice', 'totalPrice', 'costPrice', 'vipPrice', 'storePostageDiscount', 'cartInfo', 'levelPrice', 'memberPrice');
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
