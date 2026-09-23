<?php
declare(strict_types=1);

namespace modules\product\normal\crontab;

use crmeb\crontab\CrontabTaskInterface;
use modules\product\normal\service\CrontabServices;
use think\facade\Log;

class NormalOrderCancelTask implements CrontabTaskInterface
{
    public function getMark(): string
    {
        return 'normalOrderCancel';
    }

    public function getName(): string
    {
        return '普通商品未支付自动取消订单';
    }

    public function getDescription(): string
    {
        return '普通商品订单超过系统设置的未支付时间后自动取消，释放库存';
    }

    public function handle(): void
    {
        try {
            app()->make(CrontabServices::class)->orderCancel();
            $this->log('普通商品未支付自动取消订单执行成功');
        } catch (\Throwable $e) {
            $this->log('普通商品未支付自动取消订单失败,失败原因:' . $e->getMessage(), 'error');
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
