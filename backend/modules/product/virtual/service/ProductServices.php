<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace modules\product\virtual\service;

use modules\product\virtual\dao\VirtualProductDao;
use app\dao\product\CenterProductDao;
use modules\product\virtual\dao\VirtualProductSkuDao;
use app\services\product\product\StoreDescriptionServices;
use app\services\product\product\StoreProductCouponServices;

/**
 * 网盘商品服务
 * 商品管理业务逻辑（下载链接/网盘地址等扩展信息）
 */
class ProductServices
{
    /**
     * 扩展表 DAO
     */
    protected VirtualProductDao $dao;

    /**
     * 中台商品 DAO
     */
    protected CenterProductDao $centerDao;

    public function __construct(VirtualProductDao $dao, CenterProductDao $centerDao)
    {
        $this->dao = $dao;
        $this->centerDao = $centerDao;
    }

    /**
     * 获取商品列表（后台用）
     * @param array $params
     * @return array
     */
    public function getList(array $params): array
    {
        $searchWhere = [
            'product_type' => 'virtual',
            'store_name'   => $params['store_name'] ?? '',
            'cate_id'      => $params['cate_id'] ?? '',
            'price_s'      => [
                $params['price_min'] ?? '',
                $params['price_max'] ?? '',
            ],
            'stock_s'      => [
                $params['stock_min'] ?? '',
                $params['stock_max'] ?? '',
            ],
            'time'         => $this->parseTimeRange($params['time'] ?? ''),
        ];
        $isDel = (int) ($params['is_del'] ?? 0);
        $searchWhere['is_del'] = $isDel;
        if (($params['status'] ?? '') !== '') {
            $searchWhere['is_show'] = (int) $params['status'];
        }
        $page = max(1, (int) ($params['page'] ?? 1));
        $limit = max(1, (int) ($params['limit'] ?? 20));
        $total = $this->centerDao->getCount($searchWhere);
        $list = $this->centerDao->getSearchList(
            $searchWhere,
            $page,
            $limit,
            ['id', 'name as store_name', 'image', 'price', 'stock', 'sales', 'is_show', 'sort_order', 'add_time']
        );
        // 图片拼站点域名（对齐 CenterProductServices::getList 口径；编辑详情接口保持原始路径）
        foreach ($list as &$item) {
            $item['image'] = set_file_url($item['image'] ?? '');
        }
        unset($item);
        return compact('total', 'list');
    }

    /**
     * 将前端日期范围转换为中台商品搜索器支持的时间戳数组。
     * @param array|string $value
     * @return array|string
     */
    protected function parseTimeRange(array|string $value): array|string
    {
        if (is_array($value) || $value === '') {
            return $value;
        }
        $range = array_map('trim', explode(',', $value));
        if (count($range) !== 2) {
            return $value;
        }
        $start = strtotime($range[0] . ' 00:00:00');
        $end = strtotime($range[1] . ' 23:59:59');
        return [$start ?: 0, $end ?: 0];
    }

