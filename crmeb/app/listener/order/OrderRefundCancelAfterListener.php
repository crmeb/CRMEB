<?php

namespace app\listener\order;

use app\jobs\RefundOrderJob;
use crmeb\interfaces\ListenerInterface;

/**
 * 售后单取消
 * Class OrderRefundCancelAfterListener
 * @package app\listener\order
 */
class OrderRefundCancelAfterListener implements ListenerInterface
{
    public function handle($event): void
    {
        [$orderRefundInfo] = $event;
    }
}
