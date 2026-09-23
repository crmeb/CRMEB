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
use app\dao\product\CenterProductDao;
use app\dao\user\UserBrokerageDao;
use app\services\activity\coupon\StoreCouponUserServices;
use app\services\pay\PayServices;
use app\services\statistic\CapitalFlowServices;
use app\services\user\UserBillServices;
use app\services\user\UserBrokerageServices;
use app\services\user\UserMoneyServices;
use app\services\user\UserServices;
use crmeb\basic\BaseService;
use crmeb\trade\product\ProductTypeRegistry;
use think\facade\Log;

/**
 * 订单退款执行引擎（D 系列批次 D2c 新增）
 *
 * 统一承载退款资金回滚能力（承接自旧退款服务，旧实现已随批次 C 删除）。
 * 输入为中台订单（eb_order_center）数据，按五维执行回滚：
 * - 佣金：按分销插件佣金流水 link_id=中台订单 id 反查回退
 * - 积分：收回赠送积分（幂等）+ 返还下单使用积分
 * - 库存：从中台 extra_info.items 读取快照，经 ProductTypeHandler / ActivityDataProxy 回退
 * - 优惠券：从中台订单 extra_info 读取 coupon_id 并恢复用户优惠券
 * - 支付退款：balance 退余额 / yue 余额退款 / weixin、alipay、allinpay 走 PayServices::refund
 *
 * 引擎只负责资金/资源回滚与流水记录，不负责订单状态流转（状态机由调用方维护）
 */
class RefundExecutorServices extends BaseService
{
    /**
     * 佣金流水类型集合（与分佣写入侧一致，用于按 link_id 反查回退）
     * @var array
     */
    protected const BROKERAGE_REFUND_TYPES = [
        'self_brokerage', 'one_brokerage', 'two_brokerage',
        'staff_brokerage', 'agent_brokerage', 'division_brokerage', 'pink_master_brokerage',
    ];

    /**
     * 佣金流水数据访问
     * @var UserBrokerageDao
     */
    protected UserBrokerageDao $brokerageDao;

    /**
     * 构造方法
     * RefundExecutorServices constructor.
     * @param UserBrokerageDao $brokerageDao
     */
    public function __construct(UserBrokerageDao $brokerageDao)
    {
        $this->brokerageDao = $brokerageDao;
    }

    /**
     * 执行订单资金回滚（引擎主入口）
     * 依次执行：佣金回退 → 积分回退 → 库存回退 → 优惠券降级 → 支付退款 → 资本流水 → 开票标记
     * @param array $order 中台订单数据
     * @param float $refundPrice 现金退款金额（纯积分退款传 0）
     * @param array $context 上下文：refund_type(original/balance)、refund_trade_no、refund_integral、refund_id
     * @return array 退款结果：refund_price / refund_trade_no / pay_type
     * @throws \RuntimeException 订单未支付或回滚失败
     */
    public function execute(array $order, float $refundPrice, array $context = []): array
    {
        // 现金退款前提是订单已支付
        if ($refundPrice > 0 && (int)($order['pay_status'] ?? 0) !== 1) {
            throw new \RuntimeException('订单未支付，无法退款');
        }

        // 1. 本地资源回滚（事务内：佣金/积分/库存/优惠券均为本地库操作，支付退款为远程通道不放事务）
        $this->transaction(function () use ($order, $context) {
            $this->rollbackBrokerage($order);
            $this->rollbackIntegral($order, (int)($context['refund_integral'] ?? 0));
            $this->rollbackStock($order);
            $this->rollbackCoupon($order);
        });

        // 2. 支付退款（事务外：余额退款为本地操作，其余走远程支付通道）
        $refundTradeNo = '';
        if ($refundPrice > 0 && !empty($order['pay_type'])) {
            $refundTradeNo = $this->refundByPayType($order, $refundPrice, $context);
        }

        // 3. 资本流水 + 开票标记
        $this->writeFlow($order, $refundPrice);
        $this->markInvoiceRefunded($order);

        return [
            'refund_price'    => $refundPrice,
            'refund_trade_no' => $refundTradeNo,
            'pay_type'        => $order['pay_type'] ?? '',
        ];
    }

