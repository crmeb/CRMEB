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


use app\services\activity\advance\StoreAdvanceServices;
use app\services\agent\AgentLevelServices;
use app\services\activity\coupon\StoreCouponUserServices;
use app\services\agent\DivisionServices;
use app\services\pay\PayServices;
use app\services\product\product\StoreCategoryServices;
use app\services\shipping\ShippingTemplatesFreeServices;
use app\services\shipping\ShippingTemplatesRegionServices;
use app\services\shipping\ShippingTemplatesServices;
use app\services\wechat\WechatUserServices;
use app\services\BaseServices;
use crmeb\exceptions\ApiException;
use crmeb\services\CacheService;
use app\dao\order\StoreOrderDao;
use app\services\user\UserServices;
use app\services\user\UserBillServices;
use app\services\user\UserAddressServices;
use app\services\activity\bargain\StoreBargainServices;
use app\services\activity\seckill\StoreSeckillServices;
use app\services\system\store\SystemStoreServices;
use app\services\activity\combination\StoreCombinationServices;
use app\services\product\product\StoreProductServices;
use think\facade\Cache;
use think\facade\Log;

/**
 * 订单创建
 * Class StoreOrderCreateServices
 * @package app\services\order
 */
class StoreOrderCreateServices extends BaseServices
{
    /**
     * StoreOrderCreateServices constructor.
     * @param StoreOrderDao $dao
     */
    public function __construct(StoreOrderDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 使用雪花算法生成订单ID
     * @return string
     * @throws \Exception
     */
    public function getNewOrderId(string $prefix = 'wx')
    {
        $snowflake = new \Godruoyi\Snowflake\Snowflake();
        $is_callable = function ($currentTime) {
            $redis = Cache::store('redis');
            $swooleSequenceResolver = new \Godruoyi\Snowflake\RedisSequenceResolver($redis->handler());
            return $swooleSequenceResolver->sequence($currentTime);
        };
        //32位
        if (PHP_INT_SIZE == 4) {
            $id = abs($snowflake->setSequenceResolver($is_callable)->id());
        } else {
            $id = $snowflake->setStartTimeStamp(strtotime('2020-06-05') * 1000)->setSequenceResolver($is_callable)->id();
        }
        return $prefix . $id;
    }

    /**
     * 核销订单生成核销码
     * @return false|string
     */
    public function getStoreCode()
    {
        list($msec, $sec) = explode(' ', microtime());
        $num = time() + mt_rand(10, 999999) . '' . substr($msec, 2, 3);//生成随机数
        if (strlen($num) < 12)
            $num = str_pad((string)$num, 12, 0, STR_PAD_RIGHT);
        else
            $num = substr($num, 0, 12);
        if ($this->dao->count(['verify_code' => $num])) {
            return $this->getStoreCode();
        }
        return $num;
    }

    /**
     * 创建订单
     * @param $uid
     * @param $key
     * @param $cartGroup
     * @param $userInfo
     * @param $addressId
     * @param $payType
     * @param bool $useIntegral
     * @param int $couponId
     * @param string $mark
     * @param int $combinationId
     * @param int $pinkId
     * @param int $seckillId
     * @param int $bargainId
     * @param int $isChannel
     * @param int $shippingType
     * @param string $real_name
     * @param string $phone
     * @param int $storeId
     * @param bool $news
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function createOrder($uid, $key, $cartGroup, $userInfo, $addressId, $payType, $useIntegral = false, $couponId = 0, $mark = '', $combinationId = 0, $pinkId = 0, $seckillId = 0, $bargainId = 0, $isChannel = 0, $shippingType = 1, $real_name = '', $phone = '', $storeId = 0, $news = false, $advanceId = 0, $virtual_type = 0, $customForm = [])
    {
        /** @var StoreOrderComputedServices $computedServices */
        $computedServices = app()->make(StoreOrderComputedServices::class);
        $priceData = $computedServices->computedOrder($uid, $userInfo, $cartGroup, $addressId, $payType, $useIntegral, $couponId, true, $shippingType);
        /** @var WechatUserServices $wechatServices */
        $wechatServices = app()->make(WechatUserServices::class);
        /** @var UserAddressServices $addressServices */
        $addressServices = app()->make(UserAddressServices::class);
        if ($shippingType == 1 && $virtual_type == 0) {
            if (!$addressId) {
                throw new ApiException(410045);
            }
            if (!$addressInfo = $addressServices->getOne(['uid' => $uid, 'id' => $addressId, 'is_del' => 0]))
                throw new ApiException(410046);
            $addressInfo = $addressInfo->toArray();
        } else {
            if ((!$real_name || !$phone) && $virtual_type == 0) {
                throw new ApiException(410245);
            }
            $addressInfo['real_name'] = $real_name;
            $addressInfo['phone'] = $phone;
            $addressInfo['province'] = '';
            $addressInfo['city'] = '';
            $addressInfo['district'] = '';
            $addressInfo['detail'] = '';
        }
        $cartInfo = $cartGroup['cartInfo'];
        $priceGroup = $cartGroup['priceGroup'];
        $cartIds = [];
        $totalNum = 0;
        $gainIntegral = 0;
        foreach ($cartInfo as $cart) {
            $cartIds[] = $cart['id'];
            $totalNum += $cart['cart_num'];
            if (!$seckillId) $seckillId = $cart['seckill_id'];
            if (!$bargainId) $bargainId = $cart['bargain_id'];
            if (!$combinationId) $combinationId = $cart['combination_id'];
            if (!$advanceId) $advanceId = $cart['advance_id'];
            $cartInfoGainIntegral = isset($cart['productInfo']['give_integral']) ? bcmul((string)$cart['cart_num'], (string)$cart['productInfo']['give_integral'], 0) : 0;
            $gainIntegral = bcadd((string)$gainIntegral, (string)$cartInfoGainIntegral, 0);
        }
        if (count($cartInfo) == 1 && isset($cartInfo[0]['productInfo']['presale']) && $cartInfo[0]['productInfo']['presale'] == 1) {
            $advance_id = $cartInfo[0]['product_id'];
        } else {
            $advance_id = 0;
        }
        $deduction = $seckillId || $bargainId || $combinationId;
        if ($deduction) {
            $couponId = 0;
            $useIntegral = false;
            $systemPayType = PayServices::PAY_TYPE;
            unset($systemPayType['offline']);
            if ($payType != 'pc' && !array_key_exists($payType, $systemPayType)) {
                throw new ApiException(410246);
            }
        }
        //$shipping_type = 1 快递发货 $shipping_type = 2 门店自提
        $storeSelfMention = sys_config('store_self_mention') ?? 0;
        if (!$storeSelfMention) $shippingType = 1;

        $orderInfo = [
            'uid' => $uid,
            'order_id' => $this->getNewOrderId('cp'),
            'real_name' => $addressInfo['real_name'],
            'user_phone' => $addressInfo['phone'],
            'user_address' => $addressInfo['province'] . ' ' . $addressInfo['city'] . ' ' . $addressInfo['district'] . ' ' . $addressInfo['detail'],
            'cart_id' => $cartIds,
            'total_num' => $totalNum,
            'total_price' => $priceGroup['totalPrice'],
            'total_postage' => $shippingType == 1 ? $priceGroup['storePostage'] : 0,
            'coupon_id' => $couponId,
            'coupon_price' => $priceData['coupon_price'],
            'pay_price' => $priceData['pay_price'],
            'pay_postage' => $priceData['pay_postage'],
            'deduction_price' => $priceData['deduction_price'],
            'paid' => 0,
            'pay_type' => $payType,
            'use_integral' => $priceData['usedIntegral'],
            'gain_integral' => $gainIntegral,
            'mark' => htmlspecialchars($mark),
            'combination_id' => $combinationId,
            'pink_id' => $pinkId,
            'seckill_id' => $seckillId,
            'bargain_id' => $bargainId,
            'advance_id' => $advance_id,
            'cost' => $priceGroup['costPrice'],
            'is_channel' => $isChannel,
            'add_time' => time(),
            'unique' => $key,
            'shipping_type' => $shippingType,
            'channel_type' => $userInfo['user_type'],
            'province' => strval($userInfo['user_type'] == 'wechat' || $userInfo['user_type'] == 'routine' ? $wechatServices->value(['uid' => $uid, 'user_type' => $userInfo['user_type']], 'province') : ''),
            'spread_uid' => 0,
            'spread_two_uid' => 0,
            'virtual_type' => $virtual_type,
            'pay_uid' => $uid,
            'custom_form' => json_encode($customForm),
            'division_id' => $userInfo['division_id'],
            'agent_id' => $userInfo['agent_id'],
            'staff_id' => $userInfo['staff_id'],
        ];

        if ($shippingType == 2) {
            $orderInfo['verify_code'] = $this->getStoreCode();
            /** @var SystemStoreServices $storeServices */
            $storeServices = app()->make(SystemStoreServices::class);
            $orderInfo['store_id'] = $storeServices->getStoreDispose($storeId, 'id');
            if (!$orderInfo['store_id']) {
                throw new ApiException(410247);
            }
        }
        /** @var StoreOrderCartInfoServices $cartServices */
        $cartServices = app()->make(StoreOrderCartInfoServices::class);
        /** @var StoreSeckillServices $seckillServices */
        $seckillServices = app()->make(StoreSeckillServices::class);
        $priceData['coupon_id'] = $couponId;
        $order = $this->transaction(function () use ($cartIds, $orderInfo, $cartInfo, $key, $userInfo, $useIntegral, $priceData, $combinationId, $seckillId, $bargainId, $cartServices, $seckillServices, $uid, $addressId, $advanceId) {
            //创建订单
            $order = $this->dao->save($orderInfo);
            if (!$order) {
                throw new ApiException(410200);
            }
            //记录自提人电话和姓名
            /** @var UserServices $userService */
            $userService = app()->make(UserServices::class);
            $userService->update(['uid' => $uid], ['real_name' => $orderInfo['real_name'], 'record_phone' => $orderInfo['user_phone']]);
            //占用库存
            $seckillServices->occupySeckillStock($cartInfo, $key);
            //积分抵扣
            if ($priceData['usedIntegral'] > 0) {
                $this->deductIntegral($userInfo, $useIntegral, $priceData, (int)$userInfo['uid'], $order['id']);
            }
            //扣库存
            $this->decGoodsStock($cartInfo, $combinationId, $seckillId, $bargainId, $advanceId);
            //保存购物车商品信息
            $cartServices->setCartInfo($order['id'], $uid, $cartInfo);
            return $order;
        });

        // 订单创建成功后置事件
        event('order.orderCreateAfter', [$order, compact('cartInfo', 'priceData', 'addressId', 'cartIds', 'news'), $uid, $key, $combinationId, $seckillId, $bargainId]);
        // 推送订单
        event('out.outPush', ['order_create_push', ['order_id' => (int)$order['id']]]);
        return $order;
    }


