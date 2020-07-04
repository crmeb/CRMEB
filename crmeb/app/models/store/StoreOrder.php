<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/20
 */

namespace app\models\store;

use app\admin\model\store\StoreProductAttrValue;
use app\admin\model\system\ShippingTemplatesFree;
use app\admin\model\system\ShippingTemplatesRegion;
use crmeb\basic\BaseModel;
use think\facade\Cache;
use crmeb\traits\ModelTrait;
use think\facade\Log;
use app\models\system\SystemStore;
use app\models\routine\RoutineTemplate;
use app\models\user\{
    User, UserAddress, UserBill, WechatUser
};
use crmeb\services\{
    SystemConfigService, WechatTemplateService, workerman\ChannelService
};
use crmeb\repositories\{
    GoodsRepository, PaymentRepositories, OrderRepository, ShortLetterRepositories, UserRepository
};
use app\admin\model\system\ShippingTemplates;

/**
 * TODO 订单Model
 * Class StoreOrder
 * @package app\models\store
 */
class StoreOrder extends BaseModel
{
    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'store_order';

    use ModelTrait;

    protected $insert = ['add_time'];

    protected static $payType = ['weixin' => '微信支付', 'yue' => '余额支付', 'offline' => '线下支付'];

    protected static $deliveryType = ['send' => '商家配送', 'express' => '快递配送'];

    protected function setAddTimeAttr()
    {
        return time();
    }

    protected function setCartIdAttr($value)
    {
        return is_array($value) ? json_encode($value) : $value;
    }

    protected function getCartIdAttr($value)
    {
        return json_decode($value, true);
    }

    /**获取订单组信息
     * @param $cartInfo
     * @return array
     */
    public static function getOrderPriceGroup($cartInfo, $addr)
    {
        $storeFreePostage = floatval(sys_config('store_free_postage')) ?: 0;//满额包邮
        $totalPrice = self::getOrderSumPrice($cartInfo, 'truePrice');//获取订单总金额
        $costPrice = self::getOrderSumPrice($cartInfo, 'costPrice');//获取订单成本价
        $vipPrice = self::getOrderSumPrice($cartInfo, 'vip_truePrice');//获取订单会员优惠金额
        //如果满额包邮等于0
        if (!$storeFreePostage) {
            $storePostage = 0;
        } else {
            if ($addr) {
                //按照运费模板计算每个运费模板下商品的件数/重量/体积以及总金额 按照首重倒序排列
                $temp_num = [];
                foreach ($cartInfo as $cart) {
                    $temp = ShippingTemplates::get($cart['productInfo']['temp_id']);
                    if (!$temp) $temp = ShippingTemplates::get(1);
                    if ($temp->getData('type') == 1) {
                        $num = $cart['cart_num'];
                    } elseif ($temp->getData('type') == 2) {
                        $num = $cart['cart_num'] * $cart['productInfo']['attrInfo']['weight'];
                    } else {
                        $num = $cart['cart_num'] * $cart['productInfo']['attrInfo']['volume'];
                    }
                    $region = ShippingTemplatesRegion::where('temp_id', $cart['productInfo']['temp_id'])->where('city_id', $addr['city_id'])->find();
                    if (!$region) $region = ShippingTemplatesRegion::where('temp_id', $cart['productInfo']['temp_id'])->where('city_id', 0)->find();
                    if (!$region) $region = ShippingTemplatesRegion::where('temp_id', 1)->where('city_id', 0)->find();
                    if (!$region) {
                        return self::setErrorInfo('运费模板不存在');
                    }
                    if (!isset($temp_num[$cart['productInfo']['temp_id']])) {
                        $temp_num[$cart['productInfo']['temp_id']]['number'] = $num;
                        $temp_num[$cart['productInfo']['temp_id']]['price'] = bcmul($cart['cart_num'], $cart['truePrice'], 2);
                        $temp_num[$cart['productInfo']['temp_id']]['first'] = $region['first'];
                        $temp_num[$cart['productInfo']['temp_id']]['first_price'] = $region['first_price'];
                        $temp_num[$cart['productInfo']['temp_id']]['continue'] = $region['continue'];
                        $temp_num[$cart['productInfo']['temp_id']]['continue_price'] = $region['continue_price'];
                        $temp_num[$cart['productInfo']['temp_id']]['temp_id'] = $cart['productInfo']['temp_id'];
                        $temp_num[$cart['productInfo']['temp_id']]['city_id'] = $addr['city_id'];
                    } else {
                        $temp_num[$cart['productInfo']['temp_id']]['number'] += $num;
                        $temp_num[$cart['productInfo']['temp_id']]['price'] += bcmul($cart['cart_num'], $cart['truePrice'], 2);
                    }
                }
                array_multisort(array_column($temp_num, 'first_price'), SORT_DESC, $temp_num);
                $type = $storePostage = 0;
                foreach ($temp_num as $k => $v) {
                    if (ShippingTemplatesFree::where('temp_id', $v['temp_id'])->where('city_id', $v['city_id'])->where('number', '<=', $v['number'])->where('price', '<=', $v['price'])->find()) {
                        unset($temp_num[$k]);
                    }
                }
                foreach ($temp_num as $v) {
                    if ($type == 0) {
                        if ($v['number'] <= $v['first']) {
                            $storePostage = bcadd($storePostage, $v['first_price'], 2);
                        } else {
                            if ($v['continue'] <= 0) {
                                $storePostage = $storePostage;
                            } else {
                                $storePostage = bcadd(bcadd($storePostage, $v['first_price'], 2), bcmul(ceil(bcdiv(bcsub($v['number'], $v['first']), $v['continue'] ?? 0, 2)), $v['continue_price']), 2);
                            }
                        }
                        $type = 1;
                    } else {
                        if ($v['continue'] <= 0) {
                            $storePostage = $storePostage;
                        } else {
                            $storePostage = bcadd($storePostage, bcmul(ceil(bcdiv($v['number'], $v['continue'] ?? 0, 2)), $v['continue_price']), 2);
                        }
                    }
                }
            } else {
                $storePostage = 0;
            }
            if ($storeFreePostage <= $totalPrice) $storePostage = 0;//如果总价大于等于满额包邮 邮费等于0
        }
        return compact('storePostage', 'storeFreePostage', 'totalPrice', 'costPrice', 'vipPrice');
    }


    /**获取某个字段总金额
     * @param $cartInfo
     * @param $key 键名
     * @return int|string
     */
    public static function getOrderSumPrice($cartInfo, $key = 'truePrice')
    {
        $SumPrice = 0;
        foreach ($cartInfo as $cart) {
            $SumPrice = bcadd($SumPrice, bcmul($cart['cart_num'], $cart[$key], 2), 2);
        }
        return $SumPrice;
    }


    /**
     * 拼团
     * @param $cartInfo
     * @return array
     */
    public static function getCombinationOrderPriceGroup($cartInfo)
    {
        $storePostage = floatval(sys_config('store_postage')) ?: 0;
        $storeFreePostage = floatval(sys_config('store_free_postage')) ?: 0;
        $totalPrice = self::getCombinationOrderTotalPrice($cartInfo);
        $costPrice = self::getCombinationOrderTotalPrice($cartInfo);
        if (!$storeFreePostage) {
            $storePostage = 0;
        } else {
            foreach ($cartInfo as $cart) {
                if (!StoreCombination::where('id', $cart['combination_id'])->value('is_postage'))
                    $storePostage = bcadd($storePostage, StoreCombination::where('id', $cart['combination_id'])->value('postage'), 2);
            }
            if ($storeFreePostage <= $totalPrice) $storePostage = 0;
        }
        return compact('storePostage', 'storeFreePostage', 'totalPrice', 'costPrice');
    }

    /**
     * 拼团价格
     * @param $cartInfo
     * @return float
     */
    public static function getCombinationOrderTotalPrice($cartInfo)
    {
        $totalPrice = 0;
        foreach ($cartInfo as $cart) {
            if ($cart['combination_id']) {
                $totalPrice = bcadd($totalPrice, bcmul($cart['cart_num'], StoreCombination::where('id', $cart['combination_id'])->value('price'), 2), 2);
            }
        }
        return (float)$totalPrice;
    }

    /**
     * 缓存订单信息
     * @param $uid
     * @param $cartInfo
     * @param $priceGroup
     * @param array $other
     * @param int $cacheTime
     * @return string
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public static function cacheOrderInfo($uid, $cartInfo, $priceGroup, $other = [], $cacheTime = 600)
    {
        $key = md5(time());
        Cache::set('user_order_' . $uid . $key, compact('cartInfo', 'priceGroup', 'other'), $cacheTime);
        return $key;
    }

    /**
     * 获取订单缓存信息
     * @param $uid
     * @param $key
     * @return mixed|null
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public static function getCacheOrderInfo($uid, $key)
    {
        $cacheName = 'user_order_' . $uid . $key;
        if (!Cache::has($cacheName)) return null;
        return Cache::get($cacheName);
    }

    /**
     * 删除订单缓存
     * @param $uid
     * @param $key
     */
    public static function clearCacheOrderInfo($uid, $key)
    {
        Cache::delete('user_order_' . $uid . $key);
    }

    /**
     * 生成订单
     * @param $uid
     * @param $key
     * @param $addressId
     * @param $payType
     * @param bool $useIntegral
     * @param int $couponId
     * @param string $mark
     * @param int $combinationId
     * @param int $pinkId
     * @param int $seckill_id
     * @param int $bargain_id
     * @param bool $test
     * @param int $isChannel
     * @param int $shipping_type
     * @param string $real_name
     * @param string $phone
     * @return StoreOrder|bool|\think\Model
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */

