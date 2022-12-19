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


use app\services\user\UserServices;
use app\services\wechat\WechatUserServices;
use crmeb\basic\BaseJobs;
use crmeb\traits\QueueTrait;
use think\facade\Log;

class UserJob extends BaseJobs
{
    use QueueTrait;

    /**
     * 执行同步数据后
     * @param $openids
     * @return bool
     */
    public function doJob($openids)
    {
        if (!$openids || !is_array($openids)) {
            return true;
        }
        $noBeOpenids = [];
        try {
            /** @var WechatUserServices $wechatUser */
            $wechatUser  = app()->make(WechatUserServices::class);
            $noBeOpenids = $wechatUser->syncWechatUser($openids);
        } catch (\Throwable $e) {
            Log::error('更新wechatUser用户信息失败,失败原因:' . $e->getMessage());
        }
        if (!$noBeOpenids) {
            return true;
        }
        try {
            /** @var UserServices $user */
            $user = app()->make(UserServices::class);
            $user->importUser($noBeOpenids);
        } catch (\Throwable $e) {
            Log::error('新增用户失败,失败原因:' . $e->getMessage());
        }
        return true;
    }
}
