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
declare(strict_types=1);

namespace app\services\order;

use app\dao\order\OrderCenterDao;
use app\dao\order\OrderEconomizeDao;
use app\dao\order\OrderLogDao;
use app\dao\user\UserDao;
use app\jobs\notice\PrintJob;
use app\jobs\OrderInvoiceJob;
use app\jobs\OrderJob;
use app\jobs\ProductLogJob;
use app\model\order\OrderCenter;
use crmeb\basic\BaseService;
use app\services\statistic\CapitalFlowServices;
use app\services\system\admin\SystemAdminServices;
use app\services\product\product\StoreProductCouponServices;
use app\services\user\UserBillServices;
use app\services\user\UserServices;
use app\services\user\member\MemberCardServices;
use app\services\order\RefundExecutorServices;
use app\services\pay\PayServices;
use think\facade\Log;

/**
 * 订单生命周期通用服务
 * 提供所有产品类型共用的订单生命周期处理逻辑（写日志、送优惠券、开票、资金流水、打印、通知等）
 * 由各产品类型模块的监听器调用，避免代码重复
 * Class OrderLifecycleServices
 * @package app\services\order
 */
class OrderLifecycleServices extends BaseService
{
    /**
     * 按订单幂等增加购买次数，标记与计数同事务提交。
     * @param array $order 订单任务数据
     * @return void
     */
    public function recordPayCountAndPromoter(array $order): void
    {
        $orderNo = (string)($order['order_no'] ?? $order['order_id'] ?? '');
        if ($orderNo === '') {
            throw new \RuntimeException('支付后置任务缺少订单号');
        }
        $this->transaction(function () use ($orderNo): void {
            $centerDao = $this->app->make(OrderCenterDao::class);
            $center = $centerDao->getByOrderNoForUpdate($orderNo);
            if (!$center || !in_array((int)$center['pay_status'], [1, 2, 3], true)) {
                throw new \RuntimeException('支付后置任务的订单不存在或未支付');
            }
            if ((int)$center['pay_counted'] === 1) {
                return;
            }
            $user = $this->app->make(UserDao::class)->getByUidForUpdate((int)$center['uid']);
            if (!$user) {
                throw new \RuntimeException('支付后置任务的用户不存在');
            }
            $userServices = $this->app->make(UserServices::class);
            if (!$userServices->bcInc((int)$center['uid'], 'pay_count', '1', 'uid', 0)) {
                throw new \RuntimeException('购买次数更新失败');
            }
            if (!(int)$user['is_promoter']) {
                $price = $centerDao->sum(['pay_status' => 1, 'refund_status' => 0, 'uid' => (int)$center['uid']], 'pay_price');
                if (is_brokerage_statu($price)) {
                    if (!$userServices->update((int)$center['uid'], ['is_promoter' => 1])) {
                        throw new \RuntimeException('推广资格更新失败');
                    }
                }
            }
            if (!$centerDao->update((int)$center['id'], ['pay_counted' => 1])) {
                throw new \RuntimeException('购买次数幂等标记更新失败');
            }
        });
    }

