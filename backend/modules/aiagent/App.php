<?php
declare(strict_types=1);

namespace modules\aiagent;

use crmeb\crontab\CrontabTaskRegistry;
use modules\aiagent\crontab\AiAgentTaskDispatcherTask;
use think\App as ThinkApp;

/** AI Agent 扩展入口 */
class App
{
    public static function register(ThinkApp $app): void
    {
        // 注册 AI 自动化任务调度触发器（timer 按 eb_system_crontab.mark=aiAgentTask 每分钟触发）
        $app->make(CrontabTaskRegistry::class)->register('aiAgentTask', AiAgentTaskDispatcherTask::class);
    }

    public static function boot(ThinkApp $app): void
    {
        $eventFile = __DIR__ . '/event.php';
        if (is_file($eventFile)) {
            $event = include $eventFile;
            if (is_array($event)) {
                $app->loadEvent($event);
            }
        }
    }
}
