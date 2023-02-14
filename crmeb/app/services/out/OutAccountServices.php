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

namespace app\services\out;

use app\dao\out\OutAccountDao;
use app\services\BaseServices;
use crmeb\exceptions\AdminException;
use crmeb\exceptions\AuthException;
use crmeb\services\CacheService;
use crmeb\services\FormBuilder as Form;
use crmeb\services\HttpService;
use crmeb\utils\JwtAuth;
use Firebase\JWT\ExpiredException;

/**
 * 获取token
 * Class LoginServices
 * @package app\services\kefu
 * @method get($id, ?array $field = [], ?array $with = []) 获取一条数据
 * @method update($id, array $data, ?string $key = null)
 * @method save(array $data)保存
 */
class OutAccountServices extends BaseServices
{
    /**
     * LoginServices constructor.
     * @param OutAccountDao $dao
     */
    public function __construct(OutAccountDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 账号密码登录
     * @param string $appid
     * @param string $appsecret
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function authLogin(string $appid, string $appsecret = null)
    {
        $autInfo = $this->dao->get(['appid' => $appid, 'is_del' => 0]);
        if (!$autInfo) {
            throw new AuthException(410141);
        }
        if ($appsecret && !password_verify($appsecret, $autInfo->appsecret)) {
            throw new AuthException(400744);
        }
        if ($autInfo->status == 0) {
            throw new AuthException(400595);
        }
        $token = $this->createToken($autInfo->id, 'out');
        $data['last_time'] = time();
        $data['ip'] = request()->ip();
        $this->update($autInfo['id'], $data);
        return [
            'access_token' => $token['token'],
            'exp_time' => $token['params']['exp'],
            'auth_info' => $autInfo->hidden(['appsecret', 'ip', 'is_del', 'add_time', 'status', 'last_time'])->toArray()
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
        /** @var CacheService $cacheService */
        $cacheService = app()->make(CacheService::class);

        /** @var JwtAuth $jwtAuth */
        $jwtAuth = app()->make(JwtAuth::class);

        //获取token信息
        [$md5Token, $id, $type] = $this->verifyToken($token, $jwtAuth, $cacheService);

        //获取对外账号
        $authInfo = $this->dao->getOne(['id' => $id, 'is_del' => 0]);
        $this->checkAuth($authInfo, $md5Token, $cacheService);
        return $authInfo->hidden(['appsecret', 'ip', 'is_del', 'add_time', 'status', 'last_time'])->toArray();
    }

    /**
     * 获取一条
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getOne($where = [])
    {
        $info = $this->dao->getOne($where);
        return $info ? $info->toArray() : [];
    }

    /**
     * 获取列表
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getList(array $where = [])
    {
        [$page, $limit] = $this->getPageValue();
        $where['is_del'] = 0;
        $list = $this->dao->getList($where, $page, $limit);
        $count = $this->dao->count($where);
        if ($list) {
            foreach ($list as &$item) {
                $item['add_time'] = $item['add_time'] ? date('Y-m-d H:i:s', $item['add_time']) : '暂无';
                $item['last_time'] = $item['last_time'] ? date('Y-m-d H:i:s', $item['last_time']) : '暂无';
                $item['rules'] = is_null($item['rules']) ? [] : explode(',', $item['rules']);
            }
        }
        return compact('count', 'list');
    }

    /**
     * 刷新token
     * @param string $token
     * @return array
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function refresh(string $token): array
    {
        /** @var CacheService $cacheService */
        $cacheService = app()->make(CacheService::class);

        /** @var JwtAuth $jwtAuth */
        $jwtAuth = app()->make(JwtAuth::class);

        //获取token信息
        [$md5Token, $id, $type] = $this->verifyToken($token, $jwtAuth, $cacheService);

        //获取对外账号
        $authInfo = $this->dao->getOne(['id' => $id, 'is_del' => 0]);
        $this->checkAuth($authInfo, $md5Token, $cacheService);

        $cacheService->delete($md5Token);

        $token = $jwtAuth->createToken($id, $type);
        $data['last_time'] = time();
        $data['ip'] = request()->ip();
        $this->dao->update($id, $data);
        return [
            'access_token' => $token['token'],
            'exp_time' => $token['params']['exp'],
        ];
    }

    /**
     * 核对用户
     * @param $authInfo
     * @param string $md5Token
     * @param CacheService $cacheService
     * @return bool
     */
    protected function checkAuth($authInfo, string $md5Token, CacheService $cacheService): bool
    {
        if (!$authInfo) {
            if (!request()->isCli()) {
                $cacheService->delete($md5Token);
            }
            throw new AuthException(110003);
        }

        if ($authInfo->status == 2) {
            if (!request()->isCli()) {
                $cacheService->delete($md5Token);
            }
            throw new AuthException(400595);
        }
        return true;
    }

    /**
     * 获取token
     * @param string $token
     * @param JwtAuth $jwtAuth
     * @param CacheService $cacheService
     * @return array
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    protected function verifyToken(string $token, JwtAuth $jwtAuth, CacheService $cacheService): array
    {
        if (!$token || $token === 'undefined') {
            throw new AuthException(400172);
        }

        $md5Token = md5($token);

        if (!$cacheService->has($md5Token) || !($cacheToken = $cacheService->get($md5Token, '', NULL, 'out'))) {
            throw new AuthException(110006);
        }

        //解析token
        [$id, $type] = $jwtAuth->parseToken($token);
        if (!$id || $type != 'out') {
            throw new AuthException(400172);
        }

        try {
            $jwtAuth->verifyToken();
        } catch (\Throwable $e) {
            if (!request()->isCli()) {
                $cacheService->delete($md5Token);
            }
            throw new AuthException(400172);
        }

        return [$md5Token, $id, $type];
    }

    /**
     * 设置账号推送接口
     * @param $id
     * @param $data
     * @return \crmeb\basic\BaseModel
     */
    public function outSetUpSave($id, $data)
    {
        return $this->dao->update($id, $data);
    }

    /**
     * 测试获取token接口
     * @param $data
     * @return int[]|mixed
     */
    public function textOutUrl($data)
    {
        if (!$data['push_account'] || !$data['push_password'] || !$data['push_token_url']) throw new AdminException(100100);
        $param = ['push_account' => $data['push_account'], 'push_password' => $data['push_password']];
        $res = HttpService::getRequest($data['push_token_url'], $param);
        $res = $res ? json_decode($res, true) : ['status' => 400];
        if (!isset($res['status']) && $res['status'] != 200) {
            throw new AdminException(100015);
        } else {
            return $res['data'];
        }
    }
}
