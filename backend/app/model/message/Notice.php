<?php
declare(strict_types=1);

namespace app\model\message;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

/**
 * 站内信模型
 * 对应表：eb_notice
 */
class Notice extends BaseModel
{
    use ModelTrait;

    protected $name = 'notice';
    protected $pk = 'id';

    const TYPE_SYSTEM = 'system';
    const TYPE_ORDER = 'order';
    const TYPE_ACTIVITY = 'activity';
    const TYPE_USER = 'user';

    const STATUS_DRAFT = 0;
    const STATUS_PUBLISHED = 1;
    const STATUS_REVOKED = 2;

    const TARGET_ALL = 'all';
    const TARGET_USER = 'user';
    const TARGET_LEVEL = 'level';

    const LINK_NONE = 'none';
    const LINK_PAGE = 'page';
    const LINK_WEBVIEW = 'webview';
    const LINK_ORDER = 'order';
    const LINK_ACTIVITY = 'activity';

    public static array $typeMap = [
        self::TYPE_SYSTEM => '系统通知',
        self::TYPE_ORDER => '订单通知',
        self::TYPE_ACTIVITY => '活动通知',
        self::TYPE_USER => '用户消息',
    ];

    public static array $statusMap = [
        self::STATUS_DRAFT => '草稿',
        self::STATUS_PUBLISHED => '已发布',
        self::STATUS_REVOKED => '已撤回',
    ];

    public static array $linkTypeMap = [
        self::LINK_NONE => '无链接',
        self::LINK_PAGE => '页面跳转',
        self::LINK_WEBVIEW => '网页链接',
        self::LINK_ORDER => '订单详情',
        self::LINK_ACTIVITY => '活动页面',
    ];

    public function getTypeTextAttr(): string
    {
        return static::$typeMap[$this->type] ?? '未知类型';
    }

    public function getStatusTextAttr(): string
    {
        return static::$statusMap[$this->status] ?? '未知状态';
    }

    public function readRecords()
    {
        return $this->hasMany(NoticeRead::class, 'notice_id', 'id');
    }
}
