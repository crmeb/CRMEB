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

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use think\facade\Log;

/**
 * 打印机专用的安全 HTTP 传输
 */
class PrinterHttp
{
    /** @var ClientInterface 校验证书的请求客户端 */
    protected ClientInterface $httpClient;

    /**
     * 初始化打印请求客户端
     * @param ClientInterface|null $httpClient 请求客户端
     * @return void
     */
    public function __construct(?ClientInterface $httpClient = null)
    {
        $this->httpClient = $httpClient ?? new Client();
    }

    /**
     * 发送有界的 HTTPS 表单请求
     * @param string $url 打印服务接口
     * @param array $data 表单数据
     * @param array|false $header 请求头
     * @param int $timeout 请求超时秒数
     * @return string|false
     */
    public function postRequest(string $url, array $data = [], array|false $header = false, int $timeout = 10)
    {
        $parts = parse_url($url);
        if (!is_array($parts) || ($parts['scheme'] ?? '') !== 'https' || empty($parts['host'])
            || isset($parts['user']) || isset($parts['pass']) || isset($parts['fragment'])) {
            return false;
        }

        $timeout = max(1, min(30, $timeout));
        try {
            $response = $this->httpClient->request('POST', $url, [
                'form_params' => $data,
                'headers' => $header === false ? [] : $header,
                'verify' => true,
                'allow_redirects' => false,
                'http_errors' => false,
                'connect_timeout' => min(5, $timeout),
                'timeout' => $timeout,
                'version' => 1.1,
            ]);
            if ($response->getStatusCode() !== 200) {
                Log::error('打印服务 HTTP 请求失败，状态码：' . $response->getStatusCode());
                return false;
            }
            return (string)$response->getBody();
        } catch (GuzzleException $e) {
            // HTTP 异常可能携带凭据、token 和小票内容，只记录异常类型。
            Log::error('打印服务 HTTP 请求失败，异常类型：' . get_class($e));
            return false;
        }
    }

    /**
     * 解析打印服务返回的非空 JSON 对象
     * @param string|false $response 接口响应
     * @return array|null
     */
    public function decodeResponse($response): ?array
    {
        if (!is_string($response) || $response === '') {
            return null;
        }
        try {
            $decoded = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            Log::warning('打印服务返回无效 JSON');
            return null;
        }
        return is_array($decoded) && !array_is_list($decoded) ? $decoded : null;
    }
}