    /**
     * 抵扣积分
     * @param array $userInfo
     * @param bool $useIntegral
     * @param array $priceData
     * @param int $uid
     * @param string $key
     */
    public function deductIntegral(array $userInfo, bool $useIntegral, array $priceData, int $uid, $orderId)
    {
        $res2 = true;
        if ($useIntegral && $userInfo['integral'] > 0) {
            /** @var UserServices $userServices */
            $userServices = app()->make(UserServices::class);
            if (!$priceData['SurplusIntegral']) {
                $res2 = false !== $userServices->update($uid, ['integral' => 0]);
            } else {
                $res2 = false !== $userServices->bcDec($userInfo['uid'], 'integral', $priceData['usedIntegral'], 'uid');
            }
            /** @var UserBillServices $userBillServices */
            $userBillServices = app()->make(UserBillServices::class);
            $res3 = $userBillServices->income('deduction', $uid, [
                'number' => $priceData['usedIntegral'],
                'deductionPrice' => $priceData['deduction_price']
            ], $userInfo['integral'] - $priceData['usedIntegral'], $orderId);

            $res2 = $res2 && false != $res3;
        }
        if (!$res2) {
            throw new ApiException(410227);
        }
    }

    /**
     * 扣库存
     * @param array $cartInfo
     * @param int $combinationId
     * @param int $seckillId
     * @param int $bargainId
     */
    public function decGoodsStock(array $cartInfo, int $combinationId, int $seckillId, int $bargainId, int $advanceId)
    {
        $res5 = true;
        /** @var StoreProductServices $services */
        $services = app()->make(StoreProductServices::class);
        /** @var StoreSeckillServices $seckillServices */
        $seckillServices = app()->make(StoreSeckillServices::class);
        /** @var StoreCombinationServices $pinkServices */
        $pinkServices = app()->make(StoreCombinationServices::class);
        /** @var StoreBargainServices $bargainServices */
        $bargainServices = app()->make(StoreBargainServices::class);
        /** @var StoreAdvanceServices $advanceServices */
        $advanceServices = app()->make(StoreAdvanceServices::class);
        try {
            foreach ($cartInfo as $cart) {
                //减库存加销量
                if ($combinationId) $res5 = $res5 && $pinkServices->decCombinationStock((int)$cart['cart_num'], $combinationId, isset($cart['productInfo']['attrInfo']) ? $cart['productInfo']['attrInfo']['unique'] : '');
                else if ($seckillId) $res5 = $res5 && $seckillServices->decSeckillStock((int)$cart['cart_num'], $seckillId, isset($cart['productInfo']['attrInfo']) ? $cart['productInfo']['attrInfo']['unique'] : '');
                else if ($bargainId) $res5 = $res5 && $bargainServices->decBargainStock((int)$cart['cart_num'], $bargainId, isset($cart['productInfo']['attrInfo']) ? $cart['productInfo']['attrInfo']['unique'] : '');
                else if ($advanceId) $res5 = $res5 && $advanceServices->decAdvanceStock((int)$cart['cart_num'], $advanceId, isset($cart['productInfo']['attrInfo']) ? $cart['productInfo']['attrInfo']['unique'] : '');
                else $res5 = $res5 && $services->decProductStock((int)$cart['cart_num'], (int)$cart['productInfo']['id'], isset($cart['productInfo']['attrInfo']) ? $cart['productInfo']['attrInfo']['unique'] : '');
            }
            if (!$res5) {
                throw new ApiException(410238);
            }
        } catch (\Throwable $e) {
            throw new ApiException(410238);
        }
    }

