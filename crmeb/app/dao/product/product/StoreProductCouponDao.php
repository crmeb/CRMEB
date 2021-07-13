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

namespace app\dao\product\product;

use app\dao\BaseDao;
use app\model\product\product\StoreProductCoupon;

/**
 *
 * Class StoreProductCouponDao
 * @package app\dao\coupon
 */
class StoreProductCouponDao extends BaseDao
{

    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return StoreProductCoupon::class;
    }

    /**
     * 获取商品关联优惠卷
     * @param array $product_ids
     * @param string $field
     * @return int|void
     */
    public function getProductCoupon(array $product_ids, string $field = '*')
    {
        return $this->search(['product_id' => $product_ids])->field($field)->select()->toArray();
    }

}
