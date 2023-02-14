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

namespace crmeb\services\easywechat\open3rd;


use crmeb\exceptions\ApiException;
use crmeb\services\HttpService;

/**
 * Class AccessTokenServeService
 * @package crmeb\services
 */
class AccessToken extends HttpService
{
    /**
     * 第三方平台 appid
     * @var string
     */
    protected $component_appid;

    /**
     * 第三方平台 appsecret
     * @var string
     */
    protected $component_appsecret;

    /**
     * 微信后台推送的 ticket
     * @var string
     */
    protected $component_verify_ticket;

    /**
     * @var Cache|null
     */
    protected $cache;

    /**
     * 第三方平台token
     * @var string
     */
    protected $component_access_token;

    /**
     * 授权方appid
     * @var string
     */
    protected $authorizer_appid;

    /**
     * 接口调用令牌（在授权的公众号/小程序具备 API 权限时，才有此返回值）
     * @var string
     */
    protected $authorizer_access_token;

    /**
     * 刷新令牌（在授权的公众号具备API权限时，才有此返回值），刷新令牌主要用于第三方平台获取和刷新已授权用户的 authorizer_access_token。一旦丢失，只能让用户重新授权，才能再次拿到新的刷新令牌。用户重新授权后，之前的刷新令牌会失效
     * @var string
     */
    protected $authorizer_refresh_token;

    /**
     * @var string
     */
    protected $cacheTokenPrefix = "component_access_token_crmeb";

    /**
     * 获取第三方平台token
     * @var string
     */
    const TOKEN_URL = 'https://api.weixin.qq.com/cgi-bin/component/api_component_token';

    /**
     * 使用授权码获取授权信息
     */
    const AUTH_INFO = 'https://api.weixin.qq.com/cgi-bin/component/api_query_auth';
    /**
     * 获取、刷新接口调用token
     */
    const AUTHORIZER_TOKEN = 'https://api.weixin.qq.com/cgi-bin/component/api_authorizer_token';

    /**
     * AccessTokenServeService constructor.
     * @param string $component_appid
     * @param string $component_appsecret
     * @param string $component_verify_ticket
     * @param string $authorizer_appid
     * @param Cache|null $cache
     */
    public function __construct(string $component_appid, string $component_appsecret, string $component_verify_ticket, string $authorizer_appid = '', $cache = null)
    {
        if (!$cache) {
            $cache = app()->make(\crmeb\services\CacheService::class);
        }
        $this->component_appid = $component_appid;
        $this->component_appsecret = $component_appsecret;
        $this->component_verify_ticket = $component_verify_ticket;
        $this->authorizer_appid = $authorizer_appid;
        $this->cache = $cache;
    }

    /**
     * 获取配置
     * @return array
     */
    public function getConfig()
    {
        return [
            'component_appid' => $this->component_appid,
            'component_appsecret' => $this->component_appsecret,
            'component_verify_ticket' => $this->component_verify_ticket,
        ];
    }

    /**
     * @return string
     */
    public function getComponentAppid()
    {
        return $this->component_appid;
    }

    /**
     * @return string
     */
    public function getComponentAppsecret()
    {
        return $this->component_appsecret;
    }

    /**
     * @return string
     */
    public function getAuthorizerAppid()
    {
        return $this->authorizer_appid;
    }

    /**
     * 获取第三方缓存token
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getComponentToken()
    {
        $accessTokenKey = md5($this->component_appid . '_' . $this->component_appid . '_' . $this->component_verify_ticket . '_' . $this->cacheTokenPrefix);
        $component_access_token = $this->cache->get($accessTokenKey);
        if (!$component_access_token) {
            $getToken = $this->getTokenFromServer();
            $this->cache->set($accessTokenKey, $getToken['component_access_token'], $getToken['expires_in'] ? $getToken['expires_in'] - 200 : 7000);
            $component_access_token = $getToken['component_access_token'];
        }
        $this->component_access_token = $component_access_token;

        return $component_access_token;

    }

    /**
     * 从服务器获取token
     * @return mixed
     */
    public function getTokenFromServer()
    {
        $config = $this->getConfig();
        if (!$config['component_appid'] || !$config['component_appsecret']) {
            throw new ApiException('请先配置第三方component_appid、component_appsecret');
        }
        if (!$config['component_verify_ticket']) {
            throw new ApiException('未配置微信开放平台或者未收到推送ticket,请等待10分钟后再试');
        }
        $res = $this->postRequest(self::TOKEN_URL, $config);
        $res = json_decode($res, true);
        if (!$res || $res['errcode'] != 0 || !isset($res['component_access_token']) || !$res['component_access_token']) {
            throw new ApiException('获取component_access_token失败,原因：' . $res['errmsg']);
        }
        return $res;
    }

    /**
     * 获取授权方token
     * @param $authorizer_appid
     * @return mixed
     */
    public function getAccessToken($authorizer_appid)
    {
        $accessTokenKey = md5('authorizer_access_token' . $authorizer_appid . '_' . $this->cacheTokenPrefix);
        $authorizer_access_token = $this->cache->get($accessTokenKey);
        if (!$authorizer_access_token) {
            $refreshTokenKey = md5('authorizer_refresh_token' . $authorizer_appid . '_' . $this->cacheTokenPrefix);
            $authorizer_refresh_token = $this->cache->get($refreshTokenKey);
            if (!$authorizer_refresh_token) {
                throw new ApiException('请重新授权');
            }
            $res = $this->freshAuthorizationToken($authorizer_appid, $authorizer_refresh_token);
            $this->cache->set(md5('authorizer_access_token' . $res['authorizer_appid'] . '_' . $this->cacheTokenPrefix), $res['authorizer_access_token'], $res['expires_in'] ? $res['expires_in'] - 200 : 7000);
            $this->cache->set(md5('authorizer_refrssh_token' . $res['authorizer_appid'] . '_' . $this->cacheTokenPrefix), $res['authorizer_refresh_token'], 30 * 24 * 3600);
            $authorizer_access_token = $res['authorizer_access_token'];
        }
        return $authorizer_access_token;
    }

