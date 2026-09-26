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
use modules\product\normal\service\ProductSaveServices;
use modules\product\normal\service\ProductServices;

/**
 * MCP 工具：实物商品编辑（插件贡献）
 *
 * 只更新传入的字段，其余字段保留原值（内部先读取现状合并后再走标准保存服务）。
 * 典型用法：AI 客户端先读取商品资料，改写名称/简介/详情/关键词后调用本工具写回。
 */
class NormalProductUpdateTool implements McpToolInterface
{
    /**
     * 实物商品服务（读取现状）
     * @var ProductServices
     */
    private ProductServices $productServices;

    /**
     * 实物商品保存服务
     * @var ProductSaveServices
     */
    private ProductSaveServices $saveServices;

    /**
     * 构造函数
     * @param ProductServices $productServices 实物商品服务
     * @param ProductSaveServices $saveServices 实物商品保存服务
     */
    public function __construct(ProductServices $productServices, ProductSaveServices $saveServices)
    {
        $this->productServices = $productServices;
        $this->saveServices = $saveServices;
    }

    /**
     * 工具名称
     * @return string
     */
    public function name(): string
    {
        return 'crmeb_product_normal_update';
    }

    /**
     * 工具描述
     * @return string
     */
    public function description(): string
    {
        return '更新已有实物商品的资料，只覆盖传入字段，未传字段保持原值。可更新：store_name（名称）、intro（简介，最长256字）、'
            . 'description（详情富文本HTML）、keyword（关键词，逗号分隔）、unit_name（单位）、cate_id（分类ID，数字或数组，建议先调 '
            . 'crmeb_category_list 查询）、slider_image（轮播图站点地址数组，整组替换，第一张为主图）、is_show（1上架 0下架）、sort（排序）；'
            . '单规格商品还可更新 price/original_price/stock/cost。'
            . '图片说明：slider_image 与 description 内 <img> 均须使用站点图片地址；AI 生成的图片（base64/外部URL）'
            . '请先调用 crmeb_upload_image 上传换取站点地址后再传入，直接传 base64 会被拒绝。'
            . '限制：多规格商品（spec_type=1）不支持本工具修改价格库存，请在管理后台调整SKU；仅支持 product_type=normal 的实物商品。'
            . '建议先用 crmeb_product_normal_read 读取商品现状再按需传入要修改的字段。';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '编辑实物商品';
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
     * 写操作声明：执行前需客户端确认
     * @return string
     */
    public function interaction(): string
    {
        return 'execute';
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
                'id'             => ['type' => 'number', 'description' => '商品ID，必填'],
                'store_name'     => ['type' => 'string', 'description' => '商品名称，可选'],
                'intro'          => ['type' => 'string', 'description' => '商品简介，可选，最长256字'],
                'description'    => ['type' => 'string', 'description' => '商品详情富文本HTML，可选'],
                'keyword'        => ['type' => 'string', 'description' => '关键词，可选，逗号分隔'],
                'unit_name'      => ['type' => 'string', 'description' => '计件单位，可选'],
                'cate_id'        => ['type' => 'number', 'description' => '分类ID，可选，数字或数字数组'],
                'slider_image'   => [
                    'type'        => 'array',
                    'items'       => ['type' => 'string'],
                    'description' => '轮播图站点地址数组，可选，整组替换（未传则保持原轮播图），第一张为主图；每项须为 crmeb_upload_image 返回的站点地址',
                ],
                'price'          => ['type' => 'number', 'description' => '售价，可选，仅单规格商品生效'],
                'original_price' => ['type' => 'number', 'description' => '划线原价，可选，仅单规格商品生效'],
                'stock'          => ['type' => 'number', 'description' => '库存，可选，仅单规格商品生效'],
                'cost'           => ['type' => 'number', 'description' => '成本价，可选，仅单规格商品生效'],
                'is_show'        => ['type' => 'number', 'description' => '上下架：1上架，0下架，可选'],
                'sort'           => ['type' => 'number', 'description' => '排序，可选'],
            ],
            'required'   => ['id'],
        ];
    }

    /**
     * 执行工具
     *
     * @param array $args 参数（id + 可选更新字段）
     * @return array 更新结果与字段对照；校验失败时返回 error 结构
     */
    public function execute(array $args): array
    {
        if (!isset($args['id']) || !is_numeric($args['id']) || (int)$args['id'] <= 0) {
            return ['error' => '参数错误：缺少 id 或格式不正确'];
        }
        $id = (int)$args['id'];
        try {
            $current = $this->productServices->getExtendInfo($id);
            if (!$current) {
                return ['error' => '商品不存在或已删除'];
            }
            if (($current['product_type'] ?? 'normal') !== 'normal') {
                return ['error' => '该商品不是实物商品（product_type=' . $current['product_type'] . '），本工具仅支持实物商品'];
            }

            $data = $this->mergeData($current, $args);
            if ($data === null) {
                return ['error' => '没有传入任何可更新的字段'];
            }
            $this->saveServices->save($id, $data);
        } catch (\Throwable $e) {
            return ['error' => '更新商品失败：' . $e->getMessage()];
        }

        return [
            'id'       => $id,
            'updated'  => array_keys($data['__updated']),
            'notice'   => '商品资料已更新',
        ];
    }

    /**
     * 以商品现状为基底合并更新字段，补齐保存服务所需但不允许经由本工具清空的键
     *
     * 保存服务为全量保存：未设置的键会被默认值覆盖，因此必须从现状回填
     * 赠券、运费、限购、预售等本工具不开放修改的配置，避免误清。
     *
     * @param array $current 商品现状（getExtendInfo 返回结构）
     * @param array $args 更新入参
     * @return array|null 合并后的保存数据；无任何可更新字段时返回 null
     */
    private function mergeData(array $current, array $args): ?array
    {
        $updated = [];
        $data = [];

        // ---- 文本类字段 ----
        foreach (['store_name', 'intro', 'keyword', 'unit_name', 'description'] as $field) {
            if (array_key_exists($field, $args)) {
                $value = trim((string)$args[$field]);
                $updated[$field] = $value;
                $data[$field] = match ($field) {
                    'store_name' => mb_substr($value, 0, 128),
                    'intro'      => mb_substr($value, 0, 256),
                    'keyword'    => mb_substr($value, 0, 255),
                    'unit_name'  => mb_substr($value, 0, 16),
                    default      => $value,
                };
            }
        }
        if (isset($data['store_name']) && $data['store_name'] === '') {
            throw new \RuntimeException('store_name 不能为空');
        }

        // ---- 分类 ----
        if (array_key_exists('cate_id', $args)) {
            $cateId = is_array($args['cate_id']) ? $args['cate_id'] : [$args['cate_id']];
            $cateId = array_values(array_filter(array_map('intval', $cateId), fn($cid) => $cid > 0));
            if ($cateId === []) {
                throw new \RuntimeException('cate_id 格式不正确');
            }
            $data['cate_id'] = $cateId;
            $updated['cate_id'] = $cateId;
        }

        // ---- 轮播图 ----
        if (array_key_exists('slider_image', $args)) {
            $slider = is_array($args['slider_image']) ? $args['slider_image'] : [$args['slider_image']];
            $slider = array_values(array_filter(array_map(fn($item) => trim((string)$item), $slider)));
            if ($slider === []) {
                throw new \RuntimeException('slider_image 不能为空');
            }
            foreach ($slider as $item) {
                if (str_starts_with($item, 'data:')) {
                    throw new \RuntimeException('slider_image 不接受 base64 数据，请先调用 crmeb_upload_image 上传换取站点地址');
                }
            }
            $data['slider_image'] = $slider;
            $updated['slider_image'] = $slider;
        }

        // ---- 状态与排序 ----
        if (array_key_exists('is_show', $args)) {
            $data['is_show'] = (int)$args['is_show'] === 1 ? 1 : 0;
            $updated['is_show'] = $data['is_show'];
        }
        if (array_key_exists('sort', $args)) {
            $data['sort'] = (int)$args['sort'];
            $updated['sort'] = $data['sort'];
        }

        // ---- 价格库存（仅单规格）----
        $specType = (int)($current['spec_type'] ?? 0);
        $priceFields = ['price', 'original_price', 'stock', 'cost'];
        $priceInput = [];
        foreach ($priceFields as $field) {
            if (array_key_exists($field, $args)) {
                $priceInput[$field] = $args[$field];
            }
        }
        if ($priceInput !== [] && $specType === 1) {
            throw new \RuntimeException('多规格商品不支持通过本工具修改价格库存，请在管理后台调整SKU');
        }
        foreach ($priceInput as $field => $value) {
            $data[$field] = $field === 'stock' ? max(0, (int)$value) : max(0.0, (float)$value);
            $updated[$field] = $data[$field];
        }

        if ($updated === []) {
            return null;
        }

        // ---- 回填保存服务的必填与不可见配置（未更新时保持原值）----
        $data += [
            'store_name'       => (string)($current['store_name'] ?? ''),
            'cate_id'          => array_map('intval', (array)($current['cate_id'] ?? [])),
            'slider_image'     => array_values((array)($current['slider_image'] ?? [])),
            'intro'            => (string)($current['intro'] ?? ''),
            'description'      => (string)($current['description'] ?? ''),
            'keyword'          => (string)($current['keyword'] ?? ''),
            'unit_name'        => (string)($current['unit_name'] ?? '件'),
            'spec_type'        => $specType,
            'attrs'            => (array)($current['attrs'] ?? []),
            'items'            => (array)($current['items'] ?? []),
            'cost'             => (float)($current['cost'] ?? 0),
            'ficti'            => (int)($current['ficti'] ?? 0),
            'sort'             => (int)($current['sort'] ?? 0),
            'is_show'          => (int)($current['is_show'] ?? 0),
            'is_sub'           => (int)($current['is_sub'] ?? 0),
            'is_vip'           => (int)($current['is_vip'] ?? 0),
            'vip_product'      => (int)($current['vip_product'] ?? 0),
            'presale'          => (int)($current['presale'] ?? 0),
            'presale_time'     => (array)($current['presale_time'] ?? []),
            'is_limit'         => (int)($current['is_limit'] ?? 0),
            'min_qty'          => (int)($current['min_qty'] ?? 1),
            'limit_type'       => (int)($current['limit_type'] ?? 0),
            'limit_num'        => (int)($current['limit_num'] ?? 0),
            'is_gift'          => (int)($current['is_gift'] ?? 0),
            'gift_price'       => (float)($current['gift_price'] ?? 0),
            'give_integral'    => (float)($current['give_integral'] ?? 0),
            'coupon_ids'       => array_values(array_filter(array_map('intval', explode(',', (string)($current['give_coupon_ids'] ?? ''))))),
            'label_list'       => array_map('intval', (array)($current['label_list'] ?? [])),
            'custom_form'      => (array)($current['custom_form'] ?? []),
            'params_list'      => (array)($current['params_list'] ?? []),
            'protection_list'  => array_map('intval', (array)($current['protection_list'] ?? [])),
            'recommend_list'   => (array)($current['recommend_list'] ?? []),
            'video_link'       => (string)($current['video_link'] ?? ''),
            'weight'           => (float)($current['weight'] ?? 0),
            'volume'           => (float)($current['volume'] ?? 0),
            'delivery_methods' => array_values((array)($current['delivery_methods'] ?? ['express'])),
            'freight_config'   => (array)($current['freight_config'] ?? []),
        ];

        // ---- 单规格改价时同步 SKU，避免主表价格被 SKU 覆盖回旧值 ----
        if ($specType === 0 && $priceInput !== []) {
            $attrs = $data['attrs'];
            if (isset($attrs[0]) && is_array($attrs[0])) {
                foreach (['price', 'original_price', 'stock', 'cost'] as $field) {
                    if (isset($data[$field])) {
                        $attrs[0][$field] = $data[$field];
                    }
                }
                $data['attrs'] = $attrs;
            }
        }

        $data['__updated'] = $updated;
        return $data;
    }
}
