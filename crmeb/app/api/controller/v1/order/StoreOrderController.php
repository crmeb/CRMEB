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
namespace app\api\controller\v1\order;

use app\Request;
use app\services\pay\PayServices;
use app\services\shipping\ExpressServices;
use app\services\system\admin\SystemAdminServices;
use app\services\user\UserInvoiceServices;
use app\services\activity\{lottery\LuckLotteryServices,
    StoreBargainServices,
    StoreCombinationServices,
    StorePinkServices,
    StoreSeckillServices
};
use app\services\coupon\StoreCouponIssueServices;
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
    StoreOrderTakeServices,
    StoreOrderWriteOffServices};
use app\services\pay\OrderPayServices;
use app\services\pay\YuePayServices;
use app\services\product\product\StoreProductReplyServices;
use app\services\shipping\ShippingTemplatesServices;
use app\services\system\attachment\SystemAttachmentServices;
use app\services\system\store\SystemStoreServices;
use crmeb\services\CacheService;
use crmeb\services\UtilService;
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
        'pc' => 3
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
     * 订单确认
     * @param Request $request
     * @return mixed
     */
    public function confirm(Request $request, ShippingTemplatesServices $services)
    {
        if (!$services->get(1, ['id'])) {
            return app('json')->fail('默认模板未配置，无法下单');
        }
        [$cartId, $new, $addressId] = $request->postMore([
            'cartId',
            'new',
            ['addressId', 0]
        ], true);
        if (!is_string($cartId) || !$cartId) {
            return app('json')->fail('请提交购买的商品');
        }
        $user = $request->user()->toArray();
        return app('json')->successful($this->services->getOrderConfirmData($user, $cartId, !!$new, $addressId));
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
    public function computedOrder(Request $request, StoreOrderComputedServices $computedServices, $key)
    {
        if (!$key) return app('json')->fail('参数错误!');
        $uid = $request->uid();
        if ($this->services->be(['order_id|unique' => $key, 'uid' => $uid, 'is_del' => 0]))
            return app('json')->status('extend_order', '订单已生成', ['orderId' => $key, 'key' => $key]);
        list($addressId, $couponId, $payType, $useIntegral, $mark, $combinationId, $pinkId, $seckill_id, $bargainId, $shipping_type) = $request->postMore([
            'addressId', 'couponId', ['payType', 'yue'], ['useIntegral', 0], 'mark', ['combinationId', 0], ['pinkId', 0], ['seckill_id', 0], ['bargainId', ''],
            ['shipping_type', 1],
        ], true);
        $payType = strtolower($payType);
        $cartGroup = $this->services->getCacheOrderInfo($uid, $key);
        if (!$cartGroup) return app('json')->fail('订单已过期,请刷新当前页面!');
        $priceGroup = $computedServices->setParamData([
            'combinationId' => $combinationId,
            'pinkId' => $pinkId,
            'seckill_id' => $seckill_id,
            'bargainId' => $bargainId,
        ])->computedOrder($request->uid(), $request->user()->toArray(), $cartGroup, $addressId, $payType, !!$useIntegral, (int)$couponId, false, (int)$shipping_type);
        if ($priceGroup)
            return app('json')->status('NONE', 'ok', $priceGroup);
        else
            return app('json')->fail('计算失败');
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
     * @param $key
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function create(Request $request, StoreBargainServices $bargainServices, StorePinkServices $pinkServices, StoreOrderCreateServices $createServices, StoreSeckillServices $seckillServices, UserInvoiceServices $userInvoiceServices, StoreOrderInvoiceServices $storeOrderInvoiceServices, StoreCombinationServices $combinationServices, $key)
    {
        if (!$key) return app('json')->fail('参数错误!');
        $uid = (int)$request->uid();
        if ($checkOrder = $this->services->getOne(['order_id|unique' => $key, 'uid' => $uid, 'is_del' => 0]))
            return app('json')->status('extend_order', '订单已创建，请点击查看完成支付', ['orderId' => $checkOrder['order_id'], 'key' => $key]);
        [$addressId, $couponId, $payType, $useIntegral, $mark, $combinationId, $pinkId, $seckill_id, $bargainId, $from, $shipping_type, $real_name, $phone, $storeId, $news, $invoice_id, $quitUrl, $advanceId, $virtual_type] = $request->postMore([
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
            ['virtual_type', 0]
        ], true);
        $payType = strtolower($payType);
        $cartGroup = $this->services->getCacheOrderInfo($uid, $key);
        if (!$cartGroup) {
            return app('json')->fail('订单已过期,请刷新当前页面!');
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
                return app('json')->status('ORDER_EXIST', '订单生成失败，你已经在该团内不能再参加了', ['orderId' => $this->services->getStoreIdPink($pinkId, $uid)]);
            if ($this->services->getIsOrderPink($pinkId, $uid))
                return app('json')->status('ORDER_EXIST', '订单生成失败，你已经参加该团了，请先支付订单', ['orderId' => $this->services->getStoreIdPink($pinkId, $uid)]);
            if (!CacheService::checkStock(md5($pinkId), 1, 3) || !CacheService::popStock(md5($pinkId), 1, 3)) {
                return app('json')->fail('该团人员已满');
            }
        }
        if ($from != 'pc') {
            if (!$this->services->checkPaytype($payType)) {
                return app('json')->fail('暂不支持该支付方式，请刷新页面或者联系管理员');
            }
        } else {
            $payType = 'pc';
        }
        $isChannel = $this->getChennel[$from] ?? ($request->isApp() ? 0 : 1);

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
                    return app('json')->fail('您购买的商品库存已不足' . $item['cart_num'] . $item['productInfo']['unit_name']);
                }
            }
        }
        $virtual_type = $cartGroup['cartInfo'][0]['productInfo']['virtual_type'] ?? 0;
        $order = $createServices->createOrder($uid, $key, $cartGroup, $request->user()->toArray(), $addressId, $payType, !!$useIntegral, $couponId, $mark, $combinationId, $pinkId, $seckill_id, $bargainId, $isChannel, $shipping_type, $real_name, $phone, $storeId, !!$news, $advanceId, $virtual_type);
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
            return app('json')->fail('订单生成失败');
        }
        $orderId = $order['order_id'];
        $orderInfo = $this->services->getOne(['order_id' => $orderId]);
        if (!$orderInfo || !isset($orderInfo['paid'])) {
            return app('json')->fail('支付订单不存在!');
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
                    if ($orderInfo['paid']) return app('json')->fail('支付已支付!');
                    //支付金额为0
                    if (bcsub((string)$orderInfo['pay_price'], '0', 2) <= 0) {
                        //创建订单jspay支付
                        /** @var StoreOrderSuccessServices $success */
                        $success = app()->make(StoreOrderSuccessServices::class);
                        $payPriceStatus = $success->zeroYuanPayment($orderInfo, $uid, PayServices::WEIXIN_PAY);
                        if ($payPriceStatus)//0元支付成功
                            return app('json')->status('success', '微信支付成功', $info);
                        else
                            return app('json')->status('pay_error');
                    } else {
                        /** @var OrderPayServices $payServices */
                        $payServices = app()->make(OrderPayServices::class);
                        if (!$from && $request->isApp()) {
                            $from = 'weixin';
                        }
                        $info['jsConfig'] = $payServices->orderPay($orderInfo, $from);
                        if ($from == 'weixinh5') {
                            return app('json')->status('wechat_h5_pay', '订单创建成功', $info);
                        } else {
                            return app('json')->status('wechat_pay', '订单创建成功', $info);
                        }
                    }
                    break;
                case PayServices::YUE_PAY:
                    /** @var YuePayServices $yueServices */
                    $yueServices = app()->make(YuePayServices::class);
                    $pay = $yueServices->yueOrderPay($orderInfo, $uid);
                    if ($pay['status'] === true)
                        return app('json')->status('success', '余额支付成功', $info);
                    else {
                        if (is_array($pay))
                            return app('json')->status($pay['status'], $pay['msg'], $info);
                        else
                            return app('json')->status('pay_error', $pay);
                    }
                    break;
                case PayServices::ALIAPY_PAY:
                    if (!$quitUrl && ($request->isH5() || $request->isWechat())) {
                        return app('json')->status('pay_error', '请传入支付宝支付回调URL', $info);
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
                            return app('json')->status('success', '支付宝支付成功', $info);
                        else
                            return app('json')->status('pay_error');
                    } else {
                        /** @var OrderPayServices $payServices */
                        $payServices = app()->make(OrderPayServices::class);
                        $info['jsConfig'] = $payServices->alipayOrder($orderInfo, $quitUrl, $from == 'routine');
                        $payKey = md5($orderInfo['order_id']);
                        CacheService::set($payKey, ['order_id' => $orderInfo['order_id'], 'other_pay_type' => false], 300);
                        $info['pay_key'] = $payKey;
                        return app('json')->status(PayServices::ALIAPY_PAY . '_pay', '订单创建成功', $info);
                    }
                    break;
                case PayServices::OFFLINE_PAY:
                case 'pc':
                    return app('json')->status('success', '订单创建成功', $info);
                    break;
            }
        } else return app('json')->fail('订单生成失败!');
    }

    /**
     * 订单 再次下单
     * @param Request $request
     * @return mixed
     */
    public function again(Request $request, StoreCartServices $services)
    {
        list($uni) = $request->postMore([
            ['uni', ''],
        ], true);
        if (!$uni) return app('json')->fail('参数错误!');
        $order = $this->services->getUserOrderDetail($uni, (int)$request->uid());
        if (!$order) return app('json')->fail('订单不存在!');
        $order = $this->services->tidyOrder($order, true);
        $cateId = [];
        foreach ($order['cartInfo'] as $v) {
            if ($v['combination_id']) return app('json')->fail('拼团商品不能再来一单，请在拼团商品内自行下单!');
            else if ($v['bargain_id']) return app('json')->fail('砍价商品不能再来一单，请在砍价商品内自行下单!');
            else if ($v['seckill_id']) return app('json')->fail('秒杀商品不能再来一单，请在秒杀商品内自行下单!');
            else if ($v['advance_id']) return app('json')->fail('预售商品不能再来一单，请在预售商品内自行下单!');
            else $cateId[] = $services->setCart($request->uid(), (int)$v['product_id'], (int)$v['cart_num'], isset($v['productInfo']['attrInfo']['unique']) ? $v['productInfo']['attrInfo']['unique'] : '', '0', true);
        }
        if (!$cateId) return app('json')->fail('再来一单失败，请重新下单!');
        return app('json')->successful('ok', ['cateId' => implode(',', $cateId)]);
    }


    /**
     * 订单支付
     * @param Request $request
     * @return mixed
     */
    public function pay(Request $request, StorePinkServices $services, OrderPayServices $payServices, YuePayServices $yuePayServices)
    {
        [$uni, $paytype, $from, $quitUrl] = $request->postMore([
            ['uni', ''],
            ['paytype', 'weixin'],
            ['from', 'weixin'],
            ['quitUrl', '']
        ], true);
        if (!$uni) return app('json')->fail('参数错误!');
        $order = $this->services->getUserOrderDetail($uni, (int)$request->uid());
        if (!$order)
            return app('json')->fail('订单不存在!');
        if ($order['paid'])
            return app('json')->fail('该订单已支付!');
        if ($order['pink_id'] && $services->isPinkStatus($order['pink_id'])) {
            return app('json')->fail('该订单已失效!');
        }
        if (!Cache::get('pay_' . $order['order_id'])) {
            switch ($from) {
                case 'weixin':
                    if (in_array($order->is_channel, [1, 2, 3])) {//0
                        $order['order_id'] = mt_rand(100, 999) . '_' . $order['order_id'];
                    }
                    break;
                case 'weixinh5':
                    if (in_array($order->is_channel, [0, 1, 3])) {
                        $order['order_id'] = mt_rand(100, 999) . '_' . $order['order_id'];
                    }
                    break;
                case 'routine':
                    if (in_array($order->is_channel, [0, 2, 3])) {
                        $order['order_id'] = mt_rand(100, 999) . '_' . $order['order_id'];
                    }
                    break;
                case 'pc':
                case 'aliapy':
                    $order['order_id'] = mt_rand(100, 999) . '_' . $order['order_id'];
                    break;
            }
        }
        $order['pay_type'] = $paytype; //重新支付选择支付方式
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
                    return app('json')->fail('请传入支付宝支付回调URL');
                }
                $isCode = $from == 'routine' || $from == 'pc';
                $jsConfig = $payServices->alipayOrder($order->toArray(), $quitUrl, $isCode);
                if ($isCode && !($jsConfig->invalid ?? false)) $jsConfig->invalid = time() + 60;
                $payKey = md5($order['order_id']);
                CacheService::set($payKey, ['order_id' => $order['order_id'], 'other_pay_type' => false], 300);
                return app('json')->status(PayServices::ALIAPY_PAY . '_pay', '订单创建成功', ['jsConfig' => $jsConfig, 'order_id' => $order['order_id'], 'pay_key' => $payKey]);
                break;
            case PayServices::YUE_PAY:
                $pay = $yuePayServices->yueOrderPay($order->toArray(), $request->uid());
                if ($pay['status'] === true)
                    return app('json')->status('success', '余额支付成功');
                else {
                    if (is_array($pay))
                        return app('json')->status($pay['status'], $pay['msg']);
                    else
                        return app('json')->status('pay_error', $pay);
                }
                break;
            case PayServices::OFFLINE_PAY:
                if ($this->services->setOrderTypePayOffline($order['order_id']))
                    return app('json')->status('success', '订单创建成功');
                else
                    return app('json')->status('success', '支付失败');
                break;
        }
        return app('json')->fail('支付方式错误');
    }

    /**
     * 支付宝单独支付
     * @param OrderPayServices $payServices
     * @param string $key
     * @param string $quitUrl
     * @return mixed
     */
    public function aliPay(OrderPayServices $payServices, OtherOrderServices $services, string $key, string $quitUrl)
    {
        if (!$key || !($orderCache = CacheService::get($key))) {
            return app('json')->fail('该订单无法支付');
        }
        if (!isset($orderCache['order_id'])) {
            return app('json')->fail('该订单无法支付');
        }
        $payType = isset($orderCache['other_pay_type']) && $orderCache['other_pay_type'] == true;
        if ($payType) {
            $orderInfo = $services->getOne(['order_id' => $orderCache['order_id'], 'is_del' => 0, 'paid' => 0]);
        } else {
            $orderInfo = $this->services->get(['order_id' => $orderCache['order_id'], 'paid' => 0, 'is_del' => 0]);
        }

        if (!$orderInfo) {
            return app('json')->fail('订单支付状态有误，无法进行支付');
        }
        if (!$quitUrl) {
            return app('json')->fail('请传入支付宝支付回调URL');
        }
        $payInfo = $payServices->alipayOrder($orderInfo->toArray(), $quitUrl);
        return app('json')->success(['pay_content' => $payInfo]);
    }

    /**
     * 订单列表
     * @param Request $request
     * @return mixed
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
        } elseif (in_array($where['status'], [0, 1, 2, 3, 4])) {
            $where['pid'] = 0;
        }
        $list = $this->services->getOrderApiList($where);
        return app('json')->successful($list);
    }

    /**
     * 订单详情
     * @param Request $request
     * @param $uni
     * @return mixed
     */
    public function detail(Request $request, StoreOrderEconomizeServices $services, $uni)
    {
        /** @var StoreOrderCartInfoServices $storeOrderCartInfoServices */
        $storeOrderCartInfoServices = app()->make(StoreOrderCartInfoServices::class);

        if (!strlen(trim($uni))) return app('json')->fail('参数错误');
        $order = $this->services->getUserOrderDetail($uni, (int)$request->uid(), ['split', 'invoice']);
        if (!$order) return app('json')->fail('订单不存在');
        $order = $order->toArray();
        $splitNum = [];
        if (isset($order['split']) && $order['split']) {
            foreach ($order['split'] as &$item) {
                $item = $this->services->tidyOrder($item, true);
                if ($item['_status']['_type'] == 3) {
                    foreach ($item['cartInfo'] ?: [] as $key => $product) {
                        $item['cartInfo'][$key]['add_time'] = isset($product['add_time']) ? date('Y-m-d H:i', (int)$product['add_time']) : '时间错误';
                    }
                }
            }
            $splitNum = $storeOrderCartInfoServices->getSplitCartNum($order['cart_id']);
        }
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
        $order['system_store'] = false;
        if ($order['store_id']) {
            /** @var SystemStoreServices $storeServices */
            $storeServices = app()->make(SystemStoreServices::class);
            $order['system_store'] = $storeServices->getStoreDispose($order['store_id']);
        }
        if (($order['shipping_type'] === 2 || $order['delivery_uid'] != 0) && $order['verify_code']) {
            $name = $order['verify_code'] . '.jpg';
            /** @var SystemAttachmentServices $attachmentServices */
            $attachmentServices = app()->make(SystemAttachmentServices::class);
            $imageInfo = $attachmentServices->getInfo(['name' => $name]);
            $siteUrl = sys_config('site_url');
            if (!$imageInfo) {
                $imageInfo = UtilService::getQRCodePath($order['verify_code'], $name);
                if (is_array($imageInfo)) {
                    $attachmentServices->attachmentAdd($imageInfo['name'], $imageInfo['size'], $imageInfo['type'], $imageInfo['dir'], $imageInfo['thumb_path'], 1, $imageInfo['image_type'], $imageInfo['time'], 2);
                    $url = $imageInfo['dir'];
                } else
                    $url = '';
            } else $url = $imageInfo['att_dir'];
            if (isset($imageInfo['image_type']) && $imageInfo['image_type'] == 1) $url = $siteUrl . $url;
            $order['code'] = $url;
        }
        $order['mapKey'] = sys_config('tengxun_map_key');
        $order['yue_pay_status'] = (int)sys_config('balance_func_status') && (int)sys_config('yue_pay_status') == 1 ? (int)1 : (int)2;//余额支付 1 开启 2 关闭
        $order['pay_weixin_open'] = (int)sys_config('pay_weixin_open') ?? 0;//微信支付 1 开启 0 关闭
        $order['ali_pay_status'] = sys_config('ali_pay_status') ? true : false;//支付包支付 1 开启 0 关闭
        $orderData = $this->services->tidyOrder($order, true, true);
        $vipTruePrice = 0;
        foreach ($orderData['cartInfo'] ?? [] as $key => $cart) {
            $vipTruePrice = bcadd((string)$vipTruePrice, (string)$cart['vip_sum_truePrice'], 2);
            if (isset($splitNum[$cart['id']])) {
                $orderData['cartInfo'][$key]['cart_num'] = $cart['cart_num'] - $splitNum[$cart['id']];
                if ($orderData['cartInfo'][$key]['cart_num'] == 0) unset($orderData['cartInfo'][$key]);
            }
        }
        $orderData['cartInfo'] = array_merge($orderData['cartInfo']);
        $orderData['vip_true_price'] = $vipTruePrice;
        $economize = $services->get(['order_id' => $order['order_id']], ['postage_price', 'member_price']);
        if ($economize) {
            $orderData['postage_price'] = $economize['postage_price'];
            $orderData['member_price'] = $economize['member_price'];
        } else {
            $orderData['postage_price'] = 0;
            $orderData['member_price'] = 0;
        }
        $orderData['routine_contact_type'] = sys_config('routine_contact_type', 0);
        /** @var UserInvoiceServices $userInvoice */
        $userInvoice = app()->make(UserInvoiceServices::class);
        $invoice_func = $userInvoice->invoiceFuncStatus();
        $orderData['invoice_func'] = $invoice_func['invoice_func'];
        $orderData['special_invoice'] = $invoice_func['special_invoice'];
        $orderData['refund_cartInfo'] = $orderData['cartInfo'];
        $orderData['refund_total_num'] = $orderData['total_num'];
        $orderData['refund_pay_price'] = $orderData['pay_price'];
        $is_apply_refund = true;
        if (in_array($order['pid'], [0, -1])) {
            $cart_infos = $storeOrderCartInfoServices->getSplitCartList((int)$order['id'], 'surplus_num,cart_info');
            $orderData['refund_cartInfo'] = [];
            $orderData['refund_total_num'] = $orderData['refund_pay_price'] = 0;
            if ($cart_infos) {
                $cart_info = [];
                foreach ($cart_infos as $cart) {
                    $info = $cart['cart_info'];
                    $info['cart_num'] = $cart['surplus_num'];
                    $cart_info[] = $info;
                }
                $orderData['refund_cartInfo'] = $cart_info;
                /** @var StoreOrderComputedServices $orderComputeServices */
                $orderComputeServices = app()->make(StoreOrderComputedServices::class);
                $orderData['refund_total_num'] = $orderComputeServices->getOrderSumPrice($cart_info, 'cart_num', false);
                $orderData['refund_pay_price'] = $orderComputeServices->getOrderSumPrice($cart_info, 'truePrice');
            } else {//主订单已全部发货 不可申请退款
                $is_apply_refund = false;
            }
        }
        $orderData['is_apply_refund'] = $is_apply_refund;
        return app('json')->successful('ok', $orderData);
    }

    /**
     * 退款订单详情
     * @param Request $request
     * @param $uni
     * @param string $cartId
     * @return mixed
     */
    public function refund_detail(Request $request, $uni, $cartId = '')
    {
        if (!strlen(trim($uni))) return app('json')->fail('参数错误');
        /** @var StoreOrderCartInfoServices $storeOrderCartInfoServices */
        $storeOrderCartInfoServices = app()->make(StoreOrderCartInfoServices::class);
        $order = $this->services->getUserOrderDetail($uni, (int)$request->uid(), ['split', 'invoice']);
        if (!$order) return app('json')->fail('订单不存在');
        $order = $order->toArray();
        $orderData = $this->services->tidyOrder($order, true, true);
        $splitNum = $storeOrderCartInfoServices->getSplitCartNum($order['cart_id']);
        foreach ($orderData['cartInfo'] ?? [] as $key => $cart) {
            $orderData['cartInfo'][$key]['one_postage_price'] = bcdiv($cart['postage_price'], $cart['cart_num'], 2);
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
        return app('json')->successful('ok', $orderData);
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
        if (!$uni) return app('json')->fail('参数错误!');
        $res = $this->services->removeOrder($uni, (int)$request->uid());
        if ($res) {
            return app('json')->successful();
        } else {
            return app('json')->fail('删除失败');
        }
    }

    /**
     * 订单收货
     * @param Request $request
     * @return mixed
     */
    public function take(Request $request, StoreOrderTakeServices $services, StoreCouponIssueServices $issueServices)
    {
        list($uni) = $request->postMore([
            ['uni', ''],
        ], true);
        if (!$uni) return app('json')->fail('参数错误!');
        $order = $services->takeOrder($uni, (int)$request->uid());
        if ($order) {
            return app('json')->successful('收货成功');
        } else
            return app('json')->fail('收货失败');
    }


    /**
     * 订单 查看物流
     * @param Request $request
     * @param $uni
     * @return mixed
     */
    public function express(Request $request, StoreOrderCartInfoServices $services, ExpressServices $expressServices, $uni, $type = '')
    {
        if (!$uni || !($order = $this->services->getUserOrderDetail($uni, $request->uid()))) return app('json')->fail('查询订单不存在!');
        if ($type != 'refund' && ($order['delivery_type'] != 'express' || !$order['delivery_id'])) return app('json')->fail('该订单不存在快递单号!');
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
        return app('json')->successful([
            'order' => $orderInfo,
            'express' => [
                'result' => ['list' => $expressServices->query($cacheName, $express, $orderInfo['delivery_code'])
                ]
            ]
        ]);
    }

    /**
     * 订单评价
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function comment(Request $request, StoreOrderCartInfoServices $cartInfoServices, StoreProductReplyServices $replyServices)
    {

        $group = $request->postMore([
            ['unique', ''], ['comment', ''], ['pics', ''], ['product_score', 5], ['service_score', 5]
        ]);
        $unique = $group['unique'];
        unset($group['unique']);
        if (!$unique) return app('json')->fail('参数错误!');
        $cartInfo = $cartInfoServices->getOne(['unique' => $unique]);
        $uid = $request->uid();
        $user_info = $request->user();
        $group['nickname'] = $user_info['nickname'];
        $group['avatar'] = $user_info['avatar'];
        if (!$cartInfo) return app('json')->fail('评价商品不存在!');
        $orderUid = $this->services->value(['id' => $cartInfo['oid']], 'uid');
        if ($uid != $orderUid) return app('json')->fail('评价商品不存在!');
        if ($replyServices->be(['oid' => $cartInfo['oid'], 'unique' => $unique]))
            return app('json')->fail('该商品已评价!');
        $group['comment'] = htmlspecialchars(trim($group['comment']));
        if ($group['product_score'] < 1) return app('json')->fail('请为商品评分');
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

        $res = $replyServices->save($group);
        if (!$res) {
            return app('json')->fail('评价失败!');
        }
        try {
            $this->services->checkOrderOver($replyServices, $cartInfoServices->getCartColunm(['oid' => $cartInfo['oid']], 'unique', ''), $cartInfo['oid']);
        } catch (\Exception $e) {
            return app('json')->fail($e->getMessage());
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
            return app('json')->successful(['to_lottery' => false]);
        }
        $lottery = $lottery->toArray();
        try {
            $luckLotteryServices->checkoutUserAuth($uid, (int)$lottery['id'], [], $lottery);
            $lottery_num = $luckLotteryServices->getLotteryNum($uid, (int)$lottery['id'], [], $lottery);
            if ($lottery_num > 0) return app('json')->successful(['to_lottery' => true]);
        } catch (\Exception $e) {
            return app('json')->successful(['to_lottery' => false]);
        }
    }

    /**
     * 订单统计数据
     * @param Request $request
     * @return mixed
     */
    public function data(Request $request)
    {
        return app('json')->successful($this->services->getOrderData((int)$request->uid(), true));
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
    public function refund_verify(Request $request, StoreOrderRefundServices $services)
    {
        $data = $request->postMore([
            ['text', ''],
            ['refund_reason_wap_img', ''],
            ['refund_reason_wap_explain', ''],
            ['uni', ''],
            ['refund_type', 1],
            ['cart_id', 0],
            ['refund_num', 0]
        ]);
        $uni = $data['uni'];
        unset($data['uni']);
        if ($data['refund_reason_wap_img'] != '') {
            $data['refund_reason_wap_img'] = explode(',', $data['refund_reason_wap_img']);
        } else {
            $data['refund_reason_wap_img'] = [];
        }
        if (!$uni || $data['text'] == '' || $data['refund_num'] <= 0) return app('json')->fail('参数错误!');
        $res = $services->orderApplyRefund($this->services->getUserOrderDetail($uni, (int)$request->uid()), $data['text'], $data['refund_reason_wap_explain'], $data['refund_reason_wap_img'], $data['refund_type'], $data['cart_id'], $data['refund_num']);
        if ($res)
            return app('json')->successful('提交申请成功');
        else
            return app('json')->fail('提交失败');
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
        if ($id == '' || $express_id == '') return app('json')->fail('参数错误!');
        $res = $services->editRefundExpress($id, $express_id);
        if ($res)
            return app('json')->successful('提交快递单号成功');
        else
            return app('json')->fail('提交失败');
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
        list($id) = $request->postMore([['id', 0]], true);
        if (!$id) return app('json')->fail('参数错误');
        if ($this->services->cancelOrder($id, (int)$request->uid()))
            return app('json')->successful('取消订单成功');
        return app('json')->fail('取消订单失败');
    }


    /**
     * 订单商品信息
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function product(Request $request, StoreOrderCartInfoServices $services)
    {
        list($unique) = $request->postMore([['unique', '']], true);
        if (!$unique || !($cartInfo = $services->getOne(['unique' => $unique]))) return app('json')->fail('评价商品不存在!');
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
        return app('json')->successful($cartProduct);
    }

    /**
     * 门店核销
     * @param Request $request
     */
    public function order_verific(Request $request, StoreOrderWriteOffServices $services)
    {
        list($verifyCode, $isConfirm) = $request->postMore([
            ['verify_code', ''],
            ['is_confirm', 0]
        ], true);
        if (!$verifyCode) return app('json')->fail('Lack of write-off code');
        $uid = $request->uid();
        $orderInfo = $services->writeOffOrder($verifyCode, (int)$isConfirm, $uid);
        if ($isConfirm == 0) {
            return app('json')->success($orderInfo);
        }
        return app('json')->success('Write off successfully');
    }
}
