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
use app\dao\user\UserUserBrokerageDao;

/**
 * 用户关联佣金
 * Class UserUserBrokerageServices
 * @package app\services\user
 */
class UserUserBrokerageServices extends BaseServices
{

    /**
     * UserUserBrokerageServices constructor.
     * @param UserUserBrokerageDao $dao
     */
    public function __construct(UserUserBrokerageDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取佣金列表
     * @param array $where
     * @param string $field
     * @param string $order
     * @param int $limit
     * @return array
     */
    public function getBrokerageList(array $where, string $field = '*', string $order = '', int $limit = 0)
    {
        if ($limit) {
            [$page] = $this->getPageValue();
        } else {
            [$page, $limit] = $this->getPageValue();
        }
        $list = $this->dao->getList($where, $field, $order, $page, $limit);
        $count = $this->dao->getCount($where);
        return [$count, $list];
    }
}
