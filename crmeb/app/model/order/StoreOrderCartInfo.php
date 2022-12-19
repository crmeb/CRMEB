<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
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
 * TODO 订单记录Model
 * Class StoreOrderCartInfo
 * @package app\model\order
 */
class StoreOrderCartInfo extends BaseModel
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
    protected $name = 'store_order_cart_info';

    /**
     * 购物车信息获取器
     * @param $value
     * @return array|mixed
     */
    public function getCartInfoAttr($value)
    {
        return json_decode($value, true) ?? [];
    }

    /**
     * 订单ID搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchOidAttr($query, $value, $data)
    {
        if ($value !== '') $query->where('oid', $value);
    }

    /**
     * 购物车ID搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchCartIdAttr($query, $value, $data)
    {
        if (is_array($value)) {
            $query->whereIn('cart_id', $value);
        } else {
            $query->where('cart_id', $value);
        }
    }

    /**
     * 原购物车ID搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchOldCartIdAttr($query, $value, $data)
    {
        if (is_array($value)) {
            $query->whereIn('old_cart_id', $value);
        } else {
            $query->where('old_cart_id', $value);
        }
    }

    /**
     *  拆分状态搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchSplitStatusAttr($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('split_status', $value);
        } else {
            if (in_array($value, [0, 1, 2])) {
                $query->where('split_status', $value);
            }
        }
    }
}
