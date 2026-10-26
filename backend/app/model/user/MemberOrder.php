<?php
declare(strict_types=1);

namespace app\model\user;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

/**
 * 会员订单模型
 * 对应表 eb_member_order，承载会员购买/免费领取/卡密领取订单
 */
class MemberOrder extends BaseModel
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
    protected $name = 'member_order';

    /**
     * 新增时自动写入添加时间
     * @var array
     */
    protected $insert = ['add_time'];

    /**
     * 字段类型转换
     * @var array
     */
    protected $type = [
        'member_price' => 'float',
        'pay_price' => 'float',
        'money' => 'float',
    ];

    /**
     * 自动写入添加时间
     * @return int
     */
    protected function setAddTimeAttr()
    {
        return time();
    }

    /**
     * 关联用户
     * @return \think\model\relation\HasOne
     */
    public function user()
    {
        return $this->hasOne(User::class, 'uid', 'uid')->field('uid,nickname,phone,avatar');
    }

    /**
     * 用户ID搜索器
     * @param mixed $query
     * @param mixed $value
     */
    public function searchUidAttr($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('uid', $value);
        } else {
            $query->where('uid', $value);
        }
    }

    /**
     * 订单号搜索器
     * @param mixed $query
     * @param mixed $value
     */
    public function searchOrderIdAttr($query, $value)
    {
        $query->where('order_id', $value);
    }

    /**
     * 订单类型搜索器
     * @param mixed $query
     * @param mixed $value
     */
    public function searchTypeAttr($query, $value)
    {
        if (is_array($value)) {
            $query->where('type', 'in', $value);
        } else {
            $query->where('type', $value);
        }
    }

    /**
     * 订单状态搜索器
     * @param mixed $query
     * @param mixed $value
     */
    public function searchStatusAttr($query, $value)
    {
        $query->where('status', $value);
    }

    /**
     * 支付状态搜索器
     * @param mixed $query
     * @param mixed $value
     */
    public function searchPaidAttr($query, $value)
    {
        $query->where('paid', $value);
    }

    /**
     * 会员类型搜索器
     * -1 表示查询所有已购买会员（激活付费会员统计）
     * @param mixed $query
     * @param mixed $value
     */
    public function searchMemberTypeAttr($query, $value)
    {
        if ($value === '' || $value === null) {
            return;
        }
        if ($value === -1) {
            $query->where('member_type', '<>', '');
        } else {
            $query->where('member_type', $value);
        }
    }

    /**
     * 支付方式搜索器
     * @param mixed $query
     * @param mixed $value
     */
    public function searchPayTypeAttr($query, $value)
    {
        if ($value === '' || $value === null) {
            return;
        }
        $query->where('pay_type', $value);
    }

    /**
     * 免费支付筛选（免费领取 / 试用免费单）
     * @param mixed $query
     * @param mixed $value
     */
    public function searchFreePayAttr($query, $value)
    {
        if (!$value) {
            return;
        }
        $query->where(function ($q) {
            $q->where('type', 0)->whereOr(function ($q2) {
                $q2->where('type', 1)->where('is_free', 1);
            });
        });
    }

    /**
     * 渠道类型搜索器
     * @param mixed $query
     * @param mixed $value
     */
    public function searchChannelTypeAttr($query, $value)
    {
        $query->where('channel_type', $value);
    }

    /**
     * 添加时间搜索器
     * @param mixed $query
     * @param mixed $value
     */
    public function searchAddTimeAttr($query, $value)
    {
        if (empty($value) && $value !== '0') return;
        if (is_string($value)) {
            $query->whereTime('add_time', $value);
        } elseif (is_array($value) && count($value) == 2) {
            $startTime = $value[0];
            $endTime = $value[1] ?? 0;
            if ($startTime && $endTime) {
                $query->whereBetween('add_time', [$startTime, $endTime]);
            } elseif ($startTime) {
                $query->whereTime('add_time', '>=', $startTime);
            } elseif ($endTime) {
                $query->whereTime('add_time', '<', $endTime);
            }
        }
    }

    /**
     * 支付时间搜索器
     * @param mixed $query
     * @param mixed $value
     */
    public function searchPayTimeAttr($query, $value)
    {
        if (empty($value) && $value !== '0') return;
        if (is_string($value)) {
            $query->whereTime('pay_time', $value);
        } elseif (is_array($value) && count($value) == 2) {
            $startTime = $value[0];
            $endTime = $value[1] ?? 0;
            if ($startTime && $endTime) {
                $query->whereBetween('pay_time', [$startTime, $endTime]);
            } elseif ($startTime) {
                $query->whereTime('pay_time', '>=', $startTime);
            } elseif ($endTime) {
                $query->whereTime('pay_time', '<', $endTime);
            }
        }
    }
}
