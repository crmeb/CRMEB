<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\model\order;

use app\model\user\User;
use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;
use think\Model;

/**
 * TODO 订单Model
 * Class StoreOrder
 * @package app\model\order
 */
class StoreOrder extends BaseModel
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
    protected $name = 'store_order';

    protected $insert = ['add_time'];

    /**
     * 更新时间
     * @var bool | string | int
     */
    protected $updateTime = false;

    /**
     * 创建时间修改器
     * @return int
     */
    protected function setAddTimeAttr()
    {
        return time();
    }

    /**
     * 一对一关联用户表
     * @return \think\model\relation\HasOne
     */
    public function user()
    {
        return $this->hasOne(User::class, 'uid', 'uid')->field(['uid', 'nickname', 'phone', 'spread_uid'])->bind([
            'nickname' => 'nickname',
            'phone' => 'phone',
            'spread_uid' => 'spread_uid',
        ]);
    }

    /**
     * 关联订单发票
     * @return \think\model\relation\HasOne
     */
    public function invoice()
    {
        return $this->hasOne(StoreOrderInvoice::class, 'order_id', 'id');
    }

    /**
     * 购物车ID修改器
     * @param $value
     * @return false|string
     */
    protected function setCartIdAttr($value)
    {
        return is_array($value) ? json_encode($value) : $value;
    }

    /**
     * 购物车获取器
     * @param $value
     * @param $data
     * @return mixed
     */
    protected function getCartIdAttr($value, $data)
    {
        return json_decode($value, true);
    }

    /**
     * 订单ID搜索器
     * @param Model $query
     * @param $value
     */
    public function searchOrderIdAttr($query, $value)
    {
        $query->where('order_id', $value);
    }

    /**不等于余额支付
     * @param $query
     * @param $value
     */
    public function searchPayTypeNoAttr($query, $value)
    {
        $query->where('pay_type', "<>", $value);
    }

    /**
     * 订单id或者用户名搜索器
     * @param $query
     * @param $value
     */
    public function searchOrderIdRealNameAttr($query, $value)
    {
        $query->where('order_id|real_name', $value);
    }

    /**
     * 用户ID搜索器
     * @param Model $query
     * @param $value
     */
    public function searchUidAttr($query, $value)
    {
        if (is_array($value))
            $query->whereIn('uid', $value);
        else
            $query->where('uid', $value);
    }

    /**
     * 支付状态搜索器
     * @param Model $query
     * @param $value
     */
    public function searchPaidAttr($query, $value)
    {
        $query->where('paid', $value);
    }

    /**
     * 退款状态搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchRefundStatusAttr($query, $value, $data)
    {
        $query->where('refund_status', $value);
    }

    /**
     * 退款状态搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchRefundStatusInAttr($query, $value)
    {
        $query->whereIn('refund_status', $value);
    }

    /**
     * 是否是拼团订单
     * @param Model $query
     * @param $value
     */
    public function searchPinkIdAttr($query, $value)
    {
        $query->where('pink_id', $value);
    }

    /**
     * 拼团id搜索器
     * @param Model $query
     * @param $value
     */
    public function searchCombinationIdAttr($query, $value)
    {
        $query->where('combination_id', $value);
    }

    /**
     * 没有拼团订单或拼团商品
     * @param Model $query
     * @param $value
     */
    public function searchCpIdGtAttr($query, $value)
    {
        $query->where('combination_id|pink_id', '>', $value);
    }

    /**
     * 不是秒杀搜索器
     * @param Model $query
     * @param $value
     */
    public function searchSeckillIdGtAttr($query, $value)
    {
        $query->where('seckill_id', '>', $value);
    }

    /**
     * 秒杀id商品搜索器
     * @param Model $query
     * @param $value
     */
    public function searchSeckillIdAttr($query, $value)
    {
        $query->where('seckill_id', $value);
    }

    /**
     * 砍价商品id搜索器
     * @param Model $query
     * @param $value
     */
    public function searchBargainIdAttr($query, $value)
    {
        $query->where('bargain_id', $value);
    }

    /**
     * 属于砍价搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchBargainIdGtAttr($query, $value)
    {
        $query->where('bargain_id', '>', $value);
    }

    /**
     * 核销码搜索器
     * @param Model $query
     * @param $value
     */
    public function searchVerifyCodeAttr($query, $value)
    {
        $query->where('verify_code', $value);
    }

    /**
     * 支付状态搜索器
     * @param Model $query
     * @param $value
     */
    public function searchIsDelAttr($query, $value)
    {
        if ($value != '') $query->where('is_del', $value);
    }

    /**
     * 用户来源
     * @param Model $query
     * @param $value
     */
    public function searchChannelTypeAttr($query, $value)
    {
        if ($value != '') $query->where('channel_type', $value);
    }
}
