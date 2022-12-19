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
namespace app\api\controller\v2\store;

use app\services\activity\coupon\StoreCouponIssueServices;
use app\services\product\product\StoreProductCouponServices;
use app\Request;

class StoreCouponsController
{
    protected $services;

    public function __construct(StoreCouponIssueServices $services)
    {
        $this->services = $services;
    }

    /**
     * 可领取优惠券列表
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function lst(Request $request)
    {
        $where = $request->getMore([
            ['type', 0],
            ['product_id', 0]
        ]);
        return app('json')->success($this->services->getIssueCouponList($request->uid(), $where));
    }

    /**
     * 获取新人券
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getNewCoupon(Request $request)
    {
        $userInfo = $request->user();
        $data = [];
        /** @var StoreCouponIssueServices $couponService */
        $couponService = app()->make(StoreCouponIssueServices::class);
        $data['list'] = $couponService->getNewCoupon();
        $data['image'] = sys_config('coupon_img');
        if ($userInfo->add_time === $userInfo->last_time) {
            $data['show'] = 1;
        } else {
            $data['show'] = 0;
        }
        return app('json')->success($data);
    }

    /**
     * 赠送下单之后订单中 关联优惠劵
     * @param Request $request
     * @param $orderId
     * @return mixed
     */
    public function getOrderProductCoupon(Request $request, $orderId)
    {

        $uid = (int)$request->uid() ?? 0;
        if (!$orderId) {
            return app('json')->fail(100100);
        }
        /** @var StoreProductCouponServices $storeProductCoupon */
        $storeProductCoupon = app()->make(StoreProductCouponServices::class);
        $list = $storeProductCoupon->getOrderProductCoupon($uid, $orderId);
        return app('json')->success($list);
    }

    /**
     * 获取每日新增的优惠券
     * @return mixed
     */
    public function getTodayCoupon(Request $request)
    {
        $uid = $request->uid() ?? 0;
        /** @var StoreCouponIssueServices $couponService */
        $couponService = app()->make(StoreCouponIssueServices::class);
        $data['list'] = $couponService->getTodayCoupon($uid);
        $data['image'] = sys_config('coupon_img');
        return app('json')->success($data);
    }
}
