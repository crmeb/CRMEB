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

use app\dao\order\OrderInvoiceDao;
use crmeb\interfaces\McpToolInterface;
use modules\service\mcp\SensitiveData;

/**
 * MCP 工具：发票记录列表
 *
 * 查询订单开票记录，开票人手机号脱敏后返回。
 */
class InvoiceListTool implements McpToolInterface
{
    /**
     * 订单发票 DAO
     * @var OrderInvoiceDao
     */
    private OrderInvoiceDao $dao;

    /**
     * 构造函数
     * @param OrderInvoiceDao $dao 订单发票 DAO
     */
    public function __construct(OrderInvoiceDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 工具名称
     * @return string
     */
    public function name(): string
    {
        return 'crmeb_invoice_list';
    }

    /**
     * 工具描述
     * @return string
     */
    public function description(): string
    {
        return '查询订单开票记录列表，支持按开票状态、订单 ID、用户 UID 筛选；'
            . 'header_type 抬头类型：1 个人、2 企业；type 发票类型：1 普通发票、2 专用发票；'
            . 'is_pay 是否支付、is_refund 订单是否退款、is_invoice 是否已开票；'
            . 'invoice_number 为开票票号。开票人手机号与注册电话已脱敏。';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '发票记录列表';
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
                'page'       => ['type' => 'number', 'description' => '页码'],
                'limit'      => ['type' => 'number', 'description' => '每页数量'],
                'uid'        => ['type' => 'number', 'description' => '用户 UID'],
                'order_id'   => ['type' => 'number', 'description' => '订单 ID'],
                'is_invoice' => ['type' => 'number', 'description' => '开票状态：1 已开票、0 未开票'],
            ],
        ];
    }

    /**
     * 执行工具
     *
     * @param array $args 参数（page/limit/uid/order_id/is_invoice）
     * @return array 发票记录列表和总数
     */
    public function execute(array $args): array
    {
        $page = max(1, (int)($args['page'] ?? 1));
        $limit = min(100, max(1, (int)($args['limit'] ?? 10)));

        $where = [['is_del', '=', 0]];
        if (!empty($args['uid'])) {
            $where[] = ['uid', '=', (int)$args['uid']];
        }
        if (!empty($args['order_id'])) {
            $where[] = ['order_id', '=', (int)$args['order_id']];
        }
        if (isset($args['is_invoice']) && $args['is_invoice'] !== '') {
            $where[] = ['is_invoice', '=', (int)$args['is_invoice'] == 1 ? 1 : 0];
        }

        $list = $this->dao->search($where)
            ->field('id,uid,order_id,header_type,type,name,duty_number,drawer_phone,email,tell,is_pay,is_refund,is_invoice,invoice_number,invoice_time,add_time')
            ->order('id desc')
            ->page($page, $limit)
            ->select()
            ->toArray();

        foreach ($list as &$item) {
            $item['drawer_phone'] = SensitiveData::phone((string)($item['drawer_phone'] ?? ''));
            $item['tell'] = SensitiveData::phone((string)($item['tell'] ?? ''));
        }
        unset($item);

        $count = $this->dao->search($where)->count();
        return ['list' => $list, 'count' => $count];
    }
}
