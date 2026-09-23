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
declare (strict_types=1);

namespace app\services\user;

use crmeb\auth\AuthTokenVerifier;
use crmeb\basic\BaseService;
use app\dao\user\UserAuthDao;
use crmeb\exceptions\AuthException;
use crmeb\services\CacheService;

/**
 *
 * Class UserAuthServices
 * @package app\services\user
 */
class UserAuthServices extends BaseService
{
    /**
     * @var UserAuthDao
     */
    protected $dao;

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
        // 统一走认证校验骨架：空值/令牌桶缓存/JWT 校验已收口至 AuthTokenVerifier
        [$md5Token, $id, $type, $pwd, $tokenData] = AuthTokenVerifier::verify((string)$token, '请登录', '请登录', '登录已过期,请重新登录', 401);

        // 令牌桶缓存数据结构校验（登录态缓存必含 uid）
        if (!is_array($tokenData) || empty($tokenData) || !isset($tokenData['uid'])) {
            throw new AuthException('请登录', [], 401);
        }

        $user = $this->dao->get(['uid' => $id, 'is_del' => 0, 'status' => 1]);

        // 令牌身份与会员现状一致性校验（用户被删/禁用/换号登录时令牌即失效）
        if (!$user || $user->uid != $tokenData['uid']) {
            if (!request()->isCli()) CacheService::delete($md5Token);
            throw new AuthException('登录状态有误,请重新登录', [], 401);
        }

        $this->dao->update(['uid' => $id], ['last_time' => time()]);

        $tokenData['type'] = $type;
        return compact('user', 'tokenData');
    }

}
