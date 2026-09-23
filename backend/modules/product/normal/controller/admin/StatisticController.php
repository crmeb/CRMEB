<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------

namespace modules\product\normal\controller\admin;

use app\adminapi\controller\AuthController;
use modules\product\normal\service\StatisticServices;

/**
 * 实物商品统计控制器。
 * 统计接口独立于通用商品统计，避免把其他商品类型混入结果。
 */
class StatisticController extends AuthController
{
    public function getBasic(StatisticServices $service)
    {
        return app('json')->success($service->getBasic($this->getWhere()));
    }

    public function getTrend(StatisticServices $service)
    {
        $where = $this->getWhere();
        return app('json')->success($service->getTrend($where));
    }

    public function getProductRanking(StatisticServices $service)
    {
        $where = $this->request->getMore([
            ['data', '', '', 'time'],
            ['sort', 'pay'],
        ]);
        $where['time'] = $this->getDay($where['time']);
        return app('json')->success($service->getProductRanking($where));
    }

    public function getExcel(StatisticServices $service)
    {
        return app('json')->success($service->getTrend($this->getWhere(), true));
    }

    protected function getWhere(): array
    {
        $where = $this->request->getMore([
            ['data', '', '', 'time'],
        ]);
        $where['time'] = $this->getDay($where['time']);
        return $where;
    }

    protected function getDay($time): string
    {
        if (is_string($time) && str_contains($time, '-')) {
            [$startTime, $endTime] = explode('-', $time, 2);
            if ($startTime && $endTime) {
                return date('Y/m/d 00:00:00', strtotime($startTime)) . '-' . date('Y/m/d 23:59:59', strtotime($endTime));
            }
        }
        return date('Y/m/d 00:00:00', strtotime('-30 days')) . '-' . date('Y/m/d 23:59:59');
    }
}
