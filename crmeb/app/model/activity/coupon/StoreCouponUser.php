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

namespace app\model\activity\coupon;

use app\model\user\User;
use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;
use think\Model;

/**
 * TODO 优惠券发放Model
 * Class StoreCouponUser
 * @package app\model\coupon
 */
class StoreCouponUser extends BaseModel
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
    protected $name = 'store_coupon_user';

    /**
     * 获取类型
     * @var string[]
     */
    protected $gainType = ['send' => '后台发放', 'get' => '手动领取'];

    /**
     * 类型获取器
     * @param $value
     * @return string
     */
    public function getTypeAttr($value)
    {
        return $this->gainType[$value];
    }

    /**
     * 使用状态
     * @var string[]
     */
    protected $statusType = [0 => '未使用', 1 => '已使用', 2 => '已过期'];

    /**
     * 状态获取器
     * @param $value
     * @return string
     */
    public function getStatusAttr($value)
    {
        return $this->statusType[$value];
    }

    /**
     * @return \think\model\relation\HasOne
     */
    public function issue()
    {
        return $this->hasOne(StoreCouponIssue::class, 'id', 'cid')->field(['id', 'end_use_time', 'start_use_time', 'type', 'coupon_time', 'product_id', 'category_id', 'receive_type'])->bind([
            'applicable_type' => 'type',
            'coupon_time' => 'coupon_time',
            'product_id',
            'category_id',
            'receive_type',
            'start_use_time',
            'end_use_time'
        ]);
    }

    /**
     * 获取领取人名称头像
     * @return \think\model\relation\HasOne
     */
    public function userInfo()
    {
        return $this->hasOne(User::class, 'uid', 'uid')->field('uid,nickname,avatar')->bind(['nickname', 'avatar']);
    }

    /**
     * 优惠券ID搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchCidAttr($query, $value, $data)
    {
        if (is_array($value)) {
            $query->where('cid', 'IN', $value);
        } else {
            $query->where('cid', $value);
        }
    }

    /**
     * 用户ID搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchUidAttr($query, $value, $data)
    {
        $query->where('uid', $value);
    }

    /**
     * 优惠券名称搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchCouponTitleAttr($query, $value, $data)
    {
        $query->where('coupon_title', 'like', '%' . $value . '%');
    }

    /**
     * 获取方式搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchTypeAttr($query, $value, $data)
    {
        $query->where('type', $value);
    }


    /**
     * 是否失效
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchIsFailAttr($query, $value, $data)
    {
        $query->where('is_fail', $value);
    }

    /**
     * 是否在使用期限内搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchTimeAttr($query, $value, $data)
    {
        $query->whereTime('add_time', '>=', $value)->whereTime('end_time', '<=', $value);
    }
}
