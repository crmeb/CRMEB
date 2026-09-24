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

use app\dao\user\UserBrokerageDao;
use app\dao\user\UserExtractDao;
use app\dao\user\UserMoneyDao;
use app\dao\user\UserRechargeDao;
use crmeb\interfaces\McpToolInterface;
use modules\service\mcp\ReportPeriod;

/**
 * MCP 工具：财务总览统计
 *
 * 汇总指定时间段内平台核心资金数据：充值、提现、余额流水、佣金流水。
 */
class FinanceOverviewTool implements McpToolInterface
{
    /**
     * 充值 DAO
     * @var UserRechargeDao
     */
    private UserRechargeDao $rechargeDao;

    /**
     * 提现 DAO
     * @var UserExtractDao
     */
    private UserExtractDao $extractDao;

    /**
     * 余额流水 DAO
     * @var UserMoneyDao
     */
    private UserMoneyDao $moneyDao;

    /**
     * 佣金流水 DAO
     * @var UserBrokerageDao
     */
    private UserBrokerageDao $brokerageDao;

    /**
     * 构造函数
     * @param UserRechargeDao $rechargeDao 充值 DAO
     * @param UserExtractDao $extractDao 提现 DAO
     * @param UserMoneyDao $moneyDao 余额流水 DAO
     * @param UserBrokerageDao $brokerageDao 佣金流水 DAO
     */
    public function __construct(
        UserRechargeDao $rechargeDao,
        UserExtractDao $extractDao,
        UserMoneyDao $moneyDao,
        UserBrokerageDao $brokerageDao
    ) {
        $this->rechargeDao = $rechargeDao;
        $this->extractDao = $extractDao;
        $this->moneyDao = $moneyDao;
        $this->brokerageDao = $brokerageDao;
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
            . '提现（按申请时间统计已提现/审核中/未通过的笔数与金额及成功手续费）；'
            . '余额流水（按记账时间统计有效记录的收入与支出笔数、金额）；'
            . '佣金流水（按记账时间统计有效记录的收入与支出笔数、金额，收入含冻结中佣金）。'
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

        // 提现：按申请时间，分状态统计
        $extractFields = ['COUNT(id) AS cnt', 'COALESCE(SUM(extract_price),0) AS amount', 'COALESCE(SUM(extract_fee),0) AS fee'];
        $extractSuccess = $this->extractDao->search([['status', '=', 1]])
            ->whereBetween('add_time', $range)->field($extractFields)->find();
        $extractSuccess = $extractSuccess ? $extractSuccess->toArray() : [];
        $extractAudit = $this->extractDao->search([['status', '=', 0]])
            ->whereBetween('add_time', $range)->field($extractFields)->find();
        $extractAudit = $extractAudit ? $extractAudit->toArray() : [];
        $extractFail = $this->extractDao->search([['status', '=', -1]])
            ->whereBetween('add_time', $range)->field(['COUNT(id) AS cnt'])->find();
        $extractFail = $extractFail ? $extractFail->toArray() : [];

        // 余额流水：仅有效记录，按记账时间统计收/支
        $flowFields = ['COUNT(id) AS cnt', 'COALESCE(SUM(number),0) AS amount'];
        $moneyIn = $this->moneyDao->search([['pm', '=', 1], ['status', '=', 1]])
            ->whereBetween('add_time', $range)->field($flowFields)->find();
        $moneyIn = $moneyIn ? $moneyIn->toArray() : [];
        $moneyOut = $this->moneyDao->search([['pm', '=', 0], ['status', '=', 1]])
            ->whereBetween('add_time', $range)->field($flowFields)->find();
        $moneyOut = $moneyOut ? $moneyOut->toArray() : [];

        // 佣金流水：仅有效记录，按记账时间统计收/支（收入含冻结中佣金）
        $brokerageIn = $this->brokerageDao->search([['pm', '=', 1], ['status', '=', 1]])
            ->whereBetween('add_time', $range)->field($flowFields)->find();
        $brokerageIn = $brokerageIn ? $brokerageIn->toArray() : [];
        $brokerageOut = $this->brokerageDao->search([['pm', '=', 0], ['status', '=', 1]])
            ->whereBetween('add_time', $range)->field($flowFields)->find();
        $brokerageOut = $brokerageOut ? $brokerageOut->toArray() : [];

        return [
            'start_date' => $period['start_date'],
            'end_date'   => $period['end_date'],
            'metrics'    => [
                'recharge' => [
                    'count'       => (int)($recharge['cnt'] ?? 0),
                    'amount'      => (string)($recharge['amount'] ?? '0.00'),
                    'give_amount' => (string)($recharge['give_amount'] ?? '0.00'),
                ],
                'extract'  => [
                    'success_count'  => (int)($extractSuccess['cnt'] ?? 0),
                    'success_amount' => (string)($extractSuccess['amount'] ?? '0.00'),
                    'success_fee'    => (string)($extractSuccess['fee'] ?? '0.00'),
                    'auditing_count' => (int)($extractAudit['cnt'] ?? 0),
                    'auditing_amount' => (string)($extractAudit['amount'] ?? '0.00'),
                    'fail_count'     => (int)($extractFail['cnt'] ?? 0),
                ],
                'user_money' => [
                    'in_count'  => (int)($moneyIn['cnt'] ?? 0),
                    'in_amount' => (string)($moneyIn['amount'] ?? '0.00'),
                    'out_count' => (int)($moneyOut['cnt'] ?? 0),
                    'out_amount' => (string)($moneyOut['amount'] ?? '0.00'),
                ],
                'user_brokerage' => [
                    'in_count'  => (int)($brokerageIn['cnt'] ?? 0),
                    'in_amount' => (string)($brokerageIn['amount'] ?? '0.00'),
                    'out_count' => (int)($brokerageOut['cnt'] ?? 0),
                    'out_amount' => (string)($brokerageOut['amount'] ?? '0.00'),
                ],
            ],
        ];
    }
}
