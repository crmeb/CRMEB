<?php


namespace app\api\controller\wechat;


use app\models\user\User;
use app\models\user\UserToken;
use app\models\user\WechatUser;
use app\Request;
use crmeb\services\WechatService;
use crmeb\utils\Canvas;
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
        ob_clean();
        return WechatService::serve();
    }

    /**
     * 支付异步回调
     */
    public function notify()
    {
        ob_clean();
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
        $login_type = $request->param('login_type', '');
        try {
            $wechatInfo = WechatService::oauthService()->user()->getOriginal();
        } catch (\Exception $e) {
            return app('json')->fail('授权失败', ['message' => $e->getMessage(), 'line' => $e->getLine()]);
        }
        if (!isset($wechatInfo['nickname'])) {
            try {
                $wechatInfo = WechatService::getUserInfo($wechatInfo['openid']);
            } catch (\Exception $e) {
                return app('json')->fail('获取信息失败', ['message' => $e->getMessage(), 'line' => $e->getLine()]);
            }
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
        if ($user->login_type == 'h5' && ($h5UserInfo = User::where(['account' => $user->phone, 'phone' => $user->phone, 'user_type' => 'h5'])->find()))
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

    public function follow()
    {
        $canvas = Canvas::instance();
        $path = 'uploads/follow/';
        $imageType = 'jpg';
        $name = 'follow';
        $siteUrl = sys_config('site_url');
        $imageUrl = $path . $name . '.' . $imageType;
//        if (file_exists($imageUrl)) {
//            return app('json')->success('ok', ['path' => $siteUrl . '/' . $imageUrl]);
//        }
        $canvas->setImageUrl('static/qrcode/follow.png')->setImageHeight(720)->setImageWidth(500)->pushImageValue();
        $wechatQrcode = sys_config('wechat_qrcode');
        if (($strlen = stripos($wechatQrcode, 'uploads')) !== false) {
            $wechatQrcode = substr($wechatQrcode, $strlen);
        }
        if (!$wechatQrcode)
            return app('json')->fail('请上传二维码');
        $canvas->setImageUrl($wechatQrcode)->setImageHeight(344)->setImageWidth(344)->setImageLeft(76)->setImageTop(76)->pushImageValue();
        $image = $canvas->setFileName($name)->setImageType($imageType)->setPath($path)->setBackgroundWidth(500)->setBackgroundHeight(720)->starDrawChart();
        return app('json')->success('ok', ['path' => $image ? $siteUrl . '/' . $image : '']);
    }
}