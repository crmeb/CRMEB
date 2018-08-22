<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/21
 */

namespace app\routine\model\user;

use basic\ModelBasic;
use think\Session;
use traits\ModelTrait;

/**
 * 用户model
 * Class User
 * @package app\routine\model\user
 */
class User extends ModelBasic
{
    use ModelTrait;

    public static function updateWechatUser($wechatUser,$uid)
    {
        return self::edit([
            'nickname'=>$wechatUser['nickname']?:'',
            'avatar'=>$wechatUser['headimgurl']?:''
        ],$uid,'uid');
    }



    /**
     * 小程序用户添加
     * @param $routineUser
     * @param int $spread_uid
     * @return object
     */
    public static function setRoutineUser($routineUser,$spread_uid = 0){
        return self::set([
            'account'=>'rt'.$routineUser['uid'].time(),
            'pwd'=>md5(123456),
            'nickname'=>$routineUser['nickname']?:'',
            'avatar'=>$routineUser['headimgurl']?:'',
            'spread_uid'=>$spread_uid,
            'uid'=>$routineUser['uid'],
            'add_time'=>$routineUser['add_time'],
            'user_type'=>$routineUser['user_type']
        ]);
    }

    /**
     * 获得当前登陆用户UID
     * @return int $uid
     */
    public static function getActiveUid()
    {
        $uid = null;
        $uid = Session::get('LoginUid');
        if($uid) return $uid;
        else return 0;
    }
    public static function getUserInfo($uid)
    {
        $userInfo = self::where('uid',$uid)->find();
        if(!$userInfo) exception('读取用户信息失败!');
        return $userInfo->toArray();
    }

    /**
     * 判断当前用户是否推广员
     * @param int $uid
     * @return bool
     */
    public static function isUserSpread($uid = 0){
        if(!$uid) return false;
        $isPromoter = self::where('uid',$uid)->value('is_promoter');
        if($isPromoter) return true;
        else return false;
    }
}