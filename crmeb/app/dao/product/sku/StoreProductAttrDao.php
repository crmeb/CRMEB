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
use app\model\product\sku\StoreProductAttr;

/**
 * Class StoreProductAttrDao
 * @package app\dao\product\sku
 */
class StoreProductAttrDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return StoreProductAttr::class;
    }

    /**
     * 删除sku
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
     * 保存sku
     * @param array $data
     * @return mixed|\think\Collection
     * @throws \Exception
     */
    public function saveAll(array $data)
    {
        return $this->getModel()->saveAll($data);
    }

    /**
     * 获取商品sku
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getProductAttr(array $where)
    {
        return $this->search($where)->select()->toArray();
    }
}
