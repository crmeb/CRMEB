<?php
declare(strict_types=1);

namespace modules\service\mcp\tools;

use app\dao\product\CenterProductDao;
use app\services\product\CenterProductServices;
use modules\service\mcp\McpException;
use crmeb\interfaces\McpToolInterface;

/** MCP 工具：低库存商品预警与指定实物商品 SKU 库存。 */
class InventoryAlertTool implements McpToolInterface
{
    /** @var CenterProductDao 中台商品 DAO */
    private CenterProductDao $dao;
    /** @var CenterProductServices 商品服务 */
    private CenterProductServices $products;

    /** @param CenterProductDao $dao 中台商品 DAO @param CenterProductServices $products 商品服务 */
    public function __construct(CenterProductDao $dao, CenterProductServices $products)
    {
        $this->dao = $dao;
        $this->products = $products;
    }

    /** @return string 工具名 */
    public function name(): string { return 'crmeb_inventory_alert'; }

    /** @return string 工具描述 */
    public function description(): string
    {
        return '查询在售商品的中台总库存低于阈值的商品（分页），可指定实物商品 ID 查看其已启用 SKU 的库存。'
            . '各产品类型有独立 SKU 表，全站预警以中台汇总库存为准；指定商品的 SKU 结果仅支持实物 normal 类型。';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '库存预警';
    }

    /** @return array 入参 JSON Schema */
    public function inputSchema(): array
    {
        return ['type' => 'object', 'properties' => [
            'threshold' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 10000, 'description' => '库存上限（含），默认10'],
            'page' => ['type' => 'integer', 'description' => '页码，默认1'],
            'limit' => ['type' => 'integer', 'description' => '每页最多50'],
            'product_id' => ['type' => 'integer', 'minimum' => 1, 'description' => '实物商品ID，另返回其已启用 SKU 库存'],
        ]];
    }

    /** @param array $args 筛选条件 @return array 库存预警列表 @throws McpException 参数错误 */
    public function execute(array $args): array
    {
        $threshold = $args['threshold'] ?? 10;
        if (!is_int($threshold) || $threshold < 0 || $threshold > 10000) {
            throw new McpException('threshold 必须是 0 到 10000 的整数');
        }
        $page = max(1, (int)($args['page'] ?? 1));
        $limit = min(50, max(1, (int)($args['limit'] ?? 20)));
        $where = [['is_del', '=', 0], ['is_show', '=', 1], ['stock', '<=', $threshold]];
        $query = $this->dao->search($where);
        $list = $query->field('id,product_type,name,stock,image')
            ->order('stock asc,id asc')->page($page, $limit)->select()->toArray();
        $count = $this->dao->search($where)->count();
        $result = ['threshold' => $threshold, 'list' => $list, 'count' => $count, 'page' => $page, 'limit' => $limit];
        if (isset($args['product_id'])) {
            $productId = filter_var($args['product_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($productId === false) {
                throw new McpException('product_id 必须为正整数');
            }
            $product = $this->dao->search([['id', '=', $productId], ['is_del', '=', 0]])
                ->field('id,product_type,name,stock')->find();
            if (!$product || (string)$product['product_type'] !== 'normal') {
                throw new McpException('仅支持查询现存实物商品的 SKU 库存');
            }
            $skus = $this->products->getNormalProductSkuList((int)$productId);
            $result['product'] = $product->toArray();
            $result['skus'] = array_map(static fn(array $sku): array => [
                'sku_id' => (int)($sku['id'] ?? 0),
                'specification' => (string)($sku['suk'] ?? $sku['sku_name'] ?? ''),
                'stock' => (int)($sku['stock'] ?? 0),
                'low_stock' => (int)($sku['stock'] ?? 0) <= $threshold,
            ], $skus);
        }
        return $result;
    }
}
