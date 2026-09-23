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
use app\model\message\NoticeChannel;

/**
 * 消息渠道全局配置 DAO
 *
 * 对应表 eb_notice_channel（原 eb_notice_channel_config）。
 * 提供渠道配置的分页查询、按渠道标识查询、启用渠道列表等操作。
 * @package app\dao\message
 */
class NoticeChannelDao extends BaseDao
{
    /**
     * 仅认领仍未登记归属的渠道定义。
     * @param int $id
     * @param string $owner
     * @return int
     */
    public function claimUnowned(int $id, string $owner): int
    {
        return $this->getModel()->where('id', $id)->where('owner_plugin_id', '')->update(['owner_plugin_id' => $owner]);
    }

    /**
     * 仅删除匹配归属的渠道定义。
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
        return NoticeChannel::class;
    }

    /**
     * 获取列表
     * @param array $where
     * @param string $field
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getList(array $where, string $field = '*', int $page = 0, int $limit = 0): array
    {
        return $this->getModel()->where($where)->field($field)
            ->when($page && $limit, function ($query) use ($page, $limit) {
                $query->page($page, $limit);
            })
            ->order('sort asc, id asc')
            ->select()
            ->toArray();
    }

    /**
     * 按渠道标识查询
     * @param string $channelKey
     * @return array|null
     */
    public function getByChannelKey(string $channelKey): ?array
    {
        $result = $this->getModel()->where('channel_key', $channelKey)->find();
        return $result ? $result->toArray() : null;
    }

    /**
     * 获取所有已启用渠道
     * @return array
     */
    public function getAllEnabled(): array
    {
        return $this->getModel()->where('status', 1)->order('sort asc')->select()->toArray();
    }
}
