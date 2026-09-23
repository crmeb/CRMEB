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
namespace app\listener\user;


use app\services\user\UserServices;
use crmeb\interfaces\ListenerInterface;
use think\facade\Log;

/**
 * 用户登录后置事件
 * 由 LoginServices::createToken 在 token 生成成功后统一触发
 * 作为所有渠道登录的统一汇聚点，渠道模块可通过监听 UserLogin 事件实现登录后置处理
 * Class UserLoginListener
 * @package app\listener\user
 */
class UserLoginListener implements ListenerInterface
{
    /**
     * 用户登录后置事件处理
     * @param mixed $event 事件参数，结构：[['uid' => int, 'channel' => string, 'login_type' => string]]
     *   - uid: 用户ID
     *   - channel: 登录渠道（h5/wechat_mini/wechat_mp/app）
     *   - login_type: 登录方式（password/mobile/wechat_code/wechat_auth/scan/apple）
     * @return void
     */
    public function handle($event): void
    {
        // 事件参数结构对齐 LoginServices::createToken 触发格式
        $payload = $event[0] ?? [];
        $uid = (int)($payload['uid'] ?? 0);
        $channel = (string)($payload['channel'] ?? '');
        $loginType = (string)($payload['login_type'] ?? '');

        if (!$uid) {
            return;
        }

        try {
            $request = app()->request;

            // 统一兜底更新最后登录时间和IP
            // 即使部分登录路径未走 updateUserInfo，UserLogin 事件触发后也会记录
            // 与 LoginServices::updateUserInfo 的更新幂等，不会造成数据冲突
            /** @var UserServices $userServices */
            $userServices = app()->make(UserServices::class);
            $userServices->update($uid, [
                'last_time' => time(),
                'last_ip'   => $request->ip(),
            ], 'uid');

            // 记录登录审计日志，便于异常登录排查和登录统计
            Log::info('用户登录', [
                'uid'        => $uid,
                'channel'    => $channel,
                'login_type' => $loginType,
                'ip'         => $request->ip(),
                'time'       => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            Log::error('用户登录监听失败,失败原因:' . $e->getMessage());
        }
    }
}
