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

use crmeb\basic\BaseService;
use app\dao\order\AfterSaleDao;
use app\dao\order\AfterSaleItemDao;
use app\dao\order\OrderCenterDao;
use app\model\order\OrderCenter;
use app\services\order\rule\AfterSaleRuleResolver;
use app\services\shipping\ExpressServices;
use crmeb\trade\order\aftersale\AfterSaleHandlerInterface;
use crmeb\trade\order\aftersale\AfterSaleRuleRegistry;
use think\facade\Db;
use think\facade\Log;

/**
 * 售后核心服务
 * 负责售后单的创建、审核、退款、退货、补发、维修等全流程管理
 *
 * 职责边界：
 * - 状态管理：负责 eb_after_sale 表的状态更新和时间戳记录
 * - 路由分发：根据产品类型获取对应的 AfterSaleHandlerInterface 实现
 * - 规则校验：通过 AfterSaleRuleResolver 做二维规则校验（产品类型 × 活动类型）
 */
class AfterSaleServices extends BaseService
{
    /**
     * @var AfterSaleDao
     */
    protected $dao;

    /**
     * @var AfterSaleRuleResolver
     */
    protected AfterSaleRuleResolver $ruleResolver;

    /**
     * @var OrderCenterServices
     */
    protected OrderCenterServices $centerServices;

    /** @var OrderCenterDao */
    protected OrderCenterDao $centerDao;

    /**
     * 初始化
     */
    protected function initialize(): void
    {
        $this->dao = $this->app->make(AfterSaleDao::class);
        $this->ruleResolver = $this->app->make(AfterSaleRuleResolver::class);
        $this->centerServices = $this->app->make(OrderCenterServices::class);
        $this->centerDao = $this->app->make(OrderCenterDao::class);
    }

    // ===== 规则校验 =====

    /**
     * 获取可用售后类型
     * v8.1.1 增强：下发商品级 refundable_num，支持按 cart_id 选择售后范围
     * - 保留 allowed/types/deadline/max_num 字段（向后兼容）
     * - 新增 items 字段：每个 cart_id 的可退数量和商品信息
     * @param string $orderNo
     * @return array
     */
    public function getAvailableTypes(string $orderNo): array
    {
        $center = $this->centerServices->getOne(['order_no' => $orderNo]);
        if (empty($center)) {
            return [];
        }
        if (is_object($center)) {
            $center = $center->toArray();
        }

        // 校验订单支付状态：未付款/已取消/已删除/已退款的订单不允许售后
        $eligibility = $this->checkOrderEligibility($center);
        if (!$eligibility['eligible']) {
            return ['allowed' => false, 'reason' => $eligibility['reason']];
        }

        // 商品级售后：不再做"整单已有进行中售后"拦截
        // 由前端根据 items[].refundable_num 判断商品可选性，由 apply() 在提交时按 cart_id 校验

        $sourceOrder = $this->getSourceOrder($center);

        $rule = $this->ruleResolver->resolve($center, $sourceOrder);
        if (!$rule['allow']) {
            return ['allowed' => false, 'reason' => $rule['reject_reason'] ?? ''];
        }

        // 加载商品级可退数量
        $items = $this->loadRefundableItems($orderNo);
        // 整单剩余可退数量合计（向后兼容 max_num 字段）
        $totalRefundable = 0;
        $hasCash = false;
        $hasIntegral = false;
        foreach ($items as $item) {
            $totalRefundable += (int)($item['refundable_num'] ?? 0);
            if ((float)($item['true_price'] ?? 0) > 0) {
                $hasCash = true;
            }
            if ((int)($item['refundable_integral'] ?? 0) > 0) {
                $hasIntegral = true;
            }
        }
        if ($totalRefundable <= 0) {
            return ['allowed' => false, 'reason' => '当前订单暂无可退商品'];
        }

        return [
            'allowed'   => true,
            'types'     => $rule['available_types'] ?? [],
            'deadline'  => $rule['deadline'] ?? 0,
            'max_num'   => $totalRefundable,
            // 用户实际支付的运费作为可退款金额的一部分下发，前端用于展示退款上限。
            'pay_postage' => number_format((float)($center['pay_postage'] ?? 0), 2, '.', ''),
            // 退款维度：现金与积分是两个独立维度，可同时存在（积分+余额混合支付订单）
            'refund_targets' => ['has_cash' => $hasCash, 'has_integral' => $hasIntegral],
            'items'     => $items,
        ];
    }

    /**
     * 加载订单商品级可退数量
     * 查询订单的全部 order_items，聚合已申请售后单的 refund_num，计算每个 cart_id 的剩余可退数量
     * @param string $orderNo 订单号
     * @return array 返回 [{cart_id, product_id, store_name, image, sku, cart_num, refunded_num, refundable_num, true_price}, ...]
     */
    protected function loadRefundableItems(string $orderNo): array
    {
        // 获取订单明细（从中台 extra_info.items 或源订单购物车表加载）
        $center = $this->centerServices->getOne(['order_no' => $orderNo]);
        if (empty($center)) {
            return [];
        }
        if (is_object($center)) {
            $center = $center->toArray();
        }

        // 积分活动订单：提取每件退还积分参考价（用于售后退款积分计算与前端展示）
        $rawExtra = $center['extra_info'] ?? [];
        $extra = is_array($rawExtra) ? $rawExtra : (json_decode((string)$rawExtra, true) ?: []);
        $integralUnitPrice = (int)($extra['activity_info']['integral_unit_price'] ?? 0);

        $orderItems = $this->centerServices->loadOrderItems($center);

        if (empty($orderItems)) {
            return [];
        }

        // 查询已申请的有效售后单，按 cart_id 聚合已申请数量
        $validAfterSales = $this->dao->getValidByOrderNo($orderNo);
        $details = $this->app->make(AfterSaleItemDao::class)
            ->getQuantitiesByAfterSaleIds(array_column($validAfterSales, 'id'));
        $detailsBySale = [];
        foreach ($details as $detail) {
            $detailsBySale[(int)$detail['after_sale_id']][] = $detail;
        }
        $refundedNumMap = []; // cart_id => 已申请数量合计
        foreach ($validAfterSales as $row) {
            $saleDetails = $detailsBySale[(int)($row['id'] ?? 0)] ?? [];
            if ($saleDetails) {
                foreach ($saleDetails as $detail) {
                    $cid = (int)$detail['cart_id'];
                    $refundedNumMap[$cid] = ($refundedNumMap[$cid] ?? 0) + (int)$detail['refund_num'];
                }
                continue;
            }
            // 老售后单从服务端保存的快照读取精确数量。
            $snapshot = $row['product_snapshot'] ?? [];
            if (is_string($snapshot)) {
                $snapshot = json_decode($snapshot, true) ?: [];
            }
            $snapItems = is_array($snapshot) ? ($snapshot['items'] ?? $snapshot) : [];
            $hasSnapshot = false;
            foreach ($snapItems as $snapItem) {
                $cid = (int)($snapItem['cart_id'] ?? 0);
                if ($cid > 0) {
                    $hasSnapshot = true;
                    $refundedNumMap[$cid] = ($refundedNumMap[$cid] ?? 0)
                        + (int)($snapItem['refund_num'] ?? $snapItem['cart_num'] ?? 0);
                }
            }
            if ($hasSnapshot) {
                continue;
            }
            $cartIds = $row['cart_ids'];
            if (is_string($cartIds)) {
                $cartIds = json_decode($cartIds, true) ?: [];
            }
            if (!is_array($cartIds) || empty($cartIds)) {
                // 兼容旧售后单（无 cart_ids）：按 product_snapshot.items 反推
                $snapshot = $row['product_snapshot'] ?? null;
                if (is_string($snapshot)) {
                    $snapshot = json_decode($snapshot, true) ?: [];
                }
                $snapItems = $snapshot['items'] ?? ($snapshot ?? []);
                foreach ($snapItems as $snapItem) {
                    $cid = (int)($snapItem['cart_id'] ?? 0);
                    if ($cid > 0) {
                        $refundedNumMap[$cid] = ($refundedNumMap[$cid] ?? 0) + (int)($snapItem['cart_num'] ?? 0);
                    }
                }
                continue;
            }
            // 新格式：cart_ids 数组 + refund_num 平均分摊到每个 cart_id
            // 简化处理：refund_num 是整笔售后单的总数量，若 cart_ids 只有一个元素则全归该 cart_id
            $refundNum = (int)($row['refund_num'] ?? 0);
            if (count($cartIds) === 1) {
                $cid = (int)$cartIds[0];
                $refundedNumMap[$cid] = ($refundedNumMap[$cid] ?? 0) + $refundNum;
            } else {
                // 缺少明细和快照的旧多商品售后无法可靠分配，保守占满涉及的商品，禁止重复退货。
                foreach ($cartIds as $cid) {
                    $cid = (int)$cid;
                    foreach ($orderItems as $orderItem) {
                        if ((int)($orderItem['cart_id'] ?? 0) === $cid) {
                            $refundedNumMap[$cid] = (int)($orderItem['cart_num'] ?? 0);
                        }
                    }
                }
            }
        }

        // 组装返回
        $deductionIntegral = $integralUnitPrice > 0 ? [] : $this->allocateDeductionIntegral($center, $orderItems);
        $result = [];
        foreach ($orderItems as $item) {
            $cartId = (int)($item['cart_id'] ?? 0);
            if ($cartId <= 0) {
                continue; // 缺失 cart_id 的明细无法做商品级售后
            }
            $cartNum = (int)($item['cart_num'] ?? 1);
            $refundedNum = $refundedNumMap[$cartId] ?? 0;
            $refundableNum = max(0, $cartNum - $refundedNum);
            $sku = '';
            if (!empty($item['sku'])) {
                $sku = (string)$item['sku'];
            } elseif (!empty($item['attr_info']['suk'])) {
                $sku = (string)$item['attr_info']['suk'];
            }
            $skuUnique = (string)($item['unique'] ?? $item['attr_info']['unique'] ?? '');
            $result[] = [
                // cart_id 使用字符串跨端传输，避免 JavaScript 对 BIGINT 购物车 ID 的数字精度丢失。
                'cart_id'        => (string)$cartId,
                'product_id'     => (int)($item['product_id'] ?? 0),
                'store_name'     => (string)($item['store_name'] ?? ''),
                'image'          => (string)($item['image'] ?? ''),
                'sku'            => $sku,
                'sku_unique'     => $skuUnique,
                'cart_num'       => $cartNum,
                'refunded_num'   => $refundedNum,
                'refundable_num' => $refundableNum,
                'true_price'     => (string)($item['true_price'] ?? $item['price'] ?? '0.00'),
                // 积分参考价：非积分订单为 0，仅积分活动订单有值
                'integral_unit_price' => $integralUnitPrice,
                'deduction_integral_total' => $deductionIntegral[$cartId] ?? 0,
                'refundable_integral' => $integralUnitPrice > 0
                    ? $integralUnitPrice * $refundableNum
                    : $this->deductionIntegralForQuantity($deductionIntegral[$cartId] ?? 0, $cartNum, $refundedNum, $refundableNum),
                'snapshot'       => $item,
            ];
        }

        return $result;
    }

    /**
     * 将普通订单抵扣积分按商品金额分摊，累计取整保证尾差不丢失。
     * @param array $center 中台订单
     * @param array $items 商品快照
     * @return array cart_id => 整行抵扣积分
     */
    protected function allocateDeductionIntegral(array $center, array $items): array
    {
        $integral = max(0, (int)($center['use_integral_num'] ?? 0));
        if (!$integral) {
            return [];
        }
        $weights = [];
        foreach ($items as $item) {
            $cid = (int)($item['cart_id'] ?? 0);
            $weights[$cid] = bcmul((string)($item['true_price'] ?? $item['price'] ?? '0'), (string)($item['cart_num'] ?? 1), 4);
        }
        $total = array_reduce($weights, fn ($sum, $weight) => bcadd($sum, $weight, 4), '0');
        if (bccomp($total, '0', 4) <= 0) {
            foreach ($items as $item) {
                $weights[(int)($item['cart_id'] ?? 0)] = (string)($item['cart_num'] ?? 1);
            }
            $total = (string)array_sum($weights);
        }
        $allocated = 0;
        $cumulative = '0';
        $result = [];
        foreach ($weights as $cid => $weight) {
            $cumulative = bcadd($cumulative, $weight, 4);
            $target = bccomp($total, '0', 4) > 0
                ? (int)bcdiv(bcmul((string)$integral, $cumulative, 4), $total, 0) : 0;
            $result[$cid] = $target - $allocated;
            $allocated = $target;
        }
        return $result;
    }

    /**
     * 计算选中数量应退的抵扣积分，逐次退款累计取整。
     * @param int $total 整行积分
     * @param int $quantity 整行数量
     * @param int $refunded 已占用数量
     * @param int $selected 本次数量
     * @return int
     */
    protected function deductionIntegralForQuantity(int $total, int $quantity, int $refunded, int $selected): int
    {
        if ($quantity <= 0 || $total <= 0) {
            return 0;
        }
        $before = min($quantity, max(0, $refunded));
        $after = min($quantity, $before + max(0, $selected));
        return (int)bcdiv(bcmul((string)$total, (string)$after, 0), (string)$quantity, 0)
            - (int)bcdiv(bcmul((string)$total, (string)$before, 0), (string)$quantity, 0);
    }

