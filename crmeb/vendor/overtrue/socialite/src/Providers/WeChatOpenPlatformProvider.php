<?php

/*
 * This file is part of the overtrue/socialite.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Overtrue\Socialite\Providers;

use Symfony\Component\HttpFoundation\Request;

/**
 * Class WeChatProvider.
 *
 * @see https://open.weixin.qq.com/cgi-bin/showdocument?action=dir_list&t=resource/res_list&verify=1&id=open1419318590&token=&lang=zh_CN [WeChat - 公众开放平台代公众号 OAuth 文档]
 */
class WeChatOpenPlatformProvider extends WeChatProvider
{
    /**
     * Component AppId.
     *
     * @deprecated 2.0 Will be removed in the future
     *
     * @var string
     */
    protected $componentAppId;

    /**
     * Component Access Token.
     *
     * @deprecated 2.0 Will be removed in the future
     *
     * @var string
     */
    protected $componentAccessToken;

    /**
     * @var \EasyWeChat\OpenPlatform\AccessToken|array
     */
    protected $credentials;

    /**
     * {@inheritdoc}.
     */
    protected $scopes = ['snsapi_base'];

    /**
     * Create a new provider instance.
     * (Overriding).
     *
     * @param \Symfony\Component\HttpFoundation\Request  $request
     * @param string                                     $clientId
     * @param \EasyWeChat\OpenPlatform\AccessToken|array $credentials
     * @param string|null                                $redirectUrl
     */
    public function __construct(Request $request, $clientId, $credentials, $redirectUrl = null)
    {
        parent::__construct($request, $clientId, null, $redirectUrl);

        $this->credentials = $credentials;
        if (is_array($credentials)) {
            list($this->componentAppId, $this->componentAccessToken) = $credentials;
        }
    }

    /**
     * {@inheritdoc}.
     */
    public function getCodeFields($state = null)
    {
        $this->with(['component_appid' => $this->componentAppId()]);

        return parent::getCodeFields($state);
    }

    /**
     * {@inheritdoc}.
     */
    protected function getTokenUrl()
    {
        return $this->baseUrl.'/oauth2/component/access_token';
    }

    /**
     * {@inheritdoc}.
     */
    protected function getTokenFields($code)
    {
        return [
            'appid' => $this->clientId,
            'component_appid' => $this->componentAppId(),
            'component_access_token' => $this->componentAccessToken(),
            'code' => $code,
            'grant_type' => 'authorization_code',
        ];
    }

    /**
     * Get component app id.
     *
     * @return string
     */
    protected function componentAppId()
    {
        return $this->componentAppId ?: $this->credentials->getAppId();
    }

    /**
     * Get component access token.
     *
     * @return string
     */
    protected function componentAccessToken()
    {
        return $this->componentAccessToken ?: $this->credentials->getToken();
    }
}
