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
 * 实物商品扩展表 Model
 * 表：eb_pt_normal
 * 仅保留实物专属字段（weight/volume/has_installation/command_word/warranty_days），
 * 通用字段已迁移至 eb_product_center。
 */
class NormalProductModel extends BaseModel
{
    /**
     * 表名（不含前缀 eb_）
     */
    protected $name = 'pt_normal';

    /**
     * 主键
     */
    protected $pk = 'id';
}
