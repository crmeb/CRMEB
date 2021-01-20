<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
namespace app\api\controller\v2\store;

use app\services\coupon\StoreCouponIssueServices;
use app\services\product\product\StoreProductCouponServices;
use think\Request;

class StoreCouponsController
{
    protected $services;

    public function __construct(StoreCouponIssueServices $services)
    {
        $this->services = $services;
    }

    /**
     * 可领取优惠券列表
     * @param \app\Request $request
     * @return mixed
     */
    public function lst(Request $request)
    {
        $where = $request->getMore([
            ['type', 0],
            ['product_id', 0]
        ]);
        return app('json')->successful($this->services->getIssueCouponList($request->uid(), $where));
    }

    /**
     * 获取新人券
     * @return mixed
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
        //会员领取优惠券
        //$couponService->sendMemberCoupon($userInfo->uid);
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

        $uid = (int)$request->uid();
        if (!$orderId) {
            return app('json')->fail('参数错误');
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
    public function getTodayCoupon()
    {
        /** @var StoreCouponIssueServices $couponService */
        $couponService = app()->make(StoreCouponIssueServices::class);
        $data['list'] = $couponService->getTodayCoupon();
        $data['image'] = sys_config('coupon_img');
        return app('json')->success($data);
    }
}
