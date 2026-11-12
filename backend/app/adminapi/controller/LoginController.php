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
namespace app\adminapi\controller;

use crmeb\services\CacheService;
use think\facade\App;
use crmeb\utils\Captcha;
use app\services\system\admin\SystemAdminServices;

/**
 * 后台登陆
 * Class Login
 * @package app\adminapi\controller
 */
class LoginController extends AuthController
{

    /**
     * @var SystemAdminServices
     */
    protected $services;

    /**
     * Login constructor.
     * @param App $app
     * @param SystemAdminServices $services
     */
    public function __construct(App $app, SystemAdminServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    protected function initialize()
    {
        // TODO: Implement initialize() method.
    }

    /**
     * 验证码
     * @return $this|\think\Response
     */
    public function captcha()
    {
        return app()->make(Captcha::class)->create();
    }

    /**
     * @return mixed
     */
    public function ajcaptcha()
    {
        $captchaType = $this->request->get('captchaType');
        return app('json')->success(aj_captcha_create($captchaType));
    }

    /**
     * 一次验证
     * @return mixed
     */
    public function ajcheck()
    {
        [$token, $pointJson, $captchaType] = $this->request->postMore([
            ['token', ''],
            ['pointJson', ''],
            ['captchaType', ''],
        ], true);
        try {
            aj_captcha_check_one($captchaType, $token, $pointJson);
            return app('json')->success();
        } catch (\Throwable $e) {
            return app('json')->fail('验证码错误');
        }
    }

    /**
     * 登陆
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function login()
    {
        [$account, $password, $key, $captchaVerification, $captchaType] = $this->request->postMore([
            ['account', ''],
            ['pwd', ''],
            ['key', ''],
            ['captchaVerification', ''],
            ['captchaType', '']
        ], true);

        if ($captchaVerification !== '') {
            try {
                aj_captcha_check_two($captchaType, $captchaVerification);
            } catch (\Throwable $e) {
                return app('json')->fail('验证码错误');
            }
        }

        if (strlen(trim($password)) < 6 || strlen(trim($password)) > 32) {
            return app('json')->fail('账号密码必须是在6到32位之间');
        }

        $this->validate(['account' => $account, 'pwd' => $password], \app\adminapi\validate\setting\SystemAdminValidata::class, 'get');

        // 登录失败锁定：按 账号+IP 计数，5 次锁 1 分钟、10 次锁 2 分钟、20 次及以上锁 5 分钟（封顶 5 分钟），防在线爆破（安全审计 A4）
        $failKey = 'admin_login_fail:' . md5(strtolower(trim((string)$account)) . '|' . $this->request->ip());
        $lockKey = 'admin_login_lock:' . md5(strtolower(trim((string)$account)) . '|' . $this->request->ip());
        $lockExpiry = (int)CacheService::get($lockKey, 0);
        if ($lockExpiry > time()) {
            return app('json')->fail('失败次数过多，账号已锁定，请 ' . ($lockExpiry - time()) . ' 秒后重试');
        }

        $result = $this->services->login($account, $password, 'admin', $key);
        if (!$result) {
            // 累计失败次数并按档位设置锁定到期时间（同档重复失败仅顺延不叠加）
            $failNum = (int)CacheService::get($failKey, 0) + 1;
            CacheService::set($failKey, $failNum, 600);
            $lockMinutes = 0;
            if ($failNum >= 20) {
                $lockMinutes = 5;
            } elseif ($failNum >= 10) {
                $lockMinutes = 2;
            } elseif ($failNum >= 5) {
                $lockMinutes = 1;
            }
            if ($lockMinutes > 0) {
                $newExpiry = time() + $lockMinutes * 60;
                CacheService::set($lockKey, max((int)CacheService::get($lockKey, 0), $newExpiry), 600);
            }
            $num = CacheService::get('login_captcha', 1);
            if ($num > 1) {
                return app('json')->fail('账号或密码错误', ['login_captcha' => 1]);
            }
            CacheService::set('login_captcha', $num + 1, 60);
            return app('json')->fail('账号或密码错误', ['login_captcha' => 0]);
        }
        // 登录成功清空该账号的失败计数与锁定状态
        CacheService::delete($failKey);
        CacheService::delete($lockKey);
        CacheService::delete('login_captcha');
        return app('json')->success($result);
    }

    /**
     * 获取后台登录页轮播图以及LOGO
     * @return mixed
     */
    public function info()
    {
        return app('json')->success($this->services->getLoginInfo());
    }
}
