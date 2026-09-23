<?php
declare(strict_types=1);

namespace modules\marketing\signin\crontab;

use crmeb\crontab\CrontabTaskInterface;
use modules\marketing\signin\service\UserSignServices;
use think\facade\Log;

/**
 * 每日未签到提醒任务
 */
class SignRemindTask implements CrontabTaskInterface
{
    /**
     * 获取任务标识。
     * @return string
     */
    public function getMark(): string
    {
        return 'signRemind';
    }

    /**
     * 获取任务名称。
     * @return string
     */
    public function getName(): string
    {
        return '未签到提醒';
    }

    /**
     * 获取任务说明。
     * @return string
     */
    public function getDescription(): string
    {
        return '向当天未签到的用户发送签到提醒消息';
    }

    /**
     * 在签到与提醒均启用时执行提醒。
     * @return void
     */
    public function handle(): void
    {
        if (!sys_config('sign_status', 0) || !sys_config('sign_remind', 0)) {
            return;
        }
        try {
            app()->make(UserSignServices::class)->sendSignRemind();
            $this->log('未签到提醒执行成功');
        } catch (\Throwable $e) {
            $this->log('未签到提醒失败,失败原因:' . $e->getMessage(), 'error');
            throw $e;
        }
    }

    /**
     * 根据任务日志开关写入执行日志。
     * @param string $msg 日志内容
     * @param string $level 日志等级
     * @return void
     */
    protected function log(string $msg, string $level = 'info'): void
    {
        if (config("log.timer_log", false)) {
            Log::$level(date('Y-m-d H:i:s') . $msg);
        }
    }
}
