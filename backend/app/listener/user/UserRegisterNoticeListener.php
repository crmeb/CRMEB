<?php
declare(strict_types=1);

namespace app\listener\user;

use app\services\message\Notice;

/**
 * 用户注册通知监听器
 * 监听 UserRegister 事件，通过 Notice API 发送注册成功通知。
 */
class UserRegisterNoticeListener
{
    public function handle(array $event): void
    {
        [$spreadUid, $userType, $name, $uid] = $event + [1 => '', 2 => '', 3 => 0];

        try {
            if (!$spreadUid) return;

            // 场景：新用户通过推广链接注册并绑定上下级关系时，通知上级推广人
            // 扁平业务数据：复用 bind_spread_uid 通知模板，is_system 模板用 {nickname}
            Notice::to((int)$spreadUid)->sendMsg('bind_spread_uid', '绑定推广关系通知', [
                'nickname'    => (string)$name,              // 新用户昵称
                'uid'         => (string)$uid,               // 新用户ID
                'spread_uid'  => (string)$spreadUid,         // 推广人UID（消息接收者）
                'spread_time' => date('Y-m-d H:i:s'),        // 绑定时间
                'user_type'   => (string)$userType,          // 用户来源端（wechat/routine/h5 等）
            ]);
        } catch (\Throwable $e) {
        }
    }
}
