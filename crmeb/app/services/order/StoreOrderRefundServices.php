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

use app\dao\order\StoreOrderDao;
use app\jobs\notice\SmsJob;
use app\services\activity\StoreAdvanceServices;
use app\services\activity\StoreBargainServices;
use app\services\activity\StoreCombinationServices;
use app\services\activity\StorePinkServices;
use app\services\activity\StoreSeckillServices;
use app\services\BaseServices;
use app\services\coupon\StoreCouponIssueUserServices;
use app\services\coupon\StoreCouponUserServices;
use app\services\message\notice\RoutineTemplateListService;
use app\services\message\notice\WechatTemplateListService;
use app\services\message\service\StoreServiceServices;
use app\services\pay\PayServices;
use app\services\product\product\StoreProductServices;
use app\services\user\UserBillServices;
use app\services\user\UserServices;
use app\services\wechat\WechatUserServices;
use crmeb\services\AliPayService;
use crmeb\services\CacheService;
use crmeb\services\FormBuilder as Form;
use crmeb\services\MiniProgramService;
use crmeb\services\WechatService;
use crmeb\services\workerman\ChannelService;
use crmeb\utils\Str;
use think\exception\ValidateException;


/**
 * 订单退款
 * Class StoreOrderRefundServices
 * @package app\services\order
 */
