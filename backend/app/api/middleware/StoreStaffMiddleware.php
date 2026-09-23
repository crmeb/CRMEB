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

use app\services\system\store\SystemStoreStaffServices;
use crmeb\interfaces\MiddlewareInterface;
use think\Request;

/**
 * 核销员身份校验中间件（系统内置）
 */
class StoreStaffMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, \Closure $next)
    {
        $uid = $request->uid();
        /** @var SystemStoreStaffServices $storeServices */
        $storeServices = app()->make(SystemStoreStaffServices::class);
        if (!$storeServices->verifyStatus($uid)) {
            return app('json')->fail('权限不足');
        }
        return $next($request);
    }
}
