<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2023 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\adminapi\controller\v1\statistic;


use app\adminapi\controller\AuthController;
use app\services\statistic\TradeStatisticServices;
use think\facade\App;

/**
 * Class TradeStatistic
 * @package app\controller\admin\v1\statistic
 */
class TradeStatistic extends AuthController
{
    /**
     * TradeStatistic constructor.
     * @param App $app
     * @param TradeStatisticServices $services
     */
    public function __construct(App $app, TradeStatisticServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 顶部数据
     * @return mixed
     */
    public function topTrade()
    {
        $leftToday = $this->services->getTopLeftTrade(['time' => 'today']);
        $leftyestoday = $this->services->getTopLeftTrade(['time' => 'yestoday']);
        $rightOne = $this->services->getTopRightOneTrade();
        $rightTwo = $this->services->getTopRightTwoTrade();
        $right = ['today' => $rightOne, 'month' => $rightTwo];
        $totalleft = [$leftToday, $leftyestoday];
        $left = [];
        foreach ($totalleft as $k => $v) {
            $left['name'] = "当日订单金额";
            $left['x'] = $v['curve']['x'];
            $left['series'][$k]['money'] = round($v['total_money'], 2);
            $left['series'][$k]['value'] = array_values($v['curve']['y']);
        }

        $data['left'] = $left;
        $data['right'] = $right;
        return app('json')->success($data);
    }

    /**
     * 底部数据
     * @return mixed
     */
    public function bottomTrade()
    {
        $day = $this->request->getMore([
            ['data', ""],
        ]);
        $bottom = $this->services->getBottomTrade($day);
        return app('json')->success($bottom);
    }

}
