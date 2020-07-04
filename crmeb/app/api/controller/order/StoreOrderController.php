<?php

namespace app\api\controller\order;

use app\admin\model\system\{
    SystemAttachment, ShippingTemplates
};
use app\admin\model\user\User;
use app\models\routine\RoutineFormId;
use crmeb\repositories\OrderRepository;
use app\models\store\{
    StoreBargainUser,
    StoreCart,
    StoreCoupon,
    StoreCouponIssue,
    StoreCouponUser,
    StoreOrder,
    StoreOrderCartInfo,
    StoreOrderStatus,
    StorePink,
    StoreProductReply,
    StoreSeckill
};
use app\models\system\SystemStore;
use app\models\user\UserAddress;
use app\models\user\UserLevel;
use app\Request;
use think\facade\Cache;
use crmeb\services\{
    CacheService,
    ExpressService,
    SystemConfigService,
    UtilService
};

/**
 * 订单类
 * Class StoreOrderController
 * @package app\api\controller\order
 */
class StoreOrderController
{
    /**
     * 订单确认
     * @param Request $request
     * @return mixed
     */
    public function confirm(Request $request)
    {
        $temp = ShippingTemplates::get(1);
        if (!$temp) return app('json')->fail('默认模板未配置，无法下单');
        list($cartId) = UtilService::postMore(['cartId'], $request, true);
        if (!is_string($cartId) || !$cartId) return app('json')->fail('请提交购买的商品');
        $uid = $request->uid();
        $cartGroup = StoreCart::getUserProductCartList($uid, $cartId, 1);
        if (count($cartGroup['invalid'])) return app('json')->fail($cartGroup['invalid'][0]['productInfo']['store_name'] . '已失效!');
        if (!$cartGroup['valid']) return app('json')->fail('请提交购买的商品');
        $cartInfo = $cartGroup['valid'];
        $addr = UserAddress::where('uid', $uid)->where('is_default', 1)->find();
        $priceGroup = StoreOrder::getOrderPriceGroup($cartInfo, $addr);
        if ($priceGroup === false) {
            return app('json')->fail(StoreOrder::getErrorInfo('运费模板不存在'));
        }
        $other = [
            'offlinePostage' => sys_config('offline_postage'),
            'integralRatio' => sys_config('integral_ratio')
        ];
        $usableCoupons = StoreCouponUser::getUsableCouponList($uid, $cartGroup, $priceGroup['totalPrice']);
        $usableCoupon = isset($usableCoupons[0]) ? $usableCoupons[0] : null;
        $cartIdA = explode(',', $cartId);
        $seckill_id = 0;
        $combination_id = 0;
        $bargain_id = 0;
        if (count($cartIdA) == 1) {
            $seckill_id = StoreCart::where('id', $cartId)->value('seckill_id');
            $combination_id = StoreCart::where('id', $cartId)->value('combination_id');
            $bargain_id = StoreCart::where('id', $cartId)->value('bargain_id');
        }
        $data['deduction'] = $seckill_id || $combination_id || $bargain_id;
        $data['usableCoupon'] = $usableCoupon;
        $data['addressInfo'] = UserAddress::getUserDefaultAddress($uid);
        $data['seckill_id'] = $seckill_id;
        $data['combination_id'] = $combination_id;
        $data['bargain_id'] = $bargain_id;
        $data['cartInfo'] = $cartInfo;
        $data['priceGroup'] = $priceGroup;
        $data['orderKey'] = StoreOrder::cacheOrderInfo($uid, $cartInfo, $priceGroup, $other);
        $data['offlinePostage'] = $other['offlinePostage'];
        $vipId = UserLevel::getUserLevel($uid);
        $user = $request->user();
        if (isset($user['pwd'])) unset($user['pwd']);
        $user['vip'] = $vipId !== false ? true : false;
        if ($user['vip']) {
            $user['vip_id'] = $vipId;
            $user['discount'] = UserLevel::getUserLevelInfo($vipId, 'discount');
        }
        $data['userInfo'] = $user;
        $data['integralRatio'] = $other['integralRatio'];
        $data['offline_pay_status'] = (int)sys_config('offline_pay_status') ?? (int)2;
        $data['store_self_mention'] = (int)sys_config('store_self_mention') ?? 0;//门店自提是否开启
        $data['system_store'] = ($res = SystemStore::getStoreDispose()) ? $res : [];//门店信息
        return app('json')->successful($data);
    }

