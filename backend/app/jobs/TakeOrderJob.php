<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\jobs;


use app\services\order\OrderCenterServices;
use app\services\order\OrderTakeServices;
use crmeb\basic\BaseJobs;
use think\facade\Log;

/**
 * 自动收货消息队列
 * Class TakeOrderJob
 * @package crmeb\jobs
 */
class TakeOrderJob extends BaseJobs
{

    /**
     * @param $id
     * @return bool
     */
    public function doJob($id)
    {
        /** @var OrderTakeServices $services */
        $services  = app()->make(OrderTakeServices::class);
        $orderInfo = $services->get($id);
        if (!$orderInfo) {
            return true;
        }
        if (!$orderInfo->paid) {
            return true;
        }
        if ($orderInfo->refund_status == 2) {
            return true;
        }
        /** @var OrderCenterServices $orderServices */
        $orderServices = app()->make(OrderCenterServices::class);
        $order         = $orderServices->tidyOrder($orderInfo);
        if ($order['_status']['_type'] != 2) {
            return true;
        }
        $orderInfo->status = 2;
        $res = $orderInfo->save();
        try {
            // 订单收货后置动作
            $services->storeProductOrderUserTakeDelivery($order);
        } catch (\Throwable $e) {
            Log::error('自动收货消息队列执行成功,收货后置动作失败,失败原因:' . $e->getMessage());
        }
        return $res;
    }

}
