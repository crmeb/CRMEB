<?php

namespace app\listener\order;

use app\jobs\RefundOrderJob;
use app\services\order\OutStoreOrderRefundServices;
use crmeb\interfaces\ListenerInterface;

/**
 * 售后单生成
 * Class orderRefundCreateAfter
 * @package app\listener\order
 */
class OrderRefundCreateAfter implements ListenerInterface
{
    public function handle($event): void
    {
        [$order] = $event;
    }
}