    /**
     * 计算订单金额
     * @param Request $request
     * @param $key
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function computedOrder(Request $request, $key)
    {

//        $priceGroup = StoreOrder::getOrderPriceGroup($cartInfo);
        if (!$key) return app('json')->fail('参数错误!');
        $uid = $request->uid();
        if (StoreOrder::be(['order_id|unique' => $key, 'uid' => $uid, 'is_del' => 0]))
            return app('json')->status('extend_order', '订单已生成', ['orderId' => $key, 'key' => $key]);
        list($addressId, $couponId, $payType, $useIntegral, $mark, $combinationId, $pinkId, $seckill_id, $formId, $bargainId, $shipping_type) = UtilService::postMore([
            'addressId', 'couponId', ['payType', 'yue'], ['useIntegral', 0], 'mark', ['combinationId', 0], ['pinkId', 0], ['seckill_id', 0], ['formId', ''], ['bargainId', ''],
            ['shipping_type', 1],
        ], $request, true);
        $payType = strtolower($payType);
        if ($bargainId) {
            $bargainUserTableId = StoreBargainUser::getBargainUserTableId($bargainId, $uid);//TODO 获取用户参与砍价表编号
            if (!$bargainUserTableId)
                return app('json')->fail('砍价失败');
            $status = StoreBargainUser::getBargainUserStatusEnd($bargainUserTableId);
            if ($status == 3)
                return app('json')->fail('砍价已支付');
            StoreBargainUser::setBargainUserStatus($bargainId, $uid); //修改砍价状态
        }
        if ($pinkId) {
            $cache_pink = Cache::get(md5('store_pink_'.$pinkId));
            if($cache_pink && bcsub($cache_pink['people'], $cache_pink['now_people'], 0) <= 0){
                return app('json')->status('ORDER_EXIST', '订单生成失败，该团人员已满', ['orderId' => StoreOrder::getStoreIdPink($pinkId, $request->uid())]);
            }
            if (StorePink::getIsPinkUid($pinkId, $request->uid()))
                return app('json')->status('ORDER_EXIST', '订单生成失败，你已经在该团内不能再参加了', ['orderId' => StoreOrder::getStoreIdPink($pinkId, $request->uid())]);
            if (StoreOrder::getIsOrderPink($pinkId, $request->uid()))
                return app('json')->status('ORDER_EXIST', '订单生成失败，你已经参加该团了，请先支付订单', ['orderId' => StoreOrder::getStoreIdPink($pinkId, $request->uid())]);
        }
        $priceGroup = StoreOrder::cacheKeyCreateOrder($request->uid(), $key, $addressId, $payType, (int)$useIntegral, $couponId, $mark, $combinationId, $pinkId, $seckill_id, $bargainId, true, 0, $shipping_type);
        if ($priceGroup)
            return app('json')->status('NONE', 'ok', $priceGroup);
        else
            return app('json')->fail(StoreOrder::getErrorInfo('计算失败'));
    }

    /**
     * 订单创建
     * @param Request $request
     * @param $key
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function create(Request $request, $key)
    {
        if (!$key) return app('json')->fail('参数错误!');
        $uid = $request->uid();
        if (StoreOrder::be(['order_id|unique' => $key, 'uid' => $uid, 'is_del' => 0]))
            return app('json')->status('extend_order', '订单已生成', ['orderId' => $key, 'key' => $key]);
        list($addressId, $couponId, $payType, $useIntegral, $mark, $combinationId, $pinkId, $seckill_id, $formId, $bargainId, $from, $shipping_type, $real_name, $phone, $storeId) = UtilService::postMore([
            'addressId', 'couponId', 'payType', ['useIntegral', 0], 'mark', ['combinationId', 0], ['pinkId', 0], ['seckill_id', 0], ['formId', ''], ['bargainId', ''], ['from', 'weixin'],
            ['shipping_type', 1], ['real_name', ''], ['phone', ''], ['store_id', 0]
        ], $request, true);
        $payType = strtolower($payType);
        if ($bargainId) {
            $bargainUserTableId = StoreBargainUser::getBargainUserTableId($bargainId, $uid);//TODO 获取用户参与砍价表编号
            if (!$bargainUserTableId)
                return app('json')->fail('砍价失败');
            $status = StoreBargainUser::getBargainUserStatusEnd($bargainUserTableId);
            if ($status == 3)
                return app('json')->fail('砍价已支付');
            StoreBargainUser::setBargainUserStatus($bargainId, $uid); //修改砍价状态
        }
        if ($pinkId) {
            $cache_pink = Cache::get(md5('store_pink_'.$pinkId));
            if($cache_pink && bcsub($cache_pink['people'], $cache_pink['now_people'], 0) <= 0){
                return app('json')->status('ORDER_EXIST', '订单生成失败，该团人员已满', ['orderId' => StoreOrder::getStoreIdPink($pinkId, $request->uid())]);
            }
            if (StorePink::getIsPinkUid($pinkId, $request->uid()))
                return app('json')->status('ORDER_EXIST', '订单生成失败，你已经在该团内不能再参加了', ['orderId' => StoreOrder::getStoreIdPink($pinkId, $request->uid())]);
            if (StoreOrder::getIsOrderPink($pinkId, $request->uid()))
                return app('json')->status('ORDER_EXIST', '订单生成失败，你已经参加该团了，请先支付订单', ['orderId' => StoreOrder::getStoreIdPink($pinkId, $request->uid())]);
        }
        $isChannel = 1;
        if ($from == 'weixin')
            $isChannel = 0;
        elseif ($from == 'weixinh5')
            $isChannel = 2;
        $order = StoreOrder::cacheKeyCreateOrder($request->uid(), $key, $addressId, $payType, (int)$useIntegral, $couponId, $mark, $combinationId, $pinkId, $seckill_id, $bargainId, false, $isChannel, $shipping_type, $real_name, $phone, $storeId);
        if ($order === false) return app('json')->fail(StoreOrder::getErrorInfo('订单生成失败'));
        $orderId = $order['order_id'];
        $info = compact('orderId', 'key');
        if ($orderId) {
            event('OrderCreated', [$order]); //订单创建成功事件
//            event('ShortMssageSend', [$orderId, 'AdminPlaceAnOrder']);//发送管理员通知
            switch ($payType) {
                case "weixin":
                    $orderInfo = StoreOrder::where('order_id', $orderId)->find();
                    if (!$orderInfo || !isset($orderInfo['paid'])) return app('json')->fail('支付订单不存在!');
                    $orderInfo = $orderInfo->toArray();
                    if ($orderInfo['paid']) return app('json')->fail('支付已支付!');
                    //支付金额为0
                    if (bcsub((float)$orderInfo['pay_price'], 0, 2) <= 0) {
                        //创建订单jspay支付
                        $payPriceStatus = StoreOrder::jsPayPrice($orderId, $uid, $formId);
                        if ($payPriceStatus)//0元支付成功
                            return app('json')->status('success', '微信支付成功', $info);
                        else
                            return app('json')->status('pay_error', StoreOrder::getErrorInfo());
                    } else {
                        try {
                            if ($from == 'routine') {
                                $jsConfig = OrderRepository::jsPay($orderId); //创建订单jspay
                            } else if ($from == 'weixinh5') {
                                $jsConfig = OrderRepository::h5Pay($orderId);
                            } else {
                                $jsConfig = OrderRepository::wxPay($orderId);
                            }
                        } catch (\Exception $e) {
                            return app('json')->status('pay_error', $e->getMessage(), $info);
                        }
                        $info['jsConfig'] = $jsConfig;
                        if ($from == 'weixinh5') {
                            return app('json')->status('wechat_h5_pay', '订单创建成功', $info);
                        } else {
                            return app('json')->status('wechat_pay', '订单创建成功', $info);
                        }
                    }
                    break;
                case 'yue':
                    if (StoreOrder::yuePay($orderId, $request->uid(), $formId))
                        return app('json')->status('success', '余额支付成功', $info);
                    else {
                        $errorinfo = StoreOrder::getErrorInfo();
                        if (is_array($errorinfo))
                            return app('json')->status($errorinfo['status'], $errorinfo['msg'], $info);
                        else
                            return app('json')->status('pay_error', $errorinfo);
                    }
                    break;
                case 'offline':
                    return app('json')->status('success', '订单创建成功', $info);
                    break;
            }
        } else return app('json')->fail(StoreOrder::getErrorInfo('订单生成失败!'));
    }

    /**
     * 订单 再次下单
     * @param Request $request
     * @return mixed
     */
    public function again(Request $request)
    {
        list($uni) = UtilService::postMore([
            ['uni', ''],
        ], $request, true);
        if (!$uni) return app('json')->fail('参数错误!');
        $order = StoreOrder::getUserOrderDetail($request->uid(), $uni);
        if (!$order) return app('json')->fail('订单不存在!');
        $order = StoreOrder::tidyOrder($order, true);
        $res = [];
        foreach ($order['cartInfo'] as $v) {
            if ($v['combination_id']) return app('json')->fail('拼团产品不能再来一单，请在拼团产品内自行下单!');
            else if ($v['bargain_id']) return app('json')->fail('砍价产品不能再来一单，请在砍价产品内自行下单!');
            else if ($v['seckill_id']) return app('json')->ail('秒杀产品不能再来一单，请在秒杀产品内自行下单!');
            else $res[] = StoreCart::setCart($request->uid(), $v['product_id'], $v['cart_num'], isset($v['productInfo']['attrInfo']['unique']) ? $v['productInfo']['attrInfo']['unique'] : '', 'product', 0, 0);
        }
        $cateId = [];
        foreach ($res as $v) {
            if (!$v) return app('json')->fail('再来一单失败，请重新下单!');
            $cateId[] = $v['id'];
        }
        event('OrderCreateAgain', implode(',', $cateId));
        return app('json')->successful('ok', ['cateId' => implode(',', $cateId)]);
    }


