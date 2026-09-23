<?php
declare(strict_types=1);

namespace modules\channel\wechat_mini\crontab;

use crmeb\crontab\CrontabTaskInterface;
use modules\channel\wechat_mini\service\LiveRoomServices;
use think\facade\Log;

class LiveRoomStatusTask implements CrontabTaskInterface
{
    public function getMark(): string
    {
        return 'liveRoomStatus';
    }

    public function getName(): string
    {
        return '自动更新直播间状态';
    }

    public function getDescription(): string
    {
        return '同步直播间的开播/关闭状态，与微信小程序后台保持一致';
    }

    public function handle(): void
    {
        try {
            app()->make(LiveRoomServices::class)->syncRoomStatus();
            $this->log('自动更新直播间状态执行成功');
        } catch (\Throwable $e) {
            $this->log('自动更新直播间状态失败,失败原因:' . $e->getMessage(), 'error');
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
