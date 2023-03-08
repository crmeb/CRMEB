<?php


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