    /**
     * 订单支付
     * @param Request $request
     * @return mixed
     */
    public function pay(Request $request)
    {
        list($uni, $paytype, $from) = UtilService::postMore([
            ['uni', ''],
            ['paytype', 'weixin'],
            ['from', 'weixin']
        ], $request, true);
        if (!$uni) return app('json')->fail('参数错误!');
        $order = StoreOrder::getUserOrderDetail($request->uid(), $uni);
        if (!$order)
            return app('json')->fail('订单不存在!');
        if ($order['paid'])
            return app('json')->fail('该订单已支付!');
        if ($order['pink_id']){
            $cache_pink = Cache::get(md5('store_pink_'.$order['pink_id']));
            if(StorePink::isPinkStatus($order['pink_id'])  || ($cache_pink && bcsub($cache_pink['people'], $cache_pink['now_people'], 0) <= 0)){
                return app('json')->fail('该订单已失效!');
            }
        }
        if ($from == 'weixin') {//0
            if (in_array($order->is_channel, [1, 2]))
                $order['order_id'] = mt_rand(100, 999) . '_' . $order['order_id'];
        }
        if ($from == 'weixinh5') {//2
            if (in_array($order->is_channel, [0, 1]))
                $order['order_id'] = mt_rand(100, 999) . '_' . $order['order_id'];
        }
        if ($from == 'routine') {//1
            if (in_array($order->is_channel, [0, 2]))
                $order['order_id'] = mt_rand(100, 999) . '_' . $order['order_id'];
        }

        $order['pay_type'] = $paytype; //重新支付选择支付方式
        switch ($order['pay_type']) {
            case 'weixin':
                try {
                    if ($from == 'routine') {
                        $jsConfig = OrderRepository::jsPay($order); //订单列表发起支付
                    } else if ($from == 'weixinh5') {
                        $jsConfig = OrderRepository::h5Pay($order);
                    } else {
                        $jsConfig = OrderRepository::wxPay($order);
                    }
                } catch (\Exception $e) {
                    return app('json')->fail($e->getMessage());
                }
                if ($from == 'weixinh5') {
                    return app('json')->status('wechat_h5_pay', ['jsConfig' => $jsConfig, 'order_id' => $order['order_id']]);
                } else {
                    return app('json')->status('wechat_pay', ['jsConfig' => $jsConfig, 'order_id' => $order['order_id']]);
                }
                break;
            case 'yue':
                if (StoreOrder::yuePay($order['order_id'], $request->uid()))
                    return app('json')->status('success', '余额支付成功');
                else {
                    $error = StoreOrder::getErrorInfo();
                    return app('json')->fail(is_array($error) && isset($error['msg']) ? $error['msg'] : $error);
                }
                break;
            case 'offline':
                StoreOrder::createOrderTemplate($order);
                if (StoreOrder::setOrderTypePayOffline($order['order_id']))
                    return app('json')->status('success', '订单创建成功');
                else
                    return app('json')->status('success', '支付失败');
                break;
        }
        return app('json')->fail('支付方式错误');
    }

