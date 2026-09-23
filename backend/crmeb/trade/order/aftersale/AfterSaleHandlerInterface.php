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

/**
 * 售后处理器接口
 * 定义售后规则提供 + 售后全流程回调的统一契约
 */
interface AfterSaleHandlerInterface
{
    // ======================== 售后规则 ========================

    /**
     * 获取售后规则
     * @param array $orderCenter 中台订单数据
     * @param array $sourceOrder 源表订单数据
     * @return array 规则数组（allow, reject_reason, available_types, refund_target, max_refund_num, deadline）
     */
    public function getRule(array $orderCenter, array $sourceOrder): array;

    // ======================== 售后生命周期回调 ========================

    /**
     * 提交售后申请后
     * @param string $afterSaleNo 售后单号
     * @param array $data 售后申请数据
     * @return void
     */
    public function afterApply(string $afterSaleNo, array $data): void;

    /**
     * 审核通过/拒绝后
     * @param string $afterSaleNo 售后单号
     * @param array $data 审核数据
     * @return void
     */
    public function afterAudit(string $afterSaleNo, array $data): void;

    /**
     * 用户填写退货物流后
     * @param string $afterSaleNo 售后单号
     * @param array $deliveryData 退货物流数据
     * @return void
     */
    public function afterReturn(string $afterSaleNo, array $deliveryData): void;

    /**
     * 商家确认收货后
     * @param string $afterSaleNo 售后单号
     * @return void
     */
    public function afterReceive(string $afterSaleNo): void;

    /**
     * 创建产品类型专属退款方案。返回值会在退款意图创建时固化，恢复任务不会重新计算。
     * @param array $afterSale
     * @param array $order
     * @param array $items
     * @param array $context
     * @return array
     */
    public function buildRefundPlan(array $afterSale, array $order, array $items, array $context = []): array;

    /**
     * 支付渠道提交前的产品准备动作，例如冻结权益、校验预约或幂等撤销履约。
     * 外部动作必须使用退款号作为对方幂等键，并具备查询能力；否则应在方案中转人工处理。
     */
    public function beforeRefundSubmit(
        RefundEffectContextInterface $effects,
        array $refundOrder,
        array $afterSale,
        array $order,
        array $items
    ): void;

    /**
     * 执行产品类型专属的本地退款效果。
     * 所有副作用必须通过 $effects->once() 登记，禁止在此调用支付渠道。
     * @param RefundEffectContextInterface $effects
     * @param array $refundOrder
     * @param array $afterSale
     * @param array $order
     * @param array $items
     * @return void
     */
    public function applyRefundEffects(
        RefundEffectContextInterface $effects,
        array $refundOrder,
        array $afterSale,
        array $order,
        array $items
    ): void;

    /**
     * 退款执行后
     * @param string $afterSaleNo 售后单号
     * @param float $amount 实际退款金额
     * @param array $context 退款上下文
     * @return void
     */
    public function afterRefund(string $afterSaleNo, float $amount, array $context): void;

    /**
     * 补发商品后
     * @param string $afterSaleNo 售后单号
     * @param array $deliveryData 补发物流数据
     * @return void
     */
    public function afterResend(string $afterSaleNo, array $deliveryData): void;

    /**
     * 维修状态变更
     * @param string $afterSaleNo 售后单号
     * @param string $repairStatus 维修状态
     * @return void
     */
    public function afterRepair(string $afterSaleNo, string $repairStatus): void;

    /**
     * 售后完结
     * @param string $afterSaleNo 售后单号
     * @return void
     */
    public function afterComplete(string $afterSaleNo): void;

    /**
     * 售后取消
     * @param string $afterSaleNo 售后单号
     * @return void
     */
    public function afterCancel(string $afterSaleNo): void;

    // ======================== 表单与操作（数据驱动前端） ========================

    /**
     * 获取售后申请表单配置
     * 按售后类型返回动态字段，产品类型 Handler 可覆写注入特有字段
     * @param string $type 售后类型 refund_only/return_refund/exchange/resend/repair
     * @return array ['fields' => [['key','label','type','required','placeholder','maxlength','max','default'], ...]]
     *               type 取值：text/textarea/price/number/images/picker/goods_select
     */
    public function getFormConfig(string $type): array;

    /**
     * 获取用户可操作按钮
     * 按售后单当前状态返回可操作动作，产品类型 Handler 可覆写
     * @param array $afterSale 售后单数据（含 status/type 等字段）
     * @return array [['key','label','api','method','confirm','form'], ...]
     *               api 为相对路径，method 为请求方式，confirm 为确认提示，form 为子表单标识
     */
    public function getUserActions(array $afterSale): array;
}
