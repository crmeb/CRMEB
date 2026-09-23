<?php
declare(strict_types=1);

namespace modules\service\mcp\tools;

use app\dao\activity\coupon\StoreCouponIssueDao;
use app\dao\activity\coupon\StoreCouponUserDao;
use modules\service\mcp\CouponIssueCriteria;
use modules\service\mcp\McpException;
use crmeb\interfaces\McpToolInterface;
use modules\service\mcp\ReportPeriod;

/** MCP 工具：按使用时间统计用户券的已使用状态。 */
class CouponUsageStatisticsTool implements McpToolInterface
{
    /** @var StoreCouponUserDao 用户券 DAO */
    private StoreCouponUserDao $userCouponDao;

    /** @var StoreCouponIssueDao 发行配置 DAO */
    private StoreCouponIssueDao $issueDao;

    /**
     * @param StoreCouponUserDao $userCouponDao 用户券 DAO
     * @param StoreCouponIssueDao $issueDao 发行配置 DAO
     */
    public function __construct(StoreCouponUserDao $userCouponDao, StoreCouponIssueDao $issueDao)
    {
        $this->userCouponDao = $userCouponDao;
        $this->issueDao = $issueDao;
    }

    /** @return string MCP 工具名称 */
    public function name(): string
    {
        return 'crmeb_coupon_usage_statistics';
    }

    /** @return string 工具描述与使用统计口径 */
    public function description(): string
    {
        return '按用户券 use_time 统计全站或指定发行配置在日期范围内已使用的券张数、使用人数与券面额合计，默认近 7 天。'
            . '只计截至目前仍为已使用状态的券；退款后恢复的券不计入，不能当作历史使用事件总数。'
            . '券面额合计不是订单实际抵扣金额或财务成本。';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '用券统计';
    }

    /** @return array MCP 入参 JSON Schema */
    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'issue_id' => ['type' => 'integer', 'minimum' => 1, 'description' => '优惠券发行配置 ID；不传统计全站'],
                'start_date' => ['type' => 'string', 'format' => 'date', 'description' => '开始日期 YYYY-MM-DD，与 end_date 同时提供；默认近 7 天'],
                'end_date' => ['type' => 'string', 'format' => 'date', 'description' => '结束日期 YYYY-MM-DD，包含结束日；最多 31 天'],
            ],
        ];
    }

    /**
     * @param array $args 可选发行配置 ID 与使用日期范围
     * @return array 优惠券使用情况统计
     * @throws McpException 参数错误或发行记录不存在
     */
    public function execute(array $args): array
    {
        $period = ReportPeriod::fromArgs($args, '优惠券使用统计');
        $issueId = CouponIssueCriteria::issueId($args, $this->issueDao);
        $query = $this->userCouponDao->search()->where('cid', '>', 0)
            ->where('status', 1)
            ->whereBetween('use_time', [$period['start_time'], $period['end_time']]);
        if ($issueId !== null) {
            $query->where('cid', $issueId);
        }

        $result = $query->field([
            'COUNT(id) AS used_coupons',
            'COUNT(DISTINCT CASE WHEN uid>0 THEN uid END) AS unique_users',
            'COALESCE(SUM(coupon_price),0) AS face_value_total',
        ])->find();
        $row = $result ? $result->toArray() : [];

        return [
            'issue_id' => $issueId,
            'start_date' => $period['start_date'],
            'end_date' => $period['end_date'],
            'metrics' => [
                'used_coupons' => (int)($row['used_coupons'] ?? 0),
                'unique_users' => (int)($row['unique_users'] ?? 0),
                'face_value_total' => (string)($row['face_value_total'] ?? '0.00'),
            ],
        ];
    }
}
