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

namespace app\services\kefu;


use crmeb\exceptions\AuthException;
use crmeb\services\oauth\OAuth;
use crmeb\utils\JwtAuth;
use Firebase\JWT\ExpiredException;
use think\facade\Cache;
use app\services\BaseServices;
use crmeb\services\CacheService;
use app\dao\service\StoreServiceDao;
use crmeb\services\app\WechatOpenService;
use app\services\wechat\WechatUserServices;

/**
 * 客服登录
 * Class LoginServices
 * @package app\services\kefu
 * @method get($id, ?array $field = [], ?array $with = []) 获取一条数据
 */
class LoginServices extends BaseServices
{
    /**
     * LoginServices constructor.
     * @param StoreServiceDao $dao
     */
    public function __construct(StoreServiceDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 客服账号密码登录
     * @param string $account
     * @param string $password
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function authLogin(string $account, string $password = null)
    {
        $kefuInfo = $this->dao->get(['account' => $account]);
        if (!$kefuInfo) {
            throw new AuthException(410141);
        }
        if ($password && !password_verify($password, $kefuInfo->password)) {
            throw new AuthException(410025);
        }
        if (!$kefuInfo->status) {
            throw new AuthException(410027);
        }
        $token = $this->createToken($kefuInfo->id, 'kefu');
        $kefuInfo->update_time = time();
        $kefuInfo->ip = request()->ip();
        $kefuInfo->save();
        return [
            'token' => $token['token'],
            'exp_time' => $token['params']['exp'],
            'kefuInfo' => $kefuInfo->hidden(['password', 'ip', 'update_time', 'add_time', 'status', 'mer_id', 'customer', 'notify'])->toArray()
        ];
    }

    /**
     * 解析token
     * @param string $token
     * @return array
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function parseToken(string $token)
    {
        $noCli = !request()->isCli();
        /** @var CacheService $cacheService */
        $cacheService = app()->make(CacheService::class);
        //检测token是否过期
        $md5Token = md5($token);
        if (!$token || !$cacheService->has($md5Token) || !($cacheService->get($md5Token, '', NULL, 'kefu'))) {
            throw new AuthException(110005);
        }
        if ($token === 'undefined') {
            throw new AuthException(110005);
        }

        /** @var JwtAuth $jwtAuth */
        $jwtAuth = app()->make(JwtAuth::class);
        //设置解析token
        [$id, $type] = $jwtAuth->parseToken($token);

        //验证token
        try {
            $jwtAuth->verifyToken();
        } catch (\Throwable $e) {
            $noCli && $cacheService->delete($md5Token);
            throw new AuthException(110006);
        }

        //获取管理员信息
        $adminInfo = $this->dao->get($id);
        if (!$adminInfo || !$adminInfo->id) {
            $noCli && $cacheService->delete($md5Token);
            throw new AuthException(110007);
        }

        $adminInfo->type = $type;
        return $adminInfo->hidden(['password', 'ip', 'status']);
    }

    /**
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function wechatAuth()
    {
        /** @var OAuth $oauth */
        $oauth = app()->make(OAuth::class);
        $original = $oauth->oauth(null, ['open' => true]);
        if (!isset($original['unionid'])) {
            throw new AuthException(410132);
        }
        /** @var WechatUserServices $userService */
        $userService = app()->make(WechatUserServices::class);
        $uid = $userService->value(['unionid' => $original['unionid']], 'uid');
        if (!$uid) {
            throw new AuthException(410133);
        }
        $kefuInfo = $this->dao->get(['uid' => $uid]);
        if (!$kefuInfo) {
            throw new AuthException(410142);
        }
        if (!$kefuInfo->status) {
            throw new AuthException(410027);
        }
        $token = $this->createToken($kefuInfo->id, 'kefu');
        $kefuInfo->update_time = time();
        $kefuInfo->ip = request()->ip();
        $kefuInfo->save();
        return [
            'token' => $token['token'],
            'exp_time' => $token['params']['exp'],
            'kefuInfo' => $kefuInfo->hidden(['password', 'ip', 'update_time', 'add_time', 'status', 'mer_id', 'customer', 'notify'])->toArray()
        ];
    }

    /**
     * 检测有没有人扫描登录
     * @param string $key
     * @return array|int[]
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function scanLogin(string $key)
    {
        $hasKey = Cache::has($key);
        if ($hasKey === false) {
            $status = 0;//不存在需要刷新二维码
        } else {
            $keyValue = CacheService::get($key);
            if ($keyValue === '0') {
                $status = 1;//正在扫描中
                $kefuInfo = $this->dao->get(['uniqid' => $key], ['account', 'uniqid']);
                if ($kefuInfo) {
                    $tokenInfo = $this->authLogin($kefuInfo->account);
                    $tokenInfo['status'] = 3;
                    $kefuInfo->uniqid = '';
                    $kefuInfo->save();
                    CacheService::delete($key);
                    return $tokenInfo;
                }
            } else {
                $status = 2;//没有扫描
            }
        }
        return ['status' => $status];
    }
}
