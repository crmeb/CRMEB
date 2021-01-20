<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
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
use crmeb\utils\ApiErrorCode;
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
     * @throws \Psr\SimpleCache\InvalidArgumentException\
     */
    public function parseToken($token): array
    {
        $md5Token = is_null($token) ? '' : md5($token);

        if ($token === 'undefined') {
            throw new AuthException('请登录', 410000);
        }
        if (!$token || !$tokenData = CacheService::getTokenBucket($md5Token))
            throw new AuthException('请登录', 410000);

        if (!is_array($tokenData) || empty($tokenData) || !isset($tokenData['uid'])) {
            throw new AuthException('请登录', 410000);
        }

        /** @var JwtAuth $jwtAuth */
        $jwtAuth = app()->make(JwtAuth::class);
        //设置解析token
        [$id, $type] = $jwtAuth->parseToken($token);


        try {
            $jwtAuth->verifyToken();
        } catch (\Throwable $e) {
            CacheService::clearToken($md5Token);
            throw new AuthException('登录已过期,请重新登录', 410001);
        }

        $user = $this->dao->get($id);

        if (!$user || $user->uid != $tokenData['uid']) {
            CacheService::clearToken($md5Token);
            throw new AuthException('登录状态有误,请重新登录', 410002);
        }
        $tokenData['type'] = $type;
        return compact('user', 'tokenData');
    }

}