    /**
     * 支付成功通用处理
     * @param array $orderInfo 订单信息
     */
    public function handlePaySuccess(array $orderInfo): void
    {
        $orderNo = $orderInfo['order_no'] ?? ($orderInfo['order_id'] ?? '');
        if (!$orderNo) {
            return;
        }

        // 写入 eb_order_log 状态日志
        /** @var OrderLogDao $logDao */
        $logDao = app()->make(OrderLogDao::class);
        $logDao->save([
            'order_no'      => $orderNo,
            'uid'           => $orderInfo['uid'] ?? 0,
            'operator_id'   => $orderInfo['uid'] ?? 0,
            'operator_type' => 'user',
            'from_status'   => OrderCenter::STATUS_PENDING_PAYMENT,
            'to_status'     => OrderCenter::STATUS_PROCESSING,
            'content'       => '订单处理中',
            'add_time'      => time(),
        ]);

        // 赠送商品优惠券（非活动订单）
        if (!($orderInfo['seckill_id'] ?? 0) && !($orderInfo['bargain_id'] ?? 0) && !($orderInfo['combination_id'] ?? 0)) {
            /** @var StoreProductCouponServices $storeProductCouponServices */
            $storeProductCouponServices = app()->make(StoreProductCouponServices::class);
            // 传入 uid 和订单号 order_no，方法内部通过 order_no 查询中台订单
            $storeProductCouponServices->giveOrderProductCoupon((int)$orderInfo['uid'], $orderInfo['order_id'] ?? '');
        }

        // 修改开票数据支付状态
        /** @var OrderInvoiceServices $orderInvoiceServices */
        $orderInvoiceServices = app()->make(OrderInvoiceServices::class);
        $invoiceInfo = $orderInvoiceServices->get(['order_id' => $orderInfo['id']]);
        if ($invoiceInfo) {
            $invoiceInfo->is_pay = 1;
            if ($invoiceInfo->save() && sys_config('elec_invoice', 1) == 1 && sys_config('auto_invoice', 1) == 1) {
                OrderInvoiceJob::dispatchSecs(10, 'autoInvoice', [$invoiceInfo['id']]);
            }
        }

        // 写入资金流水（仅第三方在线支付渠道有真实资金流入，渠道集合由 PayServices 从驱动注册中心动态获取）
        /** @var PayServices $payServices */
        $payServices = app()->make(PayServices::class);
        if ($payServices->isThirdPartyChannel((string)($orderInfo['pay_type'] ?? ''))) {
            /** @var UserServices $userServices */
            $userServices = app()->make(UserServices::class);
            $userInfo = $userServices->get($orderInfo['uid']);
            /** @var CapitalFlowServices $capitalFlowServices */
            $capitalFlowServices = app()->make(CapitalFlowServices::class);
            $orderInfo['nickname'] = $userInfo['nickname'];
            $orderInfo['phone'] = $userInfo['phone'];
            $capitalFlowServices->setFlow($orderInfo, 'order');
        }

        // 小票打印
        PrintJob::dispatch([$orderInfo['id'], 1]);

        // 支付成功后发送消息
        OrderJob::dispatch([$orderInfo]);

        // 商品日志记录支付记录
        ProductLogJob::dispatch(['pay', ['uid' => $orderInfo['uid'], 'order_id' => $orderInfo['id']]]);
    }

    /**
     * 订单取消通用处理
     * @param array $order 订单信息
     */
    public function handleCancel(array $order): void
    {
        // 取消订单回滚：积分/库存/优惠券走退款资金回滚引擎统一实现
        /** @var RefundExecutorServices $refundExecutor */
        $refundExecutor = app()->make(RefundExecutorServices::class);

        // 返还使用积分
        $refundExecutor->rollbackIntegral($order);

        // 回滚库存和销量
        $refundExecutor->rollbackStock($order);

        // 恢复下单时消费的优惠券
        $refundExecutor->rollbackCoupon($order);

        // 写入 eb_order_log
        /** @var OrderLogDao $logDao */
        $logDao = app()->make(OrderLogDao::class);
        $logDao->save([
            'order_no'      => $order['order_no'] ?? $order['order_id'] ?? '',
            'uid'           => $order['uid'] ?? 0,
            'operator_id'   => $order['uid'] ?? 0,
            'operator_type' => 'user',
            'from_status'   => OrderCenter::STATUS_PROCESSING,
            'to_status'     => OrderCenter::STATUS_CANCELED,
            'content'       => '订单已取消',
            'add_time'      => time(),
        ]);
    }

    /**
     * 订单完成 写入日志
     * @param array $order 订单信息
     */
    public function logComplete(array $order): void
    {
        try {
            /** @var OrderLogDao $logDao */
            $logDao = app()->make(OrderLogDao::class);
            $logDao->save([
                'order_no'      => $order['order_no'] ?? $order['order_id'] ?? '',
                'uid'           => $order['uid'] ?? 0,
                'operator_id'   => $order['uid'] ?? 0,
                'operator_type' => 'user',
                'from_status'   => OrderCenter::STATUS_PROCESSING,
                'to_status'     => OrderCenter::STATUS_COMPLETED,
                'content'       => '订单完成',
                'add_time'      => time(),
            ]);
        } catch (\Throwable $e) {
            Log::error('订单完成写入日志失败:' . $e->getMessage());
        }
    }

