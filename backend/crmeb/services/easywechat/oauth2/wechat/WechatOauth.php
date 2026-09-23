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
declare(strict_types=1);

namespace crmeb\services\easywechat\oauth2\wechat;

use EasyWeChat\Kernel\Exceptions\HttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * 微信网页授权(sns)
 *
 * 独立于 EasyWeChat 应用实例:sns 接口不走普通 access_token,
 * 而是使用授权返回的 sns access_token(按 openid 缓存)。
 *
 * 历史修复:旧实现把 sns token 存进 Doctrine Cache 接口
 * (think Cache 无 fetch/save 方法,H5 授权必然 fatal),
 * 现统一使用 PSR-16 缓存存取。
 *
 * Class WechatOauth
 * @package crmeb\services\easywechat\oauth2\wechat
 */
class WechatOauth
{
    /**
     * 通过code获取网页授权access_token
     */
    const API_OAUTH_ACCESS_TOKEN = 'https://api.weixin.qq.com/sns/oauth2/access_token';

    /**
     * 检验授权凭证（access_token）是否有效
     */
    const API_OAUTH_CHECK_TOKEN = 'https://api.weixin.qq.com/sns/auth';

    /**
     * 刷新access_token
     */
    const API_OAUTH_REFRESH_TOKEN = 'https://api.weixin.qq.com/sns/oauth2/refresh_token';

    /**
     * 获取用户信息
     */
    const API_OAUTH_GET_USER_INFO = 'https://api.weixin.qq.com/sns/userinfo';

    /**
     * App ID.
     *
     * @var string
     */
    protected string $appId;

    /**
     * App secret.
     *
     * @var string
     */
    protected string $secret;

    protected string $openid = '';

    /**
     * @var Request|null
     */
    protected ?Request $request = null;

    /**
     * Response Json key name.
     *
     * @var string
     */
    protected string $tokenJsonKey = 'access_token';

    /**
     * Response Json key name.
     *
     * @var string
     */
    protected string $refreshTokenJsonKey = 'refresh_token';

    /**
     * Cache key prefix.
     *
     * @var string
     */
    protected string $prefix = 'easywechat.common.oauth.access_token.';

    protected ?HttpClientInterface $httpClient = null;

    public function __construct(string $appId = '', string $appSecret = '')
    {
        $this->appId = $appId;
        $this->secret = $appSecret;
    }

    /**
     * @param Request $request
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * 获取code
     * @return mixed
     */
    public function getCode()
    {
        return $this->request?->get('code');
    }

    /**
     * 授权获取token
     * @param string $code
     * @return array
     * @throws HttpException
     */
    public function oauth(string $code = '')
    {
        $params = [
            'appid' => $this->appId,
            'secret' => $this->secret,
            'code' => $code ?: $this->getCode(),
            'grant_type' => 'authorization_code',
        ];

        $token = $this->httpGet(self::API_OAUTH_ACCESS_TOKEN, $params);

        if (empty($token[$this->tokenJsonKey])) {
            throw new HttpException('Request AccessToken fail. response: ' . json_encode($token, JSON_UNESCAPED_UNICODE));
        }
        $this->setCache($token);

        return $token;
    }

    /**
     * 刷新token
     * @param string $refresh_token
     * @return array
     * @throws HttpException
     */
    public function refreshToken(string $refresh_token)
    {
        $params = [
            'appid' => $this->appId,
            'refresh_token' => $refresh_token,
            'grant_type' => 'refresh_token',
        ];

        $token = $this->httpGet(self::API_OAUTH_REFRESH_TOKEN, $params);

        if (empty($token[$this->tokenJsonKey])) {
            throw new HttpException('Request AccessToken fail. response: ' . json_encode($token, JSON_UNESCAPED_UNICODE));
        }
        $this->setCache($token);

        return $token;
    }

    /**
     * 获取用户信息(使用授权时缓存的 sns access_token)
     * @param string $openId
     * @param string $lang
     * @return array
     */
    public function getUserInfo($openId, $lang = 'zh_CN')
    {
        $this->openid = $openId;
        $token = $this->getToken();

        return $this->httpGet(self::API_OAUTH_GET_USER_INFO, [
            'access_token' => $token,
            'openid' => $openId,
            'lang' => $lang,
        ]);
    }

    /**
     * 获取指定 openid 的 sns access_token(优先缓存,无缓存时用 refresh_token 换新)
     * @param bool $forceRefresh
     * @return string
     */
    public function getToken(bool $forceRefresh = false)
    {
        $cacheKey = $this->prefix;
        $cached = app('cache')->get($cacheKey . $this->tokenJsonKey . $this->openid);

        if ($forceRefresh || !$cached) {
            $refreshCached = app('cache')->get($cacheKey . $this->refreshTokenJsonKey . $this->openid);
            if ($refreshCached) {
                $token = $this->refreshToken($refreshCached);

                return $token[$this->tokenJsonKey];
            }
            return '';
        }

        return $cached;
    }

    /**
     * 缓存 token 信息(sns access_token / refresh_token)
     * @param array $token
     * @return bool
     */
    public function setCache(array $token)
    {
        $cacheKey = $this->prefix;
        // 授权 token 有效期 7200s,提前 1500s 过期
        app('cache')->set($cacheKey . $this->tokenJsonKey . $token['openid'], $token[$this->tokenJsonKey], $token['expires_in'] - 1500);
        app('cache')->set($cacheKey . $this->refreshTokenJsonKey . $token['openid'], $token[$this->refreshTokenJsonKey], 30 * 24 * 3600);
        return true;
    }

    /**
     * GET 请求 sns 接口,统一返回数组
     * @param string $url
     * @param array $query
     * @return array
     */
    protected function httpGet(string $url, array $query): array
    {
        $response = $this->httpClient()->request('GET', $url, ['query' => $query]);

        try {
            $data = $response->toArray(false);
        } catch (\Throwable $e) {
            $data = [];
        }

        return is_array($data) ? $data : [];
    }

    protected function httpClient(): HttpClientInterface
    {
        return $this->httpClient ??= \Symfony\Component\HttpClient\HttpClient::create([
            'timeout' => 30,
        ]);
    }
}
