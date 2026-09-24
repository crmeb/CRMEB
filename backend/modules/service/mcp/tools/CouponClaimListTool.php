<?php
declare(strict_types=1);

namespace modules\service\mcp\tools;

use app\dao\activity\coupon\StoreCouponUserDao;
use app\dao\activity\coupon\StoreCouponIssueDao;
use modules\service\mcp\CouponIssueCriteria;
use crmeb\interfaces\McpToolInterface;
use modules\service\mcp\ReportPeriod;

/** MCP 工具：用户券领取与后台发放记录。 */
class CouponClaimListTool implements McpToolInterface
{
    /** @var StoreCouponUserDao 用户券 DAO */
    private StoreCouponUserDao $coupons;
    /** @var StoreCouponIssueDao 券发行 DAO */
    private StoreCouponIssueDao $issues;
    /** @param StoreCouponUserDao $coupons 用户券 DAO @param StoreCouponIssueDao $issues 发行 DAO */
    public function __construct(StoreCouponUserDao $coupons, StoreCouponIssueDao $issues)
    {
        $this->coupons = $coupons;
        $this->issues = $issues;
    }
    /** @return string 工具名 */
    public function name(): string { return 'crmeb_coupon_claim_list'; }
    /** @return string 工具描述 */
    public function description(): string
    {
        return '查询已发放到用户的券记录（非优惠券发行配置）：get 为手动领取，send 为后台发放。'
            . '可按发行配置、用户 ID 与创建日期筛选；返回状态、有效期和使用时间，不返回用户联系信息。';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '用户券记录';
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
            'issue_id' => ['type' => 'integer', 'minimum' => 1, 'description' => '发行配置 ID'],
            'uid' => ['type' => 'integer', 'minimum' => 1, 'description' => '用户 ID'],
            'start_date' => ['type' => 'string', 'format' => 'date', 'description' => '领取开始日期 YYYY-MM-DD'],
            'end_date' => ['type' => 'string', 'format' => 'date', 'description' => '领取结束日期 YYYY-MM-DD'],
            'page' => ['type' => 'integer', 'description' => '页码，默认1'],
            'limit' => ['type' => 'integer', 'description' => '每页最多50'],
        ]];
    }
    /** @param array $args 查询参数 @return array 用户券列表 */
    public function execute(array $args): array
    {
        $period = ReportPeriod::fromArgs($args, '优惠券领取记录');
        $issueId = CouponIssueCriteria::issueId($args, $this->issues);
        $page = max(1, (int)($args['page'] ?? 1));
        $limit = min(50, max(1, (int)($args['limit'] ?? 20)));
        $where = [['cid', '>', 0], ['add_time', 'between', [$period['start_time'], $period['end_time']]]];
        if ($issueId !== null) { $where[] = ['cid', '=', $issueId]; }
        if (isset($args['uid'])) {
            $uid = filter_var($args['uid'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($uid === false) { throw new \modules\service\mcp\McpException('uid 必须为正整数'); }
            $where[] = ['uid', '=', $uid];
        }
        $list = $this->coupons->search($where)
            ->field('id,cid,uid,coupon_title,coupon_price,type as gain_type,status as status_code,is_fail,add_time,start_time,end_time,use_time')
            ->order('id desc')->page($page, $limit)->select()->toArray();
        return ['start_date' => $period['start_date'], 'end_date' => $period['end_date'], 'list' => $list,
            'count' => $this->coupons->search($where)->count(), 'page' => $page, 'limit' => $limit];
    }
}
