<?php
declare(strict_types=1);

namespace modules\service\mcp;

use app\dao\activity\coupon\StoreCouponIssueDao;

/** 优惠券统计工具共用的发行 ID 校验。 */
final class CouponIssueCriteria
{
    /**
     * @param array $args 工具参数
     * @param StoreCouponIssueDao $dao 优惠券发行 DAO
     * @return int|null 未指定时返回 null
     * @throws McpException 发行 ID 无效或不存在
     */
    public static function issueId(array $args, StoreCouponIssueDao $dao): ?int
    {
        $value = $args['issue_id'] ?? null;
        if ($value === null) {
            return null;
        }
        if (!is_int($value) && (!is_string($value) || !ctype_digit($value))) {
            throw new McpException('issue_id 必须是正整数');
        }
        $issueId = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($issueId === false) {
            throw new McpException('issue_id 必须是正整数');
        }
        // 已结束或已删除的发行记录仍可查询历史持有和使用数据。
        if (!$dao->search()->where('id', $issueId)->field('id')->find()) {
            throw new McpException('优惠券发行记录不存在');
        }
        return $issueId;
    }
}
