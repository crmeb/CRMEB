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

namespace app\adminapi\controller\system;

use app\adminapi\controller\AuthController;
use app\services\system\SystemEventServices;
use think\facade\App;

class SystemEventController extends AuthController
{
    protected $services;

    public function __construct(App $app, SystemEventServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    public function index()
    {
        return app('json')->success($this->services->getEventList());
    }

    public function read(string $name)
    {
        return app('json')->success($this->services->getEventInfo($name));
    }

    public function categories()
    {
        return app('json')->success($this->services->getCategories());
    }
}
