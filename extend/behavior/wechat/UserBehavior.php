<?php
/**
 *
* @author: xaboy<365615158@qq.com>
 * @day: 2017/11/25
*/

namespace behavior\wechat;

use app\wap\model\user\User;
use app\wap\model\user\WechatUser;
use think\Cookie;
use think\Request;

class UserBehavior
{
    /**
     * 微信授权成功后
     * @param $userInfo
     */
    public static function wechatOauthAfter($openid,$wechatInfo)
    {
//        echo "调试中";
//        var_dump($wechatInfo);
        $wechatInfo['nickname'] = filterEmoji($wechatInfo['nickname']);
        Cookie::set('is_login',1);
        if(isset($wechatInfo['unionid']) && $wechatInfo['unionid'] != '' && WechatUser::be(['unionid'=>$wechatInfo['unionid']])){
            WechatUser::edit($wechatInfo,$wechatInfo['unionid'],'unionid');
            $uid = WechatUser::where('unionid',$wechatInfo['unionid'])->value('uid');
            if(!User::be(['uid'=>$uid])){
                $wechatInfo = WechatUser::where('uid',$uid)->find();
                User::setWechatUser($wechatInfo);
            }else{
                User::updateWechatUser($wechatInfo,$uid);
            }
        }else if(WechatUser::be(['openid'=>$wechatInfo['openid']])){
            WechatUser::edit($wechatInfo,$wechatInfo['openid'],'openid');
            User::updateWechatUser($wechatInfo,WechatUser::openidToUid($wechatInfo['openid']));
        }else{
            if(isset($wechatInfo['subscribe_scene'])) unset($wechatInfo['subscribe_scene']);
            if(isset($wechatInfo['qr_scene'])) unset($wechatInfo['qr_scene']);
            if(isset($wechatInfo['qr_scene_str'])) unset($wechatInfo['qr_scene_str']);
            $wechatInfo = WechatUser::set($wechatInfo);
            User::setWechatUser($wechatInfo);
        }
        User::where('uid',WechatUser::openidToUid($openid))
            ->limit(1)->update(['last_time'=>time(),'last_ip'=>Request::instance()->ip()]);
    }

}