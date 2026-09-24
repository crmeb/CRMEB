<?php
declare(strict_types=1);

namespace modules\service\mcp\tools;

use app\dao\activity\coupon\StoreCouponIssueDao;
use app\dao\activity\coupon\StoreCouponUserDao;
use modules\service\mcp\CouponIssueCriteria;
use modules\service\mcp\McpException;
use crmeb\interfaces\McpToolInterface;

/** MCP 工具：优惠券用户持有状态统计。 */
class CouponHoldingStatisticsTool implements McpToolInterface
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
        return 'crmeb_coupon_holding_statistics';
    }

    /** @return string 工具描述与持有统计口径 */
    public function description(): string
    {
        return '统计全站或指定发行配置已发到用户的券张数、持有人数和未使用/已使用/标记过期数量。'
            . '当前可用持有量还要求未使用、未失效且在券使用期限内；过期状态可能延迟更新，不能仅凭状态=未使用判断券可用。'
            . '发行配置已结束或删除仍可查询其历史用户券，不返回用户明细。';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '持券统计';
    }

    /** @return string 工具分类（管理后台分组展示） */
    public function category(): string
    {
        return '营销';
    }

    /** @return array MCP 入参 JSON Schema */
    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'issue_id' => ['type' => 'integer', 'minimum' => 1, 'description' => '优惠券发行配置 ID；不传统计全站'],
            ],
        ];
    }

    /**
     * @param array $args 可选发行配置 ID
     * @return array 用户券持有状态快照
     * @throws McpException 参数错误或发行记录不存在
     */
    public function execute(array $args): array
    {
        $issueId = CouponIssueCriteria::issueId($args, $this->issueDao);
        $query = $this->userCouponDao->search()->where('cid', '>', 0);
        if ($issueId !== null) {
            $query->where('cid', $issueId);
        }

        $result = $query->field([
            'COUNT(id) AS issued_total',
            'COUNT(DISTINCT CASE WHEN uid>0 THEN uid END) AS unique_holders',
            'COALESCE(SUM(CASE WHEN status=0 THEN 1 ELSE 0 END),0) AS status_unspent',
            'COALESCE(SUM(CASE WHEN status=1 THEN 1 ELSE 0 END),0) AS status_used',
            'COALESCE(SUM(CASE WHEN status=2 THEN 1 ELSE 0 END),0) AS status_expired',
            'COALESCE(SUM(CASE WHEN status=0 AND is_fail=0 AND start_time<=UNIX_TIMESTAMP() AND end_time>UNIX_TIMESTAMP() THEN 1 ELSE 0 END),0) AS usable_unspent',
            'COUNT(DISTINCT CASE WHEN uid>0 AND status=0 AND is_fail=0 AND start_time<=UNIX_TIMESTAMP() AND end_time>UNIX_TIMESTAMP() THEN uid END) AS usable_holders',
        ])->find();
        $row = $result ? $result->toArray() : [];
        $metrics = [];
        foreach (['issued_total', 'unique_holders', 'status_unspent', 'status_used', 'status_expired', 'usable_unspent', 'usable_holders'] as $field) {
            $metrics[$field] = (int)($row[$field] ?? 0);
        }

        return ['issue_id' => $issueId, 'as_of_time' => time(), 'metrics' => $metrics];
    }
}
