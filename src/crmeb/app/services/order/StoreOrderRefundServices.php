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

use app\services\pay\PayServices;
use crmeb\utils\Str;
use crmeb\utils\Queue;
use app\services\BaseServices;
use app\dao\order\StoreOrderDao;
use crmeb\services\WechatService;
use crmeb\jobs\RoutineTemplateJob;
use app\services\user\UserServices;
use crmeb\services\MiniProgramService;
use think\exception\ValidateException;
use app\services\user\UserBillServices;
use crmeb\services\FormBuilder as Form;
use app\services\wechat\WechatUserServices;
use crmeb\services\workerman\ChannelService;
use app\services\coupon\StoreCouponUserServices;
use crmeb\jobs\WechatTemplateJob as TemplateJob;
use app\services\product\product\StoreProductServices;


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
        $f[] = Form::number('refund_price', '退款金额', (float)bcsub((string)$order->getData('pay_price'), (string)$order->getData('refund_price'), 2))->precision(2)->required('请输入退款金额');
//        $f[] = Form::radio('type', '状态', 1)->options([['label' => '直接退款', 'value' => 1], ['label' => '退款（无需退货，并且返回原状态）', 'value' => 2]]);
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
            /** @var UserBillServices $userBillServices */
            $userBillServices = app()->make(UserBillServices::class);
            //回退库存
            if ($type == 1) {
                /** @var StoreOrderStatusServices $services */
                $services = app()->make(StoreOrderStatusServices::class);
                if (!$services->count(['oid' => $order['id'], 'change_type' => 'refund_price'])) {
                    $this->regressionStock($order);
                }
            }

            //退金额
            if ($refundData['refund_price'] > 0) {
                switch ($order['pay_type']) {
                    case PayServices::WEIXIN_PAY:
                        $no = $order['order_id'];
                        if ($order['trade_no']) {
                            $no = $order['trade_no'];
                            $refundData['type'] = 'trade_no';
                        }
                        if ($order['is_channel'] == 1) {
                            //小程序退款
                            MiniProgramService::payOrderRefund($no, $refundData);//小程序
                        } else {
                            //微信公众号退款
                            WechatService::payOrderRefund($no, $refundData);//公众号
                        }
                        break;
                    case PayServices::YUE_PAY:
                        //余额退款
                        if (!$this->yueRefund($order, $refundData)) {
                            throw new ValidateException('余额退款失败');
                        }
                        break;
                }
            }
            /** @var UserServices $userServices */
            $userServices = app()->make(UserServices::class);
            $usermoney = $userServices->value(['uid' => $order['uid']], 'now_money');
            $userBillServices->income('pay_product_refund', $order['uid'], $refundData['refund_price'], bcadd((string)$usermoney, (string)$refundData['refund_price'], 2), $order['id']);
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

//        /** @var UserBillServices $userBillServices */
//        $userBillServices = app()->make(UserBillServices::class);
//
//        $usermoney = $userServices->value(['uid' => $order['uid']], 'now_money');
        $res = $userServices->bcInc($order['uid'], 'now_money', $refundData['refund_price'], 'uid');
        return $res ? true : false;
