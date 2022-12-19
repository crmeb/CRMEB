<?php


namespace app\listener\order;

use app\services\order\StoreOrderStatusServices;
use app\services\order\StoreOrderTakeServices;
use app\services\user\UserBillServices;
use crmeb\interfaces\ListenerInterface;
use think\facade\Log;

/**
 * 订单确认收货
 * Class OrderTake
 * @package app\listener\order
 */
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

            //检查主订单是否需要修改状态
            if ($order['pid'] > 0) {
                /** @var StoreOrderTakeServices $storeOrderTake */
                $storeOrderTake = app()->make(StoreOrderTakeServices::class);
                $storeOrderTake->checkMaster($order['pid']);
            }
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
        }
    }
}
