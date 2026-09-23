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
declare (strict_types=1);

namespace crmeb\basic;

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
    private $authRule = [];
    /**
     * 构造方法
     * @access public
     * 容器不支持联合类型注入，未传参数时回退到当前应用。
     * @param \think\App|App|null $app 应用实例或仓库现有控制器注入的门面
     */
    public function __construct(\think\App|App|null $app = null)
    {
        $this->app = $app instanceof \think\App ? $app : app();
        $this->request = app('request');
        $this->initialize();
    }

    /**
     * @return mixed
     */
    abstract protected function initialize();


}
