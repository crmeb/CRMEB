<?php
namespace app\adminapi\middleware;

use crmeb\exceptions\AdminException;
use crmeb\interfaces\MiddlewareInterface;
use crmeb\services\CacheService;
use think\Request;

/** 匿名诊断入口仅限制创建频率，不要求登录或密钥。 */
class QueueProbeThrottleMiddleware implements MiddlewareInterface
{
    /**
     * 全站每 60 秒允许一次创建，查询不占用创建额度。
     * @param Request $request 请求
     * @param \Closure $next 后续处理
     * @return mixed
     */
    public function handle(Request $request, \Closure $next)
    {
        // 固定限流键保留至窗口结束，由 setMutex 按时间自动允许下次创建。
        if (!CacheService::setMutex('system:queue_probe:create', 60)) {
            throw new AdminException('测试创建过于频繁，请 60 秒后重试');
        }
        return $next($request);
    }
}