    /**
     * 积分回退：收回订单赠送积分（幂等）+ 返还下单使用积分
     * 由旧退款链路 regressionIntegral 迁移，字段映射为中台订单
     * @param array $order 中台订单数据
     * @param int $refundIntegral 需返还积分（售后单 refund_integral，>0 时按售后单精确返还，否则按整单使用积分）
     * @return void
     */
    public function rollbackIntegral(array $order, int $refundIntegral = 0): void
    {
        $orderId = (int)($order['id'] ?? 0);
        $uid = (int)($order['uid'] ?? 0);
        // 已退款/已删除订单不再回滚（防重复退款路径误触发）
        if (($order['status_code'] ?? '') === 'refunded' || (int)($order['is_del'] ?? 0) === 1) {
            return;
        }
        if ($orderId <= 0 || $uid <= 0) {
            return;
        }

        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $userInfo = $userServices->get($uid, ['integral']);
        if (!$userInfo) {
            return;
        }
        $currentIntegral = (int)($userInfo['integral'] ?? 0);

        /** @var UserBillServices $userBillServices */
        $userBillServices = app()->make(UserBillServices::class);

        // 操作1：收回订单赠送积分（下单赠送 gain + 商品赠送 product_gain，integral_refund 已回退过则幂等跳过）
        $orderGain = (int)$userBillServices->sum([
            'category' => 'integral',
            'type'     => 'gain',
            'link_id'  => $orderId,
            'uid'      => $uid,
        ], 'number');
        $productGain = (int)$userBillServices->sum([
            'category' => 'integral',
            'type'     => 'product_gain',
            'link_id'  => $orderId,
            'uid'      => $uid,
        ], 'number');
        $giveIntegral = $orderGain + $productGain;
        if ($giveIntegral > 0) {
            // 幂等标记查实际落库 type：模板 'integral_refund' 落库 type 为 order_deduction
            // （type=integral_refund 是模板 'order_integral_refund' 返还使用积分的流水，语义不同）
            $count = (int)$userBillServices->count([
                'category' => 'integral',
                'type'     => 'order_deduction',
                'link_id'  => $orderId,
            ]);
            if (!$count) {
                // 当前积分不足时扣减全部，差额不追回
                $deductAmount = min($currentIntegral, $giveIntegral);
                if ($deductAmount > 0) {
                    $userBillServices->billIntegralIncome('integral_refund', $uid, $deductAmount, $orderId);
                    $currentIntegral = bcsub((string)$currentIntegral, (string)$deductAmount, 2);
                }
                // 清除积分冻结
                $userBillServices->update(['link_id' => $orderId], ['frozen_time' => 0]);
            }
        }

        // 操作2：返还下单使用的积分（售后单指定 refund_integral 时按精确值，否则按整单使用积分）
        $useIntegral = $refundIntegral > 0 ? $refundIntegral : (int)($order['use_integral_num'] ?? 0);
        // 兜底：调用方未加载 use_integral_num 字段时按中台订单 id 补查
        if ($useIntegral <= 0 && $refundIntegral <= 0 && $orderId > 0) {
            /** @var OrderCenterDao $centerDao */
            $centerDao = app()->make(OrderCenterDao::class);
            $useIntegral = (int)$centerDao->value(['id' => $orderId], 'use_integral_num');
        }
        if ($useIntegral > 0) {
            $userBillServices->billIntegralIncome('pay_product_integral_back', $uid, $useIntegral, $orderId);
        }
    }

