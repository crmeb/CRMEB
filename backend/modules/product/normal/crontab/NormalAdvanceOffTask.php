<?php
declare(strict_types=1);

namespace modules\product\normal\crontab;

use crmeb\crontab\CrontabTaskInterface;
use modules\product\normal\service\CrontabServices;
use think\facade\Log;

/**
 * 预售商品到期自动下架任务
 * 预售为普通商品能力，mark 与 eb_system_crontab 种子 advanceOff 对齐
 */
class NormalAdvanceOffTask implements CrontabTaskInterface
{
    public function getMark(): string
    {
        return 'advanceOff';
    }

    public function getName(): string
    {
        return '预售商品到期自动下架';
    }

    public function getDescription(): string
    {
        return '普通商品预售到达预售结束时间后自动下架';
    }

    public function handle(): void
    {
        try {
            app()->make(CrontabServices::class)->advanceOff();
            $this->log('普通商品预售到期自动下架执行成功');
        } catch (\Throwable $e) {
            $this->log('普通商品预售到期自动下架失败,失败原因:' . $e->getMessage(), 'error');
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
