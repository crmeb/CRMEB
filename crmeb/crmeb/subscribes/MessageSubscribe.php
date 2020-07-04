<?php

namespace crmeb\subscribes;

use app\admin\model\wechat\WechatMessage;
use app\models\store\StoreOrder;
use app\models\store\StoreProduct;
use app\models\store\StoreProductReply;
use app\models\store\StoreService;
use app\models\user\User;
use app\models\user\UserExtract;
use app\models\user\WechatUser;
use crmeb\repositories\CustomerRepository;
use crmeb\repositories\ShortLetterRepositories;
use crmeb\services\SystemConfigService;
use crmeb\services\workerman\ChannelService;
use think\facade\Log;

/**
 * 用户消息事件
 * Class MessageSubscribe
 * @package crmeb\subscribes
 */
class MessageSubscribe
{
    public function handle()
    {

    }

    /** 后台订单下单，评论，支付成功，后台消息提醒
     * @param $event
     */
    public function onAdminNewPush($event)
    {
        try {
            $data['ordernum'] = StoreOrder::where('paid', 1)->where('status', 0)
                ->where('shipping_type', 1)->where('refund_status', 0)
                ->where('is_del', 0)->count();
            $store_stock = sys_config('store_stock');
            if ($store_stock < 0) $store_stock = 2;
            $data['inventory'] = StoreProduct::where('stock', '<=', $store_stock)->where('is_show', 1)->where('is_del', 0)->count();//库存
            $data['commentnum'] = StoreProductReply::where('is_reply', 0)->count();
            $data['reflectnum'] = UserExtract::where('status', 0)->count();//提现
            $data['msgcount'] = intval($data['ordernum']) + intval($data['inventory']) + intval($data['commentnum']) + intval($data['reflectnum']);
            ChannelService::instance()->send('ADMIN_NEW_PUSH', $data);
        } catch (\Exception $e) {
        }
    }

    /**
     * 微信消息前置操作
     * @param $event
     */
    public function onWechatMessageBefore($event)
    {
        list($message) = $event;
        WechatUser::saveUser($message->FromUserName);

        $event = isset($message->Event) ?
            $message->MsgType . (
            $message->Event == 'subscribe' && isset($message->EventKey) ? '_scan' : ''
            ) . '_' . $message->Event : $message->MsgType;
        WechatMessage::setMessage(json_encode($message), $message->FromUserName, strtolower($event));
    }

    /**
     * 用户取消关注公众号前置操作
     * @param $event
     */
    public function onWechatEventUnsubscribeBefore($event)
    {
        list($message) = $event;
        WechatUser::unSubscribe($message->FromUserName);
    }

    /**
     * 下发送短信事件
     * @param array $event
     */
    public function onShortMssageSend($event)
    {
        //$actions 可为数组
        list($order_id, $actions) = $event;
        try {
            if (is_array($actions)) {
                foreach ($actions as $action) {
                    $actionName = 'onMssageSend' . $action;
                    if (method_exists($this, $actionName)) $this->$actionName($order_id);
                }
            } else {
                $actionName = 'onMssageSend' . $actions;
                if (method_exists($this, $actionName)) $this->$actionName($order_id);
            }
        } catch (\Exception $e) {
            Log::error('短信下发事件发生系统错误,错误原因：' . $e->getMessage());
        }
    }


    /**
     *  支付成功短信提醒
     * @param string $order_id
     */
    public function onMssageSendPaySuccess($order_id)
    {
        $storeInfo = StoreOrder::where(['order_id' => $order_id, 'paid' => 1, 'refund_status' => 0])->find();
        if (!$storeInfo) return;
        $switch = sys_config('lower_order_switch') ? true : false;
        //模板变量
        $pay_price = $storeInfo->pay_price;
        ShortLetterRepositories::send($switch, $storeInfo->user_phone, compact('order_id', 'pay_price'), 'PAY_SUCCESS_CODE', '用户支付成功发送短信失败，订单号为：' . $order_id);
    }

    /**
     * 发货短信提醒
     * @param string $order_id
     */
    public function onMssageSendDeliver($order_id)
    {
        $storeInfo = StoreOrder::where(['order_id' => $order_id, 'paid' => 1, 'refund_status' => 0, 'status' => 1])->find();
        if (!$storeInfo) return;
        $switch = sys_config('deliver_goods_switch') ? true : false;
        //模板变量
        $nickname = User::where('uid', $storeInfo->uid)->value('nickname');
        $store_name = StoreOrder::getProductTitle($storeInfo->cart_id);
        ShortLetterRepositories::send($switch, $storeInfo->user_phone, compact('order_id', 'store_name', 'nickname'), 'DELIVER_GOODS_CODE', '用户发货发送短信失败，订单号为：' . $order_id);
    }

