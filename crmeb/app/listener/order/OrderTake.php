<?php


namespace app\listener\order;

use app\services\order\StoreOrderStatusServices;
use app\services\order\StoreOrderTakeServices;
use app\services\user\UserBillServices;
use crmeb\interfaces\ListenerInterface;
use think\facade\Log;

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
            /** @var StoreOrderTakeServices $storeOrderTake */
            $storeOrderTake = app()->make(StoreOrderTakeServices::class);
            if ($order['pid'] > 0) {
                $p_order = $storeOrderTake->get((int)$order['pid'], ['id,pid,status']);
                //主订单全部发货 且子订单没有待收货 有待评价
                if ($p_order['status'] == 1 && !$storeOrderTake->count(['pid' => $order['pid'], 'status' => 2]) && $storeOrderTake->count(['pid' => $order['pid'], 'status' => 3])) {
                    $storeOrderTake->update($p_order['id'], ['status' => 2]);
                    $statusService->save([
                        'oid' => $p_order['id'],
                        'change_type' => 'take_delivery',
                        'change_message' => '已收货',
                        'change_time' => time()
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
        }
    }
}
