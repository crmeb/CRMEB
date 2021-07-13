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
declare (strict_types=1);

namespace crmeb\basic;

use crmeb\services\HttpService;
use think\exception\ValidateException;
use think\facade\App;

/**
 * 控制器基础类
 */
abstract class BaseController
{
    /**
     * Request实例
     * @var \app\Request
     */
    protected $request;

    /**
     * 应用实例
     * @var \think\App
     */
    protected $app;

    /**
     * 控制器中间件
     * @var array
     */
    protected $middleware = [];

    /**
     * @var
     */
    protected $services;

    /**
     * 需要授权的接口地址
     * @var string[]
     */
    private $authRule = ['marketing/bargain/<id>', 'marketing/combination/<id>', 'marketing/seckill/<id>'];

    /**
     * 构造方法
     * @access public
     * @param App $app 应用对象
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->request = app('request');
        $this->init();
    }

    /**
     * 初始化
     */
    final private function init()
    {
        try {
            $this->authorizationDecryptCrmeb();
        } catch (\Throwable $e) {
            if (in_array($this->request->rule()->getRule(), $this->authRule)) {
                throw new ValidateException($e->getMessage());
            }
        }
        $this->initialize();
    }

    /**
     * @return mixed
     */
    protected function initialize()
    {

    }


    /**
     * @param bool $bool
     * @param callable|null $callable
     * @return array|bool
     */
    protected function authorizationDecryptCrmeb()
    {
        $path = app()->getRootPath() . 'public' . DS . 'install' . DS . 'install.lock';
        if (!is_file($path)) {
            $path = app()->getRootPath() . ".constant";
        }
        if (!is_file($path)) {
            throw new \RuntimeException('授权文件丢失', 42010);
        }
        $installtime = (int)@filectime($path);
        $time = $installtime;
        if (!$time) {
            $time = time() - 10;
        }
        if (date('m', $time) < date('m', time())) {
            $time = time() - 10;
        }
        $encryptStr = app()->db->name('system_config')->where('menu_name', 'cert_crmeb')->value('value');
        $encryptStr = $encryptStr ? json_decode($encryptStr, true) : null;
        $res = ['id' => '-1', 'key' => 'crmeb'];
        if (in_array(date('d'), [5, 10, 15, 20, 25, 30]) && rand(1000, 100000) < 4000 && $encryptStr && $time < time()) {
            $res = HttpService::request('http://store.crmeb.net/api/web/auth/get_id', 'POST', [
                'domain_name' => request()->host(true),
                'label' => 23,
            ]);
            if (!isset($res['id']) || !$res['id']) {
                $res = json_decode($res, true);
                if (!$res['data']['id'] && $encryptStr) {
                    app()->db->name('system_config')->where('menu_name', 'cert_crmeb')->delete();
                    throw new \Exception('您的授权已到期,请联系CRMEB官方进行授权认证');
                }
            }
            file_put_contents($path, time() + 86400);
            $installtime = $installtime + 86400;
        }
        return [$res, $installtime, $encryptStr];
    }
}
