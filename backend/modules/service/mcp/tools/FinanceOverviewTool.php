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

use app\dao\user\UserMoneyDao;
use app\dao\user\UserRechargeDao;
use crmeb\interfaces\McpToolInterface;
use modules\service\mcp\ReportPeriod;

/**
 * MCP 工具：财务总览统计
 *
 * 汇总指定时间段内平台核心资金数据：充值、余额流水（提现/佣金统计已随分销佣金功能下线移除）。
 */
class FinanceOverviewTool implements McpToolInterface
{
    /**
     * 充值 DAO
     * @var UserRechargeDao
     */
    private UserRechargeDao $rechargeDao;

    /**
     * 余额流水 DAO
     * @var UserMoneyDao
     */
    private UserMoneyDao $moneyDao;

    /**
     * 构造函数
     * @param UserRechargeDao $rechargeDao 充值 DAO
     * @param UserMoneyDao $moneyDao 余额流水 DAO
     */
    public function __construct(
        UserRechargeDao $rechargeDao,
        UserMoneyDao $moneyDao
    ) {
        $this->rechargeDao = $rechargeDao;
        $this->moneyDao = $moneyDao;
    }

    /**
     * 工具名称
     * @return string
     */
    public function name(): string
    {
        return 'crmeb_finance_overview';
    }

    /**
     * 工具描述
     * @return string
     */
    public function description(): string
    {
        return '汇总统计指定时间段内平台核心资金数据：'
            . '充值（仅已支付，按支付时间统计笔数、金额、赠送金额）；'
            . '余额流水（按记账时间统计有效记录的收入与支出笔数、金额）。'
            . '不包含积分/经验变动，金额单位为元。默认近7天，最多31天。';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '财务总览';
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
     * @return array 财务总览统计结果
     */
    public function execute(array $args): array
    {
        $period = ReportPeriod::fromArgs($args, '财务总览');
        $range = [$period['start_time'], $period['end_time']];

        // 充值：仅已支付，按支付时间
        $recharge = $this->rechargeDao->search([['paid', '=', 1]])
            ->whereBetween('pay_time', $range)
            ->field(['COUNT(id) AS cnt', 'COALESCE(SUM(price),0) AS amount', 'COALESCE(SUM(give_price),0) AS give_amount'])
            ->find();
        $recharge = $recharge ? $recharge->toArray() : [];

        // 余额流水：仅有效记录，按记账时间统计收/支
        $flowFields = ['COUNT(id) AS cnt', 'COALESCE(SUM(number),0) AS amount'];
        $moneyIn = $this->moneyDao->search([['pm', '=', 1], ['status', '=', 1]])
            ->whereBetween('add_time', $range)->field($flowFields)->find();
        $moneyIn = $moneyIn ? $moneyIn->toArray() : [];
        $moneyOut = $this->moneyDao->search([['pm', '=', 0], ['status', '=', 1]])
            ->whereBetween('add_time', $range)->field($flowFields)->find();
        $moneyOut = $moneyOut ? $moneyOut->toArray() : [];

        return [
            'start_date' => $period['start_date'],
            'end_date'   => $period['end_date'],
            'metrics'    => [
                'recharge' => [
                    'count'       => (int)($recharge['cnt'] ?? 0),
                    'amount'      => (string)($recharge['amount'] ?? '0.00'),
                    'give_amount' => (string)($recharge['give_amount'] ?? '0.00'),
                ],
                'user_money' => [
                    'in_count'  => (int)($moneyIn['cnt'] ?? 0),
                    'in_amount' => (string)($moneyIn['amount'] ?? '0.00'),
                    'out_count' => (int)($moneyOut['cnt'] ?? 0),
                    'out_amount' => (string)($moneyOut['amount'] ?? '0.00'),
                ],
            ],
        ];
    }
}
