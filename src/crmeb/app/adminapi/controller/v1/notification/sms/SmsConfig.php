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
namespace app\adminapi\controller\v1\notification\sms;

use app\services\message\sms\SmsAdminServices;
use app\services\message\sms\SmsRecordServices;
use app\services\serve\ServeServices;
use crmeb\services\CacheService;
use app\adminapi\controller\AuthController;
use think\facade\App;

/**
 * 短信配置
 * Class SmsConfig
 * @package app\admin\controller\sms
 */
class SmsConfig extends AuthController
{
    /**
     * 构造方法
     * SmsConfig constructor.
     * @param App $app
     * @param SmsAdminServices $services
     */
    public function __construct(App $app, SmsAdminServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }


    /**
     * 保存短信配置
     * @return mixed
     */
    public function save_basics()
    {
        [$account, $token] = $this->request->postMore([
            ['sms_account', ''],
            ['sms_token', '']
        ], true);

        validate(\app\adminapi\validate\notification\SmsConfigValidate::class)->check(['sms_account' => $account, 'sms_token' => $token]);

        if ($this->services->login($account, $token)) {
            return app('json')->success('登录成功');
        } else {
            return app('json')->fail('账号或密码错误');
        }
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
     * 退出
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function logout()
    {
        $res = CacheService::redisHandler()->delete('sms_account');
        if ($res) {
            $this->services->updateSmsConfig('', '');
            CacheService::clear();
            return app('json')->success('退出成功');
        } else {
            return app('json')->fail('退出失败');
        }
    }

    /**
     * 短信发送记录
     * @return mixed
     */
    public function record(ServeServices $services)
    {
        [$page, $limit, $status] = $this->request->getMore([
            [['page', 'd'], 0],
            [['limit', 'd'], 10],
            ['type', '', '', 'status'],
        ], true);
        return app('json')->success($services->user()->record($page, $limit, 1, $status));
    }

    /**
     * @return mixed
     */
    public function data()
    {
        return app('json')->success($this->services->getSmsData());
    }
}
