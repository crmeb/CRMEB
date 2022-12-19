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

namespace app\dao\product\product;

use app\dao\BaseDao;
use app\model\product\product\StoreProductCate;

/**
 * Class StoreProductCateDao
 * @package app\dao\product\product
 */
class StoreProductCateDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return StoreProductCate::class;
    }

    /**
     * 保存数据
     * @param array $data
     * @return mixed|void
     */
    public function saveAll(array $data)
    {
        $this->getModel()->insertAll($data);
    }

    /**
     * 根据商品id获取分类id
     * @param array $productId
     * @return array
     */
    public function productIdByCateId(array $productId)
    {
        return $this->getModel()->whereIn('product_id', $productId)->column('cate_id');
    }

    /**
     * 根据分类获取商品id
     * @param array $cate_id
     * @return array
     */
    public function cateIdByProduct(array $cate_id)
    {
        return $this->getModel()->whereIn('cate_id', $cate_id)->column('product_id');
    }
}
