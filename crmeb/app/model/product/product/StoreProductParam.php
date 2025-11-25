<?php

namespace app\model\product\product;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

/**
 * 商品参数模型
 * @author wuhaotian
 * @email 442384644@qq.com
 * @date 2024/12/17
 */
class StoreProductParam extends BaseModel
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
    protected $name = 'store_product_param';
}
