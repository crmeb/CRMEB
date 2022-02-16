<?php

namespace app\adminapi\controller\v1\marketing\integral;

use app\adminapi\controller\AuthController;
use app\services\activity\integral\StorePointRecordServices;
use think\facade\App;

class StorePointRecord extends AuthController
{
    /**
     * @param App $app
     * @param StorePointRecordServices $services
     */
    public function __construct(App $app, StorePointRecordServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 积分记录
     * @return mixed
     */
    public function pointRecord()
    {
        $where = $this->request->getMore([
            ['time', ''],
            ['trading_type', 0]
        ]);
        $date = $this->services->pointRecord($where);
        return app('json')->success($date);
    }

    /**
     * 积分记录备注
     * @return mixed
     */
    public function pointRecordRemark($id = 0)
    {
        [$mark] = $this->request->postMore([
            ['mark', '']
        ], true);
        if (!$id) return app('json')->fail('参数错误');
        if ($mark === '') return app('json')->fail('备注不能为空');
        $this->services->recordRemark($id, $mark);
        return app('json')->success('备注成功');
    }

    /**
     * 积分统计基础信息
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
     * 积分统计趋势图
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
     * 积分来源
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
     * 积分消耗
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
