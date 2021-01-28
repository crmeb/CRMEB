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

namespace app\services\pay;


use app\services\BaseServices;
use app\services\order\StoreOrderInvoiceServices;
use app\services\order\StoreOrderServices;
use app\services\order\StoreOrderStatusServices;
use crmeb\jobs\ProductLogJob;
use crmeb\utils\Queue;
use think\exception\ValidateException;

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
            throw new ValidateException('订单不存在');
        }

        if ($orderInfo->paid) {
            throw new ValidateException('订单已支付');
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
        //支付记录
        Queue::instance()->job(ProductLogJob::class)->data('pay', ['uid' => $orderInfo['uid'], 'order_id' => $orderInfo['id']])->push();
        return $res && $orderInfo->save();
    }
}
