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

namespace app\listener\user;

use crmeb\interfaces\ListenerInterface;
use think\facade\Log;

/**
 * 用户退出登录后置事件
 * 由 LoginServices::logout 统一触发，作为所有渠道注销的统一汇聚点
 * 渠道模块可通过监听 UserLogout 事件实现渠道特定的登录态清理（如微信 session 销毁）
 * Class UserLogoutListener
 * @package app\listener\user
 */
class UserLogoutListener implements ListenerInterface
{
    /**
     * 用户退出登录后置事件处理
     * @param mixed $event 事件参数，结构：[['uid' => int, 'channel' => string]]
     *   - uid: 用户ID
     *   - channel: 登录渠道（h5/wechat_mini/wechat_mp/app）
     * @return void
     */
    public function handle($event): void
    {
        // 事件参数结构对齐 LoginServices::logout 触发格式
        $payload = $event[0] ?? [];
        $uid = (int)($payload['uid'] ?? 0);
        $channel = (string)($payload['channel'] ?? '');

        if (!$uid) {
            return;
        }

        try {
            // 记录注销审计日志，便于追踪用户活跃度
            Log::info('用户退出登录', [
                'uid'     => $uid,
                'channel' => $channel,
                'ip'      => app()->request->ip(),
                'time'    => date('Y-m-d H:i:s'),
            ]);

            // 说明：
            // 1. token 缓存清理由调用方（AuthController/LoginController）负责，因为需要从 request 中获取 token
            // 2. 渠道特定的登录态清理（如微信小程序 session 销毁、公众号授权态清理）
            //    由各渠道模块监听 UserLogout 事件自行实现，app 层不直接调用 modules
        } catch (\Throwable $e) {
            Log::error('用户注销监听失败,失败原因:' . $e->getMessage());
        }
    }
}
