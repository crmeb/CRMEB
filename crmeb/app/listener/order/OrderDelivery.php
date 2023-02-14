<?php


namespace app\listener\order;


use app\jobs\TakeOrderJob;
use crmeb\interfaces\ListenerInterface;

/**
 * 订单到期自动收货
 * Class OrderDelivery
 * @package app\listener\order
 */
class OrderDelivery implements ListenerInterface
{
    public function handle($event): void
    {
        [$orderInfo, $storeTitle, $data, $type] = $event;

        //到期自动收货
        $time = sys_config('system_delivery_time') ?? 0;
        if ($time != 0) {
            $sevenDay = 24 * 3600 * $time;
            TakeOrderJob::dispatchSecs((int)$sevenDay, [$orderInfo->id]);
        }
    }
}
