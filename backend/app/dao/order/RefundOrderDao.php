<?php
declare(strict_types=1);

namespace app\dao\order;

use app\model\order\RefundOrder;
use crmeb\basic\BaseDao;

class RefundOrderDao extends BaseDao
{
    protected function setModel(): string
    {
        return RefundOrder::class;
    }

    public function getByAfterSaleId(int $afterSaleId, bool $lock = false)
    {
        $query = $this->getModel()->where('after_sale_id', $afterSaleId);
        if ($lock) {
            $query->lock(true);
        }
        return $query->find();
    }

    public function getByIdForUpdate(int $id)
    {
        return $this->getModel()->where('id', $id)->lock(true)->find();
    }

    public function sumCompletedByOrderCenterId(int $orderCenterId): string
    {
        return (string)$this->getModel()
            ->where('order_center_id', $orderCenterId)
            ->where('state', RefundOrder::STATE_COMPLETED)
            ->sum('amount');
    }

    /**
     * 已创建的退款意图均占用可退额度，避免并发售后分别通过额度校验。
     */
    public function sumReservedByOrderCenterId(int $orderCenterId): string
    {
        return (string)$this->getModel()
            ->where('order_center_id', $orderCenterId)
            ->sum('amount');
    }

    public function getRecoverable(int $now, int $limit = 100): array
    {
        return $this->getModel()
            ->whereIn('state', [
                RefundOrder::STATE_PENDING,
                RefundOrder::STATE_SUBMITTING,
                RefundOrder::STATE_UNKNOWN,
                RefundOrder::STATE_CHANNEL_PROCESSING,
                RefundOrder::STATE_CHANNEL_SUCCEEDED,
                RefundOrder::STATE_APPLYING,
                RefundOrder::STATE_LOCAL_FAILED,
                RefundOrder::STATE_RETRYABLE_FAILED,
            ])
            ->where(function ($query) use ($now) {
                $query->where('next_retry_time', 0)->whereOr('next_retry_time', '<=', $now);
            })
            ->order('id asc')
            ->limit($limit)
            ->select()
            ->toArray();
    }
}
