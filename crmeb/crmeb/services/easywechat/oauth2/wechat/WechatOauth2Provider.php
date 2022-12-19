<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace crmeb\services\easywechat\oauth2\wechat;


use crmeb\services\SystemConfigService;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use think\facade\Request;

/**
 * 微信网页授权
 * Class WechatOauthProvider
 * @package crmeb\services\easywechat\oauth\wechat
 * @method oauth(string $code = '') code授权获取acces_token openid
 * @method getUserInfo($openId, $lang = 'zh_CN') openid 获取用户信息
 * @method  setRequest(Request $request) 设置request对象
 */
class WechatOauth2Provider implements ServiceProviderInterface
{

    /**
     *
     * @param Container $pimple
     */
    public function register(Container $pimple)
    {
        $request = app('request');
        $wechat = SystemConfigService::more(['wechat_appid', 'wechat_app_appid', 'wechat_app_appsecret', 'wechat_appsecret']);
        if ($request->isApp()) {
            $appId = isset($wechat['wechat_app_appid']) ? trim($wechat['wechat_app_appid']) : '';
            $appsecret = isset($wechat['wechat_app_appsecret']) ? trim($wechat['wechat_app_appsecret']) : '';
        } else {
            $appId = isset($wechat['wechat_appid']) ? trim($wechat['wechat_appid']) : '';
            $appsecret = isset($wechat['wechat_appsecret']) ? trim($wechat['wechat_appsecret']) : '';
        }

        $pimple['oauth2'] = function ($pimple) use ($appId, $appsecret) {
            return new WechatOauth($pimple['access_token'], $appId, $appsecret);
        };
    }
}
