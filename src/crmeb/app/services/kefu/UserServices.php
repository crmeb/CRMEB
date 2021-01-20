<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\services\kefu;


use app\dao\user\UserDao;
use app\services\BaseServices;
use crmeb\traits\ServicesTrait;
use think\exception\ValidateException;
use app\services\user\UserLabelServices;
use app\services\system\SystemUserLevelServices;
use app\services\user\UserLabelRelationServices;
use app\services\message\service\StoreServiceRecordServices;

/**
 * Class UserServices
 * @package app\services\kefu
 */
class UserServices extends BaseServices
{
    use ServicesTrait;

    /**
     * UserServices constructor.
     * @param UserDao $dao
     */
    public function __construct(UserDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取用户信息
     * @param int $uid
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getUserInfo(int $uid)
    {
        /** @var StoreServiceRecordServices $kefuService */
        $kefuService = app()->make(StoreServiceRecordServices::class);
        if (!$kefuService->count(['to_uid' => $uid])) {
            throw new ValidateException('不存在此用户');
        }
        $userInfo = $this->dao->get($uid, ['nickname', 'avatar', 'spread_uid', 'is_promoter', 'birthday', 'now_money', 'user_type', 'level', 'group_id', 'phone', 'is_money_level'], ['userGroup']);
        if (!$userInfo) {
            throw new ValidateException('用户不存在');
        }
        /** @var UserLabelRelationServices $labalServices */
        $labalServices = app()->make(UserLabelRelationServices::class);
        $labalId = $labalServices->getColumn(['uid' => $uid], 'label_id', 'label_id');
        /** @var UserLabelServices $services */
        $services = app()->make(UserLabelServices::class);
        $labelNames = $services->getColumn([['id', 'in', $labalId]], 'label_name');
        $userInfo->labelNames = $labelNames;
        $userInfo->spread_name = $userInfo->level_name = '';
        if ($userInfo->spread_uid) {
            $userInfo->spread_name = $this->dao->value(['uid' => $userInfo->spread_uid], 'nickname');
        }
        if ($userInfo->level) {
            /** @var SystemUserLevelServices $levelService */
            $levelService = app()->make(SystemUserLevelServices::class);
            $userInfo->level_name = $levelService->value(['id' => $userInfo->level], 'name');
        }
        if ($userInfo->userGroup) {
            $userInfo->group_name = $userInfo->userGroup->group_name;
            unset($userInfo->userGroup);
        }
        return $userInfo->toArray();
    }
}
