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
use modules\service\mcp\McpException;
use crmeb\interfaces\McpToolInterface;

/**
 * MCP 工具：售后订单详情
 *
 * 按订单号查询存在退款状态的订单，仅返回必要字段。
 */
class RefundDetailTool implements McpToolInterface
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
        return 'crmeb_refund_detail';
    }

    /**
     * 工具描述
     * @return string
     */
    public function description(): string
    {
        return '按订单号查询订单中台的退款标记及已退款金额，不等于真实售后单详情；真实售后记录请使用 crmeb_after_sale_list';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '退款标记详情';
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
                'order_id' => ['type' => 'string', 'description' => '售后订单号'],
            ],
            'required'   => ['order_id'],
        ];
    }

    /**
     * 执行工具
     *
     * @param array $args 参数（order_id）
     * @return array 售后订单详情（已过滤敏感字段）
     * @throws McpException 参数错误或售后订单不存在
     */
    public function execute(array $args): array
    {
        if (empty($args['order_id'])) {
            throw new McpException('参数错误：缺少 order_id');
        }
        $info = $this->dao->search([['order_no', '=', $args['order_id']], ['refund_status', '>', 0], ['is_del', '=', 0]])
            ->field('id,order_no,uid,title,total_price,pay_price,refund_status,refund_price,refund_time,add_time')
            ->find();
        if (!$info) {
            throw new McpException('售后订单不存在');
        }
        $info = is_object($info) ? $info->toArray() : (array)$info;

        // 过滤敏感字段，只返回必要信息（售后申请说明在 extra_info JSON 中）
        return [
            'id'            => $info['id'],
            'order_no'      => $info['order_no'] ?? '',
            'uid'           => $info['uid'],
            'title'         => $info['title'] ?? '',
            'total_price'   => $info['total_price'] ?? 0,
            'pay_price'     => $info['pay_price'] ?? 0,
            'refund_status' => $info['refund_status'] ?? 0,
            'refund_price'  => $info['refund_price'] ?? 0,
            'refund_time'   => $info['refund_time'] ?? 0,
            'add_time'      => $info['add_time'] ?? 0,
        ];
    }
}
