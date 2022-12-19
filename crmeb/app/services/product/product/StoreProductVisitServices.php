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

namespace app\services\product\product;


use app\dao\product\product\StoreProductVisitDao;
use app\services\BaseServices;

/**
 * Class StoreProductVisitServices
 * @package app\services\product\product
 * @method getUserVisitProductList(array $where, int $page, int $limit) 用户浏览商品足记
 */
class StoreProductVisitServices extends BaseServices
{

    /**
     * StoreProductVisitServices constructor.
     * @param StoreProductVisitDao $dao
     */
    public function __construct(StoreProductVisitDao $dao)
    {
        $this->dao = $dao;
    }
}
