<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace modules\product\normal\service;

use crmeb\basic\BaseService;
use app\services\product\CenterProductServices;
use modules\product\normal\dao\NormalProductDao;
use modules\product\normal\dao\NormalProductSkuDao;

/**
 * 实物商品保存服务
 * 直接写入 eb_product 中台主表 + eb_pt_normal_sku 实物SKU表
 *
 * 不再依赖旧的 eb_store_product 表
 */
class ProductSaveServices extends BaseService
{
    /**
     * @var CenterProductServices
     */
    protected $centerProductServices;

    protected function initialize(): void
    {
        $this->centerProductServices = $this->app->make(CenterProductServices::class);
    }

    /**
     * 保存商品（新增/编辑统一入口）
     * @param int $id eb_product.id，0=新增 >0=编辑
     * @param array $data
     * @return int 商品ID
     */
    public function save(int $id, array $data): int
    {
        $data = $this->fillDefaults($data);
        $this->validate($data);
        $data = $this->stripImageUrl($data);
        $data = $this->prepareData($data);

        if ($id > 0) {
            $existing = $this->centerProductServices->get($id, ['id', 'product_type', 'is_del']);
            if (!$existing) {
                throw new \RuntimeException('商品不存在');
            }
            if (is_object($existing)) {
                $existing = $existing->toArray();
            }
            if (($existing['product_type'] ?? 'normal') !== 'normal') {
                throw new \RuntimeException('只能编辑实物商品');
            }
            if ((int)($existing['is_del'] ?? 0) === 1) {
                throw new \RuntimeException('已删除商品不可编辑');
            }
        }

        $centerData = $this->buildCenterProductData('normal', $data);

        $productId = $this->transaction(function () use ($id, $data, $centerData) {
            if ($id > 0) {
                // 编辑不覆盖真实销量：sales 由支付成功累加、退款递减，保存表单不收集该字段，固定 0 更新会把销量清零
                unset($centerData['sales']);
                $this->centerProductServices->update($id, $centerData);
                $productId = $id;
            } else {
                // 中台主表 add_time 列默认 0，新增时必须显式写入（编辑不可覆盖原值）
                $centerData['add_time'] = time();
                $model = $this->centerProductServices->create($centerData);
                $productId = (int) ($model['id'] ?? $model);
            }

            $this->saveSku($productId, $data);
            $this->saveDescription($productId, $data['description'] ?? '');
            $this->saveProductExtend($productId, $data);

            return $productId;
        });

        return $productId;
    }

    /**
     * 保存商品详情到描述表
     * @param int $productId
     * @param string $description
     * @return void
     */
    protected function saveDescription(int $productId, string $description): void
    {
        $descService = app(\app\services\product\product\StoreDescriptionServices::class);
        $descService->saveDescription($productId, $description, 'normal');
    }

    /**
     * 补齐默认字段
     * @param array $data
     * @return array
     */
    protected function fillDefaults(array $data): array
    {
        $defaults = [
            'store_name'      => '',
            'intro'      => '',
            'cate_id'         => [],
            'label_id'        => [],
            'slider_image'    => [],
            'image'           => '',
            'recommend_image' => '',
            'price'           => 0,
            'original_price'  => 0,
            'cost'            => 0,
            'stock'           => 0,
            'ficti'           => 0,
            'give_integral'   => 0,
            'description'     => '',
            'is_show'         => 0,
            'is_sub'          => 0,
            'is_vip'          => 0,
            'vip_product'     => 0,
            'presale'         => 0,
            'presale_time'    => [],
            'is_limit'        => 0,
            'min_qty'         => 1,
            'limit_type'      => 0,
            'limit_num'       => 0,
            'product_type'    => 'normal',
            'delivery_methods'=> ['express'],
            'freight_config'  => [
                'express'      => ['type' => 'template', 'postage' => 0, 'temp_id' => 0],
                'door_service' => ['type' => 'free', 'postage' => 0],
            ],
            'weight'          => 0,
            'volume'          => 0,
            'custom_form'     => [],
            'params_list'     => [],
            'label_list'      => [],
            'protection_list' => [],
            'recommend'       => [],
            'recommend_list'  => [],
            'is_gift'         => 0,
            'gift_price'      => 0,
            'activity'        => ['默认', '秒杀', '砍价', '拼团'],
            'coupon_ids'      => [],
            'type'            => 0,
            'keyword'         => '',
            'sort'            => 0,
            'unit_name'       => '件',
            'spec_type'       => 0,
            'items'           => [],
            'attrs'           => [],
            'video_link'      => '',
            'video_open'      => 0,
            'seletVideo'      => 0,
        ];

        foreach ($defaults as $key => $value) {
            if (!isset($data[$key])) {
                $data[$key] = $value;
            }
        }

        return $data;
    }

