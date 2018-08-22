<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/21
 */

namespace app\routine\model\user;

use basic\ModelBasic;
use traits\ModelTrait;
use app\routine\model\user\User;
use app\routine\model\user\WechatUser;
class RoutineUser extends ModelBasic
{
    use ModelTrait;

    /**
     * 小程序创建用户后返回uid
     * @param $routineInfo
     * @return mixed
     */
    public static function routineOauth($routine){
        $routineInfo['nickname'] = $routine['nickName'];//姓名
        $routineInfo['sex'] = $routine['gender'];//性别
        $routineInfo['language'] = $routine['language'];//语言
        $routineInfo['city'] = $routine['city'];//城市
        $routineInfo['province'] = $routine['province'];//省份
        $routineInfo['country'] = $routine['country'];//国家
        $routineInfo['headimgurl'] = $routine['avatarUrl'];//头像
//        $routineInfo[''] = $routine['code'];//临时登录凭证  是获取用户openid和session_key(会话密匙)
        $routineInfo['routine_openid'] = $routine['routine_openid'];//openid
        $routineInfo['session_key'] = $routine['session_key'];//会话密匙
        $routineInfo['unionid'] = $routine['unionid'];//用户在开放平台的唯一标识符
        $routineInfo['user_type'] = 'routine';//用户类型
        //  判断unionid  存在根据unionid判断
        if($routineInfo['unionid'] != '' && WechatUser::be(['unionid'=>$routineInfo['unionid']])){
            WechatUser::edit($routineInfo,$routineInfo['unionid'],'unionid');
            $uid = WechatUser::where('unionid',$routineInfo['unionid'])->value('uid');
            User::updateWechatUser($routineInfo,$uid);
        }else if(WechatUser::be(['routine_openid'=>$routineInfo['routine_openid']])){ //根据小程序openid判断
            WechatUser::edit($routineInfo,$routineInfo['routine_openid'],'routine_openid');
            $uid = WechatUser::where('routine_openid',$routineInfo['routine_openid'])->value('uid');
            User::updateWechatUser($routineInfo,$uid);
        }else{
            if(User::isUserSpread($routine['spid'])) $routineInfo['spread_uid'] = $routine['spid'];//用户上级
            else $routineInfo['spread_uid'] = 0;
            $routineInfo['add_time'] = time();//用户添加时间
            $routineInfo = WechatUser::set($routineInfo);
            $res = User::setRoutineUser($routineInfo);
            $uid = $res->uid;
        }
        return $uid;
    }

    /**
     * 判断是否是小程序用户
     * @param int $uid
     * @return bool|int|string
     */
    public static function isRoutineUser($uid = 0){
        if(!$uid) return false;
        return WechatUser::where('uid',$uid)->where('user_type','routine')->count();
    }
}