<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\services\order;


use app\services\coupon\StoreCouponUserServices;
use app\services\pay\PayServices;
use app\services\product\product\StoreCategoryServices;
use app\services\wechat\WechatUserServices;
use app\jobs\OrderCreateAfterJob;
use app\jobs\ProductLogJob;
use crmeb\utils\Arr;
use app\services\BaseServices;
use app\jobs\UnpaidOrderSend;
use crmeb\services\CacheService;
use app\dao\order\StoreOrderDao;
use app\services\user\UserServices;
use app\jobs\UnpaidOrderCancelJob;
use think\exception\ValidateException;
use crmeb\services\SystemConfigService;
use app\services\user\UserBillServices;
use app\services\user\UserAddressServices;
use app\services\activity\StoreBargainServices;
use app\services\activity\StoreSeckillServices;
use app\services\system\store\SystemStoreServices;
use app\services\activity\StoreCombinationServices;
use app\services\product\product\StoreProductServices;

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
        //32位
        if (PHP_INT_SIZE == 4) {
            $id = abs($snowflake->id());
        } else {
            $id = $snowflake->setStartTimeStamp(strtotime('2020-06-05') * 1000)->id();
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
    public function createOrder($uid, $key, $cartGroup, $userInfo, $addressId, $payType, $useIntegral = false, $couponId = 0, $mark = '', $combinationId = 0, $pinkId = 0, $seckillId = 0, $bargainId = 0, $isChannel = 0, $shippingType = 1, $real_name = '', $phone = '', $storeId = 0, $news = false)
    {
        /** @var StoreOrderComputedServices $computedServices */
        $computedServices = app()->make(StoreOrderComputedServices::class);
        $priceData = $computedServices->computedOrder($uid, $key, $cartGroup, $addressId, $payType, $useIntegral, $couponId, true, $shippingType);
        /** @var WechatUserServices $wechatServices */
        $wechatServices = app()->make(WechatUserServices::class);
        /** @var UserAddressServices $addressServices */
        $addressServices = app()->make(UserAddressServices::class);
        if ($shippingType === 1) {
            if (!$addressId) {
                throw new ValidateException('请选择收货地址!');
            }
            if (!$addressInfo = $addressServices->getOne(['uid' => $uid, 'id' => $addressId, 'is_del' => 0]))
                throw new ValidateException('地址选择有误!');
            $addressInfo = $addressInfo->toArray();
        } else {
            if ((!$real_name || !$phone)) {
                throw new ValidateException('请填写姓名和电话');
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
            $cartInfoGainIntegral = isset($cart['productInfo']['give_integral']) ? bcmul((string)$cart['cart_num'], (string)$cart['productInfo']['give_integral'], 0) : 0;
            $gainIntegral = bcadd((string)$gainIntegral, (string)$cartInfoGainIntegral, 0);
        }
        $deduction = $seckillId || $bargainId || $combinationId;
        if ($deduction) {
            $couponId = 0;
            $useIntegral = false;
            $systemPayType = PayServices::PAY_TYPE;
            unset($systemPayType['offline']);
            if ($payType != 'pc' && !array_key_exists($payType, $systemPayType)) {
                throw new ValidateException('营销商品不能使用线下支付!');
            }
        }
        //$shipping_type = 1 快递发货 $shipping_type = 2 门店自提
        $storeSelfMention = sys_config('store_self_mention') ?? 0;
        if (!$storeSelfMention) $shippingType = 1;

        $orderInfo = [
            'uid' => $uid,
            'order_id' => $this->getNewOrderId(),
            'real_name' => $addressInfo['real_name'],
            'user_phone' => $addressInfo['phone'],
            'user_address' => $addressInfo['province'] . ' ' . $addressInfo['city'] . ' ' . $addressInfo['district'] . ' ' . $addressInfo['detail'],
            'cart_id' => $cartIds,
            'total_num' => $totalNum,
            'total_price' => $priceGroup['totalPrice'],
            'total_postage' => $priceGroup['storePostage'],
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
            'cost' => $priceGroup['costPrice'],
            'is_channel' => $isChannel,
            'add_time' => time(),
            'unique' => $key,
            'shipping_type' => $shippingType,
            'channel_type' => $userInfo['user_type'],
            'province' => $userInfo['user_type'] == 'wecaht' || $userInfo['user_type'] == 'routine' ? $wechatServices->value(['uid' => $uid, 'user_type' => $userInfo['user_type']], 'province') : ''
        ];
        if ($shippingType === 2) {
            $orderInfo['verify_code'] = $this->getStoreCode();
            /** @var SystemStoreServices $storeServices */
            $storeServices = app()->make(SystemStoreServices::class);
            $orderInfo['store_id'] = $storeServices->getStoreDispose($storeId, 'id');
            if (!$orderInfo['store_id']) {
                throw new ValidateException('暂无门店无法选择门店自提');
            }
        }
        /** @var StoreOrderCartInfoServices $cartServices */
        $cartServices = app()->make(StoreOrderCartInfoServices::class);
        /** @var StoreSeckillServices $seckillServices */
        $seckillServices = app()->make(StoreSeckillServices::class);
        $priceData['coupon_id'] = $couponId;
        $order = $this->transaction(function () use ($cartIds, $orderInfo, $cartInfo, $key, $userInfo, $useIntegral, $priceData, $combinationId, $seckillId, $bargainId, $cartServices, $seckillServices, $uid) {
            //创建订单
            $order = $this->dao->save($orderInfo);
            if (!$order) {
                throw new ValidateException('订单生成失败!');
            }
            //记录自提人电话和姓名
            /** @var UserServices $userService */
            $userService = app()->make(UserServices::class);
            $userService->update(['uid' => $uid], ['real_name' => $orderInfo['real_name'], 'record_phone' => $orderInfo['user_phone']]);
            //占用库存
            $seckillServices->occupySeckillStock($cartInfo, $key);
            //积分抵扣
            $this->deductIntegral($userInfo, $useIntegral, $priceData, (int)$userInfo['uid'], $key);
            //扣库存
            $this->decGoodsStock($cartInfo, $combinationId, $seckillId, $bargainId);
            //保存购物车商品信息
            $cartServices->setCartInfo($order['id'], $this->computeOrderProductTruePrice($cartInfo, $priceData));
            return $order;
        });
        //订单创建成功后置事件
        event('order.orderCreateAfter', [$order, compact('cartInfo', 'addressId', 'cartIds', 'news'), $uid, $key, $combinationId, $seckillId, $bargainId]);
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
    public function deductIntegral(array $userInfo, bool $useIntegral, array $priceData, int $uid, string $key)
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
            ], $userInfo['integral'], $key);

            $res2 = $res2 && false != $res3;
        }
        if (!$res2) {
            throw new ValidateException('使用积分抵扣失败!');
        }
    }

    /**
     * 扣库存
     * @param array $cartInfo
     * @param int $combinationId
     * @param int $seckillId
     * @param int $bargainId
     */
    public function decGoodsStock(array $cartInfo, int $combinationId, int $seckillId, int $bargainId)
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
        foreach ($cartInfo as $cart) {
            //减库存加销量
            if ($combinationId) $res5 = $res5 && $pinkServices->decCombinationStock((int)$cart['cart_num'], $combinationId, isset($cart['productInfo']['attrInfo']) ? $cart['productInfo']['attrInfo']['unique'] : '');
            else if ($seckillId) $res5 = $res5 && $seckillServices->decSeckillStock((int)$cart['cart_num'], $seckillId, isset($cart['productInfo']['attrInfo']) ? $cart['productInfo']['attrInfo']['unique'] : '');
            else if ($bargainId) $res5 = $res5 && $bargainServices->decBargainStock((int)$cart['cart_num'], $bargainId, isset($cart['productInfo']['attrInfo']) ? $cart['productInfo']['attrInfo']['unique'] : '');
            else $res5 = $res5 && $services->decProductStock((int)$cart['cart_num'], (int)$cart['productInfo']['id'], isset($cart['productInfo']['attrInfo']) ? $cart['productInfo']['attrInfo']['unique'] : '');
        }
        if (!$res5) {
            throw new ValidateException('扣库存失败!');
        }
    }

    /**
     * 订单创建后的后置事件
     * @param UserAddressServices $addressServices
     * @param $order
     * @param array $group
     */
    public function orderCreateAfter($order, array $group)
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
                CacheService::redisHandler()->delete($key);
            }, $group['cartIds']);
        } else {
            /** @var StoreCartServices $cartServices */
            $cartServices = app()->make(StoreCartServices::class);
            $cartServices->deleteCartStatus($group['cartIds']);
        }
    }

    /**
     * 计算订单每个商品真实付款价格
     * @param array $cartInfo
     * @param array $priceData
     * @return array
     */
    public function computeOrderProductTruePrice(array $cartInfo, array $priceData)
    {
        $cartInfo = $this->computeOrderProductCoupon($cartInfo, $priceData);
        $cartInfo = $this->computeOrderProductIntegral($cartInfo, $priceData);
        foreach ($cartInfo as &$cart) {
            $coupon_price = $cart['coupon_price'] ?? 0;
            $integral_price = $cart['integral_price'] ?? 0;
            if ($coupon_price) {
                $cart['truePrice'] = $cart['truePrice'] > $coupon_price ? bcsub((string)$cart['truePrice'], (string)$coupon_price, 2) : 0;
            }
            if ($integral_price) {
                $cart['truePrice'] = $cart['truePrice'] > $integral_price ? bcsub((string)$cart['truePrice'], (string)$integral_price, 2) : 0;
            }
        }
        return $cartInfo;
    }

    /**
     * 计算订单商品积分实际抵扣金额
     * @param array $cartInfo
     * @param array $priceData
     */
    public function computeOrderProductIntegral(array $cartInfo, array $priceData)
    {
        $total_price = 0.00;
        $compute_price = 0.00;
        $integral_price = 0.00;
        $count = 0;
        $where = $priceData['deduction_price'] ? true : false;
        if ($where) {
            foreach ($cartInfo as $cart) {
                $total_price += bcmul((string)$cart['truePrice'], (string)$cart['cart_num'], 2);
                $count++;
            }
        }

        foreach ($cartInfo as &$cart) {
            if ($where) {
                if ($count > 1) {
                    $integral_price = bcmul((string)bcdiv((string)$cart['truePrice'], (string)$total_price, 4), (string)$priceData['deduction_price'], 2);
                    $compute_price += bcmul((string)$integral_price, (string)$cart['cart_num'], 2);
                } else {
                    $integral_price = bcdiv((string)bcsub((string)$priceData['deduction_price'], $compute_price, 2), $cart['cart_num'], 2);
                }
                $count--;
            }
            $cart['integral_price'] = $integral_price;
        }
        return $cartInfo;
    }

    /**
     * 计算订单商品优惠券实际抵扣金额
     * @param array $cartInfo
     * @param array $priceData
     */
    public function computeOrderProductCoupon(array $cartInfo, array $priceData)
    {
        $count = 0;
        $total_price = 0.00;
        $compute_price = 0.00;
        $coupon_price = 0.00;
        $where = false;
        if ($priceData['coupon_id'] && $priceData['coupon_price']) {
            /** @var StoreCouponUserServices $couponServices */
            $couponServices = app()->make(StoreCouponUserServices::class);
            $couponInfo = $couponServices->getOne(['id' => $priceData['coupon_id']], '*', ['issue']);
            if ($couponInfo) {
                $type = $couponInfo['applicable_type'] ?? 0;
                switch ($type) {
                    case 0:
                    case 3:
                        foreach ($cartInfo as $cart) {
                            $total_price += bcmul((string)$cart['truePrice'], (string)$cart['cart_num'], 2);
                            $count++;
                        }
                        foreach ($cartInfo as &$cart) {
                            if ($count > 1) {
                                $coupon_price = bcmul((string)bcdiv((string)$cart['truePrice'], (string)$total_price, 4), (string)$couponInfo['coupon_price'], 2);
                                $compute_price += bcmul((string)$coupon_price, (string)$cart['cart_num'], 2);
                            } else {
                                $coupon_price = bcdiv((string)bcsub((string)$couponInfo['coupon_price'], $compute_price, 2), $cart['cart_num'], 2);
                            }
                            $count--;
                            $cart['coupon_price'] = $coupon_price;
                        }
                        break;
                    case 1://品类券
                        /** @var StoreCategoryServices $storeCategoryServices */
                        $storeCategoryServices = app()->make(StoreCategoryServices::class);
                        $cateGorys = $storeCategoryServices->getAllById((int)$couponInfo['category_id']);
                        if ($cateGorys) {
                            $cateIds = array_column($cateGorys, 'id');
                            foreach ($cartInfo as $cart) {
                                if (isset($cart['productInfo']['cate_id']) && array_intersect(explode(',', $cart['productInfo']['cate_id']), $cateIds)) {
                                    $total_price += bcmul((string)$cart['truePrice'], (string)$cart['cart_num'], 2);
                                    $count++;
                                }
                            }
                            foreach ($cartInfo as &$cart) {
                                if (isset($cart['productInfo']['cate_id']) && array_intersect(explode(',', $cart['productInfo']['cate_id']), $cateIds)) {
                                    if ($count > 1) {
                                        $coupon_price = bcmul((string)bcdiv((string)$cart['truePrice'], (string)$total_price, 4), (string)$couponInfo['coupon_price'], 2);
                                        $compute_price += bcmul((string)$coupon_price, (string)$cart['cart_num'], 2);
                                    } else {
                                        $coupon_price = bcdiv((string)bcsub((string)$couponInfo['coupon_price'], $compute_price, 2), $cart['cart_num'], 2);
                                    }
                                    $count--;
                                } else {
                                    $coupon_price = 0;
                                }
                                $cart['coupon_price'] = $coupon_price;
                            }
                        }
                        break;
                    case 2:
                        foreach ($cartInfo as $cart) {
                            if (isset($cart['product_id']) && in_array($cart['product_id'], explode(',', $couponInfo['product_id']))) {
                                $total_price += bcmul((string)$cart['truePrice'], (string)$cart['cart_num'], 2);
                                $count++;
                            }
                        }
                        foreach ($cartInfo as &$cart) {
                            if (isset($cart['product_id']) && in_array($cart['product_id'], explode(',', $couponInfo['product_id']))) {
                                if ($count > 1) {
                                    $coupon_price = bcmul((string)bcdiv((string)$cart['truePrice'], (string)$total_price, 4), (string)$couponInfo['coupon_price'], 2);
                                    $compute_price += bcmul((string)$coupon_price, (string)$cart['cart_num'], 2);
                                } else {
                                    $coupon_price = bcdiv((string)bcsub((string)$couponInfo['coupon_price'], $compute_price, 2), $cart['cart_num'], 2);
                                }
                                $count--;
                            } else {
                                $coupon_price = 0;
                            }
                            $cart['coupon_price'] = $coupon_price;
                        }
                        break;
                }
            }
        }
        return $cartInfo;
    }

}