    /**
     * 图片路径去站点域名前缀
     * 编辑详情接口回传的 slider_image/推荐图/SKU 规格图可能带 site_url 前缀，入库统一还原为相对路径
     * @param array $data
     * @return array
     */
    protected function stripImageUrl(array $data): array
    {
        $siteUrl = rtrim((string) sys_config('site_url'), '/');
        if ($siteUrl === '') {
            return $data;
        }
        $strip = static function ($path) use ($siteUrl): string {
            $path = str_replace('\\', '/', (string) $path);
            return str_starts_with($path, $siteUrl . '/') ? substr($path, strlen($siteUrl)) : $path;
        };
        if (!empty($data['slider_image']) && is_array($data['slider_image'])) {
            $data['slider_image'] = array_map($strip, $data['slider_image']);
        }
        if (!empty($data['recommend_image']) && is_string($data['recommend_image'])) {
            $data['recommend_image'] = $strip($data['recommend_image']);
        }
        if (!empty($data['attrs'])) {
            foreach ($data['attrs'] as &$attr) {
                if (is_array($attr) && !empty($attr['pic']) && is_string($attr['pic'])) {
                    $attr['pic'] = $strip($attr['pic']);
                }
            }
            unset($attr);
        }
        return $data;
    }

    /**
     * 数据校验
     * @param array $data
     * @return void
     */
    protected function validate(array $data): void
    {
        if (empty($data['cate_id']) || count($data['cate_id']) < 1) {
            throw new \RuntimeException('请选择商品分类');
        }
        if (empty($data['store_name'])) {
            throw new \RuntimeException('请输入商品名称');
        }
        if (empty($data['slider_image']) || count($data['slider_image']) < 1) {
            throw new \RuntimeException('请选择商品轮播图');
        }
        if (!empty($data['is_limit']) && $data['is_limit'] == 1) {
            if ($data['min_qty'] > $data['limit_num']) {
                throw new \RuntimeException('起购数量不能大于限购数量');
            }
            if (!in_array($data['limit_type'] ?? 0, [1, 2])) {
                throw new \RuntimeException('请选择限购类型');
            }
            if (($data['limit_num'] ?? 0) <= 0) {
                throw new \RuntimeException('限购数量不能小于1');
            }
        }
    }

