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
 * 网盘商品订单业务表 Model
 * 表：eb_pt_virtual_order
 * 字段：id, order_no, product_id, status, send_time, complete_time
 */
class VirtualOrderModel extends BaseModel
{
    /**
     * 表名（不含前缀 eb_）
     */
    protected $name = 'pt_virtual_order';

    /**
     * 主键
     */
    protected $pk = 'id';
}
