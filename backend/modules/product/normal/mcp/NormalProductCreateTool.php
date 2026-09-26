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

use app\services\product\product\StoreCategoryServices;
use crmeb\interfaces\McpToolInterface;
use modules\product\normal\service\ProductSaveServices;

/**
 * MCP 工具：实物商品新增（插件贡献）
 *
 * 创建单规格实物商品。典型用法：AI 客户端自行识图生成商品资料后，
 * 将本工具的 slider_image 传用户提供的商品图，完成"一张照片建品"。
 */
class NormalProductCreateTool implements McpToolInterface
{
    /**
     * 实物商品保存服务
     * @var ProductSaveServices
     */
    private ProductSaveServices $saveServices;

    /**
     * 商品分类服务（校验分类有效性）
     * @var StoreCategoryServices
     */
    private StoreCategoryServices $categoryServices;

    /**
     * 构造函数
     * @param ProductSaveServices $saveServices 实物商品保存服务
     * @param StoreCategoryServices $categoryServices 商品分类服务
     */
    public function __construct(ProductSaveServices $saveServices, StoreCategoryServices $categoryServices)
    {
        $this->saveServices = $saveServices;
        $this->categoryServices = $categoryServices;
    }

    /**
     * 工具名称
     * @return string
     */
    public function name(): string
    {
        return 'crmeb_product_normal_create';
    }

