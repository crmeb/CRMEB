<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/21
 */

namespace app\routine\model\user;

use app\routine\model\store\StoreCouponUser;
use basic\ModelBasic;
use service\SystemConfigService;
use traits\ModelTrait;
/**
 * 微信用户model
 * Class WechatUser
 * @package app\routine\model\user
 */
class WechatUser extends ModelBasic
{
    use ModelTrait;

    public static function getOpenId($uid = ''){
        if($uid == '') return false;
        return self::where('uid',$uid)->value('routine_openid');
    }
    /**
     * 用uid获得openid
     * @param $uid
     * @return mixed
     */
    public static function uidToOpenid($uid)
    {
        $openid = self::where('uid',$uid)->value('routine_openid');
        return $openid;
    }

    public static function userTakeOrderGiveCoupon($uid)
    {
        $couponId = SystemConfigService::get('store_order_give_coupon');
        if($couponId) StoreCouponUser::addUserCoupon($uid,$couponId);
    }
}