//        return $res && $userBillServices->income('pay_product_refund', $order['uid'], $refundData['refund_price'], bcadd((string)$usermoney, (string)$refundData['refund_price'], 2), $order['id']);
    }

    /**
     * 回退积分和优惠卷
     * @param $order
     * @return bool
     */
    public function integralAndCouponBack($order)
    {
        if (!$order['use_integral'] && !$order['back_integral']) {
            return true;
        }
        if ($order['back_integral'] && !(int)$order['use_integral']) {
            return true;
        }
        if ($order['back_integral'] >= $order['use_integral']) {
            return true;
        }
        $data['back_integral'] = bcsub($order['use_integral'], $order['back_integral'], 0);
        if (!$data['back_integral']) {
            return true;
        }

        //回退积分
        $order = $this->regressionIntegral($order);
        $res = true;
        //回退优惠卷
        if ($order['coupon_id']) {
            /** @var StoreCouponUserServices $coumonUserServices */
            $coumonUserServices = app()->make(StoreCouponUserServices::class);
            $res = $res && $coumonUserServices->recoverCoupon((int)$order['coupon_id']);
        }
        /** @var StoreOrderStatusServices $statusService */
        $statusService = app()->make(StoreOrderStatusServices::class);
        $statusService->save([
            'oid' => $order['id'],
            'change_type' => 'integral_back',
            'change_message' => '商品退积分：' . $data['back_integral'],
            'change_time' => time()
        ]);
        $order['use_integral'] = 0;
        $order['deduction_price'] = 0;
        $order['coupon_id'] = 0;
        $order['coupon_price'] = 0;
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
            'link_id' => $order['id']
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

        $res5 = true;
        /** @var StoreOrderCartInfoServices $cartServices */
        $cartServices = app()->make(StoreOrderCartInfoServices::class);
        /** @var StoreProductServices $services */
        $services = app()->make(StoreProductServices::class);
        $cartInfo = $cartServices->getCartInfoList(['cart_id' => $order['cart_id']], ['cart_info']);
        foreach ($cartInfo as $cart) {
            $cart['cart_info'] = is_array($cart['cart_info']) ? $cart['cart_info'] : json_decode($cart['cart_info'], true);
            //增库存减销量
            $unique = isset($cart['cart_info']['productInfo']['attrInfo']) ? $cart['cart_info']['productInfo']['attrInfo']['unique'] : '';
            $res5 = $res5 && $services->incProductStock((int)$cart['cart_info']['cart_num'], (int)$cart['cart_info']['productInfo']['id'], $unique);
        }
        return $res5;
    }

    /**
     * 发送退款模板消息
     * @param $data
     * @param $order
     */
    public function productOrderRefundYSendTemplate($data, $order)
    {
        /** @var WechatUserServices $wechatServices */
        $wechatServices = app()->make(WechatUserServices::class);
        if ($order['is_channel'] == 1) {
            /** @var StoreOrderCartInfoServices $orderInfoServices */
            $orderInfoServices = app()->make(StoreOrderCartInfoServices::class);
            $storeName = $orderInfoServices->getCarIdByProductTitle($order['cart_id']);
            $storeTitle = Str::substrUTf8($storeName, 20);
            $openid = $wechatServices->uidToOpenid($order['uid'], 'routine');
            Queue::instance()->do('sendOrderRefundSuccess')->job(RoutineTemplateJob::class)->data($openid, $order, $storeTitle)->push();
        } else {
            $openid = $wechatServices->uidToOpenid($order['uid'], 'wechat');
            Queue::instance()->do('sendOrderRefundSuccess')->job(TemplateJob::class)->data($openid, $data, $order)->push();
        }
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
        $this->productOrderRefundYSendTemplate($data, $order);
        /** @var StoreOrderStatusServices $statusService */
        $statusService = app()->make(StoreOrderStatusServices::class);
        $statusService->save([
            'oid' => $order['id'],
            'change_type' => 'refund_price',
            'change_message' => '退款给用户：' . $refund_price . '元',
            'change_time' => time()
        ]);
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
     * 不退款发送模板消息
     * @param $order
     * @param $data
     */
    public function OrderRefundNoSendTemplate($order)
    {
        /** @var WechatUserServices $wechatServices */
        $wechatServices = app()->make(WechatUserServices::class);
        if ($order->is_channel == 1) {
            /** @var StoreOrderCartInfoServices $orderInfoServices */
            $orderInfoServices = app()->make(StoreOrderCartInfoServices::class);
            $storeName = $orderInfoServices->getCarIdByProductTitle($order['cart_id']);
            $storeTitle = Str::substrUTf8($storeName, 20);
            $openid = $wechatServices->uidToOpenid($order['uid'], 'routine');
            Queue::instance()->do('sendOrderRefundFail')->job(RoutineTemplateJob::class)->data($openid, $order, $storeTitle)->push();
        } else {
            $openid = $wechatServices->uidToOpenid($order['uid'], 'wechat');
            Queue::instance()->do('sendOrderRefundNoStatus')->job(TemplateJob::class)->data($openid, $order)->push();
        }
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
    public function orderApplyRefund($order, string $refundReasonWap = '', string $refundReasonWapExplain = '', array $refundReasonWapImg = [])
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
//        if ($order['status'] == 1) {
//            throw new ValidateException('订单当前无法退款!');
//        }

        $order->refund_status = 1;
        $order->refund_reason_time = time();
        $order->refund_reason_wap = $refundReasonWap;
        $order->refund_reason_wap_explain = $refundReasonWapExplain;
        $order->refund_reason_wap_img = json_encode($refundReasonWapImg);

        $this->transaction(function () use ($order, $refundReasonWap) {
            /** @var StoreOrderStatusServices $statusService */
            $statusService = app()->make(StoreOrderStatusServices::class);
            $res1 = false !== $statusService->save([
                    'oid' => $order['id'],
                    'change_type' => 'apply_refund',
                    'change_message' => '用户申请退款，原因：' . $refundReasonWap,
                    'change_time' => time()
                ]);
            $res2 = false !== $order->save();
            $res = $res1 && $res2;
            if (!$res)
                throw new ValidateException('申请退款失败!');
        });

        try {
            ChannelService::instance()->send('NEW_REFUND_ORDER', ['order_id' => $order['order_id']]);
        } catch (\Exception $e) {
        }
        $this->sendAdminRefund($order);
        return true;

    }

    /**
     * 用户发起退款管理员短信提醒
     * 用户退款中模板消息
     * @param string $order_id
     */
    public function sendAdminRefund($order)
    {
        /** @var WechatUserServices $wechatServices */
        $wechatServices = app()->make(WechatUserServices::class);
        if ($order['is_channel'] == 1) {
            //小程序
            $openid = $wechatServices->uidToOpenid($order['uid'], 'routine');
            return Queue::instance()->do('sendOrderRefundStatus')->job(RoutineTemplateJob::class)->data($openid, $order)->push();
        } else {
            $openid = $wechatServices->uidToOpenid($order['uid'], 'wechat');
            return Queue::instance()->do('sendOrderApplyRefund')->job(TemplateJob::class)->data($openid, $order)->push();
        }
    }

}
