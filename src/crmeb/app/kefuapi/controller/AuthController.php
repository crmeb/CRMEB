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

namespace app\kefuapi\controller;


use think\facade\App;

/**
 * Class AuthController
 * @package app\kefuapi\controller
 */
abstract class AuthController
{

    /**
     * @var int
     */
    protected $kefuId;

    /**
     * @var array
     */
    protected $kefuInfo;

    /**
     * @var App
     */
    protected $app;

    /**
     * @var
     */
    protected $request;

    /**
     * AuthController constructor.
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->request = app('request');
        $this->initialize();
    }

    /**
     * 初始化
     */
    protected function initialize()
    {
        $this->kefuId = $this->request->kefuId() ?? 0;
        $this->kefuInfo = $this->request->kefuInfo() ?? [];
    }

    /**
     * 成功返回
     * @param string $msg
     * @param array|null $data
     * @return mixed
     */
    protected function success($msg = 'ok', ?array $data = null)
    {
        return app('json')->success($msg, $data);
    }

    /**
     * 失败返回
     * @param string $msg
     * @param array|null $data
     */
    protected function fail($msg = 'fail', ?array $data = null)
    {
        return app('json')->fail($msg, $data);
    }
}
