<?php
declare(strict_types=1);

namespace modules\service\mcp\tools;

use app\dao\order\OrderCenterDao;
use app\services\order\OrderCenterServices;
use modules\service\mcp\McpException;
use crmeb\interfaces\McpToolInterface;

/** MCP 工具：订单物流轨迹，只返回快递公司、运单号及节点。 */
class OrderLogisticsTool implements McpToolInterface
{
    /** @var OrderCenterDao 订单中台 DAO */
    private OrderCenterDao $dao;
    /** @var OrderCenterServices 订单详情服务 */
    private OrderCenterServices $orders;
    /** @param OrderCenterDao $dao 订单 DAO @param OrderCenterServices $orders 订单服务 */
    public function __construct(OrderCenterDao $dao, OrderCenterServices $orders)
    {
        $this->dao = $dao;
        $this->orders = $orders;
    }
    /** @return string 工具名 */
    public function name(): string { return 'crmeb_order_logistics'; }
    /** @return string 工具描述 */
    public function description(): string
    {
        return '按订单号查询快递公司、运单号与物流轨迹；仅支持有运单号的快递配送订单。'
            . '驱动未配置或轨迹查询失败时轨迹为空，不代表尚未发货；不返回收件人地址或手机号。';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '订单物流';
    }

    /** @return string 工具分类（管理后台分组展示） */
    public function category(): string
    {
        return '订单';
    }
    /** @return array 入参 JSON Schema */
    public function inputSchema(): array
    {
        return ['type' => 'object', 'required' => ['order_no'], 'properties' => [
            'order_no' => ['type' => 'string', 'description' => '精确订单号'],
        ]];
    }
    /** @param array $args 订单号 @return array 物流摘要 @throws McpException 订单不存在 */
    public function execute(array $args): array
    {
        $orderNo = trim((string)($args['order_no'] ?? ''));
        if ($orderNo === '') { throw new McpException('order_no 不能为空'); }
        $order = $this->dao->search([['order_no', '=', $orderNo], ['is_del', '=', 0]])
            ->field('id,order_no,shipping_type,delivery_time')->find();
        if (!$order) { throw new McpException('订单不存在'); }
        if ((int)$order['shipping_type'] !== 1) {
            return ['order_no' => $orderNo, 'shipping_type' => (int)$order['shipping_type'], 'message' => '该订单不是快递配送'];
        }
        $detail = $this->orders->getDetail((int)$order['id']);
        $logistics = is_array($detail['extend']['logistics'] ?? null) ? $detail['extend']['logistics'] : [];
        $tracks = [];
        foreach (array_slice((array)($logistics['tracks'] ?? []), 0, 50) as $track) {
            if (!is_array($track)) { continue; }
            $status = mb_substr((string)($track['title'] ?? ''), 0, 150);
            $tracks[] = ['status' => preg_replace('/(?<!\d)1[3-9]\d{9}(?!\d)/', '***', $status),
                'time' => (string)($track['time'] ?? '')];
        }
        return ['order_no' => $orderNo, 'delivery_time' => (int)$order['delivery_time'],
            'delivery_name' => (string)($logistics['delivery_name'] ?? ''),
            'delivery_id' => (string)($logistics['delivery_id'] ?? ''), 'tracks' => $tracks];
    }
}
