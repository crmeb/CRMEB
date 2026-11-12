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

namespace app\api\middleware;

use crmeb\exceptions\ApiException;
use crmeb\interfaces\MiddlewareInterface;
use crmeb\utils\JwtAuth;
use think\facade\Env;
use think\Request;

/**
 * 定时任务外部触发密钥校验中间件
 * /api/crontab/* 供服务器 crontab 免登录触发。
 * 优先使用独立 CRONTAB_KEY（与 JWT 的 APP_KEY 分离，降低一钥失陷面）；
 * 未配置时回退 APP_KEY（兼容旧部署）。可选 CRONTAB_ALLOW_IPS 限制来源 IP。
 * Class CrontabKeyMiddleware
 * @package app\api\middleware
 */
class CrontabKeyMiddleware implements MiddlewareInterface
{
    /**
     * @param Request $request
     * @param \Closure $next
     * @return mixed
     * @throws ApiException
     */
    public function handle(Request $request, \Closure $next)
    {
        $key = (string)($request->header('key', '') ?: $request->param('key', ''));
        // 独立定时任务密钥优先；未配置时回退 APP_KEY（请在生产配置 crontab.key）
        $expected = (string)Env::get('crontab.key', '');
        if ($expected === '') {
            $expected = JwtAuth::secretKey();
        }
        if ($key === '' || !hash_equals($expected, $key)) {
            throw new ApiException('定时任务调用密钥错误或缺失', [], 403);
        }

        // 可选来源 IP 白名单（逗号分隔）；配置后非白名单 IP 一律拒绝
        $allowIps = (string)Env::get('crontab.allow_ips', '');
        if ($allowIps !== '') {
            $ip = (string)$request->ip();
            $list = array_values(array_filter(array_map('trim', explode(',', $allowIps)), static fn($v) => $v !== ''));
            if ($list && !in_array($ip, $list, true)) {
                throw new ApiException('定时任务来源 IP 不允许', [], 403);
            }
        }

        return $next($request);
    }
}
