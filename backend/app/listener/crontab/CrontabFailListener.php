<?php
namespace app\listener\crontab;

use crmeb\interfaces\ListenerInterface;
use think\facade\Log;

class CrontabFailListener implements ListenerInterface
{
    public function handle($event): void
    {
        [$crontabId, $crontabName, $mark, $executeTime, $error] = $event;

        try {
            Log::error('定时任务执行失败,任务ID:' . $crontabId . ',任务名称:' . $crontabName . ',任务标识:' . $mark . ',执行时间:' . $executeTime . ',错误信息:' . $error);
        } catch (\Throwable $e) {
            Log::error('定时任务失败事件监听失败,失败原因:' . $e->getMessage());
        }
    }
}
