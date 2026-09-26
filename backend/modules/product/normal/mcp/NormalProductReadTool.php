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

namespace modules\product\normal\mcp;

use crmeb\interfaces\McpToolInterface;
use modules\product\normal\service\ProductServices;

/**
 * MCP 工具：实物商品详情读取（插件贡献）
 *
 * 返回实物商品完整资料（中台主表 + 实物扩展 + SKU），供 AI 客户端
 * 在改写商品资料（如按照片重新完善详情）前了解商品现状。
 */
class NormalProductReadTool implements McpToolInterface
{
    /**
     * 实物商品服务
     * @var ProductServices
     */
    private ProductServices $productServices;

    /**
     * 构造函数
     * @param ProductServices $productServices 实物商品服务
     */
    public function __construct(ProductServices $productServices)
    {
        $this->productServices = $productServices;
    }

    /**
     * 工具名称
     * @return string
     */
    public function name(): string
    {
        return 'crmeb_product_normal_read';
    }

    /**
     * 工具描述
     * @return string
     */
    public function description(): string
    {
        return '按商品ID读取实物商品完整资料，返回名称(store_name)、简介(intro)、详情富文本(description)、关键词(keyword)、'
            . '单位(unit_name)、分类ID数组(cate_id)、价格(price/original_price/cost)、库存(stock)、销量、主图与轮播图(image/slider_image)、'
            . '上下架状态(is_show: 1上架 0下架)、规格模式(spec_type: 0单规格 1多规格)、规格头(items)与SKU明细(attrs，含每条规格的'
            . 'detail/price/original_price/stock/cost)、配送方式(delivery_methods)与运费配置(freight_config)。'
            . '与 crmeb_product_detail（仅中台主表基础字段）不同：本工具额外返回实物SKU明细与运费配置，是编辑实物商品前的推荐读取入口；'
            . '仅支持 product_type=normal 的实物商品，已删除商品不可读。description 最长返回5000字符。';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '实物商品详情';
    }

    /**
     * 工具分类（管理后台分组展示）
     * @return string
     */
    public function category(): string
    {
        return '商品';
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
                'id' => ['type' => 'number', 'description' => '商品ID（中台商品 eb_product_center 主键）'],
            ],
            'required'   => ['id'],
        ];
    }

    /**
     * 执行工具
     *
     * @param array $args 参数（id）
     * @return array 商品完整资料；参数或商品异常时返回 error 结构
     */
    public function execute(array $args): array
    {
        if (!isset($args['id']) || !is_numeric($args['id']) || (int)$args['id'] <= 0) {
            return ['error' => '参数错误：缺少 id 或格式不正确'];
        }
        try {
            $info = $this->productServices->getExtendInfo((int)$args['id']);
        } catch (\Throwable $e) {
            return ['error' => '读取商品失败：' . $e->getMessage()];
        }
        if (!$info) {
            return ['error' => '商品不存在或已删除'];
        }
        if (($info['product_type'] ?? 'normal') !== 'normal') {
            return ['error' => '该商品不是实物商品（product_type=' . $info['product_type'] . '），本工具仅支持实物商品'];
        }

        return [
            'id'               => (int)($info['id'] ?? 0),
            'store_name'       => (string)($info['store_name'] ?? ''),
            'product_type'     => (string)($info['product_type'] ?? 'normal'),
            'intro'            => (string)($info['intro'] ?? ''),
            'description'      => mb_substr((string)($info['description'] ?? ''), 0, 5000),
            'keyword'          => (string)($info['keyword'] ?? ''),
            'unit_name'        => (string)($info['unit_name'] ?? ''),
            'cate_id'          => array_map('intval', (array)($info['cate_id'] ?? [])),
            'price'            => (float)($info['price'] ?? 0),
            'original_price'   => (float)($info['original_price'] ?? 0),
            'cost'             => (float)($info['cost'] ?? 0),
            'stock'            => (int)($info['stock'] ?? 0),
            'sales'            => (int)($info['sales'] ?? 0),
            'image'            => (string)($info['image'] ?? ''),
            'slider_image'     => array_values((array)($info['slider_image'] ?? [])),
            'is_show'          => (int)($info['is_show'] ?? 0),
            'spec_type'        => (int)($info['spec_type'] ?? 0),
            'items'            => array_values((array)($info['items'] ?? [])),
            'attrs'            => $this->formatAttrs((array)($info['attrs'] ?? [])),
            'delivery_methods' => array_values((array)($info['delivery_methods'] ?? [])),
            'freight_config'   => (array)($info['freight_config'] ?? []),
        ];
    }

    /**
     * 精简 SKU 明细（只保留 AI 改写资料所需的字段）
     * @param array $attrs SKU 列表
     * @return array
     */
    private function formatAttrs(array $attrs): array
    {
        $result = [];
        foreach ($attrs as $attr) {
            if (!is_array($attr)) {
                continue;
            }
            $result[] = [
                'detail'            => (array)($attr['detail'] ?? []),
                'price'             => (float)($attr['price'] ?? 0),
                'original_price'    => (float)($attr['original_price'] ?? 0),
                'cost'              => (float)($attr['cost'] ?? 0),
                'stock'             => (int)($attr['stock'] ?? 0),
                'pic'               => (string)($attr['pic'] ?? ''),
                'is_default_select' => (int)($attr['is_default_select'] ?? 0),
            ];
        }
        return $result;
    }
}
