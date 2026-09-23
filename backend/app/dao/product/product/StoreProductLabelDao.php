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
use app\model\product\product\StoreProductLabel;

class StoreProductLabelDao extends BaseDao
{
    protected function setModel(): string
    {
        return StoreProductLabel::class;
    }

    public function conditionSearch($where)
    {
        return $this->getModel()
            ->when(isset($where['name']) && $where['name'] !== '', function ($query) use ($where) {
                $query->whereLike('name', '%' . $where['name'] . '%');
            })->when(!empty($where['cate_id']), function ($query) use ($where) {
                $query->where('cate_id', $where['cate_id']);
            })->when(isset($where['is_show']) && $where['is_show'] !== '' && $where['is_show'] !== null, function ($query) use ($where) {
                $query->where('is_show', $where['is_show']);
            })->when(isset($where['status']) && $where['status'] !== '' && $where['status'] !== null, function ($query) use ($where) {
                $query->where('status', $where['status']);
            })->when(isset($where['is_del']) && $where['is_del'] !== '' && $where['is_del'] !== null, function ($query) use ($where) {
                $query->where('is_del', $where['is_del']);
            });
    }

    public function getLabelList(array $where = [], string $field = '*', int $page = 0, int $limit = 0)
    {
        return $this->conditionSearch($where)->field($field)
            ->when($page && $limit, function ($query) use ($page, $limit) {
                $query->page($page, $limit);
            })->order('sort DESC')->select()->toArray();
    }

    public function getLabelCount(array $where = [])
    {
        return $this->conditionSearch($where)->count();
    }

    public function labelUseList(bool $is_show = true)
    {
        if ($is_show) {
            $list = $this->getModel()->where('is_show', 1)->where('status', 1)->where('is_del', 0)->select()->toArray();
        } else {
            $list = $this->getModel()->where('status', 1)->where('is_del', 0)->select()->toArray();
        }
        $arr = [];
        foreach ($list as $item) {
            $arr[$item['cate_id']][] = $item;
        }
        return $arr;
    }

    public function labelListByIds($ids)
    {
        return $this->getModel()->whereIn('id', $ids)->where('status', 1)->where('is_show', 1)->select()->toArray();
    }
}
