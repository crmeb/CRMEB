<?php
declare(strict_types=1);

namespace modules\service\mcp\tools;

use app\dao\product\product\StoreProductLogDao;
use modules\service\mcp\McpException;
use crmeb\interfaces\McpToolInterface;
use modules\service\mcp\ProductReportPeriod;

/** MCP 工具：按商品行为日志指标获取商品排行。 */
class ProductRankingTool implements McpToolInterface
{
    private const SORT_FIELDS = ['pay', 'price', 'visit', 'user', 'cart', 'orders', 'collect'];

    /** @var StoreProductLogDao 商品行为日志 DAO */
    private StoreProductLogDao $dao;

    /**
     * @param StoreProductLogDao $dao 商品行为日志 DAO
     */
    public function __construct(StoreProductLogDao $dao)
    {
        $this->dao = $dao;
    }

    /** @return string MCP 工具名称 */
    public function name(): string
    {
        return 'crmeb_product_ranking';
    }

    /** @return string 工具描述及排行口径 */
    public function description(): string
    {
        return '按商品行为日志获取商品排行榜，默认按已支付件数排序，也可按商品行支付金额、浏览、日志去重用户、加购、下单或收藏排序。'
            . '金额是商品快照单价乘支付件数，未扣优惠或退款；user 指标按所有日志 UID 去重（匿名 UID=0 视为一人），旧浏览日志可能包含分类 ID 误写。';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '商品排行';
    }

    /** @return array MCP 入参 JSON Schema */
    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'start_date' => ['type' => 'string', 'format' => 'date', 'description' => '开始日期 YYYY-MM-DD，与 end_date 同时提供；默认近 7 天'],
                'end_date' => ['type' => 'string', 'format' => 'date', 'description' => '结束日期 YYYY-MM-DD，包含结束日；最多 31 天'],
                'sort' => ['type' => 'string', 'enum' => self::SORT_FIELDS, 'description' => 'pay=已支付件数（默认），price=商品行支付金额，visit=浏览，user=日志去重用户，cart=加购，orders=下单，collect=收藏'],
                'limit' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 20, 'description' => '返回前 N 名，默认 10，最多 20'],
            ],
        ];
    }

    /**
     * @param array $args 日期范围、排序指标及条数
     * @return array 商品排行列表
     * @throws McpException 参数错误
     */
    public function execute(array $args): array
    {
        $period = ProductReportPeriod::fromArgs($args);
        $sort = $args['sort'] ?? 'pay';
        if (!is_string($sort) || !in_array($sort, self::SORT_FIELDS, true)) {
            throw new McpException('sort 不在可用的商品排行指标中');
        }
        $rawLimit = $args['limit'] ?? 10;
        if (!is_int($rawLimit) && (!is_string($rawLimit) || !ctype_digit($rawLimit))) {
            throw new McpException('limit 必须是 1 到 20 的整数');
        }
        $limit = filter_var($rawLimit, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 20]]);
        if ($limit === false) {
            throw new McpException('limit 必须是 1 到 20 的整数');
        }

        // 直接复用 DAO 的单次聚合，避免排行 Service 对每个商品额外查询复购率。
        $items = $this->dao->getRanking([
            'time' => [$period['start_time'], $period['end_time']],
            'sort' => $sort,
        ]);
        $list = [];
        foreach ($items as $item) {
            $productId = (int)($item['product_id'] ?? 0);
            if ($productId <= 0) {
                continue;
            }
            $list[] = [
                'rank' => count($list) + 1,
                'product_id' => $productId,
                'name' => (string)($item['store_name'] ?? ''),
                'image' => (string)($item['image'] ?? ''),
                'visits' => (int)($item['visit'] ?? 0),
                'logged_users' => (int)($item['user'] ?? 0),
                'cart_items' => (int)($item['cart'] ?? 0),
                'ordered_items' => (int)($item['orders'] ?? 0),
                'paid_items' => (int)($item['pay'] ?? 0),
                'gross_paid_amount' => (string)($item['price'] ?? '0.00'),
                'collections' => (int)($item['collect'] ?? 0),
            ];
            if (count($list) >= $limit) {
                break;
            }
        }

        return [
            'start_date' => $period['start_date'],
            'end_date' => $period['end_date'],
            'sort' => $sort,
            'list' => $list,
        ];
    }
}
