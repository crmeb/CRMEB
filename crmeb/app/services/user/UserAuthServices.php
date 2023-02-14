<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare (strict_types=1);

namespace app\services\user;

use app\services\BaseServices;
use app\dao\user\UserAuthDao;
use crmeb\exceptions\AuthException;
use crmeb\services\CacheService;
use crmeb\utils\JwtAuth;

/**
 *
 * Class UserAuthServices
 * @package app\services\user
 */
class UserAuthServices extends BaseServices
{

    /**
     * UserAuthServices constructor.
     * @param UserAuthDao $dao
     */
    public function __construct(UserAuthDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取授权信息
     * @param $token
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function parseToken($token): array
    {
        $md5Token = is_null($token) ? '' : md5($token);

        if ($token === 'undefined') {
            throw new AuthException(110002);
        }
        if (!$token || !$tokenData = CacheService::get($md5Token, '', NULL, 'api'))
            throw new AuthException(110002);

        if (!is_array($tokenData) || empty($tokenData) || !isset($tokenData['uid'])) {
            throw new AuthException(110002);
        }

        /** @var JwtAuth $jwtAuth */
        $jwtAuth = app()->make(JwtAuth::class);
        //设置解析token
        [$id, $type] = $jwtAuth->parseToken($token);


        try {
            $jwtAuth->verifyToken();
        } catch (\Throwable $e) {
            if (!request()->isCli()) CacheService::delete($md5Token);
            throw new AuthException(110003);
        }

        $user = $this->dao->get(['uid' => $id, 'is_del' => 0]);

        if (!$user || $user->uid != $tokenData['uid']) {
            if (!request()->isCli()) CacheService::delete($md5Token);
            throw new AuthException(110004);
        }
        $tokenData['type'] = $type;
        return compact('user', 'tokenData');
    }

}