    /**
     * 数据预处理
     * @param array $data
     * @return array
     */
    protected function prepareData(array $data): array
    {
        // 首图：从轮播图第一张取
        $data['image'] = $data['slider_image'][0] ?? '';

        // SKU字段名映射：新命名 → 旧系统内部命名
        // 前端/API使用新字段名，后端内部处理使用新字段名
        if (!empty($data['attrs'])) {
            foreach ($data['attrs'] as &$attr) {
                if (isset($attr['ot_price']) && !isset($attr['original_price'])) {
                    $attr['original_price'] = $attr['ot_price'];
                }
                if (isset($attr['is_vip_price']) && !isset($attr['vip_price'])) {
                    $attr['vip_price'] = $attr['is_vip_price'];
                }
                // suk（逗号分隔字符串,如"红色,M"）→ detail（KV对象，如{"颜色":"红色","尺寸":"M"}）
                if (!isset($attr['detail']) && !empty($attr['suk']) && !empty($data['items'])) {
                    $sukValues = explode(',', $attr['suk']);
                    $detail = [];
                    foreach ($data['items'] as $i => $item) {
                        $key = $item['value'] ?? $item['name'] ?? ('规格' . ($i + 1));
                        $detail[$key] = $sukValues[$i] ?? '';
                    }
                    $attr['detail'] = $detail;
                }
            }
            unset($attr);
        }

        // 多规格 items 中 value 去重后存入，用于后续重构 detail
        if (($data['spec_type'] ?? 0) == 1 && !empty($data['items'])) {
            $uniqueItems = [];
            $seen = [];
            foreach ($data['items'] as $item) {
                $val = $item['value'] ?? '';
                if ($val !== '' && !in_array($val, $seen)) {
                    $seen[] = $val;
                    $uniqueItems[] = $item;
                }
            }
            $data['items'] = $uniqueItems;
        }

        // 单规格且无 SKU 数据时，补齐默认 SKU
        if (($data['spec_type'] ?? 0) == 0 && empty($data['attrs'])) {
            $data['attrs'] = [[
                'price'         => $data['price'] ?? 0,
                'original_price' => $data['original_price'] ?? 0,
                'stock'         => $data['stock'] ?? 0,
                'cost'          => $data['cost'] ?? 0,
                'pic'           => $data['image'] ?: '',
                'vip_price'     => $data['vip_price'] ?? 0,
                'detail'        => ['规格' => '默认'],
            ]];
        }

        $detail = $data['attrs'] ?? [];
        if (empty($detail) || !is_array($detail)) {
            throw new \RuntimeException('请完善商品规格');
        }

        if (($data['spec_type'] ?? 0) == 0) {
            foreach ($detail as &$item) {
                if (!is_array($item)) {
                    continue;
                }
                if (empty($item['detail']) || !is_array($item['detail'])) {
                    $item['detail'] = ['规格' => '默认'];
                }
            }
            unset($item);
        }

        $seenDetailKeys = [];

        // 补齐每个 SKU 的默认字段
        foreach ($detail as &$item) {
            if (!is_array($item)) {
                throw new \RuntimeException('商品规格数据格式错误');
            }
            $detailValues = $item['detail'] ?? [];
            if (!is_array($detailValues) || empty($detailValues)) {
                throw new \RuntimeException('请完善商品规格值');
            }
            $detailKey = $this->normalizeSkuDetailKey($detailValues);
            if (isset($seenDetailKeys[$detailKey])) {
                throw new \RuntimeException('商品规格不能重复');
            }
            $seenDetailKeys[$detailKey] = true;
            $item['price'] = (float)($item['price'] ?? 0);
            $item['original_price'] = (float)($item['original_price'] ?? $item['price']);
            $item['stock'] = (int)($item['stock'] ?? 0);
            if ($item['price'] < 0 || $item['original_price'] < 0 || $item['stock'] < 0) {
                throw new \RuntimeException('商品价格和库存不能为负数');
            }
            $item['cost'] = $item['cost'] ?? 0;
            $item['vip_price'] = $item['vip_price'] ?? $item['price'];
        }
        unset($item);

        $data['attrs'] = $detail;

        // 价格计算
        if (($data['spec_type'] ?? 0) == 1) {
            $defaultItems = array_filter($detail, fn($i) => (int)($i['is_default_select'] ?? 0) === 1);
            if (!empty($defaultItems)) {
                $defaultItem = reset($defaultItems);
                $data['price'] = $defaultItem['price'];
                $data['original_price'] = $defaultItem['original_price'];
            } else {
                $data['price'] = min(array_column($detail, 'price'));
                $data['original_price'] = min(array_column($detail, 'original_price'));
            }
        } else {
            $data['price'] = min(array_column($detail, 'price'));
            $data['original_price'] = min(array_column($detail, 'original_price'));
        }

        // 库存汇总
        $data['stock'] = array_sum(array_column($detail, 'stock'));

        $data['unit_name'] = $data['unit_name'] ?? '件';
        $data['keyword'] = $data['keyword'] ?? '';
        $data['sort'] = $data['sort'] ?? 0;
        $data['is_show'] = $data['is_show'] ?? 0;

        // 商品简介
        if (!empty($data['description']) && empty($data['intro'])) {
            $data['intro'] = strip_tags((string) $data['description']);
            $data['intro'] = mb_substr($data['intro'], 0, 256);
        }

        return $data;
    }

