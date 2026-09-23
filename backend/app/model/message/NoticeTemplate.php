<?php

namespace app\model\message;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

/**
 * 消息模板配置模型
 *
 * 每个通知场景（notification_id）× 每个渠道（channel_key）一行模板配置记录，
 * 存储渠道消息标题、内容模板及渠道特有配置（extra JSON）。
 * 原名 NotificationChannel，v8.1.0 起更名以明确其"模板配置"职责，避免与渠道配置表混淆。
 */
class NoticeTemplate extends BaseModel
{
    use ModelTrait;

    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 数据表名
     * @var string
     */
    protected $name = 'notice_template';

    /**
     * 按通知ID筛选
     * @param $query
     * @param $value
     */
    public function searchNotificationIdAttr($query, $value)
    {
        if ($value !== '' && $value !== null) {
            $query->where('notification_id', $value);
        }
    }

    /**
     * 按渠道标识筛选
     * @param $query
     * @param $value
     */
    public function searchChannelKeyAttr($query, $value)
    {
        if ($value !== '') {
            $query->where('channel_key', $value);
        }
    }

    /**
     * 按状态筛选
     * @param $query
     * @param $value
     */
    public function searchStatusAttr($query, $value)
    {
        if ($value !== '' && $value !== null) {
            $query->where('status', $value);
        }
    }
}