    /**
     * 订单数据创建之后的商品实际金额计算，佣金计算，优惠折扣计算，设置默认地址，清理购物车
     * @param $order
     * @param array $group
     * @param $activity
     */
    public function orderCreateAfter($order, array $group, $activity)
    {
        /** @var UserAddressServices $addressServices */
        $addressServices = app()->make(UserAddressServices::class);
        //设置用户默认地址
        if (!$addressServices->be(['is_default' => 1, 'uid' => $order['uid']])) {
            $addressServices->setDefaultAddress($group['addressId'], $order['uid']);
        }
        //删除购物车
        if ($group['news']) {
            array_map(function ($key) {
                CacheService::delete($key);
            }, $group['cartIds']);
        } else {
            /** @var StoreCartServices $cartServices */
            $cartServices = app()->make(StoreCartServices::class);
            $cartServices->deleteCartStatus($group['cartIds']);
        }
        $uid = (int)$order['uid'];
        $orderId = (int)$order['id'];
        try {
            $cartInfo = $group['cartInfo'] ?? [];
            $priceData = $group['priceData'] ?? [];
            $addressId = $group['addressId'] ?? 0;
            $spread_ids = [];
            /** @var StoreOrderCreateServices $createService */
            $createService = app()->make(StoreOrderCreateServices::class);
            if ($cartInfo && $priceData) {
                /** @var StoreOrderCartInfoServices $cartServices */
                $cartServices = app()->make(StoreOrderCartInfoServices::class);
                [$cartInfo, $spread_ids] = $createService->computeOrderProductTruePrice($cartInfo, $priceData, $addressId, $uid, $order);
                $cartServices->updateCartInfo($orderId, $cartInfo);
            }

            $orderData = [];
            $spread_uid = $spread_two_uid = 0;
            /** @var UserServices $userServices */
            $userServices = app()->make(UserServices::class);
            if ($spread_ids) {
                [$spread_uid, $spread_two_uid] = $spread_ids;
                $orderData['spread_uid'] = $spread_uid;
                $orderData['spread_two_uid'] = $spread_two_uid;
            } else {
                $spread_uid = $userServices->getSpreadUid($uid);
                $orderData = ['spread_uid' => 0, 'spread_two_uid' => 0];
                if ($spread_uid) {
                    $orderData['spread_uid'] = $spread_uid;
                }
                if ($spread_uid > 0 && sys_config('brokerage_level') == 2) {
                    $spread_two_uid = $userServices->getSpreadUid($spread_uid, [], false);
                    if ($spread_two_uid) {
                        $orderData['spread_two_uid'] = $spread_two_uid;
                    }
                }
            }
            $isCommission = 0;
            if ($order['combination_id']) {
                //检测拼团是否参与返佣
                /** @var StoreCombinationServices $combinationServices */
                $combinationServices = app()->make(StoreCombinationServices::class);
                $isCommission = $combinationServices->value(['id' => $order['combination_id']], 'is_commission');
            }
            if ($cartInfo && (!$activity || $isCommission)) {
                /** @var StoreOrderComputedServices $orderComputed */
                $orderComputed = app()->make(StoreOrderComputedServices::class);
                if ($userServices->checkUserPromoter($spread_uid)) $orderData['one_brokerage'] = $orderComputed->getOrderSumPrice($cartInfo, 'one_brokerage', false);
                if ($userServices->checkUserPromoter($spread_two_uid)) $orderData['two_brokerage'] = $orderComputed->getOrderSumPrice($cartInfo, 'two_brokerage', false);
                $orderData['staff_brokerage'] = $orderComputed->getOrderSumPrice($cartInfo, 'staff_brokerage', false);
                $orderData['agent_brokerage'] = $orderComputed->getOrderSumPrice($cartInfo, 'agent_brokerage', false);
                $orderData['division_brokerage'] = $orderComputed->getOrderSumPrice($cartInfo, 'division_brokerage', false);
            }
            $createService->update(['id' => $orderId], $orderData);
        } catch (\Throwable $e) {
            throw new ApiException('计算订单实际优惠、积分、邮费、佣金失败，原因：' . $e->getMessage());
        }
    }

