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
 * MCP 工具：订单详情
 *
 * 按订单号查询，仅返回面向 AI 助手的必要字段。
 */
class OrderDetailTool implements McpToolInterface
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
        return 'crmeb_order_detail';
    }

    /**
     * 工具描述
     * @return string
     */
    public function description(): string
    {
        return '按订单号（order_no）查询单笔订单详情，返回金额、支付方式、状态码、退款状态、下单/支付/发货时间等；'
            . '不含收货人姓名、电话与收货地址（订单中台不存这些字段），入参是订单号字符串而非内部ID。';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '订单详情';
    }

    /**
     * 工具分类（管理后台分组展示）
     * @return string
     */
    public function category(): string
    {
        return '订单';
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
                'order_id' => ['type' => 'string', 'description' => '订单号'],
            ],
            'required'   => ['order_id'],
        ];
    }

    /**
     * 执行工具
     *
     * @param array $args 参数（order_id）
     * @return array 订单详情（已过滤敏感字段）
     * @throws McpException 参数错误或订单不存在
     */
    public function execute(array $args): array
    {
        if (empty($args['order_id'])) {
            throw new McpException('参数错误：缺少 order_id');
        }
        $info = $this->dao->search([['order_no', '=', $args['order_id']], ['is_del', '=', 0]])
            ->field('id,order_no,uid,source_type,product_type,title,total_num,total_price,pay_price,pay_type,pay_status,status_code,refund_status,shipping_type,add_time,pay_time,delivery_time')
            ->find();
        if (!$info) {
            throw new McpException('订单不存在');
        }
        $info = is_object($info) ? $info->toArray() : (array)$info;

        // 过滤敏感字段，只返回必要信息（收货人/地址等在 extra_info JSON 中）
        return [
            'id'            => $info['id'],
            'order_no'      => $info['order_no'] ?? '',
            'uid'           => $info['uid'],
            'source_type'   => $info['source_type'] ?? '',
            'product_type'  => $info['product_type'] ?? '',
            'title'         => $info['title'] ?? '',
            'total_num'     => $info['total_num'] ?? 0,
            'total_price'   => $info['total_price'] ?? 0,
            'pay_price'     => $info['pay_price'] ?? 0,
            'pay_type'      => $info['pay_type'] ?? '',
            'pay_status'    => $info['pay_status'] ?? 0,
            'status_code'   => $info['status_code'] ?? '',
            'refund_status' => $info['refund_status'] ?? 0,
            'shipping_type' => $info['shipping_type'] ?? 0,
            'add_time'      => $info['add_time'] ?? 0,
            'pay_time'      => $info['pay_time'] ?? 0,
            'delivery_time' => $info['delivery_time'] ?? 0,
        ];
    }
}
