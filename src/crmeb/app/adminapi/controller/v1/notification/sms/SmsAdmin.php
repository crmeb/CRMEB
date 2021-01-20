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

use app\adminapi\controller\AuthController;
use app\services\message\sms\SmsAdminServices;
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
            return app('json')->fail('发送失败');
        }
        $phone = request()->param('phone');
        if (!trim($phone)) {
            return app('json')->fail('请填写手机号');
        }
        return app('json')->success($this->services->captcha($phone));
    }

    /**
     * 修改/注册短信平台账号
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
        if (!strlen(trim($account))) return app('json')->fail('请填写账号');
        if (!strlen(trim($password))) return app('json')->fail('请填写密码');
        if (!$signLen) return app('json')->fail('请填写短信签名');
        if ($signLen > 8) return app('json')->fail('短信签名最长为8位');
        if (!strlen(trim($code))) return app('json')->fail('请填写验证码');
        if (!strlen(trim($url))) return app('json')->fail('请填写域名');
        $status = $this->services->register($account, $password, $url, $phone, $code, $sign);
        return app('json')->success('短信平台：' . $status['msg']);
    }
}
