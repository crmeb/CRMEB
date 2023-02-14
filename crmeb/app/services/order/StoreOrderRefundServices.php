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

use app\dao\order\StoreOrderRefundDao;
use app\jobs\ProductLogJob;
use app\services\activity\advance\StoreAdvanceServices;
use app\services\activity\bargain\StoreBargainServices;
use app\services\activity\combination\StoreCombinationServices;
use app\services\activity\combination\StorePinkServices;
use app\services\activity\seckill\StoreSeckillServices;
use app\services\BaseServices;
use app\services\activity\coupon\StoreCouponIssueUserServices;
use app\services\activity\coupon\StoreCouponUserServices;
use app\services\pay\PayServices;
use app\services\product\product\StoreProductServices;
use app\services\shipping\ExpressServices;
use app\services\statistic\CapitalFlowServices;
use app\services\user\UserBillServices;
use app\services\user\UserBrokerageServices;
use app\services\user\UserMoneyServices;
use app\services\user\UserServices;
use crmeb\exceptions\AdminException;
use crmeb\exceptions\ApiException;
use crmeb\services\AliPayService;
use crmeb\services\CacheService;
use crmeb\services\FormBuilder as Form;
use crmeb\services\pay\Pay;
use crmeb\services\workerman\ChannelService;


/**
 * 订单退款
 * Class StoreOrderRefundServices
 * @method getOrderRefundMoneyByWhere
 * @package app\services\order
 */
class StoreOrderRefundServices extends BaseServices
{
    /**
     * 订单services
     * @var StoreOrderServices
     */
    protected $storeOrderServices;

    /**
     * 构造方法
     * StoreOrderRefundServices constructor.
     * @param StoreOrderRefundDao $dao
     */
    public function __construct(StoreOrderRefundDao $dao, StoreOrderServices $storeOrderServices)
    {
        $this->dao = $dao;
        $this->storeOrderServices = $storeOrderServices;
    }

    /**
     * 订单退款表单
     * @param int $id
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function refundOrderForm(int $id, $type = 'refund')
    {
        if ($type == 'refund') {//售后订单
            $orderRefund = $this->dao->get($id);
            if (!$orderRefund) {
                throw new AdminException(100026);
            }
            $order = $this->storeOrderServices->get((int)$orderRefund['store_order_id']);
            if (!$order) {
                throw new AdminException(100026);
            }
            if (!$order['paid']) {
                throw new AdminException(400488);
            }
            if ($orderRefund['refund_price'] > 0 && in_array($orderRefund['refund_type'], [1, 5])) {
                if ($orderRefund['refund_price'] <= $orderRefund['refunded_price']) {
                    throw new AdminException(400485);
                }
            }
            $f[] = Form::input('order_id', '退款单号', $orderRefund->getData('order_id'))->disabled(true);
            $f[] = Form::number('refund_price', '退款金额', (float)bcsub((string)$orderRefund->getData('refund_price'), (string)$orderRefund->getData('refunded_price'), 2))->min(0)->required('请输入退款金额');
            return create_form('退款处理', $f, $this->url('/refund/refund/' . $id), 'PUT');
        } else {//订单主动退款
            $order = $this->storeOrderServices->get((int)$id);
            if (!$order) {
                throw new AdminException(100026);
            }
            if (!$order['paid']) {
                throw new AdminException(400488);
            }
            if ($order['pay_price'] > 0 && in_array($order['refund_status'], [0, 1])) {
                if ($order['pay_price'] <= $order['refund_price']) {
                    throw new AdminException(400485);
                }
            }
            $f[] = Form::input('order_id', '退款单号', $order->getData('order_id'))->disabled(true);
            $f[] = Form::number('refund_price', '退款金额', (float)bcsub((string)$order->getData('pay_price'), (string)$order->getData('refund_price'), 2))->precision(2)->required('请输入退款金额');
            return create_form('退款处理', $f, $this->url('/order/refund/' . $id), 'PUT');
        }
    }

    /**
     * 同意退款：拆分退款单、退积分、佣金等
     * @param int $id
     * @param array $refundData
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function agreeRefund(int $id, array $refundData)
    {
        $order = $this->transaction(function () use ($id, $refundData) {
            //退款拆分
            $orderRefundInfo = $this->dao->get($id);
            if (!$orderRefundInfo) throw new AdminException(100026);
            $cart_ids = [];
            if ($orderRefundInfo['cart_info']) {
                foreach ($orderRefundInfo['cart_info'] as $cart) {
                    $cart_ids[] = [
                        'cart_id' => $cart['id'],
                        'cart_num' => $cart['cart_num'],
                    ];
                }
            }
            if (!$cart_ids) return false;
            $orderInfo = $this->storeOrderServices->get($orderRefundInfo['store_order_id']);
            /** @var StoreOrderSplitServices $storeOrderSplitServices */
            $storeOrderSplitServices = app()->make(StoreOrderSplitServices::class);
            [$splitOrderInfo, $otherOrder] = $storeOrderSplitServices->equalSplit($orderRefundInfo['store_order_id'], $cart_ids, $orderInfo);


            //回退积分和优惠卷
            if (!$this->integralAndCouponBack($splitOrderInfo)) {
                throw new AdminException(400489);
            }
            //退拼团
            if ($splitOrderInfo['pid'] == 0 && !$splitOrderInfo['pink_id']) {
                /** @var StorePinkServices $pinkServices */
                $pinkServices = app()->make(StorePinkServices::class);
                if (!$pinkServices->setRefundPink($splitOrderInfo)) {
                    throw new AdminException(400490);
                }
            }

            //退佣金
            /** @var UserBrokerageServices $userBrokerageServices */
            $userBrokerageServices = app()->make(UserBrokerageServices::class);
            if (!$userBrokerageServices->orderRefundBrokerageBack($splitOrderInfo)) {
                throw new AdminException(400491);
            }

            //回退库存
            if ($splitOrderInfo['status'] == 0) {
                /** @var StoreOrderStatusServices $services */
                $services = app()->make(StoreOrderStatusServices::class);
                if (!$services->count(['oid' => $splitOrderInfo['id'], 'change_type' => 'refund_price'])) {
                    /** @var StoreOrderServices $orderServices */
                    $orderServices = app()->make(StoreOrderServices::class);
                    $this->regressionStock($orderServices->get($splitOrderInfo['id']));
                }
            }

