<?php
declare(strict_types=1);

namespace app\dao\message;

use app\model\message\Notice;
use crmeb\basic\BaseDao;

class NoticeDao extends BaseDao
{
    /**
     * 获取当前模型
     * @return string
     */
    protected function setModel(): string
    {
        return Notice::class;
    }

    public function getById(int $id): array
    {
        return $this->getModel()->where('is_del', 0)->findOrEmpty($id)->toArray();
    }

    public function create(array $data): array
    {
        $data['created_at'] = time();
        $data['updated_at'] = time();
        $notice = $this->getModel()->create($data);
        return $notice->toArray();
    }

    public function update($id, array $data, ?string $key = null)
    {
        $data['updated_at'] = time();
        return (bool) $this->getModel()->where('id', $id)->where('is_del', 0)->update($data);
    }

    public function delete($id, ?string $key = null)
    {
        return $this->update($id, ['is_del' => 1]);
    }

    public function listForAdmin(array $filter = [], int $page = 1, int $limit = 20): array
    {
        $query = $this->getModel()->where('is_del', 0);
        if (!empty($filter['type'])) {
            $query->where('type', $filter['type']);
        }
        if (isset($filter['status']) && $filter['status'] !== '') {
            $query->where('status', $filter['status']);
        }
        if (!empty($filter['keyword'])) {
            $query->where('title', 'like', "%{$filter['keyword']}%");
        }
        $total = $query->count();
        $list = $query->order('created_at', 'desc')
            ->page($page, $limit)
            ->select()
            ->toArray();
        return ['list' => $list, 'total' => $total];
    }

    /**
     * 用户站内信列表
     * @param int $uid 用户ID
     * @param string|null $type 消息类型
     * @param int $page 页码
     * @param int $limit 每页数量
     * @return array
     */
    public function listForUser(int $uid, ?string $type = null, int $page = 1, int $limit = 20): array
    {
        $now = time();
        $uidJson = json_encode($uid);
        $query = $this->getModel()
            ->alias('n')
            ->leftJoin('notice_read r', 'n.id = r.notice_id AND r.uid = ' . $uid)
            ->where('n.is_del', 0)
            ->where('n.status', Notice::STATUS_PUBLISHED)
            ->where('n.send_at', '<=', $now)
            ->where(function ($q) use ($now) {
                $q->where('n.expire_at', 0)->whereOr('n.expire_at', '>', $now);
            })
            ->where(function ($q) use ($uid, $uidJson) {
                $q->where('n.target_type', Notice::TARGET_ALL)
                    ->whereOr(function ($q2) use ($uid, $uidJson) {
                        $q2->where('n.target_type', Notice::TARGET_USER)
                            ->whereRaw("JSON_CONTAINS(n.target_ids, ?, '$')", [$uidJson]);
                    });
            })
            ->where('(r.is_deleted IS NULL OR r.is_deleted = 0)');
        if ($type) {
            $query->where('n.type', $type);
        }
        $total = $query->count();
        $list = $query->field('n.*, r.is_read, r.read_at')
            ->order('n.send_at', 'desc')
            ->page($page, $limit)
            ->select()
            ->toArray();
        return ['list' => $list, 'total' => $total];
    }

    /**
     * 用户未读站内信数量
     * @param int $uid 用户ID
     * @param string|null $type 消息类型
     * @return int
     */
    public function getUnreadCount(int $uid, ?string $type = null): int
    {
        $now = time();
        $uidJson = json_encode($uid);
        $query = $this->getModel()
            ->alias('n')
            ->leftJoin('notice_read r', 'n.id = r.notice_id AND r.uid = ' . $uid)
            ->where('n.is_del', 0)
            ->where('n.status', Notice::STATUS_PUBLISHED)
            ->where('n.send_at', '<=', $now)
            ->where(function ($q) use ($now) {
                $q->where('n.expire_at', 0)->whereOr('n.expire_at', '>', $now);
            })
            ->where(function ($q) use ($uid, $uidJson) {
                $q->where('n.target_type', Notice::TARGET_ALL)
                    ->whereOr(function ($q2) use ($uid, $uidJson) {
                        $q2->where('n.target_type', Notice::TARGET_USER)
                            ->whereRaw("JSON_CONTAINS(n.target_ids, ?, '$')", [$uidJson]);
                    });
            })
            ->where('(r.is_read IS NULL OR r.is_read = 0)')
            ->where('(r.is_deleted IS NULL OR r.is_deleted = 0)');
        if ($type) {
            $query->where('n.type', $type);
        }
        return $query->count();
    }

    public function incrementReadCount(int $id): bool
    {
        return (bool) $this->getModel()->where('id', $id)->inc('read_count')->update();
    }
}
