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
use crmeb\interfaces\McpToolInterface;

/**
 * MCP 工具：余额流水列表
 *
 * v8 起用户余额变动记于独立账单表 user_money，历史数据可能仍存于旧账单表。
 */
class UserMoneyListTool implements McpToolInterface
{
    /**
     * 余额流水 DAO
     * @var UserMoneyDao
     */
    private UserMoneyDao $dao;

    /**
     * 构造函数
     * @param UserMoneyDao $dao 余额流水 DAO
     */
    public function __construct(UserMoneyDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 工具名称
     * @return string
     */
    public function name(): string
    {
        return 'crmeb_user_money_list';
    }

    /**
     * 工具描述
     * @return string
     */
    public function description(): string
    {
        return '查询用户余额流水列表（v8 独立余额账单表），支持按用户 UID、变动类型、收/支方向筛选，'
            . '返回变动金额、变动后余额、标题与备注；'
            . 'type 常见值：recharge 充值入账、pay_product 余额支付购买商品、pay_member 余额购买会员、'
            . 'offline_scan 线下收银、pay_product_refund 订单退款到余额、recharge_refund 充值退款、'
            . 'extract 佣金提现到余额、lottery_add/lottery_use 抽奖、system_add/system_sub 系统调整、register_system_add 注册赠送；'
            . 'pm：1 收入、0 支出；status：1 有效、0 待确定、-1 无效。';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '余额流水列表';
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
                'page'  => ['type' => 'number', 'description' => '页码'],
                'limit' => ['type' => 'number', 'description' => '每页数量'],
                'uid'   => ['type' => 'number', 'description' => '用户 UID'],
                'type'  => ['type' => 'string', 'description' => '变动类型，如 recharge/pay_product/system_add'],
                'pm'    => ['type' => 'number', 'description' => '收/支方向：1 收入、0 支出'],
            ],
        ];
    }

    /**
     * 执行工具
     *
     * @param array $args 参数（page/limit/uid/type/pm）
     * @return array 余额流水列表和总数
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

        $list = $this->dao->search($where)
            ->field('id,uid,link_id,type,title,number,balance,pm,mark,status,add_time')
            ->order('id desc')
            ->page($page, $limit)
            ->select()
            ->toArray();

        $count = $this->dao->search($where)->count();
        return ['list' => $list, 'count' => $count];
    }
}
