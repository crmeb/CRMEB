<?php
declare(strict_types=1);

namespace app\dao\order;

use app\model\order\AfterSaleItem;
use crmeb\basic\BaseDao;

/**
 * 售后商品规范化明细查询。
 */
class AfterSaleItemDao extends BaseDao
{
    /**
     * 批量读取有效售后单的商品数量，避免用单头总数量推算。
     * @param array $ids 售后单ID
     * @return array
     */
    public function getQuantitiesByAfterSaleIds(array $ids): array
    {
        if (!$ids) {
            return [];
        }
        return $this->getModel()->whereIn('after_sale_id', $ids)
            ->field('after_sale_id, cart_id, refund_num, refund_integral')->select()->toArray();
    }

    /**
     * 设置明细模型。
     * @return string
     */
    protected function setModel(): string
    {
        return AfterSaleItem::class;
    }

    /**
     * 获取售后商品及冻结退款数据。
     * @param int $afterSaleId 售后单ID
     * @return array
     */
    public function getByAfterSaleId(int $afterSaleId): array
    {
        return $this->getModel()->where('after_sale_id', $afterSaleId)->order('id asc')->select()->toArray();
    }
}
