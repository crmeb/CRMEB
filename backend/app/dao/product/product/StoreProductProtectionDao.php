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
use app\model\product\product\StoreProductProtection;

class StoreProductProtectionDao extends BaseDao
{
    protected function setModel(): string
    {
        return StoreProductProtection::class;
    }

    public function conditionSearch($where)
    {
        return $this->getModel()
            ->when(isset($where['title']) && $where['title'] !== '', function ($query) use ($where) {
                $query->whereLike('title', '%' . $where['title'] . '%');
            })->when(isset($where['is_del']) && $where['is_del'] !== '' && $where['is_del'] !== null, function ($query) use ($where) {
                $query->where('is_del', $where['is_del']);
            })->when(isset($where['status']) && $where['status'] !== '' && $where['status'] !== null, function ($query) use ($where) {
                $query->where('status', $where['status']);
            });
    }

    public function protectionList(array $where = [], string $field = '*', int $page = 0, int $limit = 0)
    {
        return $this->conditionSearch($where)->field($field)
            ->when($page && $limit, function ($query) use ($page, $limit) {
                $query->page($page, $limit);
            })->order('sort DESC')->select()->toArray();
    }

    public function protectionCount(array $where = [])
    {
        return $this->conditionSearch($where)->count();
    }

    public function protectionListByIds($ids)
    {
        return $this->getModel()->whereIn('id', $ids)->select()->toArray();
    }
}