    /**
     * 订单列表
     * @param Request $request
     * @return mixed
     */
    public function lst(Request $request)
    {
        list($type, $page, $limit, $search) = UtilService::getMore([
            ['type', ''],
            ['page', 0],
            ['limit', ''],
            ['search', ''],
        ], $request, true);
        return app('json')->successful(StoreOrder::getUserOrderSearchList($request->uid(), $type, $page, $limit, $search));
    }

    /**
     * 订单详情
     * @param Request $request
     * @param $uni
     * @return mixed
     */
    public function detail(Request $request, $uni)
    {
        if (!strlen(trim($uni))) return app('json')->fail('参数错误');
        $order = StoreOrder::getUserOrderDetail($request->uid(), $uni);
        if (!$order) return app('json')->fail('订单不存在');
        $order = $order->toArray();
        //是否开启门店自提
        $store_self_mention = sys_config('store_self_mention');
        //关闭门店自提后 订单隐藏门店信息
        if ($store_self_mention == 0) $order['shipping_type'] = 1;
        if ($order['verify_code']) {
            $verify_code = $order['verify_code'];
            $verify[] = substr($verify_code, 0, 4);
            $verify[] = substr($verify_code, 4, 4);
            $verify[] = substr($verify_code, 8);
            $order['_verify_code'] = implode(' ', $verify);
        }
        $order['add_time_y'] = date('Y-m-d', $order['add_time']);
        $order['add_time_h'] = date('H:i:s', $order['add_time']);
        $order['system_store'] = SystemStore::getStoreDispose($order['store_id']);
        if ($order['shipping_type'] === 2 && $order['verify_code']) {
            $name = $order['verify_code'] . '.jpg';
            $imageInfo = SystemAttachment::getInfo($name, 'name');
            $siteUrl = sys_config('site_url');
            if (!$imageInfo) {
                $imageInfo = UtilService::getQRCodePath($order['verify_code'], $name);
                if (is_array($imageInfo)) {
                    SystemAttachment::attachmentAdd($imageInfo['name'], $imageInfo['size'], $imageInfo['type'], $imageInfo['dir'], $imageInfo['thumb_path'], 1, $imageInfo['image_type'], $imageInfo['time'], 2);
                    $url = $imageInfo['dir'];
                } else
                    $url = '';
            } else $url = $imageInfo['att_dir'];
            if (isset($imageInfo['image_type']) && $imageInfo['image_type'] == 1) $url = $siteUrl . $url;
            $order['code'] = $url;
        }
        $order['mapKey'] = sys_config('tengxun_map_key');
        return app('json')->successful('ok', StoreOrder::tidyOrder($order, true, true));
    }

