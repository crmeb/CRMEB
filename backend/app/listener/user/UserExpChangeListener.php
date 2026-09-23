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

use app\services\user\UserLevelServices;
use app\services\user\UserServices;
use crmeb\interfaces\ListenerInterface;
use think\facade\Log;


/**
 * 用户经验变动事件监听器
 * 处理：等级升降检测 + 等级变更通知
 * Class UserExpChangeListener
 * @package app\listener\user
 */
class UserExpChangeListener implements ListenerInterface
{
    /**
     * 用户经验变动事件处理
     * @param $event [$uid, $amount]
     *  - int $uid 用户ID
     *  - float $amount 变动数量
     * @return void
     */
    public function handle($event): void
    {
        [$uid, $amount] = $event;

        try {
            // 触发用户等级事件
            event('UserLevel', [$uid]);
            // 发送等级变更通知
            // $this->sendLevelChangeNotice($uid, $oldLevel, $newLevel);
        } catch (\Throwable $e) {
            Log::error('用户经验变动监听失败,失败原因:' . $e->getMessage());
        }
    }

       
}
