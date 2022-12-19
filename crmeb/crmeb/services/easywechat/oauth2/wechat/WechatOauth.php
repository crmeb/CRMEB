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

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\FilesystemCache;
use EasyWeChat\Core\AbstractAPI;
use EasyWeChat\Core\AccessToken;
use EasyWeChat\Core\Exceptions\HttpException;
use EasyWeChat\Core\Http;
use EasyWeChat\Support\Collection;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class WechatOauth
 * @package crmeb\services\easywechat\oauth\wechat
 */
class WechatOauth extends AbstractAPI
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
    protected $appId;

    /**
     * App secret.
     *
     * @var string
     */
    protected $secret;

    /**
     * Cache.
     *
     * @var Cache
     */
    protected $cache;

    protected $openid;

    /**
     * @var Request
     */
    protected $request;

    /**
     * Query name.
     *
     * @var string
     */
    protected $queryName = 'access_token';

    /**
     * Response Json key name.
     *
     * @var string
     */
    protected $tokenJsonKey = 'access_token';


    /**
     * Response Json key name.
     *
     * @var string
     */
    protected $refreshTokenJsonKey = 'refresh_token';

    /**
     * Cache key prefix.
     *
     * @var string
     */
    protected $prefix = 'easywechat.common.oauth.access_token.';

    /**
     * WechatOauth constructor.
     * @param AccessToken $accessToken
     * @param $appId
     * @param $appSecret
     */
    public function __construct(AccessToken $accessToken, $appId, $appSecret)
    {
        parent::__construct($accessToken);
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
        return $this->request->get('code');
    }

    /**
     * 授权获取token
     * @param string $code
     * @return false|mixed
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

        $http = new Http();

        $token = $http->parseJSON($http->get(self::API_OAUTH_ACCESS_TOKEN, $params));

        if (empty($token[$this->tokenJsonKey])) {
            throw new HttpException('Request AccessToken fail. response: ' . json_encode($token, JSON_UNESCAPED_UNICODE));
        }
        $this->setCache($token);

        return $token;
    }

    /**
     * 刷新token
     * @param string $refresh_token
     * @return false|mixed
     * @throws HttpException
     */
    public function refreshToken(string $refresh_token)
    {
        $params = [
            'appid' => $this->appId,
            'refresh_token' => $refresh_token,
            'grant_type' => 'refresh_token',
        ];

        $http = new Http();

        $token = $http->parseJSON($http->get(self::API_OAUTH_REFRESH_TOKEN, $params));

        if (empty($token[$this->tokenJsonKey])) {
            throw new HttpException('Request AccessToken fail. response: ' . json_encode($token, JSON_UNESCAPED_UNICODE));
        }
        $this->setCache($token);

        return $token;
    }

    /**
     * 获取用户信息
     * @param $openId
     * @param string $lang
     * @return Collection|null
     * @throws HttpException
     */
    public function getUserInfo($openId, $lang = 'zh_CN')
    {
        $params = [
            'openid' => $openId,
            'lang' => $lang,
        ];
        $this->openid = $openId;
        return $this->parseJSON('get', [self::API_OAUTH_GET_USER_INFO, $params]);
    }

    /**
     * 获取token
     * @param false $forceRefresh
     * @return bool|mixed|string
     * @throws HttpException
     */
    public function getToken($forceRefresh = false)
    {
        $cacheKey = $this->prefix;
        $cached = $this->getCache()->fetch($cacheKey . $this->tokenJsonKey . $this->openid);

        if ($forceRefresh || !$cached) {
            $refreshCached = $this->getCache()->fetch($cacheKey . $this->refreshTokenJsonKey . $this->openid);
            if ($refreshCached) {
                $token = $this->refreshToken($refreshCached);

                return $token[$this->tokenJsonKey];
            }
            return '';
        }

        return $cached;
    }

    /**
     * 保存token信息
     * @param $token
     * @return bool
     */
    public function setCache($token)
    {
        $cacheKey = $this->prefix;
        // XXX: T_T... 7200 - 1500
        $this->getCache()->save($cacheKey . $this->tokenJsonKey . $token['openid'], $token[$this->tokenJsonKey], $token['expires_in'] - 1500);
        $this->getCache()->save($cacheKey . $this->refreshTokenJsonKey . $token['openid'], $token[$this->refreshTokenJsonKey], 30 * 24 * 3600);
        return true;
    }

    /**
     * Return the cache manager.
     *
     * @return \Doctrine\Common\Cache\Cache
     */
    public function getCache()
    {
        return $this->cache ?: $this->cache = new FilesystemCache(sys_get_temp_dir());
    }

    /**
     * Attache access token to request query.
     *
     * @return \Closure
     */
    protected function accessTokenMiddleware()
    {
        return function (callable $handler) {
            return function (RequestInterface $request, array $options) use ($handler) {
                $token = $this->getToken();
                if (!$token) {
                    return $handler($request, $options);
                }

                $request = $request->withUri(Uri::withQueryValue($request->getUri(), $this->queryName, $token));

                return $handler($request, $options);
            };
        };
    }


}