    /**
     * 确认收货短信提醒
     * @param string $order_id
     */
    public function onMssageSendReceiving($order_id)
    {
        $storeInfo = StoreOrder::where(['order_id' => $order_id, 'paid' => 1, 'refund_status' => 0, 'status' => 2])->find();
        if (!$storeInfo) return;
        $switch = sys_config('confirm_take_over_switch') ? true : false;
        //模板变量
        $store_name = StoreOrder::getProductTitle($storeInfo->cart_id);
        ShortLetterRepositories::send($switch, $storeInfo->user_phone, compact('store_name', 'order_id'), 'TAKE_DELIVERY_CODE', '用户确认收货发送短信失败，订单号为：' . $order_id);
    }

    /**
     * 用户下单管理员短信提醒
     * @param string $order_id
     */
    public function onMssageSendAdminPlaceAnOrder($order_id)
    {
        $storeInfo = StoreOrder::where(['order_id' => $order_id, 'paid' => 0, 'refund_status' => 0, 'status' => 0])->find();
        if (!$storeInfo) return;
        $switch = sys_config('admin_lower_order_switch') ? true : false;
        $switch && CustomerRepository::getAdminNoticeAuth(function ($userInfo) use ($storeInfo) {
            //模板变量
            $admin_name = $userInfo->nickname;
            $order_id = $storeInfo->order_id;
            ShortLetterRepositories::send(true, $userInfo->phone, compact('admin_name', 'order_id'), 'ADMIN_PLACE_ORDER_CODE', '用户下单成功管理员发送短信通知失败，订单号为：' . $storeInfo->order_id);
        });
    }

    /**
     * 支付成功管理员短信提醒
     * @param string $order_id
     */
    public function onMssageSendAdminPaySuccess($order_id)
    {
        $storeInfo = StoreOrder::where(['order_id' => $order_id, 'paid' => 1, 'refund_status' => 0, 'status' => 0])->find();
        if (!$storeInfo) return;
        $switch = sys_config('admin_pay_success_switch') ? true : false;
        $switch && CustomerRepository::getAdminNoticeAuth(function ($userInfo) use ($storeInfo) {
            //模板变量
            $admin_name = $userInfo->nickname;
            $order_id = $storeInfo->order_id;
            ShortLetterRepositories::send(true, $userInfo->phone, compact('admin_name', 'order_id'), 'ADMIN_PAY_SUCCESS_CODE', '用户支付成功管理员发送短信通知失败，订单号为：' . $storeInfo->order_id);
        });
    }

    /**
     * 用户确认收货管理员短信提醒
     * @param string $order_id
     */
    public function onMssageSendAdminConfirmTakeOver($order_id)
    {
        $storeInfo = StoreOrder::where(['order_id' => $order_id, 'paid' => 1, 'refund_status' => 0, 'status' => 2])->find();
        if (!$storeInfo) return;
        $switch = sys_config('admin_confirm_take_over_switch') ? true : false;
        $switch && CustomerRepository::getAdminNoticeAuth(function ($userInfo) use ($storeInfo) {
            //模板变量
            $admin_name = $userInfo->nickname;
            $order_id = $storeInfo->order_id;
            ShortLetterRepositories::send(true, $userInfo->phone, compact('admin_name', 'order_id'), 'ADMIN_TAKE_DELIVERY_CODE', '用户确认收货成功管理员发送短信通知失败，订单号为：' . $storeInfo->order_id);
        });
    }

    /**
     * 用户发起退款管理员短信提醒
     * @param string $order_id
     */
    public function onMssageSendAdminRefund($order_id)
    {
        $storeInfo = StoreOrder::where(['order_id' => $order_id, 'paid' => 1, 'refund_status' => 1])->find();
        if (!$storeInfo) return;
        $switch = sys_config('admin_refund_switch') ? true : false;
        $switch && CustomerRepository::getAdminNoticeAuth(function ($userInfo) use ($storeInfo) {
            //模板变量
            $admin_name = $userInfo->nickname;
            $order_id = $storeInfo->order_id;
            ShortLetterRepositories::send(true, $userInfo->phone, compact('admin_name', 'order_id'), 'ADMIN_RETURN_GOODS_CODE', '用户退款管理员发送短信通知失败，订单号为：' . $storeInfo->order_id);
        });
    }
}