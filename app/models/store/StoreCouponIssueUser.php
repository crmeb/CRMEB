<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2018/01/22
 */

namespace app\models\store;


use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

/**
 * TODO 优惠券前台用户领取Model
 * Class StoreCouponIssueUser
 * @package app\models\store
 */
class StoreCouponIssueUser extends BaseModel
{
    /**
     * 模型名称
     * @var string
     */
    protected $name = 'store_coupon_issue_user';

    use ModelTrait;

    public static function addUserIssue($uid,$issue_coupon_id)
    {
        $add_time = time();
        return self::create(compact('uid','issue_coupon_id','add_time'));
    }
}