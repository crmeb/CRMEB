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
use app\dao\coupon\StoreCouponProductDao;

/**
 *
 * Class StoreCouponProductServices
 * @package app\services\coupon
 * @method saveAll(array $data) 批量保存
 */
class StoreCouponProductServices extends BaseServices
{

    /**
     * StoreCouponProductServices constructor.
     * @param StoreCouponProductDao $dao
     */
    public function __construct(StoreCouponProductDao $dao)
    {
        $this->dao = $dao;
    }

}
