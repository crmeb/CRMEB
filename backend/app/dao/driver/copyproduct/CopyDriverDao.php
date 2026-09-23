<?php

namespace app\dao\driver\copyproduct;

use crmeb\basic\BaseDao;
use app\model\driver\copyproduct\CopyDriver;

class CopyDriverDao extends BaseDao
{
    protected function setModel(): string
    {
        return CopyDriver::class;
    }

    public function getList(array $where = [], array $field = ['*'], int $page = 0, int $limit = 0, $sort = null, array $with = [])
    {
        return $this->search($where)->field($field)->when($page && $limit, function ($query) use ($page, $limit) {
            $query->page($page, $limit);
        })->when($sort, function ($query) use ($sort) {
            if (is_array($sort)) {
                foreach ($sort as $v => $k) {
                    if (is_numeric($v)) {
                        $query->order($k, 'desc');
                    } else {
                        $query->order($v, $k);
                    }
                }
            } else {
                $query->order($sort);
            }
        })->with($with)->select()->toArray();
    }

    public function getByDriverKey(string $driverKey)
    {
        return $this->search(['driver_key' => $driverKey])->find();
    }

    public function getEnabledDrivers()
    {
        return $this->search(['status' => 1])->order('sort', 'desc')->select()->toArray();
    }

    public function disableAllExcept(int $exceptId)
    {
        return $this->getModel()->where('id', '<>', $exceptId)->update(['status' => 0, 'update_time' => time()]);
    }
}
