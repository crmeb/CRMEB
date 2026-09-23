<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
namespace app\model\product;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;
use think\Model;

/**
 * 商品规格模板
 * 表：eb_store_product_rule
 */
class ProductRule extends BaseModel
{
    use ModelTrait;

    /**
     * 模型名称（不含前缀 eb_）
     * @var string
     */
    protected $name = 'store_product_rule';

    /**
     * 规格模板名称搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchRuleNameAttr($query, $value)
    {
        $query->where('rule_name', 'like', '%' . $value . '%');
    }
}
