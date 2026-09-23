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

namespace app\model\driver\express;

use crmeb\basic\BaseModel;

/**
 * 电子面单打印记录
 * Class ExpressDumpLog
 * @package app\model\driver\express
 */
class ExpressDumpLog extends BaseModel
{
    /**
     * @var string
     */
    protected $name = 'express_dump_log';

    /**
     * @var string
     */
    protected $pk = 'id';

    /**
     * @var bool
     */
    protected $autoWriteTimestamp = false;

    /**
     * 订单ID搜索器
     * @param $query
     * @param $value
     */
    public function searchOrderIdAttr($query, $value)
    {
        if ($value) $query->where('order_id', $value);
    }

    /**
     * 快递公司编码搜索器
     * @param $query
     * @param $value
     */
    public function searchComAttr($query, $value)
    {
        if ($value) $query->where('com', $value);
    }

    /**
     * 快递单号搜索器
     * @param $query
     * @param $value
     */
    public function searchKuaidinumAttr($query, $value)
    {
        if ($value) $query->where('kuaidinum', 'like', "%{$value}%");
    }

    /**
     * 订单编号搜索器
     * @param $query
     * @param $value
     */
    public function searchOrderSnAttr($query, $value)
    {
        if ($value) $query->where('order_sn', 'like', "%{$value}%");
    }

    /**
     * 状态搜索器
     * @param $query
     * @param $value
     */
    public function searchStatusAttr($query, $value)
    {
        if ($value !== '') $query->where('status', $value);
    }

    /**
     * 创建时间获取器
     * @param $value
     * @return string
     */
    public function getAddTimeAttr($value)
    {
        return $value ? date('Y-m-d H:i:s', (int)$value) : '';
    }
}
