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
use modules\product\normal\model\NormalProductSkuModel;

/**
 * 实物商品SKU表 DAO
 */
class NormalProductSkuDao extends BaseDao
{
    /**
     * 获取当前模型类
     */
    protected function setModel(): string
    {
        return NormalProductSkuModel::class;
    }

    /**
     * 根据商品ID删除所有SKU
     * @param int $productId
     * @return bool
     */
    public function deleteByProductId(int $productId): bool
    {
        return $this->delete(['product_id' => $productId]) !== false;
    }

    /**
     * 扣减指定 SKU 库存
     * @param int $skuId SKU记录ID
     * @param int $num 扣减数量
     * @return bool
     */
    public function decStock(int $skuId, int $num): bool
    {
        if ($skuId <= 0 || $num <= 0) {
            return false;
        }
        // 库存扣减必须带下限条件，避免并发下单超卖；update() 返回 0 表示库存不足或 SKU 不存在。
        return (int) $this->getModel()
            ->where('id', $skuId)
            ->where('stock', '>=', $num)
            ->dec('stock', $num)
            ->update() > 0;
    }

    /**
     * 回滚指定 SKU 库存
     * @param int $skuId SKU记录ID
     * @param int $num 回滚数量
     * @return bool
     */
    public function incStock(int $skuId, int $num): bool
    {
        if ($skuId <= 0 || $num <= 0) {
            return false;
        }
        return (int) $this->getModel()
            ->where('id', $skuId)
            ->inc('stock', $num)
            ->update() > 0;
    }

    /**
     * 根据商品ID查询已启用的 SKU 列表
     * @param int $productId 商品ID
     * @return array
     */
    public function getShowListByProductId(int $productId): array
    {
        return $this->getModel()
            ->where('product_id', $productId)
            ->where('is_show', 1)
            ->select()
            ->toArray();
    }

    /**
     * 更新 SKU 图片
     * @param int $skuId SKU记录ID
     * @param string $image 图片地址
     * @return bool
     */
    public function updateImage(int $skuId, string $image): bool
    {
        if ($skuId <= 0) {
            return false;
        }
        return $this->update($skuId, ['image' => $image]) !== false;
    }

    /**
     * 按商品ID获取 SKU 字段映射
     * @param int $productId 商品ID
     * @param string $column 字段名
     * @param string $key 作为键的字段名
     * @return array
     */
    public function getColumnByProductId(int $productId, string $column = '*', string $key = ''): array
    {
        $query = $this->getModel()->where('product_id', $productId);
        if ($key !== '') {
            return $query->column($column, $key);
        }
        return $query->column($column);
    }
}
