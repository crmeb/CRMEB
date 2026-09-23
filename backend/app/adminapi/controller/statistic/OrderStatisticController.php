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
namespace app\adminapi\controller\statistic;

use app\adminapi\controller\AuthController;
use app\services\statistic\OrderStatisticServices;
use think\facade\App;

class OrderStatisticController extends AuthController
{
    /**
     * @var OrderStatisticServices
     */
    protected $services;


    public function __construct(App $app, OrderStatisticServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 订单统计基础信息
     * @return mixed
     */
    public function getBasic()
    {
        $where = $this->request->getMore([
            ['time', '']
        ]);
        $data = $this->services->getBasic($where);
        return app('json')->success($data);
    }

    /**
     * 订单统计趋势图
     * @return mixed
     */
    public function getTrend()
    {
        $where = $this->request->getMore([
            ['time', '']
        ]);
        $data = $this->services->getTrend($where);
        return app('json')->success($data);
    }

    /**
     * 订单来源
     * @return mixed
     */
    public function getChannel()
    {
        $where = $this->request->getMore([
            ['time', '']
        ]);
        $data = $this->services->getChannel($where);
        return app('json')->success($data);
    }

    /**
     * 订单类型
     * @return mixed
     */
    public function getType()
    {
        $where = $this->request->getMore([
            ['time', '']
        ]);
        $data = $this->services->getType($where);
        return app('json')->success($data);
    }
}