    /**
     * 构建中台商品主表数据（仅 eb_product_center 的字段）
     * 产品类型专属字段由 Handler::afterProductSave 写入 eb_pt_normal
     * @param string $productType
     * @param array $data
     * @return array
     */
    protected function buildCenterProductData(string $productType, array $data): array
    {
        $cateId = 0;
        if (!empty($data['cate_id']) && is_array($data['cate_id'])) {
            $cateId = (int) reset($data['cate_id']);
        }

        $intro = '';
        if (!empty($data['intro'])) {
            $intro = $data['intro'];
        } elseif (!empty($data['description'])) {
            $intro = strip_tags((string) $data['description']);
            $intro = mb_substr($intro, 0, 256);
        }

        // 兼容前端两种提交格式：主商品管理使用 is_sub 数组（1=分销，0=会员价），插件管理使用 is_sub 标量 + is_vip 标量
        if (is_array($data['is_sub'] ?? [])) {
            $isSub = in_array(1, $data['is_sub']) ? 1 : 0;
            $isVip = in_array(0, $data['is_sub']) ? 1 : 0;
        } else {
            $isSub = intval($data['is_sub'] ?? 0);
            $isVip = intval($data['is_vip'] ?? 0);
        }

        // 预售时间（用于写入中台表）
        $presale = intval($data['presale'] ?? 0);
        $presaleStartTime = $presale && !empty($data['presale_time']) ? strtotime($data['presale_time'][0]) : 0;
        $presaleEndTime = $presale && !empty($data['presale_time']) ? strtotime($data['presale_time'][1]) : 0;

        $centerData = [
            'product_type' => $productType,
            'name'         => $data['store_name'] ?? '',
            'intro'        => $intro,
            'video_link'   => $data['video_link'] ?? '',
            'keyword'      => $data['keyword'] ?? '',
            'image'        => $data['image'] ?? '',
            'slider_image' => !empty($data['slider_image']) && is_array($data['slider_image']) ? json_encode($data['slider_image']) : ($data['slider_image'] ?? ''),
            'price'        => $data['price'] ?? 0,
            'original_price' => $data['original_price'] ?? 0,
            'stock'          => $data['stock'] ?? 0,
            'virtual_sales'  => $data['ficti'] ?? 0,
            'sales'          => 0,
            'cate_id'        => $cateId,
            'sort_order'     => $data['sort'] ?? 0,
            'unit_name'    => $data['unit_name'] ?? '件',
            'spec_type'       => $data['spec_type'] ?? 0,
            'is_show'         => $data['is_show'] ?? 0,
            'is_del'          => 0,
            // 会员价/分销/会员专属字段统一写入中台主表
            'is_sub'          => $isSub,
            'is_vip'          => $isVip,
            'vip_product'     => intval($data['vip_product'] ?? 0),
            // 商品通用展示字段统一写入中台主表，避免与扩展表重复存储
            'custom_form'     => !empty($data['custom_form']) ? json_encode($data['custom_form']) : '',
            'params_list'     => !empty($data['params_list']) ? json_encode($data['params_list']) : '',
            'label_list'      => !empty($data['label_list']) && is_array($data['label_list']) ? implode(',', $data['label_list']) : '',
            'protection_list' => !empty($data['protection_list']) && is_array($data['protection_list']) ? implode(',', $data['protection_list']) : '',
            // 优品推荐（前端传数组对象，转为逗号分隔的 product_id 字符串）
            'recommend_list'  => !empty($data['recommend_list']) && is_array($data['recommend_list']) ? implode(',', array_column($data['recommend_list'], 'product_id')) : '',
            'recommend_image' => $data['recommend_image'] ?? '',
            // ====== 以下字段从 eb_pt_normal 迁移到 eb_product_center ======
            // 配送方式
            'delivery_methods'  => !empty($data['delivery_methods']) && is_array($data['delivery_methods'])
                ? array_values(array_unique($data['delivery_methods'])) : ['express'],
            // 运费配置：type 必填，但需保留 door_service 等其他节点（不能因缺少 type 就整包丢弃）
            'freight_config'    => $this->normalizeFreightConfig($data['freight_config'] ?? null),
            // 预售
            'presale'            => $presale,
            'presale_start_time' => $presaleStartTime,
            'presale_end_time'   => $presaleEndTime,
            // 限购配置
            'is_limit'  => intval($data['is_limit'] ?? 0),
            'limit_type' => intval($data['limit_type'] ?? 0),
            'limit_num'  => intval($data['limit_num'] ?? 0),
            'min_qty'    => intval($data['min_qty'] ?? 1),
            // 赠品/积分
            'is_gift'       => intval($data['is_gift'] ?? 0),
            'gift_price'    => floatval($data['gift_price'] ?? 0),
            'give_integral' => floatval($data['give_integral'] ?? 0),
            'give_coupon_ids' => !empty($data['coupon_ids']) && is_array($data['coupon_ids']) ? implode(',', $data['coupon_ids']) : '',
        ];
        // 预售发货时间（预售活动结束后N天内发货）：仅调用方显式提供时才写入（最小1天），
        // 未提供时新增走列默认值1、编辑保持库中原值，避免不带该字段的保存把已配置值重置
        if (isset($data['presale_day'])) {
            $centerData['presale_day'] = max(1, intval($data['presale_day']));
        }

        return $centerData;
    }

