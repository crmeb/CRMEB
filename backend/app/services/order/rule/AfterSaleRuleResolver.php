<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace app\services\order\rule;

use crmeb\trade\order\aftersale\AfterSaleRuleRegistry;

/**
 * 售后规则解析器
 * 按二维维度（产品类型 × 活动类型）分别取规则并合并
 *
 * 合并策略：
 * 1. 一票否决：任一维度拒绝 → 整体拒绝
 * 2. 可用类型 = 各维度 available_types 的交集（空维度不参与交集）
 * 3. 截止时间取最晚、最大退款数量取最小
 *
 * 未注册 product_type 规则的订单默认拒绝售后——刻意固化：
 * 积分单（integral_order）/会员单/充值单不支持统一售后流程（refundIntegral 能力已随旧服务下线），
 * 避免"无规则 = 全放行"的安全漏洞。
 * 退款标的物由 AfterSaleServices 按订单实际支付维度自算（refund_targets），不经本解析器。
 */
class AfterSaleRuleResolver
{
    /**
     * 解析售后规则（二维合并）
     * @param array $orderCenter 中台订单数据（需含 product_type/activity_type）
     * @param array $sourceOrder 源表订单数据
     * @return array ['allow'=>bool, 'reject_reason'=>string, 'available_types'=>array, 'max_refund_num'=>int, 'deadline'=>int]
     */
    public function resolve(array $orderCenter, array $sourceOrder): array
    {
        // 两个维度分别取规则（未注册的维度返回默认规则，不参与约束）
        $rules = [
            $this->getDimensionRule('product_type', $orderCenter['product_type'] ?? '', $orderCenter, $sourceOrder),
            $this->getDimensionRule('activity_type', $orderCenter['activity_type'] ?? '', $orderCenter, $sourceOrder),
        ];

        return $this->mergeRules($rules);
    }

    /**
     * 获取单维度规则
     * @param string $dimension 维度标识
     * @param string $typeKey 该维度下的类型标识
     * @param array $orderCenter 中台订单数据
     * @param array $sourceOrder 源表订单数据
     * @return array
     */
    protected function getDimensionRule(string $dimension, string $typeKey, array $orderCenter, array $sourceOrder): array
    {
        if ($typeKey === '') {
            // 维度未生效（如订单无活动类型），不参与约束
            return $this->createDefaultRule();
        }
        $provider = AfterSaleRuleRegistry::getProvider($dimension, $typeKey);
        if ($provider) {
            return $provider->getRule($orderCenter, $sourceOrder);
        }
        // activity_type 是附加约束维度：未注册约束时放行（活动不影响基本售后资格，
        // 基本资格由 product_type 维度决定，否则秒杀/拼团等活动订单将全部无法售后）
        if ($dimension === 'activity_type') {
            return $this->createDefaultRule();
        }
        // product_type 维度生效但未注册规则 → 默认拒绝（安全兜底，积分/会员/充值三类刻意固化不支持）
        return $this->createRejectRule();
    }

    /**
     * 合并二维规则
     * @param array $rules 各维度规则数组
     * @return array
     */
    protected function mergeRules(array $rules): array
    {
        $result = $this->createDefaultRule();

        // 1. 一票否决：任一维度拒绝 → 整体拒绝
        foreach ($rules as $rule) {
            if (empty($rule['allow'])) {
                $result['allow'] = false;
                $result['reject_reason'] = $rule['reject_reason'] ?? '当前订单不支持售后';
                return $result;
            }
        }

        // 2. 可用类型 = 各维度 available_types 的交集（空维度不参与）
        $intersected = null;
        foreach ($rules as $rule) {
            if (!empty($rule['available_types'])) {
                $intersected = $intersected === null
                    ? $rule['available_types']
                    : array_values(array_intersect($intersected, $rule['available_types']));
            }
        }
        $result['available_types'] = $intersected ?? [];

        // 3. 截止时间取最晚（0 表示无限制）
        foreach ($rules as $rule) {
            if (($rule['deadline'] ?? 0) > $result['deadline']) {
                $result['deadline'] = $rule['deadline'];
            }
        }

        // 4. 最大退款数量取最小（0 表示无限制）
        foreach ($rules as $rule) {
            $maxNum = $rule['max_refund_num'] ?? 0;
            if ($maxNum > 0) {
                $result['max_refund_num'] = $result['max_refund_num'] > 0
                    ? min($result['max_refund_num'], $maxNum)
                    : $maxNum;
            }
        }

        return $result;
    }

    /**
     * 创建默认规则（维度未生效时使用，不参与约束）
     * @return array
     */
    protected function createDefaultRule(): array
    {
        return [
            'allow'           => true,
            'reject_reason'   => '',
            'available_types' => [],
            'max_refund_num'  => 0,
            'deadline'        => 0,
        ];
    }

    /**
     * 创建拒绝规则（维度生效但未注册规则时使用，安全兜底）
     * @return array
     */
    protected function createRejectRule(): array
    {
        return [
            'allow'           => false,
            'reject_reason'   => '当前订单不支持售后',
            'available_types' => [],
            'max_refund_num'  => 0,
            'deadline'        => 0,
        ];
    }
}