    /**
     * 获取授权信息
     * @param $authorization_code 授权码
     * @return authorizer_appid    string    授权方 appid
     * @return authorizer_access_token string    接口调用令牌（在授权的公众号/小程序具备 API 权限时，才有此返回值）
     * @return authorizer_refresh_token    string    刷新令牌（在授权的公众号具备API权限时，才有此返回值），刷新令牌主要用于第三方平台获取和刷新已授权用户的 authorizer_access_token。一旦丢失，只能让用户重新授权，才能再次拿到新的刷新令牌。用户重新授权后，之前的刷新令牌会失效
     * @return array|bool|mixed
     */
    public function getAuthorizationInfo($authorization_code)
    {
        $res = $this->httpRequest(self::AUTH_INFO, ['authorization_code' => $authorization_code], false);
        if (!$res || $res['errcode'] != 0 || !isset($res['authorization_info']) || !$res['authorization_info']) {
            throw new ApiException('获取authorizer_access_token失败');
        }
        $res = $res['authorization_info'];
        $this->cache->set(md5('authorizer_access_token' . $res['authorizer_appid'] . '_' . $this->cacheTokenPrefix), $res['authorizer_access_token'], $res['expires_in'] ? $res['expires_in'] - 200 : 7000);
        //在授权的公众号具备API权限时，才有此返回值
        if (isset($res['authorizer_refrsh_token'])) {
            $this->cache->set(md5('authorizer_refresh_token' . $res['authorizer_appid'] . '_' . $this->cacheTokenPrefix), $res['authorizer_refrsh_token'], 30 * 24 * 3600);
        }
        return $res;
    }

    /**
     *  获取/刷新接口调用令牌
     * @param $authorizer_appid 授权方appid
     * @param $authorizer_refresh_token 刷新令牌，获取授权信息时得到
     * @return authorizer_access_token    string    授权方令牌
     * @return authorizer_refresh_token    string    刷新令牌
     * @return array|bool|mixed
     */
    public function freshAuthorizationToken($authorizer_appid, $authorizer_refresh_token)
    {
        $res = $this->postRequest(self::AUTHORIZER_TOKEN, ['component_access_token' => $this->component_access_token, 'authorizer_appid' => $authorizer_appid, 'authorizer_refresh_token' => $authorizer_refresh_token]);
        if (!$res || $res['errcode'] != 0 || !isset($res['authorizer_access_token']) || !$res['authorizer_access_token']) {
            throw new ApiException('刷新authorizer_access_token失败,请重新获取授权');
        }
        return $res;
    }


    /**
     * 请求
     * @param string $url
     * @param array $data
     * @param string $method
     * @param bool $isHeader
     * @return array|mixed
     */
    public function httpRequest(string $url, array $data = [], bool $is_atuh = true, string $method = 'POST')
    {
        if (!$is_atuh) {
            $this->getComponentToken();
            if (!$this->component_access_token) {
                throw new ApiException('配置已更改或component_access_token已失效');
            }
            $url .= '?component_access_token=' . $this->component_access_token;
            $data = array_merge($data, ['component_appid' => $this->component_appid]);
        } else {
            if (!$this->authorizer_appid) {
                throw new ApiException('缺少授权方authorizer_appid');
            }
            $access_token = $this->getAccessToken($this->authorizer_appid);
            if (!$access_token) {
                throw new ApiException('配置已更改或授权已失效请重新授权');
            }
            $url .= '?access_token=' . $access_token;
        }
        $res = $this->request($url, $method, $data);
        if (!$res) {
            throw new ApiException('请求微信服务器发生异常，请稍后重试');
        }
        return json_decode($res, true) ?: false;
    }

    /**
     * XML数据转换成array数组
     * @param string $xml
     * @return array
     */
    public function xmlToArray($xml)
    {
        // 禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $res = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        return (array)$res;
    }

    /**
     * 对解密后的明文进行补位删除
     * @param decrypted 解密后的明文
     * @return 删除填充补位后的明文
     */
    public function decode($text)
    {

        $pad = ord(substr($text, -1));
        if ($pad < 1 || $pad > 32) {
            $pad = 0;
        }
        return substr($text, 0, (strlen($text) - $pad));
    }

    /**
     * 对密文进行解密
     * @param string $encodingAesKey 解密
     * @param string $encrypted 需要解密的密文
     * @return string 解密得到的明文
     */
    public function decrypt($encodingAesKey, $encrypted)
    {
        try {
            //使用BASE64对需要解密的字符串进行解码
            $ciphertext_dec = base64_decode($encrypted);
            $iv = substr(base64_decode($encodingAesKey . "="), 0, 16);
            $decrypted = openssl_decrypt($ciphertext_dec, 'AES-256-CBC', $this->key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);
        } catch (\Throwable $e) {
            throw new ApiException($e->getMessage());
        }
        try {
            //去除补位字符
            $result = $this->decode($decrypted);
            //去除16位随机字符串,网络字节序和AppId
            if (strlen($result) < 16)
                return "";
            $content = substr($result, 16, strlen($result));
            $len_list = unpack("N", substr($content, 0, 4));
            $xml_len = $len_list[1];
            $xml_content = substr($content, 4, $xml_len);
        } catch (\Throwable $e) {
            throw new ApiException($e->getMessage());
        }
        return $xml_content;
    }
}
