<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace modules\product\normal\service;

// @deprecated eb_store_order_status 表已删除
use app\dao\order\OrderCenterDao;
use app\model\order\OrderCenter;
use app\services\order\OrderTakeServices;
use app\services\product\CenterProductServices;
use app\jobs\UnpaidOrderCancelJob;
use crmeb\basic\BaseService;
use think\facade\Log;
use modules\product\normal\dao\NormalOrderDao;
use modules\product\normal\handler\NormalOrderAdapter;

/**
 * 实物商品定时任务服务
 * 负责 normal 类型订单的自动取消与自动收货
 */
class CrontabServices extends BaseService
{
    /**
     * @var OrderCenterDao
     */
    protected $dao;

    /**
     * 初始化 DAO
     */
    protected function initialize(): void
    {
        $this->dao = $this->app->make(OrderCenterDao::class);
    }

    /**
     * 未支付自动取消订单（仅处理 normal 类型）
     * @return void
     */
    public function orderCancel(): void
    {
        try {
            // 系统预设取消订单时间段（小时）
            $systemValue = sys_config_more(['order_cancel_time']);
            $secs = (int)($systemValue['order_cancel_time'] ?? 0);
            if ($secs === 0) {
                return;
            }

            // 查询 normal 扩展表中待支付的订单号
            $normalOrderDao = app(NormalOrderDao::class);
            $orderNos = $normalOrderDao->getColumn(['status' => NormalOrderAdapter::EXT_STATUS_PENDING_PAYMENT], 'order_no');
            if (empty($orderNos)) {
                return;
            }

            // 关联 order_center 查询超时未支付订单
            $expireTime = time() - $secs * 3600;
            $list = $this->dao->getDb()
                ->whereIn('order_no', $orderNos)
                ->where('pay_status', 0)
                ->where('status_code', OrderCenter::STATUS_PENDING_PAYMENT)
                ->where('refund_status', 0)
                ->where('add_time', '<=', $expireTime)
                ->field('id,order_no,uid,pay_status,status_code,refund_status,add_time')
                ->select()
                ->toArray();

            if (empty($list)) {
                return;
            }

            foreach ($list as $order) {
                try {
                    // 与延迟取消任务共用查单/关单和订单锁，不能绕过正在创建或已支付的合同。
                    if (!app(UnpaidOrderCancelJob::class)->doJob((string)$order['order_no'], $secs * 3600)) {
                        throw new \RuntimeException('取消尚未完成，等待下批次重试');
                    }
                } catch (\Throwable $e) {
                    Log::error('普通商品自动取消订单失败,订单号:' . $order['order_no'] . ',原因:' . $e->getMessage());
                }
            }
        } catch (\Throwable $e) {
            Log::error('普通商品自动取消订单异常:' . $e->getMessage());
        }
    }

    /**
     * 自动确认收货（仅处理 normal 类型）
     * @return void
     */
    public function takeDelivery(): void
    {
        try {
            // 系统预设自动收货天数，0 为关闭
            $systemDeliveryTime = (int)sys_config('system_delivery_time', 0);
            if ($systemDeliveryTime === 0) {
                return;
            }

            // 查询 normal 扩展表中待收货的订单号
            $normalOrderDao = app(NormalOrderDao::class);
            $orderNos = $normalOrderDao->getColumn(['status' => NormalOrderAdapter::EXT_STATUS_PENDING_RECEIVE], 'order_no');
            if (empty($orderNos)) {
                return;
            }

            // 计算超时时间戳
            $expireTime = time() - $systemDeliveryTime * 86400;

            // @deprecated eb_store_order_status 表已删除，改为直接通过 order_center 表状态筛选
            $orderList = $this->dao->getDb()
                ->whereIn('order_no', $orderNos)
                ->whereIn('pay_status', [OrderCenter::PAY_PAID, OrderCenter::PAY_PARTIAL_REFUND])
                ->where('status_code', OrderCenter::STATUS_PROCESSING)
                ->where('refund_status', '<>', OrderCenter::REFUND_APPLYING)
                ->where('delivery_time', '>', 0)
                ->where('delivery_time', '<=', $expireTime)
                ->field('id,order_no,uid')
                ->select()
                ->toArray();

            if (empty($orderList)) {
                return;
            }

            /** @var OrderTakeServices $takeServices */
            $takeServices = app()->make(OrderTakeServices::class);
            // @deprecated eb_store_order_status 表已删除，状态记录已移除

            foreach ($orderList as $order) {
                try {
                    // 复用手动收货的锁、奖励幂等与产品状态分流，事件只在提交后触发一次。
                    $takeServices->takeOrder((string)$order['order_no'], (int)$order['uid']);
                } catch (\Throwable $e) {
                    Log::error('普通商品自动收货失败,订单号:' . $order['order_no'] . ',原因:' . $e->getMessage());
                }
            }
        } catch (\Throwable $e) {
            Log::error('普通商品自动收货异常:' . $e->getMessage());
        }
    }

    /**
     * 预售商品到期自动下架（预售为普通商品能力）
     * @return void
     */
    public function advanceOff(): void
    {
        try {
            app()->make(CenterProductServices::class)->downAdvance();
        } catch (\Throwable $e) {
            Log::error('普通商品预售到期自动下架异常:' . $e->getMessage());
        }
    }
}
