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

namespace app\services\workerman;


use app\services\system\admin\AdminAuthServices;
use crmeb\exceptions\AuthException;
use crmeb\services\workerman\Response;
use Workerman\Connection\TcpConnection;

/**
 * 管理后台长连接业务处理
 * 处理 admin 端 workerman 连接的登录鉴权等业务逻辑
 */
class WorkermanHandle
{
    /**
     * @var WorkermanService
     */
    protected $service;

    /**
     * @param WorkermanService $service
     */
    public function __construct(WorkermanService &$service)
    {
        $this->service = &$service;
    }

    /**
     * 管理员登录鉴权
     * @param TcpConnection $connection
     * @param array $res
     * @param Response $response
     * @return bool|null
     */
    public function login(TcpConnection &$connection, array $res, Response $response)
    {
        if (!isset($res['data']) || !$token = $res['data']) {
            return $response->close([
                'msg' => '授权失败!'
            ]);
        }

        try {
            /** @var AdminAuthServices $adminAuthService */
            $adminAuthService = app()->make(AdminAuthServices::class);
            $authInfo = $adminAuthService->parseToken($token);
        } catch (AuthException $e) {
            return $response->close([
                'msg' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }

        if (!$authInfo || !isset($authInfo['id'])) {
            return $response->close([
                'msg' => '授权失败!'
            ]);
        }

        $connection->adminInfo = $authInfo;
        $connection->adminId = $authInfo['id'];
        $this->service->setUser($connection);

        return $response->success();
    }
}
