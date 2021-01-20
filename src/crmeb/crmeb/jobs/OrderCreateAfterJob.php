<?php


namespace crmeb\jobs;


use app\services\order\StoreOrderCartInfoServices;
use app\services\order\StoreOrderCreateServices;
use crmeb\basic\BaseJob;
use think\facade\Log;

class OrderCreateAfterJob extends BaseJob
{
    public function doJob($orderId, $cartInfo, $priceData, $order, $data)
    {
        try {
            /** @var StoreOrderCartInfoServices $cartServices */
            $cartServices = app()->make(StoreOrderCartInfoServices::class);
            /** @var StoreOrderCreateServices $createService */
            $createService = app()->make(StoreOrderCreateServices::class);
            $cartServices->setCartInfo($orderId, $createService->computeOrderProductTruePrice($cartInfo, $priceData));

            $createService->orderCreateAfter($order, $data);
        } catch (\Throwable $e) {
            Log::error('订单后置队列发生错误,错误原因:' . $e->getMessage());
        }
        return true;
    }
}