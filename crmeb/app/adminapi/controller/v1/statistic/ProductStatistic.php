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
use app\services\statistic\ProductStatisticServices;
use think\facade\App;

/**
 * Class ProductStatistic
 * @package app\adminapi\controller\v1\statistic
 */
class ProductStatistic extends AuthController
{
    /**
     * ProductStatistic constructor.
     * @param App $app
     * @param ProductStatisticServices $services
     */
    public function __construct(App $app, ProductStatisticServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 商品基础
     * @return mixed
     */
    public function getBasic()
    {
        $where = $this->request->getMore([
            ['data', '', '', 'time']
        ]);
        return app('json')->success($this->services->getBasic($where));
    }

    /**
     * 商品趋势
     * @return mixed
     */
    public function getTrend()
    {
        $where = $this->request->getMore([
            ['data', '', '', 'time']
        ]);
        $where['time'] = $this->getDay($where['time']);
        return app('json')->success($this->services->getTrend($where));
    }

    /**
     * 商品排行
     * @return mixed
     */
    public function getProductRanking()
    {
        $where = $this->request->getMore([
            ['data', '', '', 'time'],
            ['sort', '']
        ]);
        $where['time'] = $this->getDay($where['time']);
        return app('json')->success($this->services->getProductRanking($where));
    }

    /**
     * 导出
     * @return mixed
     */
    public function getExcel()
    {
        $where = $this->request->getMore([
            ['data', '', '', 'time']
        ]);
        $where['time'] = $this->getDay($where['time']);
        return app('json')->success($this->services->getTrend($where, true));
    }

    /**
     * 格式化时间
     * @param $time
     * @return string
     */
    public function getDay($time)
    {
        if (strstr($time, '-') !== false) {
            [$startTime, $endTime] = explode('-', $time);
            if (!$startTime || !$endTime) {
                return date("Y/m/d 00:00:00", strtotime("-30 days", time())) . '-' . date("Y/m/d 23:59:59", time());
            } else {
                return date('Y/m/d 00:00:00', strtotime($startTime)).'-'.date('Y/m/d 23:59:59', strtotime($endTime));
            }
        } else {
            return date("Y/m/d 00:00:00", strtotime("-30 days", time())) . '-' . date("Y/m/d 23:59:59", time());
        }
    }
}
