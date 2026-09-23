<?php
declare(strict_types=1);

namespace crmeb\trade\order\type;

/**
 * 订单中台状态同步契约
 *
 * crmeb 核心层定义此接口，由 app 层 OrderCenterServices 实现，
 * 实现 crmeb → app 的依赖反转，避免核心层反向依赖应用层。
 *
 * 用途：订单类型适配器（BaseOrderTypeAdapter）推进状态后，
 * 通过此接口同步中台 status_code。
 */
interface OrderCenterSyncInterface
{
    /**
     * 同步中台订单状态
     * @param string $orderNo 订单号
     * @param string $statusCode 中台统一状态码
     * @param array $extra 额外更新字段
     * @return bool 是否同步成功
     */
    public function syncStatus(string $orderNo, string $statusCode, array $extra = []): bool;
}
