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
            })->when(isset($where['is_del']) && $where['is_del'] !== '' && $where['is_del'] !== null, function ($query) use ($where) {
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
