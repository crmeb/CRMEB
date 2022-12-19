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
declare (strict_types=1);

namespace app\services\user;

use app\services\BaseServices;
use app\dao\user\UserSpreadDao;

/**
 * Class UserSpreadServices
 * @package app\services\user
 */
class UserSpreadServices extends BaseServices
{

    /**
     * UserSpreadServices constructor.
     * @param UserSpreadDao $dao
     */
    public function __construct(UserSpreadDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 记录推广关系
     * @param int $uid
     * @param int $spread_uid
     * @return false|mixed
     */
    public function setSpread(int $uid, int $spread_uid, int $spread_time = 0)
    {
        if (!$uid || !$spread_uid) return false;
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);

        if (!$userServices->getUserInfo($uid, 'uid')) {
            return false;
        }
        if (!$userServices->getUserInfo($spread_uid, 'uid')) {
            return false;
        }
        if ($this->dao->save(['uid' => $uid, 'spread_uid' => $spread_uid, 'spread_time' => $spread_time ?: time()])) {
            $userServices->incField($spread_uid, 'spread_count', 1);
            return true;
        } else {
            return false;
        }
    }

    /**
     * 查询推广用户uids
     * @param int $uid
     * @param int $type 1:一级2：二级 0：所有
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getSpreadUids(int $uid, int $type = 0, array $where = [])
    {
        if (!$uid) return [];
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        if (!$userServices->getUserInfo($uid, 'uid')) {
            return [];
        }
        if ($where && isset($where['time'])) {
            $where['timeKey'] = 'spread_time';
        }
        $where['spread_uid'] = $uid;
        $spread_one = $this->dao->getSpreadUids($where);
        if ($type == 1) {
            return $spread_one;
        }
        $where['spread_uid'] = $spread_one;
        $spread_two = $this->dao->getSpreadUids($where);
        if ($type == 2) {
            return $spread_two;
        }
        return array_unique(array_merge($spread_one, $spread_two));
    }
}