    /**
     * 获取商品详情（基础信息 + 扩展信息）
     * @param int $productId
     * @return array
     */
    public function getExtendInfo(int $productId): array
    {
        $centerInfo = $this->centerDao->get($productId);
        if (!$centerInfo) {
            return [];
        }
        $product = $centerInfo->toArray();

        // ============ 字段映射：eb_product列名 → 前端期望字段名 ============
        $product['store_name'] = $product['name'] ?? '';
        $product['store_info'] = $product['intro'] ?? '';
        $product['ficti'] = $product['ficti'] ?? ($product['virtual_sales'] ?? 0);

        // ============ 扩展信息 ============
        // 网盘链接/提取码已下移到 SKU 级（随 attrs 返回），商品级仅保留全局"自动发货"开关
        $extendInfo = $this->dao->getOne(['product_id' => $productId]);
        $product['is_auto_send'] = $extendInfo ? (int)($extendInfo['is_auto_send'] ?? 1) : 1;

        // is_sub（分销开关）和 is_vip（会员价开关）是独立标量字段
        $product['is_sub'] = (int) ($product['is_sub'] ?? 0);
        $product['is_vip'] = (int) ($product['is_vip'] ?? 0);
        $product['recommend'] = [];

        // ============ 列表/JSON字段 ============
        $product['logistics'] = !empty($product['logistics']) ? explode(',', $product['logistics']) : [];
        $product['recommend_list'] = !empty($product['recommend_list']) ? explode(',', (string)$product['recommend_list']) : [];
        $product['cate_id'] = !empty($product['cate_id']) ? explode(',', (string)$product['cate_id']) : [];
        $product['label_id'] = !empty($product['label_id']) ? explode(',', (string)$product['label_id']) : [];
        $product['label_list'] = !empty($product['label_list']) ? array_map('intval', explode(',', (string)$product['label_list'])) : [];
        $product['protection_list'] = !empty($product['protection_list']) ? array_map('intval', explode(',', (string)$product['protection_list'])) : [];
        $product['params_list'] = !empty($product['params_list']) ? json_decode($product['params_list'], true) : [];
        $product['custom_form'] = !empty($product['custom_form']) ? json_decode($product['custom_form'], true) : [];
        $product['activity'] = !empty($product['activity']) ? explode(',', (string)$product['activity']) : ['默认', '秒杀', '砍价', '拼团'];

        // ============ 数值字段 ============
        $product['give_integral'] = floatval($product['give_integral'] ?? 0);
        $pst = $product['presale_start_time'] ?? 0;
        $pet = $product['presale_end_time'] ?? 0;
        $product['presale_time'] = $pst == 0 ? [] : [date('Y-m-d H:i:s', $pst), date('Y-m-d H:i:s', $pet)];

        // ============ 视频 ============
        $vl = $product['video_link'] ?? '';
        $product['video_open'] = $vl !== '' ? 1 : 0;
        $product['seletVideo'] = (!empty($vl) && strpos($vl, 'http') !== false) ? 1 : 0;
        $product['video_link'] = $vl;

        // ============ slider_image ============
        // 注意：CenterProductModel::getSliderImageAttr 获取器已将数据库 JSON 字符串自动解码为数组，
        // 因此优先按数组处理，仅兼容旧版字符串格式（否则 is_string 误判会导致轮播图被置空不保存）
        $si = $product['slider_image'] ?? '';
        if (is_array($si)) {
            $product['slider_image'] = $si;
        } elseif (is_string($si) && $si !== '') {
            $product['slider_image'] = json_decode($si, true) ?? [];
        } else {
            $product['slider_image'] = [];
        }
        if (!empty($product['slider_image'])) {
            $product['image'] = $product['slider_image'][0] ?? '';
        }

        // ============ description（从描述表读取）============
        try {
            $descService = app(StoreDescriptionServices::class);
            $product['description'] = $descService->getDescription(['product_id' => $productId, 'activity_type' => 'normal']);
        } catch (\Throwable $e) {
            $product['description'] = '';
        }

        // ============ coupons ============
        $product['coupons'] = [];
        try {
            $couponService = app(StoreProductCouponServices::class);
            $product['coupons'] = $couponService->getProductCoupons($productId);
        } catch (\Throwable $e) {
        }

        // ============ SKU 数据（attrs）============
        $product['attrs'] = $this->buildSkuAttrs($productId, $product);
        $product['attr'] = $product['attrs'][0] ?? [];
        $product['spec_type'] = (int)($product['spec_type'] ?? 0);
        $product['is_virtual'] = (int)($product['is_virtual'] ?? 0);
        $product['product_type'] = $product['product_type'] ?? 'virtual';

        return $product;
    }

