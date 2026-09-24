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

namespace modules\service\mcp\tools;

use app\dao\user\UserExtractDao;
use crmeb\interfaces\McpToolInterface;
use modules\service\mcp\ReportPeriod;

/**
 * MCP 工具：提现统计
 *
 * 按提现申请时间统计各状态笔数与金额。
 */
class ExtractStatisticsTool implements McpToolInterface
{
    /**
     * 提现 DAO
     * @var UserExtractDao
     */
    private UserExtractDao $dao;

    /**
     * 构造函数
     * @param UserExtractDao $dao 提现 DAO
     */
    public function __construct(UserExtractDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 工具名称
     * @return string
     */
    public function name(): string
    {
        return 'crmeb_extract_statistics';
    }

    /**
     * 工具描述
     * @return string
     */
    public function description(): string
    {
        return '按提现申请时间（add_time）统计指定时间段内的提现情况：'
            . '已提现（成功）笔数、金额与手续费合计，审核中笔数与金额，未通过笔数。'
            . '不按审核/打款时间统计，金额为申请金额。默认近7天，最多31天。';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '提现统计';
    }

    /**
     * 工具分类（管理后台分组展示）
     * @return string
     */
    public function category(): string
    {
        return '财务';
    }

    /**
     * 入参 Schema
     * @return array
     */
    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'start_date' => ['type' => 'string', 'format' => 'date', 'description' => '开始日期 YYYY-MM-DD'],
                'end_date'   => ['type' => 'string', 'format' => 'date', 'description' => '结束日期 YYYY-MM-DD，包含结束日'],
            ],
        ];
    }

    /**
     * 执行工具
     *
     * @param array $args 参数（start_date/end_date）
     * @return array 提现统计结果
     */
    public function execute(array $args): array
    {
        $period = ReportPeriod::fromArgs($args, '提现统计');
        $range = [$period['start_time'], $period['end_time']];

        $sumFields = ['COUNT(id) AS cnt', 'COALESCE(SUM(extract_price),0) AS amount', 'COALESCE(SUM(extract_fee),0) AS fee'];
        $success = $this->dao->search([['status', '=', 1]])
            ->whereBetween('add_time', $range)->field($sumFields)->find();
        $success = $success ? $success->toArray() : [];
        $auditing = $this->dao->search([['status', '=', 0]])
            ->whereBetween('add_time', $range)->field($sumFields)->find();
        $auditing = $auditing ? $auditing->toArray() : [];
        $fail = $this->dao->search([['status', '=', -1]])
            ->whereBetween('add_time', $range)->field(['COUNT(id) AS cnt'])->find();
        $fail = $fail ? $fail->toArray() : [];

        return [
            'start_date' => $period['start_date'],
            'end_date'   => $period['end_date'],
            'metrics'    => [
                'success_count'   => (int)($success['cnt'] ?? 0),
                'success_amount'  => (string)($success['amount'] ?? '0.00'),
                'success_fee'     => (string)($success['fee'] ?? '0.00'),
                'auditing_count'  => (int)($auditing['cnt'] ?? 0),
                'auditing_amount' => (string)($auditing['amount'] ?? '0.00'),
                'fail_count'      => (int)($fail['cnt'] ?? 0),
            ],
        ];
    }
}