class StoreOrderRefundServices extends BaseServices
{
    /**
     * 构造方法
     * StoreOrderRefundServices constructor.
     * @param StoreOrderDao $dao
     */
    public function __construct(StoreOrderDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 订单退款表单
     * @param int $id
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function refundOrderForm(int $id)
    {
        $order = $this->dao->get($id);
        if (!$order) {
            throw new ValidateException('未查到订单');
        }
        if (!$order['paid']) {
            throw new ValidateException('未支付无法退款');
        }
        if ($order['pay_price'] > 0 && in_array($order['refund_status'], [0, 1])) {
            if ($order['pay_price'] <= $order['refund_price']) {
                throw new ValidateException('订单已退款');
            }
        }
        $f[] = Form::input('order_id', '退款单号', $order->getData('order_id'))->disabled(true);
        $f[] = Form::number('refund_price', '退款金额', (float)bcsub((string)$order->getData('pay_price'), (string)$order->getData('refund_price'), 2))->min(0)->required('请输入退款金额');
        return create_form('退款处理', $f, $this->url('/order/refund/' . $id), 'PUT');
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
                throw new ValidateException('回退积分和优惠卷失败');
            }

            //虚拟商品优惠券退款处理
            if ($order['virtual_type'] == 2) {
                /** @var StoreCouponUserServices $couponUser */
                $couponUser = app()->make(StoreCouponUserServices::class);
                $res = $couponUser->delUserCoupon(['cid' => $order['virtual_info'], 'uid' => $order['uid'], 'status' => 0]);
                if (!$res) throw new ValidateException('购买的优惠券已使用或者已过期');
                /** @var StoreCouponIssueUserServices $couponIssueUser */
                $couponIssueUser = app()->make(StoreCouponIssueUserServices::class);
                $couponIssueUser->delIssueUserCoupon(['issue_coupon_id' => $order['virtual_info'], 'uid' => $order['uid']]);
            }

            //退拼团
            if ($type == 1) {
                /** @var StorePinkServices $pinkServices */
                $pinkServices = app()->make(StorePinkServices::class);
                if (!$pinkServices->setRefundPink($order)) {
                    throw new ValidateException('拼团修改失败!');
                }
            }
            //退佣金
            /** @var UserBillServices $userBillServices */
            $userBillServices = app()->make(UserBillServices::class);
            if (!$userBillServices->orderRefundBrokerageBack($order['id'], $order['order_id'])) {
                throw new ValidateException('回退佣金失败');
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
                    $refundOrder = $this->dao->get((int)$order['pid']);
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
                        if ($refundOrder['is_channel'] == 1) {
                            //小程序退款
                            MiniProgramService::payOrderRefund($no, $refundData);//小程序
                        } else {
                            //微信公众号退款
                            WechatService::payOrderRefund($no, $refundData);//公众号
                        }
                        break;
                    case PayServices::YUE_PAY:
                        //余额退款
                        if (!$this->yueRefund($refundOrder, $refundData)) {
                            throw new ValidateException('余额退款失败');
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
            /** @var UserServices $userServices */
            $userServices = app()->make(UserServices::class);
            $usermoney = $userServices->value(['uid' => $order['uid']], 'now_money');
            $userBillServices->income('pay_product_refund', $order['uid'], ['number' => $refundData['refund_price'], 'payType' => PayServices::PAY_TYPE[$order['pay_type']] ?? '未知'], bcadd((string)$usermoney, (string)$refundData['refund_price'], 2), $order['id']);
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
        $res = $userServices->bcInc($order['uid'], 'now_money', $refundData['refund_price'], 'uid');
        return $res ? true : false;
    }

    /**
     * 回退积分和优惠卷
     * @param $order
     * @return bool
     */
    public function integralAndCouponBack($order)
    {
        $res = true;
        //回退优惠卷
        if ($order['coupon_id'] && $order['coupon_price']) {
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
        if ((int)$order['refund_status'] != 2 && $order['back_integral'] >= $order['use_integral']) {
            return $order;
        }

        $res1 = $res2 = $res3 = $res4 = true;
        //订单赠送积分
        /** @var UserBillServices $userBillServices */
        $userBillServices = app()->make(UserBillServices::class);
        $give_integral = $userBillServices->sum([
            'category' => 'integral',
            'type' => 'sign',
            'link_id' => $order['id'],
            'uid' => $order['uid']
        ], 'number');
        if ($give_integral) {
            //判断订单是否已经回退积分
            $count = $userBillServices->count(['category' => 'integral', 'type' => 'deduction', 'link_id' => $order['id']]);
            if (!$count) {
                $res1 = $userServices->bcDec($order['uid'], 'integral', $give_integral);
                //记录赠送积分收回
                $res2 = $userBillServices->income('integral_refund', $order['uid'], $give_integral, $integral, $order['id']);
            }
        }
        //返还下单使用积分
        $use_integral = $order['use_integral'];
        if ($use_integral > 0) {
            $res3 = $userServices->bcInc($order['uid'], 'integral', $use_integral);
            //记录下单使用积分还回
            $res4 = $userBillServices->income('pay_product_integral_back', $order['uid'], $use_integral, $integral, $order['id']);
        }
        if (!($res1 && $res2 && $res3 && $res4)) {
            throw new ValidateException('回退积分增加失败');
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
            } else if ($advance_id) {
                $type = 6;
                $res5 = $res5 && $advanceServices->incAdvanceStock($cart_num, (int)$advance_id, $unique);
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
            throw new ValidateException('Data does not exist!');
        }
        $f[] = Form::input('order_id', '不退款单号', $order->getData('order_id'))->disabled(true);
        $f[] = Form::input('refund_reason', '不退款原因')->type('textarea')->required('请填写不退款原因');
        return create_form('不退款原因', $f, $this->url('order/no_refund/' . $id), 'PUT');
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
            throw new ValidateException('订单不存在');
        if ($orderInfo->use_integral < 0 || $orderInfo->use_integral == $orderInfo->back_integral)
            throw new ValidateException('积分已退或者积分为零无法再退');
        if (!$orderInfo->paid)
            throw new ValidateException('未支付无法退积分');
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
            $res2 = $userBillServices->income('pay_product_integral_back', $orderInfo['uid'], $back_integral, $integral + $back_integral, $orderInfo['id']);
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
                throw new ValidateException('订单退积分失败');
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
            throw new ValidateException('支付订单不存在!');
        }
        if ($order['refund_status'] == 2) {
            throw new ValidateException('订单已退款!');
        }
        if ($order['refund_status'] == 1) {
            throw new ValidateException('正在申请退款中!');
        }
        if ($order['total_num'] < $refund_num) {
            throw new ValidateException('退款件数大于订单件数!');
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
            } elseif (in_array($order['pid'], [0, -1]) && $this->dao->count(['pid' => $order_id])) {
                /** @var StoreOrderCartInfoServices $storeOrderCartInfoServices */
                $storeOrderCartInfoServices = app()->make(StoreOrderCartInfoServices::class);
                $cart_info = $storeOrderCartInfoServices->getSplitCartList($order_id, 'cart_info');
                if (!$cart_info) {
                    throw new ValidateException('该订单已全部拆分发货，请去自订单申请');
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
            $res2 = false !== $this->dao->update(['id' => $order['id']], $data);
            $res = $res1 && $res2;
            if (!$res)
                throw new ValidateException('申请退款失败!');
            //子订单申请退款
            if ($order['pid'] > 0) {
                $p_order = $this->dao->get((int)$order['pid']);
                $split_order = $this->dao->count(['pid' => $order['pid'], 'refund_status' => 0]);
                if ($split_order || (!$split_order && $p_order['status'] == 4) || $cart_id) {
                    $this->dao->update(['id' => $order['pid']], ['refund_status' => 3, 'refund_reason_time' => time()]);
                } else {
                    $this->dao->update(['id' => $order['pid']], ['refund_status' => 4, 'refund_reason_time' => time()]);
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
    public function editRefundExpress($id, $express_id)
    {
        $this->transaction(function () use ($id, $express_id) {
            /** @var StoreOrderStatusServices $statusService */
            $statusService = app()->make(StoreOrderStatusServices::class);
            /** @var StoreOrderServices $orderService */
            $orderService = app()->make(StoreOrderServices::class);
            $res1 = false !== $statusService->save([
                    'oid' => $id,
                    'change_type' => 'refund_express',
                    'change_message' => '用户已退货，订单号：' . $express_id,
                    'change_time' => time()
                ]);
            $res2 = false !== $orderService->update(['id' => $id], ['refund_type' => 5, 'refund_express' => $express_id]);
            $res = $res1 && $res2;
            if (!$res)
                throw new ValidateException('提交退货快递单号失败!');
        });
        return true;
    }
}