    public static function cacheKeyCreateOrder($uid, $key, $addressId, $payType, $useIntegral = false, $couponId = 0, $mark = '', $combinationId = 0, $pinkId = 0, $seckill_id = 0, $bargain_id = 0, $test = false, $isChannel = 0, $shipping_type = 1, $real_name = '', $phone = '', $storeId = 0)
    {
        self::beginTrans();
        try {
            $shipping_type = (int)$shipping_type;
            $offlinePayStatus = (int)sys_config('offline_pay_status') ?? (int)2;
            if ($offlinePayStatus == 2) unset(self::$payType['offline']);
            if (!array_key_exists($payType, self::$payType)) return self::setErrorInfo('选择支付方式有误!', true);
            if (self::be(['unique' => $key, 'uid' => $uid])) return self::setErrorInfo('请勿重复提交订单', true);
            $userInfo = User::getUserInfo($uid);
            if (!$userInfo) return self::setErrorInfo('用户不存在!', true);
            $cartGroup = self::getCacheOrderInfo($uid, $key);
            if (!$cartGroup) return self::setErrorInfo('订单已过期,请刷新当前页面!', true);
            $cartInfo = $cartGroup['cartInfo'];
            $priceGroup = $cartGroup['priceGroup'];
            $other = $cartGroup['other'];
            $payPrice = (float)$priceGroup['totalPrice'];
            $addr = UserAddress::where('uid', $uid)->where('id', $addressId)->find();
            if ($payType == 'offline' && sys_config('offline_postage') == 1) {
                $payPostage = 0;
            } else {
                $payPostage = self::getOrderPriceGroup($cartInfo, $addr)['storePostage'];
            }
            if ($shipping_type === 1) {
                if (!$test && !$addressId) return self::setErrorInfo('请选择收货地址!', true);
                if (!$test && (!UserAddress::be(['uid' => $uid, 'id' => $addressId, 'is_del' => 0]) || !($addressInfo = UserAddress::find($addressId))))
                    return self::setErrorInfo('地址选择有误!', true);
            } else {
                if ((!$real_name || !$phone) && !$test) return self::setErrorInfo('请填写姓名和电话', true);
                $addressInfo['real_name'] = $real_name;
                $addressInfo['phone'] = $phone;
                $addressInfo['province'] = '';
                $addressInfo['city'] = '';
                $addressInfo['district'] = '';
                $addressInfo['detail'] = '';
            }

            $cartIds = [];
            $totalNum = 0;
            $gainIntegral = 0;
            foreach ($cartInfo as $cart){
                if (!$test && !self::checkProductStock($uid, $cart['product_id'], $cart['cart_num'], $cart['product_attr_unique'], $cart['combination_id'], $cart['seckill_id'], $cart['bargain_id'])) {
                    return false;
                }
                $cartIds[] = $cart['id'];
                $totalNum += $cart['cart_num'];
                if (!$seckill_id) $seckill_id = $cart['seckill_id'];
                if (!$bargain_id) $bargain_id = $cart['bargain_id'];
                if (!$combinationId) $combinationId = $cart['combination_id'];
                $cartInfoGainIntegral = isset($cart['productInfo']['give_integral']) ? bcmul($cart['cart_num'], $cart['productInfo']['give_integral'], 2) : 0;
                $gainIntegral = bcadd($gainIntegral, $cartInfoGainIntegral, 2);
            }
            $deduction = $seckill_id || $bargain_id || $combinationId;
            if ($deduction) {
                $couponId = 0;
                $useIntegral = false;
                if (!$test) {
                    unset(self::$payType['offline']);
                    if (!array_key_exists($payType, self::$payType)) return self::setErrorInfo('营销产品不能使用线下支付!', true);
                }
            }
            //使用优惠劵
            $res1 = true;
            if ($couponId) {
                $couponInfo = StoreCouponUser::validAddressWhere()->where('id', $couponId)->where('uid', $uid)->find();
                if (!$couponInfo) return self::setErrorInfo('选择的优惠劵无效!', true);
                $coupons = StoreCouponUser::getUsableCouponList($uid, ['valid' => $cartInfo], $payPrice);
                $flag = false;
                foreach ($coupons as $coupon) {
                    if ($coupon['id'] == $couponId) {
                        $flag = true;
                        continue;
                    }
                }
                if (!$flag)
                    return self::setErrorInfo('不满足优惠劵的使用条件!', true);
                $payPrice = (float)bcsub($payPrice, $couponInfo['coupon_price'], 2);
                $res1 = StoreCouponUser::useCoupon($couponId);
                $couponPrice = $couponInfo['coupon_price'];
            } else {
                $couponId = 0;
                $couponPrice = 0;
            }
            if (!$res1) return self::setErrorInfo('使用优惠劵失败!', true);

            //$shipping_type = 1 快递发货 $shipping_type = 2 门店自提
            $store_self_mention = sys_config('store_self_mention') ?? 0;
            if (!$store_self_mention) $shipping_type = 1;
            if ($shipping_type === 1) {
                //是否包邮
                if ((isset($other['offlinePostage']) && $other['offlinePostage'] && $payType == 'offline')) $payPostage = 0;
                $payPrice = (float)bcadd($payPrice, $payPostage, 2);
            } else if ($shipping_type === 2) {
                //门店自提没有邮费支付
                $priceGroup['storePostage'] = 0;
                $payPostage = 0;
                if (!$storeId && !$test) {
                    return self::setErrorInfo('请选择门店', true);
                }
            }

            //积分抵扣
            $res2 = true;
            $SurplusIntegral = 0;
            if ($useIntegral && $userInfo['integral'] > 0) {
                $deductionPrice = (float)bcmul($userInfo['integral'], $other['integralRatio'], 2);
                if ($deductionPrice < $payPrice) {
                    $payPrice = bcsub($payPrice, $deductionPrice, 2);
                    $usedIntegral = $userInfo['integral'];
                    $SurplusIntegral = 0;
                    $res2 = false !== User::edit(['integral' => 0], $userInfo['uid'], 'uid');
                } else {
                    $deductionPrice = $payPrice;
                    $usedIntegral = (float)bcdiv($payPrice, $other['integralRatio'], 2);
                    $SurplusIntegral = bcsub($userInfo['integral'], $usedIntegral, 2);
                    $res2 = false !== User::bcDec($userInfo['uid'], 'integral', $usedIntegral, 'uid');
                    $payPrice = 0;
                }
                $res2 = $res2 && false != UserBill::expend('积分抵扣', $uid, 'integral', 'deduction', $usedIntegral, $key, $userInfo['integral'], '购买商品使用' . floatval($usedIntegral) . '积分抵扣' . floatval($deductionPrice) . '元');
            } else {
                $deductionPrice = 0;
                $usedIntegral = 0;
            }
            if (!$res2) return self::setErrorInfo('使用积分抵扣失败!', true);
            if ($payPrice <= 0) $payPrice = 0;
            if ($test) {
                self::rollbackTrans();
                return [
                    'total_price' => $priceGroup['totalPrice'],
                    'pay_price' => $payPrice,
                    'pay_postage' => $payPostage,
                    'coupon_price' => $couponPrice,
                    'deduction_price' => $deductionPrice,
                    'SurplusIntegral' => $SurplusIntegral,
                ];
            }
            $orderInfo = [
                'uid' => $uid,
                'order_id' => $test ? 0 : self::getNewOrderId(),
                'real_name' => $addressInfo['real_name'],
                'user_phone' => $addressInfo['phone'],
                'user_address' => $addressInfo['province'] . ' ' . $addressInfo['city'] . ' ' . $addressInfo['district'] . ' ' . $addressInfo['detail'],
                'cart_id' => $cartIds,
                'total_num' => $totalNum,
                'total_price' => $priceGroup['totalPrice'],
                'total_postage' => $priceGroup['storePostage'],
                'coupon_id' => $couponId,
                'coupon_price' => $couponPrice,
                'pay_price' => $payPrice,
                'pay_postage' => $payPostage,
                'deduction_price' => $deductionPrice,
                'paid' => 0,
                'pay_type' => $payType,
                'use_integral' => $usedIntegral,
                'gain_integral' => $gainIntegral,
                'mark' => htmlspecialchars($mark),
                'combination_id' => $combinationId,
                'pink_id' => $pinkId,
                'seckill_id' => $seckill_id,
                'bargain_id' => $bargain_id,
                'cost' => $priceGroup['costPrice'],
                'is_channel' => $isChannel,
                'add_time' => time(),
                'unique' => $key,
                'shipping_type' => $shipping_type,
            ];
            if ($shipping_type === 2) {
                $orderInfo['verify_code'] = self::getStoreCode();
                $orderInfo['store_id'] = SystemStore::getStoreDispose($storeId, 'id');
                if (!$orderInfo['store_id']) return self::setErrorInfo('暂无门店无法选择门店自提！', true);
            }
            $order = self::create($orderInfo);
            if (!$order) return self::setErrorInfo('订单生成失败!', true);
            $res5 = true;
            foreach ($cartInfo as $cart) {
                //减库存加销量
                if ($combinationId) $res5 = $res5 && StoreCombination::decCombinationStock($cart['cart_num'], $combinationId, isset($cart['productInfo']['attrInfo']) ? $cart['productInfo']['attrInfo']['unique'] : '');
                else if ($seckill_id) $res5 = $res5 && StoreSeckill::decSeckillStock($cart['cart_num'], $seckill_id, isset($cart['productInfo']['attrInfo']) ? $cart['productInfo']['attrInfo']['unique'] : '');
                else if ($bargain_id) $res5 = $res5 && StoreBargain::decBargainStock($cart['cart_num'], $bargain_id, isset($cart['productInfo']['attrInfo']) ? $cart['productInfo']['attrInfo']['unique'] : '');
                else $res5 = $res5 && StoreProduct::decProductStock($cart['cart_num'], $cart['productInfo']['id'], isset($cart['productInfo']['attrInfo']) ? $cart['productInfo']['attrInfo']['unique'] : '');
            }
            //保存购物车商品信息
            $res4 = false !== StoreOrderCartInfo::setCartInfo($order['id'], $cartInfo);
            //购物车状态修改
            $res6 = false !== StoreCart::where('id', 'IN', $cartIds)->update(['is_pay' => 1]);
            if (!$res4 || !$res5 || !$res6) return self::setErrorInfo('订单生成失败!', true);
            //自动设置默认地址
            UserRepository::storeProductOrderCreateEbApi($order, compact('cartInfo', 'addressId'));
            self::clearCacheOrderInfo($uid, $key);
            self::commitTrans();
            StoreOrderStatus::status($order['id'], 'cache_key_create_order', '订单生成');
            return $order;
        } catch (\PDOException $e) {
            self::rollbackTrans();
            return self::setErrorInfo('生成订单时SQL执行错误错误原因：' . $e->getMessage());
        } catch (\Exception $e) {
            self::rollbackTrans();
            return self::setErrorInfo('生成订单时系统错误错误原因：' . $e->getMessage());
        }
    }


    /**
     * 回退积分
     * @param $order 订单信息
     * @return bool
     */
    public static function RegressionIntegral($order)
    {
        if ($order['paid'] || $order['status'] == -2 || $order['is_del']) return true;
        if ($order['use_integral'] <= 0) return true;
        if ((int)$order['status'] != -2 && (int)$order['refund_status'] != 2 && $order['back_integral'] >= $order['use_integral']) return true;
        $res = User::bcInc($order['uid'], 'integral', $order['use_integral']);
        if (!$res) return self::setErrorInfo('回退积分增加失败');
        UserBill::income('积分回退', $order['uid'], 'integral', 'deduction', $order['use_integral'], $order['unique'], User::where('uid', $order['uid'])->value('integral'), '购买商品失败,回退积分' . floatval($order['use_integral']));
        return false !== self::where('order_id', $order['order_id'])->update(['back_integral' => $order['use_integral']]);
    }


    /**
     * 回退库存和销量
     * @param $order 订单信息
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function RegressionStock($order)
    {
        if ($order['paid'] || $order['status'] == -2 || $order['is_del']) return true;
        $combinationId = $order['combination_id'];
        $seckill_id = $order['seckill_id'];
        $bargain_id = $order['bargain_id'];
        $res5 = true;
        $cartInfo = StoreOrderCartInfo::where('cart_id', 'in', $order['cart_id'])->select();
        foreach ($cartInfo as $cart) {
            //增库存减销量
            if ($combinationId) $res5 = $res5 && StoreCombination::incCombinationStock($cart['cart_info']['cart_num'], $combinationId, isset($cart['productInfo']['attrInfo']) ? $cart['productInfo']['attrInfo']['unique'] : '');
            else if ($seckill_id) $res5 = $res5 && StoreSeckill::incSeckillStock($cart['cart_info']['cart_num'], $seckill_id, isset($cart['productInfo']['attrInfo']) ? $cart['productInfo']['attrInfo']['unique'] : '');
            else if ($bargain_id) $res5 = $res5 && StoreBargain::incBargainStock($cart['cart_info']['cart_num'], $bargain_id, isset($cart['productInfo']['attrInfo']) ? $cart['productInfo']['attrInfo']['unique'] : '');
            else $res5 = $res5 && StoreProduct::incProductStock($cart['cart_info']['cart_num'], $cart['cart_info']['productInfo']['id'], isset($cart['cart_info']['productInfo']['attrInfo']) ? $cart['cart_info']['productInfo']['attrInfo']['unique'] : '');
        }
        return $res5;
    }

    /**
     * 回退优惠卷
     * @param $order 订单信息
     * @return bool
     */
    public static function RegressionCoupon($order)
    {
        if ($order['paid'] || $order['status'] == -2 || $order['is_del']) return true;
        $res = true;
        if ($order['coupon_id'] && StoreCouponUser::be(['id' => $order['coupon_id'], 'uid' => $order['uid'], 'status' => 1])) {
            $res = $res && false !== StoreCouponUser::where('id', $order['coupon_id'])->where('uid', $order['uid'])->update(['status' => 0, 'use_time' => 0]);
        }
        return $res;
    }

