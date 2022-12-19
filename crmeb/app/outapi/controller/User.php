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
namespace app\outapi\controller;

use think\facade\App;
use app\services\user\OutUserServices;

/**
 * 用户控制器
 * Class User
 * @package app\outapi\controller
 */
class User extends AuthController
{
    /**
     * User constructor.
     * @param App $app
     * @param OutUserServices $service
     * @method temp
     */
    public function __construct(App $app, OutUserServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 用户列表
     * @return mixed
     */
    public function lst()
    {
        $where = $this->request->getMore([
            ['nickname', ''],
            ['status', ''],
            ['field_key', ''],
        ]);
        return app('json')->success($this->services->getUserList($where));
    }

    /**
     * 保存新建的资源
     *
     * @param \think\Request $request
     * @return \think\Response
     */
    public function save()
    {
        $data = $this->request->postMore([
            ['real_name', ''],
            ['phone', 0],
            ['mark', ''],
            ['pwd', ''],
            ['level', 0],
            ['spread_open', 0],
            ['is_promoter', 0],
            ['status', 1]
        ]);
        $uid = $this->services->saveUser(0, $data);
        if (!$uid) {
            return app('json')->fail(100022);
        }
        return app('json')->success(100021, ['uid' => $uid]);
    }

    /**
     * 更新用户
     * @param $uid
     * @return mixed
     */
    public function update($uid)
    {
        $data = $this->request->postMore([
            ['real_name', ''],
            ['phone', 0],
            ['mark', ''],
            ['pwd', ''],
            ['level', 0],
            ['spread_open', 1],
            ['is_promoter', 0],
            ['status', 1]
        ]);
        if (!$uid) return app('json')->fail(100100);
        $this->services->saveUser((int)$uid, $data);
        return app('json')->success(100001);
    }

    /**
     * 赠送相关
     * @param int $uid
     * @return mixed
     */
    public function give($uid)
    {
        $data = $this->request->postMore([
            ['money_status', 0],
            ['money', 0],
            ['integration_status', 0],
            ['integration', 0],
            ['days', 0],
            ['coupon', 0]
        ]);
        if (!$uid) return app('json')->fail(100100);
        if (!$this->services->otherGive((int)$uid, $data)) {
            return app('json')->fail(100005);
        }
        return app('json')->success(100010);
    }
}