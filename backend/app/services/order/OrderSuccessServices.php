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

namespace app\services\order;


use app\dao\order\OrderCenterDao;
use app\services\pay\PayRecordServices;
use app\services\pay\PayServices;
use app\services\user\UserMoneyServices;
use crmeb\basic\BaseService;
use crmeb\exceptions\ApiException;
use crmeb\trade\mode\TradeModeRegistry;
use crmeb\trade\product\ProductTypeRegistry;
use think\facade\Log;

/**
 * 订单支付成功处理
 * Class OrderSuccessServices
 * @package app\services\order
 * @method getOne(array $where, ?string $field = '*', array $with = []) 获取去一条数据
 */
class OrderSuccessServices extends BaseService
{
    /**
     * @var OrderCenterDao
     */
    protected $dao;

    /**
     * OrderSuccessServices constructor.
     * @param OrderCenterDao $dao
     */
    public function __construct(OrderCenterDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 0元支付
     * @param array $orderInfo
     * @param int $uid
     * @return bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function zeroYuanPayment(array $orderInfo, int $uid, string $payType = PayServices::YUE_PAY)
    {
        if ($orderInfo['paid']) {
            throw new ApiException('该订单已支付');
        }
        return $this->paySuccess($orderInfo, $payType);//余额支付成功
    }

    /**
     * 商品支付成功。一次事务内改已支付并处理必须同成败的活动；提交后再履约和广播。
     * @param array $orderInfo 订单信息，至少含订单号
     * @param string $paytype 支付渠道
     * @param array $other 流水与通知字段
     * @return bool
     */
    public function paySuccess(array $orderInfo, string $paytype = PayServices::WEIXIN_PAY, array $other = []): bool
    {
        $orderNo = (string)($orderInfo['order_no'] ?? $orderInfo['order_id'] ?? '');
        $tradeNo = (string)($other['trade_no'] ?? '');
        $paidOrder = null;

        $this->transaction(function () use ($orderInfo, $paytype, $other, $orderNo, $tradeNo, &$paidOrder) {
            $row = $this->dao->getDb()->where('order_no', $orderNo)->lock(true)->find();
            if (!$row) {
                throw new ApiException('订单不存在');
            }
            $order = array_replace($orderInfo, $row->toArray());
            $order['order_id'] = $orderNo;
            $extra = $this->parseExtra($order['extra_info'] ?? []);
            $order['real_name'] = $extra['contact']['name'] ?? ($order['real_name'] ?? '');
            $order['sku_unique'] = (string)($order['sku_unique'] ?? ($extra['sku_info']['unique'] ?? ''));
            $order['pink_id'] = (int)($order['pink_id'] ?? ($extra['pink_id'] ?? 0));

            if ((int)$order['pay_status'] !== 0) {
                return;
            }
            if (in_array($order['status_code'], ['canceled', 'deleted', 'refunded'], true)) {
                if ($tradeNo !== '') {
                    Log::error('[Pay] 已取消订单收到付款，需人工处理 order_no=' . $orderNo);
                    app()->make(PayRecordServices::class)->compensateCanceled($orderNo, $tradeNo, $other);
                    return;
                }
                throw new ApiException('订单已取消');
            }

            $records = app()->make(PayRecordServices::class);
            $paytype = $records->validate($order, 'product', $paytype, $other);

            if ($paytype === PayServices::YUE_PAY && bccomp((string)$order['pay_price'], '0', 2) > 0) {
                $records->assertChannel($orderNo, PayServices::YUE_PAY);
                $amount = (string)$order['pay_price'];
                if (bccomp($amount, (string)($orderInfo['pay_price'] ?? $amount), 2) !== 0) {
                    throw new ApiException('订单金额已变化');
                }
                app()->make(UserMoneyServices::class)->moneyIncome('pay_product', (int)$order['uid'], $amount, (int)$order['id']);
            }

            $activityType = (string)($order['activity_type'] ?? 'normal');
            $activityId = (int)($order['activity_id'] ?? 0);
            if ($activityType !== '' && $activityType !== 'normal' && $activityId > 0) {
                app()->make(TradeModeRegistry::class)->get($activityType)->onOrderPay($orderNo, $activityId, $order);
                $fresh = $this->dao->getDb()->where('order_no', $orderNo)->find();
                if ($fresh) {
                    $order = array_replace($order, $fresh->toArray());
                    $order['order_id'] = $orderNo;
                }
            }

            $payTime = time();
            $order['pay_time'] = $payTime;
            $order['pay_type'] = $paytype;
            $statusData = ['pay_status' => 1, 'pay_type' => $paytype, 'pay_time' => $payTime];
            if ($tradeNo !== '') {
                $extra = $this->parseExtra($order['extra_info'] ?? []);
                $extra['trade_no'] = $tradeNo;
                $extra['pay_uid'] = (int)($order['uid'] ?? 0);
                if (isset($extra['payment']) && is_array($extra['payment'])) {
                    $extra['payment']['transaction_id'] = $tradeNo;
                }
                $order['extra_info'] = $extra;
                $statusData['extra_info'] = json_encode($extra, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            }
            if (!app()->make(OrderCenterServices::class)->syncStatus($orderNo, 'processing', $statusData)) {
                throw new \RuntimeException('订单支付状态更新失败');
            }
            $records->markCompleted($orderNo, $tradeNo);
            $order['pay_status'] = 1;
            $order['send_name'] = $order['real_name'] ?? '';
            $paidOrder = $order;
        });

        if (!$paidOrder) {
            return true;
        }

        $this->fulfillAfterPay($orderNo, $paidOrder);
        $this->broadcastAfterPay($paidOrder);
        return true;
    }

    /**
     * 解析订单扩展字段。
     * @param mixed $extra 数组或 JSON
     * @return array
     */
    protected function parseExtra($extra): array
    {
        if (is_string($extra)) {
            $extra = json_decode($extra, true) ?: [];
        }
        return is_array($extra) ? $extra : [];
    }

    /**
     * 提交后按产品类型履约，再按交易模式做支付后处理。失败只记日志。
     * @param string $orderNo 订单号
     * @param array $order 已支付订单
     * @return void
     */
    protected function fulfillAfterPay(string $orderNo, array $order): void
    {
        try {
            $type = (string)($order['product_type'] ?? 'normal');
            if (!app()->make(ProductTypeRegistry::class)->get($type)->fulfillOrder($orderNo, $order)) {
                Log::error('[Pay] 产品履约未完成 order_no=' . $orderNo . ' product_type=' . $type);
            }
        } catch (\Throwable $e) {
            Log::error('[Pay] 产品履约失败 order_no=' . $orderNo . ' ' . $e->getMessage());
        }

        $activityType = (string)($order['activity_type'] ?? 'normal');
        $activityId = (int)($order['activity_id'] ?? 0);
        if ($activityType === '' || $activityType === 'normal' || $activityId <= 0) {
            return;
        }
        try {
            app()->make(TradeModeRegistry::class)->get($activityType)->afterOrderPay($orderNo, $activityId, $order);
        } catch (\Throwable $e) {
            Log::error('[Pay] 活动支付后处理失败 order_no=' . $orderNo . ' ' . $e->getMessage());
        }
    }

    /**
     * 支付提交后的广播。一次失败不挡住下一次，也不改回未支付。
     * @param array $order 已支付订单
     * @return void
     */
    protected function broadcastAfterPay(array $order): void
    {
        $events = [
            ['OrderPayLottery', [$order]],
            ['OrderPaySuccess', [$order]],
            ['OutPush', ['order_pay_push', ['order_id' => (int)($order['id'] ?? 0)]]],
        ];
        foreach ($events as [$name, $payload]) {
            try {
                event($name, $payload);
            } catch (\Throwable $e) {
                Log::error('[Pay] 支付后广播失败 event=' . $name . ' order_no=' . ($order['order_id'] ?? '') . ' ' . $e->getMessage());
            }
        }
    }

}
