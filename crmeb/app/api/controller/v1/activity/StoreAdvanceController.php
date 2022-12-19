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

namespace app\api\controller\v1\activity;

use app\Request;
use app\services\activity\advance\StoreAdvanceServices;

/**
 * 预售控制器
 * Class StoreAdvanceController
 * @package app\api\controller\v1\activity
 */
class StoreAdvanceController
{
    /**
     * StoreAdvanceController constructor.
     * @param StoreAdvanceServices $services
     */
    public function __construct(StoreAdvanceServices $services)
    {
        $this->services = $services;
    }

    /**
     * 预售列表
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index(Request $request)
    {
        $where = $request->getMore([
            ['time_type', 0]
        ]);
        $where['status'] = 1;
        $data = $this->services->getList($where);
        return app('json')->success($data);
    }

    /**
     * 预售商品详情
     * @param Request $request
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function detail(Request $request, $id)
    {
        $data = $this->services->getAdvanceinfo($request, $id);
        return app('json')->success($data);
    }
}
