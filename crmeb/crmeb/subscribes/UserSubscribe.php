<?php

namespace crmeb\subscribes;

use app\models\user\User;
use app\models\user\WechatUser;
use think\facade\Cookie;
use app\admin\model\system\SystemAttachment;
use app\models\user\UserLevel;

/**
 * 用户事件
 * Class UserSubscribe
 * @package crmeb\subscribes
 */
class UserSubscribe
{

    public function handle()
    {

    }

    /**
     * 管理员后台给用户添加金额
     * @param $event
     */
    public function onAdminAddMoney($event)
    {
        list($user, $money) = $event;
        //$user 用户信息
        //$money 添加的金额
    }

    /**
     * 微信授权成功后
     * @param $event
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function onWechatOauthAfter($event)
    {
        list($openid, $wechatInfo, $spreadId, $login_type) = $event;

        if (!User::be(['uid' => $spreadId])) $spreadId = 0;

        $wechatInfo['nickname'] = filter_emoji($wechatInfo['nickname']);
        Cookie::set('is_login', 1);
        if (isset($wechatInfo['unionid']) && $wechatInfo['unionid'] != '' && ($uid = WechatUser::where('unionid', $wechatInfo['unionid'])->where('user_type', '<>', 'h5')->value('uid'))) {
            WechatUser::edit($wechatInfo, $uid, 'uid');
            if (!User::be(['uid' => $uid])) {
                $wechatInfo = WechatUser::where('uid', $uid)->find();
                User::setWechatUser($wechatInfo, $spreadId);
            } else {
                if ($login_type) $wechatInfo['login_type'] = $login_type;
                User::updateWechatUser($wechatInfo, $uid);
            }
        } else if ($uid = WechatUser::where(['openid' => $wechatInfo['openid']])->where('user_type', '<>', 'h5')->value('uid')) {
            WechatUser::edit($wechatInfo, $uid, 'uid');
            if ($login_type) $wechatInfo['login_type'] = $login_type;
            User::updateWechatUser($wechatInfo, $uid);
        } else {
            if (isset($wechatInfo['subscribe_scene'])) unset($wechatInfo['subscribe_scene']);
            if (isset($wechatInfo['qr_scene'])) unset($wechatInfo['qr_scene']);
            if (isset($wechatInfo['qr_scene_str'])) unset($wechatInfo['qr_scene_str']);
//            $isLogin = request()->isLogin();
//            $bind = false;
//            if($isLogin){
//                $loginUid = request()->user();
//                $isUser = $loginUid ? request()->tokenData()->type === 'user' : false;
//                $bind = $loginUid && $isUser && !$loginUid->openid && !User::be(['openid' => $wechatInfo['openid']]);
//                //微信用户绑定 h5用户
//                if ($bind) {
//                    $wechatInfo['uid'] = $loginUid->uid;
//                };
//            }
            $wechatInfo = WechatUser::create($wechatInfo);
//            if ($isLogin && $bind)
//                User::where('uid', $wechatInfo['uid'])
//                    ->limit(1)->update(['openid' => $wechatInfo['openid']]);
//            else
            User::setWechatUser($wechatInfo, $spreadId);
        }
        $uid = WechatUser::openidToUid($openid, 'openid');
        // 设置推广关系
        User::setSpread($spreadId, $uid);

        User::where('uid', $uid)
            ->limit(1)->update(['last_time' => time(), 'last_ip' => app('request')->ip()]);
    }

    /**
     * 用户访问记录
     * @param $event
     */
    public function onInitLogin($event)
    {
        list($userInfo) = $event;
        $request = app('request');
        User::edit(['last_time' => time(), 'last_ip' => $request->ip()], $userInfo->uid, 'uid');
    }

    /**
     * 检查是否能成为会员
     * @param $event
     */
    public function onUserLevelAfter($event)
    {
        list($userUid) = $event;
        UserLevel::setLevelComplete($userUid);
    }

}