    /**
     * 库存回退：从中台 extra_info.items 读取商品快照，经 ProductTypeHandler / ActivityDataProxy 回退
     * 由旧退款链路 regressionStock 迁移（原实现已中台化）
     * @param array $order 中台订单数据
     * @return bool
     */
    public function rollbackStock(array $order): bool
    {
        if (($order['status_code'] ?? '') === 'refunded' || (int)($order['is_del'] ?? 0) === 1) {
            return true;
        }

        $res = true;
        $orderId = (int)($order['id'] ?? 0);
        // 从订单中台 extra_info.items 读取商品快照（旧 cart_info 表已废弃）
        $extraInfo = $order['extra_info'] ?? null;
        if (is_string($extraInfo)) {
            $extraInfo = json_decode($extraInfo, true) ?: [];
        }
        if (!is_array($extraInfo)) {
            $extraInfo = [];
        }
        // 兜底：调用方未加载 extra_info 时按中台订单 id 补查
        if (empty($extraInfo['items']) && $orderId > 0) {
            /** @var OrderCenterDao $centerDao */
            $centerDao = app()->make(OrderCenterDao::class);
            $centerOrder = $centerDao->get($orderId, ['extra_info']);
            if ($centerOrder) {
                $extraInfo = $centerOrder['extra_info'] ?? [];
                if (is_string($extraInfo)) {
                    $extraInfo = json_decode($extraInfo, true) ?: [];
                }
                if (!is_array($extraInfo)) {
                    $extraInfo = [];
                }
            }
        }
        $items = $extraInfo['items'] ?? [];

        // 活动订单的活动 ID 从中台 extra_info.activity_info 提取（中台无 combination_id 等实体列）
        $activityInfo = $extraInfo['activity_info'] ?? [];
        if (!is_array($activityInfo)) {
            $activityInfo = [];
        }
        $combinationId = (int)($activityInfo['combination_id'] ?? 0);
        $seckillId = (int)($activityInfo['seckill_id'] ?? 0);
        $bargainId = (int)($activityInfo['bargain_id'] ?? 0);

        // 通过 ProductTypeHandler 回退库存（活动商品保留事件代理）
        $productType = $order['product_type'] ?? '';
        $handler = null;
        if ($productType) {
            try {
                /** @var ProductTypeRegistry $registry */
                $registry = app()->make(ProductTypeRegistry::class);
                $handler = $registry->get($productType);
            } catch (\Throwable $e) {
                $handler = null;
            }
        }
        // 仅已支付订单回退销量：未支付取消的订单从未累加过销量，不能跟着库存一起扣
        $paidOrder = (int)($order['pay_status'] ?? 0) === 1;
        foreach ($items as $item) {
            $cartNum = (int)($item['cart_num'] ?? 0);
            if ($cartNum <= 0) {
                continue;
            }
            $unique = (string)($item['attr_info']['unique'] ?? '');
            $productId = (int)($item['product_id'] ?? 0);
            if ($combinationId) {
                // 通过 ActivityDataProxy 事件代理回退拼团库存（避免直接引用 modules 层）
                $_results = event('ActivityDataProxy', ['mode' => 'combination', 'action' => 'incCombinationStock', 'args' => [$cartNum, $combinationId, $unique]]);
                $res = $res && proxy_event_result($_results, false);
            } elseif ($seckillId) {
                // 通过 ActivityDataProxy 事件代理回退秒杀库存
                $_results = event('ActivityDataProxy', ['mode' => 'seckill', 'action' => 'incSeckillStock', 'args' => [$cartNum, $seckillId, $unique]]);
                $res = $res && proxy_event_result($_results, false);
            } elseif ($bargainId) {
                // 通过 ActivityDataProxy 事件代理回退砍价库存
                $_results = event('ActivityDataProxy', ['mode' => 'bargain', 'action' => 'incBargainStock', 'args' => [$cartNum, $bargainId, $unique]]);
                $res = $res && proxy_event_result($_results, false);
            } else {
                // 普通商品通过 ProductTypeHandler 回退库存
                if ($handler && $productId > 0) {
                    try {
                        $res = $res && $handler->incStock($productId, $unique, $cartNum);
                    } catch (\Throwable $e) {
                        $res = false;
                    }
                    // 回退支付时累加的冗余销量（仅已支付订单），失败不影响退款主流程
                    if ($paidOrder) {
                        try {
                            app()->make(CenterProductDao::class)->decSales($productId, $cartNum);
                        } catch (\Throwable $e) {
                        }
                    }
                }
            }
        }
        return $res;
    }

