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
namespace app\api\controller\v1\order;

use app\Request;
use app\services\pay\PayServices;
use app\services\shipping\ExpressServices;
use app\services\system\admin\SystemAdminServices;
use app\services\user\UserInvoiceServices;
use crmeb\services\pay\extend\allinpay\AllinPay;
use app\services\activity\{lottery\LuckLotteryServices,
    bargain\StoreBargainServices,
    combination\StoreCombinationServices,
    combination\StorePinkServices,
    seckill\StoreSeckillServices
};
use app\services\activity\coupon\StoreCouponIssueServices;
use app\services\order\{OtherOrderServices,
    StoreCartServices,
    StoreOrderCartInfoServices,
    StoreOrderComputedServices,
    StoreOrderCreateServices,
    StoreOrderEconomizeServices,
    StoreOrderInvoiceServices,
    StoreOrderRefundServices,
    StoreOrderServices,
    StoreOrderSuccessServices,
    StoreOrderTakeServices
};
use app\services\pay\OrderPayServices;
use app\services\pay\YuePayServices;
use app\services\product\product\StoreProductReplyServices;
use app\services\shipping\ShippingTemplatesServices;
use crmeb\services\CacheService;
use think\facade\Cache;

/**
 * 订单控制器
 * Class StoreOrderController
 * @package app\api\controller\order
 */
class StoreOrderController
{

    /**
     * @var StoreOrderServices
     */
    protected $services;

    /**
     * @var int[]
     */
    protected $getChennel = [
        'weixin' => 0,
        'routine' => 1,
        'weixinh5' => 2,
        'pc' => 3,
        'app' => 4
    ];

    /**
     * StoreOrderController constructor.
     * @param StoreOrderServices $services
     */
    public function __construct(StoreOrderServices $services)
    {
        $this->services = $services;
    }

    /**
     * 获取确认订单页面是否展示快递配送和到店自提
     * @param Request $request
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function checkShipping(Request $request)
    {
        [$cartId, $new] = $request->postMore(['cartId', 'new'], true);
        return app('json')->success($this->services->checkShipping($request->uid(), $cartId, $new));
    }

    /**
     * 订单确认
     * @param Request $request
     * @param ShippingTemplatesServices $services
     * @return mixed
     */
    public function confirm(Request $request, ShippingTemplatesServices $services)
    {
        if (!$services->get(1, ['id'])) {
            return app('json')->fail(410207);
        }
        [$cartId, $new, $addressId, $shipping_type] = $request->postMore([
            'cartId',
            'new',
            ['addressId', 0],
            ['shipping_type', 1],
        ], true);
        if (!is_string($cartId) || !$cartId) {
            return app('json')->fail(410201);
        }
        $user = $request->user()->toArray();
        return app('json')->success($this->services->getOrderConfirmData($user, $cartId, !!$new, $addressId, (int)$shipping_type));
    }

    /**
     * 计算订单金额
     * @param Request $request
     * @param StoreOrderComputedServices $computedServices
     * @param $key
     * @return mixed
     */
    public function computedOrder(Request $request, StoreOrderComputedServices $computedServices, $key)
    {
        if (!$key) return app('json')->fail(100100);
        $uid = $request->uid();
        if ($this->services->be(['order_id|unique' => $key, 'uid' => $uid, 'is_del' => 0]))
            return app('json')->status('extend_order', 410173, ['orderId' => $key, 'key' => $key]);
        list($addressId, $couponId, $payType, $useIntegral, $mark, $combinationId, $pinkId, $seckill_id, $bargainId, $shipping_type) = $request->postMore([
            'addressId', 'couponId', ['payType', 'yue'], ['useIntegral', 0], 'mark', ['combinationId', 0], ['pinkId', 0], ['seckill_id', 0], ['bargainId', ''],
            ['shipping_type', 1],
        ], true);
        $payType = strtolower($payType);
        $cartGroup = $this->services->getCacheOrderInfo($uid, $key);
        if (!$cartGroup) return app('json')->fail(410208);
        $priceGroup = $computedServices->setParamData([
            'combinationId' => $combinationId,
            'pinkId' => $pinkId,
            'seckill_id' => $seckill_id,
            'bargainId' => $bargainId,
        ])->computedOrder($request->uid(), $request->user()->toArray(), $cartGroup, $addressId, $payType, !!$useIntegral, (int)$couponId, false, (int)$shipping_type);
        if ($priceGroup)
            return app('json')->status('NONE', 100010, $priceGroup);
        else
            return app('json')->fail(100016);
    }

