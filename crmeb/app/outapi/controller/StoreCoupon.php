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

use app\services\activity\coupon\StoreCouponIssueServices;
use app\services\product\product\StoreProductCouponServices;
use think\facade\App;

/**
 * 优惠券控制器
 * Class StoreCoupon
 * @package app\outapi\controller
 */
class StoreCoupon extends AuthController
{
    /**
     * StoreCoupon constructor.
     * @param App $app
     * @param StoreCouponIssueServices $service
     * @method temp
     */
    public function __construct(App $app, StoreCouponIssueServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 获取优惠券列表
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function lst()
    {
        $where = $this->request->getMore([
            ['status', 1],
            ['coupon_title', ''],
            ['receive_type', ''],
            ['type', ''],
        ]);
        $list = $this->services->getCouponList($where);
        return app('json')->success($list);
    }

    /**
     * 新增优惠券
     * @return void
     */
    public function save()
    {
        $data = $this->request->postMore([
            ['coupon_title', ''],
            ['coupon_price', 0.00],
            ['use_min_price', 0.00],
            ['coupon_time', 0],
            ['start_use_time', 0],
            ['end_use_time', 0],
            ['start_time', 0],
            ['end_time', 0],
            ['receive_type', 0],
            ['total_count', 0],
            ['status', 0],
        ]);
        $data['type'] = 0;
        $data['product_id'] = '';
        $data['category_id'] = 0;

        $data['is_permanent'] = 0;
        if ((int)$data['total_count'] == 0) {
            $data['is_permanent'] = 1;
        }
        $id = $this->services->saveCoupon($data);
        return app('json')->success(100000, ['id' => $id]);
    }

    /**
     * 修改优惠券状态
     * @param $id
     * @param $status
     * @return mixed
     */
    public function status($id, $status)
    {
        if ($id < 1 || !in_array((int)$status, [0, 1])) {
            return app('json')->fail(100100);
        }
        $this->services->update($id, ['status' => $status]);
        return app('json')->success(100001);
    }

    /**
     * 删除
     * @param string $id
     * @return mixed
     */
    public function delete($id)
    {
        if (!$id) return app('json')->fail(100100);

        $this->services->update($id, ['is_del' => 1]);
        /** @var StoreProductCouponServices $storeProductService */
        $storeProductService = app()->make(StoreProductCouponServices::class);
        //删除商品关联这个优惠券
        $storeProductService->delete(['issue_coupon_id' => $id]);
        return app('json')->success(100002);
    }
}