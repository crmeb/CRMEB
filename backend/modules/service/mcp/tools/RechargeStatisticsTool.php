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

use app\dao\user\UserRechargeDao;
use crmeb\interfaces\McpToolInterface;
use modules\service\mcp\ReportPeriod;

/**
 * MCP 工具：充值统计
 *
 * 按支付时间统计已支付充值，与后台财务统计口径一致。
 */
class RechargeStatisticsTool implements McpToolInterface
{
    /**
     * 充值 DAO
     * @var UserRechargeDao
     */
    private UserRechargeDao $dao;

    /**
     * 构造函数
     * @param UserRechargeDao $dao 充值 DAO
     */
    public function __construct(UserRechargeDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 工具名称
     * @return string
     */
    public function name(): string
    {
        return 'crmeb_recharge_statistics';
    }

    /**
     * 工具描述
     * @return string
     */
    public function description(): string
    {
        return '按支付时间（pay_time）统计指定时间段内已支付（paid=1）的余额充值：'
            . '充值笔数、充值金额、充值活动赠送金额，以及该时段内充值订单的累计退款金额。'
            . '未支付记录不计入。默认近7天，最多31天。';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '充值统计';
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
     * @return array 充值统计结果
     */
    public function execute(array $args): array
    {
        $period = ReportPeriod::fromArgs($args, '充值统计');
        $range = [$period['start_time'], $period['end_time']];

        $paid = $this->dao->search([['paid', '=', 1]])
            ->whereBetween('pay_time', $range)
            ->field(['COUNT(id) AS cnt', 'COALESCE(SUM(price),0) AS amount', 'COALESCE(SUM(give_price),0) AS give_amount'])
            ->find();
        $paid = $paid ? $paid->toArray() : [];

        $refunded = $this->dao->search([['paid', '=', 1], ['refund_price', '>', 0]])
            ->whereBetween('pay_time', $range)
            ->field(['COUNT(id) AS cnt', 'COALESCE(SUM(refund_price),0) AS amount'])
            ->find();
        $refunded = $refunded ? $refunded->toArray() : [];

        return [
            'start_date' => $period['start_date'],
            'end_date'   => $period['end_date'],
            'metrics'    => [
                'recharge_count' => (int)($paid['cnt'] ?? 0),
                'recharge_amount' => (string)($paid['amount'] ?? '0.00'),
                'give_amount'    => (string)($paid['give_amount'] ?? '0.00'),
                'refund_count'   => (int)($refunded['cnt'] ?? 0),
                'refund_amount'  => (string)($refunded['amount'] ?? '0.00'),
            ],
        ];
    }
}
