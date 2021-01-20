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
use app\services\BaseServices;
use app\services\message\sms\SmsSendServices;
use app\services\user\UserBillServices;
use app\services\user\UserServices;
use app\services\wechat\WechatUserServices;
use crmeb\jobs\RoutineTemplateJob;
use crmeb\jobs\SmsAdminJob;
use crmeb\jobs\WechatTemplateJob as TemplateJob;
use crmeb\utils\Queue;
use crmeb\utils\Str;
use think\exception\ValidateException;
use think\facade\Log;

/**
 * 订单收货
 * Class StoreOrderTakeServices
 * @package app\services\order
 * @method get(int $id, ?array $field = []) 获取一条
 */
class StoreOrderTakeServices extends BaseServices
{
    /**
     * 构造方法
     * StoreOrderTakeServices constructor.
     * @param StoreOrderDao $dao
     */
    public function __construct(StoreOrderDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 用户订单收货
     * @param $uni
     * @param $uid
     * @return bool
     */
    public function takeOrder(string $uni, int $uid)
    {
        $order = $this->dao->getUserOrderDetail($uni, $uid);
        if (!$order) {
            throw new ValidateException('订单不存在!');
        }
        /** @var StoreOrderServices $orderServices */
        $orderServices = app()->make(StoreOrderServices::class);
        $order = $orderServices->tidyOrder($order);
        if ($order['_status']['_type'] != 2) {
            throw new ValidateException('订单状态错误!');
        }
        $order->status = 2;
        /** @var StoreOrderStatusServices $statusService */
        $statusService = app()->make(StoreOrderStatusServices::class);
        $res = $order->save() && $statusService->save([
                'oid' => $order['id'],
                'change_type' => 'user_take_delivery',
                'change_message' => '用户已收货',
                'change_time' => time()
            ]);
        $res = $res && $this->storeProductOrderUserTakeDelivery($order);
        if (!$res) {
            throw new ValidateException('收货失败');
        }
        return $order;
    }

    /**
     * 订单确认收货
     * @param $order
     * @return bool
     */
    public function storeProductOrderUserTakeDelivery($order, bool $isTran = true)
    {
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $userInfo = $userServices->get((int)$order['uid']);
        //获取购物车内的商品标题
        /** @var StoreOrderCartInfoServices $orderInfoServices */
        $orderInfoServices = app()->make(StoreOrderCartInfoServices::class);
        $storeName = $orderInfoServices->getCarIdByProductTitle($order['cart_id']);
        $storeTitle = Str::substrUTf8($storeName, 20);

        $res = $this->transaction(function () use ($order, $userInfo, $storeTitle) {
            //赠送积分
            $res1 = $this->gainUserIntegral($order, $userInfo, $storeTitle);
            if (!($res1)) {
                throw new ValidateException('收货失败!');
            }
            return true;
        }, $isTran);

        if ($res) {
            try {
                //修改收货状态
                /** @var UserBillServices $userBillServices */
                $userBillServices = app()->make(UserBillServices::class);
                $userBillServices->takeUpdate((int)$order['uid'], (int)$order['id']);
                //增加收货订单状态
                /** @var StoreOrderStatusServices $statusService */
                $statusService = app()->make(StoreOrderStatusServices::class);
                $statusService->save([
                    'oid' => $order['id'],
                    'change_type' => 'take_delivery',
                    'change_message' => '已收货',
                    'change_time' => time()
                ]);
                //发送模板消息
                $this->orderTakeSendAfter($order, $userInfo, $storeTitle);
                //发送短信
                $this->smsSend($order, $storeTitle);
            } catch (\Throwable $e) {
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * 赠送积分
     * @param $order
     * @param $userInfo
     * @param $storeTitle
     * @return bool
     */
    public function gainUserIntegral($order, $userInfo, $storeTitle)
    {
        $res2 = true;
        if (!$userInfo) {
            return true;
        }
        /** @var UserBillServices $userBillServices */
        $userBillServices = app()->make(UserBillServices::class);
        if ($order['gain_integral'] > 0) {
            $res2 = false != $userBillServices->income('pay_give_integral', $order['uid'], $order['gain_integral'], $userInfo['integral'], $order['id']);
        }
        $order_integral = 0;
        $res3 = true;
        $order_give_integral = sys_config('order_give_integral');
        if ($order['pay_price'] && $order_give_integral) {
            $order_integral = bcmul($order_give_integral, (string)$order['pay_price'], 0);
            $res3 = false != $userBillServices->income('order_give_integral', $order['uid'], $order_integral, $userInfo['integral'], $order['id']);
        }
        $give_integral = $order_integral + $order['gain_integral'];
        if ($give_integral > 0) {
            $integral = $userInfo['integral'] + $give_integral;
            $userInfo->integral = $integral;
            $res1 = false != $userInfo->save();
            $res = $res1 && $res2 && $res3;
            $this->sendUserIntegral($order, $give_integral, $integral, $storeTitle);
            return $res;
        }
        return true;
    }


    /**
     * 赠送积分发送模板消息
     * @param $order
     * @param $give_integral
     * @param $integral
     */
    public function sendUserIntegral($order, $give_integral, $integral, $storeTitle)
    {
        /** @var WechatUserServices $wechatServices */
        $wechatServices = app()->make(WechatUserServices::class);
        //小程序
        $openid = $wechatServices->uidToOpenid($order['uid'], 'routine');
        Queue::instance()->do('sendUserIntegral')->job(RoutineTemplateJob::class)->data($openid, $order, $storeTitle, $give_integral, $integral)->push();
    }

    /**
     * 确认收货发送模板消息
     * @param $order
     * @param $userInfo
     * @param $storeTitle
     * @return mixed
     */
    public function orderTakeSendAfter($order, $userInfo, $storeTitle)
    {
        /** @var WechatUserServices $wechatServices */
        $wechatServices = app()->make(WechatUserServices::class);
        if ($order['is_channel'] == 1) {
            //小程序
            $openid = $wechatServices->uidToOpenid($userInfo['uid'], 'routine');
            return Queue::instance()->do('sendOrderTakeOver')->job(RoutineTemplateJob::class)->data($openid, $order, $storeTitle)->push();
        } else {
            $openid = $wechatServices->uidToOpenid($userInfo['uid'], 'wechat');
            return Queue::instance()->do('sendOrderTakeSuccess')->job(TemplateJob::class)->data($openid, $order, $storeTitle)->push();
        }
    }

    /**
     * 发送短信
     * @param $order
     * @param $storeTitle
     */
    public function smsSend($order, $storeTitle)
    {
        /** @var SmsSendServices $smsServices */
        $smsServices = app()->make(SmsSendServices::class);
        $switch = sys_config('confirm_take_over_switch') ? true : false;
        //模板变量
        $store_name = $storeTitle;
        $order_id = $order['order_id'];
        $smsServices->send($switch, $order['user_phone'], compact('store_name', 'order_id'), 'TAKE_DELIVERY_CODE');
    }

    /**
     * 自动收货
     * @return bool
     */
    public function autoTakeOrder()
    {
        //7天前时间戳
        $systemDeliveryTime = (int)sys_config('system_delivery_time', 0);
        //0为取消自动收货功能
        if ($systemDeliveryTime == 0) {
            return true;
        }
        $sevenDay = strtotime(date('Y-m-d H:i:s', strtotime('-' . $systemDeliveryTime . ' day')));
        /** @var StoreOrderStoreOrderStatusServices $service */
        $service = app()->make(StoreOrderStoreOrderStatusServices::class);
        $orderList = $service->getTakeOrderIds([
            'change_time' => $sevenDay,
            'is_del' => 0,
            'paid' => 1,
            'status' => 1,
            'change_type' => ['delivery_goods', 'delivery_fictitious', 'delivery']
        ]);
        foreach ($orderList as $order) {
            if ($order['status'] == 2) {
                continue;
            }
            if ($order['paid'] == 1 && $order['status'] == 1) {
                $data['status'] = 2;
            } else if ($order['pay_type'] == 'offline') {
                $data['status'] = 2;
            } else {
                continue;
            }
            try {
                $this->transaction(function () use ($order, $data) {
                    /** @var StoreOrderStatusServices $statusService */
                    $statusService = app()->make(StoreOrderStatusServices::class);
                    $res = $this->dao->update($order['id'], $data) && $statusService->save([
                            'oid' => $order['id'],
                            'change_type' => 'take_delivery',
                            'change_message' => '已收货[自动收货]',
                            'change_time' => time()
                        ]);
                    $res = $res && $this->storeProductOrderUserTakeDelivery($order, false);
                    if (!$res) {
                        throw new ValidateException('订单号' . $order['order_id'] . '自动收货失败');
                    }
                });
            } catch (\Throwable $e) {
                Log::error('自动收货失败,失败原因：' . $e->getMessage());
            }

        }
    }
}