    /**
     * 取消订单
     * @param string order_id 订单id
     * @param $uid
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function cancelOrder($order_id, $uid)
    {
        $order = self::where('order_id', $order_id)->where('uid', $uid)->find();
        if (!$order) return self::setErrorInfo('没有查到此订单');
        self::beginTrans();
        try {
            $res = self::RegressionIntegral($order) && self::RegressionStock($order) && self::RegressionCoupon($order);
            $order->is_del = 1;
            if ($res && $order->save()) {
                self::commitTrans();
                return true;
            } else
                return false;
        } catch (\Exception $e) {
            self::rollbackTrans();
            return self::setErrorInfo(['line' => $e->getLine(), 'message' => $e->getMessage()]);
        }
    }

    /**
     * 生成订单唯一id
     * @param $uid 用户uid
     * @return string
     */
    public static function getNewOrderId()
    {
        list($msec, $sec) = explode(' ', microtime());
        $msectime = number_format((floatval($msec) + floatval($sec)) * 1000, 0, '', '');
        $orderId = 'wx' . $msectime . mt_rand(10000, 99999);
        if (self::be(['order_id' => $orderId])) $orderId = 'wx' . $msectime . mt_rand(10000, 99999);
        return $orderId;
    }

    /**
     * 修改订单号
     * @param $orderId
     * @return string
     */
    public static function changeOrderId($orderId)
    {
        $ymd = substr($orderId, 2, 8);
        $key = substr($orderId, 16);
        return 'wx' . $ymd . date('His') . $key;
    }

    /**
     * 查找购物车里的所有产品标题
     * @param $cartId 购物车id
     * @return bool|string
     */
    public static function getProductTitle($cartId)
    {
        $title = '';
        try {
            $orderCart = StoreOrderCartInfo::where('cart_id', 'in', $cartId)->field('cart_info')->select();
            foreach ($orderCart as $item) {
                if (isset($item['cart_info']['productInfo']['store_name'])) {
                    $title .= $item['cart_info']['productInfo']['store_name'] . '|';
                }
            }
            unset($item);
            if (!$title) {
                $productIds = StoreCart::where('id', 'in', $cartId)->column('product_id');
                $productlist = ($productlist = StoreProduct::getProductField($productIds, 'store_name')) ? $productlist->toArray() : [];
                foreach ($productlist as $item) {
                    if (isset($item['store_name'])) $title .= $item['store_name'] . '|';
                }
            }
            if ($title) $title = substr($title, 0, strlen($title) - 1);
            unset($item);
        } catch (\Exception $e) {
        }
        return $title;
    }

    /**
     * 获取门店自提唯一核销码
     * @return bool|string
     */
    public static function getStoreCode()
    {
        list($msec, $sec) = explode(' ', microtime());
        $num = bcadd(time(), mt_rand(10, 999999), 0) . '' . substr($msec, 2, 3);//生成随机数
        if (strlen($num) < 12)
            $num = str_pad((string)$num, 12, 0, STR_PAD_RIGHT);
        else
            $num = substr($num, 0, 12);
        if (self::be(['verify_code' => $num])) return self::getStoreCode();
        return $num;
    }

    /**
     * 余额支付
     * @param $order_id
     * @param $uid
     * @param string $formId
     * @return bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function yuePay($order_id, $uid, $formId = '')
    {
        $orderInfo = self::where('uid', $uid)->where('order_id', $order_id)->where('is_del', 0)->find();
        if (!$orderInfo) return self::setErrorInfo('订单不存在!');
        if ($orderInfo['paid']) return self::setErrorInfo('该订单已支付!');
//        if($orderInfo['pay_type'] != 'yue') return self::setErrorInfo('该订单不能使用余额支付!');
        $userInfo = User::getUserInfo($uid);
        if ($userInfo['now_money'] < $orderInfo['pay_price'])
            return self::setErrorInfo(['status' => 'pay_deficiency', 'msg' => '余额不足' . floatval($orderInfo['pay_price'])]);
        self::beginTrans();

        $res1 = false !== User::bcDec($uid, 'now_money', $orderInfo['pay_price'], 'uid');
        $res2 = UserBill::expend('购买商品', $uid, 'now_money', 'pay_product', $orderInfo['pay_price'], $orderInfo['id'], $userInfo['now_money'], '余额支付' . floatval($orderInfo['pay_price']) . '元购买商品');
        $res3 = self::paySuccess($order_id, 'yue', $formId);//余额支付成功
        try {
            PaymentRepositories::yuePayProduct($userInfo, $orderInfo);
        } catch (\Exception $e) {
            self::rollbackTrans();
            return self::setErrorInfo($e->getMessage());
        }
        $res = $res1 && $res2 && $res3;
        self::checkTrans($res);
        return $res;
    }

    /**
     * 微信支付 为 0元时
     * @param $order_id
     * @param $uid
     * @param string $formId
     * @return bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function jsPayPrice($order_id, $uid, $formId = '')
    {
        $orderInfo = self::where('uid', $uid)->where('order_id', $order_id)->where('is_del', 0)->find();
        if (!$orderInfo) return self::setErrorInfo('订单不存在!');
        if ($orderInfo['paid']) return self::setErrorInfo('该订单已支付!');
        $userInfo = User::getUserInfo($uid);
        self::beginTrans();
        $res1 = UserBill::expend('购买商品', $uid, 'now_money', 'pay_product', $orderInfo['pay_price'], $orderInfo['id'], $userInfo['now_money'], '微信支付' . floatval($orderInfo['pay_price']) . '元购买商品');
        $res2 = self::paySuccess($order_id, 'weixin', $formId);//微信支付为0时
        $res = $res1 && $res2;
        self::checkTrans($res);
        return $res;
    }


    /**
     * 用户申请退款
     * @param $uni
     * @param $uid
     * @param string $refundReasonWap
     * @return bool
     */
    public static function orderApplyRefund($uni, $uid, $refundReasonWap = '', $refundReasonWapExplain = '', $refundReasonWapImg = [])
    {
        $order = self::getUserOrderDetail($uid, $uni);
        if (!$order) return self::setErrorInfo('支付订单不存在!');
        if ($order['refund_status'] == 2) return self::setErrorInfo('订单已退款!');
        if ($order['refund_status'] == 1) return self::setErrorInfo('正在申请退款中!');
        if ($order['status'] == 1) return self::setErrorInfo('订单当前无法退款!');
        self::beginTrans();
        $res1 = false !== StoreOrderStatus::status($order['id'], 'apply_refund', '用户申请退款，原因：' . $refundReasonWap);
        $res2 = false !== self::edit(['refund_status' => 1, 'refund_reason_time' => time(), 'refund_reason_wap' => $refundReasonWap, 'refund_reason_wap_explain' => $refundReasonWapExplain, 'refund_reason_wap_img' => json_encode($refundReasonWapImg)], $order['id'], 'id');
        $res = $res1 && $res2;
        self::checkTrans($res);
        if (!$res)
            return self::setErrorInfo('申请退款失败!');
        else {
            try {
                if (in_array($order['is_channel'], [0, 2])) {
                    //公众号发送模板消息通知客服
                    WechatTemplateService::sendAdminNoticeTemplate([
                        'first' => "亲,有个订单申请退款 \n订单号:{$order['order_id']}",
                        'keyword1' => '退款申请',
                        'keyword2' => '已支付',
                        'keyword3' => date('Y/m/d H:i', time()),
                        'remark' => '请及时处理'
                    ]);
                }
                if (in_array($order['is_channel'], [1, 2])) {
                    //小程序 发送模板消息
                    RoutineTemplate::sendOrderRefundStatus($order, $refundReasonWap);
                }
                //通知后台消息提醒
                ChannelService::instance()->send('NEW_REFUND_ORDER', ['order_id' => $order['order_id']]);
            } catch (\Exception $e) {
            }
            //发送短信
            event('ShortMssageSend', [$order['order_id'], 'AdminRefund']);
            return true;
        }
    }

    /**
     * //TODO 支付成功后
     * @param $orderId
     * @param $paytype
     * @param $notify
     * @return bool
     */
    public static function paySuccess($orderId, $paytype = 'weixin', $formId = '')
    {
        $order = self::where('order_id', $orderId)->find();
        $resPink = true;
        $res1 = self::where('order_id', $orderId)->update(['paid' => 1, 'pay_type' => $paytype, 'pay_time' => time()]);//订单改为支付
        if ($order->combination_id && $res1 && !$order->refund_status) $resPink = StorePink::createPink($order);//创建拼团
        $oid = self::where('order_id', $orderId)->value('id');
        StoreOrderStatus::status($oid, 'pay_success', '用户付款成功');
        $now_money = User::where('uid', $order['uid'])->value('now_money');
        if ($order->combination_id == 0 && $order->seckill_id == 0 && $order->bargain_id == 0) {
            UserBill::expend('购买商品', $order['uid'], 'now_money', 'pay_money', $order['pay_price'], $order['id'], $now_money, '支付' . floatval($order['pay_price']) . '元购买商品');
        }
        //支付成功后
        event('OrderPaySuccess', [$order, $formId]);
        $res = $res1 && $resPink;
        return false !== $res;
    }

    /*
     * 线下支付消息通知
     * 待完善
     *
     * */
    public static function createOrderTemplate($order)
    {

        //$goodsName = StoreOrderCartInfo::getProductNameList($order['id']);
//        RoutineTemplateService::sendTemplate(WechatUser::getOpenId($order['uid']),RoutineTemplateService::ORDER_CREATE, [
//            'first'=>'亲，您购买的商品已支付成功',
//            'keyword1'=>date('Y/m/d H:i',$order['add_time']),
//            'keyword2'=>implode(',',$goodsName),
//            'keyword3'=>$order['order_id'],
//            'remark'=>'点击查看订单详情'
//        ],Url::build('/wap/My/order',['uni'=>$order['order_id']],true,true));
//        RoutineTemplateService::sendAdminNoticeTemplate([
//            'first'=>"亲,您有一个新订单 \n订单号:{$order['order_id']}",
//            'keyword1'=>'新订单',
//            'keyword2'=>'线下支付',
//            'keyword3'=>date('Y/m/d H:i',time()),
//            'remark'=>'请及时处理'
//        ]);
    }

    /**
     * 获取订单详情
     * @param $uid
     * @param $key
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getUserOrderDetail($uid, $key)
    {
        return self::where('order_id|unique', $key)->where('uid', $uid)->where('is_del', 0)->find();
    }


    /**
     * TODO 订单发货
     * @param array $postageData 发货信息
     * @param string $oid orderID
     */
    public static function orderPostageAfter($postageData, $oid)
    {
        $order = self::where('id', $oid)->find();
        if ($postageData['delivery_type'] == 'send') {//送货
            RoutineTemplate::sendOrderPostage($order);
        } else if ($postageData['delivery_type'] == 'express') {//发货
            RoutineTemplate::sendOrderPostage($order, 1);
        }
    }

    /** 收货后发送模版消息
     * @param $order
     */
    public static function orderTakeAfter($order)
    {
        $title = self::getProductTitle($order['cart_id']);
        if ($order['is_channel'] == 1) {//小程序
            RoutineTemplate::sendOrderTakeOver($order, $title);
        } else {
            $openid = WechatUser::where('uid', $order['uid'])->value('openid');
            \crmeb\services\WechatTemplateService::sendTemplate($openid, \crmeb\services\WechatTemplateService::ORDER_TAKE_SUCCESS, [
                'first' => '亲，您的订单已收货',
                'keyword1' => $order['order_id'],
                'keyword2' => '已收货',
                'keyword3' => date('Y-m-d H:i:s', time()),
                'keyword4' => $title,
                'remark' => '感谢您的光临！'
            ]);
        }
    }

