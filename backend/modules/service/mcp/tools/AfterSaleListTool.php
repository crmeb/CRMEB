<?php
declare(strict_types=1);

namespace modules\service\mcp\tools;

use app\dao\order\AfterSaleDao;
use crmeb\interfaces\McpToolInterface;
use modules\service\mcp\McpException;

/** MCP 工具：真实售后单列表，不用订单退款状态替代售后记录。 */
class AfterSaleListTool implements McpToolInterface
{
    /** @var AfterSaleDao 售后 DAO */
    private AfterSaleDao $dao;
    /** @param AfterSaleDao $dao 售后 DAO */
    public function __construct(AfterSaleDao $dao) { $this->dao = $dao; }
    /** @return string 工具名 */
    public function name(): string { return 'crmeb_after_sale_list'; }
    /** @return string 工具描述 */
    public function description(): string
    {
        return '查询真实售后申请记录，支持订单号、售后状态筛选；包括仅退款、退货退款、换货、补发、维修，'
            . '与 crmeb_refund_list 按订单退款标记筛选不同。不返回买家理由、备注或退货地址。';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '售后记录查询';
    }

    /** @return string 工具分类（管理后台分组展示） */
    public function category(): string
    {
        return '售后退款';
    }
    /** @return array 入参 JSON Schema */
    public function inputSchema(): array
    {
        return ['type' => 'object', 'properties' => [
            'order_no' => ['type' => 'string', 'description' => '精确订单号'],
            'status' => ['type' => 'string', 'description' => '售后状态码，如 pending_audit/refunded/completed'],
            'page' => ['type' => 'integer', 'description' => '页码，默认1'],
            'limit' => ['type' => 'integer', 'description' => '每页最多50'],
        ]];
    }
    /** @param array $args 筛选条件 @return array 售后列表 @throws McpException 参数错误 */
    public function execute(array $args): array
    {
        $page = max(1, (int)($args['page'] ?? 1));
        $limit = min(50, max(1, (int)($args['limit'] ?? 20)));
        $where = [['is_del', '=', 0]];
        if (!empty($args['order_no'])) {
            $where[] = ['order_no', '=', (string)$args['order_no']];
        }
        if (!empty($args['status'])) {
            if (!preg_match('/^[a-z_]{1,32}$/', (string)$args['status'])) {
                throw new McpException('status 格式错误');
            }
            $where[] = ['status', '=', (string)$args['status']];
        }
        $list = $this->dao->search($where)
            ->field('id,after_sale_no,order_no,uid,type as after_sale_type,status as status_code,refund_price,actual_refund_price,refund_num,add_time,refund_time')
            ->order('id desc')->page($page, $limit)->select()->toArray();
        return ['list' => $list, 'count' => $this->dao->search($where)->count(), 'page' => $page, 'limit' => $limit];
    }
}
