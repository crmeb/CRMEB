<?php
declare(strict_types=1);

namespace app\dao\user;

use crmeb\basic\BaseDao;
use app\model\user\OfflineReceiptStatus;

/**
 * 线下收银状态 DAO
 * 负责 eb_offline_receipt_status 表的数据访问
 */
class OfflineReceiptStatusDao extends BaseDao
{
    protected function setModel(): string
    {
        return OfflineReceiptStatus::class;
    }

    public function getList(array $where, string $field = '*', int $page = 0, int $limit = 0)
    {
        return $this->search($where)->field($field)->when($page && $limit, function ($query) use ($page, $limit) {
            $query->page($page, $limit);
        })->order('id desc')->select()->toArray();
    }
}
