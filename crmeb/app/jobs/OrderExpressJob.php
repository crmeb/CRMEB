<?php

namespace app\jobs;

use app\services\order\StoreOrderDeliveryServices;
use crmeb\basic\BaseJobs;
use crmeb\traits\QueueTrait;
use think\facade\Log;

class OrderExpressJob extends BaseJobs
{
    use QueueTrait;

    public function doJob($expressInfo)
    {
        $id = $expressInfo['id'];
        $data = [
            'type' => 1,
            'delivery_name' => $expressInfo['delivery_name'],
            'delivery_code' => $expressInfo['delivery_code'],
            'delivery_id' => $expressInfo['delivery_id'],
            'express_record_type' => 1,
            'express_temp_id' => '',
            'to_name' => '',
            'to_tel' => '',
            'to_addr' => '',
            'sh_delivery_name' => '',
            'sh_delivery_id' => '',
            'sh_delivery_uid' => '',
            'fictitious_content' => '',
            'cart_ids' => [],
            'day_type' => 0,
            'pickup_time' => [],
            'service_type' => '',
        ];
        try {
            app()->make(StoreOrderDeliveryServices::class)->splitDelivery($id, $data, false);
        } catch (\Throwable $e) {
            Log::error('订单ID' . $id . '发货失败,失败原因:' . $e->getMessage());
        }
        return true;
    }
}