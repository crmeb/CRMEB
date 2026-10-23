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

namespace app\dao\product\product;

use crmeb\basic\BaseDao;
use app\model\product\product\StoreProductRelation;

/**
 * Class StoreProductRelationDao
 * @package app\dao\product\product
 */
class StoreProductRelationDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return StoreProductRelation::class;
    }

    /**
     * 批量插入，绕过模型 saveAll 语义
     * 该表使用 (uid, product_id, type, category) 联合主键，saveAll() 会因主键字段齐全
     * 而按 UPDATE 处理，导致新记录静默写入失败
     * @param array $data
     * @return int
     */
    public function insertAll(array $data): int
    {
        return $this->getModel()->insertAll($data);
    }

    /**
     * 获取收藏列表
     * @param array $where
     * @param string $field
     * @param int $page
     * @param int $limit
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getList(array $where, string $field, int $page, int $limit)
    {
        return $this->search($where)->field($field)->with(['product'])->page($page, $limit)->select()->toArray();
    }
}
