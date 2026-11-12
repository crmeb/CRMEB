<?php
declare(strict_types=1);

namespace modules\product\normal\listener;

/**
 * 实物商品支付广播不再推进待发货。
 * 待发货、销量和支付后生命周期改由 Handler::fulfillOrder 在事务提交后直接调用。
 */
class OrderPaidListener
{
    /**
     * 保留类名，避免旧注册再次履约。
     * @param mixed $event 事件参数
     * @return void
     */
    public function handle($event): void
    {
    }
}