    /**
     * 佣金回退：按分销插件佣金流水 link_id=中台订单 id 反查并回退
     * @param array $order 中台订单数据
     * @return void
     */
    protected function rollbackBrokerage(array $order): void
    {
        $orderId = (int)($order['id'] ?? 0);
        if ($orderId <= 0) {
            return;
        }
        $where = [
            'link_id' => $orderId,
            'type'    => self::BROKERAGE_REFUND_TYPES,
            'pm'      => 1,
        ];
        $list = $this->brokerageDao->getUserBrokerageList($where);
        if (empty($list)) {
            return;
        }

        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        /** @var UserBrokerageServices $brokerageServices */
        $brokerageServices = app()->make(UserBrokerageServices::class);
        foreach ($list as $item) {
            $uid = (int)($item['uid'] ?? 0);
            $number = (float)($item['number'] ?? 0);
            if ($uid <= 0 || $number <= 0) {
                continue;
            }
            // 退佣时余额不足则只扣到 0，差额不追回（与旧 orderRefundBrokerageBack 行为一致）
            $legacyBalance = (float)$userServices->value(['uid' => $uid], 'brokerage_price');
            $profile = event('DistributionServiceProxy', [
                'service' => 'DistributionUserServices',
                'action' => 'profile',
                'args' => [$uid, ['brokerage_price' => $legacyBalance]],
            ]);
            $brokeragePrice = (float)($profile[0]['brokerage_price'] ?? $legacyBalance);
            $backNumber = min($number, $brokeragePrice);
            if ($backNumber <= 0) {
                continue;
            }
            $brokerageServices->brokerageIncome('brokerage_refund', $uid, $backNumber, $orderId);
        }
        // 解除分佣冻结
        $this->brokerageDao->update($where, ['frozen_time' => 0]);
    }

    /**
     * 优惠券回退：恢复订单创建时消费的用户优惠券
     * @param array $order 中台订单数据
     * @return void
     */
    public function rollbackCoupon(array $order): void
    {
        $couponId = (int)($order['coupon_id'] ?? 0);
        if (!$couponId) {
            $extraInfo = $order['extra_info'] ?? [];
            if (is_string($extraInfo)) {
                $extraInfo = json_decode($extraInfo, true) ?: [];
            } elseif (is_object($extraInfo) && method_exists($extraInfo, 'toArray')) {
                $extraInfo = $extraInfo->toArray();
            }
            if (is_array($extraInfo)) {
                $couponId = (int)($extraInfo['coupon_id'] ?? 0);
            }
        }

        if ($couponId > 0) {
            /** @var StoreCouponUserServices $couponServices */
            $couponServices = app()->make(StoreCouponUserServices::class);
            if (!$couponServices->recoverCoupon($couponId)) {
                throw new \RuntimeException('优惠券回退失败');
            }
            return;
        }

        if ((float)($order['coupon_price'] ?? 0) > 0) {
            Log::warning('[RefundExecutor] 优惠券回退缺少 coupon_id，订单号=' . ($order['order_no'] ?? ''));
        }
    }