    /**
     * 计算订单每个商品真实付款价格
     * @param array $cartInfo
     * @param array $priceData
     * @param $addressId
     * @param int $uid
     * @return array
     */
    public function computeOrderProductTruePrice(array $cartInfo, array $priceData, $addressId, int $uid, $orderInfo)
    {
        //统一放入默认数据
        foreach ($cartInfo as &$cart) {
            $cart['use_integral'] = 0;
            $cart['integral_price'] = 0.00;
            $cart['coupon_price'] = 0.00;
        }
        try {
            [$cartInfo, $spread_ids] = $this->computeOrderProductBrokerage($uid, $cartInfo, $orderInfo);
            $cartInfo = $this->computeOrderProductCoupon($cartInfo, $priceData);
            $cartInfo = $this->computeOrderProductIntegral($cartInfo, $priceData);
//            $cartInfo = $this->computeOrderProductPostage($cartInfo, $priceData, $addressId);
        } catch (\Throwable $e) {
            Log::error('订单商品结算失败,File：' . $e->getFile() . ',Line：' . $e->getLine() . ',Message：' . $e->getMessage());
            throw new ApiException(410248);
        }
        //truePice实际支付单价（存在）
        //几件商品总体优惠 以及积分抵扣金额
        foreach ($cartInfo as &$cart) {
            $coupon_price = $cart['coupon_price'] ?? 0;
            $integral_price = $cart['integral_price'] ?? 0;
            $cart['sum_true_price'] = bcmul((string)$cart['truePrice'], (string)$cart['cart_num'], 2);
            if ($coupon_price) {
                $cart['sum_true_price'] = bcsub((string)$cart['sum_true_price'], (string)$coupon_price, 2);
                $uni_coupon_price = (string)bcdiv((string)$coupon_price, (string)$cart['cart_num'], 4);
                $cart['truePrice'] = $cart['truePrice'] > $uni_coupon_price ? bcsub((string)$cart['truePrice'], $uni_coupon_price, 2) : 0;
            }
            if ($integral_price) {
                $cart['sum_true_price'] = bcsub((string)$cart['sum_true_price'], (string)$integral_price, 2);
                $uni_integral_price = (string)bcdiv((string)$integral_price, (string)$cart['cart_num'], 4);
                $cart['truePrice'] = $cart['truePrice'] > $uni_integral_price ? bcsub((string)$cart['truePrice'], $uni_integral_price, 2) : 0;
            }
        }
        return [$cartInfo, $spread_ids];
    }

