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
declare (strict_types = 1);

namespace app\services\user;

use crmeb\basic\BaseService;
use app\dao\user\UserUserBillDao;

/**
 *
 * Class UserUserBillServices
 * @package app\services\user
 */
class UserUserBillServices extends BaseService
{
    /**
     * @var UserUserBillDao
     */
    protected $dao;

    /**
     * UserUserBillServices constructor.
     * @param UserUserBillDao $dao
     */
    public function __construct(UserUserBillDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * @param $where
     * @param $field
     * @param $order
     * @return array
     */
    public function getBrokerageList(array $where, string $field = '*', string $order = '', $is_page = true)
    {
        [$page, $limit] = $this->getPageValue($is_page);
        $list = $this->dao->getList($where, $field, $order, $page, $limit);
        $count = $this->dao->getCount($where);
        return [$count, $list];
    }
}