    /**
     * 支付退款：balance 退余额 / yue 余额退款 / weixin、alipay、allinpay 走统一退款通道
     * @param array $order 中台订单数据
     * @param float $refundPrice 退款金额
     * @param array $context 上下文：refund_type(original/balance)、refund_trade_no、refund_id
     * @return string 退款单号（支付通道/余额流水单号），无则空串
     */
    public function refundByPayType(array $order, float $refundPrice, array $context = []): string
    {
        $refundTradeNo = '';
        $payType = (string)($order['pay_type'] ?? '');
        $uid = (int)($order['uid'] ?? 0);
        $orderId = (int)($order['id'] ?? 0);
        $orderNo = (string)($order['order_no'] ?? '');

        // 退余额模式：不区分原支付方式，直接退回用户余额
        if (($context['refund_type'] ?? 'original') === 'balance') {
            /** @var UserMoneyServices $moneyServices */
            $moneyServices = app()->make(UserMoneyServices::class);
            $moneyServices->moneyIncome('pay_product_refund', $uid, $refundPrice, $orderId);
            return $refundTradeNo;
        }

        // 生成退款单号（无则基于订单号随机生成）
        $refundId = $context['refund_id'] ?? ($orderNo . rand(100, 999));
        $refundData = [
            'refund_price' => $refundPrice,
            'pay_price'    => $order['pay_price'] ?? $refundPrice,
            'refund_id'    => $refundId,
        ];
        if (!empty($context['refund_trade_no'])) {
            $refundData['refund_trade_no'] = $context['refund_trade_no'];
        }

        switch ($payType) {
            case PayServices::YUE_PAY:
                // 余额退款
                /** @var UserMoneyServices $moneyServices */
                $moneyServices = app()->make(UserMoneyServices::class);
                $moneyServices->moneyIncome('pay_product_refund', $uid, $refundPrice, $orderId);
                $refundTradeNo = $refundId;
                break;
            case PayServices::WEIXIN_PAY:
            case PayServices::ALIAPY_PAY:
            case PayServices::ALLIN_PAY:
                if ($payType === PayServices::ALIAPY_PAY) {
                    mt_srand();
                    $refundId = $refundId ?? ($orderNo . rand(100, 999));
                    $refundData['refund_id'] = $refundId;
                }
                /** @var PayServices $payServices */
                $payServices = app()->make(PayServices::class);
                $payServices->refund($payType, $orderNo, $refundData, $order);
                $refundTradeNo = $refundId;
                break;
            default:
                // 未知支付方式（offline/free 等）：无真实资金通道，仅记录日志
                Log::warning('[RefundExecutor] 未知支付方式跳过支付退款：pay_type=' . $payType . '，订单号=' . $orderNo);
        }
        return $refundTradeNo;
    }

    /**
     * 写资本流水（refund 类型）
     * @param array $order 中台订单数据
     * @param float $refundPrice 退款金额
     * @return void
     */
    protected function writeFlow(array $order, float $refundPrice): void
    {
        if ($refundPrice <= 0) {
            return;
        }
        $payType = (string)($order['pay_type'] ?? '');
        // 是否真实资金渠道由 PayServices 动态判定（第三方渠道来自驱动注册中心，yue/offline 为系统常量渠道）
        /** @var PayServices $payServices */
        $payServices = app()->make(PayServices::class);
        if (!$payServices->isRealPaymentChannel($payType)) {
            return;
        }
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $userInfo = $userServices->get((int)($order['uid'] ?? 0));
        /** @var CapitalFlowServices $flowServices */
        $flowServices = app()->make(CapitalFlowServices::class);
        $order['nickname'] = $userInfo['nickname'] ?? '';
        $order['phone'] = $userInfo['phone'] ?? '';
        $order['refund_price'] = $refundPrice;
        // 中台订单无 order_id 字段，补 order_no 作为交易订单号写入流水
        $order['order_id'] = $order['order_no'] ?? '';
        $flowServices->setFlow($order, 'refund');
    }

    /**
     * 开票记录标记已退款
     * @param array $order 中台订单数据
     * @return void
     */
    protected function markInvoiceRefunded(array $order): void
    {
        $orderId = (int)($order['id'] ?? 0);
        if ($orderId <= 0) {
            return;
        }
        /** @var OrderInvoiceServices $invoiceServices */
        $invoiceServices = app()->make(OrderInvoiceServices::class);
        $invoiceServices->update(['order_id' => $orderId], ['is_refund' => 1]);
    }
}
