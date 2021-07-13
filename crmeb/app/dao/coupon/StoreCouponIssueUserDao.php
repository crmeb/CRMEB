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
declare (strict_types=1);

namespace app\dao\coupon;

use app\dao\BaseDao;
use app\model\coupon\StoreCouponIssueUser;

/**
 *
 * Class StoreCouponIssueUserDao
 * @package app\dao\coupon
 */
class StoreCouponIssueUserDao extends BaseDao
{

    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return StoreCouponIssueUser::class;
    }

    /**
     * 获取领取的优惠卷列表
     * @param array $where
     * @param int $page
     * @param int $limit
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getList(array $where, int $page, int $limit)
    {
        return $this->search($where)->with('userInfo')->page($page, $limit)->select()->toArray();
    }
}
