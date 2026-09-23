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
use crmeb\interfaces\ListenerInterface;

/**
 * 用户升级事件
 * Class UserLevelListener
 * @package app\listener\user
 */
class UserLevelListener implements ListenerInterface
{
    public function handle($event): void
    {
        [$uid] = $event;

        //用户升级
        /** @var UserLevelServices $levelServices */
        $levelServices = app()->make(UserLevelServices::class);
        $levelServices->detection((int)$uid);
    }
}