    /**
     * 删除订单
     * @param $uni
     * @param $uid
     * @return bool
     */
    public static function removeOrder($uni, $uid)
    {
        $order = self::getUserOrderDetail($uid, $uni);
        if (!$order) return self::setErrorInfo('订单不存在!');
        $order = self::tidyOrder($order);
        if ($order['_status']['_type'] != 0 && $order['_status']['_type'] != -2 && $order['_status']['_type'] != 4)
            return self::setErrorInfo('该订单无法删除!');
        if (false !== self::edit(['is_del' => 1], $order['id'], 'id') && false !== StoreOrderStatus::status($order['id'], 'remove_order', '删除订单')) {
            //未支付和已退款的状态下才可以退积分退库存退优惠券
            if ($order['_status']['_type'] == 0 || $order['_status']['_type'] == -2) {
                event('StoreOrderRegressionAllAfter', [$order]);
            }
            event('UserOrderRemoved', $uni);
            return true;
        } else
            return self::setErrorInfo('订单删除失败!');
    }


    /**
     * //TODO 用户确认收货
     * @param $uni
     * @param $uid
     */
    public static function takeOrder($uni, $uid)
    {
        $order = self::getUserOrderDetail($uid, $uni);
        if (!$order) return self::setErrorInfo('订单不存在!');
        $order = self::tidyOrder($order);
        if ($order['_status']['_type'] != 2) return self::setErrorInfo('订单状态错误!');
        self::beginTrans();
        if (false !== self::edit(['status' => 2], $order['id'], 'id') &&
            false !== StoreOrderStatus::status($order['id'], 'user_take_delivery', '用户已收货')) {
            try {
                OrderRepository::storeProductOrderUserTakeDelivery($order, $uid);
                UserBill::where('uid', $order['uid'])->where('link_id', $order['id'])->where('type', 'pay_money')->update(['take' => 1]);
            } catch (\Exception $e) {
                self::rollbackTrans();
                return self::setErrorInfo($e->getMessage());
            }
            self::commitTrans();
            event('UserLevelAfter', [User::get($uni)]);
            event('UserOrderTake', $uni);
            //短信通知
            event('ShortMssageSend', [$order['order_id'], ['Receiving', 'AdminConfirmTakeOver']]);
            return true;
        } else {
            self::rollbackTrans();
            return false;
        }
    }

    /**
     * 获取订单状态购物车等信息
     * @param $order
     * @param bool $detail 是否获取订单购物车详情
     * @param bool $isPic 是否获取订单状态图片
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function tidyOrder($order, $detail = false, $isPic = false)
    {
        if ($detail == true && isset($order['id'])) {
            $cartInfo = StoreOrderCartInfo::where('oid', $order['id'])->column('cart_info', 'unique') ?: [];
            $info = [];
            foreach ($cartInfo as $k => $cart) {
                $cart = json_decode($cart, true);
                $cart['unique'] = $k;
                //新增是否评价字段
                $cart['is_reply'] = StoreProductReply::where('unique', $k)->count();
                array_push($info, $cart);
                unset($cart);
            }
            $order['cartInfo'] = $info;
        }
        $status = [];
        if (!$order['paid'] && $order['pay_type'] == 'offline' && !$order['status'] >= 2) {
            $status['_type'] = 9;
            $status['_title'] = '线下付款';
            $status['_msg'] = '商家处理中,请耐心等待';
            $status['_class'] = 'nobuy';
        } else if (!$order['paid']) {
            $status['_type'] = 0;
            $status['_title'] = '未支付';
            //系统预设取消订单时间段
            $keyValue = ['order_cancel_time', 'order_activity_time', 'order_bargain_time', 'order_seckill_time', 'order_pink_time'];
            //获取配置
            $systemValue = SystemConfigService::more($keyValue);
            //格式化数据
            $systemValue = self::setValeTime($keyValue, is_array($systemValue) ? $systemValue : []);
            if ($order['pink_id'] || $order['combination_id']) {
                $order_pink_time = $systemValue['order_pink_time'] ? $systemValue['order_pink_time'] : $systemValue['order_activity_time'];
                $time = bcadd($order['add_time'], $order_pink_time * 3600, 0);
                $status['_msg'] = '请在' . date('m-d H:i:s', $time) . '前完成支付!';
            } else if ($order['seckill_id']) {
                $order_seckill_time = $systemValue['order_seckill_time'] ? $systemValue['order_seckill_time'] : $systemValue['order_activity_time'];
                $time = bcadd($order['add_time'], $order_seckill_time * 3600, 0);
                $status['_msg'] = '请在' . date('m-d H:i:s', $time) . '前完成支付!';
            } else if ($order['bargain_id']) {
                $order_bargain_time = $systemValue['order_bargain_time'] ? $systemValue['order_bargain_time'] : $systemValue['order_activity_time'];
                $time = bcadd($order['add_time'], $order_bargain_time * 3600, 0);
                $status['_msg'] = '请在' . date('m-d H:i:s', $time) . '前完成支付!';
            } else {
                $time = bcadd($order['add_time'], $systemValue['order_cancel_time'] * 3600, 0);
                $status['_msg'] = '请在' . date('m-d H:i:s', $time) . '前完成支付!';
            }
            $status['_class'] = 'nobuy';
        } else if ($order['refund_status'] == 1) {
            $status['_type'] = -1;
            $status['_title'] = '申请退款中';
            $status['_msg'] = '商家审核中,请耐心等待';
            $status['_class'] = 'state-sqtk';
        } else if ($order['refund_status'] == 2) {
            $status['_type'] = -2;
            $status['_title'] = '已退款';
            $status['_msg'] = '已为您退款,感谢您的支持';
            $status['_class'] = 'state-sqtk';
        } else if (!$order['status']) {
            if ($order['pink_id']) {
                if (StorePink::where('id', $order['pink_id'])->where('status', 1)->count()) {
                    $status['_type'] = 1;
                    $status['_title'] = '拼团中';
                    $status['_msg'] = '等待其他人参加拼团';
                    $status['_class'] = 'state-nfh';
                } else {
                    $status['_type'] = 1;
                    $status['_title'] = '未发货';
                    $status['_msg'] = '商家未发货,请耐心等待';
                    $status['_class'] = 'state-nfh';
                }
            } else {
                if ($order['shipping_type'] === 1) {
                    $status['_type'] = 1;
                    $status['_title'] = '未发货';
                    $status['_msg'] = '商家未发货,请耐心等待';
                    $status['_class'] = 'state-nfh';
                } else {
                    $status['_type'] = 1;
                    $status['_title'] = '待核销';
                    $status['_msg'] = '待核销,请到核销点进行核销';
                    $status['_class'] = 'state-nfh';
                }
            }
        } else if ($order['status'] == 1) {
            if ($order['delivery_type'] == 'send') {//TODO 送货
                $status['_type'] = 2;
                $status['_title'] = '待收货';
                $status['_msg'] = date('m月d日H时i分', StoreOrderStatus::getTime($order['id'], 'delivery')) . '服务商已送货';
                $status['_class'] = 'state-ysh';
            } else {//TODO  发货
                $status['_type'] = 2;
                $status['_title'] = '待收货';
                if ($order['delivery_type'] == 'fictitious')
                    $_time = StoreOrderStatus::getTime($order['id'], 'delivery_fictitious');
                else
                    $_time = StoreOrderStatus::getTime($order['id'], 'delivery_goods');
                $status['_msg'] = date('m月d日H时i分', $_time) . '服务商已发货';
                $status['_class'] = 'state-ysh';
            }
        } else if ($order['status'] == 2) {
            $status['_type'] = 3;
            $status['_title'] = '待评价';
            $status['_msg'] = '已收货,快去评价一下吧';
            $status['_class'] = 'state-ypj';
        } else if ($order['status'] == 3) {
            $status['_type'] = 4;
            $status['_title'] = '交易完成';
            $status['_msg'] = '交易完成,感谢您的支持';
            $status['_class'] = 'state-ytk';
        }
        if (isset($order['pay_type']))
            $status['_payType'] = isset(self::$payType[$order['pay_type']]) ? self::$payType[$order['pay_type']] : '其他方式';
        if (isset($order['delivery_type']))
            $status['_deliveryType'] = isset(self::$deliveryType[$order['delivery_type']]) ? self::$deliveryType[$order['delivery_type']] : '其他方式';
        $order['_status'] = $status;
        $order['_pay_time'] = isset($order['pay_time']) && $order['pay_time'] != null ? date('Y-m-d H:i:s', $order['pay_time']) : date('Y-m-d H:i:s', $order['add_time']);
        $order['_add_time'] = isset($order['add_time']) ? (strstr($order['add_time'], '-') === false ? date('Y-m-d H:i:s', $order['add_time']) : $order['add_time']) : '';
        $order['status_pic'] = '';
        //获取产品状态图片
        if ($isPic) {
            $order_details_images = sys_data('order_details_images') ?: [];
            foreach ($order_details_images as $image) {
                if (isset($image['order_status']) && $image['order_status'] == $order['_status']['_type']) {
                    $order['status_pic'] = $image['pic'];
                    break;
                }
            }
        }
        $order['offlinePayStatus'] = (int)sys_config('offline_pay_status') ?? (int)2;
        return $order;
    }

    /**
     * 设置订单查询状态
     * @param $status
     * @param int $uid
     * @param null $model
     * @return StoreOrder|null
     */
    public static function statusByWhere($status, $uid = 0, $model = null)
    {
//        $orderId = StorePink::where('uid',$uid)->where('status',1)->column('order_id','id');//获取正在拼团的订单编号
        if ($model == null) $model = new self;
        if ('' === $status)
            return $model;
        else if ($status == 0)//未支付
            return $model->where('paid', 0)->where('status', 0)->where('refund_status', 0);
        else if ($status == 1)//待发货
            return $model->where('paid', 1)->where('status', 0)->where('refund_status', 0);
        else if ($status == 2)//待收货
            return $model->where('paid', 1)->where('status', 1)->where('refund_status', 0);
        else if ($status == 3)//待评价
            return $model->where('paid', 1)->where('status', 2)->where('refund_status', 0);
        else if ($status == 4)//已完成
            return $model->where('paid', 1)->where('status', 3)->where('refund_status', 0);
        else if ($status == -1)//退款中
            return $model->where('paid', 1)->where('refund_status', 1);
        else if ($status == -2)//已退款
            return $model->where('paid', 1)->where('refund_status', 2);
        else if ($status == -3)//退款
            return $model->where('paid', 1)->where('refund_status', 'IN', '1,2');
//        else if($status == 11){
//            return $model->where('order_id','IN',implode(',',$orderId));
//        }
        else
            return $model;
    }

    /**
     * 获取订单并分页
     * @param $uid
     * @param string $status
     * @param int $page
     * @param int $limit
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getUserOrderList($uid, $status = '', $page = 0, $limit = 8)
    {
        if ($page) $list = self::statusByWhere($status, $uid)->where('is_del', 0)->where('uid', $uid)
            ->field('add_time,seckill_id,bargain_id,combination_id,id,order_id,pay_price,total_num,total_price,pay_postage,total_postage,paid,status,refund_status,pay_type,coupon_price,deduction_price,pink_id,delivery_type,is_del,shipping_type')
            ->order('add_time DESC')->page((int)$page, (int)$limit)->select()->toArray();
        else  $list = self::statusByWhere($status, $uid)->where('is_del', 0)->where('uid', $uid)
            ->field('add_time,seckill_id,bargain_id,combination_id,id,order_id,pay_price,total_num,total_price,pay_postage,total_postage,paid,status,refund_status,pay_type,coupon_price,deduction_price,pink_id,delivery_type,is_del,shipping_type')
            ->order('add_time DESC')->page((int)$page, (int)$limit)->select()->toArray();
        foreach ($list as $k => $order) {
            $list[$k] = self::tidyOrder($order, true);
        }

        return $list;
    }

    /**
     * 获取推广人地下用户的订单金额
     * @param string $uid
     * @param string $status
     * @return array
     */
    public static function getUserOrderCount($uid = '', $status = '')
    {
        $res = self::statusByWhere($status, $uid)->where('uid', 'IN', $uid)->column('pay_price');
        return $res;
    }

