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

namespace app\dao\message;

use crmeb\basic\BaseDao;
use app\model\message\NoticeLog;

class NoticeLogDao extends BaseDao
{
    protected function setModel(): string
    {
        return NoticeLog::class;
    }

    public function getList(array $where, string $field = '*', int $page = 0, int $limit = 0): array
    {
        return $this->getModel()->where($where)->field($field)
            ->when($page && $limit, function ($query) use ($page, $limit) {
                $query->page($page, $limit);
            })
            ->order('send_time desc, id desc')
            ->select()
            ->toArray();
    }

    /**
     * 统计符合条件的记录数
     * @param array $where
     * @return int
     */
    public function getCount(array $where): int
    {
        return (int)$this->getModel()->where($where)->count();
    }

    public function getStatistics(array $where): array
    {
        // 每次 count 都基于独立的查询构造器，避免 where 条件累积（原实现复用 $query，导致 failed 恒为 0）
        $total = $this->getCount($where);
        $success = $this->getCount(array_merge($where, ['status' => NoticeLog::STATUS_SUCCESS]));
        $failed = $this->getCount(array_merge($where, ['status' => NoticeLog::STATUS_FAILED]));
        return [
            'total' => $total,
            'success' => $success,
            'failed' => $failed,
            'success_rate' => $total > 0 ? round($success / $total * 100, 2) : 0,
        ];
    }

    public function createLog(array $data): int
    {
        $data['created_at'] = time();
        $result = $this->getModel()->create($data);
        return $result->id;
    }

    public function updateStatus(int $id, int $status, string $errorMsg = ''): bool
    {
        $update = [
            'status' => $status,
            'send_time' => time(),
        ];
        if ($errorMsg) {
            $update['error_msg'] = $errorMsg;
        }
        // 正确用法：where 定位主键后再 update，think\Model::update() 首参要求 array 而非主键
        return $this->getModel()->where('id', $id)->update($update) !== false;
    }

    public function deleteByTime(int $time): int
    {
        return $this->getModel()->where('created_at', '<', $time)->delete();
    }
}