            //退金额
            if ($refundData['refund_price'] > 0) {
                if (!isset($refundData['refund_id']) || !$refundData['refund_id']) {
                    mt_srand();
                    $refundData['refund_id'] = $splitOrderInfo['order_id'] . rand(100, 999);
                }
                if ($splitOrderInfo['pid'] > 0) {//子订单
                    $refundOrder = $this->storeOrderServices->get((int)$splitOrderInfo['pid']);
                    $refundData['pay_price'] = $refundOrder['pay_price'];
                } else {
                    $refundOrder = $splitOrderInfo;
                }
                switch ($refundOrder['pay_type']) {
                    case PayServices::WEIXIN_PAY:
                        $no = $refundOrder['order_id'];
                        if ($refundOrder['trade_no']) {
                            $no = $refundOrder['trade_no'];
                            $refundData['type'] = 'trade_no';
                        }
                        if (sys_config('pay_wechat_type')) {
                            $drivers = 'v3_wechat_pay';
                        } else {
                            $drivers = 'wechat_pay';
                        }
                        /** @var Pay $pay */
                        $pay = app()->make(Pay::class, [$drivers]);
                        if ($refundOrder['is_channel'] == 1) {
                            $refundData['trade_no'] = $refundOrder['trade_no'];
                            $refundData['pay_new_weixin_open'] = sys_config('pay_new_weixin_open');
                            //小程序退款
                            $pay->refund($no, $refundData);//小程序
                        } else {
                            //微信公众号退款
                            $refundData['wechat'] = true;
                            $pay->refund($no, $refundData);//公众号
                        }
                        break;
                    case PayServices::YUE_PAY:
                        //余额退款
                        if (!$this->yueRefund($refundOrder, $refundData)) {
                            throw new AdminException(400492);
                        }
                        break;
                    case PayServices::ALIAPY_PAY:
                        mt_srand();
                        $refund_id = $refundData['refund_id'] ?? $refundOrder['order_id'] . rand(100, 999);
                        //支付宝退款
                        AliPayService::instance()->refund(strpos($refundOrder['trade_no'], '_') !== false ? $refundOrder['trade_no'] : $refundOrder['order_id'], floatval($refundData['refund_price']), $refund_id);
                        break;
                    case PayServices::ALLIN_PAY:
                        /** @var Pay $pay */
                        $pay = app()->make(Pay::class, ['allin_pay']);
                        /** @var StoreOrderServices $orderServices */
                        $orderServices = app()->make(StoreOrderServices::class);
                        $trade_no = $orderServices->value(['id' => $orderRefundInfo['store_order_id']], 'trade_no');
                        $pay->refund($trade_no, [
                            'order_id' => $refundOrder['order_id'],
                            'refund_price' => $refundData['refund_price']
                        ]);
                        break;
                }
            }
            //订单记录
            /** @var StoreOrderStatusServices $statusService */
            $statusService = app()->make(StoreOrderStatusServices::class);
            $statusService->save([
                'oid' => $splitOrderInfo['id'],
                'change_type' => 'refund_price',
                'change_message' => '退款给用户：' . $refundData['refund_price'] . '元',
                'change_time' => time()
            ]);
            $this->storeOrderServices->update($splitOrderInfo['id'], [
                'status' => -2,
                'refund_status' => 2,
                'refund_type' => $orderRefundInfo['refund_type'],
                'refund_express' => $orderRefundInfo['refund_express'],
                'refund_express_name' => $orderRefundInfo['refund_express_name'],
                'refund_reason_wap_img' => $orderRefundInfo['refund_img'],
                'refund_reason_wap_explain' => $orderRefundInfo['refund_explain'],
                'refund_reason_time' => $orderRefundInfo['refunded_time'],
                'refund_reason_wap' => $orderRefundInfo['refund_reason'],
                'refund_price' => $refundData['refund_price'],
            ], 'id');
            $splitOrderInfo = $this->storeOrderServices->get($splitOrderInfo['id']);
            $this->dao->update($id, ['store_order_id' => $splitOrderInfo['id']]);
            if ($otherOrder['id'] != 0 && $orderInfo['id'] != $otherOrder['id']) {//拆分生成新订单了
                //修改原订单还在申请的退款单
                $this->dao->update(['store_order_id' => $orderInfo['id']], ['store_order_id' => $otherOrder['id']]);
            }

            /** @var CapitalFlowServices $capitalFlowServices */
            $capitalFlowServices = app()->make(CapitalFlowServices::class);
            /** @var UserServices $userServices */
            $userServices = app()->make(UserServices::class);
            $userInfo = $userServices->get($splitOrderInfo['uid']);
            $splitOrderInfo['nickname'] = $userInfo['nickname'];
            $splitOrderInfo['phone'] = $userInfo['phone'];
            if ($splitOrderInfo['pay_type'] == 'alipay' || $splitOrderInfo['pay_type'] == 'weixin' || $splitOrderInfo['pay_type'] == 'offline') {
                $capitalFlowServices->setFlow($splitOrderInfo, 'refund');
            }