    /**
     * 获取订单当前可退现金上限
     * 按可退明细逐项累加，避免直接用订单实付金额减已退金额导致和实际 SKU 可退数量不一致。
     */
    public function getMaxRefundAmount(string $orderNo): string
    {
        $center = $this->centerServices->getOne(['order_no' => $orderNo]);
        if (empty($center)) {
            return '0.00';
        }
        if (is_object($center)) {
            $center = $center->toArray();
        }

        $items = $this->loadRefundableItems($orderNo);
        $maxRefundPrice = '0.00';
        foreach ($items as $item) {
            $refundNum = (int)($item['refundable_num'] ?? 0);
            if ($refundNum <= 0) {
                continue;
            }
            $maxRefundPrice = bcadd(
                $maxRefundPrice,
                bcmul((string)($item['true_price'] ?? '0.00'), (string)$refundNum, 4),
                2
            );
        }

        // 后台整单退款入口也必须把用户实付运费纳入可退金额上限。
        return bcadd($maxRefundPrice, $this->calculateRefundPostage($center, $items), 2);
    }

    /**
     * 按商品金额占比分摊订单已支付运费。
     *
     * 商品级售后允许部分商品/部分数量退款，不能每次都直接加整笔运费，否则多次售后会重复退款。
     * @param array $center 中台订单
     * @param array $allItems 订单全部商品明细，需含 cart_num/true_price
     * @param array $selectedItems 本次售后商品明细，含 refund_num；省略时使用各商品 refundable_num
     * @return string 本次售后最多可退的运费
     */
    protected function calculateRefundPostage(array $center, array $allItems, array $selectedItems = []): string
    {
        $postage = max(0, (float)($center['pay_postage'] ?? 0));
        if ($postage <= 0 || empty($allItems)) {
            return '0.00';
        }

        $itemMap = [];
        $totalProductAmount = 0.0;
        foreach ($allItems as $item) {
            $cartId = (int)($item['cart_id'] ?? 0);
            $unitPrice = (float)($item['true_price'] ?? $item['price'] ?? 0);
            $cartNum = max(0, (int)($item['cart_num'] ?? 0));
            if ($cartId > 0) {
                $itemMap[$cartId] = $item;
            }
            $totalProductAmount += $unitPrice * $cartNum;
        }

        if (empty($selectedItems)) {
            $selectedItems = $allItems;
        }

        $selectedProductAmount = 0.0;
        foreach ($selectedItems as $item) {
            $cartId = (int)($item['cart_id'] ?? 0);
            $detail = $itemMap[$cartId] ?? $item;
            $unitPrice = (float)($detail['true_price'] ?? $detail['price'] ?? 0);
            $quantity = array_key_exists('refund_num', $item)
                ? (int)$item['refund_num']
                : (int)($item['refundable_num'] ?? $item['cart_num'] ?? 0);
            $selectedProductAmount += $unitPrice * max(0, $quantity);
        }

        if ($selectedProductAmount <= 0) {
            return '0.00';
        }
        if ($totalProductAmount <= 0 || $selectedProductAmount >= $totalProductAmount) {
            return number_format($postage, 2, '.', '');
        }

        $allocated = round($postage * ($selectedProductAmount / $totalProductAmount), 2);
        return number_format(min($postage, max(0, $allocated)), 2, '.', '');
    }

    /**
     * 将售后总金额/积分固化到商品行，尾差归最后一行。
     * 这里只在申请时计算一次，后续退款重试直接读取持久化结果。
     */
    protected function buildAfterSaleItems(
        int $afterSaleId,
        array $items,
        array $itemDetailMap,
        string $productType,
        string $activityType,
        string $refundPrice,
        int $refundIntegral
    ): array {
        $selected = [];
        $totalWeight = 0;
        foreach ($items as $item) {
            $cartId = (int)($item['cart_id'] ?? 0);
            $detail = $itemDetailMap[$cartId] ?? null;
            if (!$detail) {
                continue;
            }
            $num = (int)($item['refund_num'] ?? 0);
            $weight = max(0, (int)round((float)$detail['true_price'] * 100) * $num);
            $integralWeight = (int)($detail['selected_integral'] ?? 0);
            $selected[] = compact('cartId', 'detail', 'num', 'weight', 'integralWeight');
            $totalWeight += $weight;
        }

        $amountCents = max(0, (int)round((float)$refundPrice * 100));
        $remainingAmount = $amountCents;
        $remainingIntegral = max(0, $refundIntegral);
        $totalIntegralWeight = array_sum(array_column($selected, 'integralWeight'));
        $cumulativeIntegralWeight = 0;
        $allocatedIntegral = 0;
        $rows = [];
        $lastIndex = count($selected) - 1;
        foreach ($selected as $index => $entry) {
            if ($index === $lastIndex) {
                $lineAmount = $remainingAmount;
                $lineIntegral = $remainingIntegral;
            } else {
                $ratio = $totalWeight > 0 ? $entry['weight'] / $totalWeight : 1 / max(1, count($selected));
                $lineAmount = min($remainingAmount, (int)floor($amountCents * $ratio));
                $lineIntegral = min($remainingIntegral, (int)floor($refundIntegral * $ratio));
            }
            if ($totalIntegralWeight > 0) {
                // 积分累计分摊，零积分商品不能承接其他行的整数尾差。
                $cumulativeIntegralWeight += $entry['integralWeight'];
                $integralTarget = (int)bcdiv(bcmul((string)$refundIntegral, (string)$cumulativeIntegralWeight, 0), (string)$totalIntegralWeight, 0);
                $lineIntegral = $integralTarget - $allocatedIntegral;
                $allocatedIntegral = $integralTarget;
            }
            $remainingAmount -= $lineAmount;
            $remainingIntegral -= $lineIntegral;

            $detail = $entry['detail'];
            $snapshot = is_array($detail['snapshot'] ?? null) ? $detail['snapshot'] : $detail;
            $snapshot['refund_num'] = $entry['num'];
            $rows[] = [
                'after_sale_id' => $afterSaleId,
                'order_item_key' => 'cart:' . $entry['cartId'],
                'cart_id' => $entry['cartId'],
                'product_id' => (int)($detail['product_id'] ?? 0),
                'sku_unique' => (string)($detail['sku_unique'] ?? ''),
                'product_type' => $productType,
                'activity_type' => $activityType,
                'refund_num' => $entry['num'],
                'refund_amount' => number_format($lineAmount / 100, 2, '.', ''),
                'refund_integral' => $lineIntegral,
                'snapshot' => $snapshot,
                'add_time' => time(),
            ];
        }
        return $rows;
    }

    /**
     * 获取源表订单数据
     * 通过产品类型 Handler 的 getOrderExtend 读取扩展表订单，失败时返回空数组
     * @param array $center 中台订单数据
     * @return array
     */
    protected function getSourceOrder(array $center): array
    {
        $productType = $center['product_type'] ?? '';
        if (empty($productType)) {
            return [];
        }

        try {
            $registry = app(\crmeb\trade\product\ProductTypeRegistry::class);
            $handler = $registry->get($productType);
            if ($handler) {
                $extend = $handler->getOrderExtend($center['order_no'] ?? '');
                return is_array($extend) ? $extend : [];
            }
        } catch (\Throwable $e) {
            // 扩展表读取失败时不约束
        }

        return [];
    }

    /**
     * 校验订单是否具备售后资格
     * 未付款/已取消/已删除的订单不允许申请售后（未付款无款可退）
     * @param array $center 中台订单数据
     * @return array ['eligible' => bool, 'reason' => string]
     */
    protected function checkOrderEligibility(array $center): array
    {
        $statusCode = $center['status_code'] ?? '';
        $payStatus = (int)($center['pay_status'] ?? 0);

        // 全额退款订单不可再次售后；部分退款订单仍允许对剩余商品继续申请。
        if ($statusCode === OrderCenter::STATUS_REFUNDED && $payStatus !== OrderCenter::PAY_PARTIAL_REFUND) {
            return ['eligible' => false, 'reason' => '订单已退款，无法申请售后'];
        }
        // 未支付订单不可售后（没付款无法退款）
        if (!in_array($payStatus, [OrderCenter::PAY_PAID, OrderCenter::PAY_PARTIAL_REFUND], true)) {
            return ['eligible' => false, 'reason' => '订单未支付，无法申请售后'];
        }
        // 已取消/已删除订单不可售后
        if (in_array($statusCode, [OrderCenter::STATUS_CANCELED, OrderCenter::STATUS_DELETED], true)) {
            return ['eligible' => false, 'reason' => '订单状态异常，无法申请售后'];
        }

        return ['eligible' => true, 'reason' => ''];
    }

    // ===== 表单与详情（数据驱动前端） =====

    /**
     * 售后类型文案映射
     * 前端可优先用 $t() 多语言渲染，此文案作为数据层语义补充
     */
    protected const TYPE_TEXT_MAP = [
        'refund_only'   => '仅退款',
        'return_refund' => '退货退款',
        'exchange'      => '换货',
        'resend'        => '补发',
        'repair'        => '维修',
    ];

    /**
     * 售后状态文案映射
     */
    protected const STATUS_TEXT_MAP = [
        'pending_audit'  => '待审核',
        'audit_passed'   => '审核通过',
        'audit_rejected' => '审核拒绝',
        'canceled'       => '已取消',
        'refunding'      => '退款中',
        'refunded'       => '已退款',
        'pending_return' => '待退货',
        'receiving'      => '待收货',
        'repairing'      => '维修中',
        'repair_complete'=> '维修完成',
        'resending'      => '补发中',
        'completed'      => '已完成',
    ];

    /**
     * 售后状态分组映射
     * 用于用户端列表 tab 筛选：processing=处理中，completed=已完成，canceled=已取消
     * repair_complete 归入处理中（维修完成待用户确认收货，流程未终结）
     */
    protected const STATUS_GROUP_MAP = [
        'processing' => ['pending_audit', 'audit_passed', 'refunding', 'pending_return', 'receiving', 'repairing', 'resending', 'repair_complete'],
        'completed'  => ['refunded', 'completed'],
        'canceled'   => ['canceled', 'audit_rejected'],
    ];

    /**
     * 获取售后申请表单配置
     * 按产品类型 Handler 返回动态字段，前端据此渲染申请表单
     * @param string $orderNo 订单号
     * @param string $type 售后类型 refund_only/return_refund/exchange/resend/repair
     * @return array ['fields'=>[...], 'type_text'=>string]
     */
    public function getFormConfig(string $orderNo, string $type): array
    {
        $center = $this->centerServices->getOne(['order_no' => $orderNo]);
        $productType = 'normal';
        if (!empty($center)) {
            if (is_object($center)) {
                $center = $center->toArray();
            }
            $productType = $center['product_type'] ?? 'normal';
        }

        // 取产品类型售后处理器，调 getFormConfig 获取动态字段
        $handler = AfterSaleRuleRegistry::getProvider('product_type', $productType);
        $config = $handler ? $handler->getFormConfig($type) : ['fields' => []];

        // 退款标的物：现金与积分是两个独立维度，可同时存在（如积分+余额混合支付订单）
        // 根据订单实际含有的退款维度追加/移除字段，而非用 refund_target 二选一替换
        $targets = $this->calcRefundTargets($orderNo);
        $fields = $config['fields'] ?? [];
        // 含积分：追加"退还积分"字段（与退款金额字段并存，非替换）
        if ($targets['has_integral']) {
            $fields = $this->appendRefundIntegralField($fields);
        }
        // 纯积分订单（无现金可退）：移除"申请退款金额"字段
        if (!$targets['has_cash'] && $targets['has_integral']) {
            $fields = $this->removeRefundPriceField($fields);
        }
        $config['fields'] = $fields;

        $config['type_text'] = self::TYPE_TEXT_MAP[$type] ?? $type;
        // 下发退款维度，前端据此渲染提示条（不再依赖 refund_target 二选一）
        $config['refund_targets'] = $targets;
        return $config;
    }

    /**
     * 计算订单退款标的物维度
     * 现金与积分是两个独立维度：现金维度看可退商品的 true_price，积分维度看 integral_unit_price。
     * 两者可同时为 true（积分+余额混合支付订单），避免用单一 refund_target 二选一导致漏退。
     * @param string $orderNo 订单号
     * @return array ['has_cash' => bool, 'has_integral' => bool]
     */
    protected function calcRefundTargets(string $orderNo): array
    {
        $items = $this->loadRefundableItems($orderNo);
        $hasCash = false;
        $hasIntegral = false;
        foreach ($items as $item) {
            // 无可退数量的商品不参与退款维度判断
            if ((int)($item['refundable_num'] ?? 0) <= 0) {
                continue;
            }
            if ((float)($item['true_price'] ?? 0) > 0) {
                $hasCash = true;
            }
            if ((int)($item['refundable_integral'] ?? 0) > 0) {
                $hasIntegral = true;
            }
        }
        return ['has_cash' => $hasCash, 'has_integral' => $hasIntegral];
    }

