<?php
declare(strict_types=1);

namespace modules\product\normal\crontab;

use crmeb\crontab\CrontabTaskInterface;
use modules\product\normal\service\CrontabServices;
use think\facade\Log;

class NormalTakeDeliveryTask implements CrontabTaskInterface
{
    public function getMark(): string
    {
        return 'normalTakeDelivery';
    }

    public function getName(): string
    {
        return '普通商品自动确认收货';
    }

    public function getDescription(): string
    {
        return '普通商品订单发货后超过系统设置的自动收货天数，自动确认收货';
    }

    public function handle(): void
    {
        try {
            app()->make(CrontabServices::class)->takeDelivery();
            $this->log('普通商品自动确认收货执行成功');
        } catch (\Throwable $e) {
            $this->log('普通商品自动确认收货失败,失败原因:' . $e->getMessage(), 'error');
            throw $e;
        }
    }

    protected function log(string $msg, string $level = 'info'): void
    {
        if (config("log.timer_log", false)) {
            Log::$level(date('Y-m-d H:i:s') . $msg);
        }
    }
}
