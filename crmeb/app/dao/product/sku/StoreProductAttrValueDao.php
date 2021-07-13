<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\dao\product\sku;

use app\dao\BaseDao;
use app\model\product\sku\StoreProductAttrValue;

/**
 * Class StoreProductAttrValueDao
 * @package app\dao\product\sku
 */
class StoreProductAttrValueDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return StoreProductAttrValue::class;
    }

    /**
     * 根据条件获取规格value
     * @param array $where
     * @param string $field
     * @param string $key
     * @return array
     */
    public function getColumn(array $where, string $field = '*', string $key = 'suk')
    {
        return $this->search($where)->column($field, $key);
    }

    /**
     * 根据条件删除规格value
     * @param int $id
     * @param int $type
     * @return bool
     * @throws \Exception
     */
    public function del(int $id, int $type)
    {
        return $this->search(['product_id' => $id, 'type' => $type])->delete();
    }

    /**
     * 保存数据
     * @param array $data
     * @return mixed|\think\Collection
     * @throws \Exception
     */
    public function saveAll(array $data)
    {
        return $this->getModel()->saveAll($data);
    }

    /**
     * 根据条件获取规格数据列表
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getProductAttrValue(array $where)
    {
        return $this->search($where)->select()->toArray();
    }

    /**获取属性列表
     * @return mixed
     */
    public function attrValue()
    {
        return $this->search()->field('product_id,sum(sales * price) as val')->with(['product'])->group('product_id')->limit(20)->select()->toArray();
    }

    /**获取属性库存
     * @param string $unique
     * @return int
     */
    public function uniqueByStock(string $unique)
    {
        return $this->search(['unique' => $unique])->value('stock') ?: 0;
    }

    /**
     * 减库存加销量减限购
     * @param array $where
     * @param int $num
     * @return mixed
     */
    public function decStockIncSalesDecQuota(array $where, int $num)
    {
        return $this->getModel()->where($where)->dec('stock', $num)->dec('quota', $num)->inc('sales', $num)->update();
    }
}