    /**
     * 搜索某个订单详细信息
     * @param $uid
     * @param $order_id
     * @return bool|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function searchUserOrder($uid, $order_id)
    {
        $order = self::where('uid', $uid)->where('order_id', $order_id)->where('is_del', 0)->field('seckill_id,bargain_id,combination_id,id,order_id,pay_price,total_num,total_price,pay_postage,total_postage,paid,status,refund_status,pay_type,coupon_price,deduction_price,delivery_type,shipping_type')
            ->order('add_time DESC')->find();
        if (!$order)
            return false;
        else
            return self::tidyOrder($order->toArray(), true);

    }

    /**
     * 订单评价信息记录
     * @param $oid
     * @return StoreOrderStatus|\think\Model
     * @throws \Exception
     */
    public static function orderOver($oid)
    {
        $res = self::edit(['status' => '3'], $oid, 'id');
        if (!$res) exception('评价后置操作失败!');
        return StoreOrderStatus::status($oid, 'check_order_over', '用户评价');
    }

    /**
     * 设置订单产品评价完毕事件
     * @param $oid
     * @return StoreOrderStatus|\think\Model
     * @throws \Exception
     */
    public static function checkOrderOver($oid)
    {
        $uniqueList = StoreOrderCartInfo::where('oid', $oid)->column('unique', 'unique');
        //订单产品全部评价完成
        if (StoreProductReply::where('unique', 'IN', $uniqueList)->where('oid', $oid)->count() == count($uniqueList)) {
            event('StoreProductOrderOver', [$oid]);
            return self::orderOver($oid);
        }
    }


    public static function getOrderStatusNum($uid)
    {
        $noBuy = (int)self::where('uid', $uid)->where('paid', 0)->where('is_del', 0)->where('pay_type', '<>', 'offline')->count();
        $noPostageNoPink = (int)self::where('o.uid', $uid)->alias('o')->where('o.paid', 1)->where('o.pink_id', 0)->where('o.is_del', 0)->where('o.status', 0)->where('o.pay_type', '<>', 'offline')->count();
        $noPostageYesPink = (int)self::where('o.uid', $uid)->alias('o')->join('StorePink p', 'o.pink_id = p.id')->where('p.status', 2)->where('o.paid', 1)->where('o.is_del', 0)->where('o.status', 0)->where('o.pay_type', '<>', 'offline')->count();
        $noPostage = (int)bcadd($noPostageNoPink, $noPostageYesPink, 0);
        $noTake = (int)self::where('uid', $uid)->where('paid', 1)->where('is_del', 0)->where('status', 1)->where('pay_type', '<>', 'offline')->count();
        $noReply = (int)self::where('uid', $uid)->where('paid', 1)->where('is_del', 0)->where('status', 2)->count();
        $noPink = (int)self::where('o.uid', $uid)->alias('o')->join('StorePink p', 'o.pink_id = p.id')->where('p.status', 1)->where('o.paid', 1)->where('o.is_del', 0)->where('o.status', 0)->where('o.pay_type', '<>', 'offline')->count();
        $noRefund = (int)self::where('uid', $uid)->where('paid', 1)->where('is_del', 0)->where('refund_status', 'IN', '1,2')->count();
        return compact('noBuy', 'noPostage', 'noTake', 'noReply', 'noPink', 'noRefund');
    }

    /**
     * 购买商品赠送积分
     * @param $order
     * @return bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function gainUserIntegral($order)
    {
        if ($order['gain_integral'] > 0) {
            $userInfo = User::getUserInfo($order['uid']);
            BaseModel::beginTrans();
            $res1 = false != User::where('uid', $userInfo['uid'])->update(['integral' => bcadd($userInfo['integral'], $order['gain_integral'], 2)]);
            $res2 = false != UserBill::income('购买商品赠送积分', $order['uid'], 'integral', 'gain', $order['gain_integral'], $order['id'], $userInfo['integral'], '购买商品赠送' . floatval($order['gain_integral']) . '积分');
            $res = $res1 && $res2;
            BaseModel::checkTrans($res);
            return $res;
        }
        return true;
    }

    /**
     * 获取当前订单中有没有拼团存在
     * @param $pid
     * @return int|string
     */
    public static function getIsOrderPink($pid = 0, $uid = 0)
    {
        return self::where('uid', $uid)->where('pink_id', $pid)->where('refund_status', 0)->where('is_del', 0)->count();
    }

    /**
     * 获取order_id
     * @param $pid
     * @return mixed
     */
    public static function getStoreIdPink($pid = 0, $uid = 0)
    {
        return self::where('uid', $uid)->where('pink_id', $pid)->where('is_del', 0)->value('order_id');
    }

    /**
     * 删除当前用户拼团未支付的订单
     */
    public static function delCombination()
    {
        self::where('combination', '>', 0)->where('paid', 0)->where('uid', User::getActiveUid())->delete();
    }

    public static function getUserPrice($uid = 0)
    {
        if (!$uid) return 0;
        $price = self::where('paid', 1)->where('uid', $uid)->where('status', 2)->where('refund_status', 0)->column('pay_price', 'id');
        $count = 0;
        if ($price) {
            foreach ($price as $v) {
                $count = bcadd($count, $v, 2);
            }
        }
        return $count;
    }


    /**
     * 个人中心获取个人订单列表和订单搜索
     * @param int $uid 用户uid
     * @param int | string 查找订单类型
     * @param int $first 分页
     * @param int 每页显示多少条
     * @param string $search 订单号
     * @return array
     * */
    public static function getUserOrderSearchList($uid, $type, $page, $limit, $search)
    {
        if ($search) {
            $order = self::searchUserOrder($uid, $search) ?: [];
            $list = $order == false ? [] : [$order];
        } else {
            $list = self::getUserOrderList($uid, $type, $page, $limit);
        }
        foreach ($list as $k => $order) {
            $list[$k] = self::tidyOrder($order, true);
            if ($list[$k]['_status']['_type'] == 3) {
                foreach ($order['cartInfo'] ?: [] as $key => $product) {
                    $list[$k]['cartInfo'][$key]['is_reply'] = StoreProductReply::isReply($product['unique'], 'product');
                    $list[$k]['cartInfo'][$key]['add_time'] = isset($product['add_time']) ? date('Y-m-d H:i', $product['add_time']) : '时间错误';
                }
            }
        }
        return $list;
    }

    /**
     * 获取用户下级的订单
     * @param int $xuid 下级用户用户uid
     * @param int $uid 用户uid
     * @param int $type 订单类型
     * @param int $first 截取行数
     * @param int $limit 展示条数
     * @return array
     * */
    public static function getSubordinateOrderlist($xUid, $uid, $type, $first, $limit)
    {
        $list = [];
        if (!$xUid) {
            $arr = User::getOneSpreadUid($uid);
            foreach ($arr as $v) $list = StoreOrder::getUserOrderList($v, $type, $first, $limit);
        } else $list = self::getUserOrderList($xUid, $type, $first, $limit);
        foreach ($list as $k => $order) {
            $list[$k] = self::tidyOrder($order, true);
            if ($list[$k]['_status']['_type'] == 3) {
                foreach ($order['cartInfo'] ?: [] as $key => $product) {
                    $list[$k]['cartInfo'][$key]['is_reply'] = StoreProductReply::isReply($product['unique'], 'product');
                }
            }
        }
        return $list;
    }

    /**
     * 获取 今日 昨日 本月 订单金额
     * @return mixed
     */
    public static function getOrderTimeData()
    {
        $to_day = strtotime(date('Y-m-d'));//今日
        $pre_day = strtotime(date('Y-m-d', strtotime('-1 day')));//昨日
        $now_month = strtotime(date('Y-m'));//本月
        //今日成交额
//        $data['todayPrice'] = (float)number_format(self::where('is_del', 0)->where('pay_time', '>=', $to_day)->where('paid', 1)->where('refund_status', 0)->value('sum(pay_price)'), 2) ?? 0;
        $data['todayPrice'] = number_format(self::where('is_del', 0)->where('pay_time', '>=', $to_day)->where('paid', 1)->where('refund_status', 0)->value('sum(pay_price)'), 2) ?? 0;
        //今日订单数
        $data['todayCount'] = self::where('is_del', 0)->where('pay_time', '>=', $to_day)->where('paid', 1)->where('refund_status', 0)->count();
        //昨日成交额
        $data['proPrice'] = number_format(self::where('is_del', 0)->where('pay_time', '<', $to_day)->where('pay_time', '>=', $pre_day)->where('paid', 1)->where('refund_status', 0)->value('sum(pay_price)'), 2) ?? 0;
        //昨日订单数
        $data['proCount'] = self::where('is_del', 0)->where('pay_time', '<', $to_day)->where('pay_time', '>=', $pre_day)->where('paid', 1)->where('refund_status', 0)->count();
        //本月成交额
        $data['monthPrice'] = number_format(self::where('is_del', 0)->where('pay_time', '>=', $now_month)->where('paid', 1)->where('refund_status', 0)->value('sum(pay_price)'), 2) ?? 0;
        //本月订单数
        $data['monthCount'] = self::where('is_del', 0)->where('pay_time', '>=', $now_month)->where('paid', 1)->where('refund_status', 0)->count();
        return $data;
    }

    /**
     * 获取某个用户的订单统计数据
     * @param $uid
     * @return mixed
     */
    public static function getOrderData($uid)
    {
        //订单支付没有退款 数量
        $data['order_count'] = self::where('is_del', 0)->where('paid', 1)->where('uid', $uid)->where('refund_status', 0)->count();
        //订单支付没有退款 支付总金额
        $data['sum_price'] = self::where('is_del', 0)->where('paid', 1)->where('uid', $uid)->where('refund_status', 0)->sum('pay_price');
        //订单待支付 数量
        $data['unpaid_count'] = self::statusByWhere(0, $uid)->where('is_del', 0)->where('uid', $uid)->count();
        //订单待发货 数量
        $data['unshipped_count'] = self::statusByWhere(1, $uid)->where('is_del', 0)->where('uid', $uid)->count();
        //订单待收货 数量
        $data['received_count'] = self::statusByWhere(2, $uid)->where('is_del', 0)->where('uid', $uid)->count();
        //订单待评价 数量
        $data['evaluated_count'] = self::statusByWhere(3, $uid)->where('is_del', 0)->where('uid', $uid)->count();
        //订单已完成 数量
        $data['complete_count'] = self::statusByWhere(4, $uid)->where('is_del', 0)->where('uid', $uid)->count();
        //订单退款
        $data['refund_count'] = self::statusByWhere(-1, $uid)->where('is_del', 0)->where('uid', $uid)->count();
        return $data;
    }


    /**
     * 获取订单统计数据
     * @param $uid
     * @return mixed
     */
    public static function getOrderDataAdmin()
    {
        //订单支付没有退款 数量
        $data['order_count'] = self::where('is_del', 0)->where('paid', 1)->where('refund_status', 0)->count();
        //订单支付没有退款 支付总金额
        $data['sum_price'] = self::where('is_del', 0)->where('paid', 1)->where('refund_status', 0)->sum('pay_price');
        //订单待支付 数量
        $data['unpaid_count'] = self::statusByWhere(0, 0)->where('is_del', 0)->count();
        //订单待发货 数量
        $data['unshipped_count'] = self::statusByWhere(1, 0)->where('is_del', 0)->count();
        //订单待收货 数量
        $data['received_count'] = self::statusByWhere(2, 0)->where('is_del', 0)->count();
        //订单待评价 数量
        $data['evaluated_count'] = self::statusByWhere(3, 0)->where('is_del', 0)->count();
        //订单已完成 数量
        $data['complete_count'] = self::statusByWhere(4, 0)->where('is_del', 0)->count();
        //订单退款 数量
        $data['refund_count'] = self::statusByWhere(-3, 0)->where('is_del', 0)->count();
        return $data;
    }


