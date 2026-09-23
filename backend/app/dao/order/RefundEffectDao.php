<?php
declare(strict_types=1);

namespace app\dao\order;

use app\model\order\RefundEffect;
use crmeb\basic\BaseDao;

/**
 * 退款本地效果与成功事件待办查询。
 */
class RefundEffectDao extends BaseDao
{
    /**
     * 查找已完成退款的待发送成功事件，包括崩溃后过期的发送租约。
     * @param int $now 当前时间
     * @param int $limit 批次数量
     * @return array
     */
    public function getPendingSuccessEvents(int $now, int $limit): array
    {
        return $this->getModel()->alias('effect')
            ->join('refund_order refund', 'refund.id = effect.refund_order_id')
            ->where('refund.state', 'completed')->where('effect.effect_type', 'refund_success_event')
            ->where(function ($query) use ($now) {
                $query->where('effect.status', 'pending')
                    ->whereOr(function ($query) use ($now) {
                        $query->where('effect.status', 'processing')->where('effect.update_time', '<=', $now - 300);
                    });
            })->field('effect.refund_order_id')->order('effect.id asc')->limit(max(1, $limit))->select()->toArray();
    }

    /**
     * 只有本次发送租约的持有者能够完成或释放待办。
     * @param int $id 效果ID
     * @param string $token 租约令牌
     * @param array $data 更新内容
     * @return void
     */
    public function finishSuccessEvent(int $id, string $token, array $data): void
    {
        $this->getModel()->where('id', $id)->where('status', 'processing')
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(payload, '$.delivery_token')) = ?", [$token])->update($data);
    }
    /**
     * 设置效果模型。
     * @return string
     */
    protected function setModel(): string
    {
        return RefundEffect::class;
    }

    /**
     * 按退款及效果业务键查找幂等记录。
     * @param int $refundOrderId 退款单ID
     * @param string $effectType 效果类型
     * @param string $effectKey 业务键
     * @return mixed
     */
    public function getEffect(int $refundOrderId, string $effectType, string $effectKey)
    {
        return $this->getModel()
            ->where('refund_order_id', $refundOrderId)
            ->where('effect_type', $effectType)
            ->where('effect_key', $effectKey)
            ->find();
    }
}
