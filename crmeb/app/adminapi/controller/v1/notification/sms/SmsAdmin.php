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
namespace app\adminapi\controller\v1\notification\sms;

use app\adminapi\controller\AuthController;
use app\services\yihaotong\SmsAdminServices;
use think\facade\App;

/**
 * 短信账号
 * Class SmsAdmin
 * @package app\adminapi\controller\v1\sms
 */
class SmsAdmin extends AuthController
{
    /**
     * 构造方法
     * SmsAdmin constructor.
     * @param App $app
     * @param SmsAdminServices $services
     */
    public function __construct(App $app, SmsAdminServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 发送验证码
     * @return mixed
     */
    public function captcha()
    {
        if (!request()->isPost()) {
            return app('json')->fail(100031);
        }
        $phone = request()->param('phone');
        if (!trim($phone)) {
            return app('json')->fail(400132);
        }
        return app('json')->success($this->services->captcha($phone));
    }

    /**
     * 修改/注册短信平台账号
     * @return mixed
     */
    public function save()
    {
        [$account, $password, $phone, $code, $url, $sign] = $this->request->postMore([
            ['account', ''],
            ['password', ''],
            ['phone', ''],
            ['code', ''],
            ['url', ''],
            ['sign', ''],
        ], true);
        $signLen = mb_strlen(trim($sign));
        if (!strlen(trim($account))) return app('json')->fail(400133);
        if (!strlen(trim($password))) return app('json')->fail(400134);
        if (!$signLen) return app('json')->fail(400135);
        if ($signLen > 8) return app('json')->fail(400136);
        if (!strlen(trim($code))) return app('json')->fail(400137);
        if (!strlen(trim($url))) return app('json')->fail(400138);
        $status = $this->services->register($account, $password, $url, $phone, $code, $sign);
        return app('json')->success($status['msg']);
    }
}
