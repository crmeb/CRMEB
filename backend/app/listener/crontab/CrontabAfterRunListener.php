<?php
namespace app\listener\crontab;

use crmeb\interfaces\ListenerInterface;
use think\facade\Log;

class CrontabAfterRunListener implements ListenerInterface
{
    public function handle($event): void
    {
        [$crontabId, $crontabName, $mark, $executeTime, $duration] = $event;

        try {
            Log::info('定时任务执行完成,任务ID:' . $crontabId . ',任务名称:' . $crontabName . ',任务标识:' . $mark . ',执行时间:' . $executeTime . ',耗时:' . $duration . 'ms');
        } catch (\Throwable $e) {
            Log::error('定时任务执行后事件监听失败,失败原因:' . $e->getMessage());
        }
    }
}
