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

namespace app\adminapi\middleware;

use app\http\middleware\BaseAuthTokenMiddleware;
use app\services\system\admin\AdminAuthServices;
use think\Request;

/**
 * 后台管理员认证中间件（令牌提取/校验骨架见 BaseAuthTokenMiddleware）
 * Class AdminAuthTokenMiddleware
 * @package app\adminapi\middleware
 */
class AdminAuthTokenMiddleware extends BaseAuthTokenMiddleware
{
    /**
     * 后台认证服务
     * @return string
     */
    protected function authService(): string
    {
        return AdminAuthServices::class;
    }

    /**
     * 绑定管理员身份宏（isAdminLogin/adminId/adminInfo），后台无游客态
     * @param Request $request 请求对象
     * @param mixed $authInfo 认证结果，游客态为 null
     * @return void
     */
    protected function bindIdentity(Request $request, $authInfo): void
    {
        // 后台不存在游客态，认证失败已在骨架 deny 阶段拦截，null 时不注入身份宏
        if (is_null($authInfo)) {
            return;
        }
        $adminInfo = $authInfo;
        $request->macro('isAdminLogin', function () use (&$adminInfo) {
            return !is_null($adminInfo);
        });
        $request->macro('adminId', function () use (&$adminInfo) {
            return $adminInfo['id'];
        });
        $request->macro('adminInfo', function () use (&$adminInfo) {
            return $adminInfo;
        });
    }
}
