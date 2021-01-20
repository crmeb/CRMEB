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

namespace app\adminapi\controller\v1\serve;


use app\adminapi\controller\AuthController;
use app\adminapi\validate\serve\ExpressValidata;
use app\adminapi\validate\serve\MealValidata;
use app\adminapi\validate\serve\ServeValidata;
use app\services\system\config\SystemConfigServices;
use crmeb\services\CacheService;
use app\services\serve\ServeServices;
use think\facade\App;

/**
 * Class Serve
 * @package app\adminapi\controller\v1\serve
 */
class Serve extends AuthController
{
    /**
     * Serve constructor.
     * @param App $app
     * @param ServeServices $services
     */
    public function __construct(App $app, ServeServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 检测登录
     * @return mixed
     */
    public function is_login()
    {
        $sms_info = CacheService::redisHandler()->get('sms_account');
        if ($sms_info) {
            return app('json')->success(['status' => true, 'info' => $sms_info]);
        } else {
            return app('json')->success(['status' => false]);
        }
    }

    /**
     * 获取套餐列表
     * @param string $type
     * @return mixed
     */
    public function mealList(string $type)
    {
        $res = $this->services->user()->mealList($type);
        if ($res) {
            return app('json')->success($res);
        } else {
            return app('json')->fail('获取套餐列表失败');
        }
    }

    /**
     * 获取支付码
     * @return mixed
     */
    public function payMeal()
    {
        $data = $this->request->postMore([
            ['meal_id', ''],
            ['price', ''],
            ['num', ''],
            ['type', ''],
            ['pay_type', ''],
        ]);

        validate(MealValidata::class)->check($data);

        $res = $this->services->user()->payMeal($data);
        if ($res) {
            return app('json')->success($res);
        } else {
            return app('json')->fail('获取支付码失败');
        }
    }

    /**
     * 开通打印电子面单
     * @return mixed
     */
    public function openExpress()
    {
        $this->services->express()->open();
        return app('json')->success('开通成功');

    }

    /**
     * 获取用户信息，用户信息内包含是否开通服务字段
     * @return mixed
     */
    public function getUserInfo()
    {
        return app('json')->success($this->services->user()->getUser());
    }

    /**
     * 查询记录
     * @return mixed
     */
    public function getRecord()
    {
        [$page, $limit, $type] = $this->request->getMore([
            [['page', 'd'], 0],
            [['limit', 'd'], 10],
            [['type', 'd'], 0],
        ], true);

        return app('json')->success($this->services->user()->record($page, $limit, $type));
    }

    /**
     * 开通服务
     * @param int $type
     * @return mixed
     */
    public function openServe($type = 0)
    {
        if ($type) {
            $this->services->copy()->open();
        } else {
            $this->services->express()->open();
        }

        return app('json')->success('开通成功');
    }

    /**
     * 修改密码
     * @return mixed
     */
    public function modify()
    {
        $data = $this->request->postMore([
            ['account', ''],
            ['password', ''],
            ['phone', ''],
            ['verify_code', ''],
        ]);

        validate(ServeValidata::class)->check($data);

        $data['password'] = md5($data['password']);
        $this->services->user()->modify($data);
        CacheService::redisHandler()->delete('sms_account');
        return app('json')->success('修改成功');
    }

    /**
     * 修改手机号
     * @return mixed
     */
    public function updatePhone()
    {
        $data = $this->request->postMore([
            ['account', ''],
            ['phone', ''],
            ['verify_code', ''],
        ]);

        validate(ServeValidata::class)->scene('phone')->check($data);

        $this->services->user()->modifyPhone($data);
        CacheService::redisHandler()->delete('sms_account');
        return app('json')->success('修改成功');
    }
}
