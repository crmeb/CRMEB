<?php
declare(strict_types=1);

namespace app\services\order;

use app\dao\order\OrderCenterDao;
use app\dao\user\UserBillDao;
use app\dao\user\UserDao;
use app\services\user\UserBillServices;
use crmeb\basic\BaseService;
use think\facade\Log;

/**
 * 普通订单退款权益冲正：按累计退款比例计算目标，扣除已冲正流水后只补差额。
 * 佣金冲正由分销插件自行处理，本服务只负责积分。
 */
class OrderRefundRewardServices extends BaseService
{
    /** @var OrderCenterDao */
    protected OrderCenterDao $centerDao;
    /** @var UserDao */
    protected UserDao $userDao;
    /** @var UserBillDao */
    protected UserBillDao $billDao;

    /**
     * 初始化账务依赖。
     * @return void
     */
    protected function initialize(): void
    {
        $this->centerDao = $this->app->make(OrderCenterDao::class);
        $this->userDao = $this->app->make(UserDao::class);
        $this->billDao = $this->app->make(UserBillDao::class);
    }

    /**
     * 对齐普通订单赠积分；同订单退款及收货共用订单锁。
     * @param array $order 中台订单
     * @param string $refundTotal 累计到账退款额
     * @return void
     */
    public function reconcile(array $order, string $refundTotal): void
    {
        if (($order['product_type'] ?? '') !== 'normal'
            || !in_array((string)($order['activity_type'] ?? ''), ['', 'normal'], true)
            || bccomp((string)($order['pay_price'] ?? '0'), '0', 2) <= 0
            || bccomp($refundTotal, '0', 2) <= 0) {
            return;
        }
        $this->transaction(function () use ($order, $refundTotal): void {
            $locked = $this->centerDao->getByIdForUpdate((int)$order['id']);
            if (!$locked) {
                throw new \RuntimeException('退款权益关联订单不存在');
            }
            $order = $locked->toArray();
            $recorded = $order['refund_reward_accounted'] ?? [];
            $recorded = is_array($recorded) ? $recorded : (json_decode((string)$recorded, true) ?: []);
            $accounted = $recorded;
            $paid = (string)$order['pay_price'];
            $total = bccomp($refundTotal, $paid, 2) >= 0 ? $paid : $refundTotal;
            $uid = (int)$order['uid'];
            $user = $this->userDao->getByUidForUpdate($uid);
            if (!$user) {
                throw new \RuntimeException('退款权益关联用户不存在');
            }
            $earned = $returned = $earnedOrder = $earnedProduct = '0';
            foreach ($this->billDao->getOrderIntegralRewards((int)$order['id'], $uid) as $bill) {
                if ((int)$bill['pm'] === 1) {
                    $earned = bcadd($earned, (string)$bill['number'], 0);
                    // 下单赠送族 gain/order_give_integral，购买赠送族 product_gain/pay_give_integral
                    if (in_array($bill['type'], ['gain', 'order_give_integral'], true)) {
                        $earnedOrder = bcadd($earnedOrder, (string)$bill['number'], 0);
                    } else {
                        $earnedProduct = bcadd($earnedProduct, (string)$bill['number'], 0);
                    }
                } else {
                    $returned = bcadd($returned, (string)$bill['number'], 0);
                }
            }
            $integralTarget = $this->target($earned, $total, $paid, 0);
            $processed = max((int)$returned, (int)($accounted['integral'] ?? 0));
            $due = max(0, (int)bcsub($integralTarget, (string)$processed, 0));
            // 按两族赠送占比拆分本次冲正额，分族落库（order_gain_refund / product_gain_refund）
            $dueOrder = $earned > 0 ? (int)bcdiv(bcmul((string)$due, $earnedOrder, 2), $earned, 0) : 0;
            $dueProduct = max(0, $due - $dueOrder);
            $balance = max(0, (int)$user['integral']);
            $deductOrder = min($dueOrder, $balance);
            $balance -= $deductOrder;
            $deductProduct = min($dueProduct, $balance);
            $deduct = $deductOrder + $deductProduct;
            $userBillServices = $this->app->make(UserBillServices::class);
            // 单号不写入备注：后台积分记录按 link_id 回查中台订单，展示在「关联订单」列
            if ($deductOrder > 0 && !$userBillServices->billIntegralIncome(
                'order_gain_refund', $uid, $deductOrder, (int)$order['id'], '订单退款回收下单赠送积分'
            )) {
                throw new \RuntimeException('退款赠积分冲正失败');
            }
            if ($deductProduct > 0 && !$userBillServices->billIntegralIncome(
                'product_gain_refund', $uid, $deductProduct, (int)$order['id'], '订单退款回收购买商品赠送积分'
            )) {
                throw new \RuntimeException('退款赠积分冲正失败');
            }
            if ($due > $deduct) {
                Log::warning('[RefundReward] 赠积分余额不足 order_no=' . $order['order_no'] . ' shortfall=' . ($due - $deduct));
            }
            // 短缺也记入已处理目标，保留旧政策：不在重试时追扣后续到账的用户积分。
            $accounted['integral'] = max($processed, (int)$integralTarget);

            $this->billDao->releaseOrderRewardFreeze((int)$order['id'], $uid);
            if ($recorded !== $accounted) {
                if (!$this->centerDao->update((int)$order['id'], [
                    'refund_reward_accounted' => json_encode($accounted, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                ])) {
                    throw new \RuntimeException('退款权益处理目标保存失败');
                }
            }
        });
    }

    /**
     * 累计目标采用 BCMath 截断，全额时精确回收原奖励，避免分次退款尾差。
     * @param string $reward 原奖励
     * @param string $refunded 累计退款
     * @param string $paid 原实付
     * @param int $scale 积分为0
     * @return string
     */
    protected function target(string $reward, string $refunded, string $paid, int $scale): string
    {
        return bcdiv(bcmul($reward, $refunded, $scale + 2), $paid, $scale);
    }
}