    /**
     * 向表单字段追加"退还积分"字段
     * 退款金额与退还积分并存（积分+余额混合支付订单），故为追加而非替换
     * @param array $fields 表单字段数组
     * @return array
     */
    protected function appendRefundIntegralField(array $fields): array
    {
        // 已存在退还积分字段则跳过，避免重复追加
        foreach ($fields as $field) {
            if (($field['key'] ?? '') === 'refund_integral') {
                return $fields;
            }
        }
        $fields[] = [
            'key'         => 'refund_integral',
            'label'       => '退还积分',
            'type'        => 'number',
            'required'    => true,
            'placeholder' => '请输入退还积分',
        ];
        return $fields;
    }

    /**
     * 从表单字段移除"申请退款金额"字段
     * 纯积分订单无现金可退，移除退款金额字段避免前端展示无用输入
     * @param array $fields 表单字段数组
     * @return array
     */
    protected function removeRefundPriceField(array $fields): array
    {
        return array_values(array_filter($fields, function ($field) {
            return ($field['key'] ?? '') !== 'refund_price';
        }));
    }

    /**
     * 获取售后详情
     * 含售后单信息、时间线、用户可操作按钮，前端详情模式据此渲染
     * @param string $afterSaleNo 售后单号
     * @return array
     */
    public function getDetail(string $afterSaleNo): array
    {
        $afterSale = $this->dao->getByNo($afterSaleNo);
        if (empty($afterSale)) {
            return [];
        }

        $type = $afterSale['type'] ?? '';
        $status = $afterSale['status'] ?? '';

        // 构造时间线（按售后类型不同节点不同，终态特殊处理）
        $timeline = $this->buildTimeline($afterSale);

        // 获取用户可操作按钮（由产品类型 Handler 提供）
        $productType = $afterSale['product_type'] ?? 'normal';
        $handler = AfterSaleRuleRegistry::getProvider('product_type', $productType);
        $actions = $handler ? $handler->getUserActions($afterSale) : [];

        return [
            'after_sale_no'       => $afterSale['after_sale_no'] ?? '',
            'order_no'            => $afterSale['order_no'] ?? '',
            // 产品类型与订单大类：供前端按业态分流跳转订单详情、展示业态标签
            'product_type'        => $productType,
            'order_category'      => $afterSale['order_category'] ?? '',
            'type'                => $type,
            'type_text'           => self::TYPE_TEXT_MAP[$type] ?? $type,
            'status'              => $status,
            'status_text'         => self::STATUS_TEXT_MAP[$status] ?? $status,
            'refund_price'        => (float)($afterSale['refund_price'] ?? 0),
            'actual_refund_price' => (float)($afterSale['actual_refund_price'] ?? 0),
            // 退款标的物与退还积分：积分订单售后展示退还积分数
            'refund_target'       => $afterSale['refund_target'] ?? 'cash',
            'refund_integral'     => (int)($afterSale['refund_integral'] ?? 0),
            'reason'              => $afterSale['reason'] ?? '',
            'remark'              => $afterSale['remark'] ?? '',
            'reject_reason'       => $afterSale['reject_reason'] ?? '',
            'evidence_images'     => $afterSale['evidence_images'] ?? [],
            'product_snapshot'    => $afterSale['product_snapshot'] ?? [],
            // v8.1.1 商品级售后：返回 cart_ids 和 refund_num，供前端展示售后范围
            'cart_ids'            => $afterSale['cart_ids'] ?? [],
            'refund_num'           => (int)($afterSale['refund_num'] ?? 0),
            'return_delivery'     => [
                'type'   => $afterSale['return_delivery_type'] ?? '',
                'no'     => $afterSale['return_delivery_no'] ?? '',
                'images' => $afterSale['return_delivery_images'] ?? [],
            ],
            'resend_delivery'     => [
                'type' => $afterSale['resend_delivery_type'] ?? '',
                'no'   => $afterSale['resend_delivery_no'] ?? '',
            ],
            'timeline'            => $timeline,
            'actions'             => $actions,
            'add_time'            => (int)($afterSale['add_time'] ?? 0),
        ];
    }

    /**
     * 构造售后时间线
     * 按售后类型返回不同节点，done 标记已完成，time 为时间戳
     * 终态（审核拒绝/已取消）做特殊处理：截断后续节点并追加终态节点
     * @param array $afterSale 售后单数据
     * @return array
     */
    protected function buildTimeline(array $afterSale): array
    {
        $type = $afterSale['type'] ?? '';
        $status = $afterSale['status'] ?? '';

        // 各售后类型的时间线节点定义
        $nodeDefs = [
            'refund_only'   => ['apply', 'audit', 'refund', 'complete'],
            'return_refund' => ['apply', 'audit', 'return', 'receive', 'refund', 'complete'],
            'exchange'      => ['apply', 'audit', 'return', 'receive', 'resend', 'complete'],
            'resend'        => ['apply', 'audit', 'resend', 'complete'],
            'repair'        => ['apply', 'audit', 'return', 'receive', 'repair', 'resend', 'complete'],
        ];
        $nodes = $nodeDefs[$type] ?? ['apply', 'audit', 'complete'];

        // 节点文案与时间字段映射
        $nodeMeta = [
            'apply'    => ['label' => '申请售后', 'time_field' => 'add_time'],
            'audit'    => ['label' => '审核通过', 'time_field' => 'audit_time'],
            'return'   => ['label' => '用户退货', 'time_field' => 'return_time'],
            'receive'  => ['label' => '商家收货', 'time_field' => 'receive_time'],
            'refund'   => ['label' => '退款完成', 'time_field' => 'refund_time'],
            'resend'   => ['label' => '商家补发', 'time_field' => 'resend_time'],
            'repair'   => ['label' => '维修完成', 'time_field' => 'repair_complete_time'],
            'complete' => ['label' => '售后完成', 'time_field' => 'complete_time'],
        ];

        // 审核拒绝：截断到 audit 节点并标记拒绝
        if ($status === 'audit_rejected') {
            $nodes = ['apply', 'audit'];
            $nodeMeta['audit'] = ['label' => '审核拒绝', 'time_field' => 'audit_time'];
        }
        // 用户取消：只保留 apply 节点，追加取消节点
        if ($status === 'canceled') {
            $nodes = ['apply', 'cancel'];
            $nodeMeta['cancel'] = ['label' => '用户取消', 'time_field' => 'cancel_time'];
        }

        $timeline = [];
        foreach ($nodes as $key) {
            $meta = $nodeMeta[$key] ?? null;
            if (!$meta) {
                continue;
            }
            $time = (int)($afterSale[$meta['time_field']] ?? 0);
            $timeline[] = [
                'key'   => $key,
                'label' => $meta['label'],
                'time'  => $time,
                'done'  => $time > 0,
            ];
        }
        return $timeline;
    }

    // ===== 售后生命周期 =====

