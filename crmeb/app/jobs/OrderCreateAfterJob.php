<?php


namespace app\jobs;


use app\services\order\StoreOrderCartInfoServices;
use app\services\order\StoreOrderCreateServices;
use app\services\order\StoreOrderComputedServices;
use app\services\user\UserServices;
use crmeb\basic\BaseJobs;
use crmeb\traits\QueueTrait;
use think\facade\Log;

class OrderCreateAfterJob extends BaseJobs
{
    use QueueTrait;

    /**
     * 订单后置处理
     * @param $orderId
     * @param $cartInfo
     * @param $priceData
     * @param $order
     * @param $data
     * @return bool
     */
    public function doJob($orderInfo, $data, $activity)
    {
        $uid = (int)$orderInfo['uid'];
        $orderId = (int)$orderInfo['id'];
        try {
            $cartInfo = $data['cartInfo'] ?? [];
            $priceData = $data['priceData'] ?? [];
            $addressId = $data['addressId'] ?? 0;
            $spread_ids = [];
            /** @var StoreOrderCreateServices $createService */
            $createService = app()->make(StoreOrderCreateServices::class);
            if ($cartInfo && $priceData) {
                /** @var StoreOrderCartInfoServices $cartServices */
                $cartServices = app()->make(StoreOrderCartInfoServices::class);
                [$cartInfo, $spread_ids] = $createService->computeOrderProductTruePrice($cartInfo, $priceData, $addressId, $uid);
                $cartServices->updateCartInfo($orderId, $cartInfo);
            }

            $orderData = [];
            $spread_uid = $spread_two_uid = 0;
            /** @var UserServices $userServices */
            $userServices = app()->make(UserServices::class);
            if ($spread_ids) {
                [$spread_uid, $spread_two_uid] = $spread_ids;
                $orderData['spread_uid'] = $spread_uid;
                $orderData['spread_two_uid'] = $spread_two_uid;
            } else {
                $spread_uid = $userServices->getSpreadUid($uid);
                $orderData = ['spread_uid' => 0, 'spread_two_uid' => 0];
                if ($spread_uid) {
                    $orderData['spread_uid'] = $spread_uid;
                }
                if ($spread_uid > 0) {
                    $spread_two_uid = $userServices->getSpreadUid($spread_uid, [], false);
                    if ($spread_two_uid) {
                        $orderData['spread_two_uid'] = $spread_two_uid;
                    }
                }
            }

            if ($cartInfo && !$activity) {
                /** @var StoreOrderComputedServices $orderComputed */
                $orderComputed = app()->make(StoreOrderComputedServices::class);
                if ($userServices->checkUserPromoter($spread_uid)) $orderData['one_brokerage'] = $orderComputed->getOrderSumPrice($cartInfo, 'one_brokerage', false);
                if ($userServices->checkUserPromoter($spread_two_uid)) $orderData['two_brokerage'] = $orderComputed->getOrderSumPrice($cartInfo, 'two_brokerage', false);
            }
            $createService->update(['id' => $orderId], $orderData);
        } catch (\Throwable $e) {
            Log::error('计算订单实际优惠、积分、邮费、佣金失败，原因：' . $e->getMessage());
        }

        return true;
    }
}
