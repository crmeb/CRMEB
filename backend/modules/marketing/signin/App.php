<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace modules\marketing\signin;

use crmeb\crontab\CrontabTaskRegistry;
use modules\marketing\signin\crontab\SignRemindTask;
use think\App as ThinkApp;

/**
 * 签到活动模块注册器
 *
 * 注册签到提醒定时任务，并加载签到相关事件监听器。
 */
class App
{
    /**
     * 模块注册
     * @param ThinkApp $app
     */
    public static function register(ThinkApp $app): void
    {
        $app->make(CrontabTaskRegistry::class)->register('signRemind', SignRemindTask::class);
    }

    /**
     * 模块启动：加载事件监听器
     * @param ThinkApp $app
     */
    public static function boot(ThinkApp $app): void
    {
        // 加载本模块事件监听器
        $eventFile = __DIR__ . '/event.php';
        if (is_file($eventFile)) {
            $event = include $eventFile;
            if (is_array($event)) {
                $app->loadEvent($event);
            }
        }
    }
}