    /**
     * 生成售后单号
     * @return string
     */
    protected function generateNo(): string
    {
        return 'AS' . date('YmdHis') . str_pad((string)mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
    }

    /**
     * 获取产品类型对应的售后生命周期处理器
     * 从 AfterSaleRuleRegistry 的 product_type 维度获取（规则提供者与生命周期回调合一）
     * @param string $productType
     * @return AfterSaleHandlerInterface|null
     */
    protected function getLifecycleHandler(string $productType): ?AfterSaleHandlerInterface
    {
        return AfterSaleRuleRegistry::getProvider('product_type', $productType);
    }

    /**
     * 用户提交售后申请
     * v8.1.1 增强：支持按 cart_id 选择商品范围和部分数量售后
     * @param array $data
     *   - order_no: string 订单号
     *   - type: string 售后类型
     *   - refund_price: float 退款金额
     *   - reason: string 申请原因
     *   - evidence_images: array 凭证图片
     *   - product_snapshot: array 商品快照（可选，若不传则由 items 自动构建）
     *   - items: array 售后商品列表 [{cart_id, refund_num}]，新参数
     * @return array
     */
    public function apply(array $data): array
    {
        $result = $this->transaction(function () use ($data): array {
            // 在读取可退明细前锁订单，数量校验、积分分配与申请保存使用同一串行视图。
            $center = $this->centerDao->getByOrderNoForUpdate((string)($data['order_no'] ?? ''));
            if (!$center) {
                throw new \RuntimeException('订单不存在');
            }
            return $this->applyLocked($data, $center->toArray());
        });
        // 售后单创建成功后派发事件：扩展层标记退款中 + 用户/客服双侧通知（监听器内部自行隔离失败）
        $this->fireRefundCreateAfterEvent($result);
        $lifecycle = $this->getLifecycleHandler((string)$result['product_type']);
        if ($lifecycle) {
            $lifecycle->afterApply((string)$result['after_sale_no'], $data);
        }
        return $result;
    }

    /**
     * 派发 OrderRefundCreateAfter 事件
     * 载荷首参对齐各扩展层监听器取数习惯：order_id=订单号，并附带通知所需的价格/商品名
     * @param array $afterSale 售后单数据（apply 落库后的完整记录）
     * @return void
     */
    protected function fireRefundCreateAfterEvent(array $afterSale): void
    {
        try {
            $orderNo = (string)($afterSale['order_no'] ?? '');
            $centerOrder = $orderNo !== '' ? $this->centerDao->get(['order_no' => $orderNo]) : null;
            /** @var array $centerArr */
            $centerArr = $centerOrder ? $centerOrder->toArray() : [];
            // 商品名与发货事件同源：从 extra_info.items 提取
            $storeName = '';
            $extra = $centerArr['extra_info'] ?? [];
            if (is_string($extra)) {
                $extra = json_decode($extra, true) ?: [];
            }
            foreach ($extra['items'] ?? [] as $item) {
                if (($item['store_name'] ?? '') !== '') {
                    $storeName .= ($storeName === '' ? '' : '|') . (string)$item['store_name'];
                }
            }
            $eventOrder = array_merge($centerArr, [
                'order_id'      => $orderNo,                                        // 监听器以 order_id 取订单号
                'pay_price'     => (string)($centerArr['pay_price'] ?? '0.00'),      // 订单实付金额
                'refund_price'  => (string)($afterSale['refund_price'] ?? '0.00'),   // 申请退款金额
                'store_name'    => $storeName,                                       // 商品名（多商品竖线分隔）
                'refund_status' => '申请售后',                                        // 通知展示用售后状态
            ]);
            event('OrderRefundCreateAfter', [$eventOrder, $afterSale]);
        } catch (\Throwable $e) {
            Log::error('派发 OrderRefundCreateAfter 事件失败:' . $e->getMessage());
        }
    }

    /**
     * 在订单锁内校验并保存售后申请。
     * @param array $data 申请数据
     * @param array $center 已锁定的中台订单
     * @return array
     */
    protected function applyLocked(array $data, array $center): array
    {
        $orderNo = $data['order_no'] ?? '';
        $type = $data['type'] ?? '';
        $uid = intval($data['uid'] ?? 0);
        $items = $data['items'] ?? [];

        if ((int)$center['uid'] !== $uid) {
            throw new \RuntimeException('订单不存在');
        }

        // 2. 商品级售后校验
        // 校验订单支付状态：未付款/已取消/已删除的订单不允许售后
        $eligibility = $this->checkOrderEligibility($center);
        if (!$eligibility['eligible']) {
            throw new \RuntimeException($eligibility['reason']);
        }
        $productType = $center['product_type'] ?? 'normal';
        $sourceOrder = $this->getSourceOrder($center);
        $rule = $this->ruleResolver->resolve($center, $sourceOrder);
        if (!$rule['allow']) {
            throw new \RuntimeException($rule['reject_reason'] ?: '当前订单不支持售后');
        }
        if (!empty($rule['available_types']) && !in_array($type, $rule['available_types'])) {
            throw new \RuntimeException('当前订单不支持该售后类型');
        }

        // 3. 校验 items 参数（商品级售后范围）
        $cartIds = [];
        $totalRefundNum = 0;
        $maxRefundPrice = '0.00'; // 退款金额上限 = 商品金额 + 按比例分摊的实付运费
        $maxRefundIntegral = 0;   // 退还积分上限 = Σ(integral_unit_price × refund_num)
        $selectedItemsForPostage = [];
        $refundableItems = $this->loadRefundableItems($orderNo); // 商品级可退数量
        $refundableMap = []; // cart_id => refundable_num
        $itemDetailMap = []; // cart_id => order_item detail（构建快照用）
        foreach ($refundableItems as $ri) {
            $refundableMap[(int)$ri['cart_id']] = $ri;
        }

        if (empty($items)) {
            // 兼容旧前端：未传 items 时，自动选择全部 refundable_num>0 的商品
            foreach ($refundableItems as $ri) {
                if ($ri['refundable_num'] > 0) {
                    $items[] = [
                        'cart_id'    => $ri['cart_id'],
                        'refund_num' => $ri['refundable_num'],
                    ];
                }
            }
        }

        if (empty($items)) {
            throw new \RuntimeException('请选择要售后的商品');
        }

        foreach ($items as $idx => $item) {
            $cartId = (int)($item['cart_id'] ?? 0);
            $refundNum = (int)($item['refund_num'] ?? 0);

            if ($cartId <= 0) {
                throw new \RuntimeException("第" . ($idx + 1) . "个商品 cart_id 缺失");
            }
            if (in_array($cartId, $cartIds, true)) {
                throw new \RuntimeException('同一商品不能重复选择售后');
            }
            if ($refundNum <= 0) {
                throw new \RuntimeException("请填写商品售后数量");
            }

            // 校验 cart_id 归属该订单
            if (!isset($refundableMap[$cartId])) {
                throw new \RuntimeException("商品 cart_id={$cartId} 不属于该订单");
            }

            $ri = $refundableMap[$cartId];

            // 校验剩余可退数量
            if ($refundNum > $ri['refundable_num']) {
                $storeName = $ri['store_name'] ?: "cart_id={$cartId}";
                throw new \RuntimeException("商品「{$storeName}」最多可退 {$ri['refundable_num']} 件");
            }

            // 校验该 cart_id 是否已有进行中售后（防重复申请）
            $active = $this->dao->getActiveByCartId($orderNo, $cartId);
            if ($active) {
                $storeName = $ri['store_name'] ?: "cart_id={$cartId}";
                throw new \RuntimeException("商品「{$storeName}」已有进行中的售后申请");
            }

            $cartIds[] = $cartId;
            $totalRefundNum += $refundNum;
            $maxRefundPrice = bcadd($maxRefundPrice, bcmul((string)$ri['true_price'], (string)$refundNum, 4), 2);
            $selectedItemsForPostage[] = [
                'cart_id'    => $cartId,
                'refund_num' => $refundNum,
                'true_price' => $ri['true_price'],
            ];
            $lineIntegral = (int)($ri['integral_unit_price'] ?? 0) > 0
                ? (int)$ri['integral_unit_price'] * $refundNum
                : $this->deductionIntegralForQuantity((int)$ri['deduction_integral_total'], (int)$ri['cart_num'], (int)$ri['refunded_num'], $refundNum);
            $maxRefundIntegral += $lineIntegral;
            $ri['selected_integral'] = $lineIntegral;
            $itemDetailMap[$cartId] = $ri;
        }

        // 运费按本次售后商品金额占订单商品总额的比例计入退款上限，避免部分售后重复退整笔运费。
        $refundPostage = $this->calculateRefundPostage($center, $refundableItems, $selectedItemsForPostage);
        $maxRefundPrice = bcadd($maxRefundPrice, $refundPostage, 2);

        // 4. 校验退款金额/退还积分（仅退款类售后需要校验，换货/补发/维修不涉及退款）
        // 退款金额（现金）与退还积分是两个独立维度，可同时 > 0（积分+余额混合支付订单）
        // exchange/resend/repair 为非退款型售后，退款金额与积分强制为 0，避免误填导致资金异常
        $isRefundType = in_array($type, ['refund_only', 'return_refund'], true);
        $refundPrice = 0.0;
        $refundIntegral = 0;
        if ($isRefundType) {
            $refundPrice = (float)($data['refund_price'] ?? 0);
            $refundIntegral = (int)($data['refund_integral'] ?? 0);

            // 纯积分订单（无现金可退）：退还积分未传时按可退积分自动计算
            if ($refundIntegral <= 0 && $maxRefundIntegral > 0
                && ((float)$maxRefundPrice <= 0 || (int)($center['use_integral_num'] ?? 0) > 0)) {
                $refundIntegral = $maxRefundIntegral;
            }

            // 退款金额与退还积分至少一项大于 0
            if ($refundPrice <= 0 && $refundIntegral <= 0) {
                throw new \RuntimeException('退款金额或退还积分至少一项必须大于 0');
            }
            if ($refundPrice > (float)$maxRefundPrice) {
                throw new \RuntimeException("退款金额不能超过 {$maxRefundPrice} 元");
            }
            if ($refundIntegral > $maxRefundIntegral) {
                throw new \RuntimeException("退还积分不能超过 {$maxRefundIntegral}");
            }
            if ((int)($center['use_integral_num'] ?? 0) > 0
                && in_array((string)($center['activity_type'] ?? ''), ['', 'normal'], true)) {
                // 普通抵扣积分随选中的退货数量完整返还，不能用少退积分制造无法再申请的尾差。
                $refundIntegral = $maxRefundIntegral;
            }
        } else {
            // 非退款类售后：强制退款金额/积分为 0，忽略前端误传
            $refundPrice = 0.0;
            $refundIntegral = 0;
        }

        $applicationValidator = $this->getLifecycleHandler($productType);
        if ($applicationValidator instanceof \crmeb\trade\order\aftersale\AfterSaleApplicationValidatorInterface) {
            $applicationValidator->validateApplication($center, array_replace($data, [
                'refund_price' => $refundPrice,
                'refund_integral' => $refundIntegral,
            ]), $items);
        }

        // 5. 构建 product_snapshot（若前端未传，则由 items 自动构建）
        $snapshot = $data['product_snapshot'] ?? [];
        if (empty($snapshot) || !is_array($snapshot)) {
            $snapItems = [];
            foreach ($items as $item) {
                $cartId = (int)$item['cart_id'];
                $refundNum = (int)$item['refund_num'];
                if (!isset($itemDetailMap[$cartId])) {
                    continue;
                }
                $ri = $itemDetailMap[$cartId];
                $snapItems[] = [
                    'cart_id'    => $cartId,
                    'product_id'  => $ri['product_id'],
                    'store_name' => $ri['store_name'],
                    'image'       => $ri['image'],
                    'sku'         => $ri['sku'],
                    'cart_num'    => $refundNum, // 售后数量（非订单购买数量）
                    'true_price'  => $ri['true_price'],
                ];
            }
            $snapshot = [
                'product_type' => $productType,
                'order_no'     => $orderNo,
                'order_amount' => $center['pay_price'] ?? '0.00',
                'items'        => $snapItems,
            ];
        }
        // 快照记录本次实际申请可包含的运费，便于售后详情审计和后续兼容处理。
        $productRefundAmount = 0.0;
        foreach ($selectedItemsForPostage as $selectedItem) {
            $productRefundAmount += (float)$selectedItem['true_price'] * (int)$selectedItem['refund_num'];
        }
        $requestedPostage = max(0, min(
            (float)$refundPostage,
            (float)$refundPrice - $productRefundAmount
        ));
        $snapshot['refund_postage'] = number_format($requestedPostage, 2, '.', '');

        // 6. 创建售后单
        $afterSaleNo = $this->generateNo();
        $refundPriceString = number_format($refundPrice, 2, '.', '');
        $afterSaleData = [
            'after_sale_no'   => $afterSaleNo,
            'order_center_id' => intval($center['id']),
            'order_no'        => $orderNo,
            'uid'             => $uid,
            'order_category'  => $center['order_category'] ?? '',
            'product_type'    => $productType,
            'activity_type'   => $center['activity_type'] ?? '',
            'type'            => $type,
            'status'          => 'pending_audit',
            'before_status'   => $center['status_code'] ?? '', // 售后前中台状态，取消/驳回时恢复
            'refund_price'    => $refundPriceString,
            'refund_integral' => $refundIntegral,
            // 退款标的物：现金与积分可同时存在，both 表示混合退款
            'refund_target'   => $refundIntegral > 0 && $refundPrice > 0
                ? 'both'
                : ($refundIntegral > 0 ? 'integral' : 'cash'),
            'reason'          => $data['reason'] ?? '',
            'remark'          => trim((string)($data['remark'] ?? '')),
            'evidence_images' => !empty($data['evidence_images']) && is_array($data['evidence_images'])
                ? json_encode($data['evidence_images']) : '',
            'product_snapshot'=> !empty($snapshot) && is_array($snapshot)
                ? json_encode($snapshot) : '',
            'cart_ids'        => $cartIds, // 模型修改器会自动 json_encode
            'refund_num'      => $totalRefundNum,
            'add_time'        => time(),
        ];
        $afterSale = $this->transaction(function () use (
            $afterSaleData,
            $items,
            $itemDetailMap,
            $productType,
            $center,
            $refundPriceString,
            $refundIntegral,
            $orderNo
        ) {
            $afterSale = $this->dao->save($afterSaleData);
            $rows = $this->buildAfterSaleItems(
                (int)$afterSale['id'],
                $items,
                $itemDetailMap,
                $productType,
                (string)($center['activity_type'] ?? ''),
                $refundPriceString,
                $refundIntegral
            );
            if ($rows) {
                /** @var AfterSaleItemDao $itemDao */
                $itemDao = app()->make(AfterSaleItemDao::class);
                $itemDao->saveAll($rows);
            }

            // 售后单与规范化商品明细必须同时落库，避免退款阶段重新猜测 JSON 快照。
            $this->centerServices->syncStatus($orderNo, OrderCenter::STATUS_AFTER_SALE, [
                'refund_status' => OrderCenter::REFUND_APPLYING,
            ]);
            return $afterSale;
        });

        return $afterSale->toArray();
    }

    /**
     * 审核售后
     * @param int $afterSaleId
     * @param string $result passed/rejected
     * @param int $auditUid
     * @param string $rejectReason
     * @param string $auditRemark
     * @return void
     */
    public function audit(int $afterSaleId, string $result, int $auditUid = 0, string $rejectReason = '', string $auditRemark = ''): void
    {
        $afterSale = $this->dao->get($afterSaleId);
        if (empty($afterSale)) {
            throw new \RuntimeException('售后单不存在');
        }
        if ($afterSale['status'] !== 'pending_audit') {
            throw new \RuntimeException('当前状态不可审核');
        }

        $updateData = [
            'audit_uid'    => $auditUid,
            'audit_remark' => $auditRemark,
            'audit_time'   => time(),
        ];

        if ($result === 'reject') {
            $updateData['status'] = 'audit_rejected';
            $updateData['reject_reason'] = $rejectReason;
            $this->dao->update($afterSaleId, $updateData);

            // 审核拒绝：中台订单状态恢复到售后前状态
            $this->restoreOrderStatus($afterSale);

            // 审核拒绝后派发事件：扩展层恢复原状态 + 通知用户拒绝原因
            $this->fireRefundRefuseEvent($afterSale->toArray(), $updateData);

            // 触发生命周期回调
            $lifecycle = $this->getLifecycleHandler($afterSale['product_type']);
            if ($lifecycle) {
                $lifecycle->afterAudit($afterSale['after_sale_no'], [
                    'result'       => 'reject',
                    'reject_reason'=> $rejectReason,
                    'audit_remark' => $auditRemark,
                ]);
            }
            return;
        }

        // 审核通过：按售后类型自动流转到下一状态
        // refund_only / resend 保持 audit_passed（等商家手动执行退款/补发）
        // return_refund/exchange/repair → pending_return（待用户填退货物流）
        $type = $afterSale['type'] ?? '';
        switch ($type) {
            case 'return_refund':
            case 'exchange':
            case 'repair':
                $updateData['status'] = 'pending_return';
                break;
            default:
                // refund_only / resend 资金或物流类：停留 audit_passed，等商家执行退款或补发
                $updateData['status'] = 'audit_passed';
                break;
        }
        $this->dao->update($afterSaleId, $updateData);

        // 触发生命周期回调
        $lifecycle = $this->getLifecycleHandler($afterSale['product_type']);
        if ($lifecycle) {
            $lifecycle->afterAudit($afterSale['after_sale_no'], [
                'result'       => 'passed',
                'audit_remark' => $auditRemark,
            ]);
        }
    }

    /**
     * 派发 OrderRefundRefuse 事件
     * 载荷首参对齐扩展层监听器取数习惯：store_order_id=中台订单ID，附带通知所需的价格/商品名/拒绝原因
     * @param array $afterSale 售后单数据
     * @param array $updateData 审核更新数据（含 reject_reason）
     * @return void
     */
    protected function fireRefundRefuseEvent(array $afterSale, array $updateData): void
    {
        try {
            $orderNo = (string)($afterSale['order_no'] ?? '');
            $centerOrder = $orderNo !== '' ? $this->centerDao->get(['order_no' => $orderNo]) : null;
            /** @var array $centerArr */
            $centerArr = $centerOrder ? $centerOrder->toArray() : [];
            // 商品名与发货事件同源：从 extra_info.items 提取
            $storeName = '';
            $extra = $centerArr['extra_info'] ?? [];
            if (is_string($extra)) {
                $extra = json_decode($extra, true) ?: [];
            }
            foreach ($extra['items'] ?? [] as $item) {
                if (($item['store_name'] ?? '') !== '') {
                    $storeName .= ($storeName === '' ? '' : '|') . (string)$item['store_name'];
                }
            }
            $eventOrder = array_merge($centerArr, [
                'order_id'       => $orderNo,                                          // 订单号
                'store_order_id' => (int)($afterSale['order_center_id'] ?? 0),         // 扩展层以此判定产品类型归属
                'pay_price'      => (string)($centerArr['pay_price'] ?? '0.00'),        // 订单实付金额
                'refund_price'   => (string)($afterSale['refund_price'] ?? '0.00'),     // 申请退款金额
                'refuse_reason'  => (string)($updateData['reject_reason'] ?? ''),       // 拒绝原因
                'store_name'     => $storeName,                                         // 商品名（多商品竖线分隔）
            ]);
            event('OrderRefundRefuse', [$eventOrder]);
        } catch (\Throwable $e) {
            Log::error('派发 OrderRefundRefuse 事件失败:' . $e->getMessage());
        }
    }

    /**
     * 用户填写退货物流
     * @param int $afterSaleId
     * @param array $deliveryData
     * @return void
     */
    public function returnGoods(int $afterSaleId, array $deliveryData): void
    {
        $afterSale = $this->dao->get($afterSaleId);
        if (empty($afterSale)) {
            throw new \RuntimeException('售后单不存在');
        }
        if ($afterSale['status'] !== 'pending_return') {
            throw new \RuntimeException('当前状态不可填写退货物流');
        }

        $this->dao->update($afterSaleId, [
            'status'                => 'receiving',
            'return_delivery_type'  => $deliveryData['delivery_type'] ?? '',
            'return_delivery_no'    => $deliveryData['delivery_no'] ?? '',
            'return_delivery_images'=> !empty($deliveryData['images']) && is_array($deliveryData['images'])
                ? json_encode($deliveryData['images']) : '',
            'return_time'           => time(),
        ]);

        $lifecycle = $this->getLifecycleHandler($afterSale['product_type']);
        if ($lifecycle) {
            $lifecycle->afterReturn($afterSale['after_sale_no'], $deliveryData);
        }
    }

    /**
     * 商家确认收货
     * 确认后按售后类型分流：return_refund→执行退款 / exchange→补发 / repair→维修
     * @param int $afterSaleId
     * @return void
     */
    public function receive(int $afterSaleId): void
    {
        $afterSale = $this->dao->get($afterSaleId);
        if (empty($afterSale)) {
            throw new \RuntimeException('售后单不存在');
        }
        if ($afterSale['status'] !== 'receiving') {
            throw new \RuntimeException('当前状态不可确认收货');
        }

        $this->dao->update($afterSaleId, [
            'receive_time' => time(),
        ]);

        $lifecycle = $this->getLifecycleHandler($afterSale['product_type']);
        if ($lifecycle) {
            $lifecycle->afterReceive($afterSale['after_sale_no']);
        }

        // 按售后类型分流
        $type = $afterSale['type'];
        if (in_array($type, ['return_refund', 'exchange', 'repair'])) {
            if ($type === 'return_refund') {
                // 退货退款：继续推进退款
                // 调用方需在 receive 后调用 refund()
            } elseif ($type === 'exchange') {
                // 换货：推进补发
                // 调用方需在 receive 后调用 resend()
            } elseif ($type === 'repair') {
                // 维修：推进维修
                $this->updateStatus($afterSaleId, 'repairing', ['repair_time' => time()]);

                if ($lifecycle) {
                    $lifecycle->afterRepair($afterSale['after_sale_no'], 'repairing');
                }
            }
        }
    }

    /**
     * 执行退款
     * 创建唯一退款意图并交给可恢复状态机推进。重复请求返回同一退款单。
     * @param int $afterSaleId
     * @param float $amount 实际退款金额
     * @param array $context
     * @return array
     */
    public function refund(int $afterSaleId, float $amount, array $context = []): array
    {
        /** @var RefundOrderServices $refundServices */
        $refundServices = app()->make(RefundOrderServices::class);
        return $refundServices->request($afterSaleId, number_format($amount, 2, '.', ''), $context);
    }

    /**
     * 补发商品
     * @param int $afterSaleId
     * @param array $deliveryData
     * @return void
     */
    public function resend(int $afterSaleId, array $deliveryData): void
    {
        $afterSale = $this->dao->get($afterSaleId);
        if (empty($afterSale)) {
            throw new \RuntimeException('售后单不存在');
        }

        $type = $afterSale['type'];
        $allowedStatus = ['exchange' => ['receiving'], 'repair' => ['repair_complete'], 'resend' => ['audit_passed']];
        $allowed = $allowedStatus[$type] ?? ['audit_passed'];
        if (!in_array($afterSale['status'], $allowed)) {
            throw new \RuntimeException('当前状态不可补发商品');
        }

        $this->dao->update($afterSaleId, [
            'status'             => 'resending',
            'resend_delivery_type'=> $deliveryData['delivery_type'] ?? '',
            'resend_delivery_no'  => $deliveryData['delivery_no'] ?? '',
            'resend_time'         => time(),
        ]);

        $lifecycle = $this->getLifecycleHandler($afterSale['product_type']);
        if ($lifecycle) {
            $lifecycle->afterResend($afterSale['after_sale_no'], $deliveryData);
        }
    }

    /**
     * 维修完成
     * @param int $afterSaleId
     * @param string $repairRemark
     * @return void
     */
    public function repairComplete(int $afterSaleId, string $repairRemark = ''): void
    {
        $afterSale = $this->dao->get($afterSaleId);
        if (empty($afterSale)) {
            throw new \RuntimeException('售后单不存在');
        }
        if ($afterSale['status'] !== 'repairing') {
            throw new \RuntimeException('当前状态不可维修完成');
        }

        $this->dao->update($afterSaleId, [
            'status'              => 'repair_complete',
            'repair_remark'       => $repairRemark,
            'repair_complete_time'=> time(),
        ]);

        $lifecycle = $this->getLifecycleHandler($afterSale['product_type']);
        if ($lifecycle) {
            $lifecycle->afterRepair($afterSale['after_sale_no'], 'repair_complete');
        }
    }

    /**
     * 用户确认收货（补发/维修完成后）
     * @param int $afterSaleId
     * @return void
     */
    public function confirmReceive(int $afterSaleId): void
    {
        $afterSale = $this->dao->get($afterSaleId);
        if (empty($afterSale)) {
            throw new \RuntimeException('售后单不存在');
        }
        if ($afterSale['status'] !== 'resending') {
            throw new \RuntimeException('当前状态不可确认收货');
        }

        // 补发收货后售后完结
        $this->complete($afterSaleId);
    }

    /**
     * 取消售后
     * @param int $afterSaleId
     * @return void
     */
    public function cancel(int $afterSaleId): void
    {
        $afterSale = $this->dao->get($afterSaleId);
        if (empty($afterSale)) {
            throw new \RuntimeException('售后单不存在');
        }

        // 只有待审核状态可取消
        if (!in_array($afterSale['status'], ['pending_audit', 'audit_passed'])) {
            throw new \RuntimeException('当前状态不可取消售后');
        }

        $this->dao->update($afterSaleId, [
            'status'     => 'canceled',
            'cancel_time'=> time(),
        ]);

        // 取消售后：中台订单状态恢复到售后前状态
        $this->restoreOrderStatus($afterSale);

        $lifecycle = $this->getLifecycleHandler($afterSale['product_type']);
        if ($lifecycle) {
            $lifecycle->afterCancel($afterSale['after_sale_no']);
        }

        // 外平台售后单取消推送
        event('OutPush', ['refund_cancel_push', ['after_sale_id' => $afterSaleId]]);
    }

    /**
     * 恢复中台订单状态
     * 售后取消/驳回时，将中台订单状态恢复到申请售后前的状态码，并清空退款申请标记
     * @param array|object $afterSale 售后单数据（需含 order_no/before_status），兼容模型对象与数组
     * @return void
     */
    protected function restoreOrderStatus($afterSale): void
    {
        // 兼容模型对象与数组（dao->get 返回模型对象，部分调用处传数组）
        if (is_object($afterSale)) {
            $afterSale = $afterSale->toArray();
        }
        if (!is_array($afterSale)) {
            return;
        }
        $orderNo = $afterSale['order_no'] ?? '';
        $beforeStatus = $afterSale['before_status'] ?? '';
        if (empty($orderNo) || empty($beforeStatus)) {
            return;
        }
        $this->centerServices->syncStatus($orderNo, $beforeStatus, [
            'refund_status' => OrderCenter::REFUND_NONE,
        ]);
    }

    /**
     * 售后完结
     * 非退款型售后（换货/补发/维修）流程完成后，中台订单状态恢复到售后前状态；
     * 退款型售后（仅退款/退货退款）退款时中台已置 refunded，此处不覆盖
     * @param int $afterSaleId
     * @return void
     */
    protected function complete(int $afterSaleId): void
    {
        $afterSale = $this->dao->get($afterSaleId);
        if (empty($afterSale)) {
            return;
        }

        $this->dao->update($afterSaleId, [
            'status'       => 'completed',
            'complete_time'=> time(),
        ]);

        // 非退款型售后完成后恢复中台订单状态（无退款，订单恢复原流转）
        if (in_array($afterSale['type'], ['exchange', 'resend', 'repair'])) {
            $this->restoreOrderStatus($afterSale);
        }

        $lifecycle = $this->getLifecycleHandler($afterSale['product_type']);
        if ($lifecycle) {
            $lifecycle->afterComplete($afterSale['after_sale_no']);
        }
    }

    /**
     * 更新售后状态（内部辅助方法）
     * @param int $afterSaleId
     * @param string $status
     * @param array $extraData
     * @return void
     */
    protected function updateStatus(int $afterSaleId, string $status, array $extraData = []): void
    {
        $updateData = array_merge(['status' => $status], $extraData);
        $this->dao->update($afterSaleId, $updateData);
    }

    // ===== 用户端列表 =====

    /**
     * 获取用户售后列表
     * 支持按状态分组筛选（processing/completed/canceled），附加 type_text/status_text 文案
     * 仅返回当前用户名下的售后单，自动过滤软删除记录
     * @param int $uid 用户ID
     * @param int $page 页码（从1开始）
     * @param int $limit 每页数量
     * @param string $statusGroup 状态分组：空=全部，processing=处理中，completed=已完成，canceled=已取消
     * @return array ['list'=>[], 'count'=>int]
     */
    public function getListForUser(int $uid, int $page = 1, int $limit = 10, string $statusGroup = ''): array
    {
        // 基础条件：限定当前用户 + 未删除
        $map = [
            ['uid', '=', $uid],
            ['is_del', '=', 0],
        ];

        // 状态分组筛选：非法分组按全部处理
        if ($statusGroup !== '' && isset(self::STATUS_GROUP_MAP[$statusGroup])) {
            $map[] = ['status', 'in', self::STATUS_GROUP_MAP[$statusGroup]];
        }

        // with('orderCenter') 预载入关联订单，供 foreach 提取 order_info（订单号/状态/标题/主图等）
        // 未预载入则 $item['order_center'] 为 null，列表项订单信息与商品兜底图均无法展示
        $list = $this->dao->getList($map, $page, $limit, '*', ['orderCenter']);

        // 产品类型注册中心：循环外取一次，用于动态获取产品类型中文名（实物/虚拟/卡密等）
        $productTypeRegistry = null;
        try {
            $productTypeRegistry = app()->make(\crmeb\trade\product\ProductTypeRegistry::class);
        } catch (\Throwable $e) {
            // 注册中心不可用时静默降级，product_type_text 留空
        }
        // 附加类型/状态文案与状态分组，避免前端硬编码映射表
        $specOrders = [];
        foreach ($list as &$item) {
            $item['type_text'] = self::TYPE_TEXT_MAP[$item['type'] ?? ''] ?? ($item['type'] ?? '');
            $item['status_text'] = self::STATUS_TEXT_MAP[$item['status'] ?? ''] ?? ($item['status'] ?? '');
            $item['status_group'] = $this->resolveStatusGroup($item['status'] ?? '');
            // 产品类型中文名：动态从注册中心获取，新增产品类型注册即生效
            $productType = $item['product_type'] ?? '';
            $productTypeText = '';
            if ($productType && $productTypeRegistry && $productTypeRegistry->has($productType)) {
                try {
                    $productTypeText = $productTypeRegistry->get($productType)->getTypeName();
                } catch (\Throwable $e) {
                    // 类型未注册时静默降级
                }
            }
            $item['product_type_text'] = $productTypeText;
            // 提取关联订单基本信息到 order_info，供前端列表展示订单号/订单状态/下单时间/金额/数量
            // title/image 作为商品快照缺失时的兜底来源（order_center 表已含订单标题/主图）
            // 注意：ThinkPHP with('orderCenter') 预载入后数组 key 为关联方法名 orderCenter（驼峰），
            //       不是 order_center（下划线），写错 key 会恒取 null
            $orderCenter = $item['orderCenter'] ?? null;
            $extraInfo = [];
            if (is_array($orderCenter) && $orderCenter) {
                $orderStatusCode = $orderCenter['status_code'] ?? '';
                $item['order_info'] = [
                    'order_no'    => $orderCenter['order_no'] ?? '',
                    'status_code' => $orderStatusCode,
                    'status_text' => OrderCenter::$statusMap[$orderStatusCode] ?? '',
                    'add_time'    => $orderCenter['add_time'] ?? 0,
                    'pay_price'   => $orderCenter['pay_price'] ?? '0.00',
                    'total_num'   => $orderCenter['total_num'] ?? 0,
                    'title'       => $orderCenter['title'] ?? '',
                    'image'       => $orderCenter['image'] ?? '',
                ];
                // 解析 extra_info（JSON），供规格信息组装（床型/票种/预订时间等）
                $rawExtra = $orderCenter['extra_info'] ?? '';
                if ($rawExtra) {
                    $extraInfo = is_array($rawExtra) ? $rawExtra : (json_decode($rawExtra, true) ?: []);
                }
            } else {
                $item['order_info'] = null;
            }
            // 收集规格扩展所需信息（按 order_no），循环外批量触发事件，避免 N+1 查询
            $orderNo = $item['order_no'] ?? ($orderCenter['order_no'] ?? '');
            if ($orderNo) {
                $specOrders[$orderNo] = [
                    'product_type' => $productType,
                    'extra_info'   => $extraInfo,
                    'source_type'  => is_array($orderCenter) ? ($orderCenter['source_type'] ?? '') : '',
                    'source_id'    => is_array($orderCenter) ? ($orderCenter['source_id'] ?? 0) : 0,
                ];
            }
            unset($item['orderCenter']);
        }
        unset($item);

        // 批量加载规格信息：通过事件让产品类型监听器组装（hotel/ticket 等），normal 默认从 extra_info 提取 sku
        $specMap = $this->loadListSpecInfo($specOrders);
        foreach ($list as &$item) {
            $item['spec_info'] = $specMap[$item['order_no'] ?? ''] ?? [];
        }
        unset($item);

        $count = $this->dao->count($map);
        return ['list' => $list, 'count' => $count];
    }

    /**
     * 批量加载列表规格信息
     * 通过 AfterSaleListSpecExtend 事件让各产品类型监听器自行组装规格（床型/票种/预订时间等），
     * normal 默认从 extra_info.items[0].sku 提取规格。架构合规：app 层不查扩展表，由扩展模块监听器自治
     * @param array $specOrders 以 order_no 为键的订单信息集合（product_type/extra_info/source_type/source_id）
     * @return array 以 order_no 为键的规格信息集合，每个值为 [{label,value},...]
     */
    protected function loadListSpecInfo(array $specOrders): array
    {
        if (empty($specOrders)) {
            return [];
        }
        $specMap = [];
        // 触发事件，监听器返回 [order_no => [{label,value},...]]
        $results = event('AfterSaleListSpecExtend', ['orders' => $specOrders]);
        if (is_array($results)) {
            foreach ($results as $ret) {
                if (!is_array($ret)) {
                    continue;
                }
                foreach ($ret as $orderNo => $specs) {
                    // 首个监听器返回优先，避免重复组装
                    if (!isset($specMap[$orderNo]) && is_array($specs)) {
                        $specMap[$orderNo] = $specs;
                    }
                }
            }
        }
        // normal 默认：从 extra_info.items[0].sku 提取规格（normal 是中台默认产品类型，app 层处理）
        foreach ($specOrders as $orderNo => $info) {
            if (isset($specMap[$orderNo])) {
                continue;
            }
            $items = $info['extra_info']['items'] ?? [];
            $sku = $items[0]['sku'] ?? ($items[0]['attr_info']['suk'] ?? '');
            if ($sku !== '' && $sku !== null) {
                $specMap[$orderNo] = [['label' => '规格', 'value' => (string)$sku]];
            }
        }
        return $specMap;
    }

    /**
     * 解析售后状态所属分组
     * 用于前端列表项着色等展示逻辑，返回 processing/completed/canceled，未知状态返回空
     * @param string $status 售后状态码
     * @return string
     */
    protected function resolveStatusGroup(string $status): string
    {
        foreach (self::STATUS_GROUP_MAP as $group => $statuses) {
            if (in_array($status, $statuses, true)) {
                return $group;
            }
        }
        return '';
    }

    // ===== 后台管理端 =====

    /**
     * 后台售后列表
     * 支持按售后单号/订单号/状态/类型/产品类型筛选，返回带中台订单关联的列表
     * @param array $where 筛选条件
     * @param int $page 页码
     * @param int $limit 每页数量
     * @return array
     */
    public function getListForAdmin(array $where, int $page, int $limit): array
    {
        $map = [];
        // 售后单号模糊查询
        if (!empty($where['after_sale_no'])) {
            $map[] = ['after_sale_no', 'like', '%' . $where['after_sale_no'] . '%'];
        }
        // 订单号模糊查询
        if (!empty($where['order_no'])) {
            $map[] = ['order_no', 'like', '%' . $where['order_no'] . '%'];
        }
        // 精确筛选
        foreach (['status', 'type', 'product_type', 'order_category'] as $field) {
            if (!empty($where[$field])) {
                $map[] = [$field, '=', $where[$field]];
            }
        }
        // 时间范围
        if (!empty($where['start_time']) && !empty($where['end_time'])) {
            $map[] = ['add_time', 'between', [(int)$where['start_time'], (int)$where['end_time']]];
        }
        $map[] = ['is_del', '=', 0];

        $list = $this->dao->getList($map, $page, $limit);
        // 附加文案
        foreach ($list as &$item) {
            $item['type_text'] = self::TYPE_TEXT_MAP[$item['type'] ?? ''] ?? ($item['type'] ?? '');
            $item['status_text'] = self::STATUS_TEXT_MAP[$item['status'] ?? ''] ?? ($item['status'] ?? '');
        }
        unset($item);

        $count = $this->dao->count($map);
        return ['list' => $list, 'count' => $count];
    }

    /**
     * 后台售后详情
     * 比用户端详情多返回审核信息、管理备注等后台专属字段
     * @param int $afterSaleId 售后单主键
     * @return array
     */
    public function getDetailForAdmin(int $afterSaleId): array
    {
        $afterSale = $this->dao->get($afterSaleId);
        if (empty($afterSale)) {
            return [];
        }

        // 复用用户端详情构造，再追加后台专属字段
        $detail = $this->getDetail($afterSale['after_sale_no'] ?? '');

        // 追加后台专属字段（审核/管理/物流完整信息）
        $detail['audit_uid'] = (int)($afterSale['audit_uid'] ?? 0);
        $detail['audit_remark'] = $afterSale['audit_remark'] ?? '';
        $detail['audit_time'] = (int)($afterSale['audit_time'] ?? 0);
        $detail['remark'] = $afterSale['remark'] ?? '';
        $detail['before_status'] = $afterSale['before_status'] ?? '';
        $detail['refund_integral'] = (int)($afterSale['refund_integral'] ?? 0);
        $detail['return_postage'] = (float)($afterSale['return_postage'] ?? 0);
        $detail['refund_type'] = $afterSale['refund_type'] ?? '';
        $detail['refund_trade_no'] = $afterSale['refund_trade_no'] ?? '';
        $detail['repair_remark'] = $afterSale['repair_remark'] ?? '';
        $detail['cancel_time'] = (int)($afterSale['cancel_time'] ?? 0);
        $detail['order_center_id'] = (int)($afterSale['order_center_id'] ?? 0);
        $detail['uid'] = (int)($afterSale['uid'] ?? 0);

        // 追加中台订单状态文案（便于后台展示售后前订单状态）
        $detail['before_status_text'] = OrderCenter::$statusMap[$detail['before_status']] ?? $detail['before_status'];

        /** @var \app\dao\order\RefundOrderDao $refundOrderDao */
        $refundOrderDao = app()->make(\app\dao\order\RefundOrderDao::class);
        $refundOrder = $refundOrderDao->getByAfterSaleId($afterSaleId);
        $detail['refund_order'] = $refundOrder ? [
            'refund_no' => (string)$refundOrder['refund_no'],
            'amount' => (string)$refundOrder['amount'],
            'state' => (string)$refundOrder['state'],
            'pay_channel' => (string)$refundOrder['pay_channel'],
            'channel_refund_id' => (string)$refundOrder['channel_refund_id'],
            'attempts' => (int)$refundOrder['attempts'],
            'next_retry_time' => (int)$refundOrder['next_retry_time'],
            'last_error' => (string)$refundOrder['last_error'],
            'channel_succeeded_at' => (int)$refundOrder['channel_succeeded_at'],
            'completed_at' => (int)$refundOrder['completed_at'],
        ] : null;

        return $detail;
    }

    /**
     * 后台更新管理备注
     * @param int $afterSaleId 售后单主键
     * @param string $remark 备注内容
     * @return void
     */
    public function updateRemark(int $afterSaleId, string $remark): void
    {
        $afterSale = $this->dao->get($afterSaleId);
        if (empty($afterSale)) {
            throw new \RuntimeException('售后单不存在');
        }
        $this->dao->update($afterSaleId, ['remark' => $remark]);
    }

    /**
     * 按 ID 查询售后单
     * 供后台控制器校验存在性使用
     * @param int $afterSaleId
     * @return array|null
     */
    public function getById(int $afterSaleId): ?array
    {
        $record = $this->dao->get($afterSaleId);
        return $record ? (is_object($record) ? $record->toArray() : $record) : null;
    }

    /**
     * 获取当前用户拥有的售后单（按售后单号）
     * 按售后单号查询并校验 uid 归属，防止越权操作/查询他人售后单
     * @param string $afterSaleNo 售后单号
     * @param int $uid 用户ID
     * @return array|null 命中且归属一致返回售后单原始记录，否则 null
     */
    public function getOwnedByNo(string $afterSaleNo, int $uid): ?array
    {
        $record = $this->dao->getByNo($afterSaleNo);
        if (empty($record) || (int)($record['uid'] ?? 0) !== $uid) {
            return null;
        }
        return $record;
    }

    /**
     * 查询售后物流进度
     * 复用 ExpressServices::query 统一物流查询能力，支持退货物流与补发物流两种类型
     * 快递公司名称→编码通过 resolveExpressCode 解析，匹配不到时由驱动按单号自动识别
     * @param int $afterSaleId 售后单主键
     * @param string $type 物流类型 return=退货物流 resend=补发物流
     * @return array {delivery_name, delivery_id, result}
     */
    public function queryExpress(int $afterSaleId, string $type = 'return'): array
    {
        $afterSale = $this->dao->get($afterSaleId);
        if (empty($afterSale)) {
            throw new \RuntimeException('售后单不存在');
        }

        // 按物流类型取对应快递公司名称与运单号
        if ($type === 'resend') {
            $deliveryName = $afterSale['resend_delivery_type'] ?? '';
            $deliveryNo = $afterSale['resend_delivery_no'] ?? '';
        } else {
            $deliveryName = $afterSale['return_delivery_type'] ?? '';
            $deliveryNo = $afterSale['return_delivery_no'] ?? '';
        }

        $deliveryName = (string)($deliveryName ?? '');
        $deliveryNo = (string)($deliveryNo ?? '');

        // 没有运单号无法查询物流
        if ($deliveryNo === '') {
            throw new \RuntimeException('该售后单暂无' . ($type === 'resend' ? '补发' : '退货') . '物流单号');
        }

        // 通过快递公司名称匹配编码，匹配不到返回 null 由驱动自动识别
        $com = $this->resolveExpressCode((string)$deliveryName);

        /** @var ExpressServices $expressServices */
        $expressServices = $this->app->make(ExpressServices::class);
        // 缓存键：售后单号 + 物流类型，避免与订单物流缓存冲突
        $cacheName = 'after_sale_express_' . ($afterSale['after_sale_no'] ?? $afterSaleId) . '_' . $type;
        // 顺丰等驱动会按收件手机号验单；售后单不存手机号，按快照→中台→源订单回退解析。
        $phone = $this->resolveAfterSalePhone($afterSale);
        $result = $expressServices->query($cacheName, $deliveryNo, $com, $phone);

        return [
            'delivery_name' => (string)$deliveryName,
            'delivery_id'   => (string)$deliveryNo,
            'result'        => is_array($result) ? $result : [],
        ];
    }

    /**
     * 解析售后物流查询所需的收件手机号。
     *
     * 售后表没有手机号字段：新订单将联系人快照保存在 order_center.extra_info，
     * 旧普通商品订单则保存在 pt_normal_order.user_phone。读取时不调用完整订单详情，
     * 避免在查询物流时再次触发订单详情中的物流扩展。
     *
     * @param mixed $afterSale 售后单模型或数组
     * @return string
     */
    protected function resolveAfterSalePhone($afterSale): string
    {
        $afterSaleData = $this->normalizeOrderData($afterSale);

        // 兼容历史数据中直接附带联系人或手机号的售后记录/关联。
        $phone = $this->extractOrderPhone($afterSaleData);
        if ($phone !== '') {
            return $phone;
        }
        foreach (['orderCenter', 'order_center'] as $relation) {
            if (!empty($afterSaleData[$relation])) {
                $phone = $this->extractOrderPhone($afterSaleData[$relation]);
                if ($phone !== '') {
                    return $phone;
                }
            }
        }

        $center = null;
        $orderCenterId = (int)($afterSaleData['order_center_id'] ?? 0);
        $orderNo = trim((string)($afterSaleData['order_no'] ?? ''));
        if ($orderCenterId > 0) {
            try {
                $center = $this->centerDao->get($orderCenterId, [
                    'id', 'order_no', 'product_type', 'source_type', 'source_id', 'extra_info',
                ]);
            } catch (\Throwable $e) {
                // 按 ID 读取失败时仍尝试订单号，兼容中台记录缺失或旧表结构。
                $this->logPhoneResolutionWarning('[AfterSale] resolve recipient phone by center id failed: ' . get_class($e));
            }
        }
        if (!$center && $orderNo !== '') {
            try {
                $center = $this->centerDao->getOne(
                    ['order_no' => $orderNo],
                    'id,order_no,product_type,source_type,source_id,extra_info'
                );
            } catch (\Throwable $e) {
                $this->logPhoneResolutionWarning('[AfterSale] resolve recipient phone by order number failed: ' . get_class($e));
            }
        }

        $centerData = $this->normalizeOrderData($center);
        $phone = $this->extractOrderPhone($centerData);
        if ($phone !== '') {
            return $phone;
        }

        // 旧订单手机号位于产品类型扩展表，由产品处理器自行读取。
        // 保留售后记录中的定位字段（部分历史中台记录只返回 extra_info）。
        // 中台字段优先，但不能用空值覆盖售后记录中仍然有效的定位字段。
        $sourceContext = $afterSaleData;
        if ($orderNo !== '') {
            $sourceContext['order_no'] = $orderNo;
        }
        foreach (['order_no', 'product_type', 'source_type', 'source_id'] as $field) {
            $value = $centerData[$field] ?? null;
            if ($field === 'order_no') {
                $value = trim((string)$value);
            }
            if ($value !== null && $value !== '') {
                $sourceContext[$field] = $value;
            }
        }
        if (!empty($sourceContext['product_type']) && !empty($sourceContext['order_no'])) {
            try {
                $phone = $this->extractOrderPhone($this->getSourceOrder($sourceContext));
                if ($phone !== '') {
                    return $phone;
                }
            } catch (\Throwable $e) {
                // 旧扩展表不存在或插件未启用时，保留空手机号交由驱动自动处理。
                $this->logPhoneResolutionWarning('[AfterSale] resolve recipient phone from source order failed: ' . get_class($e));
            }
        }

        return '';
    }

    /**
     * 记录手机号回退异常；未配置日志通道的裸测试环境直接跳过。
     * @param string $message
     * @return void
     */
    protected function logPhoneResolutionWarning(string $message): void
    {
        // 裸测试容器可能没有日志通道；正常应用上下文存在通道时记录回退失败原因。
        $logConfig = $this->app->make('config')->get('log', []);
        $defaultChannel = is_array($logConfig) ? ($logConfig['default'] ?? '') : '';
        $channels = is_array($logConfig) ? ($logConfig['channels'] ?? []) : [];
        if ($defaultChannel === '' || !is_array($channels) || !isset($channels[$defaultChannel])) {
            return;
        }
        Log::warning($message);
    }

    /**
     * 将模型、JSON 对象或数组统一为数组。
     * @param mixed $data
     * @return array
     */
    protected function normalizeOrderData($data): array
    {
        // ORM 模型优先使用 toArray；Json 类型对象则使用 value，避免把模型的同名查询方法当成无参转换器。
        for ($attempt = 0; $attempt < 3 && is_object($data); $attempt++) {
            if (method_exists($data, 'toArray')) {
                $data = $data->toArray();
                continue;
            }
            if (method_exists($data, 'value')) {
                $data = $data->value();
                continue;
            }
            $data = get_object_vars($data);
        }
        if (is_string($data)) {
            $decoded = json_decode($data, true);
            $data = is_array($decoded) ? $decoded : [];
        }
        return is_array($data) ? $data : [];
    }

    /**
     * 从订单/联系人快照中提取收件手机号。
     * 只读取收件人相关字段，避免把店铺或寄件人电话误作为验单手机号。
     * @param mixed $data
     * @param int $depth 当前快照递归深度
     * @return string
     */
    protected function extractOrderPhone($data, int $depth = 0): string
    {
        // 历史快照可能包含循环引用；限制递归深度，避免异常数据耗尽调用栈。
        if ($depth >= 8) {
            return '';
        }
        $data = $this->normalizeOrderData($data);
        if (empty($data)) {
            return '';
        }

        $extraInfo = $this->normalizeOrderData($data['extra_info'] ?? []);
        $contact = $this->normalizeOrderData($data['contact'] ?? []);
        $extraContact = $this->normalizeOrderData($extraInfo['contact'] ?? []);
        $candidates = [
            $data['user_phone'] ?? '',
            $data['receiver_phone'] ?? '',
            $data['consignee_phone'] ?? '',
            $data['contact_phone'] ?? '',
            $contact['phone'] ?? ($contact['user_phone'] ?? ''),
            $extraContact['phone'] ?? ($extraContact['user_phone'] ?? ''),
            $data['phone'] ?? '',
        ];

        foreach ($candidates as $candidate) {
            if (is_scalar($candidate)) {
                $phone = trim((string)$candidate);
                if ($phone !== '') {
                    return $phone;
                }
            }
        }

        // 个别历史售后快照会同时保存联系人信息，作为订单数据的最后一层兼容。
        $snapshotPhone = $this->extractOrderPhone($data['product_snapshot'] ?? [], $depth + 1);
        if ($snapshotPhone !== '') {
            return $snapshotPhone;
        }

        return '';
    }

    /**
     * 通过快递公司名称解析编码
     * 先精确匹配名称，再双向包含匹配（用户填写可能带"速运"等后缀差异）
     * 匹配不到返回 null，由物流驱动按单号自动识别所属快递公司
     * @param string $name 快递公司名称
     * @return string|null
     */
    protected function resolveExpressCode(string $name): ?string
    {
        if ($name === '') {
            return null;
        }
        try {
            /** @var ExpressServices $expressServices */
            $expressServices = $this->app->make(ExpressServices::class);
            $list = $expressServices->expressList();
            // 先精确匹配名称
            foreach ($list as $item) {
                if (($item['name'] ?? '') === $name) {
                    return $item['code'] ?? null;
                }
            }
            // 再双向包含匹配，兼容"顺丰"与"顺丰速运"等填写差异
            foreach ($list as $item) {
                $itemName = (string)($item['name'] ?? '');
                if ($itemName === '') {
                    continue;
                }
                if (mb_strpos($name, $itemName) !== false || mb_strpos($itemName, $name) !== false) {
                    return $item['code'] ?? null;
                }
            }
        } catch (\Throwable $e) {
            // 查询映射失败不阻断流程，交由驱动自动识别
        }
        return null;
    }

    // ==================== 旧退款链路承接（拦截判断/角标统计/拼团代申请/kefu·pc 列表） ====================
    // 承接自旧退款服务（已删除）：发货/收货/核销拦截、用户售后角标、
    // 拼团失败系统代申请、客服与 PC 端售后列表/详情的统一出口

    /**
     * 订单是否存在处理中的售后单（发货/收货/核销前拦截）
     * 语义对齐旧退款链路"申请退款中/待退货/待收货"四态：补发/维修等非退款态不拦截
     * @param int $orderCenterId 订单中台ID
     * @return bool
     */
    public function hasActiveByOrderId(int $orderCenterId): bool
    {
        return Db::name('after_sale')
            ->where('order_center_id', $orderCenterId)
            ->whereIn('status', ['pending_audit', 'audit_passed', 'pending_return', 'receiving'])
            ->where('is_del', 0)
            ->count() > 0;
    }

    /**
     * 按状态分组统计售后单数（用户售后角标）
     * @param int|null $uid 用户ID（null 为全站统计）
     * @param string $group refunding=处理中 / rejected=已拒绝 / refunded=已退款
     * @return int
     */
    public function getCountByStatusGroup(?int $uid, string $group): int
    {
        $query = Db::name('after_sale')->where('is_del', 0);
        if ($uid) {
            $query->where('uid', $uid);
        }
        switch ($group) {
            case 'refunding':
                // 处理中：四大进行态（对齐旧 refund_type [1,2,4,5] 语义）
                $query->whereIn('status', ['pending_audit', 'audit_passed', 'pending_return', 'receiving']);
                break;
            case 'rejected':
                $query->where('status', 'audit_rejected');
                break;
            case 'refunded':
                // 已退款：refunded 为退款落账瞬时态、completed 为终态，双取兜底历史数据
                $query->whereIn('status', ['refunded', 'completed']);
                break;
            default:
                return 0;
        }
        return (int)$query->count();
    }

    /**
     * 按订单号获取待审核的售后单（客服端处理"仅退款申请中"订单时定位售后单）
     * @param string $orderNo 订单号（中台 order_no）
     * @return array|null 售后单数据，无待审核单时返回 null
     */
    public function getPendingAuditByOrderNo(string $orderNo): ?array
    {
        $afterSale = Db::name('after_sale')
            ->where('order_no', $orderNo)
            ->where('status', 'pending_audit')
            ->where('is_del', 0)
            ->order('id', 'desc')
            ->find();
        return $afterSale ?: null;
    }

    /**
     * 系统代建售后申请（拼团超时/人数不足等场景）
     * 仅创建申请单（pending_audit）等待后台处理，不自动退款——语义对齐旧链路拼团退款申请
     * @param array $order 订单中台数据（需含 order_no/uid）
     * @param string $reason 申请原因
     * @return array|null 售后单数据（重复申请等异常时返回 null 静默幂等）
     */
    public function createSystemApply(array $order, string $reason): ?array
    {
        try {
            return $this->apply([
                'order_no' => (string)($order['order_no'] ?? ''),
                'type'     => 'refund_only',
                'uid'      => (int)($order['uid'] ?? 0),
                // items 留空：apply 内部自动选择全部可退商品（整单售后）
                'items'    => [],
                'reason'   => $reason,
            ]);
        } catch (\Throwable $e) {
            // 已存在进行中售后单/无可退商品等场景静默跳过，保证拼团批量处理不被单笔中断
            return null;
        }
    }

    /**
     * 客服/PC 端售后列表（承接旧退款列表契约）
     * @param array $where order_id=售后单号模糊 / time=申请时间范围 / refundTypes=状态分组 / real_name=用户·单号模糊 / uid
     * @return array list + count + num（七组状态计数对齐旧 tab 结构）
     */
    public function getRefundListForKefu(array $where): array
    {
        [$page, $limit] = $this->getPageValue();
        $query = Db::name('after_sale')->where('is_del', 0);

        // 售后单号 / 关联订单号 模糊查询
        $orderId = (string)($where['order_id'] ?? '');
        if ($orderId !== '') {
            $query->where(function ($q) use ($orderId) {
                $q->whereLike('after_sale_no', '%' . $orderId . '%')->whereOr(function ($q2) use ($orderId) {
                    $q2->whereIn('order_center_id', function ($sub) use ($orderId) {
                        $sub->name('order_center')->field('id')->whereLike('order_no', '%' . $orderId . '%');
                    });
                });
            });
        }
        // 用户昵称/手机号/单号模糊（kefu 聊天用户搜索）
        $realName = (string)($where['real_name'] ?? '');
        if ($realName !== '') {
            $query->where(function ($q) use ($realName) {
                $q->whereLike('after_sale_no', '%' . $realName . '%')->whereOr('uid', 'in', function ($sub) use ($realName) {
                    $sub->name('user')->whereLike('nickname|phone', '%' . $realName . '%')->field('uid')->select();
                });
            });
        }
        // 申请时间范围
        if (!empty($where['time'])) {
            $query->whereBetween('add_time', [strtotime($where['time'][0]), strtotime($where['time'][1]) + 86399]);
        }
        // 用户限定（PC 端/客服用户订单列表）
        if (!empty($where['uid'])) {
            $query->where('uid', (int)$where['uid']);
        }
        // 状态分组筛选（旧 refundTypes 数字语义映射）
        if (isset($where['refundTypes']) && $where['refundTypes'] !== '' && $where['refundTypes'] !== 0) {
            switch ((int)$where['refundTypes']) {
                case 1: // 退款处理中
                    $query->whereIn('status', ['pending_audit', 'audit_passed']);
                    break;
                case 2: // 商品待退货
                    $query->where('status', 'pending_return');
                    break;
                case 3: // 退货待收货
                    $query->where('status', 'receiving');
                    break;
                case 4: // 已退款
                    $query->whereIn('status', ['refunded', 'completed']);
                    break;
            }
        }

        $count = (clone $query)->count();
        $list = $query->page($page, $limit)->order('id DESC')->select()->toArray();
        if ($list) {
            // 批量预载订单号/状态（旧结构 store_order_order_id/store_order_status 对齐）
            $orderIds = array_unique(array_column($list, 'order_center_id'));
            $orderMap = Db::name('order_center')->whereIn('id', $orderIds)->column('order_no,status_code', 'id');
            foreach ($list as &$item) {
                $item = $this->tidyKefuListItem($item, $orderMap);
            }
            unset($item);
        }

        // 七组状态计数（对齐旧 tab 结构，语义映射到新状态机）
        $base = Db::name('after_sale')->where('is_del', 0);
        $num = [
            0 => ['name' => '全部', 'num' => (clone $base)->count()],
            1 => ['name' => '仅退款', 'num' => (clone $base)->where('type', 'refund_only')->whereIn('status', ['pending_audit', 'audit_passed'])->count()],
            2 => ['name' => '退货退款', 'num' => (clone $base)->where('type', 'return_refund')->whereIn('status', ['pending_audit', 'audit_passed'])->count()],
            3 => ['name' => '拒绝退款', 'num' => (clone $base)->where('status', 'audit_rejected')->count()],
            4 => ['name' => '商品待退货', 'num' => (clone $base)->where('status', 'pending_return')->count()],
            5 => ['name' => '退货待收货', 'num' => (clone $base)->where('status', 'receiving')->count()],
            6 => ['name' => '已退款', 'num' => (clone $base)->whereIn('status', ['refunded', 'completed'])->count()],
        ];
        return ['list' => $list, 'count' => $count, 'num' => $num];
    }

    /**
     * 整理客服/PC 端售后列表项（对齐旧退款列表关键字段契约）
     * @param array $item 售后单行数据
     * @param array $orderMap 订单中台映射 id => [order_no, status_code]
     * @return array
     */
    protected function tidyKefuListItem(array $item, array $orderMap): array
    {
        $status = $item['status'] ?? '';
        // 旧数字 refund_status：1=处理中 2=已退款 3=已拒绝
        if (in_array($status, ['refunded', 'completed'])) {
            $refundStatus = 2;
        } elseif ($status === 'audit_rejected' || $status === 'canceled') {
            $refundStatus = 3;
        } else {
            $refundStatus = 1;
        }
        $item['refund_status'] = $refundStatus;
        $item['refund_status_text'] = self::STATUS_TEXT_MAP[$status] ?? $status;
        $item['refund_type_text'] = self::TYPE_TEXT_MAP[$item['type'] ?? ''] ?? ($item['type'] ?? '');

        // 旧 _status 结构：-1=申请中 -2=已退款 -3=已拒绝
        $typeMap = [1 => [-1, '申请退款中'], 2 => [-2, '已退款'], 3 => [-3, '已拒绝退款']];
        [$type, $title] = $typeMap[$refundStatus] ?? [-1, '申请退款中'];
        $item['_status'] = ['_type' => $type, '_title' => $title];

        // 商品快照 → 旧 cartInfo 结构
        $snapshot = $item['product_snapshot'] ?? [];
        $snapshot = is_array($snapshot) ? $snapshot : (json_decode((string)$snapshot, true) ?: []);
        $items = $this->tidyOutItems($snapshot);
        $item['cartInfo'] = $items;
        $item['cart_info'] = $items;
        $item['_info'] = [];
        foreach ($items as $cart) {
            $item['_info'][] = ['cart_info' => $cart];
        }

        // 旧列表兼容键
        $item['order_id'] = $item['after_sale_no'] ?? '';
        $item['paid'] = 1;
        $item['total_num'] = (int)($item['refund_num'] ?? 0);
        $item['pay_price'] = $item['refund_price'] ?? '0.00';
        $item['refunded_price'] = $item['actual_refund_price'] ?? '0.00';
        $item['add_time'] = $item['_add_time'] = !empty($item['add_time']) ? date('Y-m-d H:i:s', (int)$item['add_time']) : '';
        $orderInfo = $orderMap[$item['order_center_id'] ?? 0] ?? [];
        $item['store_order_order_id'] = $orderInfo['order_no'] ?? '';
        $orderStatusMap = [
            'pending_payment' => '待支付', 'processing' => '进行中', 'pending_review' => '待评价',
            'completed' => '已完成', 'canceled' => '已取消', 'after_sale' => '售后中',
            'refunding' => '售后中', 'refunded' => '已退款',
        ];
        $item['store_order_status'] = $orderStatusMap[$orderInfo['status_code'] ?? ''] ?? '';
        return $item;
    }

    /**
     * 客服/PC 端售后单详情（按售后单号）
     * @param string $uni 售后单号
     * @return array
     */
    public function getRefundDetailForKefu(string $uni): array
    {
        $detail = $this->getDetail($uni);
        if (empty($detail)) {
            throw new \RuntimeException('退款单不存在');
        }
        $afterSale = $this->dao->getByNo($uni);
        $detail = array_merge($afterSale, $detail);
        // 旧结构兼容键
        $detail['order_id'] = $detail['after_sale_no'] ?? '';
        $detail['store_order_id'] = (int)($detail['order_center_id'] ?? 0);
        $detail['refund_status_text'] = self::STATUS_TEXT_MAP[$detail['status'] ?? ''] ?? ($detail['status'] ?? '');
        $detail['cartInfo'] = $detail['cart_info'] = $this->tidyOutItems($detail['product_snapshot'] ?? []);
        $detail['refunded_price'] = $detail['actual_refund_price'] ?? '0.00';
        $detail['_add_time'] = !empty($detail['add_time']) ? date('Y-m-d H:i:s', (int)$detail['add_time']) : '';
        // 关联订单基础信息
        $order = Db::name('order_center')->where('id', (int)($detail['order_center_id'] ?? 0))->find();
        $detail['store_order_order_id'] = $order['order_no'] ?? '';
        $detail['order_center_data'] = $order ?: [];
        return $detail;
    }

    // ==================== 外平台（outapi/out-push）售后能力 ====================
    // 承接自旧对外售后服务（已删除）：外平台以售后单号（after_sale_no）为键
    // 管理本站售后单，推送通知由 OutPushJob 异步执行

    /**
     * 按售后单号查询
     * @param string $afterSaleNo 售后单号
     * @return array|null
     */
    public function getByNo(string $afterSaleNo): ?array
    {
        return $this->dao->getByNo($afterSaleNo);
    }

    /**
     * 外平台售后单列表
     * @param array $where 查询条件：after_sale_no 售后单号（模糊）、time 时间范围、refund_status 1进行中/2已完成
     * @param int $page 页码
     * @param int $limit 每页数量
     * @return array{list: array, count: int}
     */
    public function getListForOut(array $where, int $page = 1, int $limit = 10): array
    {
        $query = Db::name('after_sale')->where('is_del', 0);
        if (!empty($where['after_sale_no'])) {
            $query->whereLike('after_sale_no', '%' . $where['after_sale_no'] . '%');
        }
        if (!empty($where['time'])) {
            // 时间范围：[$startTime, $endTime]，均为当天零点或具体时间戳
            [$start, $end] = is_string($where['time']) ? explode('-', $where['time']) : $where['time'];
            $query->whereBetween('add_time', [strtotime($start), strtotime($end) + 86399]);
        }
        if (($where['refund_status'] ?? 0) == 1) {
            // 进行中：未完结且未取消/驳回
            $query->whereNotIn('status', ['completed', 'canceled', 'audit_rejected']);
        } elseif (($where['refund_status'] ?? 0) == 2) {
            // 已完成
            $query->where('status', 'completed');
        }
        $count = (clone $query)->count();
        $list = $query->field('id, after_sale_no, order_no, uid, type, status, refund_price, actual_refund_price, refund_integral, reason, reject_reason, remark, refund_num, add_time, refund_time')
            ->page($page, $limit)->order('id DESC')->select()->toArray();
        foreach ($list as &$item) {
            $item['refund_type_name'] = $this->getOutStatusName($item['status']);
            $item['type_name'] = $this->getOutTypeName($item['type']);
        }
        return compact('list', 'count');
    }

    /**
     * 外平台售后单详情
     * @param string $afterSaleNo 售后单号
     * @return array
     */
    public function getOutDetail(string $afterSaleNo): array
    {
        $info = $this->dao->getByNo($afterSaleNo);
        if (empty($info)) {
            throw new \RuntimeException('售后单不存在');
        }
        $snapshot = $info['product_snapshot'] ?? [];
        if (is_string($snapshot)) {
            $snapshot = json_decode($snapshot, true) ?: [];
        }
        $info['items'] = $this->tidyOutItems($snapshot);
        $info['refund_type_name'] = $this->getOutStatusName($info['status']);
        $info['type_name'] = $this->getOutTypeName($info['type']);
        unset($info['product_snapshot']);
        return $info;
    }

    /**
     * 外平台同意退款（审核通过）
     * @param string $afterSaleNo 售后单号
     * @return void
     */
    public function outAgreeByNo(string $afterSaleNo): void
    {
        $info = $this->dao->getByNo($afterSaleNo);
        if (empty($info)) {
            throw new \RuntimeException('售后单不存在');
        }
        $this->audit((int)$info['id'], 'passed');
    }

    /**
     * 外平台拒绝退款（审核驳回）
     * @param string $afterSaleNo 售后单号
     * @param string $reason 拒绝原因
     * @return void
     */
    public function outRefuseByNo(string $afterSaleNo, string $reason): void
    {
        $info = $this->dao->getByNo($afterSaleNo);
        if (empty($info)) {
            throw new \RuntimeException('售后单不存在');
        }
        $this->audit((int)$info['id'], 'rejected', 0, $reason);
    }

    /**
     * 外平台直接执行退款
     * 待审核单自动过审后走退款引擎（RefundExecutor 执行真实资金回滚）
     * @param string $afterSaleNo 售后单号
     * @param float $amount 退款金额
     * @return void
     */
    public function outRefundByNo(string $afterSaleNo, float $amount): void
    {
        $info = $this->dao->getByNo($afterSaleNo);
        if (empty($info)) {
            throw new \RuntimeException('售后单不存在');
        }
        if ($amount <= 0) {
            throw new \RuntimeException('请输入退款金额');
        }
        if (bccomp((string)$amount, (string)$info['refund_price'], 2) > 0) {
            throw new \RuntimeException('退款金额大于申请金额，请修改退款金额');
        }
        // 待审核状态先自动过审，保持外平台"一键退款"旧语义
        if ($info['status'] === 'pending_audit') {
            $this->audit((int)$info['id'], 'passed', 0, '', '外平台直接退款自动过审');
        }
        $this->refund((int)$info['id'], $amount, ['refund_type' => 'original']);
    }

    /**
     * 售后单创建推送（承接自旧对外售后服务的推送能力）
     * @param int $afterSaleId 售后单ID
     * @param string $pushUrl 推送地址
     * @return bool
     */
    public function outRefundCreatePush(int $afterSaleId, string $pushUrl): bool
    {
        $afterSale = $this->dao->get($afterSaleId);
        if (empty($afterSale)) {
            throw new \RuntimeException('售后单不存在');
        }
        $refundInfo = $this->getOutDetail($afterSale['after_sale_no']);
        // 附带订单定位块：外平台按中台订单号回查
        $refundInfo['order'] = ['id' => (int)$afterSale['order_center_id'], 'order_no' => $afterSale['order_no']];
        return out_push($pushUrl, $refundInfo, '售后单');
    }

    /**
     * 售后单取消推送（承接自已删除的旧对外售后服务）
     * @param int $afterSaleId 售后单ID
     * @param string $pushUrl 推送地址
     * @return bool
     */
    public function outRefundCancelPush(int $afterSaleId, string $pushUrl): bool
    {
        $afterSale = $this->dao->get($afterSaleId);
        if (empty($afterSale)) {
            throw new \RuntimeException('售后单不存在');
        }
        $refundInfo = $this->getOutDetail($afterSale['after_sale_no']);
        $refundInfo['order'] = ['id' => (int)$afterSale['order_center_id'], 'order_no' => $afterSale['order_no']];
        return out_push($pushUrl, $refundInfo, '取消售后单');
    }

    /**
     * 从售后快照构建外平台商品列表（对齐旧契约 items 字段）
     * @param array $snapshot product_snapshot
     * @return array
     */
    protected function tidyOutItems(array $snapshot): array
    {
        $items = $snapshot['items'] ?? [];
        $list = [];
        foreach ($items as $item) {
            $list[] = [
                'store_name' => $item['store_name'] ?? '',
                'suk'        => $item['sku'] ?? '',
                'image'      => $item['image'] ?? '',
                'price'      => sprintf("%.2f", (float)($item['true_price'] ?? 0)),
                'cart_num'   => (int)($item['cart_num'] ?? 0),
            ];
        }
        return $list;
    }

    /**
     * 外平台售后状态中文名
     * @param string $status 状态码
     * @return string
     */
    protected function getOutStatusName(string $status): string
    {
        $map = [
            'pending_audit'  => '申请退款中',
            'audit_passed'   => '审核通过待退款',
            'pending_return' => '待退货',
            'receiving'      => '退货中',
            'refunded'       => '已退款',
            'resending'      => '补发中',
            'repairing'      => '维修中',
            'repair_complete'=> '维修完成',
            'completed'      => '已完成',
            'canceled'       => '已取消',
            'audit_rejected' => '拒绝退款',
        ];
        return $map[$status] ?? $status;
    }

    /**
     * 外平台售后类型中文名
     * @param string $type 售后类型
     * @return string
     */
    protected function getOutTypeName(string $type): string
    {
        $map = [
            'refund_only'   => '仅退款',
            'return_refund' => '退货退款',
            'exchange'      => '换货',
            'resend'        => '补发',
            'repair'        => '维修',
        ];
        return $map[$type] ?? $type;
    }
}
