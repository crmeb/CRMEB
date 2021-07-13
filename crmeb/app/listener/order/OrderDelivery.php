<?php


namespace app\listener\order;


use app\jobs\RoutineTemplateJob;
use app\jobs\TakeOrderJob;
use app\jobs\WechatTemplateJob as TemplateJob;
use app\services\message\sms\SmsSendServices;
use app\services\user\UserServices;
use app\services\wechat\WechatUserServices;
use crmeb\interfaces\ListenerInterface;
use crmeb\utils\Str;
use think\facade\Log;

class OrderDelivery implements ListenerInterface
{
    public function handle($event): void
    {
        [$orderInfo, $storeTitle, $data, $type] = $event;

        //发送模板消息加入队列
        /** @var WechatUserServices $wechatServices */
        $wechatServices = app()->make(WechatUserServices::class);
        $storeTitle = Str::substrUTf8($storeTitle, 20, 'UTF-8', '');

        if($type == 1 || $type == 2){
            switch ($orderInfo->is_channel) {
                case 1:
                    $openid = $wechatServices->uidToOpenid($orderInfo['uid'], 'routine');
                    $isGive = $type == 1 ? 1 : 0;
                    RoutineTemplateJob::dispatchDo('sendOrderPostage', [$openid, $orderInfo->toArray(), $storeTitle, $isGive]);
                    break;
                default:
                    $openid = $wechatServices->uidToOpenid($orderInfo['uid']);
                    if ($type == 1) {
                        TemplateJob::dispatchDo('sendOrderPostage', [$openid, $orderInfo->toArray(), $data]);
                    } else {
                        TemplateJob::dispatchDo('sendOrderDeliver', [$openid, $storeTitle, $orderInfo->toArray(), $data]);
                    }
                    break;
            }
        }

        //发送短信通知
        try {
            $order_id = $orderInfo->order_id;
            $switch = sys_config('deliver_goods_switch') ? true : false;
            $service = app()->make(UserServices::class);
            $nickname = $service->value(['uid' => $orderInfo->uid], 'nickname');
            $store_name = $storeTitle;
            /** @var SmsSendServices $smsServices */
            $smsServices = app()->make(SmsSendServices::class);
            $smsServices->send($switch, $orderInfo->user_phone, compact('order_id', 'store_name', 'nickname'), 'DELIVER_GOODS_CODE', '用户发货发送短信失败，订单号为：' . $order_id);
        } catch (\Throwable $e) {
            Log::error('发货短信通知失败,订单号：' . $orderInfo['order_id'] . ',原因：短信平台' . $e->getMessage());
        }

        //到期自动收货
        $time = sys_config('system_delivery_time') ?? 0;
        if ($time != 0) {
            $sevenDay = 24 * 3600 * $time;
            TakeOrderJob::dispatchSece((int)$sevenDay, [$orderInfo->id]);
        }
    }
}