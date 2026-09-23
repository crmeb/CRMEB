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
declare (strict_types=1);

namespace app\dao\message;

use crmeb\basic\BaseDao;
use app\model\message\NoticeConfig;

/**
 * 通知配置 DAO
 *
 * 对应表 eb_notice_config（原 eb_system_notification）。
 * 提供通知配置列表、按渠道模板映射等查询操作。
 * @package app\dao\message
 */
class NoticeConfigDao extends BaseDao
{
    /**
     * 仅认领仍未登记归属的通知配置。
     * @param int $id
     * @param string $owner
     * @return int
     */
    public function claimUnowned(int $id, string $owner): int
    {
        return $this->getModel()->where('id', $id)->where('owner_plugin_id', '')->update(['owner_plugin_id' => $owner]);
    }

    /**
     * 仅删除匹配归属的配置。
     * @param int $id
     * @param string $owner
     * @return int
     */
    public function deleteOwned(int $id, string $owner): int
    {
        return $this->getModel()->where('id', $id)->where('owner_plugin_id', $owner)->delete();
    }

    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return NoticeConfig::class;
    }

    /**
     * 获取列表
     * @param array $where
     * @param string $field
     * @param int $page
     * @param int $limit
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getList(array $where, string $field = '*', int $page = 0, $limit = 0)
    {
        return $this->getModel()->where($where)->field($field)->when($page && $limit, function ($query) use ($page, $limit) {
            $query->page($page, $limit);
        })->order('id asc')->select()->toArray();
    }

    /**
     * 获取已启用的渠道模板ID列表（从 eb_notice_template.extra 读取）
     * @param string $type 渠道类型（如 wechat、routine）
     * @return array
     */
    public function getTempId(string $type): array
    {
        $channelKey = 'is_' . $type;
        /** @var \app\dao\message\NoticeTemplateDao $channelDao */
        $channelDao = app()->make(\app\dao\message\NoticeTemplateDao::class);
        $channels = $channelDao->getModel()
            ->where('channel_key', $channelKey)
            ->where('status', 1)
            ->select()
            ->toArray();

        $ids = [];
        foreach ($channels as $channel) {
            $extra = !empty($channel['extra']) ? (is_string($channel['extra']) ? json_decode($channel['extra'], true) : $channel['extra']) : [];
            if (!empty($extra['tempid'])) {
                $ids[] = $extra['tempid'];
            }
        }
        return array_unique($ids);
    }

    /**
     * 获取已启用的渠道模板Key映射表（从 eb_notice_template 读取）
     * @param string $type 渠道类型（如 wechat、routine）
     * @return array ['tempkey' => ['{type}_content' => '...', 'name' => '...']]
     */
    public function getTempKey(string $type): array
    {
        $channelKey = 'is_' . $type;
        /** @var \app\dao\message\NoticeTemplateDao $channelDao */
        $channelDao = app()->make(\app\dao\message\NoticeTemplateDao::class);
        $channels = $channelDao->getModel()
            ->alias('c')
            ->join('notice_config n', 'c.notification_id = n.id')
            ->where('c.channel_key', $channelKey)
            ->field('c.extra, c.template_content, n.name,n.mark')
            ->select()
            ->toArray();

        $result = [];
        foreach ($channels as $channel) {
            $extra = !empty($channel['extra']) ? (is_string($channel['extra']) ? json_decode($channel['extra'], true) : $channel['extra']) : [];
            $tempkey = $extra['tempkey'] ?? '';
            if ($tempkey) {
                $result[$channel['mark']] = [
                    $type . '_content' => $channel['template_content'] ?? '',
                    'name' => $channel['name'] ?? '',
                    'tempkey' => $tempkey,
                ];
            }
        }
        return $result;
    }
}