    /**
     * 计算每个商品实际支付运费
     * @param array $cartInfo
     * @param array $priceData
     * @return array
     */
    public function computeOrderProductPostage(array $cartInfo, array $priceData, $addressId)
    {
        $storePostage = $priceData['pay_postage'] ?? 0;
        if ($storePostage) {
            /** @var UserAddressServices $addressServices */
            $addressServices = app()->make(UserAddressServices::class);
            $addr = $addressServices->getAddress($addressId);
            if ($addr) {
                $addr = $addr->toArray();
                //按照运费模板计算每个运费模板下商品的件数/重量/体积以及总金额 按照首重倒序排列
                $cityId = $addr['city_id'] ?? 0;
                $tempIds[] = 1;
                foreach ($cartInfo as $key_c => $item_c) {
                    $tempIds[] = $item_c['productInfo']['temp_id'];
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
                    $tempId = $cart['productInfo']['temp_id'] ?? 1;
                    $type = $temp[$tempId]['type'] ?? $temp[1]['type'];
                    if ($type == 1) {
                        $num = $cart['cart_num'];
                    } elseif ($type == 2) {
                        $num = $cart['cart_num'] * $cart['productInfo']['attrInfo']['weight'];
                    } else {
                        $num = $cart['cart_num'] * $cart['productInfo']['attrInfo']['volume'];
                    }
                    $region = $regions[$tempId] ?? $regions[1];
                    if (!isset($temp_num[$cart['productInfo']['temp_id']])) {
                        $temp_num[$cart['productInfo']['temp_id']]['cart_id'][] = $cart['id'];
                        $temp_num[$cart['productInfo']['temp_id']]['number'] = $num;
                        $temp_num[$cart['productInfo']['temp_id']]['type'] = $type;
                        $temp_num[$cart['productInfo']['temp_id']]['price'] = bcmul($cart['cart_num'], $cart['truePrice'], 2);
                        $temp_num[$cart['productInfo']['temp_id']]['first'] = $region['first'];
                        $temp_num[$cart['productInfo']['temp_id']]['first_price'] = $region['first_price'];
                        $temp_num[$cart['productInfo']['temp_id']]['continue'] = $region['continue'];
                        $temp_num[$cart['productInfo']['temp_id']]['continue_price'] = $region['continue_price'];
                        $temp_num[$cart['productInfo']['temp_id']]['temp_id'] = $cart['productInfo']['temp_id'];
                        $temp_num[$cart['productInfo']['temp_id']]['city_id'] = $addr['city_id'];
                    } else {
                        $temp_num[$cart['productInfo']['temp_id']]['cart_id'][] = $cart['id'];
                        $temp_num[$cart['productInfo']['temp_id']]['number'] += $num;
                        $temp_num[$cart['productInfo']['temp_id']]['price'] += bcmul($cart['cart_num'], $cart['truePrice'], 2);
                    }
                }
                $cartInfo = array_combine(array_column($cartInfo, 'id'), $cartInfo);
                /** @var ShippingTemplatesFreeServices $freeServices */
                $freeServices = app()->make(ShippingTemplatesFreeServices::class);
                foreach ($temp_num as $k => $v) {
                    if (isset($temp[$v['temp_id']]['appoint']) && $temp[$v['temp_id']]['appoint']) {
                        if ($freeServices->isFree($v['temp_id'], $v['city_id'], $v['number'], $v['price'], $v['type'])) {
                            //免运费
                            foreach ($v['cart_id'] as $c_id) {
                                if (isset($cartInfo[$c_id])) $cartInfo[$c_id]['postage_price'] = 0.00;
                            }
                        }
                    }
                }
                $count = 0;
                $compute_price = 0.00;
                $total_price = 0;
                $postage_price = 0.00;
                foreach ($cartInfo as &$cart) {
                    if (isset($cart['postage_price'])) {//免运费
                        continue;
                    }
                    $total_price = bcadd((string)$total_price, (string)bcmul((string)$cart['truePrice'], (string)$cart['cart_num'], 4), 2);
                    $count++;
                }
                foreach ($cartInfo as &$cart) {
                    if (isset($cart['postage_price'])) {//免运费
                        continue;
                    }
                    if ($count > 1) {
                        $postage_price = bcmul((string)bcdiv((string)bcmul((string)$cart['cart_num'], (string)$cart['truePrice'], 4), (string)$total_price, 4), (string)$storePostage, 2);
                        $compute_price = bcadd((string)$compute_price, (string)$postage_price, 2);
                    } else {
                        $postage_price = bcsub((string)$storePostage, $compute_price, 2);
                    }
                    $cart['postage_price'] = $postage_price;
                    $count--;
                }
                $cartInfo = array_merge($cartInfo);
            }
        }
        //保证不进运费模版计算的购物车商品postage_price字段有值
        foreach ($cartInfo as &$item) {
            if (!isset($item['postage_price'])) $item['postage_price'] = 0.00;
        }
        return $cartInfo;
    }

