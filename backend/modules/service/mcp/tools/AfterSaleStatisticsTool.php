<?php
declare(strict_types=1);

namespace modules\service\mcp\tools;

use app\dao\order\AfterSaleDao;
use crmeb\interfaces\McpToolInterface;
use modules\service\mcp\ReportPeriod;

/** MCP 工具：售后申请与退款完成统计。 */
class AfterSaleStatisticsTool implements McpToolInterface
{
    /** @var AfterSaleDao 售后 DAO */
    private AfterSaleDao $dao;
    /** @param AfterSaleDao $dao 售后 DAO */
    public function __construct(AfterSaleDao $dao) { $this->dao = $dao; }
    /** @return string 工具名 */
    public function name(): string { return 'crmeb_after_sale_statistics'; }
    /** @return string 统计口径 */
    public function description(): string
    {
        return '按真实售后单申请时间统计申请量与申请金额，按退款完成时间统计仍为 refunded 状态的退款单数和实际退款金额。'
            . '不包含维修/换货/补发金额，不把订单退款状态当作售后申请。默认近7天，最多31天。';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '售后统计';
    }

    /** @return string 工具分类（管理后台分组展示） */
    public function category(): string
    {
        return '售后退款';
    }
    /** @return array 入参 JSON Schema */
    public function inputSchema(): array
    {
        return ['type' => 'object', 'properties' => [
            'start_date' => ['type' => 'string', 'format' => 'date', 'description' => '开始日期 YYYY-MM-DD'],
            'end_date' => ['type' => 'string', 'format' => 'date', 'description' => '结束日期 YYYY-MM-DD，包含结束日'],
        ]];
    }
    /** @param array $args 日期范围 @return array 售后统计结果 */
    public function execute(array $args): array
    {
        $period = ReportPeriod::fromArgs($args, '售后统计');
        $applications = $this->dao->search([['is_del', '=', 0]])
            ->whereBetween('add_time', [$period['start_time'], $period['end_time']])
            ->field(['COUNT(id) AS applications', 'COALESCE(SUM(refund_price),0) AS requested_amount'])->find();
        $refunds = $this->dao->search([['is_del', '=', 0], ['status', '=', 'refunded']])
            ->whereBetween('refund_time', [$period['start_time'], $period['end_time']])
            ->field(['COUNT(id) AS refunded_cases', 'COALESCE(SUM(actual_refund_price),0) AS actual_refunded_amount'])->find();
        $a = $applications ? $applications->toArray() : [];
        $r = $refunds ? $refunds->toArray() : [];
        return ['start_date' => $period['start_date'], 'end_date' => $period['end_date'], 'metrics' => [
            'applications' => (int)($a['applications'] ?? 0),
            'requested_amount' => (string)($a['requested_amount'] ?? '0.00'),
            'refunded_cases' => (int)($r['refunded_cases'] ?? 0),
            'actual_refunded_amount' => (string)($r['actual_refunded_amount'] ?? '0.00'),
        ]];
    }
}
