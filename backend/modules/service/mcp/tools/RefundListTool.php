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

use app\dao\order\OrderCenterDao;
use crmeb\interfaces\McpToolInterface;

/**
 * MCP 工具：售后订单列表
 *
 * 返回所有存在退款状态的订单。
 */
class RefundListTool implements McpToolInterface
{
    /**
     * 订单中台 DAO
     * @var OrderCenterDao
     */
    private OrderCenterDao $dao;

    /**
     * 构造函数
     * @param OrderCenterDao $dao 订单中台 DAO
     */
    public function __construct(OrderCenterDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 工具名称
     * @return string
     */
    public function name(): string
    {
        return 'crmeb_refund_list';
    }

    /**
     * 工具描述
     * @return string
     */
    public function description(): string
    {
        return '查询订单中台标记为申请退款或已退款的订单，不等于真实售后申请记录；售后单请使用 crmeb_after_sale_list';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '退款订单列表';
    }

    /**
     * 工具分类（管理后台分组展示）
     * @return string
     */
    public function category(): string
    {
        return '售后退款';
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
            ],
        ];
    }

    /**
     * 执行工具
     *
     * @param array $args 参数（page/limit）
     * @return array 售后订单列表和总数
     */
    public function execute(array $args): array
    {
        $page = max(1, (int)($args['page'] ?? 1));
        $limit = min(100, max(1, (int)($args['limit'] ?? 10)));

        $where = [['refund_status', '>', 0], ['is_del', '=', 0]];
        $list = $this->dao->search($where)
            ->field('id,order_no,uid,total_price,pay_price,refund_status,refund_price,refund_time')
            ->order('id desc')
            ->page($page, $limit)
            ->select()
            ->toArray();

        $count = $this->dao->search($where)->count();
        return ['list' => $list, 'count' => $count];
    }
}
