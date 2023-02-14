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

namespace app\services\activity\coupon;

use app\services\BaseServices;
use app\dao\activity\coupon\StoreCouponIssueUserDao;

/**
 * Class StoreCouponIssueUserServices
 * @package app\services\coupon
 * @method  getColumn(array $where, string $field, ?string $key = '')
 * @method  delIssueUserCoupon(array $where)
 */
class StoreCouponIssueUserServices extends BaseServices
{

    /**
     * StoreCouponIssueUserServices constructor.
     * @param StoreCouponIssueUserDao $dao
     */
    public function __construct(StoreCouponIssueUserDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取列表
     * @param array $where
     * @return array
     */
    public function issueLog(array $where)
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getList($where, $page, $limit);
        $site_url = sys_config('site_url');
        foreach ($list as &$item) {
            $item['avatar'] = strpos($item['avatar'], 'http') === false ? ($site_url . $item['avatar']) : $item['avatar'];
        }
        $count = $this->dao->count($where);
        return compact('list', 'count');
    }
}
