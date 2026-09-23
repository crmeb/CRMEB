<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace app\services\order;


use app\dao\order\OrderCenterDao;
use app\model\order\OrderCenter;
use app\services\order\OrderCenterServices;
use app\services\order\OrderLifecycleServices;
use crmeb\basic\BaseService;
use app\services\personnel\ServicePersonnelServices;
use app\services\system\store\SystemStoreStaffServices;
use app\services\user\UserServices;
use app\services\order\AfterSaleServices;
use crmeb\trade\order\type\OrderTypeRegistry;
use crmeb\exceptions\ApiException;

/**
 * 核销订单
 * 基于订单中台表 eb_order_center 实现
 * 核销码已归属中台表实体列 verify_code
 */
class OrderWriteOffServices extends BaseService
{
    /**
     * @var OrderCenterDao
     */
    protected $dao;

    /**
     * 构造方法
     * OrderWriteOffServices constructor.
     * @param OrderCenterDao $dao
     */
    public function __construct(OrderCenterDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 订单核销
     * 通过中台表 verify_code 查询并执行核销
     * @param string $code 核销码
     * @param int $confirm 0=确认页 1=执行核销
     * @param int $uid 操作用户ID
     * @param int $auth 是否跳过权限校验
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function writeOffOrder(string $code, int $confirm, int $uid = 0, $auth = 0)
    {
        // 1. 查询中心表：核销码、已支付、无退款、未删除
        $orderInfo = $this->dao->getOne([
            ['verify_code', '=', $code],
            ['pay_status', '=', 1],          // 已支付
            ['refund_status', '=', 0],        // 无退款
            ['is_del', '=', 0],               // 未删除
        ]);
        if (!$orderInfo) {
            throw new ApiException('订单不存在');
        }

        // 2. 从 extra_info 获取配送方式等扩展信息
        $extraInfo = [];
        if (!empty($orderInfo['extra_info'])) {
            $extraInfo = is_string($orderInfo['extra_info'])
                ? (json_decode($orderInfo['extra_info'], true) ?: [])
                : $orderInfo['extra_info'];
        }
        // 3. 校验能否核销
        if (!$orderInfo['verify_code']) {
            throw new ApiException('此订单不能被核销');
        }

        $statusCode = (string)($orderInfo['status_code'] ?? '');
        if ($statusCode === OrderCenter::STATUS_COMPLETED) {
            throw new ApiException('该订单已被核销');
        }
        if (in_array($statusCode, [
            OrderCenter::STATUS_CANCELED,
            OrderCenter::STATUS_AFTER_SALE,
            OrderCenter::STATUS_REFUNDING,
            OrderCenter::STATUS_REFUNDED,
            OrderCenter::STATUS_DELETED,
        ], true)) {
            throw new ApiException('订单当前状态不能核销');
        }

        // 4. 售后检查（售后引擎拦截：存在处理中售后单不可核销）
        /** @var AfterSaleServices $afterSaleServices */
        $afterSaleServices = app()->make(AfterSaleServices::class);
        if ($afterSaleServices->hasActiveByOrderId((int)$orderInfo['id'])) {
            throw new ApiException('订单有售后申请请先处理');
        }

        // 5. 权限校验
        if ($uid) {
            $isAuth = true;
            switch ($orderInfo['shipping_type']) {
                case 1: // 配送订单 - 配送员核销
                    /** @var ServicePersonnelServices $deliverServiceServices */
                    $deliverServiceServices = app()->make(ServicePersonnelServices::class);
                    $isAuth = $deliverServiceServices->getCount(['uid' => $uid, 'status' => 1]) > 0;
                    break;
                case 2: // 自提订单 - 店员核销
                    /** @var SystemStoreStaffServices $storeStaffServices */
                    $storeStaffServices = app()->make(SystemStoreStaffServices::class);
                    $staffInfo = $storeStaffServices->get(['uid' => $uid, 'verify_status' => 1, 'status' => 1]);
                    if ($staffInfo) {
                        $isAuth = true;
                        $orderInfo->store_id = $staffInfo->store_id;
                    } else {
                        $isAuth = false;
                    }
                    break;
            }
            if (!$isAuth && $auth == 0) {
                throw new ApiException('您无权限核销此订单，请联系管理员');
            }
        }

        // 6. 拼团检查：使用订单关联的 pink_id，而不是拼团活动 activity_id
        if ($orderInfo['activity_type'] === 'combination') {
            $pinkId = (int)($extraInfo['pink_id'] ?? 0);
            if (!$pinkId) {
                throw new ApiException('拼团信息不存在，暂不能核销');
            }
            // 通过 ActivityDataProxy 事件代理查询拼团状态（避免直接引用 modules 层）
            $_results = event('ActivityDataProxy', [
                'mode' => 'combination',
                'sub_service' => 'pink',
                'action' => 'get',
                'args' => [$pinkId],
            ]);
            $pinkInfo = proxy_event_result($_results);
            if (!$pinkInfo || (int)($pinkInfo['status'] ?? 0) !== 2) {
                throw new ApiException('拼团订单暂未成功无法核销');
            }
        }

        // 7. 确认页（不执行核销，返回订单信息）
        if ($confirm == 0) {
            /** @var UserServices $services */
            $services = app()->make(UserServices::class);
            $orderInfo['nickname'] = $services->value(['uid' => $orderInfo['uid']], 'nickname');
            $orderInfo['order_id'] = $orderInfo['order_no'] ?? ($orderInfo['order_id'] ?? '');
            return $orderInfo->toArray();
        }

        $orderInfo['order_id'] = $orderInfo['order_no'] ?? ($orderInfo['order_id'] ?? '');

        // 8. 执行核销：到店自提直接进入完成态，并补齐核销时间
        $now = time();
        if ((int)($orderInfo['shipping_type'] ?? 0) === 2) {
            $orderPayload = is_object($orderInfo) ? $orderInfo->toArray() : (array)$orderInfo;
            $orderPayload['delivery_time'] = $now;
            $orderPayload['take_time'] = $now;

            /** @var OrderLifecycleServices $lifecycleServices */
            $lifecycleServices = app()->make(OrderLifecycleServices::class);
            $lifecycleServices->handleTake(
                $orderPayload,
                OrderCenter::STATUS_COMPLETED,
                '到店核销',
                'admin',
                $uid > 0 ? $uid : 0
            );

            try {
                OrderTypeRegistry::getAdapter((string)($orderInfo['source_type'] ?? ''))
                    ->advance((string)($orderInfo['order_no'] ?? ''), OrderCenter::STATUS_COMPLETED);
            } catch (\Throwable $e) {
                $this->dao->update($orderInfo['id'], [
                    'status_code' => OrderCenter::STATUS_COMPLETED,
                ]);
            }

            /** @var OrderCenterServices $centerServices */
            $centerServices = app()->make(OrderCenterServices::class);
            $centerServices->updateRecord((string)($orderInfo['order_no'] ?? ''), [
                'delivery_time' => $now,
                'take_time'     => $now,
            ]);

            $orderInfo['status_code']   = OrderCenter::STATUS_COMPLETED;
            $orderInfo['delivery_time'] = $now;
            $orderInfo['take_time']     = $now;
        } else {
            // 其他核销码订单：保持原有完成逻辑
            $this->dao->update($orderInfo['id'], [
                'status_code' => OrderCenter::STATUS_COMPLETED,
            ]);
            $orderInfo['status_code'] = OrderCenter::STATUS_COMPLETED;
        }

        // 9. 核销后续处理（赠送积分、发放佣金等）
        /** @var OrderTakeServices $storeOrderTask */
        $storeOrderTask = app()->make(OrderTakeServices::class);
        $re = $storeOrderTask->storeProductOrderUserTakeDelivery($orderInfo);
        if (!$re) {
            throw new ApiException('核销失败');
        }

        if ($orderInfo['shipping_type'] == 2) {
            event('OrderShipping', ['product', $orderInfo, 4, '', '']);
        }

        return $orderInfo->toArray();
    }
}
