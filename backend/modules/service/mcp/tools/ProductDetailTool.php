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
use modules\service\mcp\McpException;
use crmeb\interfaces\McpToolInterface;

/**
 * MCP 工具：商品详情
 *
 * 仅返回面向 AI 助手的必要字段，过滤内部敏感数据。
 */
class ProductDetailTool implements McpToolInterface
{
    /**
     * 中台商品 DAO
     * @var CenterProductDao
     */
    private CenterProductDao $dao;

    /**
     * 构造函数
     * @param CenterProductDao $dao 中台商品 DAO
     */
    public function __construct(CenterProductDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 工具名称
     * @return string
     */
    public function name(): string
    {
        return 'crmeb_product_detail';
    }

    /**
     * 工具描述
     * @return string
     */
    public function description(): string
    {
        return '按商品ID查询单件商品详情，返回名称、商品类型、介绍、价格、原价、库存、销量、单位等完整字段；'
            . '含已下架商品（is_show=0 标识上下架状态），已删除商品不可查。'
            . '与 crmeb_product_list 的区别：本工具返回单件完整信息，列表只含基础字段。';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '商品详情';
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
                'id' => ['type' => 'number', 'description' => '商品ID'],
            ],
            'required'   => ['id'],
        ];
    }

    /**
     * 执行工具
     *
     * @param array $args 参数（id）
     * @return array 商品详情（已过滤敏感字段）
     * @throws McpException 参数错误或商品不存在
     */
    public function execute(array $args): array
    {
        if (!isset($args['id']) || !is_numeric($args['id'])) {
            throw new McpException('参数错误：缺少 id 或格式不正确');
        }
        $info = $this->dao->search([['id', '=', (int)$args['id']], ['is_del', '=', 0]])
            ->field('id,name,product_type,intro,cate_id,price,original_price,stock,image,slider_image,sales,unit_name,keyword,is_show')
            ->find();
        if (!$info) {
            throw new McpException('商品不存在');
        }
        $info = is_object($info) ? $info->toArray() : (array)$info;

        // 过滤敏感字段，只返回必要信息
        return [
            'id'           => $info['id'],
            'name'         => $info['name'] ?? '',
            'product_type' => $info['product_type'] ?? 'normal',
            'intro'        => $info['intro'] ?? '',
            'cate_id'      => $info['cate_id'] ?? 0,
            'price'        => $info['price'] ?? 0,
            'original_price' => $info['original_price'] ?? 0,
            'stock'        => $info['stock'] ?? 0,
            'image'        => $info['image'] ?? '',
            'slider_image' => $info['slider_image'] ?? '',
            'sales'        => $info['sales'] ?? 0,
            'unit_name'    => $info['unit_name'] ?? '',
            'keyword'      => $info['keyword'] ?? '',
            'is_show'      => $info['is_show'] ?? 1,
        ];
    }
}
