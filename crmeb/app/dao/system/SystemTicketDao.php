<?php

namespace app\dao\system;

use app\dao\BaseDao;
use app\model\system\SystemTicket;

class SystemTicketDao extends BaseDao
{
    protected function setModel(): string
    {
        return SystemTicket::class;
    }

    public function getConditionModel($where)
    {
        return $this->getModel()->where('is_del', 0)
            ->when(isset($where['keyword']) && $where['keyword'] !== '', function ($query) use ($where) {
                $query->where('print_name', 'like', '%' . $where['keyword'] . '%');
            })->when(isset($where['type']) && $where['type'] != 0, function ($query) use ($where) {
                $query->where('type', $where['type']);
            })->when(isset($where['status']) && $where['status'] !== '', function ($query) use ($where) {
                $query->where('status', $where['status']);
            })->when(isset($where['print_type']) && $where['print_type'] != 0, function ($query) use ($where) {
                $query->where('print_type', $where['print_type']);
            });
    }

    public function ticketList($where, $page = 0, $limit = 0)
    {
        return $this->getConditionModel($where)->order('id desc')->when($page != 0, function ($query) use ($page, $limit) {
            $query->page($page, $limit);
        })->select()->toArray();
    }

    public function ticketCount($where)
    {
        return $this->getConditionModel($where)->count();
    }
}