<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\services\wechat;

use crmeb\services\easywechat\oauth2\wechat\WechatOauth;
use crmeb\services\wechat\Support;
use think\Response;

/**
 * 微信开放平台服务
 *
 * 修复历史问题:旧实现访问 Application->open_platform / ->oauth 在 4.x 兼容层上无法解析,
 * 客服扫码登录(WechatOAuth::oauth(['open'=>true]))链路实际不可用。
 * 现基于 EasyWeChat 6.x:扫码授权走 sns 接口(open 平台 appid),消息推送走开放平台 Server。
 *
 * Class WechatOpenService
 * @package app\services\wechat
 */
class WechatOpenService
{

    protected function options(): array
    {
        return [
            'app_id' => sys_config('wechat_open_app_id'),
            'secret' => sys_config('wechat_open_app_secret'),
        ];
    }

    /**
     * 开放平台扫码授权(网站应用 qrconnect 换取 openid/access_token)
     * @return WechatOauth
     */
    protected function oauth(): WechatOauth
    {
        $options = $this->options();
        $oauth = new WechatOauth(trim((string) $options['app_id']), trim((string) $options['secret']));
        $oauth->setRequest(Support::symfonyRequest());
        return $oauth;
    }

    /**
     * 开放平台消息推送(接收 component_verify_ticket 等)
     * @return Response
     */
    public function serve(): Response
    {
        $app = (new \crmeb\services\easywechat\Application($this->options()))->getOpenPlatformApp();
        $app->setRequestFromSymfonyRequest(Support::symfonyRequest());
        return Support::toThinkResponse($app->getServer()->serve());
    }

    /**
     * 使用授权码换取开放平台用户信息(含 openid/access_token)
     * @return array
     */
    public function getAuthorizationInfo(): array
    {
        return $this->oauth()->oauth();
    }

    /**
     * 获取授权方的帐号基本信息
     * @return array
     */
    public function getAuthorizerInfo(): array
    {
        return $this->oauth()->oauth();
    }
}
