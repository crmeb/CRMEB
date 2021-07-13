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

namespace app\model\coupon;

use app\model\user\User;
use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;
use think\Model;

/**
 * TODO 优惠券前台用户领取Model
 * Class StoreCouponIssueUser
 * @package app\model\coupon
 */
class StoreCouponIssueUser extends BaseModel
{
    use ModelTrait;

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'store_coupon_issue_user';

    /**
     * 获取领取人名称头像
     * @return \think\model\relation\HasOne
     */
    public function userInfo()
    {
        return $this->hasOne(User::class, 'uid', 'uid')->field('uid,nickname,avatar')->bind(['nickname','avatar']);
    }

    /**
     * 添加时间获取器
     * @param $value
     * @return false|string
     */
    public function getAddTimeAttr($value)
    {
        return date('Y-m-d H:i:s', $value);
    }

    /**
     * 领取用户搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchUidAttr($query, $value, $data)
    {
        $query->where('uid', $value);
    }

    /**
     * 领取优惠券搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchIssueCouponIdAttr($query, $value, $data)
    {
        $query->where('issue_coupon_id', $value);
    }
}
