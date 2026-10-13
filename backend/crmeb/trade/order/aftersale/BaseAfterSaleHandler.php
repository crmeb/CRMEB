<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace crmeb\trade\order\aftersale;

use crmeb\trade\product\ProductTypeRegistry;

/**
 * 售后处理器抽象基类
 * 为 AfterSaleHandlerInterface 提供默认实现，子类只需覆写核心的售后业务方法
 */
abstract class BaseAfterSaleHandler implements AfterSaleHandlerInterface
{
    public function afterApply(string $afterSaleNo, array $data): void {}
    public function afterAudit(string $afterSaleNo, array $data): void {}
    public function afterReturn(string $afterSaleNo, array $deliveryData): void {}
    public function afterReceive(string $afterSaleNo): void {}
    public function buildRefundPlan(array $afterSale, array $order, array $items, array $context = []): array
    {
        return [
            'channel_mode' => 'auto',
            'restore_inventory' => true,
            'allow_auto_recovery' => true,
        ];
    }
    public function beforeRefundSubmit(
        RefundEffectContextInterface $effects,
        array $refundOrder,
        array $afterSale,
        array $order,
        array $items
    ): void {}

    /**
     * 默认产品效果：按售后明细恢复产品库存。
     * 特殊产品可覆写整个方法，定义卡密、房态、票务或订阅权益等流程。
     */
    public function applyRefundEffects(
        RefundEffectContextInterface $effects,
        array $refundOrder,
        array $afterSale,
        array $order,
        array $items
    ): void {
        $plan = $refundOrder['context']['plan'] ?? [];
        if (($plan['restore_inventory'] ?? true) !== true) {
            return;
        }

        /** @var ProductTypeRegistry $registry */
        $registry = app()->make(ProductTypeRegistry::class);
        $handler = $registry->get((string)($afterSale['product_type'] ?? 'normal'));
        $extraInfo = $order['extra_info'] ?? [];
        if (is_string($extraInfo)) {
            $extraInfo = json_decode($extraInfo, true) ?: [];
        }
        $activityInfo = is_array($extraInfo) ? (array)($extraInfo['activity_info'] ?? []) : [];

        foreach ($items as $item) {
            $num = (int)($item['refund_num'] ?? 0);
            $productId = (int)($item['product_id'] ?? 0);
            if ($num <= 0 || $productId <= 0) {
                continue;
            }
            $effectKey = (string)($item['order_item_key'] ?? $item['cart_id'] ?? $productId);
            $payload = [
                'product_id' => $productId,
                'sku_unique' => (string)($item['sku_unique'] ?? ''),
                'refund_num' => $num,
            ];
            $activityType = (string)($item['activity_type'] ?? $afterSale['activity_type'] ?? '');
            if ($activityType !== '' && $activityType !== 'normal') {
                $this->applyActivityInventoryEffect($effects, $activityType, $effectKey, $payload, $activityInfo);
            } else {
                $effects->once('product_inventory_restore', $effectKey, $payload, function () use ($handler, $payload): void {
                    $restored = $handler->incStock($payload['product_id'], $payload['sku_unique'], $payload['refund_num']);
                    if (!$restored) {
                        throw new \RuntimeException('产品库存恢复失败');
                    }
                });
            }
        }
    }

    /**
     * 兼容现有交易活动库存。新活动类型应在自己的产品售后处理器中覆写 applyRefundEffects()。
     */
    protected function applyActivityInventoryEffect(
        RefundEffectContextInterface $effects,
        string $activityType,
        string $effectKey,
        array $payload,
        array $activityInfo
    ): void {
        $definitions = [
            'combination' => ['id_key' => 'combination_id', 'action' => 'incCombinationStock'],
            'seckill' => ['id_key' => 'seckill_id', 'action' => 'incSeckillStock'],
            'bargain' => ['id_key' => 'bargain_id', 'action' => 'incBargainStock'],
        ];
        if (!isset($definitions[$activityType])) {
            throw new \RuntimeException("活动类型 {$activityType} 未定义退款库存效果，请由产品售后处理器实现");
        }
        $definition = $definitions[$activityType];
        $activityId = (int)($activityInfo[$definition['id_key']] ?? 0);
        if ($activityId <= 0) {
            throw new \RuntimeException("活动类型 {$activityType} 缺少退款所需活动快照");
        }
        $payload['activity_type'] = $activityType;
        $payload['activity_id'] = $activityId;
        $effects->once('activity_inventory_restore', $activityType . ':' . $effectKey, $payload, function () use ($definition, $payload): void {
            $results = event('ActivityDataProxy', [
                'mode' => $payload['activity_type'],
                'action' => $definition['action'],
                'args' => [$payload['refund_num'], $payload['activity_id'], $payload['sku_unique']],
            ]);
            if (!proxy_event_result($results, false)) {
                throw new \RuntimeException('活动库存恢复失败');
            }
        });
    }
    public function afterRefund(string $afterSaleNo, float $amount, array $context): void {}
    public function afterResend(string $afterSaleNo, array $deliveryData): void {}
    public function afterRepair(string $afterSaleNo, string $repairStatus): void {}
    public function afterComplete(string $afterSaleNo): void {}
    public function afterCancel(string $afterSaleNo): void {}

