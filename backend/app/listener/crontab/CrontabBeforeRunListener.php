<?php
namespace app\listener\crontab;

use crmeb\interfaces\ListenerInterface;
use think\facade\Log;

class CrontabBeforeRunListener implements ListenerInterface
{
    public function handle($event): void
    {
        [$crontabId, $crontabName, $mark] = $event;

        try {
            Log::info('定时任务开始执行,任务ID:' . $crontabId . ',任务名称:' . $crontabName . ',任务标识:' . $mark);
        } catch (\Throwable $e) {
            Log::error('定时任务执行前事件监听失败,失败原因:' . $e->getMessage());
        }
    }
}