    /**
     * 计算订单商品积分实际抵扣金额
     * @param array $cartInfo
     * @param array $priceData
     * @return array
     */
    public function computeOrderProductIntegral(array $cartInfo, array $priceData)
    {
        $usedIntegral = $priceData['usedIntegral'] ?? 0;
        $deduction_price = $priceData['deduction_price'] ?? 0;
        if ($deduction_price) {
            $count = 0;
            $total_price = 0.00;
            $compute_price = 0.00;
            $integral_price = 0.00;
            $use_integral = 0;
            $compute_integral = 0;
            foreach ($cartInfo as $cart) {
                $total_price = bcadd((string)$total_price, (string)bcmul((string)$cart['truePrice'], (string)$cart['cart_num'], 4), 2);
                $count++;
            }
            foreach ($cartInfo as &$cart) {
                if ($count > 1) {
                    $integral_price = bcmul((string)bcdiv((string)bcmul((string)$cart['cart_num'], (string)$cart['truePrice'], 4), (string)$total_price, 4), (string)$deduction_price, 2);
                    $compute_price = bcadd((string)$compute_price, (string)$integral_price, 2);
                    $use_integral = bcmul((string)bcdiv((string)bcmul((string)$cart['cart_num'], (string)$cart['truePrice'], 4), (string)$total_price, 4), (string)$usedIntegral, 0);
                    $compute_integral = bcadd((string)$compute_integral, $use_integral, 0);
                } else {
                    $integral_price = bcsub((string)$deduction_price, $compute_price, 2);
                    $use_integral = bcsub((string)$usedIntegral, $compute_integral, 0);
                }
                $count--;
                $cart['integral_price'] = $integral_price;
                $cart['use_integral'] = $use_integral;
            }
        }
        return $cartInfo;
    }

