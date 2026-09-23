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
 * 网盘商品扩展表 Model
 * 表：eb_pt_virtual
 * 字段：id, product_id, download_url, download_password, is_auto_send
 */
class VirtualProductModel extends BaseModel
{
    /**
     * 表名（不含前缀 eb_）
     */
    protected $name = 'pt_virtual';

    /**
     * 主键
     */
    protected $pk = 'id';
}
