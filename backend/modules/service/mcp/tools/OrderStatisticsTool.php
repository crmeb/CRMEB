<?php
declare(strict_types=1);

namespace modules\service\mcp\tools;

use app\dao\order\OrderCenterDao;
use crmeb\interfaces\McpToolInterface;
use modules\service\mcp\ReportPeriod;

/** MCP 工具：订单中台下单和支付统计。 */
class OrderStatisticsTool implements McpToolInterface
{
    /** @var OrderCenterDao 订单中台 DAO */
    private OrderCenterDao $dao;

    /** @param OrderCenterDao $dao 订单中台 DAO */
    public function __construct(OrderCenterDao $dao) { $this->dao = $dao; }

    /** @return string 工具名 */
    public function name(): string { return 'crmeb_order_statistics'; }

    /** @return string 统计口径 */
    public function description(): string
    {
        return '统计商品订单在日期范围内的下单数（add_time）与支付订单数、支付人数、实付金额、已退金额（pay_time）。'
            . '支付金额为原始订单实付，净额为实付减当前累计退款；退款可能发生在统计期外，不能当作期内退款流水。默认近7天，最多31天。';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '订单统计';
    }

    /** @return array 入参 JSON Schema */
    public function inputSchema(): array
    {
        return ['type' => 'object', 'properties' => [
            'start_date' => ['type' => 'string', 'format' => 'date', 'description' => '开始日期 YYYY-MM-DD'],
            'end_date' => ['type' => 'string', 'format' => 'date', 'description' => '结束日期 YYYY-MM-DD，包含结束日'],
        ]];
    }

    /** @param array $args 日期范围 @return array 订单统计结果 */
    public function execute(array $args): array
    {
        $period = ReportPeriod::fromArgs($args, '订单统计');
        $base = [['order_category', '=', 'goods'], ['is_del', '=', 0]];
        $created = $this->dao->search($base)
            ->whereBetween('add_time', [$period['start_time'], $period['end_time']])->count();
        $paid = $this->dao->search($base)->where('pay_status', '>', 0)
            ->whereBetween('pay_time', [$period['start_time'], $period['end_time']])
            ->field(['COUNT(id) AS paid_orders', 'COUNT(DISTINCT uid) AS paying_users',
                'COALESCE(SUM(pay_price),0) AS gross_paid_amount',
                'COALESCE(SUM(refund_price),0) AS refunded_to_date',
                'COALESCE(SUM(pay_price-refund_price),0) AS net_paid_to_date'])->find();
        $row = $paid ? $paid->toArray() : [];
        return ['start_date' => $period['start_date'], 'end_date' => $period['end_date'], 'metrics' => [
            'created_orders' => (int)$created,
            'paid_orders' => (int)($row['paid_orders'] ?? 0),
            'paying_users' => (int)($row['paying_users'] ?? 0),
            'gross_paid_amount' => (string)($row['gross_paid_amount'] ?? '0.00'),
            'refunded_to_date' => (string)($row['refunded_to_date'] ?? '0.00'),
            'net_paid_to_date' => (string)($row['net_paid_to_date'] ?? '0.00'),
        ]];
    }
}
