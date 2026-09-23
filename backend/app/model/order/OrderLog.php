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

namespace app\model\order;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;
use think\Model;

/**
 * 订单状态日志Model
 * 记录订单每一步状态变更的操作记录，替代旧版 eb_store_order_status
 * Class OrderLog
 * @package app\model\order
 */
class OrderLog extends BaseModel
{
    use ModelTrait;

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'order_log';

    /**
     * 手动管理 add_time，禁止模型自动时间戳覆盖
     * 避免 getDateTime() 在字段类型判断异常时回退写入 datetime 字符串导致 int 列只存下年份
     * @var bool
     */
    protected $autoWriteTimestamp = false;

    /**
     * 创建时间字段
     * @var bool
     */
    protected $createTime = false;

    /**
     * 订单号搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchOrderNoAttr($query, $value, $data)
    {
        $query->where('order_no', $value);
    }

    /**
     * 变更后状态搜索器
     * @param Model $query
     * @param $value
     */
    public function searchToStatusAttr($query, $value)
    {
        $query->where('to_status', $value);
    }
}
