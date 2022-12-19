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

namespace crmeb\services;


use crmeb\exceptions\ApiException;

/**
 * Class AccessTokenServeService
 * @package crmeb\services
 */
class AccessTokenServeService extends HttpService
{
    /**
     * 配置
     * @var string
     */
    protected $account;

    /**
     * @var string
     */
    protected $secret;

    /**
     * @var Cache|null
     */
    protected $cache;

    /**
     * @var string
     */
    protected $accessToken;

    /**
     * @var string
     */
    protected $cacheTokenPrefix = "_crmeb_plat";

    /**
     * @var string
     */
    protected $apiHost = 'http://sms.crmeb.net/api/';

    /**
     * 登录接口
     */
    const USER_LOGIN = "user/login";


    /**
     * AccessTokenServeService constructor.
     * @param string $account
     * @param string $secret
     * @param Cache|null $cache
     */
    public function __construct(string $account, string $secret, $cache = null)
    {
        if (!$cache) {
            /** @var CacheService $cache */
            $cache = app()->make(CacheService::class);
        }
        $this->account = $account;
        $this->secret = $secret;
        $this->cache = $cache;
    }

    /**
     * 获取配置
     * @return array
     */
    public function getConfig()
    {
        return [
            'account' => $this->account,
            'secret' => $this->secret
        ];
    }

    /**
     * 获取缓存token
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getToken()
    {
        $accessTokenKey = md5($this->account . '_' . $this->secret . $this->cacheTokenPrefix);
        $cacheToken = $this->cache->get($accessTokenKey);
        if (!$cacheToken) {
            $getToken = $this->getTokenFromServer();
            $this->cache->set($accessTokenKey, $getToken['access_token'], 300);
            $cacheToken = $getToken['access_token'];
        }
        $this->accessToken = $cacheToken;

        return $cacheToken;

    }

    /**
     * 从服务器获取token
     * @return mixed
     */
    public function getTokenFromServer()
    {
        $params = [
            'account' => $this->account,
            'secret' => $this->secret,
        ];
        $response = $this->postRequest($this->get(self::USER_LOGIN), $params);
        $response = json_decode($response, true);
        if (!$response) {
            throw new ApiException(410085, ['msg' => '']);
        }
        if ($response['status'] === 200) {
            return $response['data'];
        } else {
            throw new ApiException(410085, ['msg' => ':' . $response['msg']]);
        }
    }

    /**
     * 请求
     * @param string $url
     * @param array $data
     * @param string $method
     * @param bool $isHeader
     * @return array|mixed
     */
    public function httpRequest(string $url, array $data = [], string $method = 'POST', bool $isHeader = true)
    {
        $header = [];
        if ($isHeader) {
            $this->getToken();
            if (!$this->accessToken) {
                throw new ApiException(410086);
            }
            $header = ['Authorization:Bearer-' . $this->accessToken];
        }

        $res = $this->request($this->get($url), $method, $data, $header);
        if (!$res) {
            throw new ApiException(410087);

        }
        $result = json_decode($res, true) ?: false;
        if (!isset($result['status']) || $result['status'] != 200) {
            throw new ApiException($result['msg']);
        }
        return $result['data'] ?? [];

    }

    /**
     * @param string $apiUrl
     * @return string
     */
    public function get(string $apiUrl = '')
    {
        return $this->apiHost . $apiUrl;
    }
}
