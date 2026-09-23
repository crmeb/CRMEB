<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace modules\product\normal\model;

use crmeb\basic\BaseModel;

/**
 * 实物商品SKU表 Model
 * 表：eb_pt_normal_sku
 * 实物商品独立SKU，不依赖共享的 eb_store_product_attr_value
 */
class NormalProductSkuModel extends BaseModel
{
    /**
     * 表名（不含前缀 eb_）
     */
    protected $name = 'pt_normal_sku';

    /**
     * 主键
     */
    protected $pk = 'id';
}
