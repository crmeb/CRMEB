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


use app\dao\order\StoreOrderStoreOrderCartInfoDao;
use app\services\BaseServices;

/**
 * Class StoreOrderStoreOrderCartInfoServices
 * @package app\services\order
 * @method getUserCartProductIds(array $where) 获取用户购买过的商品id
 */
class StoreOrderStoreOrderCartInfoServices extends BaseServices
{
    /**
     * StoreOrderStoreOrderCartInfoServices constructor.
     * @param StoreOrderStoreOrderCartInfoDao $dao
     */
    public function __construct(StoreOrderStoreOrderCartInfoDao $dao)
    {
        $this->dao = $dao;
    }

}
