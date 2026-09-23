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

namespace crmeb\services\wechat;

use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Request;
use think\Response as ThinkResponse;

/**
 * EasyWeChat 6.x 与 ThinkPHP 的桥接助手
 *
 * 6.x 的 Server(serve()) 返回 PSR-7 响应、请求注入依赖 Symfony Request,
 * 本类提供与 think 两侧的互转,供各 serve/notify 端点复用。
 */
class Support
{
    /**
     * 从当前 PHP 全局变量构建 Symfony Request
     * 用于 6.x $app->setRequestFromSymfonyRequest() 注入
     */
    public static function symfonyRequest(): Request
    {
        return Request::createFromGlobals();
    }

    /**
     * 将 PSR-7 响应(Server::serve() 的返回)转为 think 响应
     */
    public static function toThinkResponse(ResponseInterface $response): ThinkResponse
    {
        $contentType = $response->getHeaderLine('Content-Type');
        // 公众号服务端被动回复为 XML,支付回调为 JSON/纯文本
        $type = str_contains($contentType, 'xml') ? 'xml' : 'html';
        return ThinkResponse::create(
            (string) $response->getBody(),
            $type,
            $response->getStatusCode()
        );
    }
}
