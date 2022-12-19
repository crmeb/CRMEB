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
namespace app\outapi\controller;

use app\services\user\OutUserLevelServices;
use think\facade\App;

/**
 * 会员等级
 * Class UserLevel
 * @package app\outapi\controller
 */
class UserLevel extends AuthController
{

    /**
     * UserLevel constructor.
     * @param App $app
     * @param OutUserLevelServices $services
     */
    public function __construct(App $app, OutUserLevelServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 等级列表
     * @return void
     */
    public function lst()
    {
        $where = $this->request->getMore([
            ['title', ''],
            ['is_show', ''],
        ]);

        return app('json')->success($this->services->levelList($where));
    }

}