<?php

namespace app\listener\order;

use crmeb\interfaces\ListenerInterface;

class OrderRefund implements ListenerInterface
{
    public function handle($event): void
    {
        [$order] = $event;
    }
}
