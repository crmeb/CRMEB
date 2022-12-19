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

namespace app\model\product\product;

use crmeb\traits\ModelTrait;
use think\Model;

/**
 *  商品分类关联Model
 * Class StoreProductCate
 * @package app\model\product\product
 */
class StoreProductCate extends Model
{
    use ModelTrait;

    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'store_product_cate';

    /**
     * 一对一关联获取分类名称
     * @return \think\model\relation\HasOne
     */
    public function cateName()
    {
        return $this->hasOne(StoreCategory::class, 'id', 'cate_id')->bind([
            'cate_name' => 'cate_name'
        ]);
    }

}