    /**
     * 订单删除
     * @param Request $request
     * @return mixed
     */
    public function del(Request $request)
    {
        list($uni) = UtilService::postMore([
            ['uni', ''],
        ], $request, true);
        if (!$uni) return app('json')->fail('参数错误!');
        $res = StoreOrder::removeOrder($uni, $request->uid());
        if ($res)
            return app('json')->successful();
        else
            return app('json')->fail(StoreOrder::getErrorInfo());
    }

    /**
     * 订单收货
     * @param Request $request
     * @return mixed
     */
    public function take(Request $request)
    {
        list($uni) = UtilService::postMore([
            ['uni', ''],
        ], $request, true);
        if (!$uni) return app('json')->fail('参数错误!');
        $res = StoreOrder::takeOrder($uni, $request->uid());
        if ($res) {
            $order_info = StoreOrder::where('order_id', $uni)->find();
            $gain_integral = intval($order_info['gain_integral']);

            $gain_coupon = StoreCouponIssue::alias('a')
                ->join('store_coupon b', 'a.cid = b.id')
                ->where('a.status', 1)
                ->where('a.is_full_give', 1)
                ->where('a.is_del', 0)
                ->where('a.full_reduction', '<=', $order_info['total_price'])
                ->sum('b.coupon_price');

            return app('json')->successful(['gain_integral' => $gain_integral, 'gain_coupon' => $gain_coupon]);
        } else
            return app('json')->fail(StoreOrder::getErrorInfo());
    }