    /**
     * 累计消费
     * @param $uid
     * @return float
     */
    public static function getOrderStatusSum($uid)
    {
        return self::where('uid', $uid)->where('is_del', 0)->where('paid', 1)->where('refund_status', 0)->sum('pay_price');
    }

    public static function getPinkOrderId($id)
    {
        return self::where('id', $id)->value('order_id');
    }

    /**
     * 未支付订单自动取消
     * @param int $limit 分页截取条数
     * @param string $prefid 缓存名称
     * @param int $expire 缓存时间
     * @return string|null
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public static function orderUnpaidCancel()
    {
        //系统预设取消订单时间段
        $keyValue = ['order_cancel_time', 'order_activity_time', 'order_bargain_time', 'order_seckill_time', 'order_pink_time'];

        //获取配置
        $systemValue = SystemConfigService::more($keyValue);
        //格式化数据
        $systemValue = self::setValeTime($keyValue, is_array($systemValue) ? $systemValue : []);
        //检查是否有未支付的订单   未支付查询条件
        $unPidCount = self::where('paid', 0)->where('pay_type', '<>', 'offline')->where('is_del', 0)->where('status', 0)->where('refund_status', 0)->count();
        if (!$unPidCount) return null;
        try {
            $res = true;
            // 未支付查询条件
            $orderList = self::where('paid', 0)->where('pay_type', '<>', 'offline')->where('is_del', 0)->where('status', 0)->where('refund_status', 0)->field('add_time,pink_id,order_id,seckill_id,bargain_id,combination_id,status,cart_id,use_integral,refund_status,uid,unique,back_integral,coupon_id,paid,is_del')->select();
            foreach ($orderList as $order) {
                if ($order['seckill_id']) {
                    //优先使用单独配置的过期时间
                    $order_seckill_time = $systemValue['order_seckill_time'] ? $systemValue['order_seckill_time'] : $systemValue['order_activity_time'];
                    $res = $res && self::RegressionAll($order_seckill_time, $order);
                    unset($order_seckill_time);
                } else if ($order['bargain_id']) {
                    $order_bargain_time = $systemValue['order_bargain_time'] ? $systemValue['order_bargain_time'] : $systemValue['order_activity_time'];
                    $res = $res && self::RegressionAll($order_bargain_time, $order);
                    unset($order_bargain_time);
                } else if ($order['pink_id'] || $order['combination_id']) {
                    $order_pink_time = $systemValue['order_pink_time'] ? $systemValue['order_pink_time'] : $systemValue['order_activity_time'];
                    $res = $res && self::RegressionAll($order_pink_time, $order);
                    unset($order_pink_time);
                } else {
                    $res = $res && self::RegressionAll($systemValue['order_cancel_time'], $order);
                }
            }
            if (!$res) throw new \Exception('更新错误');
            unset($orderList, $res, $pages);
            return null;
        } catch (PDOException $e) {
            Log::error('未支付自动取消时发生数据库查询错误，错误原因为：' . $e->getMessage());
            throw new \Exception($e->getMessage());
        } catch (\think\Exception $e) {
            Log::error('未支付自动取消时发生系统错误，错误原因为：' . $e->getMessage());
            throw new \Exception($e->getMessage());
        }

    }


    /**
     * 未支付订单超过预设时间回退所有,如果不设置未支付过期时间，将不取消订单
     * @param $time 预设时间
     * @param $order 订单详情
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected static function RegressionAll($time, $order)
    {
        if ($time == 0) return true;
        if (($order['add_time'] + bcmul($time, 3600, 0)) < time()) {
            $res1 = self::RegressionStock($order);
            $res2 = self::RegressionIntegral($order);
            $res3 = self::RegressionCoupon($order);
            $res = $res1 && $res2 && $res3;
            if ($res) $res = false !== self::where('order_id', $order['order_id'])->update(['is_del' => 1, 'mark' => '订单未支付已超过系统预设时间']);
            unset($res1, $res2, $res3);
            return $res;
        } else
            return true;
    }


    /**
     * 格式化数据
     * @param array $array 原本数据键
     * @param $value 需要格式化的数据
     * @param int $default 默认值
     * @return mixed
     */
    protected static function setValeTime(array $array, $value, $default = 0)
    {
        foreach ($array as $item) {
            if (!isset($value[$item]))
                $value[$item] = $default;
            else if (is_string($value[$item]))
                $value[$item] = (float)$value[$item];
        }
        return $value;
    }

    public static function getOrderTotalPrice($cartInfo)
    {
        $totalPrice = 0;
        foreach ($cartInfo as $cart) {
            $totalPrice = bcadd($totalPrice, bcmul($cart['cart_num'], $cart['truePrice'], 2), 2);
        }
        return $totalPrice;
    }

    public static function getOrderCostPrice($cartInfo)
    {
        $costPrice = 0;
        foreach ($cartInfo as $cart) {
            $costPrice = bcadd($costPrice, bcmul($cart['cart_num'], $cart['costPrice'], 2), 2);
        }
        return $costPrice;
    }

    public static function getCombinationOrderCostPrice($cartInfo)
    {
        $costPrice = 0;
        foreach ($cartInfo as $cart) {
            if ($cart['combination_id']) {
                $costPrice = bcadd($costPrice, bcmul($cart['cart_num'], StoreCombination::where('id', $cart['combination_id'])->value('price'), 2), 2);
            }
        }
        return (float)$costPrice;
    }

    public static function yueRefundAfter($order)
    {

    }

    /**
     * 获取余额支付的金额
     * @param $uid
     * @return float|int
     */
    public static function getOrderStatusYueSum($uid)
    {
        return self::where('uid', $uid)->where('is_del', 0)->where('is_del', 0)->where('pay_type', 'yue')->where('paid', 1)->sum('pay_price');
    }

    /**
     * 砍价支付成功订单数量
     * @param $bargain
     * @return int
     */
    public static function getBargainPayCount($bargain)
    {
        return self::where('bargain_id', $bargain)->where(['paid' => 1, 'refund_status' => 0])->count();
    }

    /**
     * 7天自动收货
     * @return bool
     */
    public static function startTakeOrder()
    {

        //7天前时间戳
        $systemDeliveryTime = SystemConfigService::get('system_delivery_time') ?? 0;
        //0为取消自动收货功能
        if ($systemDeliveryTime == 0) return true;
        $sevenDay = strtotime(date('Y-m-d H:i:s', strtotime('-' . $systemDeliveryTime . ' day')));
        $model = new self;
        $model = $model->alias('o');
        $model = $model->join('StoreOrderStatus s', 's.oid=o.id');
        $model = $model->where('o.paid', 1);
        $model = $model->where('s.change_type', 'delivery_goods');
        $model = $model->where('s.change_time', '<', $sevenDay);
        $model = $model->where('o.status', 1);
        $model = $model->where('o.refund_status', 0);
        $model = $model->where('o.is_del', 0);
        $orderInfo = $model->column('id', 'id');
        if (!count($orderInfo)) return true;
        $res = true;
        foreach ($orderInfo as $key => &$item) {
            $order = self::get($item);
            if ($order['status'] == 2) continue;
            if ($order['paid'] == 1 && $order['status'] == 1) $data['status'] = 2;
            else if ($order['pay_type'] == 'offline') $data['status'] = 2;
            else continue;
            if (!self::edit($data, $item, 'id')) continue;
            try {
                OrderRepository::storeProductOrderTakeDeliveryAdmin($order, $item);
                $res = $res && true;
            } catch (\Exception $e) {
                $res = $res && false;
            }
            $res = $res && StoreOrderStatus::status($item, 'take_delivery', '已收货[自动收货]');
        }

        if (!$res) {
            throw new \Exception('');
        }

    }

    /**
     * 获取订单信息
     * @param $id
     * @param string $field
     * @return array|null|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getOrderInfo($id, $field = 'order_id')
    {
        return self::where('id', $id)->field($field)->find();
    }

    /**
     * 订单每月统计数据
     * @param $page
     * @param $limit
     * @return array
     */
    public static function getOrderDataPriceCount($page, $limit, $start, $stop)
    {
        if (!$limit) return [];
        $model = new self;
        if ($start != '' && $stop != '') $model = $model->where('pay_time', '>', $start)->where('pay_time', '<=', $stop);
        $model = $model->field('sum(pay_price) as price,count(id) as count,FROM_UNIXTIME(pay_time, \'%m-%d\') as time');
        $model = $model->where('is_del', 0);
        $model = $model->where('paid', 1);
        $model = $model->where('refund_status', 0);
        $model = $model->group("FROM_UNIXTIME(pay_time, '%Y-%m-%d')");
        $model = $model->order('pay_time DESC');
        if ($page) $model = $model->page($page, $limit);
        return $model->select();
    }

    /**
     * 前台订单管理订单列表获取
     * @param $where
     * @return mixed
     */
    public static function orderList($where)
    {
        $model = self::getOrderWhere($where, self::alias('a')->join('user r', 'r.uid=a.uid', 'LEFT'), 'a.', 'r')->field('a.id,a.order_id,a.add_time,a.status,a.total_num,a.total_price,a.total_postage,a.pay_price,a.pay_postage,a.paid,a.refund_status,a.remark,a.pay_type')->where('is_del', 0);
        if ($where['order'] != '') {
            $model = $model->order(self::setOrder($where['order']));
        } else {
            $model = $model->order('a.id desc');
        }
        $data = ($data = $model->page((int)$where['page'], (int)$where['limit'])->select()) && count($data) ? $data->toArray() : [];
        return self::tidyAdminOrder($data);
    }

