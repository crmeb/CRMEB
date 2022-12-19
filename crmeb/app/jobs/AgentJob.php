<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\jobs;

use app\services\agent\AgentLevelServices;
use app\services\user\UserServices;
use crmeb\basic\BaseJobs;
use crmeb\traits\QueueTrait;
use think\facade\Log;

/**
 * 检测分销员等级升级
 * Class OrderJob
 * @package crmeb\jobs
 */
class AgentJob extends BaseJobs
{
    use QueueTrait;

    /**
     * 执行检测升级
     * @param $order
     * @return bool
     */
    public function doJob(int $uid)
    {
        //检测分销员等级升级
        try {
            //商城分销是否开启
            if (!sys_config('brokerage_func_status')) {
                return true;
            }
            /** @var UserServices $userServices */
            $userServices = app()->make(UserServices::class);
            $userInfo = $userServices->getUserInfo($uid);
            if (!$userInfo) {
                return true;
            }
            //获取上级uid ｜｜ 开启自购返回自己uid
            $spread_uid = $userServices->getSpreadUid($uid, $userInfo);
            $two_spread_uid = 0;
            if ($spread_uid > 0 && $one_user_info = $userServices->getUserInfo($spread_uid)) {
                $two_spread_uid = $userServices->getSpreadUid($spread_uid, $one_user_info, false);
            }
            $uids = array_unique([$uid, $spread_uid, $two_spread_uid]);

            /** @var AgentLevelServices $agentLevelServices */
            $agentLevelServices = app()->make(AgentLevelServices::class);
            //检测升级
            $agentLevelServices->checkUserLevelFinish($uid, $uids);

            return true;
        } catch (\Throwable $e) {
            Log::error('检测分销等级升级失败,失败原因:' . $e->getMessage());
        }
    }
}
