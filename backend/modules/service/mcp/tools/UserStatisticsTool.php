<?php
declare(strict_types=1);

namespace modules\service\mcp\tools;

use app\dao\user\UserDao;
use app\dao\order\OrderCenterDao;
use crmeb\interfaces\McpToolInterface;
use modules\service\mcp\ReportPeriod;

/** MCP 工具：用户注册与商品订单支付人数。 */
class UserStatisticsTool implements McpToolInterface
{
    /** @var UserDao 用户 DAO */
    private UserDao $users;
    /** @var OrderCenterDao 订单中台 DAO */
    private OrderCenterDao $orders;

    /** @param UserDao $users 用户 DAO @param OrderCenterDao $orders 订单 DAO */
    public function __construct(UserDao $users, OrderCenterDao $orders)
    {
        $this->users = $users;
        $this->orders = $orders;
    }

    /** @return string 工具名 */
    public function name(): string { return 'crmeb_user_statistics'; }

    /** @return string 统计口径 */
    public function description(): string
    {
        return '统计注册时间在区间内的新用户数，以及商品订单支付时间在区间内的去重购买人数和支付订单数。'
            . '购买人数不要求同期注册；不包含充值/会员/积分订单。默认近7天，最多31天。';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '用户统计';
    }

    /** @return array 入参 JSON Schema */
    public function inputSchema(): array
    {
        return ['type' => 'object', 'properties' => [
            'start_date' => ['type' => 'string', 'format' => 'date', 'description' => '开始日期 YYYY-MM-DD'],
            'end_date' => ['type' => 'string', 'format' => 'date', 'description' => '结束日期 YYYY-MM-DD，包含结束日'],
        ]];
    }

    /** @param array $args 日期范围 @return array 用户统计结果 */
    public function execute(array $args): array
    {
        $period = ReportPeriod::fromArgs($args, '用户统计');
        $registered = $this->users->search()
            ->whereBetween('add_time', [$period['start_time'], $period['end_time']])->count();
        $result = $this->orders->search([['is_del', '=', 0], ['order_category', '=', 'goods']])
            ->where('pay_status', '>', 0)->whereBetween('pay_time', [$period['start_time'], $period['end_time']])
            ->field(['COUNT(DISTINCT uid) AS paying_users', 'COUNT(id) AS paid_orders'])->find();
        $row = $result ? $result->toArray() : [];
        return ['start_date' => $period['start_date'], 'end_date' => $period['end_date'], 'metrics' => [
            'registered_users' => (int)$registered,
            'paying_users' => (int)($row['paying_users'] ?? 0),
            'paid_orders' => (int)($row['paid_orders'] ?? 0),
        ]];
    }
}
