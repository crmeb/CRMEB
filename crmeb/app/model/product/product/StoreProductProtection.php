<?php

namespace app\model\product\product;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

class StoreProductProtection extends BaseModel
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
    protected $name = 'store_product_protection';
}