    /**
     * 计算订单商品优惠券实际抵扣金额
     * @param array $cartInfo
     * @param array $priceData
     * @return array
     */
    public function computeOrderProductCoupon(array $cartInfo, array $priceData)
    {
        if ($priceData['coupon_id'] && $priceData['coupon_price'] ?? 0) {
            $count = 0;
            $total_price = 0.00;
            $compute_price = 0.00;
            $coupon_price = 0.00;
            /** @var StoreCouponUserServices $couponServices */
            $couponServices = app()->make(StoreCouponUserServices::class);
            $couponInfo = $couponServices->getOne(['id' => $priceData['coupon_id']], '*', ['issue']);
            if ($couponInfo) {
                $type = $couponInfo['applicable_type'] ?? 0;
                $counpon_id = $couponInfo['id'];
                switch ($type) {
                    case 0:
                    case 3:
                        foreach ($cartInfo as $cart) {
                            $total_price = bcadd((string)$total_price, (string)bcmul((string)$cart['truePrice'], (string)$cart['cart_num'], 4), 2);
                            $count++;
                        }
                        foreach ($cartInfo as &$cart) {
                            if ($count > 1) {
                                $coupon_price = bcmul((string)bcdiv((string)bcmul((string)$cart['cart_num'], (string)$cart['truePrice'], 4), (string)$total_price, 4), (string)$couponInfo['coupon_price'], 2);
                                $compute_price = bcadd((string)$compute_price, (string)$coupon_price, 2);
                            } else {
                                $coupon_price = bcsub((string)$couponInfo['coupon_price'], $compute_price, 2);
                            }
                            $cart['coupon_price'] = $coupon_price;
                            $cart['coupon_id'] = $counpon_id;
                            $count--;
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
                                    $total_price = bcadd((string)$total_price, (string)bcmul((string)$cart['truePrice'], (string)$cart['cart_num'], 4), 2);
                                    $count++;
                                }
                            }
                            foreach ($cartInfo as &$cart) {
                                $cart['coupon_id'] = 0;
                                $cart['coupon_price'] = 0;
                                if (isset($cart['productInfo']['cate_id']) && array_intersect(explode(',', $cart['productInfo']['cate_id']), $cateIds)) {
                                    if ($count > 1) {
                                        $coupon_price = bcmul((string)bcdiv((string)bcmul((string)$cart['cart_num'], (string)$cart['truePrice'], 4), (string)$total_price, 4), (string)$couponInfo['coupon_price'], 2);
                                        $compute_price = bcadd((string)$compute_price, (string)$coupon_price, 2);
                                    } else {
                                        $coupon_price = bcsub((string)$couponInfo['coupon_price'], $compute_price, 2);
                                    }
                                    $cart['coupon_id'] = $counpon_id;
                                    $cart['coupon_price'] = $coupon_price;
                                    $count--;
                                }
                            }
                        }
                        break;
                    case 2://商品劵
                        foreach ($cartInfo as $cart) {
                            if (isset($cart['product_id']) && in_array($cart['product_id'], explode(',', $couponInfo['product_id']))) {
                                $total_price = bcadd((string)$total_price, (string)bcmul((string)$cart['truePrice'], (string)$cart['cart_num'], 4), 2);
                                $count++;
                            }
                        }
                        foreach ($cartInfo as &$cart) {
                            $cart['coupon_id'] = 0;
                            $cart['coupon_price'] = 0;
                            if (isset($cart['product_id']) && in_array($cart['product_id'], explode(',', $couponInfo['product_id']))) {
                                if ($count > 1) {
                                    $coupon_price = bcmul((string)bcdiv((string)bcmul((string)$cart['cart_num'], (string)$cart['truePrice'], 4), (string)$total_price, 4), (string)$couponInfo['coupon_price'], 2);
                                    $compute_price = bcadd((string)$compute_price, (string)$coupon_price, 2);
                                } else {
                                    $coupon_price = bcsub((string)$couponInfo['coupon_price'], $compute_price, 2);
                                }
                                $cart['coupon_id'] = $counpon_id;
                                $cart['coupon_price'] = $coupon_price;
                                $count--;
                            }
                        }
                        break;
                }
            }
        }
        return $cartInfo;
    }

    /**
     * 计算实际佣金
     * @param int $uid
     * @param array $cartInfo
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function computeOrderProductBrokerage(int $uid, array $cartInfo, $orderInfo)
    {
        /** @var AgentLevelServices $agentLevelServices */
        $agentLevelServices = app()->make(AgentLevelServices::class);
        [$one_brokerage_up, $two_brokerage_up, $spread_one_uid, $spread_two_uid] = $agentLevelServices->getAgentLevelBrokerage($uid);

        $BrokerageOne = sys_config('store_brokerage_ratio') != '' ? sys_config('store_brokerage_ratio') : 0;
        $BrokerageTwo = sys_config('store_brokerage_two') != '' ? sys_config('store_brokerage_two') : 0;
        $storeBrokerageRatio = $BrokerageOne + (($BrokerageOne * $one_brokerage_up) / 100);
        $storeBrokerageTwo = $BrokerageTwo + (($BrokerageTwo * $two_brokerage_up) / 100);
        if (sys_config('brokerage_level') == 1) {
            $storeBrokerageTwo = $spread_two_uid = 0;
        }

        /** @var DivisionServices $divisionService */
        $divisionService = app()->make(DivisionServices::class);
        [$storeBrokerageRatio, $storeBrokerageTwo, $staffPercent, $agentPercent, $divisionPercent] = $divisionService->getDivisionPercent($uid, $storeBrokerageRatio, $storeBrokerageTwo, sys_config('is_self_brokerage', 0));

        foreach ($cartInfo as &$cart) {
            $oneBrokerage = '0';//一级返佣金额
            $twoBrokerage = '0';//二级返佣金额
            $staffBrokerage = '0';//店员返佣金额
            $agentBrokerage = '0';//代理商返佣金额
            $divisionBrokerage = '0';//事业部返佣金额
            $cartNum = (string)$cart['cart_num'] ?? '0';
            if (isset($cart['productInfo'])) {
                $productInfo = $cart['productInfo'];

                //计算商品金额
                if (isset($productInfo['attrInfo'])) {
                    $price = bcmul((string)($productInfo['attrInfo']['price'] ?? '0'), $cartNum, 4);
                } else {
                    $price = bcmul((string)($productInfo['price'] ?? '0'), $cartNum, 4);
                }

                $staffBrokerage = bcmul((string)$price, (string)bcdiv($staffPercent, 100, 4), 2);
                $agentBrokerage = bcmul((string)$price, (string)bcdiv($agentPercent, 100, 4), 2);
                $divisionBrokerage = bcmul((string)$price, (string)bcdiv($divisionPercent, 100, 4), 2);

                //指定返佣金额
                if (isset($productInfo['is_sub']) && $productInfo['is_sub'] == 1) {
                    $oneBrokerage = $storeBrokerageRatio > 0 ? bcmul((string)($productInfo['attrInfo']['brokerage'] ?? '0'), $cartNum, 2) : 0;
                    $twoBrokerage = $storeBrokerageTwo > 0 ? bcmul((string)($productInfo['attrInfo']['brokerage_two'] ?? '0'), $cartNum, 2) : 0;
                } else {
                    if ($price) {
                        //一级返佣比例 小于等于零时直接返回 不返佣
                        if ($storeBrokerageRatio > 0) {
                            //计算获取一级返佣比例
                            $brokerageRatio = bcdiv($storeBrokerageRatio, 100, 4);
                            $oneBrokerage = bcmul((string)$price, (string)$brokerageRatio, 2);
                        }
                        //二级返佣比例小于等于0 直接返回
                        if ($storeBrokerageTwo > 0) {
                            //计算获取二级返佣比例
                            $brokerageTwo = bcdiv($storeBrokerageTwo, 100, 4);
                            $twoBrokerage = bcmul((string)$price, (string)$brokerageTwo, 2);
                        }
                    }
                }
            }

            $cart['one_brokerage'] = $oneBrokerage;
            $cart['two_brokerage'] = $twoBrokerage;
            $cart['staff_brokerage'] = $staffBrokerage;
            $cart['agent_brokerage'] = $agentBrokerage;
            $cart['division_brokerage'] = $divisionBrokerage;
        }
        return [$cartInfo, [$spread_one_uid, $spread_two_uid]];
    }
}
