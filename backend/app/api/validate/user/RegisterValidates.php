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
namespace app\api\validate\user;


use think\Validate;

/**
 * 注册验证
 * Class RegisterValidates
 * @package app\http\validates\user
 */
class RegisterValidates extends Validate
{
    protected $regex = ['phone' => '/^1[3456789]\d{9}$/'];

    protected $rule = [
        'phone' => 'require|regex:phone',
        'account' => 'require|regex:phone',
        'captcha' => 'require|length:6',
        'password' => 'require',
    ];

    protected $message = [
        'phone.require' => '请输入手机号',
        'phone.regex' => '手机号格式不正确',
        'account.require' => '请输入手机号',
        'account.regex' => '手机号格式不正确',
        'captcha.require' => '请输入验证码',
        'captcha.length' => '验证码错误',
        'password.require' => '密码必须是在6到16位之间',
    ];


    public function sceneCode()
    {
        return $this->only(['phone']);
    }


    public function sceneRegister()
    {
        return $this->only(['account', 'captcha', 'password']);
    }
}