    /**
     * 工具描述
     * @return string
     */
    public function description(): string
    {
        return '新增一个单规格实物商品并返回商品ID。必填：store_name（商品名称，建议不超过30个汉字）、'
            . 'cate_id（分类ID，可传数字或数字数组；请先调用 crmeb_category_list 查询真实分类ID，不得编造）、'
            . 'slider_image（轮播图站点地址数组，必填，可多张，第一张将作为主图）。可选：intro（简介，最长256字）、description（详情富文本HTML）、'
            . 'keyword（关键词，逗号分隔）、unit_name（单位，默认"件"）、price（售价）、original_price（原价，划线价）、'
            . 'stock（库存）、cost（成本价）、sort（排序）、is_show（1立即上架 0默认下架，缺省下架）。'
            . '图片说明：slider_image 与 description 内 <img> 均须使用站点图片地址；AI 生成的图片（base64/外部URL）'
            . '请先调用 crmeb_upload_image 上传换取站点地址后再传入，直接传 base64 会被拒绝。'
            . '本工具固定创建单规格商品（价格库存取 price/stock）；多规格商品与运费模板请在管理后台配置。'
            . '与 crmeb_product_normal_update 的区别：本工具用于创建新商品，更新已有商品请用后者。';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '新增实物商品';
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
                'store_name'     => ['type' => 'string', 'description' => '商品名称，必填，建议不超过30个汉字'],
                'cate_id'        => ['type' => 'number', 'description' => '分类ID，必填，数字或数字数组，须为 crmeb_category_list 返回的真实分类'],
                'slider_image'   => [
                    'type'        => 'array',
                    'items'       => ['type' => 'string'],
                    'description' => '轮播图站点地址数组，必填，可传多张，第一张作为主图；每项须为 crmeb_upload_image 返回的站点地址',
                ],
                'intro'          => ['type' => 'string', 'description' => '商品简介，可选，最长256字'],
                'description'    => ['type' => 'string', 'description' => '商品详情富文本HTML，可选；img 标签须使用站点图片地址，AI生成图先调 crmeb_upload_image 换取'],
                'keyword'        => ['type' => 'string', 'description' => '关键词，可选，逗号分隔'],
                'unit_name'      => ['type' => 'string', 'description' => '计件单位，可选，默认"件"'],
                'price'          => ['type' => 'number', 'description' => '售价，可选，默认0'],
                'original_price' => ['type' => 'number', 'description' => '划线原价，可选，默认同售价'],
                'stock'          => ['type' => 'number', 'description' => '库存，可选，默认0'],
                'cost'           => ['type' => 'number', 'description' => '成本价，可选，默认0'],
                'sort'           => ['type' => 'number', 'description' => '排序，可选，默认0'],
                'is_show'        => ['type' => 'number', 'description' => '上下架：1立即上架，0默认下架（缺省下架）'],
            ],
            'required'   => ['store_name', 'cate_id', 'slider_image'],
        ];
    }

    /**
     * 执行工具
     *
     * @param array $args 商品资料
     * @return array ['id' => 商品ID]；校验失败时返回 error 结构
     */
    public function execute(array $args): array
    {
        try {
            $data = $this->buildData($args);
            $error = $this->validateCategory($data['cate_id']);
            if ($error !== '') {
                return ['error' => $error];
            }
            $id = $this->saveServices->save(0, $data);
        } catch (\Throwable $e) {
            return ['error' => '新增商品失败：' . $e->getMessage()];
        }

        return [
            'id'     => $id,
            'notice' => (int)($data['is_show'] ?? 0) === 1
                ? '商品已创建并上架'
                : '商品已创建，当前为下架状态，请在管理后台上架或更新时传 is_show=1',
        ];
    }

    /**
     * 清洗入参为保存服务所需数据
     * @param array $args 原始入参
     * @return array
     */
    private function buildData(array $args): array
    {
        $storeName = trim((string)($args['store_name'] ?? ''));
        if ($storeName === '') {
            throw new \RuntimeException('store_name 不能为空');
        }
        $sliderImage = $this->normalizeImageList($args['slider_image'] ?? '');
        if ($sliderImage === []) {
            throw new \RuntimeException('slider_image 不能为空，请至少提供一张商品图URL');
        }
        $cateId = $this->normalizeCateId($args['cate_id'] ?? 0);

        $price = max(0.0, (float)($args['price'] ?? 0));
        $data = [
            'store_name'     => mb_substr($storeName, 0, 128),
            'cate_id'        => $cateId,
            'slider_image'   => $sliderImage,
            'intro'          => mb_substr(trim((string)($args['intro'] ?? '')), 0, 256),
            'description'    => (string)($args['description'] ?? ''),
            'keyword'        => mb_substr(trim((string)($args['keyword'] ?? '')), 0, 255),
            'unit_name'      => mb_substr(trim((string)($args['unit_name'] ?? '')), 0, 16) ?: '件',
            'price'          => $price,
            'original_price' => max(0.0, (float)($args['original_price'] ?? $price)),
            'stock'          => max(0, (int)($args['stock'] ?? 0)),
            'cost'           => max(0.0, (float)($args['cost'] ?? 0)),
            'sort'           => (int)($args['sort'] ?? 0),
            'is_show'        => (int)($args['is_show'] ?? 0) === 1 ? 1 : 0,
        ];
        return $data;
    }

    /**
     * 归一化分类ID（接受数字或数字数组）
     * @param mixed $cateId 原始入参
     * @return array
     */
    private function normalizeCateId($cateId): array
    {
        $list = is_array($cateId) ? $cateId : [$cateId];
        $list = array_values(array_filter(array_map('intval', $list), fn($id) => $id > 0));
        if ($list === []) {
            throw new \RuntimeException('cate_id 格式不正确，请传分类ID数字或数组');
        }
        return $list;
    }

    /**
     * 归一化轮播图列表（接受字符串或数组）
     * @param mixed $sliderImage 原始入参
     * @return array
     */
    private function normalizeImageList($sliderImage): array
    {
        $list = is_array($sliderImage) ? $sliderImage : [$sliderImage];
        $list = array_values(array_filter(array_map(fn($item) => trim((string)$item), $list)));
        foreach ($list as $item) {
            if (str_starts_with($item, 'data:')) {
                throw new \RuntimeException('slider_image 不接受 base64 数据，请先调用 crmeb_upload_image 上传换取站点地址');
            }
        }
        return $list;
    }

    /**
     * 校验首个（落库生效的）分类真实存在
     * @param array $cateId 分类ID数组
     * @return string 空串表示通过
     */
    private function validateCategory(array $cateId): string
    {
        try {
            $category = $this->categoryServices->get((int)reset($cateId));
        } catch (\Throwable $e) {
            $category = null;
        }
        if (!$category) {
            return '分类ID ' . (int)reset($cateId) . ' 不存在，请先调用 crmeb_category_list 查询真实分类ID';
        }
        return '';
    }
}
