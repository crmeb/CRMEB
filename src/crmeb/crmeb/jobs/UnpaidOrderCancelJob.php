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

namespace crmeb\jobs;


use app\services\order\StoreOrderCartInfoServices;
use app\services\order\StoreOrderRefundServices;
use app\services\order\StoreOrderServices;
use crmeb\basic\BaseJob;
use think\facade\Log;

/**
 * 未支付订单到期取消
 * Class UnpaidOrderCancelJob
 * @package crmeb\jobs
 */
class UnpaidOrderCancelJob extends BaseJob
{

    public function doJob($orderId)
    {
        /** @var StoreOrderServices $services */
        $services = app()->make(StoreOrderServices::class);
        $orderInfo = $services->get($orderId);
        if (!$orderInfo) {
            return true;
        }
        if ($orderInfo->paid) {
            return true;
        }
        if ($orderInfo->is_del) {
            return true;
        }
        if ($orderInfo->pay_type == 'offline') {
            return true;
        }
        /** @var StoreOrderCartInfoServices $cartServices */
        $cartServices = app()->make(StoreOrderCartInfoServices::class);
        $cartInfo = $cartServices->getOrderCartInfo($orderId);
        /** @var StoreOrderRefundServices $refundServices */
        $refundServices = app()->make(StoreOrderRefundServices::class);

        try {
            $res = $refundServices->transaction(function () use ($orderInfo, $refundServices) {
                //回退积分和优惠卷
                $refundServices->integralAndCouponBack($orderInfo);
                //回退库存和销量
                $refundServices->regressionStock($orderInfo);
                return true;
            });
            if ($res) {
                $orderInfo->is_del = 1;
                $orderInfo->mark = '订单未支付已超过系统预设时间';
                $orderInfo->save();
            }
            return $res;
        } catch (\Throwable $e) {
            Log::error('自动取消订单失败,失败原因:' . $e->getMessage());
            return false;
        }
    }
}
