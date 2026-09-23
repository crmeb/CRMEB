<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace modules\service\mcp;

use think\App as ThinkApp;

/**
 * MCP 服务模块应用
 *
 * 路由由 api 应用入口按场景加载（modules/service/mcp/route/user.php），
 * 无独立应用入口、无 Handler 注册。
 */
class App
{
    /**
     * 模块注册（本模块无注册中心依赖，空实现）
     *
     * @param ThinkApp $app 应用实例
     * @return void
     */
    public static function register(ThinkApp $app): void
    {
    }

    /**
     * 模块启动：加载本模块事件监听器
     *
     * @param ThinkApp $app 应用实例
     * @return void
     */
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