    /**
     * 前台订单管理 订单信息设置
     * @param $data
     * @param bool $status
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function tidyAdminOrder($data, $status = false)
    {
        foreach ($data as &$item) {
            $_info = StoreOrderCartInfo::where('oid', $item['id'])->field('cart_info')->select()->toArray();
            foreach ($_info as $k => $v) {
                if (!is_array($v['cart_info']))
                    $_info[$k]['cart_info'] = json_decode($v['cart_info'], true);
            }
            foreach ($_info as $k => $v) {
                unset($_info[$k]['cart_info']['type'], $_info[$k]['cart_info']['product_id'], $_info[$k]['cart_info']['combination_id'], $_info[$k]['cart_info']['seckill_id'], $_info[$k]['cart_info']['bargain_id'], $_info[$k]['cart_info']['bargain_id'], $_info[$k]['cart_info']['truePrice'], $_info[$k]['cart_info']['vip_truePrice'], $_info[$k]['cart_info']['trueStock'], $_info[$k]['cart_info']['costPrice'], $_info[$k]['cart_info']['productInfo']['id'], $_info[$k]['cart_info']['productInfo']['vip_price'], $_info[$k]['cart_info']['productInfo']['postage'], $_info[$k]['cart_info']['productInfo']['give_integral'], $_info[$k]['cart_info']['productInfo']['sales'], $_info[$k]['cart_info']['productInfo']['stock'], $_info[$k]['cart_info']['productInfo']['unit_name'], $_info[$k]['cart_info']['productInfo']['is_postage'], $_info[$k]['cart_info']['productInfo']['slider_image'], $_info[$k]['cart_info']['productInfo']['cost'], $_info[$k]['cart_info']['productInfo']['mer_id'], $_info[$k]['cart_info']['productInfo']['cate_id'], $_info[$k]['cart_info']['productInfo']['is_show'], $_info[$k]['cart_info']['productInfo']['store_info'], $_info[$k]['cart_info']['productInfo']['is_del'], $_info[$k]['cart_info']['is_pay'], $_info[$k]['cart_info']['is_del'], $_info[$k]['cart_info']['is_new'], $_info[$k]['cart_info']['add_time'], $_info[$k]['cart_info']['id'], $_info[$k]['cart_info']['uid'], $_info[$k]['cart_info']['product_attr_unique']);
                $_info[$k]['cart_info']['productInfo']['suk'] = '';
                if (isset($v['cart_info']['productInfo']['attrInfo'])) {
                    $_info[$k]['cart_info']['productInfo']['image'] = $_info[$k]['cart_info']['productInfo']['attrInfo']['image'];
                    $_info[$k]['cart_info']['productInfo']['price'] = $_info[$k]['cart_info']['productInfo']['attrInfo']['price'];
                    $_info[$k]['cart_info']['productInfo']['suk'] = $_info[$k]['cart_info']['productInfo']['attrInfo']['suk'];
                    unset($_info[$k]['cart_info']['productInfo']['attrInfo']);
                }
                if (!isset($v['cart_info']['productInfo']['ot_price'])) {
                    $_info[$k]['cart_info']['productInfo']['ot_price'] = $v['cart_info']['productInfo']['price'];
                }
            }
            $item['_info'] = $_info;
            $item['add_time'] = date('Y-m-d H:i:s', $item['add_time']);
//            if($item['pink_id'] || $item['combination_id']){
//                $pinkStatus = StorePink::where('order_id_key',$item['id'])->value('status');
//                switch ($pinkStatus){
//                    case 1:
//                        $item['pink_name'] = '[拼团订单]正在进行中';
//                        $item['color'] = '#f00';
//                        break;
//                    case 2:
//                        $item['pink_name'] = '[拼团订单]已完成';
//                        $item['color'] = '#00f';
//                        break;
//                    case 3:
//                        $item['pink_name'] = '[拼团订单]未完成';
//                        $item['color'] = '#f0f';
//                        break;
//                    default:
//                        $item['pink_name'] = '[拼团订单]历史订单';
//                        $item['color'] = '#457856';
//                        break;
//                }
//            }elseif ($item['seckill_id']){
//                $item['pink_name'] = '[秒杀订单]';
//                $item['color'] = '#32c5e9';
//            }elseif ($item['bargain_id']){
//                $item['pink_name'] = '[砍价订单]';
//                $item['color'] = '#12c5e9';
//            }else{
//                $item['pink_name'] = '[普通订单]';
//                $item['color'] = '#895612';
//            }
//            if($item['paid']==1){
//                switch ($item['pay_type']){
//                    case 'weixin':
//                        $item['pay_type_name']='微信支付';
//                        break;
//                    case 'yue':
//                        $item['pay_type_name']='余额支付';
//                        break;
//                    case 'offline':
//                        $item['pay_type_name']='线下支付';
//                        break;
//                    default:
//                        $item['pay_type_name']='其他支付';
//                        break;
//                }
//            }else{
//                switch ($item['pay_type']){
//                    default:
//                        $item['pay_type_name']='未支付';
//                        break;
//                    case 'offline':
//                        $item['pay_type_name']='线下支付';
//                        $item['pay_type_info']=1;
//                        break;
//                }
//            }

            if ($status) {
                $status = [];
                if (!$item['paid'] && $item['pay_type'] == 'offline' && !$item['status'] >= 2) {
                    $status['_type'] = 9;
                    $status['_title'] = '线下付款';
                    $status['_msg'] = '商家处理中,请耐心等待';
                    $status['_class'] = 'nobuy';
                } else if (!$item['paid']) {
                    $status['_type'] = 0;
                    $status['_title'] = '未支付';
                    //系统预设取消订单时间段
                    $keyValue = ['order_cancel_time', 'order_activity_time', 'order_bargain_time', 'order_seckill_time', 'order_pink_time'];
                    //获取配置
                    $systemValue = SystemConfigService::more($keyValue);
                    //格式化数据
                    $systemValue = self::setValeTime($keyValue, is_array($systemValue) ? $systemValue : []);
                    if ($item['pink_id'] || $item['combination_id']) {
                        $order_pink_time = $systemValue['order_pink_time'] ? $systemValue['order_pink_time'] : $systemValue['order_activity_time'];
                        $time = bcadd($item['add_time'], $order_pink_time * 3600, 0);
                        $status['_msg'] = '请在' . date('Y-m-d H:i:s', $time) . '前完成支付!';
                    } else if ($item['seckill_id']) {
                        $order_seckill_time = $systemValue['order_seckill_time'] ? $systemValue['order_seckill_time'] : $systemValue['order_activity_time'];
                        $time = bcadd($item['add_time'], $order_seckill_time * 3600, 0);
                        $status['_msg'] = '请在' . date('Y-m-d H:i:s', $time) . '前完成支付!';
                    } else if ($item['bargain_id']) {
                        $order_bargain_time = $systemValue['order_bargain_time'] ? $systemValue['order_bargain_time'] : $systemValue['order_activity_time'];
                        $time = bcadd($item['add_time'], $order_bargain_time * 3600, 0);
                        $status['_msg'] = '请在' . date('Y-m-d H:i:s', $time) . '前完成支付!';
                    } else {
                        $time = bcadd($item['add_time'], $systemValue['order_cancel_time'] * 3600, 0);
                        $status['_msg'] = '请在' . date('Y-m-d H:i:s', $time) . '前完成支付!';
                    }
                    $status['_class'] = 'nobuy';
                } else if ($item['refund_status'] == 1) {
                    $status['_type'] = -1;
                    $status['_title'] = '申请退款中';
                    $status['_msg'] = '商家审核中,请耐心等待';
                    $status['_class'] = 'state-sqtk';
                } else if ($item['refund_status'] == 2) {
                    $status['_type'] = -2;
                    $status['_title'] = '已退款';
                    $status['_msg'] = '已为您退款,感谢您的支持';
                    $status['_class'] = 'state-sqtk';
                } else if (!$item['status']) {
                    if ($item['pink_id']) {
                        if (StorePink::where('id', $item['pink_id'])->where('status', 1)->count()) {
                            $status['_type'] = 11;
                            $status['_title'] = '拼团中';
                            $status['_msg'] = '等待其他人参加拼团';
                            $status['_class'] = 'state-nfh';
                        } else {
                            $status['_type'] = 1;
                            $status['_title'] = '未发货';
                            $status['_msg'] = '商家未发货,请耐心等待';
                            $status['_class'] = 'state-nfh';
                        }
                    } else {
                        $status['_type'] = 1;
                        $status['_title'] = '未发货';
                        $status['_msg'] = '商家未发货,请耐心等待';
                        $status['_class'] = 'state-nfh';
                    }
                } else if ($item['status'] == 1) {
                    if ($item['delivery_type'] == 'send') {//TODO 送货
                        $status['_type'] = 2;
                        $status['_title'] = '待收货';
                        $status['_msg'] = date('m月d日H时i分', StoreOrderStatus::getTime($item['id'], 'delivery')) . '服务商已送货';
                        $status['_class'] = 'state-ysh';
                    } else {//TODO  发货
                        $status['_type'] = 2;
                        $status['_title'] = '待收货';
                        $status['_msg'] = date('m月d日H时i分', StoreOrderStatus::getTime($item['id'], 'delivery_goods')) . '服务商已发货';
                        $status['_class'] = 'state-ysh';
                    }
                } else if ($item['status'] == 2) {
                    $status['_type'] = 3;
                    $status['_title'] = '待评价';
                    $status['_msg'] = '已收货,快去评价一下吧';
                    $status['_class'] = 'state-ypj';
                } else if ($item['status'] == 3) {
                    $status['_type'] = 4;
                    $status['_title'] = '交易完成';
                    $status['_msg'] = '交易完成,感谢您的支持';
                    $status['_class'] = 'state-ytk';
                }
                if (isset($item['pay_type']))
                    $status['_payType'] = isset(self::$payType[$item['pay_type']]) ? self::$payType[$item['pay_type']] : '其他方式';
                if (isset($item['delivery_type']))
                    $status['_deliveryType'] = isset(self::$deliveryType[$item['delivery_type']]) ? self::$deliveryType[$item['delivery_type']] : '其他方式';
                $item['_status'] = $status;
            } else {
                if ($item['paid'] == 0 && $item['status'] == 0) {
                    $item['status_name'] = '未支付';
                } else if ($item['paid'] == 1 && $item['status'] == 0 && $item['refund_status'] == 0) {
                    $item['status_name'] = '未发货';
                } else if ($item['paid'] == 1 && $item['status'] == 1 && $item['refund_status'] == 0) {
                    $item['status_name'] = '待收货';
                } else if ($item['paid'] == 1 && $item['status'] == 2 && $item['refund_status'] == 0) {
                    $item['status_name'] = '待评价';
                } else if ($item['paid'] == 1 && $item['status'] == 3 && $item['refund_status'] == 0) {
                    $item['status_name'] = '已完成';
                }
            }
//            unset($item['refund_status']);
//            else if($item['paid']==1 && $item['refund_status']==1){
//                $item['status_name']=<<<HTML
//<b style="color:#f124c7">申请退款</b><br/>
//<span>退款原因：{$item['refund_reason_wap']}</span>
//HTML;
//            }else if($item['paid']==1 && $item['refund_status']==2){
//                $item['status_name']='已退款';
//            }
//            if($item['paid']==0 && $item['status']==0 && $item['refund_status']==0){
//                $item['_status']=1;
//            }else if($item['paid']==1 && $item['status']==0 && $item['refund_status']==0){
//                $item['_status']=2;
//            }else if($item['paid']==1 && $item['refund_status']==1){
//                $item['_status']=3;
//            }else if($item['paid']==1 && $item['status']==1 && $item['refund_status']==0){
//                $item['_status']=4;
//            }else if($item['paid']==1 && $item['status']==2 && $item['refund_status']==0){
//                $item['_status']=5;
//            }else if($item['paid']==1 && $item['status']==3 && $item['refund_status']==0){
//                $item['_status']=6;
//            }else if($item['paid']==1 && $item['refund_status']==2){
//                $item['_status']=7;
//            }
        }
        return $data;
    }

    /**
     * 处理where条件
     * @param $where
     * @param $model
     * @param string $aler
     * @param string $join
     * @return StoreOrder|null
     */
    public static function getOrderWhere($where, $model, $aler = '', $join = '')
    {
        if (isset($where['status']) && $where['status'] != '') $model = self::statusWhere($where['status'], $model, $aler);
        if (isset($where['is_del']) && $where['is_del'] != '' && $where['is_del'] != -1) $model = $model->where($aler . 'is_del', $where['is_del']);
        if (isset($where['combination_id'])) {
            if ($where['combination_id'] == '普通订单') {
                $model = $model->where($aler . 'combination_id', 0)->where($aler . 'seckill_id', 0)->where($aler . 'bargain_id', 0);
            }
            if ($where['combination_id'] == '拼团订单') {
                $model = $model->where($aler . 'combination_id', ">", 0)->where($aler . 'pink_id', ">", 0);
            }
            if ($where['combination_id'] == '秒杀订单') {
                $model = $model->where($aler . 'seckill_id', ">", 0);
            }
            if ($where['combination_id'] == '砍价订单') {
                $model = $model->where($aler . 'bargain_id', ">", 0);
            }
        }
        if (isset($where['type'])) {
            switch ($where['type']) {
                case 1:
                    $model = $model->where($aler . 'combination_id', 0)->where($aler . 'seckill_id', 0)->where($aler . 'bargain_id', 0);
                    break;
                case 2:
                    $model = $model->where($aler . 'combination_id', ">", 0);
                    break;
                case 3:
                    $model = $model->where($aler . 'seckill_id', ">", 0);
                    break;
                case 4:
                    $model = $model->where($aler . 'bargain_id', ">", 0);
                    break;
            }
        }

        if (isset($where['real_name']) && $where['real_name'] != '')
            $model = $model->where($aler . 'order_id|' . $aler . 'real_name|' . $aler . 'user_phone' . ($join ? '|' . $join . '.nickname|' . $join . '.uid' : ''), 'LIKE', "%$where[real_name]%");
        if (isset($where['data']) && $where['data'] !== '')
            $model = self::getModelTime($where, $model, $aler . 'add_time');
        return $model;
    }

