<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace modules\product\virtual\model;

use crmeb\basic\BaseModel;

/**
 * 网盘商品SKU表 Model
 * 表：eb_pt_virtual_sku
 * 网盘商品独立SKU，不依赖共享的 eb_store_product_attr_value
 */
class VirtualProductSkuModel extends BaseModel
{
    /**
     * 表名（不含前缀 eb_）
     */
    protected $name = 'pt_virtual_sku';

    /**
     * 主键
     */
    protected $pk = 'id';
}
