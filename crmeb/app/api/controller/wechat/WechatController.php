<?php


namespace app\api\controller\wechat;


use app\models\user\User;
use app\models\user\UserToken;
use app\models\user\WechatUser;
use app\Request;
use crmeb\services\WechatService;
use think\facade\Cookie;

/**
 * 微信公众号
 * Class WechatController
 * @package app\api\controller\wechat
 */
class WechatController
{
    /**
     * 微信公众号服务
     * @return \think\Response
     */
    public function serve()
    {
        return WechatService::serve();
    }

    /**
     * 支付异步回调
     */
    public function notify()
    {
        WechatService::handleNotify();
    }

    /**
     * 公众号权限配置信息获取
     * @param Request $request
     * @return mixed
     */
    public function config(Request $request)
    {
        return app('json')->success(json_decode(WechatService::jsSdk($request->get('url')), true));
    }

    /**
     * 公众号授权登陆
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function auth(Request $request)
    {
        $spreadId = intval($request->param('spread'));
        $login_type = $request->param('login_type','');
        try {
            $wechatInfo = WechatService::oauthService()->user()->getOriginal();
        } catch (\Exception $e) {
            return app('json')->fail('授权失败');
        }
        if (!isset($wechatInfo['nickname'])) {
            $wechatInfo = WechatService::getUserInfo($wechatInfo['openid']);
            if (!$wechatInfo['subscribe'] && !isset($wechatInfo['nickname']))
                exit(WechatService::oauthService()->scopes(['snsapi_userinfo'])
                    ->redirect($this->request->url(true))->send());
            if (isset($wechatInfo['tagid_list']))
                $wechatInfo['tagid_list'] = implode(',', $wechatInfo['tagid_list']);
        } else {
            if (isset($wechatInfo['privilege'])) unset($wechatInfo['privilege']);
            if (!WechatUser::be(['openid' => $wechatInfo['openid']]))
                $wechatInfo['subscribe'] = 0;
        }
        $openid = $wechatInfo['openid'];
        event('WechatOauthAfter', [$openid, $wechatInfo, $spreadId, $login_type]);
        $user = User::where('uid', WechatUser::openidToUid($openid, 'openid'))->find();
        if (!$user)
            return app('json')->fail('获取用户信息失败');
        if($user->login_type == 'h5' && ($h5UserInfo = User::where(['account'=>$user->phone,'phone'=>$user->phone,'user_type'=>'h5'])->find()))
            $token = UserToken::createToken($h5UserInfo, 'wechat');
        else
            $token = UserToken::createToken($user, 'wechat');
        // 设置推广关系
        User::setSpread(intval($spreadId), $user->uid);
        if ($token) {
            event('UserLogin', [$user, $token]);
            return app('json')->success('登录成功', ['token' => $token->token, 'expires_time' => $token->expires_time]);
        } else
            return app('json')->fail('登录失败');
    }
}