    /**
     * 订单 查看物流
     * @param Request $request
     * @param $uni
     * @return mixed
     */
    public function express(Request $request, $uni)
    {
        if (!$uni || !($order = StoreOrder::getUserOrderDetail($request->uid(), $uni))) return app('json')->fail('查询订单不存在!');
        if ($order['delivery_type'] != 'express' || !$order['delivery_id']) return app('json')->fail('该订单不存在快递单号!');
        $cacheName = $uni . $order['delivery_id'];
        $result = CacheService::get($cacheName, null);
        if ($result === NULL) {
            $result = ExpressService::query($order['delivery_id']);
            if (is_array($result) &&
                isset($result['result']) &&
                isset($result['result']['deliverystatus']) &&
                $result['result']['deliverystatus'] >= 3)
                $cacheTime = 0;
            else
                $cacheTime = 1800;
            CacheService::set($cacheName, $result, $cacheTime);
        }
        $orderInfo = [];
        $cartInfo = StoreOrderCartInfo::where('oid', $order['id'])->column('cart_info', 'unique') ?? [];
        $info = [];
        $cartNew = [];
        foreach ($cartInfo as $k => $cart) {
            $cart = json_decode($cart, true);
            $cartNew['cart_num'] = $cart['cart_num'];
            $cartNew['truePrice'] = $cart['truePrice'];
            $cartNew['productInfo']['image'] = $cart['productInfo']['image'];
            $cartNew['productInfo']['store_name'] = $cart['productInfo']['store_name'];
            $cartNew['productInfo']['unit_name'] = $cart['productInfo']['unit_name'] ?? '';
            array_push($info, $cartNew);
            unset($cart);
        }
        $orderInfo['delivery_id'] = $order['delivery_id'];
        $orderInfo['delivery_name'] = $order['delivery_name'];
        $orderInfo['delivery_type'] = $order['delivery_type'];
        $orderInfo['cartInfo'] = $info;
        return app('json')->successful(['order' => $orderInfo, 'express' => $result ? $result : []]);
    }

