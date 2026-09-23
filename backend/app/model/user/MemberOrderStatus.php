<?php
declare(strict_types=1);

namespace app\model\user;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

/**
 * 会员订单状态模型
 * 对应表 eb_member_order_status，记录会员订单的状态变更轨迹
 */
class MemberOrderStatus extends BaseModel
{
    use ModelTrait;

    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'member_order_status';

    /**
     * 新增时自动写入添加时间
     * @var array
     */
    protected $insert = ['add_time'];

    /**
     * 自动写入添加时间
     * @return int
     */
    protected function setAddTimeAttr()
    {
        return time();
    }

    /**
     * 订单ID搜索器
     * @param mixed $query
     * @param mixed $value
     */
    public function searchOrderIdAttr($query, $value)
    {
        $query->where('order_id', $value);
    }

    /**
     * 兼容旧字段名 oid 搜索器
     * @param mixed $query
     * @param mixed $value
     */
    public function searchOidAttr($query, $value)
    {
        $query->where('order_id', $value);
    }
}
