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

namespace app\services\system\admin;


use app\dao\system\admin\AdminAuthDao;
use crmeb\auth\AuthTokenVerifier;
use crmeb\basic\BaseService;
use app\services\other\CacheServices;
use crmeb\exceptions\AuthException;
use crmeb\services\CacheService;

/**
 * admin授权service
 * Class AdminAuthServices
 * @package app\services\system\admin
 */
class AdminAuthServices extends BaseService
{
    /**
     * @var AdminAuthDao
     */
    protected $dao;

    /**
     * 构造方法
     * AdminAuthServices constructor.
     * @param AdminAuthDao $dao
     */
    public function __construct(AdminAuthDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取Admin授权信息
     * @param string $token
     * @param string $msg
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function parseToken(string $token, string $msg = '登录已过期,请重新登录'): array
    {
        // 统一走认证校验骨架：空值/令牌桶缓存/JWT 校验已收口至 AuthTokenVerifier；失败回调承接编辑商品草稿暂存
        [$md5Token, $id, $type, $pwd] = AuthTokenVerifier::verify($token, $msg, $msg, $msg, 401, function ($id, $type) {
            $this->authFailAfter($id, $type);
        });

        //获取管理员信息
        $adminInfo = $this->dao->get($id);
        if (!$adminInfo || !$adminInfo->id) {
            if (!request()->isCli()) {
                CacheService::delete($md5Token);
            }
            $this->authFailAfter($id, $type);
            throw new AuthException($msg, [], 401);
        }
        // 改密踢人：令牌内 pwd 指纹与当前密码不一致则拒绝
        if ($pwd !== '' && $pwd !== md5($adminInfo->pwd)) {
            throw new AuthException($msg, [], 401);
        }

        $adminInfo->type = $type;
        return $adminInfo->hidden(['pwd', 'is_del', 'status'])->toArray();
    }

    /**
     * token验证失败后事件
     */
    protected function authFailAfter($id, $type)
    {
        try {
            $postData = request()->post();
            $rule = trim(strtolower(request()->rule()->getRule()));
            $method = trim(strtolower(request()->method()));
            //添加商品退出后事件
            if ($rule === 'product/product/<id>' && $method === 'post') {
                $this->saveProduct($id, $postData);
            }
        } catch (\Throwable $e) {
        }
    }

    /**
     * 保存提交数据
     * @param $adminId
     * @param $postData
     */
    protected function saveProduct($adminId, $postData)
    {
        /** @var CacheServices $cacheService */
        $cacheService = app()->make(CacheServices::class);
        $cacheService->setDbCache($adminId . '_product_data', $postData, 68400);
    }
}
