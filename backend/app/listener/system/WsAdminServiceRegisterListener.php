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

namespace app\listener\system;

use app\services\workerman\WorkermanService;
use crmeb\interfaces\ListenerInterface;

/**
 * 长连接网关服务注册监听器（系统内置）
 * 响应 app 层 WebSocketServiceRegister 事件，返回管理后台通知服务的处理器类名，
 * 由长连接命令统一实例化并挂载到统一长连接网关
 */
class WsAdminServiceRegisterListener implements ListenerInterface
{
    /**
     * 返回系统内置 admin 服务处理器类名
     * @param mixed $event 事件数据（暂未使用）
     * @return string WorkermanService 完整类名
     */
    public function handle($event)
    {
        return WorkermanService::class;
    }
}
