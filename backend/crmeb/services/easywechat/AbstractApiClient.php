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

namespace crmeb\services\easywechat;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * 业务 API 客户端基类(EasyWeChat 6.x)
 *
 * 持有模块 Application 的 ApiClient($app->getClient(),自动携带 access_token),
 * 统一以数组返回微信响应,错误语义(errcode/errmsg 或 return_code/result_code)
 * 沿用原有约定由调用方判断,不在此处拦截。
 */
abstract class AbstractApiClient
{
    public function __construct(protected HttpClientInterface $api)
    {
    }

    protected function getJson(string $uri, array $query = []): array
    {
        return $this->wrap($this->api->get($uri, $query));
    }

    protected function postJson(string $uri, array $data = []): array
    {
        return $this->wrap($this->api->postJson($uri, $data));
    }

    protected function wrap(ResponseInterface $response): array
    {
        $data = $response->toArray(false);

        return is_array($data) ? $data : [];
    }
}