    /**
     * 规范化运费配置
     * 委托 FreightConfigHelper::normalize() 统一处理：
     * - 兼容旧格式输入（顶层 type/fixed/template 自动升级）
     * - 输出新双节点格式（express + door_service）
     * - type=free 时强制 postage=0 避免脏数据
     *
     * @param mixed $config
     * @return array
     */
    protected function normalizeFreightConfig($config): array
    {
        return FreightConfigHelper::normalize($config);
    }

    /**
     * 写实物商品SKU表
     * @param int $productId
     * @param array $data
     * @return void
     */
    protected function saveSku(int $productId, array $data): void
    {
        /** @var NormalProductSkuDao $skuDao */
        $skuDao = app(NormalProductSkuDao::class);
        $defaultDetailKey = (($data['spec_type'] ?? 0) == 0)
            ? $this->normalizeSkuDetailKey(['规格' => '默认'])
            : '';

        // 编辑时按规格内容复用旧唯一码，避免调整规格顺序后购物车指向错误 SKU。
        $existingSkuMap = [];
        foreach ($skuDao->selectList(['product_id' => $productId]) as $existingSku) {
            if (is_object($existingSku)) {
                $existingSku = $existingSku->toArray();
            }
            $existingSuk = json_decode((string)($existingSku['suk'] ?? ''), true);
            if (is_array($existingSuk) && !empty($existingSuk)) {
                $existingSkuMap[$this->normalizeSkuDetailKey($existingSuk)] = (string)($existingSku['unique_code'] ?? '');
            } elseif ($defaultDetailKey !== '' && !isset($existingSkuMap[$defaultDetailKey])) {
                $existingSkuMap[$defaultDetailKey] = (string)($existingSku['unique_code'] ?? '');
            }
        }

        // 编辑时先删除旧SKU
        $skuDao->deleteByProductId($productId);

        $detail = $data['attrs'] ?? [];

        foreach ($detail as $index => $item) {
            $detailValues = $item['detail'] ?? [];
            $suk = is_array($detailValues) ? json_encode($detailValues) : (string) $detailValues;
            $image = $item['pic'] ?? $data['image'] ?? '';
            $detailKey = is_array($detailValues) ? $this->normalizeSkuDetailKey($detailValues) : '';
            $uniqueCode = $existingSkuMap[$detailKey] ?? '';
            if ($uniqueCode === '') {
                $uniqueCode = substr(strtolower(md5('normal_' . $productId . '_' . $detailKey)), 0, 8);
            }

            $skuData = [
                'product_id'        => $productId,
                'suk'               => $suk,
                'price'             => $item['price'] ?? 0,
                'original_price'    => $item['original_price'] ?? 0,
                'cost'              => $item['cost'] ?? 0,
                'vip_price'         => $item['vip_price'] ?? ($item['price'] ?? 0),
                'stock'             => $item['stock'] ?? 0,
                'image'             => $image,
                'unique_code'       => $uniqueCode,
                'is_default_select' => (int)($item['is_default_select'] ?? ($index === 0 ? 1 : 0)),
                'is_show'           => $item['is_show'] ?? 1,
                'bar_code'          => $item['bar_code'] ?? '',
                'bar_code_number'   => $item['bar_code_number'] ?? '',
                'weight'            => $item['weight'] ?? 0,
                'volume'            => $item['volume'] ?? 0,
                'unit_name'         => $item['unit_name'] ?? '',
            ];

            $skuDao->save($skuData);
        }
    }

    /**
     * 生成稳定的 SKU 规格键，确保字段顺序变化不会生成不同唯一码。
     */
    protected function normalizeSkuDetailKey(array $detail): string
    {
        ksort($detail);
        return (string)json_encode($detail, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * 保存产品扩展表 eb_pt_normal
     * 将产品类型专属字段写入扩展表
     */
    protected function saveProductExtend(int $productId, array $data): void
    {
        // 通用字段已迁移到 eb_product_center，扩展表仅保留实际存在的实物专属字段
        $extendData = [
            'product_id'        => $productId,
            'weight'            => $data['weight'] ?? 0,
            'volume'            => $data['volume'] ?? 0,
            'add_time'          => time(),
            'update_time'       => time(),
        ];

        /** @var NormalProductDao $dao */
        $dao = app(NormalProductDao::class);
        $exist = $dao->getOne(['product_id' => $productId]);
        if ($exist) {
            unset($extendData['add_time']);
            $dao->update($exist['id'], $extendData);
        } else {
            $dao->save($extendData);
        }
    }
}
