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

namespace app\services\order;


use app\dao\order\StoreOrderStoreOrderStatusDao;
use app\services\BaseServices;

/**
 * Class StoreOrderStoreOrderStatusServices
 * @package app\services\order
 * @method getTakeOrderIds(array $where, ?int $limit = 0)
 */
class StoreOrderStoreOrderStatusServices extends BaseServices
{

    /**
     * StoreOrderStoreOrderStatusServices constructor.
     * @param StoreOrderStoreOrderStatusDao $dao
     */
    public function __construct(StoreOrderStoreOrderStatusDao $dao)
    {
        $this->dao = $dao;
    }
}
