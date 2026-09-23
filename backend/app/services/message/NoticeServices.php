<?php
declare(strict_types=1);

namespace app\services\message;

use app\dao\message\NoticeDao;
use app\dao\message\NoticeReadDao;
use app\model\message\Notice;
use crmeb\basic\BaseService;

/**
 * 站内信服务
 */
class NoticeServices extends BaseService
{
    protected $noticeDao;
    protected $noticeReadDao;

    public function __construct(NoticeDao $noticeDao, NoticeReadDao $noticeReadDao)
    {
        $this->noticeDao = $noticeDao;
        $this->noticeReadDao = $noticeReadDao;
    }

    public function create(array $data): array
    {
        if ($data['target_type'] === Notice::TARGET_ALL) {
            $data['total_count'] = $this->getTotalUserCount();
        } elseif ($data['target_type'] === Notice::TARGET_USER) {
            $targetIds = json_decode($data['target_ids'] ?? '[]', true) ?: [];
            $data['total_count'] = count($targetIds);
        }
        if (empty($data['send_at'])) {
            $data['send_at'] = time();
        }
        return $this->noticeDao->create($data);
    }

    public function update(int $id, array $data): bool
    {
        // 只允许更新前端可编辑字段，防止绕过 publish()/revoke() 直接改 status
        $allowedFields = [
            'type', 'title', 'content', 'image', 'link_type', 'link_url',
            'target_type', 'target_ids', 'module_key', 'extra_data',
            'send_at', 'expire_at',
        ];
        $filtered = array_intersect_key($data, array_flip($allowedFields));
        return $this->noticeDao->update($id, $filtered);
    }

    public function delete(int $id): bool
    {
        return $this->noticeDao->delete($id);
    }

    public function publish(int $id): bool
    {
        $notice = $this->noticeDao->getById($id);
        if (empty($notice)) {
            return false;
        }
        return $this->noticeDao->update($id, [
            'status' => Notice::STATUS_PUBLISHED,
            'send_at' => time(),
        ]);
    }

    public function revoke(int $id): bool
    {
        return $this->noticeDao->update($id, [
            'status' => Notice::STATUS_REVOKED,
        ]);
    }

    public function getListForAdmin(array $filter = [], int $page = 1, int $limit = 20): array
    {
        return $this->noticeDao->listForAdmin($filter, $page, $limit);
    }

    public function getListForUser(int $uid, ?string $type = null, int $page = 1, int $limit = 20): array
    {
        return $this->noticeDao->listForUser($uid, $type, $page, $limit);
    }

    public function getDetail(int $id): array
    {
        return $this->noticeDao->getById($id);
    }

    public function getUnreadCount(int $uid, ?string $type = null): int
    {
        return $this->noticeDao->getUnreadCount($uid, $type);
    }

    public function markAsRead(int $noticeId, int $uid): bool
    {
        $result = $this->noticeReadDao->markAsRead($noticeId, $uid);
        if ($result) {
            $this->noticeDao->incrementReadCount($noticeId);
        }
        return $result;
    }

    public function batchMarkAsRead(array $noticeIds, int $uid): int
    {
        $count = $this->noticeReadDao->batchMarkAsRead($noticeIds, $uid);
        if ($count > 0) {
            foreach ($noticeIds as $noticeId) {
                $this->noticeDao->incrementReadCount((int) $noticeId);
            }
        }
        return $count;
    }

    public function markAllAsRead(int $uid): bool
    {
        return $this->noticeReadDao->markAllAsRead($uid);
    }

    public function deleteForUser(int $noticeId, int $uid): bool
    {
        return $this->noticeReadDao->markAsDeleted($noticeId, $uid);
    }

    public function batchDeleteForUser(array $noticeIds, int $uid): int
    {
        return $this->noticeReadDao->batchMarkAsDeleted($noticeIds, $uid);
    }

    public function sendSystemNotice(string $title, string $content, array $options = []): array
    {
        $data = [
            'type' => $options['type'] ?? Notice::TYPE_SYSTEM,
            'title' => $title,
            'content' => $content,
            'image' => $options['image'] ?? '',
            'link_type' => $options['link_type'] ?? Notice::LINK_NONE,
            'link_url' => $options['link_url'] ?? '',
            'target_type' => $options['target_type'] ?? Notice::TARGET_ALL,
            'target_ids' => isset($options['target_ids']) ? json_encode($options['target_ids']) : null,
            'module_key' => $options['module_key'] ?? '',
            'extra_data' => isset($options['extra_data']) ? json_encode($options['extra_data']) : null,
            'status' => Notice::STATUS_PUBLISHED,
            'send_at' => $options['send_at'] ?? time(),
            'expire_at' => $options['expire_at'] ?? 0,
            'created_by' => $options['created_by'] ?? 0,
        ];
        return $this->create($data);
    }

    public function sendOrderNotice(int $uid, string $title, string $content, string $orderNo): array
    {
        return $this->sendSystemNotice($title, $content, [
            'type' => Notice::TYPE_ORDER,
            'link_type' => Notice::LINK_ORDER,
            'link_url' => $orderNo,
            'target_type' => Notice::TARGET_USER,
            'target_ids' => [$uid],
        ]);
    }

    /**
     * 为指定用户创建站内信（同时创建 notice_read 关联记录）
     * @param int    $uid     用户ID
     * @param string $title   标题
     * @param string $content 内容
     * @param string $mark    消息标识（模块 mark）
     * @param array  $extra   模板变量数据
     * @return array
     */
    public function createNoticeForUser(int $uid, string $title, string $content, string $mark, array $extra = []): array
    {
        $notice = $this->sendSystemNotice($title, $content, [
            'type'        => Notice::TYPE_USER,
            'target_type' => Notice::TARGET_USER,
            'target_ids'  => [$uid],
            'module_key'  => $mark,
            'extra_data'  => $extra,
        ]);
        // 为指定用户创建已读关系记录（getOrCreate 幂等，防止重复创建）
        // noticeDao->create 返回的 id 为 string，需转 int 以匹配 getOrCreate 签名
        $this->noticeReadDao->getOrCreate((int)$notice['id'], $uid);
        return $notice;
    }

    public function sendActivityNotice(string $title, string $content, string $activityUrl, array $targetIds = []): array
    {
        return $this->sendSystemNotice($title, $content, [
            'type' => Notice::TYPE_ACTIVITY,
            'link_type' => Notice::LINK_ACTIVITY,
            'link_url' => $activityUrl,
            'target_type' => empty($targetIds) ? Notice::TARGET_ALL : Notice::TARGET_USER,
            'target_ids' => $targetIds,
        ]);
    }

    protected function getTotalUserCount(): int
    {
        return \app\model\user\User::where('is_del', 0)->count();
    }
}
