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

namespace app\model\message;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

/**
 * 通知配置主表模型
 *
 * 每个消息标识（mark）一行配置记录，存储通知名称、场景说明、可用变量等。
 * 渠道模板配置见 NoticeTemplate（eb_notice_template），渠道全局配置见 NoticeChannel（eb_notice_channel）。
 * 原名 SystemNotification（eb_system_notification），v8.1.0 起更名以统一消息体系命名。
 * @package app\model\message
 */
class NoticeConfig extends BaseModel
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
    protected $name = 'notice_config';

    /**
     * 按类型筛选
     * @param $query
     * @param $value
     */
    public function searchTypeAttr($query, $value)
    {
        if ($value !== '' && $value !== null) {
            $query->where('type', $value);
        }
    }

    /**
     * 按名称/标题模糊筛选
     * @param $query
     * @param $value
     */
    public function searchNameAttr($query, $value)
    {
        if ($value !== '') {
            $query->whereLike('name|title', "%$value%");
        }
    }
}
