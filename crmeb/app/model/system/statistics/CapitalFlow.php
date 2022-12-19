<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\model\system\statistics;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

class CapitalFlow extends BaseModel
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
    protected $name = 'capital_flow';

    /**
     * 交易类型搜索器
     * @param $query
     * @param $value
     */
    public function searchTradingTypeAttr($query, $value)
    {
        if ($value) $query->where('trading_type', $value);
    }

    /**
     * 关键字搜索器
     * @param $query
     * @param $value
     */
    public function searchKeywordsAttr($query, $value)
    {
        if ($value !== '') $query->where('order_id|uid|nickname|phone', 'like', '%' . $value . '%');
    }

    /**
     * 批量id搜索器
     * @param $query
     * @param $value
     */
    public function searchIdsAttr($query, $value)
    {
        if ($value != '') $query->whereIn('id', $value);
    }
}
