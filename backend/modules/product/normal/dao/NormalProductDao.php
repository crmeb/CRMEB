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
use modules\product\normal\model\NormalProductModel;

/**
 * 实物商品扩展表 DAO
 */
class NormalProductDao extends BaseDao
{
    /**
     * 获取当前模型类
     */
    protected function setModel(): string
    {
        return NormalProductModel::class;
    }

    /**
     * 按商品ID物理删除扩展表记录
     * @param int $productId
     * @return bool
     */
    public function deleteByProductId(int $productId): bool
    {
        // BaseDao::delete 返回受影响行数（int），严格类型下需显式转换为 bool
        return $this->delete(['product_id' => $productId]) !== false;
    }

    /**
     * 按商品ID和字段获取扩展表信息
     * @param int $productId 商品ID
     * @param array|string $field 查询字段
     * @return array|\think\Model|null
     */
    public function getByProductId(int $productId, $field = '*')
    {
        return $this->getModel()->where('product_id', $productId)->field($field)->find();
    }
}
