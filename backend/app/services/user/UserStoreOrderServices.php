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
declare (strict_types=1);

namespace app\services\user;

use crmeb\basic\BaseService;
use app\dao\user\UserOrderCenterDao;

/**
 * 用户推广订单统计服务
 * 由旧 UserStoreOrderServices 迁移，底层关联表从 eb_store_order 改为 eb_order_center
 *
 * Class UserStoreOrderServices
 * @package app\services\user
 */
class UserStoreOrderServices extends BaseService
{
    /**
     * @var UserOrderCenterDao
     */
    protected $dao;

    /**
     * UserStoreOrderServices constructor.
     * @param UserOrderCenterDao $dao
     */
    public function __construct(UserOrderCenterDao $dao)
    {
        $this->dao = $dao;
    }

    public function getUserSpreadCountList($uid, $orderBy = '', $keyword = '')
    {
        if ($orderBy === '') {
            $orderBy = 'u.add_time desc';
        }
        $where = [];
        $where[] = ['u.uid', 'IN', $uid];
        if (strlen(trim($keyword))) {
            $where[] = ['u.nickname|u.phone', 'LIKE', "%$keyword%"];
        }
        [$page, $limit] = $this->getPageValue();
        $field = "u.uid,u.nickname,u.phone,u.avatar,from_unixtime(u.add_time,'%Y/%m/%d') as time,u.spread_time,u.spread_count as childCount,p.orderCount,p.numberCount";
        $list = $this->dao->getUserSpreadCountList($where, $field, $orderBy, $page, $limit);
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        foreach ($list as &$item) {
            $item['childCount'] = count($userServices->getUserSpredadUids($item['uid'], 1)) ?? 0;
        }
        return $list;
    }
}
