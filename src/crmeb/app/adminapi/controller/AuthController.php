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
namespace app\adminapi\controller;

use think\facade\App;

/**
 * 基类 所有控制器继承的类
 * Class AuthController
 * @package app\adminapi\controller
 */
class AuthController
{
    /**
     * 当前登陆管理员信息
     * @var
     */
    protected $adminInfo;

    /**
     * 当前登陆管理员ID
     * @var
     */
    protected $adminId;

    /**
     * 当前管理员权限
     * @var array
     */
    protected $auth = [];


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
     * 构造方法
     * @access public
     * @param App $app 应用对象
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->request = app('request');
        // 控制器初始化
        $this->init();
    }

    /**
     * 初始化
     */
    final private function init()
    {
        $this->initialize();
    }

    /**
     * 初始化
     */
    protected function initialize()
    {
        $this->adminId = $this->request->adminId();
        $this->adminInfo = $this->request->adminInfo();
        $this->auth = $this->request->adminInfo['rule'] ?? [];
    }
}
