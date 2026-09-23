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

namespace modules\channel\wechat_mini\listener;

use crmeb\interfaces\ListenerInterface;
use modules\channel\wechat_mini\service\Authenticator;
use modules\channel\wechat_mini\service\LiveGoodsServices;
use modules\channel\wechat_mini\service\LiveRoomServices;
use modules\channel\wechat_mini\service\RoutineSchemeServices;
use modules\channel\wechat_mini\sdk\MiniProgramService;
use think\facade\Log;

/**
 * 微信小程序渠道服务代理监听器
 * 响应 app 层 ChannelServiceProxy 事件，代理调用本模块的服务方法
 * 避免 app 层直接引用 modules 层的渠道服务
 */
class ChannelServiceProxyListener implements ListenerInterface
{
    /**
     * 实例方法服务映射：服务简称 => [服务类名, 允许调用的方法白名单]
     */
    private const INSTANCE_MAP = [
        'LiveGoodsServices' => [
            LiveGoodsServices::class,
            ['syncGoodStatus'],
        ],
        'LiveRoomServices' => [
            LiveRoomServices::class,
            ['syncRoomStatus'],
        ],
        'RoutineSchemeServices' => [
            RoutineSchemeServices::class,
            ['value'],
        ],
        // 渠道认证器：统一登录入口 AuthController 通过事件代理调用
        'Authenticator' => [
            Authenticator::class,
            ['login'],
        ],
    ];

    /**
     * 静态方法服务映射：服务简称 => [服务类名, 允许调用的方法白名单]
     */
    private const STATIC_MAP = [
        'MiniProgramService' => [
            MiniProgramService::class,
            ['appCodeUnlimitService', 'serve'],
        ],
    ];

    /**
     * 代理调用微信小程序渠道服务方法
     * @param mixed $event 事件数据，结构：
     *   ['endpoint' => 'wechat_mini', 'service' => string, 'action' => string, 'args' => array]
     * @return mixed 查询结果，不匹配返回 null
     */
    public function handle($event)
    {
        $endpoint = $event['endpoint'] ?? '';
        // 非微信小程序端不处理
        if ($endpoint !== 'wechat_mini') {
            return null;
        }

        $service = $event['service'] ?? '';
        $action = $event['action'] ?? '';
        $args = $event['args'] ?? [];

        try {
            // 实例方法调用
            if (isset(self::INSTANCE_MAP[$service])) {
                [$class, $allowedActions] = self::INSTANCE_MAP[$service];
                if (!in_array($action, $allowedActions)) {
                    return null;
                }
                $instance = app()->make($class);
                return $instance->$action(...$args);
            }

            // 静态方法调用
            if (isset(self::STATIC_MAP[$service])) {
                [$class, $allowedActions] = self::STATIC_MAP[$service];
                if (!in_array($action, $allowedActions)) {
                    return null;
                }
                return $class::$action(...$args);
            }
        } catch (\Throwable $e) {
            Log::warning('[WechatMini] ChannelServiceProxy failed: ' . $e->getMessage());
            return null;
        }

        return null;
    }
}
