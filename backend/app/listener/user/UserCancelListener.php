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

use crmeb\interfaces\ListenerInterface;
use think\facade\Log;

/**
 * 用户注销事件监听器
 * Class UserCancelListener
 * @package app\listener\user
 */
class UserCancelListener implements ListenerInterface
{
    /**
     * 用户注销事件处理
     * @param $event
     * @return void
     */
    public function handle($event): void
    {
        [$uid, $userInfo, $cancelReason] = $event;
        
        try {
            // 清理用户数据、记录注销日志、发送通知等业务逻辑
            // $uid: 用户uid
            // $userinfo: 用户信息
            // $cancelReason: 注销原因
            // 可根据实际业务需求扩展
            // 清楚推广关系
            // 清除用户相关数据
            
        } catch (\Throwable $e) {
            Log::error('用户注销监听失败,失败原因:' . $e->getMessage());
        }
    }
}
