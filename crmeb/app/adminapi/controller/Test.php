<?php
/**
 * @author: liaofei<136327134@qq.com>
 * @day: 2020/9/12
 */

namespace app\adminapi\controller;

use app\services\coupon\StoreCouponUserServices;
use app\services\order\StoreOrderCreateServices;
use crmeb\services\AliPayService;

class Test
{
    public function index(StoreOrderCreateServices $services)
    {
        /** @var StoreCouponUserServices $couponServices */
        $couponServices = app()->make(StoreCouponUserServices::class);
        $couponInfo = $couponServices->getOne([['id', '=', 2], ['uid', '=', 1], ['is_fail', '=', 0], ['status', '=', 1], ['start_time', '<', time()], ['end_time', '>', time()]], '*', ['issue']);
        dd($couponInfo);
    }
}

