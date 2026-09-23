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
use modules\product\virtual\model\VirtualOrderModel;

/**
 * 网盘商品订单表 DAO
 */
class VirtualOrderDao extends BaseDao
{
    /**
     * 获取当前模型类
     */
    protected function setModel(): string
    {
        return VirtualOrderModel::class;
    }
}
