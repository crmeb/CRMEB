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
 * MCP 工具：订单列表
 *
 * 支持按状态、关键词（订单号/收件人/手机号）筛选。
 */
class OrderListTool implements McpToolInterface
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
        return 'crmeb_order_list';
    }

    /**
     * 工具描述
     * @return string
     */
    public function description(): string
    {
        return '查询订单列表，支持按状态码与关键词筛选，返回订单号、买家UID、金额、支付/退款状态、下单时间等字段；'
            . 'status 常用值：pending_payment 待付款、processing 处理中、pending_review 待审核、completed 已完成、'
            . 'canceled 已取消、after_sale 售后中、refunding 退款中、refunded 已退款。'
            . '关键词只匹配订单号与商品标题，不含收件人信息；返回字段不含收货人与地址，按订单号查详情请用 crmeb_order_detail。';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '订单列表';
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
                'page'    => ['type' => 'number', 'description' => '页码'],
                'limit'   => ['type' => 'number', 'description' => '每页数量'],
                'status'  => ['type' => 'string', 'description' => '订单状态码：pending_payment/processing/pending_review/completed/canceled/after_sale/refunding/refunded'],
                'keyword' => ['type' => 'string', 'description' => '搜索关键词，匹配订单号/商品标题'],
            ],
        ];
    }

    /**
     * 执行工具
     *
     * @param array $args 参数（page/limit/status/keyword）
     * @return array 订单列表和总数
     */
    public function execute(array $args): array
    {
        $page = max(1, (int)($args['page'] ?? 1));
        $limit = min(100, max(1, (int)($args['limit'] ?? 10)));

        $where = [['is_del', '=', 0]];
        if (isset($args['status']) && $args['status'] !== '') {
            $where[] = ['status_code', '=', $args['status']];
        }

        // 关键词搜索（订单号/商品标题）：管道字段写法（order_no|title）会被
        // BaseDao::filterWhere 当作不存在列静默丢弃，必须用闭包显式 OR，不进 search 条件数组
        // 转义通配符防止注入；中台表无收件人字段
        $keyword = '';
        if (!empty($args['keyword'])) {
            $keyword = addcslashes($args['keyword'], '%_');
        }
        $applyKeyword = static function (\think\db\Query $query) use ($keyword): void {
            $query->where(function (\think\db\Query $q) use ($keyword) {
                $q->whereLike('order_no', '%' . $keyword . '%')
                    ->whereOr('title', 'like', '%' . $keyword . '%');
            });
        };

        $listQuery = $this->dao->search($where);
        $countQuery = $this->dao->search($where);
        if ($keyword !== '') {
            $applyKeyword($listQuery);
            $applyKeyword($countQuery);
        }

        $list = $listQuery
            ->field('id,order_no,uid,source_type,total_price,pay_price,pay_status,status_code,refund_status,shipping_type,add_time')
            ->order('id desc')
            ->page($page, $limit)
            ->select()
            ->toArray();

        $count = $countQuery->count();
        return ['list' => $list, 'count' => $count];
    }
}
