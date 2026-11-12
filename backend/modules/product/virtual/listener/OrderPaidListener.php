<?php
declare(strict_types=1);

namespace modules\product\virtual\listener;

/**
 * 网盘支付广播不再发放资源。
 * 发放改由 Handler::fulfillOrder 在事务提交后直接调用。
 */
class OrderPaidListener
{
    /**
     * 保留类名，避免旧注册再次发放。
     * @param mixed $event 事件参数
     * @return void
     */
    public function handle($event): void
    {
    }
}
