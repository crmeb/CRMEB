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

use Overtrue\Socialite\AccessToken;
use Overtrue\Socialite\AccessTokenInterface;
use Overtrue\Socialite\InvalidArgumentException;
use Overtrue\Socialite\ProviderInterface;
use Overtrue\Socialite\User;
use Overtrue\Socialite\WeChatComponentInterface;

/**
 * Class WeChatProvider.
 *
 * @see http://mp.weixin.qq.com/wiki/9/01f711493b5a02f24b04365ac5d8fd95.html [WeChat - 公众平台OAuth文档]
 * @see https://open.weixin.qq.com/cgi-bin/showdocument?action=dir_list&t=resource/res_list&verify=1&id=open1419316505&token=&lang=zh_CN [网站应用微信登录开发指南]
 */
class WeChatProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * The base url of WeChat API.
     *
     * @var string
     */
    protected $baseUrl = 'https://api.weixin.qq.com/sns';

    /**
     * {@inheritdoc}.
     */
    protected $openId;

    /**
     * {@inheritdoc}.
     */
    protected $scopes = ['snsapi_login'];

    /**
     * Indicates if the session state should be utilized.
     *
     * @var bool
     */
    protected $stateless = true;

    /**
     * Return country code instead of country name.
     *
     * @var bool
     */
    protected $withCountryCode = false;

    /**
     * @var WeChatComponentInterface
     */
    protected $component;

    /**
     * Return country code instead of country name.
     *
     * @return $this
     */
    public function withCountryCode()
    {
        $this->withCountryCode = true;

        return $this;
    }

    /**
     * WeChat OpenPlatform 3rd component.
     *
     * @param WeChatComponentInterface $component
     *
     * @return $this
     */
    public function component(WeChatComponentInterface $component)
    {
        $this->scopes = ['snsapi_base'];

        $this->component = $component;

        return $this;
    }

    /**
     * {@inheritdoc}.
     */
    public function getAccessToken($code)
    {
        $response = $this->getHttpClient()->get($this->getTokenUrl(), [
            'headers' => ['Accept' => 'application/json'],
            'query' => $this->getTokenFields($code),
        ]);

        return $this->parseAccessToken($response->getBody());
    }

    /**
     * {@inheritdoc}.
     */
    protected function getAuthUrl($state)
    {
        $path = 'oauth2/authorize';

        if (in_array('snsapi_login', $this->scopes)) {
            $path = 'qrconnect';
        }

        return $this->buildAuthUrlFromBase("https://open.weixin.qq.com/connect/{$path}", $state);
    }

    /**
     * {@inheritdoc}.
     */
    protected function buildAuthUrlFromBase($url, $state)
    {
        $query = http_build_query($this->getCodeFields($state), '', '&', $this->encodingType);

        return $url.'?'.$query.'#wechat_redirect';
    }

    /**
     * {@inheritdoc}.
     */
    protected function getCodeFields($state = null)
    {
        if ($this->component) {
            $this->with(['component_appid' => $this->component->getAppId()]);
        }

        return array_merge([
            'appid' => $this->clientId,
            'redirect_uri' => $this->redirectUrl,
            'response_type' => 'code',
            'scope' => $this->formatScopes($this->scopes, $this->scopeSeparator),
            'state' => $state ?: md5(time()),
        ], $this->parameters);
    }

    /**
     * {@inheritdoc}.
     */
    protected function getTokenUrl()
    {
        if ($this->component) {
            return $this->baseUrl.'/oauth2/component/access_token';
        }

        return $this->baseUrl.'/oauth2/access_token';
    }

    /**
     * {@inheritdoc}.
     */
    protected function getUserByToken(AccessTokenInterface $token)
    {
        $scopes = explode(',', $token->getAttribute('scope', ''));

        if (in_array('snsapi_base', $scopes)) {
            return $token->toArray();
        }

        if (empty($token['openid'])) {
            throw new InvalidArgumentException('openid of AccessToken is required.');
        }

        $language = $this->withCountryCode ? null : (isset($this->parameters['lang']) ? $this->parameters['lang'] : 'zh_CN');

        $response = $this->getHttpClient()->get($this->baseUrl.'/userinfo', [
            'query' => array_filter([
                'access_token' => $token->getToken(),
                'openid' => $token['openid'],
                'lang' => $language,
            ]),
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * {@inheritdoc}.
     */
    protected function mapUserToObject(array $user)
    {
        return new User([
            'id' => $this->arrayItem($user, 'openid'),
            'name' => $this->arrayItem($user, 'nickname'),
            'nickname' => $this->arrayItem($user, 'nickname'),
            'avatar' => $this->arrayItem($user, 'headimgurl'),
            'email' => null,
        ]);
    }

    /**
     * {@inheritdoc}.
     */
    protected function getTokenFields($code)
    {
        return array_filter([
            'appid' => $this->clientId,
            'secret' => $this->clientSecret,
            'component_appid' => $this->component ? $this->component->getAppId() : null,
            'component_access_token' => $this->component ? $this->component->getToken() : null,
            'code' => $code,
            'grant_type' => 'authorization_code',
        ]);
    }

    /**
     * Remove the fucking callback parentheses.
     *
     * @param mixed $response
     *
     * @return string
     */
    protected function removeCallback($response)
    {
        if (strpos($response, 'callback') !== false) {
            $lpos = strpos($response, '(');
            $rpos = strrpos($response, ')');
            $response = substr($response, $lpos + 1, $rpos - $lpos - 1);
        }

        return $response;
    }
}
