<?php
declare(strict_types=1);

namespace modules\service\mcp\tools;

use app\dao\activity\coupon\StoreCouponUserDao;
use app\dao\activity\coupon\StoreCouponIssueDao;
use modules\service\mcp\CouponIssueCriteria;
use crmeb\interfaces\McpToolInterface;
use modules\service\mcp\ReportPeriod;

/** MCP 工具：用户券领取与后台发放统计。 */
class CouponClaimStatisticsTool implements McpToolInterface
{
    /** @var StoreCouponUserDao 用户券 DAO */
    private StoreCouponUserDao $coupons;
    /** @var StoreCouponIssueDao 发行 DAO */
    private StoreCouponIssueDao $issues;
    /** @param StoreCouponUserDao $coupons 用户券 DAO @param StoreCouponIssueDao $issues 发行 DAO */
    public function __construct(StoreCouponUserDao $coupons, StoreCouponIssueDao $issues)
    {
        $this->coupons = $coupons;
        $this->issues = $issues;
    }
    /** @return string 工具名 */
    public function name(): string { return 'crmeb_coupon_claim_statistics'; }
    /** @return string 工具描述 */
    public function description(): string
    {
        return '按用户券创建时间统计已发券总数、手动领取张数、后台发放张数和去重领取用户数。'
            . '发行配置列表 crmeb_coupon_list 不等于用户领券记录；持有量请用 crmeb_coupon_holding_statistics。';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '发券统计';
    }

    /** @return string 工具分类（管理后台分组展示） */
    public function category(): string
    {
        return '营销';
    }
    /** @return array 入参 JSON Schema */
    public function inputSchema(): array
    {
        return ['type' => 'object', 'properties' => [
            'issue_id' => ['type' => 'integer', 'minimum' => 1, 'description' => '发行配置 ID；不传统计全站'],
            'start_date' => ['type' => 'string', 'format' => 'date', 'description' => '开始日期 YYYY-MM-DD'],
            'end_date' => ['type' => 'string', 'format' => 'date', 'description' => '结束日期 YYYY-MM-DD，包含结束日'],
        ]];
    }
    /** @param array $args 日期与发行配置 @return array 领取统计结果 */
    public function execute(array $args): array
    {
        $period = ReportPeriod::fromArgs($args, '优惠券领取统计');
        $issueId = CouponIssueCriteria::issueId($args, $this->issues);
        $query = $this->coupons->search()->where('cid', '>', 0)
            ->whereBetween('add_time', [$period['start_time'], $period['end_time']]);
        if ($issueId !== null) { $query->where('cid', $issueId); }
        $result = $query->field([
            'COUNT(id) AS issued_coupons',
            "COALESCE(SUM(CASE WHEN type='get' THEN 1 ELSE 0 END),0) AS self_claimed",
            "COALESCE(SUM(CASE WHEN type='send' THEN 1 ELSE 0 END),0) AS admin_sent",
            'COUNT(DISTINCT CASE WHEN uid>0 THEN uid END) AS unique_users',
        ])->find();
        $row = $result ? $result->toArray() : [];
        return ['issue_id' => $issueId, 'start_date' => $period['start_date'], 'end_date' => $period['end_date'], 'metrics' => [
            'issued_coupons' => (int)($row['issued_coupons'] ?? 0),
            'self_claimed' => (int)($row['self_claimed'] ?? 0),
            'admin_sent' => (int)($row['admin_sent'] ?? 0),
            'unique_users' => (int)($row['unique_users'] ?? 0),
        ]];
    }
}
