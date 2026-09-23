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

use app\dao\product\CenterProductDao;
use app\dao\product\product\StoreCategoryDao;
use app\dao\product\product\StoreProductCateDao;
use modules\service\mcp\McpException;
use crmeb\interfaces\McpToolInterface;

/**
 * MCP 工具：商品列表
 *
 * 支持按分类、关键词、库存范围筛选，仅返回上架商品。
 */
class ProductListTool implements McpToolInterface
{
    /**
     * 中台商品 DAO
     * @var CenterProductDao
     */
    private CenterProductDao $centerProductDao;

    /**
     * 商品分类 DAO
     * @var StoreCategoryDao
     */
    private StoreCategoryDao $storeCategoryDao;

    /**
     * 商品分类关联 DAO
     * @var StoreProductCateDao
     */
    private StoreProductCateDao $storeProductCateDao;

    /**
     * 构造函数
     * @param CenterProductDao    $centerProductDao    中台商品 DAO
     * @param StoreCategoryDao    $storeCategoryDao    商品分类 DAO
     * @param StoreProductCateDao $storeProductCateDao 商品分类关联 DAO
     */
    public function __construct(
        CenterProductDao $centerProductDao,
        StoreCategoryDao $storeCategoryDao,
        StoreProductCateDao $storeProductCateDao
    ) {
        $this->centerProductDao = $centerProductDao;
        $this->storeCategoryDao = $storeCategoryDao;
        $this->storeProductCateDao = $storeProductCateDao;
    }

    /**
     * 工具名称
     * @return string
     */
    public function name(): string
    {
        return 'crmeb_product_list';
    }

    /**
     * 工具描述
     * @return string
     */
    public function description(): string
    {
        return '查询上架商品列表，支持按分类ID、名称关键词、库存区间筛选，返回名称/价格/库存/销量等基础字段，'
            . '每页数量最大 100；仅返回上架且未删除的商品，已下架商品不在结果中，关键词只匹配商品名称。'
            . '分类ID可经 crmeb_category_list 查询；需要商品完整介绍与图片时改用 crmeb_product_detail。';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '商品列表';
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
                'page'      => ['type' => 'number', 'description' => '页码'],
                'limit'     => ['type' => 'number', 'description' => '每页数量'],
                'cate_id'   => ['type' => 'number', 'description' => '分类ID'],
                'keyword'   => ['type' => 'string', 'description' => '搜索关键词'],
                'stock_min' => ['type' => 'number', 'description' => '最小库存'],
                'stock_max' => ['type' => 'number', 'description' => '最大库存'],
            ],
        ];
    }

    /**
     * 执行工具
     *
     * @param array $args 参数（page/limit/cate_id/keyword/stock_min/stock_max）
     * @return array 商品列表和总数
     * @throws McpException 参数错误或分类不存在
     */
    public function execute(array $args): array
    {
        $page = max(1, (int)($args['page'] ?? 1));
        $limit = min(100, max(1, (int)($args['limit'] ?? 10)));

        $where = [['is_show', '=', 1], ['is_del', '=', 0]];

        // 分类筛选：经关联表查出分类下的商品 ID 集合
        if (!empty($args['cate_id'])) {
            $cateId = (int)$args['cate_id'];
            $categoryExists = $this->storeCategoryDao->search([['id', '=', $cateId], ['is_show', '=', 1]])
                ->count();
            if (!$categoryExists) {
                throw new McpException('分类不存在');
            }
            $productIds = $this->storeProductCateDao->search([['cate_id', '=', $cateId]])
                ->column('product_id');
            if (empty($productIds)) {
                return ['list' => [], 'count' => 0];
            }
            $where[] = ['id', 'in', $productIds];
        }

        // 关键词搜索（商品名称）：转义通配符防止注入
        if (!empty($args['keyword'])) {
            $keyword = addcslashes($args['keyword'], '%_');
            $where[] = ['name', 'like', '%' . $keyword . '%'];
        }

        if (isset($args['stock_min'])) {
            $where[] = ['stock', '>=', (int)$args['stock_min']];
        }
        if (isset($args['stock_max'])) {
            $where[] = ['stock', '<=', (int)$args['stock_max']];
        }

        $list = $this->centerProductDao->search($where)
            ->field('id,name,product_type,cate_id,price,stock,image,sales,is_show')
            ->order('id desc')
            ->page($page, $limit)
            ->select()
            ->toArray();

        $count = $this->centerProductDao->search($where)->count();
        return ['list' => $list, 'count' => $count];
    }
}
