<?php

namespace app\dao\message;

use crmeb\basic\BaseDao;
use app\model\message\NoticeTemplate;

/**
 * 消息模板配置 DAO
 *
 * 对应表 eb_notice_template（原 eb_notification_channel）。
 * 提供通知模板按通知/渠道查询、开关更新、extra 合并更新等操作。
 */
class NoticeTemplateDao extends BaseDao
{
    /**
     * 按所属通知删除渠道模板（不触及发送/已读历史）。
     * @param int $notificationId
     * @return int
     */
    public function deleteByNotificationId(int $notificationId): int
    {
        return $this->getModel()->where('notification_id', $notificationId)->delete();
    }

    /**
     * 删除指定通知下的单个渠道模板行（不触及发送/已读历史）。
     * @param int    $notificationId 通知ID
     * @param string $channelKey     渠道标识
     * @return int
     */
    public function deleteByNotificationAndChannel(int $notificationId, string $channelKey): int
    {
        return $this->getModel()
            ->where('notification_id', $notificationId)
            ->where('channel_key', $channelKey)
            ->delete();
    }

    /**
     * 查询渠道是否被其他通知模板引用。
     * @param string $channelKey
     * @return int
     */
    public function countByChannelKey(string $channelKey): int
    {
        return $this->getModel()->where('channel_key', $channelKey)->count();
    }
    /**
     * 查询已开启的小程序订阅模板及通知标识。
     * @return array
     */
    public function getEnabledRoutineTemplates(): array
    {
        $noticeTable = app()->make(\app\model\message\NoticeConfig::class)->getTable();
        return $this->getModel()->alias('c')
            ->join($noticeTable . ' n', 'c.notification_id = n.id')
            ->where('c.channel_key', 'is_routine')
            ->where('c.status', 1)
            ->field('n.mark, c.extra')->select()->toArray();
    }

    /**
     * 绑定模型
     * @return string
     */
    protected function setModel(): string
    {
        return NoticeTemplate::class;
    }

    /**
     * 按通知ID获取全部渠道模板
     * @param int $notificationId 通知ID
     * @return array
     */
    public function getByNotificationId($notificationId)
    {
        return $this->getModel()->where('notification_id', $notificationId)->select()->toArray();
    }

    /**
     * 按通知ID集合批量获取渠道模板
     * @param array $notificationIds 通知ID列表
     * @return array
     */
    public function getByNotificationIds(array $notificationIds)
    {
        return $this->getModel()->whereIn('notification_id', $notificationIds)->select()->toArray();
    }

    /**
     * 按渠道标识获取全部模板
     * @param string $channelKey 渠道标识
     * @return array
     */
    public function getByChannelKey($channelKey)
    {
        return $this->getModel()->where('channel_key', $channelKey)->select()->toArray();
    }

    /**
     * 获取指定通知 + 渠道的模板记录
     * @param int    $notificationId 通知ID
     * @param string $channelKey     渠道标识
     * @return \think\Model|null
     */
    public function getByNotificationAndChannel($notificationId, $channelKey)
    {
        return $this->getModel()->where('notification_id', $notificationId)->where('channel_key', $channelKey)->find();
    }

    /**
     * 更新渠道开关状态，记录不存在时自动创建
     * @param int    $notificationId 通知ID
     * @param string $channelKey     渠道标识
     * @param int    $status         状态（1开启/2关闭）
     * @return bool
     */
    public function updateStatus($notificationId, $channelKey, $status)
    {
        $existing = $this->getModel()
            ->where('notification_id', $notificationId)
            ->where('channel_key', $channelKey)
            ->find();

        if ($existing) {
            return (bool) $this->getModel()->where('id', $existing->id)
                ->update(['status' => $status, 'updated_at' => time()]);
        }

        // 首次开启时自动创建渠道模板记录
        return (bool) $this->getModel()->create([
            'notification_id' => $notificationId,
            'channel_key'     => $channelKey,
            'status'          => $status,
            'template_title'  => '',
            'template_content'=> '',
            'extra'           => '',
            'created_at'      => time(),
            'updated_at'      => time(),
        ]);
    }

    /**
     * 批量保存模板记录
     * @param array $data 模板记录列表
     * @return bool
     */
    public function batchSave(array $data)
    {
        return $this->getModel()->saveAll($data);
    }

    /**
     * 根据 mark 和 channel_key 更新 extra JSON 字段
     * @param string $mark       消息标识
     * @param string $channelKey 渠道标识（is_wechat / is_routine）
     * @param array  $mergeExtra 要合并到 extra 的键值对
     * @return bool
     */
    public function updateExtraByMarkAndChannel(string $mark, string $channelKey, array $mergeExtra): bool
    {
        $channel = $this->getModel()
            ->alias('c')
            ->join('notice_config n', 'c.notification_id = n.id')
            ->where('c.channel_key', $channelKey)
            ->where('n.mark', $mark)
            ->field('c.id, c.extra')
            ->find();

        if (!$channel) {
            return false;
        }

        $extra = !empty($channel['extra'])
            ? (is_string($channel['extra']) ? json_decode($channel['extra'], true) : $channel['extra'])
            : [];
        $extra = array_merge($extra, $mergeExtra);

        return (bool)$this->getModel()
            ->where('id', $channel['id'])
            ->update(['extra' => json_encode($extra), 'updated_at' => time()]);
    }
}
