<?php
declare(strict_types=1);

namespace app\dao\message;

use app\model\message\NoticeRead;
use crmeb\basic\BaseDao;

class NoticeReadDao extends BaseDao
{
    /**
     * 获取当前模型
     * @return string
     */
    protected function setModel(): string
    {
        return NoticeRead::class;
    }

    public function getOrCreate(int $noticeId, int $uid): array
    {
        $record = $this->getModel()
            ->where('notice_id', $noticeId)
            ->where('uid', $uid)
            ->findOrEmpty();
        if (!$record->id) {
            $record = $this->getModel()->create([
                'notice_id' => $noticeId,
                'uid' => $uid,
                'is_read' => 0,
                'is_deleted' => 0,
                'created_at' => time(),
            ]);
        }
        return $record->toArray();
    }

    public function markAsRead(int $noticeId, int $uid): bool
    {
        $record = $this->getModel()
            ->where('notice_id', $noticeId)
            ->where('uid', $uid)
            ->findOrEmpty();
        if (!$record->id) {
            $this->getModel()->create([
                'notice_id' => $noticeId,
                'uid' => $uid,
                'is_read' => 1,
                'read_at' => time(),
                'created_at' => time(),
            ]);
            return true;
        }
        if ($record->is_read) {
            return true;
        }
        return (bool) $record->markAsRead();
    }

    public function batchMarkAsRead(array $noticeIds, int $uid): int
    {
        $count = 0;
        foreach ($noticeIds as $noticeId) {
            if ($this->markAsRead((int) $noticeId, $uid)) {
                $count++;
            }
        }
        return $count;
    }

    public function markAsDeleted(int $noticeId, int $uid): bool
    {
        $record = $this->getOrCreate($noticeId, $uid);
        return (bool) $this->getModel()->where('id', $record['id'])->update(['is_deleted' => 1]);
    }

    public function batchMarkAsDeleted(array $noticeIds, int $uid): int
    {
        return $this->getModel()
            ->whereIn('notice_id', $noticeIds)
            ->where('uid', $uid)
            ->update(['is_deleted' => 1]);
    }

    public function markAllAsRead(int $uid, ?string $type = null): bool
    {
        $query = $this->getModel()->where('uid', $uid)->where('is_read', 0);
        return (bool) $query->update(['is_read' => 1, 'read_at' => time()]);
    }
}
