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
 * 用户提现申请事件监听器
 * Class UserExtractApplyListener
 * @package app\listener\user
 */
class UserExtractApplyListener implements ListenerInterface
{
    /**
     * 用户提现申请事件处理
     * @param $event
     * @return void
     */
    public function handle($event): void
    {
        [$nickname, $amount] = $event;
        
        try {
        } catch (\Throwable $e) {
            Log::error('用户提现申请监听失败,失败原因:' . $e->getMessage());
        }
    }
}