    /**
     * 设置where条件
     * @param $status
     * @param null $model
     * @param string $alert
     * @return StoreOrder|null
     */
    public static function statusWhere($status, $model = null, $alert = '')
    {
        if ($model == null) $model = new self;
        if ('' === $status)
            return $model;
        else if ($status == 0)//未支付
            return $model->where($alert . 'paid', 0)->where($alert . 'status', 0)->where($alert . 'refund_status', 0);
        else if ($status == 1)//已支付 未发货
            return $model->where($alert . 'paid', 1)->where($alert . 'status', 0)->where($alert . 'refund_status', 0);
        else if ($status == 2)//已支付  待收货
            return $model->where($alert . 'paid', 1)->where($alert . 'status', 1)->where($alert . 'refund_status', 0);
        else if ($status == 3)// 已支付  已收货  待评价
            return $model->where($alert . 'paid', 1)->where($alert . 'status', 2)->where($alert . 'refund_status', 0);
        else if ($status == 4)// 交易完成
            return $model->where($alert . 'paid', 1)->where($alert . 'status', 3)->where($alert . 'refund_status', 0);
        else if ($status == -1)//退款中
            return $model->where($alert . 'paid', 1)->where($alert . 'refund_status', 1);
        else if ($status == -2)//已退款
            return $model->where($alert . 'paid', 1)->where($alert . 'refund_status', 2);
        else if ($status == -3)//退款
            return $model->where($alert . 'paid', 1)->where($alert . 'refund_status', 'in', '1,2');
        else
            return $model;
    }

    /**
     * 订单详情 管理员
     * @param $orderId
     * @param string $field
     * @return array|null|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getAdminOrderDetail($orderId, $field = '*')
    {
        return self::where('order_id', $orderId)->field($field)->find();
    }

    /**
     * 获取指定时间区间的支付金额 管理员
     * @param $start
     * @param $stop
     * @return float
     */
    public static function getOrderTimeBusinessVolumePrice($start, $stop)
    {
        return self::where('is_del', 0)->where('paid', 1)->where('refund_status', 0)->where('add_time', '>=', $start)->where('add_time', '<', $stop)->sum('pay_price');
    }

    /**
     * 获取指定时间区间的支付订单数量 管理员
     * @param $start
     * @param $stop
     * @return float
     */
    public static function getOrderTimeBusinessVolumeNumber($start, $stop)
    {
        return self::where('is_del', 0)->where('paid', 1)->where('refund_status', 0)->where('add_time', '>=', $start)->where('add_time', '<', $stop)->count();
    }

    /**
     * 获取当前时间到指定时间的支付金额 管理员
     * @param $start 开始时间
     * @param $stop  结束时间
     * @return mixed
     */
    public static function chartTimePrice($start, $stop)
    {
        $model = new self;
        $model = $model->field('sum(pay_price) as num,FROM_UNIXTIME(add_time, \'%Y-%m-%d\') as time');
        $model = $model->where('is_del', 0);
        $model = $model->where('paid', 1);
        $model = $model->where('refund_status', 0);
        $model = $model->where('add_time', '>=', $start);
        $model = $model->where('add_time', '<', $stop);
        $model = $model->group("FROM_UNIXTIME(add_time, '%Y-%m-%d')");
        $model = $model->order('add_time ASC');
        return $model->select();
    }

    /**
     * 获取当前时间到指定时间的支付订单数 管理员
     * @param $start 开始时间
     * @param $stop  结束时间
     * @return mixed
     */
    public static function chartTimeNumber($start, $stop)
    {
        $model = new self;
        $model = $model->field('count(id) as num,FROM_UNIXTIME(add_time, \'%Y-%m-%d\') as time');
        $model = $model->where('is_del', 0);
        $model = $model->where('paid', 1);
        $model = $model->where('refund_status', 0);
        $model = $model->where('add_time', '>=', $start);
        $model = $model->where('add_time', '<', $stop);
        $model = $model->group("FROM_UNIXTIME(add_time, '%Y-%m-%d')");
        $model = $model->order('add_time ASC');
        return $model->select();
    }

    /**
     * 修改支付方式为线下支付
     * @param $orderId
     * @return bool
     */
    public static function setOrderTypePayOffline($orderId)
    {
        return self::edit(['pay_type' => 'offline'], $orderId, 'order_id');
    }

    /**
     * 线下付款
     * @param $id
     * @return $this
     */
    public static function updateOffline($id)
    {
        $count = self::where('id', $id)->count();
        if (!$count) return self::setErrorInfo('订单不存在');
        $count = self::where('id', $id)->where('paid', 0)->count();
        if (!$count) return self::setErrorInfo('订单已支付');
        $res = self::where('id', $id)->update(['paid' => 1, 'pay_time' => time()]);
        return $res;
    }

    /**
     * 向创建订单10分钟未付款的用户发送短信
     */
    public static function sendTen()
    {
        $switch = sys_config('unpaid_order_switch') ? true : false;
        if($switch){
            $list = self::where('paid', 0)
                ->where('is_del', 0)
                ->where('is_system_del', 0)
                ->where('add_time', '>', time() - 900)
                ->where('add_time', '<', time() - 600)
                ->field('order_id,user_phone')
                ->select();
            if ($list) {
                $list = $list->toArray();
                foreach ($list as $phone) {
                    ShortLetterRepositories::send(true, $phone['user_phone'], ['order_id' => $phone['order_id']], 'ORDER_PAY_FALSE');
                }
            }
        }
    }

    public function productInfo()
    {
        return $this->hasMany(StoreProductReply::class, 'oid', 'id');
    }

    public static function setOrderProductReplyWhere($where)
    {
        $model = self::where('status', 3)->order('add_time desc')->whereIn('id', function ($query) {
            $query->name('store_order')->alias('o')->join('store_product_reply a', 'a.oid = o.id')->group('o.id')->field('o.id')->select();
        })->with('productInfo', function ($query) use ($where) {
            $alias = '';
            if (isset($where['title']) && $where['title'] != '')
                $query->where("{$alias}comment", 'LIKE', "%$where[title]%");
            if (isset($where['is_reply']) && $where['is_reply'] != '') {
                if ($where['is_reply'] >= 0) {
                    $query->where("{$alias}is_reply", $where['is_reply']);
                } else {
                    $query->where("{$alias}is_reply", '>', 0);
                }
            }
            if (isset($where['producr_id']) && $where['producr_id'] != 0)
                $query->where($alias . 'product_id', $where['producr_id']);
            $query->where("{$alias}is_del", 0);
        });
        return $model;

    }

    public static function getOrderProductReplyList($where)
    {
        $list = self::setOrderProductReplyWhere($where)->page((int)$where['message_page'], (int)$where['limit'])->select();
        $list = count($list) ? $list->toArray() : [];
        foreach ($list as $key => $item) {
            if (isset($item['productInfo']) && is_array($item['productInfo']) && count($item['productInfo'])) {
                foreach ($item['productInfo'] as $k => $v) {
                    if (!$v['nickname'] && $v['uid']) {
                        $v['nickname'] = User::where('uid', $v['uid'])->value('nickname');
                        $v['avatar'] = User::where('uid', $v['uid'])->value('avatar');
                    }
                    $v['image'] = '';
                    $v['store_name'] = '';
                    if ($v['product_id']) {
                        $product = StoreProduct::where('id', $v['product_id'])->field(['image', 'store_name'])->find();
                        if ($product) {
                            $v['image'] = $product['image'];
                            $v['store_name'] = $product['store_name'];
                        }
                    }
                    $list[$key]['productInfo'][$k] = $v;
                }
            }
        }
        $count = self::setOrderProductReplyWhere($where)->count();
        return compact('list', 'count');
    }

    /**
     * 验证加入购物车、生成订单时商品得库存以及是否能购买情况
     * @param $uid
     * @param $product_id
     * @param int $cart_num
     * @param string $product_attr_unique
     * @param int $combination_id
     * @param int $seckill_id
     * @param int $bargain_id
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function checkProductStock($uid, $product_id, $cart_num = 1, $product_attr_unique = '', $combination_id = 0, $seckill_id = 0, $bargain_id = 0)
    {
        if ($cart_num < 1) $cart_num = 1;
        if (!$product_attr_unique) return self::setErrorInfo('请选择商品属性');
        if ($seckill_id) {
            if (!StoreSeckill::isSeckillEnd($seckill_id))
                return self::setErrorInfo('活动已结束');
            $StoreSeckillinfo = StoreSeckill::getValidProduct($seckill_id);
            if (!$StoreSeckillinfo)
                return self::setErrorInfo('该产品已下架或删除');
            $userbuycount = StoreOrder::where('uid', $uid)->where('paid', 1)->where('seckill_id', $seckill_id)->sum('total_num');
            if ($StoreSeckillinfo['num'] <= $userbuycount || $StoreSeckillinfo['num'] < $cart_num)
                return self::setErrorInfo('每人限购' . $StoreSeckillinfo['num'] . '件');
            $res = StoreProductAttrValue::where('product_id', $seckill_id)->where('unique', $product_attr_unique)->where('type', 1)->field('suk,quota')->find();
            if ($cart_num > $res['quota'])
                return self::setErrorInfo('该产品库存不足' . $cart_num);
            $product_stock = StoreProductAttrValue::where('product_id', $StoreSeckillinfo['product_id'])->where('suk', $res['suk'])->where('type', 0)->value('stock');
            if ($product_stock < $cart_num)
                return self::setErrorInfo('该产品库存不足' . $cart_num);
        } elseif ($bargain_id) {
            if (!StoreBargain::validBargain($bargain_id))
                return self::setErrorInfo('该产品已下架或删除');
            $StoreBargainInfo = StoreBargain::getBargain($bargain_id);
            $res = StoreProductAttrValue::where('product_id', $bargain_id)->where('type', 2)->field('suk,quota')->find();
            if ($cart_num > $res['quota'])
                return self::setErrorInfo('该产品库存不足' . $cart_num);
            $product_stock = StoreProductAttrValue::where('product_id', $StoreBargainInfo['product_id'])->where('suk', $res['suk'])->where('type', 0)->value('stock');
            if ($product_stock < $cart_num)
                return self::setErrorInfo('该产品库存不足' . $cart_num);
        } elseif ($combination_id) {//拼团
            $StoreCombinationInfo = StoreCombination::getCombinationOne($combination_id);
            if (!$StoreCombinationInfo)
                return self::setErrorInfo('该产品已下架或删除');
            $userbuycount = StoreOrder::where('uid', $uid)->where('paid', 1)->where('combination_id', $combination_id)->count();
            if ($StoreCombinationInfo['num'] <= $userbuycount || $StoreCombinationInfo['num'] < $cart_num)
                return self::setErrorInfo('每人限购' . $StoreCombinationInfo['num'] . '件');
            $res = StoreProductAttrValue::where('product_id', $combination_id)->where('unique', $product_attr_unique)->where('type', 3)->field('suk,quota')->find();
            if ($cart_num > $res['quota'])
                return self::setErrorInfo('该产品库存不足' . $cart_num);
            $product_stock = StoreProductAttrValue::where('product_id', $StoreCombinationInfo['product_id'])->where('suk', $res['suk'])->where('type', 0)->value('stock');
            if ($product_stock < $cart_num)
                return self::setErrorInfo('该产品库存不足' . $cart_num);
        } else {
            if (!StoreProduct::isValidProduct($product_id))
                return self::setErrorInfo('该产品已下架或删除');
            if (!StoreProductAttr::issetProductUnique($product_id, $product_attr_unique))
                return self::setErrorInfo('请选择有效的产品属性');
            if (StoreProduct::getProductStock($product_id, $product_attr_unique) < $cart_num)
                return self::setErrorInfo('该产品库存不足' . $cart_num);
        }
        return true;
    }

}
