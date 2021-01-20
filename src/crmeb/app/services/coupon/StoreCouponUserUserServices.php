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

namespace app\services\coupon;

use app\services\BaseServices;
use app\dao\coupon\StoreCouponUserUserDao;

/**
 *
 * Class StoreCouponUserUserServices
 * @package app\services\coupon
 */
class StoreCouponUserUserServices extends BaseServices
{

    /**
     * StoreCouponUserUserServices constructor.
     * @param StoreCouponUserUserDao $dao
     */
    public function __construct(StoreCouponUserUserDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取列表
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getList(array $where)
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->sysPage($where, $page, $limit);
        foreach ($list as $k=>&$v){
            $v['start_time'] = $v['add_time'];
        }
        $count = $this->dao->sysCount($where);
        return compact('list', 'count');
    }
}
