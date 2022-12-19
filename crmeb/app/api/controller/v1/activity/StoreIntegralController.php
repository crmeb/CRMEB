<?php

namespace app\api\controller\v1\activity;

use app\Request;
use app\services\activity\integral\StoreIntegralServices;

class StoreIntegralController
{
    protected $services;

    public function __construct(StoreIntegralServices $services)
    {
        $this->services = $services;
    }

    /**
     * 积分商城首页数据
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
        $data['banner'] = sys_data('integral_shop_banner') ?? [];//TODO 积分商城banner
        $where = ['is_show' => 1];
        $where['is_host'] = 1;
        $data['list'] = $this->services->getIntegralList($where);
        return app('json')->success(get_thumb_water($data, 'big'));
    }

    /**
     * 商品列表
     * @param Request $request
     * @return mixed
     */
    public function lst(Request $request)
    {
        $where = $request->getMore([
            ['store_name', ''],
            ['priceOrder', ''],
            ['salesOrder', ''],
        ]);
        $where['is_show'] = 1;
        $list = $this->services->getIntegralList($where);
        return app('json')->success(get_thumb_water($list, 'mid'));
    }

    /**
     * 积分商品详情
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function detail(Request $request, $id)
    {
        $data = $this->services->integralDetail($request, $id);
        return app('json')->success($data);
    }
}