    /**
     * 订单评价
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function comment(Request $request)
    {
        $group = UtilService::postMore([
            ['unique', ''], ['comment', ''], ['pics', ''], ['product_score', 5], ['service_score', 5]
        ], $request);
        $unique = $group['unique'];
        unset($group['unique']);
        if (!$unique) return app('json')->fail('参数错误!');
        $cartInfo = StoreOrderCartInfo::where('unique', $unique)->find();
        $uid = $request->uid();
        $user_info = User::get($uid);
        $group['nickname'] = $user_info['nickname'];
        $group['avatar'] = $user_info['avatar'];
        if (!$cartInfo) return app('json')->fail('评价产品不存在!');
        $orderUid = StoreOrder::getOrderInfo($cartInfo['oid'], 'uid')['uid'];
        if ($uid != $orderUid) return app('json')->fail('评价产品不存在!');
        if (StoreProductReply::be(['oid' => $cartInfo['oid'], 'unique' => $unique]))
            return app('json')->fail('该产品已评价!');
        $group['comment'] = htmlspecialchars(trim($group['comment']));
        if ($group['product_score'] < 1) return app('json')->fail('请为产品评分');
        else if ($group['service_score'] < 1) return app('json')->fail('请为商家服务评分');
        if ($cartInfo['cart_info']['combination_id']) $productId = $cartInfo['cart_info']['product_id'];
        else if ($cartInfo['cart_info']['seckill_id']) $productId = $cartInfo['cart_info']['product_id'];
        else if ($cartInfo['cart_info']['bargain_id']) $productId = $cartInfo['cart_info']['product_id'];
        else $productId = $cartInfo['product_id'];
        if ($group['pics']) $group['pics'] = json_encode(is_array($group['pics']) ? $group['pics'] : explode(',', $group['pics']));
        $group = array_merge($group, [
            'uid' => $uid,
            'oid' => $cartInfo['oid'],
            'unique' => $unique,
            'product_id' => $productId,
            'add_time' => time(),
            'reply_type' => 'product'
        ]);
        StoreProductReply::beginTrans();
        $res = StoreProductReply::reply($group, 'product');
        if (!$res) {
            StoreProductReply::rollbackTrans();
            return app('json')->fail('评价失败!');
        }
        try {
            StoreOrder::checkOrderOver($cartInfo['oid']);
        } catch (\Exception $e) {
            StoreProductReply::rollbackTrans();
            return app('json')->fail($e->getMessage());
        }
        StoreProductReply::commitTrans();
        event('UserCommented', $res);
        event('AdminNewPush');
        return app('json')->successful();
    }

    /**
     * 订单统计数据
     * @param Request $request
     * @return mixed
     */
    public function data(Request $request)
    {
        return app('json')->successful(StoreOrder::getOrderData($request->uid()));
    }

    /**
     * 订单退款理由
     * @return mixed
     */
    public function refund_reason()
    {
        $reason = sys_config('stor_reason') ?: [];//退款理由
        $reason = str_replace("\r\n", "\n", $reason);//防止不兼容
        $reason = explode("\n", $reason);
        return app('json')->successful($reason);
    }

    /**
     * 订单退款审核
     * @param Request $request
     * @return mixed
     */
    public function refund_verify(Request $request)
    {
        $data = UtilService::postMore([
            ['text', ''],
            ['refund_reason_wap_img', ''],
            ['refund_reason_wap_explain', ''],
            ['uni', '']
        ], $request);
        $uni = $data['uni'];
        unset($data['uni']);
        if ($data['refund_reason_wap_img']) $data['refund_reason_wap_img'] = explode(',', $data['refund_reason_wap_img']);
        if (!$uni || $data['text'] == '') return app('json')->fail('参数错误!');
        $res = StoreOrder::orderApplyRefund($uni, $request->uid(), $data['text'], $data['refund_reason_wap_explain'], $data['refund_reason_wap_img']);
        if ($res)
            return app('json')->successful('提交申请成功');
        else
            return app('json')->fail(StoreOrder::getErrorInfo());
    }


