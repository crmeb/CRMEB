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

use app\model\activity\combination\StorePink;
use app\model\system\store\SystemStore;
use app\model\system\store\SystemStoreStaff;
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
     * 支付类型
     * @var string[]
     */
    protected $pay_type = [
        1 => 'weixin',
        2 => 'yue',
        3 => 'offline',
        4 => 'alipay'
    ];

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
    protected function setAddTimeAttr($time = 0)
    {
        if ($time) return $time;
        return time();
    }

    /**
     * 自定义表单修改器
     * @param $value
     * @return array|mixed
     */
    public function setCustomFormAttr($value)
    {
        return is_array($value) ? json_encode($value) : $value;
    }

    /**
     * 自定义表单获取器
     * @param $value
     * @return array|mixed
     */
    public function getCustomFormAttr($value)
    {
        return is_string($value) ? json_decode($value, true) ?? [] : [];
    }

    /**
     * 一对多关联查询子订单
     * @return \think\model\relation\HasMany
     */
    public function split()
    {
        return $this->hasMany(StoreOrder::class, 'pid', 'id');
    }

    /**
     * 一对一关联用户表
     * @return \think\model\relation\HasOne
     */
    public function user()
    {
        return $this->hasOne(User::class, 'uid', 'uid')->field(['uid', 'nickname', 'phone', 'spread_uid'])->bind([
            'nickname' => 'nickname',
            'phone' => 'phone'
        ]);
    }

    public function division()
    {
        return $this->hasOne(User::class, 'uid', 'division_id')->field(['uid', 'nickname'])->bind([
            'division_name' => 'nickname'
        ]);
    }

    /**
     * 一对一关联上级用户信息
     * @return \think\model\relation\HasOne
     */
    public function spread()
    {
        return $this->hasOne(User::class, 'uid', 'spread_uid')->field(['uid', 'nickname'])->bind([
            'spread_nickname' => 'nickname'
        ]);
    }

    /**
     * 一对一拼团获取状态
     * @return \think\model\relation\HasOne
     */
    public function pink()
    {
        return $this->hasOne(StorePink::class, 'id', 'pink_id')->field(['id', 'order_id_key', 'status'])->bind([
            'pinkStatus' => 'status'
        ]);
    }

    /**
     * 门店一对一关联
     * @return \think\model\relation\HasOne
     */
    public function store()
    {
        return $this->hasOne(SystemStore::class, 'id', 'store_id')->field(['id', 'name'])->bind([
            'store_name' => 'name'
        ]);
    }

    /**
     * 订单关联店员
     * @return \think\model\relation\HasOne
     */
    public function staff()
    {
        return $this->hasOne(SystemStoreStaff::class, 'uid', 'clerk_id')->field(['id', 'uid', 'store_id', 'staff_name'])->bind([
            'staff_uid' => 'uid',
            'staff_store_id' => 'store_id',
            'clerk_name' => 'staff_name'
        ]);
    }

    /**
     * 店员关联用户
     * @return \think\model\relation\HasOne
     */
    public function staffUser()
    {
        return $this->hasOne(User::class, 'uid', 'staff_uid')->field(['uid', 'nickname'])->bind([
            'clerk_name' => 'nickname'
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
     * 一对多关联退款订单
     * @return \think\model\relation\hasMany
     */
    public function refund()
    {
        return $this->hasMany(StoreOrderRefund::class, 'store_order_id', 'id')->where('refund_type', '<>', 3)->where('is_cancel', 0);
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
        return $value ? json_decode($value, true) : [];
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

    /**
     * 父类ID搜索器
     * @param Model $query
     * @param $value
     */
    public function searchPidAttr($query, $value)
    {
        if ($value === 0) {
            $query->where('pid', '>=', 0);
        } else {
            $query->where('pid', $value);
        }
    }

    /**
     * 没拆分订单 与子订单(0:为拆分订单-1：已拆分主订单 >0 :拆分后子订单)
     * @param Model $query
     * @param $value
     */
    public function searchNotPidAttr($query, $value)
    {
        $query->where('pid', '<>', -1);
    }

    /**
     * @param Model $query
     * @param $value
     */
    public function searchIdAttr($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('id', $value);
        } else {
            $query->where('id', $value);
        }
    }

    /**
     * 支付方式搜索器
     * @param $query
     * @param $value
     */
    public function searchPayTypeAttr($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('pay_type', $value);
        } else {
            if ($value !== '') {
                $pay_type = $this->pay_type;
                if (in_array($value, array_keys($pay_type)) && $type = $pay_type[$value] ?? '') {
                    $query->where('pay_type', $type);
                } else {
                    $query->where('pay_type', $value);
                }
            }
        }
    }

    /**
     * 不等于余额支付
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
        if (in_array($value, [0, 1])) {
            $query->where('paid', $value);
        }
    }

    /**
     * 退款状态搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchRefundStatusAttr($query, $value, $data)
    {
        if ($value !== '') {
            if (is_array($value)) {
                $query->whereIn('refund_status', $value);
            } else {
                $query->where('refund_status', $value);
            }
        }
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
     * 是否删除搜索器
     * @param Model $query
     * @param $value
     */
    public function searchIsSystemDelAttr($query, $value)
    {
        if ($value != '') $query->where('is_system_del', $value);
    }

    /**
     * 退款状态搜索器
     * @param $query
     * @param $value
     */
    public function searchRefundTypeAttr($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('refund_type', $value);
        } else {
            if ($value == -1) {
                $query->where('refund_type', 'in', '0,3');
            } else {
                if ($value == 0 || $value == '') {
                    $query->where('refund_type', '<>', 0);
                } else {
                    $query->where('refund_type', $value);
                }
            }
        }
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

    /**
     * 退款id搜索器
     * @param Model $query
     * @param $value
     */
    public function searchRefundIdAttr($query, $value)
    {
        if ($value) {
            $query->where('id', 'in', $value);
        }
    }

    /**
     * 上级｜上上级推广人
     * @param $query
     * @param $value
     */
    public function searchSpreadOrUidAttr($query, $value)
    {
        if ($value) $query->where('spread_uid|spread_two_uid', $value);
    }

    /**
     * 上级推广人
     * @param $query
     * @param $value
     */
    public function searchSpreadUidAttr($query, $value)
    {
        if ($value) $query->where('spread_uid', $value);
    }

    /**
     * 上上级推广人
     * @param $query
     * @param $value
     */
    public function searchSpreadTwoUidAttr($query, $value)
    {
        if ($value) $query->where('spread_two_uid', $value);
    }

    /**
     * 支付渠道
     * @param $query
     * @param $value
     */
    public function searchIsChannelAttr($query, $value)
    {
        if ($value !== '') $query->where('is_channel', $value);
    }

    /**
     * 活动查询0普通，1秒杀，2砍价，3拼团，4预售
     * @param $query
     * @param $value
     * @param $data
     */
    public function searchActivityTypeAttr($query, $value, $data)
    {
        if ($value !== '') {
            switch ($value) {
                case 0:
                    $query->where('combination_id', 0)->where('seckill_id', 0)->where('bargain_id', 0)->where('advance_id', 0);
                    break;
                case 1:
                    $query->where('seckill_id', '>', 0);
                    break;
                case 2:
                    $query->where('bargain_id', '>', 0);
                    break;
                case 3:
                    $query->where('combination_id', '>', 0);
                    break;
                case 4:
                    $query->where('advance_id', '>', 0);
                    break;
                default:
                    break;
            }
        }
    }

    /**
     * 事业部推广订单
     * @param $query
     * @param $value
     */
    public function searchDivisionIdAttr($query, $value)
    {
        if ($value !== '') $query->where('division_id', $value);
    }

    /**
     * 代理商推广订单
     * @param $query
     * @param $value
     */
    public function searchAgentIdAttr($query, $value)
    {
        if ($value !== '') $query->where('agent_id', $value);
    }
}
