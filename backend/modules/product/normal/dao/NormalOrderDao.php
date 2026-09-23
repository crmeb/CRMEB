<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace modules\product\normal\dao;

use crmeb\basic\BaseDao;
use modules\product\normal\model\NormalOrderModel;

/**
 * 实物商品订单表 DAO
 */
class NormalOrderDao extends BaseDao
{
    /**
     * 获取当前模型类
     */
    protected function setModel(): string
    {
        return NormalOrderModel::class;
    }

    /**
     * 根据筛选条件查询指定字段值列表
     *
     * @param array $filters 筛选条件，格式 [['field', 'op', 'value'], ...]
     * @param string $column 要查询的字段名
     * @return array
     */
    public function columnByFilters(array $filters, string $column): array
    {
        $query = $this->getModel()->newQuery();

        foreach ($filters as $cond) {
            $field = $cond[0] ?? '';
            $op    = $cond[1] ?? '=';
            $value = $cond[2] ?? '';

            if ($field === '') {
                continue;
            }

            // 过滤条件可能带表别名前缀，查询单表时去掉
            $field = str_replace('pt_normal_order.', '', $field);

            $query->where($field, $op, $value);
        }

        return $query->column($column);
    }
}
