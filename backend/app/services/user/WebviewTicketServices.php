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

namespace app\services\user;

use crmeb\basic\BaseService;
use crmeb\exceptions\ApiException;
use crmeb\services\CacheService;

/**
 * 小程序 webview 打开插件 H5 时的一次性登录票据
 */
class WebviewTicketServices extends BaseService
{
    /** 票据有效期（秒） */
    private const TTL = 120;

    /**
     * 为已登录用户签发一次性票据
     * @param int $uid
     * @param string $path 插件 H5 路径，只允许 /pages-modules/ 下的页面
     * @return string
     */
    public function issue(int $uid, string $path): string
    {
        if ($uid <= 0) {
            throw new ApiException('请登录');
        }
        $path = $this->normalizePath($path);
        $ticket = bin2hex(random_bytes(16));
        $saved = CacheService::set($this->cacheKey($ticket), [
            'uid' => $uid,
            'path' => $path,
        ], self::TTL);
        if (!$saved) {
            throw new ApiException('票据签发失败');
        }
        return $ticket;
    }

    /**
     * 用票据换取用户 token，票据立即失效
     * @param string $ticket
     * @param string $path 必须与签发时的插件路径一致
     * @return array{token: string, expires_time: int}
     */
    public function exchange(string $ticket, string $path): array
    {
        if (!preg_match('/^[a-f0-9]{32}$/', $ticket)) {
            throw new ApiException('票据无效');
        }
        $path = $this->normalizePath($path);
        $cached = CacheService::get($this->cacheKey($ticket));
        CacheService::delete($this->cacheKey($ticket));
        $cachedPath = is_array($cached) ? (string)($cached['path'] ?? '') : '';
        $uid = is_array($cached) ? (int)($cached['uid'] ?? 0) : 0;
        if ($uid <= 0 || $cachedPath === '' || strlen($cachedPath) !== strlen($path) || !hash_equals($cachedPath, $path)) {
            throw new ApiException('票据无效或已使用');
        }

        /** @var LoginServices $loginServices */
        $loginServices = app()->make(LoginServices::class);
        $token = $loginServices->createToken($uid, 'api');
        if (!$token || empty($token['token'])) {
            throw new ApiException('登录失败');
        }
        return [
            'token' => $token['token'],
            'expires_time' => (int)($token['params']['exp'] ?? 0),
        ];
    }

    /**
     * 只接受插件分包页面，避免票据被拿去打开任意网址
     * @param string $path
     * @return string
     */
    public function normalizePath(string $path): string
    {
        $path = trim($path);
        if ($path === '' || strpbrk($path, "\r\n\\") !== false) {
            throw new ApiException('插件页面路径不正确');
        }
        $decoded = rawurldecode($path);
        $parts = explode('?', $decoded, 2);
        $pathname = $parts[0];
        $query = $parts[1] ?? '';
        if (!preg_match('#^/pages-modules/[a-z0-9_]+(?:/[a-z0-9_]+)+$#', $pathname)) {
            throw new ApiException('插件页面路径不正确');
        }
        if ($query !== '' && (str_contains($query, 'ticket=') || !preg_match('/^[A-Za-z0-9_\-=.&%]*$/', $query))) {
            throw new ApiException('插件页面参数不正确');
        }
        return $query === '' ? $pathname : $pathname . '?' . $query;
    }

    private function cacheKey(string $ticket): string
    {
        return 'webview_ticket_' . $ticket;
    }
}
