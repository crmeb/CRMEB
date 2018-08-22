<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/14
 */

namespace app\wap\model\user;

use app\wap\model\store\StoreCoupon;
use app\wap\model\store\StoreCouponUser;
use basic\ModelBasic;
use service\SystemConfigService;
use service\UtilService;
use service\WechatService;
use service\CacheService as Cache;
use think\Session;
use traits\ModelTrait;

class WechatUser extends ModelBasic
{
    use ModelTrait;

    protected $insert = ['add_time'];

    public static function setAddTimeAttr($value)
    {
        return time();
    }

    /**
     * .添加新用户
     * @param $openid
     * @return object
     */
    public static function setNewUser($openid)
    {
        $userInfo = WechatService::getUserInfo($openid);
        if(!isset($userInfo['subscribe']) || !$userInfo['subscribe'] || !isset($userInfo['openid']))
            exception('请关注公众号!');
        $userInfo['tagid_list'] = implode(',',$userInfo['tagid_list']);
        self::beginTrans();
        $wechatUser = self::set($userInfo);
        if(!$wechatUser){
            self::rollbackTrans();
            exception('用户储存失败!');
        }
        if(!User::setWechatUser($wechatUser)){
            self::rollbackTrans();
            exception('用户信息储存失败!');
        }
        self::commitTrans();
        self::userFirstSubGiveCoupon($openid);
        return $wechatUser;
    }

    public static function userFirstSubGiveCoupon($openid)
    {
        $couponId = SystemConfigService::get('wechat_first_sub_give_coupon');
        if($couponId) StoreCouponUser::addUserCoupon(self::openidToUid($openid),$couponId);
    }

    public static function userTakeOrderGiveCoupon($uid)
    {
        $couponId = SystemConfigService::get('store_order_give_coupon');
        if($couponId) StoreCouponUser::addUserCoupon($uid,$couponId);
    }

    /**
     * 更新用户信息
     * @param $openid
     * @return bool
     */
    public static function updateUser($openid)
    {
        $userInfo = WechatService::getUserInfo($openid);
        $userInfo['tagid_list'] = implode(',',$userInfo['tagid_list']);
        return self::edit($userInfo,$openid,'openid');
    }

    /**
     * 用户存在就更新 不存在就添加
     * @param $openid
     */
    public static function saveUser($openid)
    {
        self::be($openid,'openid') == true ? self::updateUser($openid) : self::setNewUser($openid);
    }

    /**
     * 用户取消关注
     * @param $openid
     * @return bool
     */
    public static function unSubscribe($openid)
    {
        return self::edit(['subscribe'=>0],$openid,'openid');
    }

    /**
     * 用uid获得openid
     * @param $uid
     * @return mixed
     */
    public static function uidToOpenid($uid,$update = false)
    {
        $cacheName = 'openid_'.$uid;
        $openid = Cache::get($cacheName);
        if($openid && !$update) return $openid;
        $openid = self::where('uid',$uid)->value('openid');
        if(!$openid) exception('对应的openid不存在!');
        Cache::set($cacheName,$openid,0);
        return $openid;
    }

    /**
     * 用uid获得Unionid
     * @param $uid
     * @return mixed
     */
    public static function uidToUnionid($uid,$update = false)
    {
        $cacheName = 'unionid_'.$uid;
        $unionid = Cache::get($cacheName);
        if($unionid && !$update) return $unionid;
        $unionid = self::where('uid',$uid)->value('unionid');
        if(!$unionid) exception('对应的unionid不存在!');
        Cache::set($cacheName,$unionid,0);
        return $unionid;
    }

    /**
     * 用openid获得uid
     * @param $uid
     * @return mixed
     */
    public static function openidToUid($openid,$update = false)
    {
        $cacheName = 'uid_'.$openid;
        $uid = Cache::get($cacheName);
        if($uid && !$update) return $uid;
        $uid = self::where('openid',$openid)->value('uid');
        if(!$uid) exception('对应的uid不存在!');
        Cache::set($cacheName,$uid,0);
        return $uid;
    }

    /**
     * 获取用户信息
     * @param $openid
     * @return array
     */
    public static function getWechatInfo($openid)
    {
        if(is_numeric($openid)) $openid = self::uidToOpenid($openid);
        $wechatInfo = self::where('openid',$openid)->find();
        if(!$wechatInfo) {
            self::setNewUser($openid);
            $wechatInfo = self::where('openid',$openid)->find();
        }
        if(!$wechatInfo) exception('获取用户信息失败!');
        return $wechatInfo->toArray();
    }

}