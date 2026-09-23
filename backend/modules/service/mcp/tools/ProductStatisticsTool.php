<?php
declare(strict_types=1);

namespace modules\service\mcp\tools;

use app\dao\product\CenterProductDao;
use app\dao\product\product\StoreProductLogDao;
use modules\service\mcp\McpException;
use crmeb\interfaces\McpToolInterface;
use modules\service\mcp\ProductReportPeriod;

/** MCP 工具：按商品行为日志统计浏览、加购和成交。 */
class ProductStatisticsTool implements McpToolInterface
{
    /** @var StoreProductLogDao 商品行为日志 DAO */
    private StoreProductLogDao $dao;

    /** @var CenterProductDao 中台商品 DAO */
    private CenterProductDao $productDao;

    /**
     * @param StoreProductLogDao $dao 商品行为日志 DAO
     * @param CenterProductDao $productDao 中台商品 DAO
     */
    public function __construct(StoreProductLogDao $dao, CenterProductDao $productDao)
    {
        $this->dao = $dao;
        $this->productDao = $productDao;
    }

    /** @return string MCP 工具名称 */
    public function name(): string
    {
        return 'crmeb_product_statistics';
    }

    /** @return string 工具描述及统计口径 */
    public function description(): string
    {
        return '统计全站或指定商品的浏览、已登录去重访客、加购/下单/支付件数、商品行支付金额与收藏次数。'
            . '按商品行为日志写入时间统计，默认近 7 天；支付金额是商品快照单价乘支付件数，不等同订单实付或财务净收入。'
            . '旧浏览日志可能包含分类 ID 误写；现有退款日志按整单商品快照写入，无法提供准确的商品退款件数或金额。';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '商品统计';
    }

    /** @return array MCP 入参 JSON Schema */
    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'start_date' => ['type' => 'string', 'format' => 'date', 'description' => '开始日期 YYYY-MM-DD，与 end_date 同时提供；默认近 7 天'],
                'end_date' => ['type' => 'string', 'format' => 'date', 'description' => '结束日期 YYYY-MM-DD，包含结束日；最多 31 天'],
                'product_id' => ['type' => 'integer', 'minimum' => 1, 'description' => '商品 ID；不传统计全站'],
            ],
        ];
    }

    /**
     * @param array $args 日期范围和可选商品 ID
     * @return array 商品行为日志统计结果
     * @throws McpException 参数错误
     */
    public function execute(array $args): array
    {
        $period = ProductReportPeriod::fromArgs($args);
        $where = ['time' => [$period['start_time'], $period['end_time']]];
        $productId = $args['product_id'] ?? null;
        if ($productId !== null) {
            if (!is_int($productId) && (!is_string($productId) || !ctype_digit($productId))) {
                throw new McpException('product_id 必须是正整数');
            }
            $productId = filter_var($productId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($productId === false) {
                throw new McpException('product_id 必须是正整数');
            }
            if (!$this->productDao->search([['id', '=', $productId], ['is_del', '=', 0]])->field('id')->find()) {
                throw new McpException('商品不存在');
            }
            $where['product_id'] = $productId;
        }

        $result = $this->dao->search($where)->where('product_id', '>', 0)->field([
            'COALESCE(SUM(visit_num),0) AS visits',
            "COUNT(DISTINCT CASE WHEN type='visit' AND uid>0 THEN uid END) AS registered_visitors",
            'COALESCE(SUM(cart_num),0) AS cart_items',
            'COALESCE(SUM(order_num),0) AS ordered_items',
            'COALESCE(SUM(pay_num),0) AS paid_items',
            'COALESCE(SUM(pay_price * pay_num),0) AS gross_paid_amount',
            'COALESCE(SUM(collect_num),0) AS collections',
        ])->find();
        $row = $result ? $result->toArray() : [];

        // 数量转为整数，金额保留数据库 DECIMAL 字符串精度。
        $counts = [];
        foreach (['visits', 'registered_visitors', 'cart_items', 'ordered_items', 'paid_items', 'collections'] as $field) {
            $counts[$field] = (int)($row[$field] ?? 0);
        }

        return [
            'start_date' => $period['start_date'],
            'end_date' => $period['end_date'],
            'product_id' => $productId,
            'metrics' => $counts + [
                'gross_paid_amount' => (string)($row['gross_paid_amount'] ?? '0.00'),
            ],
        ];
    }
}
