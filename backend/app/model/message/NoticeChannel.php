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
 * 消息渠道全局配置模型
 *
 * 对应表 eb_notice_channel（原 eb_notice_channel_config）。
 * 存储渠道的全局配置，如图标、排序、全局开关、渠道类型等。
 * @package app\model\message
 */
class NoticeChannel extends BaseModel
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
    protected $name = 'notice_channel';

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

    /**
     * 按渠道名称模糊筛选
     * @param $query
     * @param $value
     */
    public function searchChannelNameAttr($query, $value)
    {
        if ($value !== '') {
            $query->whereLike('channel_name', "%$value%");
        }
    }

}