    /**
     * 发货通用处理：写入日志 + 同步物流信息到中台
     * @param array $orderInfo 订单信息
     * @param array $data 发货数据（delivery_type/delivery_name/delivery_id/delivery_code）
     * @param string $type 发货类型：postage/deliver/virtual
     */
    public function handleDelivery(array $orderInfo, array $data, string $type): void
    {
        $orderNo = $orderInfo['order_no'] ?? ($orderInfo['order_id'] ?? '');
        if (!$orderNo) {
            return;
        }

        try {
            // 写入 eb_order_log
            /** @var OrderLogDao $logDao */
            $logDao = app()->make(OrderLogDao::class);
            $deliveryTypeMap = ['postage' => '快递发货', 'deliver' => '配送发货', 'virtual' => '虚拟发货'];
            $deliveryDesc = $deliveryTypeMap[$type] ?? '商品已发货';
            $logDao->save([
                'order_no'      => $orderNo,
                'uid'           => $orderInfo['uid'] ?? 0,
                'operator_id'   => 0,
                'operator_type' => 'admin',
                'from_status'   => OrderCenter::STATUS_PROCESSING,
                'to_status'     => OrderCenter::STATUS_PROCESSING,
                'content'       => $deliveryDesc . ($data['delivery_name'] ?? '') . ($data['delivery_id'] ? '，运单号：' . $data['delivery_id'] : ''),
                'add_time'      => time(),
            ]);

            // 同步物流信息到中台 extra_info.delivery
            $centerDao = app(OrderCenterDao::class);
            $centerRecord = $centerDao->get(['order_no' => $orderNo]);
            if (!$centerRecord) {
                return;
            }

            $deliveryType = $data['delivery_type'] ?? ($orderInfo['delivery_type'] ?? '');
            $deliveryName = $data['delivery_name'] ?? ($orderInfo['delivery_name'] ?? '');
            $deliveryId   = $data['delivery_id'] ?? ($orderInfo['delivery_id'] ?? '');
            $deliveryCode = $data['delivery_code'] ?? ($orderInfo['delivery_code'] ?? '');

            if (empty($deliveryType) && empty($deliveryName) && empty($deliveryId)) {
                return;
            }

            $extraInfo = $centerRecord['extra_info'] ? json_decode($centerRecord['extra_info'], true) : [];
            $extraInfo['delivery'] = [
                'delivery_type' => $deliveryType,
                'delivery_name' => $deliveryName,
                'delivery_id'   => $deliveryId,
                'delivery_code' => $deliveryCode,
            ];
            // 电子面单发货时持久化面单快照（快递公司/模板/寄件人），供发货后补打面单使用
            if (!empty($data['express_dump'])) {
                $extraInfo['delivery']['express_dump'] = $data['express_dump'];
            }

            $centerDao->update(['id' => $centerRecord['id']], [
                'extra_info'    => json_encode($extraInfo),
                'delivery_time' => time(),
            ]);
        } catch (\Throwable $e) {
            Log::error('订单发货同步中台失败:' . $e->getMessage());
        }
    }

    /**
     * 收货通用处理：写入日志 + 状态 + 账单更新
     * @param array $order 订单信息
     * @param string $toStatus 收货后的目标中台状态
     * @param string $content 日志内容
     * @param string $operatorType 操作类型
     * @param int $operatorId 操作人ID
     */
    public function handleTake(
        array $order,
        string $toStatus = OrderCenter::STATUS_COMPLETED,
        string $content = '已收货',
        string $operatorType = 'user',
        int $operatorId = 0
    ): void
    {
        try {
            // 修改收货状态
            /** @var UserBillServices $userBillServices */
            $userBillServices = app()->make(UserBillServices::class);
            $userBillServices->takeUpdate((int)$order['uid'], (int)$order['id']);

            // 写入 eb_order_log
            /** @var OrderLogDao $logDao */
            $logDao = app()->make(OrderLogDao::class);
            $logDao->save([
                'order_no'      => $order['order_no'] ?? $order['order_id'] ?? '',
                'uid'           => $order['uid'] ?? 0,
                'operator_id'   => $operatorId > 0 ? $operatorId : ($order['uid'] ?? 0),
                'operator_type' => $operatorType,
                'from_status'   => OrderCenter::STATUS_PROCESSING,
                'to_status'     => $toStatus,
                'content'       => $content,
                'add_time'      => time(),
            ]);
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            throw $e;
        }
    }

