<?php
namespace app\listener\crontab;

use crmeb\interfaces\ListenerInterface;
use think\facade\Log;

class CrontabSuccessListener implements ListenerInterface
{
    public function handle($event): void
    {
        [$crontabId, $crontabName, $mark, $executeTime, $duration] = $event;

        try {
            Log::info('定时任务执行成功,任务ID:' . $crontabId . ',任务名称:' . $crontabName . ',任务标识:' . $mark . ',执行时间:' . $executeTime . ',耗时:' . $duration . 'ms');
        } catch (\Throwable $e) {
            Log::error('定时任务成功事件监听失败,失败原因:' . $e->getMessage());
        }
    }
}