    /**
     * 订单取消   未支付的订单回退积分,回退优惠券,回退库存
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function cancel(Request $request)
    {
        list($id) = UtilService::postMore([['id', 0]], $request, true);
        if (!$id) return app('json')->fail('参数错误');
        if (StoreOrder::cancelOrder($id, $request->uid()))
            return app('json')->successful('取消订单成功');
        return app('json')->fail(StoreOrder::getErrorInfo('取消订单失败'));
    }


    /**
     * 订单产品信息
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function product(Request $request)
    {
        list($unique) = UtilService::postMore([['unique', '']], $request, true);
        if (!$unique || !StoreOrderCartInfo::be(['unique' => $unique]) || !($cartInfo = StoreOrderCartInfo::where('unique', $unique)->find())) return app('json')->fail('评价产品不存在!');
        $cartInfo = $cartInfo->toArray();
        $cartProduct = [];
        $cartProduct['cart_num'] = $cartInfo['cart_info']['cart_num'];
        $cartProduct['productInfo']['image'] = isset($cartInfo['cart_info']['productInfo']['image']) ? $cartInfo['cart_info']['productInfo']['image'] : '';
        $cartProduct['productInfo']['price'] = isset($cartInfo['cart_info']['productInfo']['price']) ? $cartInfo['cart_info']['productInfo']['price'] : 0;
        $cartProduct['productInfo']['store_name'] = isset($cartInfo['cart_info']['productInfo']['store_name']) ? $cartInfo['cart_info']['productInfo']['store_name'] : '';
        if (isset($cartInfo['cart_info']['productInfo']['attrInfo'])) {
            $cartProduct['productInfo']['attrInfo']['product_id'] = isset($cartInfo['cart_info']['productInfo']['attrInfo']['product_id']) ? $cartInfo['cart_info']['productInfo']['attrInfo']['product_id'] : '';
            $cartProduct['productInfo']['attrInfo']['suk'] = isset($cartInfo['cart_info']['productInfo']['attrInfo']['suk']) ? $cartInfo['cart_info']['productInfo']['attrInfo']['suk'] : '';
            $cartProduct['productInfo']['attrInfo']['price'] = isset($cartInfo['cart_info']['productInfo']['attrInfo']['price']) ? $cartInfo['cart_info']['productInfo']['attrInfo']['price'] : '';
            $cartProduct['productInfo']['attrInfo']['image'] = isset($cartInfo['cart_info']['productInfo']['attrInfo']['image']) ? $cartInfo['cart_info']['productInfo']['attrInfo']['image'] : '';
        }
        $cartProduct['product_id'] = isset($cartInfo['cart_info']['product_id']) ? $cartInfo['cart_info']['product_id'] : 0;
        $cartProduct['combination_id'] = isset($cartInfo['cart_info']['combination_id']) ? $cartInfo['cart_info']['combination_id'] : 0;
        $cartProduct['seckill_id'] = isset($cartInfo['cart_info']['seckill_id']) ? $cartInfo['cart_info']['seckill_id'] : 0;
        $cartProduct['bargain_id'] = isset($cartInfo['cart_info']['bargain_id']) ? $cartInfo['cart_info']['bargain_id'] : 0;
        $cartProduct['order_id'] = StoreOrder::where('id', $cartInfo['oid'])->value('order_id');
        return app('json')->successful($cartProduct);
    }

    /**
     * 首页获取未支付订单
     */
    public function get_noPay(Request $request)
    {
        return app('json')->successful(StoreOrder::getUserOrderSearchList($request->uid(), 0, 0, 0, ''));
    }
}