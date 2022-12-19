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

namespace app\services\pay;


use app\services\BaseServices;
use app\services\order\StoreOrderDeliveryServices;
use app\services\order\StoreOrderInvoiceServices;
use app\services\order\StoreOrderServices;
use app\services\order\StoreOrderStatusServices;
use app\jobs\ProductLogJob;
use app\services\user\UserServices;
use app\services\statistic\CapitalFlowServices;
use crmeb\exceptions\ApiException;

/**
 * 线下支付
 * Class OrderOfflineServices
 * @package app\services\pay
 */
class OrderOfflineServices extends BaseServices
{

    /**
     * 线下支付
     * @param int $id
     * @return mixed
     */
    public function orderOffline(int $id)
    {
        /** @var StoreOrderServices $orderSerives */
        $orderSerives = app()->make(StoreOrderServices::class);
        $orderInfo = $orderSerives->get($id);
        if (!$orderInfo) {
            throw new ApiException(410173);
        }

        if ($orderInfo->paid) {
            throw new ApiException(410174);
        }
        $orderInfo->paid = 1;
        $orderInfo->pay_time = time();
        /** @var StoreOrderStatusServices $statusService */
        $statusService = app()->make(StoreOrderStatusServices::class);
        $res = $statusService->save([
            'oid' => $id,
            'change_type' => 'offline',
            'change_message' => '线下付款',
            'change_time' => time()
        ]);
        //修改开票数据支付状态
        $orderInvoiceServices = app()->make(StoreOrderInvoiceServices::class);
        $orderInvoiceServices->update(['order_id' => $orderInfo['id']], ['is_pay' => 1]);

        /** @var CapitalFlowServices $capitalFlowServices */
        $capitalFlowServices = app()->make(CapitalFlowServices::class);
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $userInfo = $userServices->get($orderInfo['uid']);
        $orderInfo['nickname'] = $userInfo['nickname'];
        $orderInfo['phone'] = $userInfo['phone'];
        $capitalFlowServices->setFlow($orderInfo, 'order');

        //虚拟商品自动发货
        if($orderInfo['virtual_type'] > 0){
            /** @var StoreOrderDeliveryServices $orderDeliveryServices */
            $orderDeliveryServices = app()->make(StoreOrderDeliveryServices::class);
            $orderDeliveryServices->virtualSend($orderInfo);
        }

        //支付记录
        ProductLogJob::dispatch(['pay', ['uid' => $orderInfo['uid'], 'order_id' => $orderInfo['id']]]);
        return $res && $orderInfo->save();
    }
}
