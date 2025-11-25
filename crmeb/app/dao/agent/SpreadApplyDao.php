<?php

namespace app\dao\agent;

use app\dao\BaseDao;
use app\model\agent\SpreadApply;

class SpreadApplyDao extends BaseDao
{
    protected function setModel(): string
    {
        return SpreadApply::class;
    }

    public function getConditionModel($where)
    {
        return $this->getModel()->where('is_del', 0)
            ->when($where['status'] !== '' && $where['status'] !== 'all', function ($query) use ($where) {
                $query->where('status', $where['status']);
            })->when($where['keyword'] !== '', function ($query) use ($where) {
                $query->whereLike('uid|nickname|real_name|phone', $where['keyword']);
            });
    }

    public function applyList($where, $page = 0, $limit = 0)
    {
        return $this->getConditionModel($where)->order('id desc')->when($page != 0, function ($query) use ($page, $limit) {
            $query->page($page, $limit);
        })->select()->toArray();
    }

    public function applyCount($where)
    {
        return $this->getConditionModel($where)->count();
    }
}