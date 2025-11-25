<?php

namespace app\dao\activity;

use app\dao\BaseDao;
use app\model\activity\StoreActivity;

class StoreActivityDao extends BaseDao
{
    protected function setModel(): string
    {
        return StoreActivity::class;
    }

    public function conditionSearch($where)
    {
        return $this->getModel()
            ->when(isset($where['title']) && $where['title'] !== '', function ($query) use ($where) {
                $query->whereLike('title', '%' . $where['title'] . '%');
            })->when(isset($where['is_del']) && $where['is_del'] !== '', function ($query) use ($where) {
                $query->where('is_del', $where['is_del']);
            })->when(isset($where['status']) && $where['status'] !== '', function ($query) use ($where) {
                $query->where('status', $where['status']);
            })->when(isset($where['type']) && $where['type'] !== '', function ($query) use ($where) {
                $query->where('type', $where['type']);
            })->when(isset($where['time_ids']) && count($where['time_ids']) > 0, function ($query) use ($where) {
                $query->where(function ($query) use ($where) {
                    foreach ($where['time_ids'] as $value) {
                        $query->whereOr('FIND_IN_SET(:value, time_ids)', ['value' => $value]);
                    }
                });
            })->when(isset($where['time']) && $where['time'] !== '', function ($query) use ($where) {
                $time = explode('-', $where['time']);
                $query->where('start_day', '<=', strtotime($time[1]))->where('end_day', '>=', strtotime($time[0]));
            });
    }

    public function activityList(array $where = [], string $field = '*', int $page = 0, int $limit = 0, $with = [])
    {
        return $this->conditionSearch($where)->field($field)
            ->when($page && $limit, function ($query) use ($page, $limit) {
                $query->page($page, $limit);
            })->when(count($with), function ($query) use ($with) {
                $query->with($with);
            })->order('add_time DESC')->select()->toArray();
    }

    public function activityCount(array $where = [])
    {
        return $this->conditionSearch($where)->count();
    }
}
