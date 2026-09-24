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
use crmeb\interfaces\McpToolInterface;

/**
 * MCP 工具：佣金流水列表
 *
 * v8 起分销佣金变动记于独立账单表 user_brokerage。
 */
class UserBrokerageListTool implements McpToolInterface
{
    /**
     * 佣金流水 DAO
     * @var UserBrokerageDao
     */
    private UserBrokerageDao $dao;

    /**
     * 构造函数
     * @param UserBrokerageDao $dao 佣金流水 DAO
     */
    public function __construct(UserBrokerageDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 工具名称
     * @return string
     */
    public function name(): string
    {
        return 'crmeb_user_brokerage_list';
    }

    /**
     * 工具描述
     * @return string
     */
    public function description(): string
    {
        return '查询用户分销佣金流水列表（v8 独立佣金账单表），支持按用户 UID、类型、收/支方向、状态筛选，'
            . '返回变动金额、变动后佣金、冻结到期时间与备注；'
            . 'type 常见值：self_brokerage 自购订单佣金、one_brokerage/two_brokerage 一/二级推广订单佣金、'
            . 'brokerage_user 推广用户佣金、extract 佣金提现、extract_fail 提现失败退回、extract_money 提现到余额、'
            . 'refund 退款退回佣金、pink_master_brokerage 拼团团长佣金；'
            . 'pm：1 收入、0 支出；status：1 有效、0 待确定、-1 无效，冻结中的佣金以 frozen_time 大于当前时间判定。';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '佣金流水列表';
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
                'page'   => ['type' => 'number', 'description' => '页码'],
                'limit'  => ['type' => 'number', 'description' => '每页数量'],
                'uid'    => ['type' => 'number', 'description' => '用户 UID'],
                'type'   => ['type' => 'string', 'description' => '变动类型，如 one_brokerage/extract/refund'],
                'pm'     => ['type' => 'number', 'description' => '收/支方向：1 收入、0 支出'],
                'status' => ['type' => 'number', 'description' => '状态：1 有效、0 待确定、-1 无效'],
            ],
        ];
    }

    /**
     * 执行工具
     *
     * @param array $args 参数（page/limit/uid/type/pm/status）
     * @return array 佣金流水列表和总数
     */
    public function execute(array $args): array
    {
        $page = max(1, (int)($args['page'] ?? 1));
        $limit = min(100, max(1, (int)($args['limit'] ?? 10)));

        $where = [];
        if (!empty($args['uid'])) {
            $where[] = ['uid', '=', (int)$args['uid']];
        }
        if (isset($args['type']) && $args['type'] !== '') {
            $where[] = ['type', '=', (string)$args['type']];
        }
        if (isset($args['pm']) && $args['pm'] !== '') {
            $where[] = ['pm', '=', (int)$args['pm'] == 1 ? 1 : 0];
        }
        if (isset($args['status']) && $args['status'] !== '') {
            $where[] = ['status', '=', (int)$args['status']];
        }

        $list = $this->dao->search($where)
            ->field('id,uid,link_id,type,title,number,balance,pm,mark,status,frozen_time,add_time')
            ->order('id desc')
            ->page($page, $limit)
            ->select()
            ->toArray();

        $count = $this->dao->search($where)->count();
        return ['list' => $list, 'count' => $count];
    }
}
