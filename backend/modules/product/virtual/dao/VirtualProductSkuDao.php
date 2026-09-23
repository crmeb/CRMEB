<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace modules\product\virtual\dao;

use crmeb\basic\BaseDao;
use modules\product\virtual\model\VirtualProductSkuModel;

/**
 * 网盘商品SKU表 DAO
 */
class VirtualProductSkuDao extends BaseDao
{
    /** 仅在库存足够时扣减库存并增加销量。 */
    public function decStockSafely(int $id, int $num): bool
    {
        return (bool)$this->getModel()->where('id', $id)->where('stock', '>=', $num)->dec('stock', $num)->inc('sales', $num)->update();
    }
    /**
     * 获取当前模型类
     */
    protected function setModel(): string
    {
        return VirtualProductSkuModel::class;
    }
}
