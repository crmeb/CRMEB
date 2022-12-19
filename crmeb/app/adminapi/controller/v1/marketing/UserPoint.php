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
namespace app\adminapi\controller\v1\marketing;

use app\adminapi\controller\AuthController;
use app\services\user\UserBillServices;
use think\facade\App;

/**
 * 积分控制器
 * Class StoreCategory
 * @package app\admin\controller\system
 */
class UserPoint extends AuthController
{

    /**
     * Finance constructor.
     * @param App $app
     * @param UserBillServices $services
     */
    public function __construct(App $app, UserBillServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 积分记录列表
     * @return mixed
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['start_time', ''],
            ['end_time', ''],
            ['nickname', ''],
            ['page', 1],
            ['limit', 10],
        ]);
        return app('json')->success($this->services->getPointList($where));
    }

    /**
     * 获取积分日志头部信息
     * @return mixed
     */
    public function integral_statistics()
    {
        $where = $this->request->getMore([
            ['start_time', ''],
            ['end_time', ''],
            ['nickname', ''],
        ]);
        return app('json')->success(['res' => $this->services->getUserPointBadgelist($where)]);
    }

}
