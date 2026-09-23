<?php
declare(strict_types=1);

namespace app\dao\user;

use crmeb\basic\BaseDao;
use app\model\user\MemberOrderStatus;

/**
 * 会员订单状态DAO
 * 负责会员订单状态变更表的通用查询
 */
class MemberOrderStatusDao extends BaseDao
{
    protected function setModel(): string
    {
        return MemberOrderStatus::class;
    }

    /**
     * 获取状态变更列表
     * @param array $where 查询条件
     * @param string $field 查询字段
     * @param int $page 页码
     * @param int $limit 每页条数
     * @return array 状态列表
     */
    public function getList(array $where, string $field = '*', int $page = 0, int $limit = 0)
    {
        return $this->search($where)->field($field)->when($page && $limit, function ($query) use ($page, $limit) {
            $query->page($page, $limit);
        })->order('id desc')->select()->toArray();
    }
}
