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
namespace app\dao\driver\printer;

use app\model\driver\printer\SystemTicket;
use crmeb\basic\BaseDao;

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
            })->when(isset($where['status']) && $where['status'] !== '' && $where['status'] !== null, function ($query) use ($where) {
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
