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
namespace crmeb\services\printer;


use crmeb\services\PluginCacheService;
use GuzzleHttp\ClientInterface;
use think\helper\Str;

/**
 * 打印机凭据与易联云授权管理
 */
class AccessToken extends PrinterHttp
{

    /**
     * token
     * @var array<string, string>
     */
    protected array $accessToken = [];

    /** @var array<string, int> 本实例授权的过期时间 */
    protected array $accessTokenExpire = [];

    /**
     * 请求接口
     * @var string
     */
    protected $apiUrl;

    /**
     * @var string
     */
    protected $clientId;

    /**
     * 终端号码
     * @var string
     */
    protected $machineCode;

    /**
     * 开发者id
     * @var string
     */
    protected $partner;

    /**
     * 驱动类型
     * @var string
     */
    protected $name;

    /**
     * 配置文件名
     * @var string
     */
    protected $configFile;

    /**
     * api key
     * @var string
     */
    protected $apiKey;

    /**
     * 飞鹅云SN
     * @var string
     */
    protected $feySn;

    /**
     * 飞鹅云UYEK
     * @var string
     */
    protected $feyUkey;

    /**
     * 飞鹅云USER
     * @var string
     */
    protected $feyUser;

    /**
     * 初始化打印机凭据
     * @param string $name 驱动名称
     * @param string $configFile 配置文件名称
     * @param array $config 驱动配置
     * @param ClientInterface|null $httpClient 请求客户端
     * @return void
     */
    public function __construct(string $name, string $configFile, array $config = [], ?ClientInterface $httpClient = null)
    {
        parent::__construct($httpClient);
        $this->clientId = $this->readConfig($config, 'clientId');
        $this->apiKey = $this->readConfig($config, 'apiKey');
        $this->partner = $this->readConfig($config, 'partner');
        $this->machineCode = $this->readConfig($config, 'terminal');
        $this->feyUser = $this->readConfig($config, 'feyUser');
        $this->feyUkey = $this->readConfig($config, 'feyUkey');
        $this->feySn = $this->readConfig($config, 'feySn');
        $this->name = $name;
        $this->configFile = $configFile;
    }

    /**
     * 获取token
     * @return mixed|null|string
     * @throws \Exception
     */
    public function getAccessToken()
    {
        if (isset($this->accessToken[$this->name]) && ($this->accessTokenExpire[$this->name] ?? 0) > time()) {
            return $this->accessToken[$this->name];
        }

        $action = 'get' . Str::studly($this->name) . 'AccessToken';
        if (method_exists($this, $action)) {
            return $this->{$action}();
        } else {
            throw new \RuntimeException(getLang('打印驱动不支持授权获取'));
        }
    }

    /**
     * 获取易联云token
     * @return mixed|null|string
     * @throws \Exception
     */
    protected function getYiLianYunAccessToken()
    {
        if ($this->clientId === '' || $this->apiKey === '') {
            throw new \RuntimeException(getLang('易联云授权配置不完整'));
        }
        $cacheKey = $this->getAccessTokenCacheKey();
        $cached = PluginCacheService::get($cacheKey, null);
        if (is_array($cached) && is_string($cached['access_token'] ?? null) && trim($cached['access_token']) !== ''
            && is_int($cached['expires_at'] ?? null) && $cached['expires_at'] > time()) {
            $this->accessTokenExpire[$this->name] = $cached['expires_at'];
            return $this->accessToken[$this->name] = $cached['access_token'];
        }
        PluginCacheService::delete($cacheKey);

        $timestamp = time();
        $request = $this->decodeResponse($this->postRequest('https://open-api.10ss.net/oauth/oauth', [
            'client_id' => $this->clientId,
            'grant_type' => 'client_credentials',
            'sign' => strtolower(md5($this->clientId . $timestamp . $this->apiKey)),
            'scope' => 'all',
            'timestamp' => $timestamp,
            'id' => $this->createUuid(),
        ]));
        if (!in_array($request['error'] ?? null, [0, '0'], true)
            || !is_array($request['body'] ?? null)
            || !is_string($request['body']['access_token'] ?? null)
            || trim($request['body']['access_token']) === '') {
            throw new \RuntimeException(getLang('易联云授权获取失败'));
        }

        // 提前失效以留出时钟误差；供应商未给期限时仍使用有限缓存，避免永久复用旧授权。
        $lifetime = $request['body']['expires_in'] ?? 3600;
        $lifetime = is_int($lifetime) || (is_string($lifetime) && ctype_digit($lifetime)) ? (int)$lifetime : 3600;
        $lifetime = $lifetime > 0 ? min(86400, $lifetime) : 3600;
        $ttl = max(1, $lifetime - min(60, (int)floor($lifetime / 10)));
        $expiresAt = time() + $ttl;
        $this->accessTokenExpire[$this->name] = $expiresAt;
        $this->accessToken[$this->name] = $request['body']['access_token'];
        PluginCacheService::set($cacheKey, ['access_token' => $this->accessToken[$this->name], 'expires_at' => $expiresAt], $ttl);
        return $this->accessToken[$this->name];
    }

    /**
     * 精确失效当前应用的授权
     * @return void
     */
    public function invalidateAccessToken(): void
    {
        unset($this->accessToken[$this->name], $this->accessTokenExpire[$this->name]);
        PluginCacheService::delete($this->getAccessTokenCacheKey());
    }

    /**
     * 生成不暴露应用凭据的授权缓存名称
     * @return string
     */
    protected function getAccessTokenCacheKey(): string
    {
        return 'printer:access_token:' . hash('sha256', $this->clientId . "\0" . $this->apiKey);
    }

    /**
     * 读取字符串配置并拒绝复杂类型
     * @param array $config 驱动配置
     * @param string $field 配置字段
     * @return string
     */
    protected function readConfig(array $config, string $field): string
    {
        return is_scalar($config[$field] ?? null) ? trim((string)$config[$field]) : '';
    }

    /**
     * 生成UUID4
     * @return string
     */
    public function createUuid()
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        $hex = bin2hex($bytes);
        return substr($hex, 0, 8) . '-' . substr($hex, 8, 4) . '-' . substr($hex, 12, 4) . '-'
            . substr($hex, 16, 4) . '-' . substr($hex, 20, 12);
    }

    /**
     * 获取属性
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        if ($name === 'terminal') {
            return $this->machineCode;
        }
        if (in_array($name, ['clientId', 'apiKey', 'accessToken', 'partner', 'machineCode', 'feyUser', 'feyUkey', 'feySn'], true)) {
            return $this->{$name};
        }
    }
}
