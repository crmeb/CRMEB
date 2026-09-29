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

namespace modules\product\virtual\listener;

use modules\product\virtual\dao\VirtualOrderDao;
use think\facade\Log;

/**
 * 订单中心列表扩展监听器
 * 合并网盘订单扩展字段（product_id/status/send_time/complete_time）到列表行，
 * 供后台网盘订单列表渲染；退款状态以中台实体列为准，不在此覆盖
 */
class OrderCenterListExtendListener
{
    /**
     * 处理列表扩展
     * @param array $eventData ['list' => &$list]
     */
    public function handle($eventData): void
    {
        $list = &$eventData['list'];
        if (empty($list)) {
            return;
        }

        $orderNos = [];
        foreach ($list as $item) {
            if (($item['source_type'] ?? '') === 'virtual_order') {
                $orderNos[] = $item['order_no'] ?? '';
            }
        }
        $orderNos = array_filter(array_unique($orderNos));
        if (empty($orderNos)) {
            return;
        }

        try {
            /** @var VirtualOrderDao $dao */
            $dao = app(VirtualOrderDao::class);
            $rows = $dao->getDb()
                ->whereIn('order_no', $orderNos)
                ->field('order_no, product_id, status, send_time, complete_time')
                ->select()
                ->toArray();
        } catch (\Throwable $e) {
            Log::error('[Virtual] OrderCenterListExtend 读取扩展表失败: ' . $e->getMessage());
            return;
        }

        $map = [];
        foreach ($rows as $row) {
            $map[$row['order_no']] = $row;
        }

        foreach ($list as &$item) {
            if (($item['source_type'] ?? '') !== 'virtual_order') {
                continue;
            }
            $ext = $map[$item['order_no'] ?? ''] ?? [];
            $item['product_id']    = (int)($ext['product_id'] ?? 0);
            $item['status']        = (string)($ext['status'] ?? '');
            $item['send_time']     = (int)($ext['send_time'] ?? 0);
            $item['complete_time'] = (int)($ext['complete_time'] ?? 0);
        }
        unset($item);
    }
}
