<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/21
 */

namespace app\ebapi\model\user;

use basic\ModelBasic;
use traits\ModelTrait;
use app\core\util\SystemConfigService;
use app\core\model\routine\RoutineQrcode;
use app\ebapi\model\store\StoreCouponUser;
/**
 * 用户附加表
 * Class WechatUser
 * @package app\ebapi\model\user
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

    /**
     * 用openid获得uid
     * @param $uid
     * @return mixed
     */
    public static function openidTouid($openid)
    {
        return  self::where('routine_openid',$openid)->value('uid');
    }

    public static function userTakeOrderGiveCoupon($uid)
    {
        $couponId = SystemConfigService::get('store_order_give_coupon');
        if($couponId) StoreCouponUser::addUserCoupon($uid,$couponId);
    }


    /**
     * 小程序创建用户后返回uid
     * @param $routineInfo
     * @return mixed
     */
    public static function routineOauth($routine){
        $routineInfo['nickname'] = filterEmoji($routine['nickName']);//姓名
        $routineInfo['sex'] = $routine['gender'];//性别
        $routineInfo['language'] = $routine['language'];//语言
        $routineInfo['city'] = $routine['city'];//城市
        $routineInfo['province'] = $routine['province'];//省份
        $routineInfo['country'] = $routine['country'];//国家
        $routineInfo['headimgurl'] = $routine['avatarUrl'];//头像
//        $routineInfo[''] = $routine['code'];//临时登录凭证  是获取用户openid和session_key(会话密匙)
        $routineInfo['routine_openid'] = $routine['openId'];//openid
        $routineInfo['session_key'] = $routine['session_key'];//会话密匙
        $routineInfo['unionid'] = $routine['unionId'];//用户在开放平台的唯一标识符
        $routineInfo['user_type'] = 'routine';//用户类型
        $page = '';//跳转小程序的页面
        $spid = 0;//绑定关系uid
        $isCOde=false;
        //获取是否有扫码进小程序
        if($routine['code']){
            $info = RoutineQrcode::getRoutineQrcodeFindType($routine['code']);
            if($info){
                $spid = $info['third_id'];
                $page = $info['page'];
                $isCOde=true;
            }else{
                $spid = $routine['spid'];
            }
        }else if($routine['spid']) $spid = $routine['spid'];
        //  判断unionid  存在根据unionid判断
        if($routineInfo['unionid'] != '' && self::be(['unionid'=>$routineInfo['unionid']])){
            self::edit($routineInfo,$routineInfo['unionid'],'unionid');
            $uid = self::where('unionid',$routineInfo['unionid'])->value('uid');
            $routineInfo['code']=$spid;
            $routineInfo['isPromoter']=$isCOde;
            User::updateWechatUser($routineInfo,$uid);
        }else if(self::be(['routine_openid'=>$routineInfo['routine_openid']])){ //根据小程序openid判断
            self::edit($routineInfo,$routineInfo['routine_openid'],'routine_openid');
            $uid = self::where('routine_openid',$routineInfo['routine_openid'])->value('uid');
            $routineInfo['code']=$spid;
            $routineInfo['isPromoter']=$isCOde;
            User::updateWechatUser($routineInfo,$uid);
        }else{
            $routineInfo['add_time'] = time();//用户添加时间
            $routineInfo = self::set($routineInfo);
//            if(User::isUserSpread($spid)) {
//                $res = User::setRoutineUser($routineInfo,$spid); //用户上级
//            }else
            $res = User::setRoutineUser($routineInfo,$spid);
            $uid = $res->uid;
        }
        $data['page'] = $page;
        $data['uid'] = $uid;
        return $data;
    }
    /**
     * 判断是否是小程序用户
     * @param int $uid
     * @return bool|int|string
     */
    public static function isRoutineUser($uid = 0){
        if(!$uid) return false;
        return self::where('uid',$uid)->where('user_type','routine')->count();
    }

    /**
     * @param int $uid
     * @return int
     */
    public static function isUserStatus($uid = 0){
        if(!$uid) return 0;
        $user = User::getUserInfo($uid);
        return $user['status'];
    }
}