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

use app\services\personnel\ServicePersonnelServices;
use app\services\system\store\SystemStoreStaffServices;
use crmeb\interfaces\MiddlewareInterface;
use think\Request;

/**
 * 移动端核销权限：核销员或配送员其一即可
 */
class WriteOffAuthMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, \Closure $next)
    {
        $uid = (int)$request->uid();
        if ($uid <= 0) {
            return app('json')->fail('请先登录');
        }

        /** @var SystemStoreStaffServices $storeStaffServices */
        $storeStaffServices = app()->make(SystemStoreStaffServices::class);
        if ($storeStaffServices->verifyStatus($uid)) {
            return $next($request);
        }

        /** @var ServicePersonnelServices $deliveryServices */
        $deliveryServices = app()->make(ServicePersonnelServices::class);
        if ($deliveryServices->checkoutIsService($uid)) {
            return $next($request);
        }

        return app('json')->fail('您无核销权限，请联系管理员');
    }
}
