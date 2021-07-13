<?php


namespace app\listener\order;


use app\jobs\RoutineTemplateJob;
use app\jobs\SmsAdminJob;
use app\jobs\WechatTemplateJob as TemplateJob;
use app\services\message\service\StoreServiceServices;
use app\services\message\sms\SmsSendServices;
use app\services\order\StoreOrderStatusServices;
use app\services\user\UserBillServices;
use app\services\wechat\WechatUserServices;
use crmeb\interfaces\ListenerInterface;

class OrderTake implements ListenerInterface
{
    public function handle($event): void
    {
        [$order, $userInfo, $storeTitle] = $event;
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
            /** @var WechatUserServices $wechatServices */
            $wechatServices = app()->make(WechatUserServices::class);
            if ($order['is_channel'] == 1) {
                //小程序
                $openid = $wechatServices->uidToOpenid($userInfo['uid'], 'routine');
                RoutineTemplateJob::dispatchDo('sendOrderTakeOver', [$openid, $order, $storeTitle]);
            } else {
                $openid = $wechatServices->uidToOpenid($userInfo['uid'], 'wechat');
                TemplateJob::dispatchDo('sendOrderTakeSuccess', [$openid, $order, $storeTitle]);
            }

            //发送短信给用户
            /** @var SmsSendServices $smsServices */
            $smsServices = app()->make(SmsSendServices::class);
            $switch = sys_config('confirm_take_over_switch') ? true : false;
            $store_name = $storeTitle;
            $order_id = $order['order_id'];
            $smsServices->send($switch, $order['user_phone'], compact('store_name', 'order_id'), 'TAKE_DELIVERY_CODE');

            //给管理员发送短信
            $switch = sys_config('admin_confirm_take_over_switch') ? true : false;
            /** @var StoreServiceServices $services */
            $services = app()->make(StoreServiceServices::class);
            $adminList = $services->getStoreServiceOrderNotice();
            SmsAdminJob::dispatchDo('sendAdminConfirmTakeOver', [$switch, $adminList, $order]);

        } catch (\Throwable $e) {
        }
    }
}