            return $splitOrderInfo;
        });
        //订单退款记录
        ProductLogJob::dispatch(['refund', ['uid' => $order['uid'], 'order_id' => $order['id']]]);
        //订单同意退款事件
        event('order.refund', [$refundData, $order, 'order_refund']);
        event('notice.notice', [['data' => $refundData, 'order' => $order], 'order_refund']);
        return true;
    }

    /**
     * 商家同意用户退货
     * @param $id
     * @return bool
     */
    public function agreeExpress($id)
    {
        $order = $this->dao->get($id, ['refund_type']);
        if (!$order) throw new AdminException(100026);
        if ($order['refund_type'] == 4) {
            return true;
        }
        $this->dao->update($id, ['refund_type' => 4], 'id');
        return true;
    }

    /**
     * 订单退款处理
     * @param int $type
     * @param $order
     * @param array $refundData
     * @return mixed
     */
    public function payOrderRefund(int $type, $order, array $refundData)
    {
        return $this->transaction(function () use ($type, $order, $refundData) {

            //回退积分和优惠卷
            if (!$this->integralAndCouponBack($order)) {
                throw new AdminException(400489);
            }
            //虚拟商品优惠券退款处理
            if ($order['virtual_type'] == 2) {
                /** @var StoreCouponUserServices $couponUser */
                $couponUser = app()->make(StoreCouponUserServices::class);
                $res = $couponUser->delUserCoupon(['cid' => $order['virtual_info'], 'uid' => $order['uid'], 'status' => 0]);
                if (!$res) throw new AdminException(400493);
                /** @var StoreCouponIssueUserServices $couponIssueUser */
                $couponIssueUser = app()->make(StoreCouponIssueUserServices::class);
                $couponIssueUser->delIssueUserCoupon(['issue_coupon_id' => $order['virtual_info'], 'uid' => $order['uid']]);
            }

            //退拼团
            if ($type == 1) {
                /** @var StorePinkServices $pinkServices */
                $pinkServices = app()->make(StorePinkServices::class);
                if (!$pinkServices->setRefundPink($order)) {
                    throw new AdminException(400490);
                }
            }

            //退佣金
            /** @var UserBrokerageServices $userBrokerageServices */
            $userBrokerageServices = app()->make(UserBrokerageServices::class);
            if (!$userBrokerageServices->orderRefundBrokerageBack($order)) {
                throw new AdminException(400491);
            }


            //回退库存
            if ($order['status'] == 0) {
                /** @var StoreOrderStatusServices $services */
                $services = app()->make(StoreOrderStatusServices::class);
                if (!$services->count(['oid' => $order['id'], 'change_type' => 'refund_price'])) {
                    $this->regressionStock($order);
                }
            }

            //退金额
            if ($refundData['refund_price'] > 0) {
                if (!isset($refundData['refund_id']) || !$refundData['refund_id']) {
                    mt_srand();
                    $refundData['refund_id'] = $order['order_id'] . rand(100, 999);
                }
                if ($order['pid'] > 0) {//子订单
                    $refundOrder = $this->storeOrderServices->get((int)$order['pid']);
                    $refundData['pay_price'] = $refundOrder['pay_price'];
                } else {
                    $refundOrder = $order;
                }
                switch ($refundOrder['pay_type']) {
                    case PayServices::WEIXIN_PAY:
                        $no = $refundOrder['order_id'];
                        if ($refundOrder['trade_no']) {
                            $no = $refundOrder['trade_no'];
                            $refundData['type'] = 'trade_no';
                        }
                        /** @var Pay $pay */
                        $pay = app()->make(Pay::class);
                        if ($refundOrder['is_channel'] == 1) {
                            //小程序退款
                            $pay->refund($no, $refundData);//小程序
                        } else {
                            //微信公众号退款
                            $refundData['wechat'] = true;
                            $pay->refund($no, $refundData);//公众号
                        }
                        break;
                    case PayServices::YUE_PAY:
                        //余额退款
                        if (!$this->yueRefund($refundOrder, $refundData)) {
                            throw new AdminException(400492);
                        }
                        break;
                    case PayServices::ALIAPY_PAY:
                        mt_srand();
                        $refund_id = $refundData['refund_id'] ?? $refundOrder['order_id'] . rand(100, 999);
                        //支付宝退款
                        AliPayService::instance()->refund(strpos($refundOrder['trade_no'], '_') !== false ? $refundOrder['trade_no'] : $refundOrder['order_id'], floatval($refundData['refund_price']), $refund_id);
                        break;
                }
            }
            //修改开票数据退款状态
            $orderInvoiceServices = app()->make(StoreOrderInvoiceServices::class);
            $orderInvoiceServices->update(['order_id' => $order['id']], ['is_refund' => 1]);
        });
    }

    /**
     * 余额退款
     * @param $order
     * @param array $refundData
     * @return bool
     */
    public function yueRefund($order, array $refundData)
    {
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $userMoney = $userServices->value(['uid' => $order['uid']], 'now_money');
        $res = $userServices->bcInc($order['uid'], 'now_money', $refundData['refund_price'], 'uid');
        /** @var UserMoneyServices $userMoneyServices */
        $userMoneyServices = app()->make(UserMoneyServices::class);
        return $res && $userMoneyServices->income('pay_product_refund', $order['uid'], $refundData['refund_price'], bcadd((string)$userMoney, (string)$refundData['refund_price'], 2), $order['id']);
    }

    /**
     * 回退积分和优惠卷
     * @param $order
     * @return bool
     */
    public function integralAndCouponBack($order)
    {
        $res = true;
        //回退优惠卷 拆分子订单不退优惠券
        if (!$order['pid'] && $order['coupon_id'] && $order['coupon_price']) {
            /** @var StoreCouponUserServices $coumonUserServices */
            $coumonUserServices = app()->make(StoreCouponUserServices::class);
            $res = $res && $coumonUserServices->recoverCoupon((int)$order['coupon_id']);
        }
        //回退积分
        $order = $this->regressionIntegral($order);
        /** @var StoreOrderStatusServices $statusService */
        $statusService = app()->make(StoreOrderStatusServices::class);
        $statusService->save([
            'oid' => $order['id'],
            'change_type' => 'integral_back',
            'change_message' => '商品退积分',
            'change_time' => time()
        ]);
        return $res && $order->save();
    }

    /**
     * 回退使用积分和赠送积分
     * @param $order
     * @return bool
     */
    public function regressionIntegral($order)
    {
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $userInfo = $userServices->get($order['uid'], ['integral']);
        if (!$userInfo) {
            $order->back_integral = $order->use_integral;
            return $order;
        }
        $integral = $userInfo['integral'];
        if ($order['status'] == -2 || $order['is_del']) {
            return $order;
        }

        $res1 = $res2 = $res3 = $res4 = true;
        //订单赠送积分
        /** @var UserBillServices $userBillServices */
        $userBillServices = app()->make(UserBillServices::class);
        $order_gain = $userBillServices->sum([
            'category' => 'integral',
            'type' => 'gain',
            'link_id' => $order['id'],
            'uid' => $order['uid']
        ], 'number');
        //商品赠送
        $product_gain = $userBillServices->sum([
            'category' => 'integral',
            'type' => 'product_gain',
            'link_id' => $order['id'],
            'uid' => $order['uid']
        ], 'number');

        $give_integral = $order_gain + $product_gain;
        if ($give_integral) {
            //判断订单是否已经回退积分
            $count = $userBillServices->count(['category' => 'integral', 'type' => 'integral_refund', 'link_id' => $order['id']]);
            if (!$count) {
                $res1 = $userServices->bcDec($order['uid'], 'integral', $give_integral);
                //记录赠送积分收回
                $integral = $integral - $give_integral;
                $res2 = $userBillServices->income('integral_refund', $order['uid'], $give_integral, $integral, $order['id']);
            }
        }
        //返还下单使用积分
        $use_integral = $order['use_integral'];
        if ($use_integral > 0) {
            $res3 = $userServices->bcInc($order['uid'], 'integral', $use_integral);
            //记录下单使用积分还回
            $res4 = $userBillServices->income('pay_product_integral_back', $order['uid'], (int)$use_integral, $integral + $use_integral, $order['id']);
        }
        if (!($res1 && $res2 && $res3 && $res4)) {
            throw new ApiException(400494);
        }
        if ($use_integral > $give_integral) {
            $order->back_integral = bcsub($use_integral, $give_integral, 2);
        }
        return $order;
    }

    /**
     * 回退库存
     * @param $order
     * @return bool
     */
    public function regressionStock($order)
    {
        if ($order['status'] == -2 || $order['is_del']) return true;
        $combination_id = $order['combination_id'];
        $seckill_id = $order['seckill_id'];
        $bargain_id = $order['bargain_id'];
        $advance_id = $order['advance_id'];
        $res5 = true;
        /** @var StoreOrderCartInfoServices $cartServices */
        $cartServices = app()->make(StoreOrderCartInfoServices::class);
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
        $cartInfo = $cartServices->getCartInfoList(['cart_id' => $order['cart_id']], ['cart_info']);
        foreach ($cartInfo as $cart) {
            $cart['cart_info'] = is_array($cart['cart_info']) ? $cart['cart_info'] : json_decode($cart['cart_info'], true);
            //增库存减销量
            $unique = isset($cart['cart_info']['productInfo']['attrInfo']) ? $cart['cart_info']['productInfo']['attrInfo']['unique'] : '';
            $cart_num = (int)$cart['cart_info']['cart_num'];
            $type = 0;
            if ($combination_id) {
                $type = 3;
                $res5 = $res5 && $pinkServices->incCombinationStock($cart_num, (int)$combination_id, $unique);
            } else if ($seckill_id) {
                $type = 1;
                $res5 = $res5 && $seckillServices->incSeckillStock($cart_num, (int)$seckill_id, $unique);
            } else if ($bargain_id) {
                $type = 2;
                $res5 = $res5 && $bargainServices->incBargainStock($cart_num, (int)$bargain_id, $unique);
            } else {
                $res5 = $res5 && $services->incProductStock($cart_num, (int)$cart['cart_info']['productInfo']['id'], $unique);
            }
            if ($type) CacheService::setStock($unique, $cart_num, $type, false);
        }
        return $res5;
    }


    /**
     * 同意退款成功发送模板消息和记录订单状态
     * @param $data
     * @param $order
     * @param $refund_price
     * @param $id
     */
    public function storeProductOrderRefundY($data, $order, $refund_price)
    {
        /** @var StoreOrderStatusServices $statusService */
        $statusService = app()->make(StoreOrderStatusServices::class);
        $statusService->save([
            'oid' => $order['id'],
            'change_type' => 'refund_price',
            'change_message' => '退款给用户：' . $refund_price . '元',
            'change_time' => time()
        ]);

        /** @var CapitalFlowServices $capitalFlowServices */
        $capitalFlowServices = app()->make(CapitalFlowServices::class);
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $userInfo = $userServices->get($order['uid']);
        $order['nickname'] = $userInfo['nickname'];
        $order['phone'] = $userInfo['phone'];
        if ($order['pay_type'] == 'alipay' || $order['pay_type'] == 'weixin' || $order['pay_type'] == 'offline') {
            $capitalFlowServices->setFlow($order, 'refund');
        }

        event('notice.notice', [['data' => $data, 'order' => $order], 'order_refund']);
    }

    /**
     * 同意退款退款失败写入订单记录
     * @param int $id
     * @param $refund_price
     */
    public function storeProductOrderRefundYFasle(int $id, $refund_price)
    {
        /** @var StoreOrderStatusServices $statusService */
        $statusService = app()->make(StoreOrderStatusServices::class);
        $statusService->save([
            'oid' => $id,
            'change_type' => 'refund_price',
            'change_message' => '退款给用户：' . $refund_price . '元失败',
            'change_time' => time()
        ]);
    }

    /**
     * 不退款记录订单变更状态
     * @param int $id
     * @param string $refundReason
     */
    public function storeProductOrderRefundNo(int $id, string $refundReason)
    {
        /** @var StoreOrderStatusServices $statusService */
        $statusService = app()->make(StoreOrderStatusServices::class);
        $statusService->save([
            'oid' => $id,
            'change_type' => 'refund_n',
            'change_message' => '不退款原因:' . $refundReason,
            'change_time' => time()
        ]);
    }


    /**
     * 不退款表单
     * @param int $id
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function noRefundForm(int $id)
    {
        $order = $this->dao->get($id);
        if (!$order) {
            throw new AdminException(100026);
        }
        $f[] = Form::input('order_id', '不退款单号', $order->getData('order_id'))->disabled(true);
        $f[] = Form::input('refund_reason', '不退款原因')->type('textarea')->required('请填写不退款原因');
        return create_form('不退款原因', $f, $this->url('refund/no_refund/' . $id), 'PUT');
    }

    /**
     * 拒绝退款
     * @param int $id
     * @param array $data
     * @param array $orderRefundInfo
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function refuseRefund(int $id, array $data, $orderRefundInfo = [])
    {
        if (!$orderRefundInfo) {
            $orderRefundInfo = $this->dao->get(['id' => $id, 'is_cancel' => 0]);
        }
        if (!$orderRefundInfo) {
            throw new ApiException(400495);
        }
        /** @var StoreOrderServices $storeOrderServices */
        $storeOrderServices = app()->make(StoreOrderServices::class);
        $this->transaction(function () use ($id, $data, $orderRefundInfo, $storeOrderServices) {
            //处理售后订单
            $this->dao->update($id, $data);
            //处理订单
            $oid = (int)$orderRefundInfo['store_order_id'];

            $storeOrderServices->update($oid, ['refund_status' => 0, 'refund_type' => 3]);
            //处理订单商品cart_info
            $this->cancelOrderRefundCartInfo($id, $oid, $orderRefundInfo);
            //记录
            /** @var StoreOrderStatusServices $statusService */
            $statusService = app()->make(StoreOrderStatusServices::class);
            $statusService->save([
                'oid' => $id,
                'change_type' => 'refund_n',
                'change_message' => '不退款原因:' . ($data['refund_reason'] ?? ''),
                'change_time' => time()
            ]);
        });
        event('notice.notice', [['orderInfo' => $orderRefundInfo], 'send_order_refund_no_status']);
        return true;
    }


    /**
     * 退积分表单创建
     * @param int $id
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function refundIntegralForm(int $id)
    {
        if (!$orderInfo = $this->dao->get($id))
            throw new AdminException(400118);
        if ($orderInfo->use_integral < 0 || $orderInfo->use_integral == $orderInfo->back_integral)
            throw new AdminException(400496);
        if (!$orderInfo->paid)
            throw new AdminException(400497);
        $f[] = Form::input('order_id', '退款单号', $orderInfo->getData('order_id'))->disabled(1);
        $f[] = Form::number('use_integral', '使用的积分', (float)$orderInfo->getData('use_integral'))->min(0)->disabled(1);
        $f[] = Form::number('use_integrals', '已退积分', (float)$orderInfo->getData('back_integral'))->min(0)->disabled(1);
        $f[] = Form::number('back_integral', '可退积分', (float)bcsub($orderInfo->getData('use_integral'), $orderInfo->getData('back_integral')))->min(0)->precision(0)->required('请输入可退积分');
        return create_form('退积分', $f, $this->url('/order/refund_integral/' . $id), 'PUT');
    }

    /**
     * 单独退积分处理
     * @param $orderInfo
     * @param $back_integral
     */
    public function refundIntegral($orderInfo, $back_integral)
    {
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $integral = $userServices->value(['uid' => $orderInfo['uid']], 'integral');
        return $this->transaction(function () use ($userServices, $orderInfo, $back_integral, $integral) {
            $res1 = $userServices->bcInc($orderInfo['uid'], 'integral', $back_integral, 'uid');
            /** @var UserBillServices $userBillServices */
            $userBillServices = app()->make(UserBillServices::class);
            $res2 = $userBillServices->income('pay_product_integral_back', $orderInfo['uid'], (int)$back_integral, $integral + $back_integral, $orderInfo['id']);
            /** @var StoreOrderStatusServices $statusService */
            $statusService = app()->make(StoreOrderStatusServices::class);
            $res3 = $statusService->save([
                'oid' => $orderInfo['id'],
                'change_type' => 'integral_back',
                'change_message' => '商品退积分:' . $back_integral,
                'change_time' => time()
            ]);
            $res4 = $orderInfo->save();
            $res = $res1 && $res2 && $res3 && $res4;
            if (!$res) {
                throw new AdminException(400498);
            }
            return true;
        });
    }

    /**
     * 订单申请退款
     * @param $uni
     * @param $uid
     * @param string $refundReasonWap
     * @param string $refundReasonWapExplain
     * @param array $refundReasonWapImg
     * @return bool|void
     */
    public function orderApplyRefund($order, string $refundReasonWap = '', string $refundReasonWapExplain = '', array $refundReasonWapImg = [], int $refundType = 0, $cart_id = 0, $refund_num = 0)
    {
        if (!$order) {
            throw new ApiException(410173);
        }
        if ($order['refund_status'] == 2) {
            throw new ApiException(410226);
        }
        if ($order['refund_status'] == 1) {
            throw new ApiException(410250);
        }
        if ($order['total_num'] < $refund_num) {
            throw new ApiException(410252);
        }
        $this->transaction(function () use ($order, $refundReasonWap, $refundReasonWapExplain, $refundReasonWapImg, $refundType, $refund_num, $cart_id) {
            $status = 0;
            $order_id = (int)$order['id'];
            if ($cart_id) {
                /** @var StoreOrderCartInfoServices $storeOrderCartInfoServices */
                $storeOrderCartInfoServices = app()->make(StoreOrderCartInfoServices::class);
                $cart_ids = [];
                $cart_ids[0] = ['cart_id' => $cart_id, 'cart_num' => $refund_num];
                /** @var StoreOrderSplitServices $storeOrderSplitServices */
                $storeOrderSplitServices = app()->make(StoreOrderSplitServices::class);
                //拆单
                $status = $order['status'];
                $order = $storeOrderSplitServices->split($order_id, $cart_ids, $order);
            } elseif (in_array($order['pid'], [0, -1]) && $this->storeOrderServices->count(['pid' => $order_id])) {
                /** @var StoreOrderCartInfoServices $storeOrderCartInfoServices */
                $storeOrderCartInfoServices = app()->make(StoreOrderCartInfoServices::class);
                $cart_info = $storeOrderCartInfoServices->getSplitCartList($order_id, 'cart_info');
                if (!$cart_info) {
                    throw new ApiException(410253);
                }
                $cart_ids = [];
                foreach ($cart_info as $key => $cart) {
                    $cart_ids[$key] = ['cart_id' => $cart['id'], 'cart_num' => $refund_num];
                }
                /** @var StoreOrderSplitServices $storeOrderSplitServices */
                $storeOrderSplitServices = app()->make(StoreOrderSplitServices::class);
                //拆单
                $status = $order['status'];
                $order = $storeOrderSplitServices->split($order_id, $cart_ids, $order);
            }

            $data = [
                'refund_status' => 1,
                'refund_reason_time' => time(),
                'refund_reason_wap' => $refundReasonWap,
                'refund_reason_wap_explain' => $refundReasonWapExplain,
                'refund_reason_wap_img' => json_encode($refundReasonWapImg),
                'refund_type' => $refundType
            ];
            if ($status) $data['status'] = $status;

            /** @var StoreOrderStatusServices $statusService */
            $statusService = app()->make(StoreOrderStatusServices::class);
            $res1 = false !== $statusService->save([
                    'oid' => $order['id'],
                    'change_type' => 'apply_refund',
                    'change_message' => '用户申请退款，原因：' . $refundReasonWap,
                    'change_time' => time()
                ]);
            $res2 = false !== $this->storeOrderServices->update(['id' => $order['id']], $data);
            $res = $res1 && $res2;
            if (!$res)
                throw new ApiException(410254);
            //子订单申请退款
            if ($order['pid'] > 0) {
                $p_order = $this->storeOrderServices->get((int)$order['pid']);
                $split_order = $this->storeOrderServices->count(['pid' => $order['pid'], 'refund_status' => 0]);
                if ($split_order || (!$split_order && $p_order['status'] == 4) || $cart_id) {
                    $this->storeOrderServices->update(['id' => $order['pid']], ['refund_status' => 3, 'refund_reason_time' => time()]);
                } else {
                    $this->storeOrderServices->update(['id' => $order['pid']], ['refund_status' => 4, 'refund_reason_time' => time()]);
                }
            } else {
                /** @var StoreOrderCartInfoServices $orderCartInfoService */
                $orderCartInfoService = app()->make(StoreOrderCartInfoServices::class);
//                if (!$orderCartInfoService->getSplitCartList()) {
//
//                }
            }
        });

        try {
            ChannelService::instance()->send('NEW_REFUND_ORDER', ['order_id' => $order['order_id']]);
        } catch (\Exception $e) {
        }
        //提醒推送
        event('notice.notice', [['order' => $order], 'send_order_apply_refund']);

        return true;

    }


    /**
     * 写入退款快递单号
     * @param $order
     * @param $express
     * @return bool
     */
    public function editRefundExpress($data)
    {
        $this->transaction(function () use ($data) {
            $id = $data['id'];
            $data['refund_type'] = 5;
            /** @var StoreOrderStatusServices $statusService */
            $statusService = app()->make(StoreOrderStatusServices::class);
            $res1 = false !== $statusService->save([
                    'oid' => $id,
                    'change_type' => 'refund_express',
                    'change_message' => '用户已退货，订单号：' . $data['refund_express'],
                    'change_time' => time()
                ]);
            if ($data['refund_img'] != '') unset($data['refund_img']);
            if ($data['refund_explain'] != '') unset($data['refund_explain']);
            $res2 = false !== $this->dao->update(['id' => $id], $data);
            $res = $res1 && $res2;
            if (!$res)
                throw new ApiException(100018);
        });
        return true;
    }

    /**
     * 订单申请退款
     * @param int $id
     * @param int $uid
     * @param array $order
     * @param array $cart_ids
     * @param int $refundType
     * @param float $refundPrice
     * @param array $refundData
     * @param int $isPink
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function applyRefund(int $id, int $uid, $order = [], array $cart_ids = [], int $refundType = 0, float $refundPrice = 0.00, array $refundData = [], $isPink = 0)
    {
        /** 查询订单是否存在 */
        /** @var StoreOrderServices $orderServices */
        $orderServices = app()->make(StoreOrderServices::class);
        if (!$order) {
            $order = $orderServices->get($id);
        }
        if (!$order) {
            throw new ApiException(410173);
        }

        $is_now = $this->dao->getCount([
            ['store_order_id', '=', $id],
            ['refund_type', 'in', [1, 2, 4, 5]],
            ['is_cancel', '=', 0],
            ['is_del', '=', 0],
            ['is_pink_cancel', '=', 0]
        ]);
        if ($is_now) throw new ApiException(410255);

        $refund_num = $order['total_num'];
        $refund_price = $order['pay_price'];
        /** @var StoreOrderCartInfoServices $storeOrderCartInfoServices */
        $storeOrderCartInfoServices = app()->make(StoreOrderCartInfoServices::class);
        //退部分
        $cartInfo = [];
        $cartInfos = $storeOrderCartInfoServices->getCartColunm(['oid' => $id], 'id,cart_id,cart_num,refund_num,cart_info');
        if ($cart_ids) {
            $cartInfo = array_combine(array_column($cartInfos, 'cart_id'), $cartInfos);
            $refund_num = 0;
            foreach ($cart_ids as $cart) {
                if ($cart['cart_num'] + $cartInfo[$cart['cart_id']]['refund_num'] > $cartInfo[$cart['cart_id']]['cart_num']) {
                    throw new ApiException(410252);
                }
                $refund_num = bcadd((string)$refund_num, (string)$cart['cart_num'], 0);
            }
            //总共申请多少件
            $total_num = array_sum(array_column($cart_ids, 'cart_num'));
            if ($total_num < $order['total_num']) {
                /** @var StoreOrderSplitServices $storeOrderSpliteServices */
                $storeOrderSpliteServices = app()->make(StoreOrderSplitServices::class);
                $cartInfos = $storeOrderSpliteServices->getSplitOrderCartInfo($id, $cart_ids, $order);
                $total_price = $pay_postage = 0;
                foreach ($cartInfos as $cart) {
                    $_info = is_string($cart['cart_info']) ? json_decode($cart['cart_info'], true) : $cart['cart_info'];
                    $total_price = bcadd((string)$total_price, bcmul((string)($_info['truePrice'] ?? 0), (string)$cart['cart_num'], 4), 2);
                    $pay_postage = bcadd((string)$pay_postage, (string)($_info['postage_price'] ?? 0), 2);
                }
                $refund_pay_price = bcadd((string)$total_price, (string)$pay_postage, 2);
                //订单实际支付金额
                $order_pay_price = bcsub((string)bcadd((string)$order['total_price'], (string)$order['pay_postage'], 2), (string)bcadd((string)$order['deduction_price'], (string)$order['coupon_price'], 2), 2);
                if ($order_pay_price != $order['pay_price'] && $refund_pay_price != $order_pay_price) {//有改价
                    $refund_price = bcmul((string)bcdiv((string)$order['pay_price'], (string)$order_pay_price, 4), (string)$refund_pay_price, 2);
                } else {
                    $refund_price = $refund_pay_price;
                }
            }
        } else {
            foreach ($cartInfos as $cart) {
                if ($cart['refund_num'] > 0) {
                    throw new ApiException(410252);
                }
            }
        }
        foreach ($cartInfos as &$cart) {
            $cart['cart_info'] = is_string($cart['cart_info']) ? json_decode($cart['cart_info'], true) : $cart['cart_info'];
        }
        $refundData['uid'] = $uid;
        $refundData['store_id'] = $order['store_id'];
        $refundData['store_order_id'] = $id;
        $refundData['refund_num'] = $refund_num;
        $refundData['refund_type'] = $refundType;
        $refundData['refund_price'] = $refund_price;
        $refundData['order_id'] = $order['refund_no'] = app()->make(StoreOrderCreateServices::class)->getNewOrderId('');
        $refundData['add_time'] = time();
        $refundData['cart_info'] = json_encode(array_column($cartInfos, 'cart_info'));
        $refundData['is_pink_cancel'] = $isPink;
        $res = $this->transaction(function () use ($id, $order, $cart_ids, $refundData, $storeOrderCartInfoServices, $cartInfo, $orderServices, $cartInfos) {
            /** @var StoreOrderStatusServices $statusService */
            $statusService = app()->make(StoreOrderStatusServices::class);
            $res1 = false !== $statusService->save([
                    'oid' => $order['id'],
                    'change_type' => 'apply_refund',
                    'change_message' => '用户申请退款，原因：' . $refundData['refund_reason'],
                    'change_time' => time()
                ]);
            $res2 = true;
            //添加退款数据
            /** @var StoreOrderRefundServices $storeOrderRefundServices */
            $storeOrderRefundServices = app()->make(StoreOrderRefundServices::class);
            $res3 = $storeOrderRefundServices->save($refundData);
            if (!$res3) {
                throw new ApiException(410251);
            }
            $res4 = true;
            if ($cart_ids) {
                //修改订单商品退款信息
                foreach ($cart_ids as $cart) {
                    $res4 = $res4 && $storeOrderCartInfoServices->update(['oid' => $id, 'cart_id' => $cart['cart_id']], ['refund_num' => (($cartInfo[$cart['cart_id']]['refund_num'] ?? 0) + $cart['cart_num'])]);
                }
            } else {
                //修改原订单状态
                $res2 = false !== $orderServices->update(['id' => $order['id']], ['refund_status' => 1]);
                foreach ($cartInfos as $cart) {
                    $res4 = $res4 && $storeOrderCartInfoServices->update(['oid' => $id, 'cart_id' => $cart['cart_id']], ['refund_num' => $cart['cart_num']]);
                }
            }
            return $res1 && $res2 && $res3 && $res4;
        });
        $storeOrderCartInfoServices->clearOrderCartInfo($order['id']);
        //申请退款事件
        event('order.orderRefundCreateAfter', [$order]);
        //提醒推送
        event('notice.notice', [['order' => $order], 'send_order_apply_refund']);
        //推送订单
        event('out.outPush', ['refund_create_push', ['order_id' => (int)$order['id']]]);
        try {
            ChannelService::instance()->send('NEW_REFUND_ORDER', ['order_id' => $order['order_id']]);
        } catch (\Exception $e) {
        }
        return $res;
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
                $SumPrice = bcadd($SumPrice, bcmul($cart['cart_num'] ?? 1, $cart[$key] ?? 0, 2), 2);
            } else {
                $SumPrice = bcadd($SumPrice, $cart[$key] ?? 0, 2);
            }
        }
        return $SumPrice;
    }

    /**
     * 退款订单列表
     * @param $where
     * @return array
     */
    public function refundList($where)
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getList($where, $page, $limit);
        $count = $this->dao->count($where);
        if ($list) {
            foreach ($list as &$item) {
                $item['paid'] = 1;
                $item['add_time'] = isset($item['add_time']) ? date('Y-m-d H:i', (int)$item['add_time']) : '';
                $item['cartInfo'] = $item['cart_info'];
                if (in_array($item['refund_type'], [1, 2, 4, 5])) {
                    $item['refund_status'] = 1;
                } elseif ($item['refund_type'] == 6) {
                    $item['refund_status'] = 2;
                } elseif ($item['refund_type'] == 3) {
                    $item['refund_status'] = 3;
                }
                foreach ($item['cart_info'] as $items) {
                    $item['_info'][]['cart_info'] = $items;
                }
                $item['total_num'] = $item['refund_num'];
                $item['pay_price'] = $item['refund_price'];
                $item['pay_postage'] = floatval($this->getOrderSumPrice($item['cart_info'], 'postage_price', false));

                if (in_array($item['refund_type'], [1, 2, 4, 5])) {
                    $_type = -1;
                    $_title = '申请退款中';
                } elseif ($item['refund_type'] == 3) {
                    $_type = -3;
                    $_title = '拒绝退款';
                } else {
                    $_type = -2;
                    $_title = '已退款';
                }
                $item['_status'] = [
                    '_type' => $_type,
                    '_title' => $_title,
                ];
            }
        }
        $data['list'] = $list;
        $data['count'] = $count;
        $del_where = ['is_cancel' => 0];
        $data['num'] = [
            0 => ['name' => '全部', 'num' => $this->dao->count($del_where)],
            1 => ['name' => '仅退款', 'num' => $this->dao->count($del_where + ['refund_type' => 1])],
            2 => ['name' => '退货退款', 'num' => $this->dao->count($del_where + ['refund_type' => 2])],
            3 => ['name' => '拒绝退款', 'num' => $this->dao->count($del_where + ['refund_type' => 3])],
            4 => ['name' => '商品待退货', 'num' => $this->dao->count($del_where + ['refund_type' => 4])],
            5 => ['name' => '退货待收货', 'num' => $this->dao->count($del_where + ['refund_type' => 5])],
            6 => ['name' => '已退款', 'num' => $this->dao->count($del_where + ['refund_type' => 6])]
        ];
        return $data;
    }

    /**
     * 退款订单详情
     * @param $uni
     * @return mixed
     */
    public function refundDetail($uni)
    {
        if (!strlen(trim($uni))) throw new ApiException(100100);
        $order = $this->dao->get(['order_id' => $uni], ['*']);
        if (!$order) throw new ApiException(410173);
        $order = $order->toArray();

        /** @var StoreOrderServices $orderServices */
        $orderServices = app()->make(StoreOrderServices::class);
        $orderInfo = $orderServices->get($order['store_order_id']);

        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $userInfo = $userServices->get($order['uid']);

        $order['mapKey'] = sys_config('tengxun_map_key');
        $order['yue_pay_status'] = (int)sys_config('balance_func_status') && (int)sys_config('yue_pay_status') == 1 ? (int)1 : (int)2;//余额支付 1 开启 2 关闭
        $order['pay_weixin_open'] = (int)sys_config('pay_weixin_open') ?? 0;//微信支付 1 开启 0 关闭
        $order['ali_pay_status'] = sys_config('ali_pay_status') ? true : false;//支付包支付 1 开启 0 关闭
        $orderData = $order;
        $orderData['store_order_sn'] = $orderInfo['order_id'];
        $orderData['cartInfo'] = $orderData['cart_info'];
        $orderData['_pay_time'] = date('Y-m-d H:i:s', $orderInfo['pay_time']);
        //核算优惠金额
        $vipTruePrice = 0;
        $total_price = 0;
        $pay_postage = '0';
        foreach ($orderData['cartInfo'] ?? [] as $key => &$cart) {
            if (!isset($cart['sum_true_price'])) $cart['sum_true_price'] = bcmul((string)$cart['truePrice'], (string)$cart['cart_num'], 2);
            $cart['vip_sum_truePrice'] = bcmul($cart['vip_truePrice'], $cart['cart_num'] ? $cart['cart_num'] : 1, 2);
            $vipTruePrice = bcadd((string)$vipTruePrice, (string)$cart['vip_sum_truePrice'], 2);
            if (isset($order['split']) && $order['split']) {
                $orderData['cartInfo'][$key]['cart_num'] = $cart['surplus_num'];
                if (!$cart['surplus_num']) unset($orderData['cartInfo'][$key]);
            }
            $total_price = bcadd($total_price, $cart['sum_true_price'], 2);
            $pay_postage = bcadd($cart['postage_price'], $pay_postage, 2);
        }
        $orderData['use_integral'] = $this->getOrderSumPrice($orderData['cartInfo'], 'use_integral', false);
        $orderData['integral_price'] = $this->getOrderSumPrice($orderData['cartInfo'], 'integral_price', false);
        $orderData['coupon_price'] = $this->getOrderSumPrice($orderData['cartInfo'], 'coupon_price', false);
        $orderData['deduction_price'] = $this->getOrderSumPrice($orderData['cartInfo'], 'integral_price', false);

        $total_price = bcadd((string)$total_price, (string)bcadd((string)$orderData['deduction_price'], (string)$orderData['coupon_price'], 2), 2);
        $orderData['vip_true_price'] = $vipTruePrice;
        $orderData['postage_price'] = 0;
        $orderData['pay_postage'] = $this->getOrderSumPrice($orderData['cart_info'], 'origin_postage_price', false);
        $orderData['member_price'] = 0;
        $orderData['routine_contact_type'] = sys_config('routine_contact_type', 0);

        switch ($orderInfo['pay_type']) {
            case PayServices::WEIXIN_PAY:
                $pay_type_name = '微信支付';
                break;
            case PayServices::YUE_PAY:
                $pay_type_name = '余额支付';
                break;
            case PayServices::OFFLINE_PAY:
                $pay_type_name = '线下支付';
                break;
            case PayServices::ALIAPY_PAY:
                $pay_type_name = '支付宝支付';
                break;
            default:
                $pay_type_name = '其他支付';
                break;
        }
        $orderData['_add_time'] = date('Y-m-d H:i:s', $orderData['add_time']);
        $orderData['add_time_y'] = date('Y-m-d', $orderData['add_time']);
        $orderData['add_time_h'] = date('H:i:s', $orderData['add_time']);
        if (in_array($orderData['refund_type'], [1, 2, 4, 5])) {
            $_type = -1;
            $_msg = '商家审核中,请耐心等待';
            $_title = '申请退款中';
        } elseif ($orderData['refund_type'] == 3) {
            $_type = -3;
            $_title = '拒绝退款';
            $_msg = '商家拒绝退款，请联系商家';
        } else {
            $_type = -2;
            $_title = '已退款';
            $_msg = '已为您退款,感谢您的支持';
        }
        $refund_name = sys_config('refund_name', '');
        $refund_phone = sys_config('refund_phone', '');
        $refund_address = sys_config('refund_address', '');
        $orderData['_status'] = [
            '_type' => $_type,
            '_title' => $_title,
            '_msg' => $_msg ?? '',
            '_payType' => $pay_type_name,
            'refund_name' => $refund_name,
            'refund_phone' => $refund_phone,
            'refund_address' => $refund_address,
        ];
        $orderData['real_name'] = $orderInfo['real_name'];
        $orderData['user_phone'] = $orderInfo['user_phone'];
        $orderData['user_address'] = $orderInfo['user_address'];
        $orderData['nickname'] = $userInfo['nickname'] ?? '';
        $orderData['total_num'] = $orderData['refund_num'];
        $orderData['pay_price'] = $orderData['refund_price'];
        $orderData['refund_status'] = in_array($orderData['refund_type'], [1, 2, 4, 5]) ? 1 : 2;
        $orderData['total_price'] = $total_price;
        $orderData['paid'] = 1;
        $orderData['mark'] = $orderData['refund_explain'];
        $orderData['express_list'] = $orderData['refund_type'] == 4 ? app()->make(ExpressServices::class)->expressList(['is_show' => 1]) : [];
        $orderData['custom_form'] = [];
        $orderData['help_info'] = [
            'pay_uid' => $orderInfo['pay_uid'],
            'pay_nickname' => '',
            'pay_avatar' => '',
            'help_status' => 0
        ];
        if ($orderInfo['uid'] != $orderInfo['pay_uid']) {
            /** @var UserServices $userServices */
            $userServices = app()->make(UserServices::class);
            $payUser = $userServices->get($orderInfo['pay_uid']);
            $orderData['help_info'] = [
                'pay_uid' => $orderInfo['pay_uid'],
                'pay_nickname' => $payUser['nickname'],
                'pay_avatar' => $payUser['avatar'],
                'help_status' => 1
            ];
        }
        $orderData['pay_postage'] = $pay_postage;
        return $orderData;
    }

    /**
     * 取消申请、后台拒绝处理cart_info refund_num数据
     * @param int $id
     * @param int $oid
     * @param array $orderRefundInfo
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function cancelOrderRefundCartInfo(int $id, int $oid, $orderRefundInfo = [])
    {
        if (!$orderRefundInfo) {
            $orderRefundInfo = $this->dao->get(['id' => $id, 'is_cancel' => 0]);
        }
        if (!$orderRefundInfo) {
            throw new ApiException(410173);
        }
        $cart_ids = array_column($orderRefundInfo['cart_info'], 'id');
        /** @var StoreOrderCartInfoServices $storeOrderCartInfoServices */
        $storeOrderCartInfoServices = app()->make(StoreOrderCartInfoServices::class);
        $cartInfos = $storeOrderCartInfoServices->getColumn([['oid', '=', $oid], ['cart_id', 'in', $cart_ids]], 'cart_id,refund_num', 'cart_id');
        foreach ($orderRefundInfo['cart_info'] as $cart) {
            $cart_refund_num = $cartInfos[$cart['id']]['refund_num'] ?? 0;
            if ($cart['cart_num'] >= $cart_refund_num) {
                $refund_num = 0;
            } else {
                $refund_num = bcsub((string)$cart_refund_num, (string)$cart['cart_num'], 0);
            }
            $storeOrderCartInfoServices->update(['oid' => $oid, 'cart_id' => $cart['id']], ['refund_num' => $refund_num]);
        }
        $storeOrderCartInfoServices->clearOrderCartInfo($oid);

        //写入订单记录表
        /** @var StoreOrderStatusServices $statusService */
        $statusService = app()->make(StoreOrderStatusServices::class);
        $statusService->save([
            'oid' => $oid,
            'change_type' => 'cancel_refund_order',
            'change_message' => '取消退款',
            'change_time' => time()
        ]);

        //售后订单取消后置事件
        event('order.orderRefundCancelAfter', [$orderRefundInfo]);
        // 推送订单
        event('out.outPush', ['refund_cancel_push', ['order_id' => (int)$orderRefundInfo['id']]]);
        return true;
    }

    /**
     * 修改备注
     * @param int $id
     * @param string $remark
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function updateRemark(int $id, string $remark)
    {
        if (!$id) {
            throw new AdminException(100100);
        }
        if (!$remark) {
            throw new AdminException(410177);
        }

        if (!$order = $this->dao->get($id)) {
            throw new AdminException(410173);
        }
        $order->remark = $remark;
        if (!$order->save()) {
            throw new AdminException(100025);
        }
        return true;
    }

    /**
     * 拒绝退款
     * @param int $id
     * @param string $refund_reason
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function refuse(int $id, string $refund_reason)
    {
        if (!$refund_reason) {
            throw new AdminException(400499);
        }

        if (!$id || !($orderRefundInfo = $this->dao->get($id))) {
            throw new AdminException(400118);
        }

        $refundData = [
            'refuse_reason' => $refund_reason,
            'refund_type' => 3,
            'refunded_time' => time()
        ];
        //拒绝退款处理
        $this->refuseRefund($id, $refundData, $orderRefundInfo);
    }
}