    /**
     * 退款申请 写入日志
     * @param array $order 订单信息
     */
    public function logRefundCreate(array $order): void
    {
        try {
            /** @var OrderLogDao $logDao */
            $logDao = app()->make(OrderLogDao::class);
            $logDao->save([
                'order_no'      => $order['order_no'] ?? $order['order_id'] ?? '',
                'uid'           => $order['uid'] ?? 0,
                'operator_id'   => $order['uid'] ?? 0,
                'operator_type' => 'user',
                'from_status'   => OrderCenter::STATUS_PROCESSING,
                'to_status'     => OrderCenter::STATUS_AFTER_SALE,
                'content'       => '用户申请退款',
                'add_time'      => time(),
            ]);
        } catch (\Throwable $e) {
            Log::error('订单退款申请监听失败,失败原因:' . $e->getMessage());
        }
    }

    /**
     * 退款成功 写入日志 + 商品日志
     * @param array $order 订单信息
     * @param array $refundData 退款数据
     */
    public function logRefundSuccess(array $order, array $refundData): void
    {
        try {
            /** @var OrderLogDao $logDao */
            $logDao = app()->make(OrderLogDao::class);
            $logDao->save([
                'order_no'      => $order['order_no'] ?? $order['order_id'] ?? '',
                'uid'           => $order['uid'] ?? 0,
                'operator_id'   => 0,
                'operator_type' => 'admin',
                'from_status'   => OrderCenter::STATUS_AFTER_SALE,
                'to_status'     => OrderCenter::STATUS_REFUNDED,
                'content'       => '退款成功，退款金额：¥' . ($refundData['refund_price'] ?? '0.00'),
                'add_time'      => time(),
            ]);

            ProductLogJob::dispatch(['refund', ['uid' => $order['uid'], 'order_id' => $order['id']]]);
        } catch (\Throwable $e) {
            Log::error('订单退款成功监听失败,失败原因:' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 退款拒绝 写入日志
     * @param array $orderRefundInfo 退款记录信息
     */
    public function logRefundRefuse(array $orderRefundInfo): void
    {
        try {
            // 退款记录中的 store_order_id 关联到 eb_store_order.id，需查询实际订单号
            $storeOrderId = $orderRefundInfo['store_order_id'] ?? 0;
            $orderNo = '';
            $uid = $orderRefundInfo['uid'] ?? 0;
            if ($storeOrderId) {
                $storeOrder = app()->make(\app\dao\order\OrderCenterDao::class)->get($storeOrderId);
                $orderNo = $storeOrder['order_id'] ?? '';
            }
            if (!$orderNo) {
                return;
            }
            /** @var OrderLogDao $logDao */
            $logDao = app()->make(OrderLogDao::class);
            $refuseReason = $orderRefundInfo['refuse_reason'] ?? '';
            $logDao->save([
                'order_no'      => $orderNo,
                'uid'           => $uid,
                'operator_id'   => 0,
                'operator_type' => 'admin',
                'from_status'   => OrderCenter::STATUS_AFTER_SALE,
                'to_status'     => OrderCenter::STATUS_PROCESSING,
                'content'       => '退款申请被拒绝' . ($refuseReason ? '，原因：' . $refuseReason : ''),
                'add_time'      => time(),
            ]);
        } catch (\Throwable $e) {
            Log::error('订单退款拒绝监听失败,失败原因:' . $e->getMessage());
        }
    }

    /**
     * 退款取消 写入日志
     * @param array $orderRefundInfo 退款记录信息
     */
    public function logRefundCancel(array $orderRefundInfo): void
    {
        try {
            $storeOrderId = $orderRefundInfo['store_order_id'] ?? 0;
            $orderNo = '';
            $uid = $orderRefundInfo['uid'] ?? 0;
            if ($storeOrderId) {
                $storeOrder = app()->make(\app\dao\order\OrderCenterDao::class)->get($storeOrderId);
                $orderNo = $storeOrder['order_id'] ?? '';
            }
            if (!$orderNo) {
                return;
            }
            /** @var OrderLogDao $logDao */
            $logDao = app()->make(OrderLogDao::class);
            $logDao->save([
                'order_no'      => $orderNo,
                'uid'           => $uid,
                'operator_id'   => $uid,
                'operator_type' => 'user',
                'from_status'   => OrderCenter::STATUS_AFTER_SALE,
                'to_status'     => OrderCenter::STATUS_PROCESSING,
                'content'       => '用户取消退款申请',
                'add_time'      => time(),
            ]);
        } catch (\Throwable $e) {
            Log::error('退款取消写入日志失败:' . $e->getMessage());
        }
    }

    /**
     * 评价 写入日志 + 通知管理员
     * @param array $replyInfo 评价信息
     * @param array $order 订单信息
     */
    public function logComment(array $replyInfo, array $order): void
    {
        try {
            /** @var OrderLogDao $logDao */
            $logDao = app()->make(OrderLogDao::class);
            $logDao->save([
                'order_no'      => $order['order_no'] ?? $order['order_id'] ?? '',
                'uid'           => $order['uid'] ?? 0,
                'operator_id'   => $order['uid'] ?? 0,
                'operator_type' => 'user',
                'from_status'   => OrderCenter::STATUS_PROCESSING,
                'to_status'     => OrderCenter::STATUS_COMPLETED,
                'content'       => '用户已评价',
                'add_time'      => time(),
            ]);

            // 通知管理员有新评价
            /** @var SystemAdminServices $systemAdmin */
            $systemAdmin = app()->make(SystemAdminServices::class);
            $systemAdmin->adminNewPush();
        } catch (\Throwable $e) {
            Log::error('订单评价事件处理失败：' . $e->getMessage());
        }
    }

    /**
     * 删除订单 写入日志
     * @param array $order 订单信息
     * @param string $deleteType 删除类型：admin_delete/user_delete/admin_batch_delete
     */
    public function logDelete(array $order, string $deleteType): void
    {
        try {
            $isAdmin = in_array($deleteType, ['admin_delete', 'admin_batch_delete']);
            /** @var OrderLogDao $logDao */
            $logDao = app()->make(OrderLogDao::class);
            $logDao->save([
                'order_no'      => $order['order_no'] ?? $order['order_id'] ?? '',
                'uid'           => $order['uid'] ?? 0,
                'operator_id'   => $isAdmin ? 0 : ($order['uid'] ?? 0),
                'operator_type' => $isAdmin ? 'admin' : 'user',
                'from_status'   => '',
                'to_status'     => '',
                'content'       => $isAdmin ? '管理员删除订单' : '用户删除订单',
                'add_time'      => time(),
            ]);
        } catch (\Throwable $e) {
            Log::error('订单删除写入日志失败:' . $e->getMessage());
        }
    }

    /**
     * 订单改价 写入日志
     * @param array $order 订单信息
     * @param mixed $payPrice 新价格
     */
    public function logPriceChange(array $order, $payPrice): void
    {
        try {
            /** @var OrderLogDao $logDao */
            $logDao = app()->make(OrderLogDao::class);
            $logDao->save([
                'order_no'      => $order['order_no'] ?? $order['order_id'] ?? '',
                'uid'           => $order['uid'] ?? 0,
                'operator_id'   => 0,
                'operator_type' => 'admin',
                'from_status'   => OrderCenter::STATUS_PROCESSING,
                'to_status'     => OrderCenter::STATUS_PROCESSING,
                'content'       => '管理员修改订单价格，新价格：¥' . ($payPrice ?? '0.00'),
                'add_time'      => time(),
            ]);
        } catch (\Throwable $e) {
            Log::error('订单改价监听失败,失败原因:' . $e->getMessage());
        }
    }

    /**
     * 记录会员省钱明细（普通商品订单支付成功后）
     * 数据源全部取中台订单列：会员价节约=vip_discount、券抵扣=coupon_price、运费=pay_postage，
     * 替代旧版遍历 eb_store_order_cart_info 累计 vip_truePrice 的断裂链路（modules 脱壳后新订单不再写购物车快照表）
     * @param array $orderInfo 订单信息（order_id 键为订单号别名，与 OrderPaySuccess 事件载荷对齐）
     * @return bool 是否写入（false=跳过或已记录）
     */
    public function recordEconomize(array $orderInfo): bool
    {
        $orderNo = (string)($orderInfo['order_id'] ?? $orderInfo['order_no'] ?? '');
        if (!$orderNo) {
            return false;
        }
        // 事件载荷字段不全，按订单号回查中台补全金额列
        $order = $this->app->make(OrderCenterDao::class)->get(['order_no' => $orderNo]);
        if (!$order) {
            return false;
        }
        /** @var OrderEconomizeDao $economizeDao */
        $economizeDao = $this->app->make(OrderEconomizeDao::class);
        // 幂等：同订单号已记录则跳过
        if ($economizeDao->getOne(['order_id' => $orderNo])) {
            return false;
        }
        // 仅付费会员记录
        /** @var UserServices $userServices */
        $userServices = $this->app->make(UserServices::class);
        $userInfo = $userServices->getUserInfo((int)$order['uid']);
        if (!$userInfo || $userInfo['is_money_level'] <= 0) {
            return false;
        }
        $save = [
            'order_type'   => 1,
            'add_time'     => time(),
            'pay_price'    => (string)$order['pay_price'],
            'order_id'     => $orderNo,
            'uid'          => (int)$order['uid'],
            // 会员价节约：中台 vip_discount 列（下单时已按会员价开关计算）
            'member_price' => (float)$order['vip_discount'],
            // 券节约：中台 coupon_price 列（口径放宽——旧版仅记会员专享券，现含全部券抵扣）
            'coupon_price' => (float)$order['coupon_price'],
        ];
        // 运费节约：会员运费折扣权益（express 权益值为折扣率百分比，100=不折）
        /** @var MemberCardServices $memberCardService */
        $memberCardService = $this->app->make(MemberCardServices::class);
        $expressRate = $memberCardService->isOpenMemberCard('express');
        if ($expressRate) {
            $postage = (string)$order['pay_postage'];
            // 原运费 = 实付运费 / 折扣率，差额即会员节约
            $originPostage = bcdiv($postage, bcdiv((string)$expressRate, '100', 2), 2);
            $save['postage_price'] = bcsub($originPostage, $postage, 2);
        }
        return (bool)$economizeDao->save($save);
    }

    /**
     * 记录会员省钱明细（线下收银订单支付成功后）
     * @param array $order 线下收银单（OfflineReceiptServices::paySuccess 已注入 order_id/money/pay_price 兼容键）
     * @return bool 是否写入
     */
    public function recordOfflineEconomize(array $order): bool
    {
        // type=3 为线下收银单
        if (($order['type'] ?? 0) != 3) {
            return false;
        }
        /** @var MemberCardServices $memberRightService */
        $memberRightService = $this->app->make(MemberCardServices::class);
        // 会员线下支付权益开启时才记录
        if (!$memberRightService->isOpenMemberCard('offline')) {
            return false;
        }
        /** @var OrderEconomizeDao $economizeDao */
        $economizeDao = $this->app->make(OrderEconomizeDao::class);
        // 幂等：同订单号已记录则跳过（表有 order_id+uid 唯一键，重复派发任务时避免唯一键冲突）
        if ($economizeDao->getOne(['order_id' => (string)($order['order_id'] ?? ''), 'uid' => (int)$order['uid']])) {
            return false;
        }
        return (bool)$economizeDao->save([
            'uid'           => (int)$order['uid'],
            'order_id'      => (string)($order['order_id'] ?? ''),
            'order_type'    => 2,
            'pay_price'     => (string)($order['pay_price'] ?? 0),
            // add_time 必传：表列 NOT NULL，ORM strict 模式下缺失键会绑 NULL 报 1048（旧版线下链路即因缺此键写入必炸）
            'add_time'      => time(),
            // 线下优惠 = 应收 - 实付
            'offline_price' => bcsub((string)($order['money'] ?? 0), (string)($order['pay_price'] ?? 0), 2),
        ]);
    }
}