    // ======================== 表单与操作（数据驱动前端，子类可覆写） ========================

    /**
     * 默认售后申请表单配置
     * 资金类售后（refund_only/return_refund）展示退款金额；其余类型不展示
     * 所有类型统一展示"售后理由（配置选择）+ 备注 + 凭证图片"
     * @param string $type 售后类型
     * @return array
     */
    public function getFormConfig(string $type): array
    {
        $fields = [];
        // 资金类售后才展示退款金额输入
        if (in_array($type, ['refund_only', 'return_refund'])) {
            $fields[] = [
                'key'         => 'refund_price',
                'label'       => '申请退款金额',
                'type'        => 'price',
                'required'    => true,
                'placeholder' => '请输入退款金额',
            ];
        }
        // 售后理由（选项来自系统配置 stor_reason，所有类型必填）
        $fields[] = [
            'key'         => 'reason',
            'label'       => '售后理由',
            'type'        => 'select',
            'required'    => true,
            'options'     => $this->getReasonOptions(),
            'placeholder' => '请选择售后理由',
        ];
        // 备注（补充说明，选填）
        $fields[] = [
            'key'         => 'remark',
            'label'       => '备注',
            'type'        => 'textarea',
            'required'    => false,
            'maxlength'   => 200,
            'placeholder' => '选填，补充描述您的问题（200字以内）',
        ];
        // 凭证图片（所有类型可选）
        $fields[] = [
            'key'      => 'evidence_images',
            'label'    => '凭证图片',
            'type'     => 'images',
            'required' => false,
            'max'      => 6,
        ];
        return ['fields' => $fields];
    }

    /**
     * 售后理由选项（系统配置 stor_reason，换行分隔；配置为空时兜底"其它原因"保证表单可用）
     * @return array
     */
    protected function getReasonOptions(): array
    {
        $reason = str_replace("\r\n", "\n", (string)sys_config('stor_reason'));
        $options = array_values(array_filter(array_map('trim', explode("\n", $reason)), fn ($v) => $v !== ''));
        return $options ?: ['其它原因'];
    }

    /**
     * 默认用户可操作按钮
     * 按售后单状态返回可操作动作，子类可覆写以增减按钮
     * @param array $afterSale 售后单数据
     * @return array
     */
    public function getUserActions(array $afterSale): array
    {
        $status = $afterSale['status'] ?? '';
        $actions = [];
        // 待审核/审核通过：用户可取消
        if (in_array($status, ['pending_audit', 'audit_passed'])) {
            $actions[] = [
                'key'     => 'cancel',
                'label'   => '取消售后',
                'api'     => 'after_sale/cancel',
                'method'  => 'post',
                'confirm' => '确定取消售后申请？',
            ];
        }
        // 待退货：用户填写退货物流
        if ($status === 'pending_return') {
            $actions[] = [
                'key'    => 'deliver_return',
                'label'  => '填写退货物流',
                'api'    => 'after_sale/deliver_return',
                'method' => 'post',
                'form'   => 'deliver_return',
            ];
        }
        // 补发中：用户确认收货
        if ($status === 'resending') {
            $actions[] = [
                'key'     => 'confirm_receive',
                'label'   => '确认收货',
                'api'     => 'after_sale/confirm_receive',
                'method'  => 'post',
                'confirm' => '确定已收到商品？',
            ];
        }
        return $actions;
    }
}
