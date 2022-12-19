<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\jobs;


use app\services\activity\combination\StorePinkServices;
use app\services\order\StoreOrderRefundServices;
use app\services\order\StoreOrderServices;
use crmeb\basic\BaseJobs;
use crmeb\traits\QueueTrait;

class PinkJob extends BaseJobs
{
    use QueueTrait;

    public function doJob($pinkId)
    {
        /** @var StorePinkServices $pinkService */
        $pinkService = app()->make(StorePinkServices::class);
        $people = $pinkService->value(['id' => $pinkId], 'people');
        $count = $pinkService->count(['k_id' => $pinkId, 'is_refund' => 0]) + 1;
        $orderIds = $pinkService->getColumn([['id|k_id', '=', $pinkId]], 'order_id_key', 'uid');
        if ($people > $count) {
            $refundData = [
                'refund_reason' => '拼团时间超时',
                'refund_explain' => '拼团时间超时',
                'refund_img' => json_encode([]),
            ];
            foreach ($orderIds as $key => $item) {
                /** @var StoreOrderServices $orderService */
                $orderService = app()->make(StoreOrderServices::class);
                $order = $orderService->get($item);

                /** @var StoreOrderRefundServices $orderRefundService */
                $orderRefundService = app()->make(StoreOrderRefundServices::class);
                $orderRefundService->applyRefund((int)$order['id'], (int)$order['uid'], $order, [], 1, (float)$order['pay_price'], $refundData, 1);

                $pinkService->update([['id|k_id', '=', $pinkId]], ['status' => 3]);
                $pinkService->orderPinkAfterNo($key, $pinkId, false, $order->is_channel);
            }
            return true;
        }
        return true;
    }
}
