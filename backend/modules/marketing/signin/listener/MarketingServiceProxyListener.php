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

namespace modules\marketing\signin\listener;

use crmeb\interfaces\ListenerInterface;
use modules\marketing\signin\service\UserSignServices;
use think\facade\Log;

/**
 * 签到营销服务代理监听器
 * 响应 app 层 MarketingServiceProxy 事件，代理调用签到模块的服务方法
 * 避免 app 层直接引用 modules 层的营销服务
 */
class MarketingServiceProxyListener implements ListenerInterface
{
    /**
     * 实例方法服务映射：服务简称 => [服务类名, 允许调用的方法白名单]
     */
    private const INSTANCE_MAP = [
        'UserSignServices' => [
            UserSignServices::class,
            ['signConfig', 'getSignSumDay'],
        ],
    ];

    /**
     * 代理调用签到服务方法
     * @param mixed $event 事件数据，结构：
     *   ['module' => 'signin', 'service' => string, 'action' => string, 'args' => array]
     * @return mixed 查询结果，不匹配返回 null
     */
    public function handle($event)
    {
        $module = $event['module'] ?? '';
        // 非签到模块不处理
        if ($module !== 'signin') {
            return null;
        }

        $service = $event['service'] ?? '';
        $action = $event['action'] ?? '';
        $args = $event['args'] ?? [];

        try {
            if (isset(self::INSTANCE_MAP[$service])) {
                [$class, $allowedActions] = self::INSTANCE_MAP[$service];
                if (!in_array($action, $allowedActions)) {
                    return null;
                }
                $instance = app()->make($class);
                return $instance->$action(...$args);
            }
        } catch (\Throwable $e) {
            Log::warning('[Signin] MarketingServiceProxy failed: ' . $e->getMessage());
            return null;
        }

        return null;
    }
}
