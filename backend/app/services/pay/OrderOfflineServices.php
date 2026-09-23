<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\services\pay;


use crmeb\basic\BaseService;
use app\services\order\OrderSuccessServices;
use app\services\order\OrderInvoiceServices;
use app\dao\order\OrderCenterDao;
use app\jobs\ProductLogJob;
use app\services\user\UserServices;
use app\services\statistic\CapitalFlowServices;
use crmeb\exceptions\ApiException;
use think\facade\Log;

/**
 * 线下支付
 * Class OrderOfflineServices
 * @package app\services\pay
 */
class OrderOfflineServices extends BaseService
{

    /**
     * 线下支付
     * 适配 eb_order_center 表结构，构建兼容格式订单数组后统一走 paySuccess，
     * 由 paySuccess 内部完成 syncStatus + 拼团 + OrderPaySuccess 事件触发，
     * cardkey/virtual/hotel 等产品类型监听器通过事件完成自动履约。
     * @param int $id 订单中台主键ID
     * @return bool
     */
    public function orderOffline(int $id)
    {
        /** @var OrderCenterDao $orderCenterDao */
        $orderCenterDao = app()->make(OrderCenterDao::class);
        $centerOrder = $orderCenterDao->get($id);
        if (!$centerOrder) {
            throw new ApiException('订单不存在');
        }

        // eb_order_center 使用 pay_status 字段标识支付状态（0=未支付 1=已支付）
        if ((int)$centerOrder['pay_status'] === 1) {
            throw new ApiException('订单已支付');
        }

        $orderNo = $centerOrder['order_no'];
        $uid = (int)$centerOrder['uid'];

        // 解析 extra_info（ORM 可能返回数组或 JSON 字符串，做兼容处理）
        $extraInfo = $centerOrder['extra_info'];
        if (!is_array($extraInfo)) {
            $extraInfo = $extraInfo ? (json_decode($extraInfo, true) ?: []) : [];
        }

        // 构建兼容支付服务接口的订单数组
        // OrderPaySuccess 事件监听器（如 cardkey OrderPaidListener）依赖此格式的 order_id 字段（值为订单号）
        // 与 CenterOrderController::payment 保持一致，确保线上线下事件 payload 格式统一
        $orderInfo = [
            'id'             => (int)$centerOrder['id'],
            'order_id'       => $orderNo,
            'uid'            => $uid,
            'pay_uid'        => $uid,
            'pay_price'      => $centerOrder['pay_price'] ?? '0.00',
            'pay_postage'    => $centerOrder['pay_postage'] ?? '0.00',
            'paid'           => 0,
            'is_channel'     => 0,
            'unique'         => $orderNo,
            'is_cancel'      => 0,
            'is_del'         => 0,
            'is_system_del'  => 0,
            'combination_id' => (int)($extraInfo['combination_id'] ?? 0),
            'seckill_id'     => 0,
            'bargain_id'     => 0,
            'refund_status'  => 0,
            'real_name'      => $extraInfo['contact']['name'] ?? '',
            'storeName'      => $centerOrder['title'] ?? '',
        ];

        // 修改开票数据支付状态（eb_order_invoice.order_id 存储的是中台主键 id）
        $orderInvoiceServices = app()->make(OrderInvoiceServices::class);
        $orderInvoiceServices->update(['order_id' => $centerOrder['id']], ['is_pay' => 1]);

        // 资金流水记录（setFlow 依赖 $orderInfo['order_id'] 订单号、pay_type 支付方式）
        /** @var CapitalFlowServices $capitalFlowServices */
        $capitalFlowServices = app()->make(CapitalFlowServices::class);
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $userInfo = $userServices->get($uid);
        $orderInfo['nickname'] = $userInfo['nickname'] ?? '';
        $orderInfo['phone'] = $userInfo['phone'] ?? '';
        $orderInfo['pay_type'] = 'offline';
        $capitalFlowServices->setFlow($orderInfo, 'order');

        // 统一走 paySuccess：内部完成 syncStatus + 拼团创建 + OrderPaySuccess 事件触发
        // cardkey/virtual/hotel 等产品类型监听器通过 OrderPaySuccess 事件完成自动履约（发卡/发货等）
        /** @var OrderSuccessServices $successServices */
        $successServices = app()->make(OrderSuccessServices::class);
        $result = $successServices->paySuccess($orderInfo, 'offline');

        // 支付记录（order_id 使用中台主键，与 StoreProductLogServices::createLog 约定一致）
        ProductLogJob::dispatch(['pay', ['uid' => $uid, 'order_id' => $centerOrder['id']]]);

        return $result;
    }
}