    /**
     * 构建 SKU 规格数据（编辑回显）
     * 读取全部 SKU，每条含 SKU 级网盘信息（download_url/download_password）与规格映射 detail
     * @param int $productId
     * @param array $product
     * @return array
     */
    protected function buildSkuAttrs(int $productId, array $product): array
    {
        try {
            $skuDao = app(VirtualProductSkuDao::class);
            $skuList = $skuDao->selectList(['product_id' => $productId], '*', 0, 0, 'is_default DESC, id ASC');
            $skuList = $skuList ? $skuList->toArray() : [];
            if ($skuList) {
                $result = [];
                foreach ($skuList as $sku) {
                    $skuAttrs = json_decode($sku['sku_attrs'] ?? '', true);
                    $detail = (is_array($skuAttrs) && $skuAttrs) ? $skuAttrs : ['规格' => $sku['sku_name'] ?: '默认'];
                    $result[] = [
                        'pic'               => $sku['image'] ?? $product['image'] ?? '',
                        'price'             => (float)($sku['price'] ?? $product['price'] ?? 0),
                        'cost'              => (float)($sku['cost'] ?? $product['cost'] ?? 0),
                        'ot_price'          => (float)($sku['ot_price'] ?? $product['ot_price'] ?? 0),
                        'stock'             => (int)($sku['stock'] ?? $product['stock'] ?? 0),
                        'bar_code'          => $sku['bar_code'] ?? '',
                        'bar_code_number'   => $sku['bar_code_number'] ?? '',
                        'brokerage'         => (float)($sku['brokerage'] ?? 0),
                        'brokerage_two'     => (float)($sku['brokerage_two'] ?? 0),
                        'vip_price'         => (float)($sku['vip_price'] ?? 0),
                        'unique'            => (string)($sku['id'] ?? ''),
                        'download_url'      => $sku['download_url'] ?? '',
                        'download_password' => $sku['download_password'] ?? '',
                        'detail'            => $detail,
                    ];
                }
                return $result;
            }
        } catch (\Throwable $e) {
        }
        return [[
            'pic'               => $product['image'] ?? '',
            'price'             => (float)($product['price'] ?? 0),
            'cost'              => (float)($product['cost'] ?? 0),
            'ot_price'          => (float)($product['original_price'] ?? 0),
            'stock'             => (int)($product['stock'] ?? 0),
            'bar_code'          => '',
            'bar_code_number'   => '',
            'brokerage'         => 0,
            'brokerage_two'     => 0,
            'vip_price'         => 0,
            'unique'            => '',
            'download_url'      => '',
            'download_password' => '',
            'detail'            => ['规格' => '默认'],
        ]];
    }

    /**
     * 从网盘商品专属 SKU 表读取成本价
     */
    protected function getCostFromSkuTable(int $productId): float
    {
        try {
            /** @var VirtualProductSkuDao $skuDao */
            $skuDao = app(VirtualProductSkuDao::class);
            $sku = $skuDao->getOne(['product_id' => $productId]);
            if ($sku) {
                return (float) $sku['cost'];
            }
        } catch (\Throwable $e) {
            // ignore
        }
        return 0.0;
    }

    /**
     * 保存商品扩展信息
     * 网盘链接/提取码已下移到 SKU 级，商品级仅保存全局"自动发货"开关
     * @param int $productId
     * @param array $data
     * @return void
     */
    public function saveExtendInfo(int $productId, array $data): void
    {
        $extendData = [
            'product_id'   => $productId,
            'is_auto_send' => $data['is_auto_send'] ?? 1,
        ];

        $exist = $this->dao->getOne(['product_id' => $productId]);
        if ($exist) {
            $this->dao->update($exist['id'], $extendData);
        } else {
            $this->dao->save($extendData);
        }
    }

    /**
     * 删除商品扩展信息
     * @param int $productId
     * @return void
     */
    public function deleteExtendInfo(int $productId): void
    {
        $this->dao->delete(['product_id' => $productId]);
    }
}
