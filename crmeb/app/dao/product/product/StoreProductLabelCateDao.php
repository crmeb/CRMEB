<?php

namespace app\dao\product\product;

use app\dao\BaseDao;
use app\model\product\product\StoreProductLabelCate;

class StoreProductLabelCateDao extends BaseDao
{
    protected function setModel(): string
    {
        return StoreProductLabelCate::class;
    }

    public function conditionSearch($where)
    {
        return $this->getModel()
            ->when(isset($where['name']) && $where['name'] !== '', function ($query) use ($where) {
                $query->whereLike('name', '%' . $where['name'] . '%');
            })->when(isset($where['is_del']) && $where['is_del'] !== '', function ($query) use ($where) {
                $query->where('is_del', $where['is_del']);
            });
    }

    public function getLabelCateList(array $where = [], string $field = '*', int $page = 0, int $limit = 0)
    {
        return $this->conditionSearch($where)->field($field)
            ->when($page && $limit, function ($query) use ($page, $limit) {
                $query->page($page, $limit);
            })->order('sort DESC')->select()->toArray();
    }

    public function getLabelCateCount(array $where = [])
    {
        return $this->conditionSearch($where)->count();
    }

    public function labelCateArr()
    {
        return $this->getModel()->where('is_del', 0)->column('name', 'id');
    }
}
