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

namespace modules\marketing\signin\listener;

use crmeb\interfaces\ListenerInterface;
use app\services\message\Notice;
use think\facade\Log;

/**
 * 签到提醒通知监听器
 */
class SignRemindNoticeListener implements ListenerInterface
{
    /**
     * 签到事件处理
     * @param $event
     * @return void
     */
    public function handle($event): void
    {
        [$type, $data] = array_pad((array)$event, 2, null);

        try {
            switch ($type) {
                // sign/sign_reward/sign_continuous/sign_miss 四类事件无生产者也无监听者，死转发已移除；
                // 签到成功通知若需启用，按 sign_remind 模式经 Notice 管道直发并补触发点
                case 'sign_remind':
                    if (!sys_config('sign_status', 0) || !sys_config('sign_remind', 0)
                        || !is_array($data) || (int)($data['uid'] ?? 0) <= 0) {
                        return;
                    }
                    $channels = [];
                    foreach ((array)sys_config('sign_remind_type', []) as $channelType) {
                        if ($channelType === 1 || $channelType === '1') {
                            $channels[] = 'is_sms';
                        } elseif ($channelType === 2 || $channelType === '2') {
                            $channels[] = 'is_system';
                        }
                    }
                    // 事件传入的本次渠道不能扩大当前配置允许的发送范围。
                    if (isset($data['channels'])) {
                        $channels = array_intersect($channels, (array)$data['channels']);
                    }
                    if (!$channels) {
                        return;
                    }
                    $data['site_name'] = sys_config('site_name') ?: '';
                    // 场景：每日向已订阅提醒且当天未签到的用户发送签到提醒
                    Notice::to((int)$data['uid'])
                        ->channels(array_values(array_unique($channels)))
                        ->sendMsg('sign_remind', '签到提醒', [
                            'uid' => (int)$data['uid'], // 用户ID
                            'phone' => $data['phone'] ?? '', // 用户手机号
                            'sign_days' => $data['sign_days'] ?? 0, // 签到天数
                            'integral' => $data['integral'] ?? 0, // 签到积分
                            'site_name' => $data['site_name'], // 商城名称
                        ]);
                    break;
            }
        } catch (\Throwable $e) {
            Log::error('签到事件监听失败,类型:' . $type . ',失败原因:' . $e->getMessage());
        }
    }
}