    /**
     * 订单创建
     * @param Request $request
     * @param StoreBargainServices $bargainServices
     * @param StorePinkServices $pinkServices
     * @param StoreOrderCreateServices $createServices
     * @param StoreSeckillServices $seckillServices
     * @param UserInvoiceServices $userInvoiceServices
     * @param StoreOrderInvoiceServices $storeOrderInvoiceServices
     * @param StoreCombinationServices $combinationServices
     * @param $key
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function create(Request $request, StoreBargainServices $bargainServices, StorePinkServices $pinkServices, StoreOrderCreateServices $createServices, StoreSeckillServices $seckillServices, UserInvoiceServices $userInvoiceServices, StoreOrderInvoiceServices $storeOrderInvoiceServices, StoreCombinationServices $combinationServices, $key)
    {
        if (!$key) return app('json')->fail(100100);
        $uid = (int)$request->uid();
        if ($checkOrder = $this->services->getOne(['order_id|unique' => $key, 'uid' => $uid, 'is_del' => 0]))
            return app('json')->status('extend_order', 410209, ['orderId' => $checkOrder['order_id'], 'key' => $key]);
        [$addressId, $couponId, $payType, $useIntegral, $mark, $combinationId, $pinkId, $seckill_id, $bargainId, $from, $shipping_type, $real_name, $phone, $storeId, $news, $invoice_id, $quitUrl, $advanceId, $virtual_type, $customForm] = $request->postMore([
            [['addressId', 'd'], 0],
            [['couponId', 'd'], 0],
            ['payType', ''],
            ['useIntegral', 0],
            ['mark', ''],
            [['combinationId', 'd'], 0],
            [['pinkId', 'd'], 0],
            [['seckill_id', 'd'], 0],
            [['bargainId', 'd'], ''],
            ['from', 'weixin'],
            [['shipping_type', 'd'], 1],
            ['real_name', ''],
            ['phone', ''],
            [['store_id', 'd'], 0],
            ['new', 0],
            [['invoice_id', 'd'], 0],
            ['quitUrl', ''],
            [['advanceId', 'd'], 0],
            ['virtual_type', 0],
            ['custom_form', []],
        ], true);
        $payType = strtolower($payType);
        $cartGroup = $this->services->getCacheOrderInfo($uid, $key);
        if (!$cartGroup) {
            return app('json')->fail(410208);
        }
        //下单前砍价验证
        if ($bargainId) {
            $bargainServices->checkBargainUser((int)$bargainId, $uid);
        }
        //下单前发票验证
        if ($invoice_id) {
            $userInvoiceServices->checkInvoice((int)$invoice_id, $uid);
        }
        if ($pinkId) {
            $pinkId = (int)$pinkId;
            /** @var StorePinkServices $pinkServices */
            $pinkServices = app()->make(StorePinkServices::class);
            if ($pinkServices->isPink($pinkId, $uid))
                return app('json')->status('ORDER_EXIST', 410210, ['orderId' => $this->services->getStoreIdPink($pinkId, $uid)]);
            if ($this->services->getIsOrderPink($pinkId, $uid))
                return app('json')->status('ORDER_EXIST', 410211, ['orderId' => $this->services->getStoreIdPink($pinkId, $uid)]);
            if (!CacheService::checkStock(md5($pinkId), 1, 3) || !CacheService::popStock(md5($pinkId), 1, 3)) {
                return app('json')->fail(410212);
            }
        }
        if ($from != 'pc') {
            if (!$this->services->checkPaytype(get_pay_type($payType))) {
                return app('json')->fail(410213);
            }
        } else {
            $payType = 'pc';
        }
        $isChannel = $this->getChennel[$from] ?? ($request->isApp() ? 0 : 1);
        $cartInfo = null;
        if ($seckill_id || $combinationId || $bargainId || $advanceId) {
            $cartInfo = $cartGroup['cartInfo'];
            foreach ($cartInfo as $item) {
                $type = 0;
                if (!isset($item['product_attr_unique']) || !$item['product_attr_unique']) continue;
                if ($item['seckill_id']) {
                    $type = 1;
                } elseif ($item['bargain_id']) {
                    $type = 2;
                } elseif ($item['combination_id']) {
                    $type = 3;
                } elseif ($item['advance_id']) {
                    $type = 6;
                }
                if ($type && (!CacheService::checkStock($item['product_attr_unique'], (int)$item['cart_num'], $type) || !CacheService::popStock($item['product_attr_unique'], (int)$item['cart_num'], $type))) {
                    return app('json')->fail(410214, null, ['cart_num' => $item['cart_num'], 'unit_name' => $item['productInfo']['unit_name']]);

                }
            }
        }
        $virtual_type = $cartGroup['cartInfo'][0]['productInfo']['virtual_type'] ?? 0;
        $order = $createServices->createOrder($uid, $key, $cartGroup, $request->user()->toArray(), $addressId, $payType, !!$useIntegral, $couponId, $mark, $combinationId, $pinkId, $seckill_id, $bargainId, $isChannel, $shipping_type, $real_name, $phone, $storeId, !!$news, $advanceId, $virtual_type, $customForm);
        if ($order === false) {
            if ($seckill_id || $combinationId || $advanceId || $bargainId) {
                foreach ($cartInfo as $item) {
                    $value = $item['cart_info'];
                    $type = 0;
                    if (!isset($value['product_attr_unique']) || $value['product_attr_unique']) continue;
                    if ($value['seckill_id']) {
                        $type = 1;
                    } elseif ($value['bargain_id']) {
                        $type = 2;
                    } elseif ($value['combination_id']) {
                        $type = 3;
                    } elseif ($value['advance_id']) {
                        $type = 6;
                    }
                    if ($type) CacheService::setStock($value['product_attr_unique'], (int)$value['cart_num'], $type, false);
                }
            }
            return app('json')->fail(410200);
        }
        $orderId = $order['order_id'];
        $orderInfo = $this->services->getOne(['order_id' => $orderId]);
        if (!$orderInfo || !isset($orderInfo['paid'])) {
            return app('json')->fail(410194);
        }
        //创建开票数据
        if ($invoice_id) {
            $storeOrderInvoiceServices->makeUp($uid, $orderId, (int)$invoice_id);
        }
        $orderInfo = $orderInfo->toArray();
        $info = compact('orderId', 'key');
        if ($orderId) {
            switch ($payType) {
                case PayServices::WEIXIN_PAY:
                    if ($orderInfo['paid']) return app('json')->fail(410174);
                    //支付金额为0
                    if (bcsub((string)$orderInfo['pay_price'], '0', 2) <= 0) {
                        //创建订单jspay支付
                        /** @var StoreOrderSuccessServices $success */
                        $success = app()->make(StoreOrderSuccessServices::class);
                        $payPriceStatus = $success->zeroYuanPayment($orderInfo, $uid, PayServices::WEIXIN_PAY);
                        if ($payPriceStatus)//0元支付成功
                            return app('json')->status('success', 410195, $info);
                        else
                            return app('json')->status('pay_error');
                    } else {
                        /** @var OrderPayServices $payServices */
                        $payServices = app()->make(OrderPayServices::class);
                        if ($from == 'app' && $request->isApp()) {
                            $from = 'weixin';
                        }
                        $info['jsConfig'] = $payServices->orderPay($orderInfo, $from);
                        if ($from == 'weixinh5') {
                            return app('json')->status('wechat_h5_pay', 410203, $info);
                        } else {
                            return app('json')->status('wechat_pay', 410203, $info);
                        }
                    }
                case PayServices::YUE_PAY:
                    /** @var YuePayServices $yueServices */
                    $yueServices = app()->make(YuePayServices::class);
                    $pay = $yueServices->yueOrderPay($orderInfo, $uid);
                    if ($pay['status'] === true)
                        return app('json')->status('success', 410197, $info);
                    else {
                        if (is_array($pay))
                            return app('json')->status($pay['status'], $pay['msg'], $info);
                        else
                            return app('json')->status('pay_error', $pay);
                    }
                case PayServices::ALIAPY_PAY:
                    if (!$quitUrl && ($request->isH5() || $request->isWechat())) {
                        return app('json')->status('pay_error', 410198, $info);
                    }
                    [$url, $param] = explode('?', $quitUrl);
                    $quitUrl = $url . '?order_id=' . $orderInfo['order_id'];
                    //支付金额为0
                    if (bcsub((string)$orderInfo['pay_price'], '0', 2) <= 0) {
                        //创建订单jspay支付
                        /** @var StoreOrderSuccessServices $success */
                        $success = app()->make(StoreOrderSuccessServices::class);
                        $payPriceStatus = $success->zeroYuanPayment($orderInfo, $uid, PayServices::ALIAPY_PAY);
                        if ($payPriceStatus)//0元支付成功
                            return app('json')->status('success', 410199, $info);
                        else
                            return app('json')->status('pay_error');
                    } else {
                        /** @var OrderPayServices $payServices */
                        $payServices = app()->make(OrderPayServices::class);
                        $info['jsConfig'] = $payServices->alipayOrder($orderInfo, $quitUrl, $from == 'routine');
                        $payKey = md5($orderInfo['order_id']);
                        CacheService::set($payKey, ['order_id' => $orderInfo['order_id'], 'other_pay_type' => false], 300);
                        $info['pay_key'] = $payKey;
                        return app('json')->status(PayServices::ALIAPY_PAY . '_pay', 410203, $info);
                    }
                case PayServices::OFFLINE_PAY:
                case 'pc':
                case 'friend':
                    return app('json')->status('success', 410203, $info);
                case PayServices::ALLIN_PAY:
                    /** @var OrderPayServices $payServices */
                    $payServices = app()->make(OrderPayServices::class);
                    $info['jsConfig'] = $payServices->orderPay($orderInfo, $payType, [
                        'returl' => sys_config('site_url') . '/pages/index/index',
                    ]);
                    if ($request->isWechat()) {
                        $info['pay_url'] = AllinPay::UNITODER_H5UNIONPAY;
                    }
                    return app('json')->status(PayServices::ALLIN_PAY . '_pay', 410203, $info);
            }
        } else return app('json')->fail(410200);
    }

    /**
     * 订单 再次下单
     * @param Request $request
     * @param StoreCartServices $services
     * @return mixed
     */
    public function again(Request $request, StoreCartServices $services)
    {
        list($uni) = $request->postMore([
            ['uni', ''],
        ], true);
        $cateId = $this->services->againOrder($services, $uni, (int)$request->uid());
        return app('json')->success(['cateId' => implode(',', $cateId)]);
    }

    /**
     * 订单支付
     * @param Request $request
     * @param StorePinkServices $services
     * @param OrderPayServices $payServices
     * @param YuePayServices $yuePayServices
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function pay(Request $request, StorePinkServices $services, OrderPayServices $payServices, YuePayServices $yuePayServices)
    {
        [$uni, $paytype, $from, $quitUrl, $type] = $request->postMore([
            ['uni', ''],
            ['paytype', 'weixin'],
            ['from', 'weixin'],
            ['quitUrl', ''],
            ['type', 0]
        ], true);
        if (!$uni) return app('json')->fail(100100);
        $orderInfo = $this->services->get(['order_id' => $uni]);
        $uid = $type == 1 ? (int)$request->uid() : $orderInfo->uid;
        $orderInfo->pay_uid = $uid;
        $orderInfo->save();
        $order = $this->services->get(['order_id' => $uni]);
        if (!$order)
            return app('json')->fail(410173);
        if ($order['paid'])
            return app('json')->fail(410174);
        if ($order['pink_id'] && $services->isPinkStatus($order['pink_id'])) {
            return app('json')->fail(410215);
        }
        $isChannel = $this->getChennel[$from];
        //缓存不存在 ｜｜ 切换另一端支付
        if (!Cache::get('pay_' . $order['order_id']) || $isChannel != $order['is_channel']) {
            switch ($from) {
                case 'weixin':
                    if ($type == 1 || in_array($order['is_channel'], [1, 2, 3, 4])) {//0
                        $order['order_id'] = mt_rand(100, 999) . '_' . $order['order_id'];
                    }
                    break;
                case 'weixinh5':
                    if ($type == 1 || in_array($order['is_channel'], [0, 1, 3, 4])) {
                        $order['order_id'] = mt_rand(100, 999) . '_' . $order['order_id'];
                    }
                    break;
                case 'routine':
                    if ($type == 1 || in_array($order['is_channel'], [0, 2, 3, 4])) {
                        $order['order_id'] = mt_rand(100, 999) . '_' . $order['order_id'];
                    }
                    break;
                case 'app':
                    if ($type == 1 || in_array($order['is_channel'], [0, 1, 2, 3])) {
                        $order['order_id'] = mt_rand(100, 999) . '_' . $order['order_id'];
                    }
                    break;
                case 'pc':
                case 'aliapy':
                    $order['order_id'] = mt_rand(100, 999) . '_' . $order['order_id'];
                    break;
            }
        }
        $order['pay_type'] = get_pay_type($paytype); //重新支付选择支付方式
        switch ($order['pay_type']) {
            case PayServices::WEIXIN_PAY:
                $jsConfig = $payServices->orderPay($order->toArray(), $from);
                if ($from == 'weixinh5') {
                    return app('json')->status('wechat_h5_pay', ['jsConfig' => $jsConfig, 'order_id' => $order['order_id']]);
                } elseif ($from == 'weixin' || $from == 'routine') {
                    return app('json')->status('wechat_pay', ['jsConfig' => $jsConfig, 'order_id' => $order['order_id']]);
                } elseif ($from == 'pc') {
                    return app('json')->status('wechat_pc_pay', ['jsConfig' => $jsConfig, 'order_id' => $order['order_id']]);
                }
                break;
            case PayServices::ALIAPY_PAY:
                if (!$quitUrl && $from != 'routine') {
                    return app('json')->fail(410198);
                }
                $isCode = $from == 'routine' || $from == 'pc';
                $jsConfig = $payServices->alipayOrder($order->toArray(), $quitUrl, $isCode);
                if ($isCode && !($jsConfig->invalid ?? false)) $jsConfig->invalid = time() + 60;
                $payKey = md5($order['order_id']);
                CacheService::set($payKey, ['order_id' => $order['order_id'], 'other_pay_type' => false], 300);
                return app('json')->status(PayServices::ALIAPY_PAY . '_pay', 410203, ['jsConfig' => $jsConfig, 'order_id' => $order['order_id'], 'pay_key' => $payKey]);
            case PayServices::YUE_PAY:
                $pay = $yuePayServices->yueOrderPay($order->toArray(), $request->uid());
                if ($pay['status'] === true)
                    return app('json')->status('success', 410197);
                else {
                    if (is_array($pay))
                        return app('json')->status($pay['status'], $pay['msg']);
                    else
                        return app('json')->status('pay_error', $pay);
                }
            case PayServices::OFFLINE_PAY:
                if ($this->services->setOrderTypePayOffline($order['order_id']))
                    return app('json')->status('success', 410203);
                else
                    return app('json')->status('success', 410216);
            case PayServices::ALLIN_PAY:
                /** @var OrderPayServices $payServices */
                $payServices = app()->make(OrderPayServices::class);
                $info['jsConfig'] = $payServices->orderPay($order->toArray(), $order['pay_type'], [
                    'returl' => sys_config('site_url') . '/pages/index/index',
                ]);
                if ($request->isWechat()) {
                    $info['pay_url'] = AllinPay::UNITODER_H5UNIONPAY;
                }
                return app('json')->status(PayServices::ALLIN_PAY . '_pay', 410203, $info);
        }
        return app('json')->fail(410218);
    }

    /**
     * 支付宝单独支付
     * @param OrderPayServices $payServices
     * @param OtherOrderServices $services
     * @param string $key
     * @param string $quitUrl
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function aliPay(OrderPayServices $payServices, OtherOrderServices $services, string $key, string $quitUrl)
    {
        $payInfo = $this->services->aliPayOrder($payServices, $services, $key, $quitUrl);
        return app('json')->success(['pay_content' => $payInfo]);
    }

    /**
     * 订单列表
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function lst(Request $request)
    {
        $where = $request->getMore([
            ['type', '', '', 'status'],
            ['search', '', '', 'real_name'],
            ['refund_type', '', '', 'refundTypes']
        ]);
        $where['uid'] = $request->uid();
        $where['is_del'] = 0;
        $where['is_system_del'] = 0;
        if (in_array($where['status'], [-1, -2, -3])) {
            $where['not_pid'] = 1;
        } elseif (in_array($where['status'], [0, 1, 2, 3, 4, 9])) {
            $where['pid'] = 0;
        }
        $list = $this->services->getOrderApiList($where);
        return app('json')->success($list);
    }

    /**
     * 订单详情
     * @param Request $request
     * @param StoreOrderEconomizeServices $services
     * @param $uni
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function detail(Request $request, StoreOrderEconomizeServices $services, $uni)
    {
        if (!strlen(trim($uni))) return app('json')->fail(100100);
        $orderData = $this->services->getUserOrderByKey($services, $uni, (int)$request->uid());
        return app('json')->success($orderData);
    }

    /**
     * 代付订单详情
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function friendDetail(Request $request)
    {
        [$orderId] = $request->getMore([
            ['order_id', '']
        ], true);
        $info = $this->services->getFriendDetail($orderId, $request->uid());
        return app('json')->success(compact('info'));
    }

    /**
     * TODO 弃用
     * 退款订单详情
     * @param Request $request
     * @param $uni
     * @param string $cartId
     * @return mixed
     */
    public function refund_detail(Request $request, $uni, $cartId = '')
    {
        if (!strlen(trim($uni))) return app('json')->fail(100100);
        /** @var StoreOrderCartInfoServices $storeOrderCartInfoServices */
        $storeOrderCartInfoServices = app()->make(StoreOrderCartInfoServices::class);
        $order = $this->services->getUserOrderDetail($uni, (int)$request->uid(), ['split', 'invoice']);
        if (!$order) return app('json')->fail(410173);
        $order = $order->toArray();
        $orderData = $this->services->tidyOrder($order, true, true);
        $splitNum = $storeOrderCartInfoServices->getSplitCartNum($order['cart_id']);
        foreach ($orderData['cartInfo'] ?? [] as $key => $cart) {
            $orderData['cartInfo'][$key]['one_postage_price'] = isset($cart['postage_price']) ? bcdiv($cart['postage_price'], $cart['cart_num'], 2) : 0;
            if ($cartId != '') {
                if ($cart['id'] != $cartId) {
                    unset($orderData['cartInfo'][$key]);
                } else {
                    if (isset($splitNum[$cart['id']])) {
                        $orderData['total_num'] = $orderData['cartInfo'][$key]['cart_num'] = $cart['cart_num'] - $splitNum[$cart['id']];
                        $orderData['pay_price'] = bcadd(bcmul($cart['truePrice'], $orderData['total_num'], 4), bcmul($orderData['total_num'], $orderData['cartInfo'][$key]['one_postage_price'], 4), 2);
                    } else {
                        $orderData['total_num'] = $orderData['cartInfo'][$key]['cart_num'];
                        $orderData['pay_price'] = bcadd(bcmul($cart['truePrice'], $cart['cart_num'], 4), $cart['postage_price'], 2);
                    }
                }
            } else {
                if (isset($splitNum[$cart['id']])) {
                    $orderData['cartInfo'][$key]['cart_num'] = $cart['cart_num'] - $splitNum[$cart['id']];
                    $orderData['total_num'] = $orderData['total_num'] - $splitNum[$cart['id']];
                    if ($orderData['cartInfo'][$key]['cart_num'] == 0) unset($orderData['cartInfo'][$key]);
                }
            }
        }
        if ($cartId == '') {
            $orderData['pay_price'] = bcsub($orderData['pay_price'], $this->services->sum(['pid' => $orderData['id']], 'pay_price'), 2);
        }
        $orderData['cartInfo'] = array_merge($orderData['cartInfo']);
        return app('json')->success($orderData);
    }


    /**
     * 订单删除
     * @param Request $request
     * @return mixed
     */
    public function del(Request $request)
    {
        [$uni] = $request->postMore([
            ['uni', ''],
        ], true);
        if (!$uni) return app('json')->fail(100100);
        $res = $this->services->removeOrder($uni, (int)$request->uid());
        if ($res) {
            return app('json')->success(100002);
        } else {
            return app('json')->fail(100008);
        }
    }

    /**
     * 订单收货
     * @param Request $request
     * @param StoreOrderTakeServices $services
     * @param StoreCouponIssueServices $issueServices
     * @return mixed
     */
    public function take(Request $request, StoreOrderTakeServices $services, StoreCouponIssueServices $issueServices)
    {
        list($uni) = $request->postMore([
            ['uni', ''],
        ], true);
        if (!$uni) return app('json')->fail(100100);
        $order = $services->takeOrder($uni, (int)$request->uid());
        if ($order) {
            return app('json')->success(410204);
        } else
            return app('json')->fail(410205);
    }


    /**
     * 订单 查看物流
     * @param Request $request
     * @param $uni
     * @return mixed
     */
    public function express(Request $request, StoreOrderCartInfoServices $services, ExpressServices $expressServices, $uni, $type = '')
    {
        if ($type == 'refund') {
            /** @var StoreOrderRefundServices $refundService */
            $refundService = app()->make(StoreOrderRefundServices::class);
            $order = $refundService->refundDetail($uni);
            $express = $order['refund_express'];
            $cacheName = $uni . $express;
            $orderInfo = [];
            $info = [];
            $cartNew = [];
            foreach ($order['cart_info'] as $k => $cart) {
                $cartNew['cart_num'] = $cart['cart_num'];
                $cartNew['truePrice'] = $cart['truePrice'];
                $cartNew['productInfo']['image'] = $cart['productInfo']['image'];
                $cartNew['productInfo']['store_name'] = $cart['productInfo']['store_name'];
                $cartNew['productInfo']['unit_name'] = $cart['productInfo']['unit_name'] ?? '';
                array_push($info, $cartNew);
                unset($cart);
            }
            $orderInfo['cartInfo'] = $info;
            $orderInfo['delivery_id'] = $express;
            $orderInfo['delivery_name'] = $order['refund_express_name'];
            $orderInfo['delivery_code'] = '';
        } else {
            if (!$uni || !($order = $this->services->getUserOrderDetail($uni, $request->uid(), []))) return app('json')->fail(410173);
            if ($type != 'refund' && ($order['delivery_type'] != 'express' || !$order['delivery_id'])) return app('json')->fail(410206);
            $express = $type == 'refund' ? $order['refund_express'] : $order['delivery_id'];
            $cacheName = $uni . $express;
            $orderInfo = [];
            $cartInfo = $services->getCartColunm(['oid' => $order['id']], 'cart_info', 'unique');
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
            $orderInfo['delivery_id'] = $express;
            $orderInfo['delivery_name'] = $type == 'refund' ? '用户退回' : $order['delivery_name'];;
            $orderInfo['delivery_code'] = $type == 'refund' ? '' : $order['delivery_code'];
            $orderInfo['delivery_type'] = $order['delivery_type'];
            $orderInfo['user_address'] = $order['user_address'];
            $orderInfo['user_mark'] = $order['mark'];
            $orderInfo['cartInfo'] = $info;
        }
        return app('json')->success([
            'order' => $orderInfo,
            'express' => [
                'result' => ['list' => $expressServices->query($cacheName, $orderInfo['delivery_id'], $orderInfo['delivery_code'], $order['user_phone'])
                ]
            ]
        ]);
    }

    /**
     * 订单评价
     * @param Request $request
     * @param StoreOrderCartInfoServices $cartInfoServices
     * @param StoreProductReplyServices $replyServices
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function comment(Request $request, StoreOrderCartInfoServices $cartInfoServices, StoreProductReplyServices $replyServices)
    {

        $group = $request->postMore([
            ['unique', ''], ['comment', ''], ['pics', ''], ['product_score', 5], ['service_score', 5]
        ]);
        $unique = $group['unique'];
        unset($group['unique']);
        if (!$unique) return app('json')->fail(100100);
        $cartInfo = $cartInfoServices->getOne(['unique' => $unique]);
        $uid = $request->uid();
        $user_info = $request->user();
        $group['nickname'] = $user_info['nickname'];
        $group['avatar'] = $user_info['avatar'];
        if (!$cartInfo) return app('json')->fail(410294);
        $orderUid = $this->services->value(['id' => $cartInfo['oid']], 'uid');
        if ($uid != $orderUid) return app('json')->fail(410294);
        if ($replyServices->be(['oid' => $cartInfo['oid'], 'unique' => $unique]))
            return app('json')->fail(410219);
        $group['comment'] = htmlspecialchars(trim($group['comment']));
        if ($group['product_score'] < 1) return app('json')->fail(410220);
        else if ($group['service_score'] < 1) return app('json')->fail(410221);
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

        $res = $replyServices->save($group);
        if (!$res) {
            return app('json')->fail(410222);
        }
        try {
            $this->services->checkOrderOver($replyServices, $cartInfoServices->getCartColunm(['oid' => $cartInfo['oid']], 'unique', ''), $cartInfo['oid']);
        } catch (\Exception $e) {
            return app('json')->fail(410222);
        }
        //缓存抽奖次数
        /** @var LuckLotteryServices $luckLotteryServices */
        $luckLotteryServices = app()->make(LuckLotteryServices::class);
        $luckLotteryServices->setCacheLotteryNum((int)$orderUid, 'comment');

        /** @var SystemAdminServices $systemAdmin */
        $systemAdmin = app()->make(SystemAdminServices::class);
        $systemAdmin->adminNewPush();

        $lottery = $luckLotteryServices->getFactorLottery(4);
        if (!$lottery) {
            return app('json')->success(['to_lottery' => false]);
        }
        $lottery = $lottery->toArray();
        try {
            $luckLotteryServices->checkoutUserAuth($uid, (int)$lottery['id'], [], $lottery);
            $lottery_num = $luckLotteryServices->getLotteryNum($uid, (int)$lottery['id'], [], $lottery);
            if ($lottery_num > 0) return app('json')->success(['to_lottery' => true]);
        } catch (\Exception $e) {
            return app('json')->success(['to_lottery' => false]);
        }
    }

    /**
     * 订单统计数据
     * @param Request $request
     * @return mixed
     */
    public function data(Request $request)
    {
        return app('json')->success($this->services->getOrderData((int)$request->uid()));
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
        return app('json')->success($reason);
    }

    /**
     * 获取可以退货的订单商品列表
     * @param Request $request
     * @param StoreOrderCartInfoServices $services
     * @param $id
     * @return mixed
     */
    public function refundCartInfo(Request $request, StoreOrderCartInfoServices $services, $id)
    {
        if (!$id) {
            return app('json')->fail(100100);
        }
        [$cart_ids] = $request->postMore([
            ['cart_ids', []]
        ], true);
        $list = $services->getRefundCartList((int)$id);
        if ($cart_ids) {
            foreach ($cart_ids as $cart) {
                if (!isset($cart['cart_id']) || !$cart['cart_id'] || !isset($cart['cart_num']) || !$cart['cart_num'] || $cart['cart_num'] <= 0) {
                    return app('json')->fail(410223);
                }
            }
            $cart_ids = array_combine(array_column($cart_ids, 'cart_id'), $cart_ids);
            foreach ($list as &$item) {
                if (isset($cart_ids[$item['cart_id']]['cart_num'])) $item['cart_num'] = $cart_ids[$item['cart_id']]['cart_num'];
            }
        }
        return app('json')->success($list);
    }

    /**
     * 获取退货商品列表
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function refundCartInfoList(Request $request)
    {
        [$cart_ids, $id] = $request->postMore([
            ['cart_ids', []],
            ['id', 0],
        ], true);
        if (!$id) {
            return app('json')->fail(100100);
        }
        return app('json')->success($this->services->refundCartInfoList((array)$cart_ids, (int)$id));
    }

    /**
     * 用户申请退款
     * @param Request $request
     * @param StoreOrderRefundServices $services
     * @param StoreOrderServices $storeOrderServices
     * @param $id
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function applyRefund(Request $request, StoreOrderRefundServices $services, StoreOrderServices $storeOrderServices, $id)
    {
        if (!$id) {
            return app('json')->fail(100100);
        }
        $data = $request->postMore([
            ['text', ''],
            ['refund_reason_wap_img', ''],
            ['refund_reason_wap_explain', ''],
            ['refund_type', 1],
            ['refund_price', 0.00],
            ['cart_ids', []]
        ]);
        if ($data['text'] == '') return app('json')->fail(100100);
        if ($data['cart_ids']) {
            foreach ($data['cart_ids'] as $cart) {
                if (!isset($cart['cart_id']) || !$cart['cart_id'] || !isset($cart['cart_num']) || !$cart['cart_num']) {
                    return app('json')->fail(410223);
                }
            }
        }

        $order = $storeOrderServices->get($id);
        $uid = (int)$request->uid();
        if (!$order || $uid != $order['uid']) {
            return app('json')->fail(410173);
        }
        $refundData = [
            'refund_reason' => $data['text'],
            'refund_explain' => $data['refund_reason_wap_explain'],
            'refund_img' => json_encode($data['refund_reason_wap_img'] != '' ? explode(',', $data['refund_reason_wap_img']) : []),
        ];
        $res = $services->applyRefund((int)$id, $uid, $order, $data['cart_ids'], (int)$data['refund_type'], (float)$data['refund_price'], $refundData);
        if ($res)
            return app('json')->success(100027);
        else
            return app('json')->fail(100028);
    }

    /**
     * 订单申请退款审核
     * @param Request $request
     * @param StoreOrderRefundServices $services
     */
    public function refund_verify(Request $request, StoreOrderRefundServices $services)
    {
//        $data = $request->postMore([
//            ['text', ''],
//            ['refund_reason_wap_img', ''],
//            ['refund_reason_wap_explain', ''],
//            ['uni', ''],
//            ['refund_type', 1],
//            ['cart_id', 0],
//            ['refund_num', 0]
//        ]);
//        $uni = $data['uni'];
//        unset($data['uni']);
//        if ($data['refund_reason_wap_img'] != '') {
//            $data['refund_reason_wap_img'] = explode(',', $data['refund_reason_wap_img']);
//        } else {
//            $data['refund_reason_wap_img'] = [];
//        }
//        if (!$uni || $data['text'] == '' || $data['refund_num'] <= 0) return app('json')->fail('参数错误!');
//        $res = $services->orderApplyRefund($this->services->getUserOrderDetail($uni, (int)$request->uid()), $data['text'], $data['refund_reason_wap_explain'], $data['refund_reason_wap_img'], $data['refund_type'], $data['cart_id'], $data['refund_num']);
//        if ($res)
//            return app('json')->success('提交申请成功');
//        else
//            return app('json')->fail('提交失败');
    }

    /**
     * 用户退货提交快递单号
     * @param Request $request
     * @param StoreOrderRefundServices $services
     * @return mixed
     */
    public function refund_express(Request $request, StoreOrderRefundServices $services)
    {
        [$id, $express_id] = $request->postMore([
            ['id', ''],
            ['express_id', '']
        ], true);
        if ($id == '' || $express_id == '') return app('json')->fail(100100);
        $res = $services->editRefundExpress($id, $express_id);
        if ($res)
            return app('json')->success(100017);
        else
            return app('json')->fail(100018);
    }

    /**
     * 订单取消   未支付的订单回退积分,回退优惠券,回退库存
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function cancel(Request $request)
    {
        list($id) = $request->postMore([['id', 0]], true);
        if (!$id) return app('json')->fail(100100);
        if ($this->services->cancelOrder($id, (int)$request->uid()))
            return app('json')->success(100019);
        return app('json')->fail(100020);
    }

    /**
     * 订单商品信息
     * @param Request $request
     * @param StoreOrderCartInfoServices $services
     * @return mixed
     */
    public function product(Request $request, StoreOrderCartInfoServices $services)
    {
        list($unique) = $request->postMore([['unique', '']], true);
        if (!$unique || !($cartInfo = $services->getOne(['unique' => $unique]))) return app('json')->fail(410294);
        $cartInfo = $cartInfo->toArray();
        $cartProduct = [];
        $cartProduct['cart_num'] = $cartInfo['cart_info']['cart_num'];
        $cartProduct['productInfo']['image'] = get_thumb_water($cartInfo['cart_info']['productInfo']['image'] ?? '');
        $cartProduct['productInfo']['price'] = $cartInfo['cart_info']['productInfo']['price'] ?? 0;
        $cartProduct['productInfo']['store_name'] = $cartInfo['cart_info']['productInfo']['store_name'] ?? '';
        if (isset($cartInfo['cart_info']['productInfo']['attrInfo'])) {
            $cartProduct['productInfo']['attrInfo']['product_id'] = $cartInfo['cart_info']['productInfo']['attrInfo']['product_id'] ?? '';
            $cartProduct['productInfo']['attrInfo']['suk'] = $cartInfo['cart_info']['productInfo']['attrInfo']['suk'] ?? '';
            $cartProduct['productInfo']['attrInfo']['price'] = $cartInfo['cart_info']['productInfo']['attrInfo']['price'] ?? '';
            $cartProduct['productInfo']['attrInfo']['image'] = get_thumb_water($cartInfo['cart_info']['productInfo']['attrInfo']['image'] ?? '');
        }
        $cartProduct['product_id'] = $cartInfo['cart_info']['product_id'] ?? 0;
        $cartProduct['combination_id'] = $cartInfo['cart_info']['combination_id'] ?? 0;
        $cartProduct['seckill_id'] = $cartInfo['cart_info']['seckill_id'] ?? 0;
        $cartProduct['bargain_id'] = $cartInfo['cart_info']['bargain_id'] ?? 0;
        $cartProduct['order_id'] = $this->services->value(['id' => $cartInfo['oid']], 'order_id');
        return app('json')->success($cartProduct);
    }
}
