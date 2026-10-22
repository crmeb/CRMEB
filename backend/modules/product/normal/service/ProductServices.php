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

use modules\product\normal\dao\NormalProductDao;
use modules\product\normal\dao\NormalProductSkuDao;
use app\dao\product\CenterProductDao;
use app\dao\product\product\StoreDescriptionDao;
use app\dao\product\product\StoreProductCateDao;
use app\model\product\CenterProductModel;
use app\model\user\UserLabel;
use think\facade\Db;

/**
 * 实物商品服务
 * 商品管理业务逻辑
 */
class ProductServices
{
    /**
     * 扩展表 DAO（eb_pt_normal）
     */
    protected NormalProductDao $dao;

    /**
     * 中台商品 DAO（eb_product_center）
     */
    protected CenterProductDao $centerDao;

    /**
     * SKU DAO（eb_pt_normal_sku）
     */
    protected NormalProductSkuDao $skuDao;

    public function __construct(NormalProductDao $dao, CenterProductDao $centerDao, NormalProductSkuDao $skuDao)
    {
        $this->dao = $dao;
        $this->centerDao = $centerDao;
        $this->skuDao = $skuDao;
    }

    /**
     * 获取商品详情（基础信息 + 扩展信息 + SKU + 描述）
     * 中台主表 eb_product_center + 扩展表 eb_pt_normal + SKU表 eb_pt_normal_sku
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

        // ============ 字段映射：eb_product_center列名 → 前端期望字段名 ============
        $product['store_name'] = $product['name'] ?? '';
        $product['ficti'] = $product['virtual_sales'] ?? 0;
        $product['views'] = $product['views'] ?? 0;
        $product['sort'] = $product['sort_order'] ?? 0;
        $product['original_price'] = $product['original_price'] ?? 0;
        $product['cost'] = 0; // 默认值，下面从SKU覆盖

        // is_sub（分销开关）和 is_vip（会员价开关）是独立标量字段
        $product['is_sub'] = (int) ($product['is_sub'] ?? 0);
        $product['is_vip'] = (int) ($product['is_vip'] ?? 0);

        // 扩展表（eb_pt_normal）仅保留实物专属字段
        $extendInfo = $this->dao->getOne(['product_id' => $productId]);
        if ($extendInfo) {
            $extend = $extendInfo->toArray();
            $product['weight'] = $extend['weight'] ?? 0;
            $product['volume'] = $extend['volume'] ?? 0;
        } else {
            $product['weight'] = 0;
            $product['volume'] = 0;
        }

        // 配送方式和运费配置已迁移到 eb_product_center，从主表读取
        // delivery_methods/freight_config 通过 ThinkPHP JSON 自动器自动处理
        $product['delivery_methods'] = $product['delivery_methods'] ?? ['express'];
        $product['freight_config'] = $product['freight_config'] ?? FreightConfigHelper::DEFAULT_CONFIG;

        // 优惠券信息从 eb_product_center.give_coupon_ids 读取
        $product['coupons'] = $this->getCouponsByIds($product['give_coupon_ids'] ?? '');

        // 商品通用展示字段以 eb_product_center 为准
        if (!empty($product['custom_form'])) {
            $decoded = is_string($product['custom_form'])
                ? (json_decode($product['custom_form'], true) ?: [])
                : $product['custom_form'];
            if (!empty($decoded)) {
                $product['custom_form'] = $decoded;
            }
        }
        if (!empty($product['params_list'])) {
            $decoded = is_string($product['params_list'])
                ? (json_decode($product['params_list'], true) ?: [])
                : $product['params_list'];
            if (!empty($decoded)) {
                $product['params_list'] = $decoded;
            }
        }
        if (!empty($product['label_list'])) {
            $product['label_list'] = is_string($product['label_list'])
                ? array_map('intval', explode(',', $product['label_list']))
                : array_map('intval', (array)$product['label_list']);
        } else {
            $product['label_list'] = [];
        }
        if (!empty($product['protection_list'])) {
            $product['protection_list'] = is_string($product['protection_list'])
                ? array_map('intval', explode(',', $product['protection_list']))
                : array_map('intval', (array)$product['protection_list']);
        } else {
            $product['protection_list'] = [];
        }

        // ============ cate_id ============
        $product['cate_id'] = !empty($product['cate_id']) ? explode(',', (string)$product['cate_id']) : [];

        // 从 label_list（标签ID数组）查询完整标签对象
        if (!empty($product['label_list'])) {
            try {
                $product['label_id'] = UserLabel::whereIn('id', $product['label_list'])
                    ->field('id,label_name')
                    ->select()
                    ->toArray();
            } catch (\Throwable $e) {
                $product['label_id'] = [];
            }
        } else {
            $product['label_id'] = [];
        }

        // ============ 预售时间 ============
        $pst = $product['presale_start_time'] ?? 0;
        $pet = $product['presale_end_time'] ?? 0;
        $product['presale_time'] = $pst == 0 ? [] : [date('Y-m-d H:i:s', $pst), date('Y-m-d H:i:s', $pet)];
        // 预售发货时间（预售活动结束后N天内发货），历史数据无值时回退默认1天
        $product['presale_day'] = max(1, (int)($product['presale_day'] ?? 1));

        // ============ 视频 ============
        $vl = $product['video_link'] ?? '';
        $product['video_open'] = $vl !== '' ? 1 : 0;
        $product['seletVideo'] = (!empty($vl) && strpos($vl, 'http') !== false) ? 1 : 0;
        $product['video_link'] = $vl;

        // ============ product_type / is_virtual（实物商品默认 normal）============
        $product['product_type'] = $product['product_type'] ?? 'normal';
        $product['is_virtual'] = (int)($product['is_virtual'] ?? 0);

        // ============ slider_image（由 Model 获取器自动解码为数组）============
        // 图片拼站点域名（对齐列表口径）；保存链路 ProductSaveServices 会去域名还原为相对路径入库
        $siteUrl = sys_config('site_url');
        $product['slider_image'] = set_file_url($product['slider_image'] ?? [], $siteUrl);
        $product['recommend_image'] = set_file_url($product['recommend_image'] ?? '', $siteUrl);
        $product['image'] = set_file_url($product['image'] ?? '', $siteUrl);
        if (!empty($product['slider_image'])) {
            $product['image'] = $product['slider_image'][0] ?? '';
        }

        // ============ description（从描述表读取）============
        try {
            $descService = app(\app\services\product\product\StoreDescriptionServices::class);
            $product['description'] = $descService->getDescription(['product_id' => $productId, 'activity_type' => 'normal']);
        } catch (\Throwable $e) {
            $product['description'] = '';
        }

        // ============ SKU 数据（attrs + items）============
        $product['items'] = [];
        $product['attrs'] = $this->buildSkuAttrs($productId, $product);
        // SKU字段名反向映射：内部命名 → 前端期望字段名
        foreach ($product['attrs'] as &$attr) {
            $attr['is_vip_price'] = $attr['vip_price'] ?? 0;
            $attr['is_sub_price'] = $attr['brokerage'] ?? 0;
            $attr['pic'] = set_file_url($attr['pic'] ?? '', $siteUrl);
        }
        unset($attr);
        $product['attr'] = $product['attrs'][0] ?? [];
        $product['spec_type'] = (int)($product['spec_type'] ?? 0);

        // 从默认SKU更新 product-level cost
        if (!empty($product['attrs'][0]['cost'])) {
            $product['cost'] = (float)$product['attrs'][0]['cost'];
        }

        // 多规格时，从 SKU 的 detail 中提取 items（规格头）
        if ($product['spec_type'] === 1) {
            $product['items'] = $this->buildSkuItems($product['attrs']);
        }

        // 推荐商品列表：中台表存储逗号分隔的ID字符串，转换为带图片的数组
        $recommend_list = [];
        $recommendStr = $product['recommend_list'] ?? '';
        if ($recommendStr !== '') {
            $recommendIds = array_filter(array_map('intval', explode(',', $recommendStr)));
            if (!empty($recommendIds)) {
                // 走 DAO 层按 ID 列表获取精简商品信息
                $list = $this->centerDao->getSimpleListByIds($recommendIds);
                // 保持推荐顺序与提交顺序一致
                $map = array_column($list, null, 'product_id');
                foreach ($recommendIds as $rid) {
                    if (isset($map[$rid])) {
                        $map[$rid]['image'] = set_file_url($map[$rid]['image'] ?? '', $siteUrl);
                        $recommend_list[] = $map[$rid];
                    }
                }
            }
        }
        $product['recommend_list'] = $recommend_list;

        return $product;
    }

    /**
     * 构建 SKU 数据
     * @param int $productId
     * @param array $product
     * @return array
     */
    protected function buildSkuAttrs(int $productId, array $product): array
    {
        try {
            $skuList = $this->skuDao->selectList(['product_id' => $productId]);
            $hasSku = false;
            $attrs = [];
            foreach ($skuList as $sku) {
                $hasSku = true;
                // 处理 sku 可能是 model 对象的情况
                if (is_object($sku)) {
                    $sku = $sku->toArray();
                }
                $suk = $sku['suk'] ?? '';
                $detail = $suk ? json_decode($suk, true) : ['规格' => '默认'];
                if (!is_array($detail)) {
                    $detail = ['规格' => '默认'];
                }
                $attrs[] = [
                    'pic'                => $sku['image'] ?? $product['image'] ?? '',
                    'price'              => (float)($sku['price'] ?? $product['price'] ?? 0),
                    'cost'               => (float)($sku['cost'] ?? $product['cost'] ?? 0),
                    'original_price'     => (float)($sku['original_price'] ?? $product['original_price'] ?? 0),
                    'stock'              => (int)($sku['stock'] ?? $product['stock'] ?? 0),
                    'bar_code'           => $sku['bar_code'] ?? '',
                    'bar_code_number'    => $sku['bar_code_number'] ?? '',
                    'brokerage'          => (float)($sku['brokerage'] ?? 0),
                    'brokerage_two'      => (float)($sku['brokerage_two'] ?? 0),
                    'vip_price'          => (float)($sku['vip_price'] ?? 0),
                    'weight'             => (float)($sku['weight'] ?? 0),
                    'volume'             => (float)($sku['volume'] ?? 0),
                    'unit_name'          => $sku['unit_name'] ?? '',
                    'is_default_select'  => (int)($sku['is_default_select'] ?? 0),
                    'is_show'            => (int)($sku['is_show'] ?? 1),
                    'detail'             => $detail,
                ];
            }
            if ($hasSku) {
                return $attrs;
            }
        } catch (\Throwable $e) {
        }

        // 无SKU记录时返回默认SKU
        return [[
            'pic'                => $product['image'] ?? '',
            'price'              => (float)($product['price'] ?? 0),
            'cost'               => (float)($product['cost'] ?? 0),
            'original_price'     => (float)($product['original_price'] ?? 0),
            'stock'              => (int)($product['stock'] ?? 0),
            'bar_code'           => '',
            'bar_code_number'    => '',
            'brokerage'          => 0,
            'brokerage_two'      => 0,
            'vip_price'          => 0,
            'weight'             => 0,
            'volume'             => 0,
            'unit_name'          => '',
            'is_default_select'  => 1,
            'is_show'            => 1,
            'detail'             => ['规格' => '默认'],
        ]];
    }

    /**
     * 从 SKU detail 中提取规格头（items）
     * 例如：[{id: 1, name: '颜色', values: ['红色','蓝色']}]
     * @param array $attrs
     * @return array
     */
    protected function buildSkuItems(array $attrs): array
    {
        $specMap = [];
        $idCounter = 1;

        foreach ($attrs as $attr) {
            $detail = $attr['detail'] ?? [];
            if (!is_array($detail)) continue;

            foreach ($detail as $name => $value) {
                if (!isset($specMap[$name])) {
                    $specMap[$name] = [
                        'id'     => $idCounter++,
                        'name'   => $name,
                        'values' => [],
                    ];
                }
                if (!in_array($value, $specMap[$name]['values'])) {
                    $specMap[$name]['values'][] = $value;
                }
            }
        }

        return array_values($specMap);
    }

    /**
     * 保存商品扩展信息到 eb_pt_normal
     * 通用字段已迁移到 eb_product_center，扩展表仅保留实物专属字段
     * @param int $productId
     * @param array $data
     * @return void
     */
    public function saveExtendInfo(int $productId, array $data): void
    {
        $extendData = [
            'product_id'       => $productId,
            'weight'           => $data['weight'] ?? 0,
            'volume'           => $data['volume'] ?? 0,
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

    // ===== 商品生命周期管理（中台主表 + 扩展表同步） =====

    /**
     * 获取实物商品列表（分页 + 状态Tab筛选）
     * @param array $where [store_name, cate_id, page, limit, type]
     * @return array ['list' => [], 'count' => int]
     */
    public function getList(array $where): array
    {
        $page  = (int)($where['page'] ?? 1);
        $limit = (int)($where['limit'] ?? 20);

        // 通用筛选交给中台模型搜索器（store_name/spec_type/is_gift/vip_product/price_s/stock_s/sales_s/time）
        // cate_id 与 type 不能传入搜索器：cate_id 需按实物口径双向展开（通用搜索器单值走 FIND_IN_SET、数组走旧透视表），
        // type 的通用搜索器 tab 口径与实物模块不同（如搜索器 case4=售罄、case5=有库存）
        $searchWhere = [
            'store_name'  => $where['store_name'] ?? '',
            'spec_type'   => $where['spec_type'] ?? '',
            'is_gift'     => $where['is_gift'] ?? '',
            'vip_product' => $where['vip_product'] ?? '',
            'price_s'     => $where['price_s'] ?? [],
            'stock_s'     => $where['stock_s'] ?? [],
            'sales_s'     => $where['sales_s'] ?? [],
            'time'        => $where['time'] ?? '',
        ];
        $query = $this->centerDao->search($searchWhere, false)->where('product_type', 'normal');

        // 状态筛选
        switch ($where['type'] ?? '1') {
            case '1': // 出售中
                $query->where('is_show', 1)->where('is_del', 0);
                break;
            case '2': // 仓库中
                $query->where('is_show', 0)->where('is_del', 0);
                break;
            case '3': // 已售馨
                $query->where('is_show', 1)->where('is_del', 0)->where('stock', 0);
                break;
            case '4': // 警戒库存
                $query->where('is_show', 1)->where('is_del', 0);
                $this->applyWarningStockFilter($query);
                break;
            case '5': // 回收站
                $query->where('is_del', 1);
                break;
            default:
                $query->where('is_show', 1)->where('is_del', 0);
                break;
        }

        // 分类筛选：商品允许存任意层级分类且仅存单个 cate_id，需双向匹配
        // （选中分类自身 + 全部子孙分类 + 全部祖先分类）
        if (!empty($where['cate_id'])) {
            $query->where('cate_id', 'in', $this->getCateMatchIds((int)$where['cate_id']));
        }

        $count = (clone $query)->count();
        $list  = $query->order('sort_order desc, id desc')
            ->page($page, $limit)
            ->select()
            ->toArray();

        // 字段映射：eb_product_center列名 → 前端期望字段名
        // 图片拼站点域名（对齐 CenterProductServices::getList 口径；编辑详情接口保持原始路径，避免回存绝对地址污染库）
        foreach ($list as &$item) {
            $item['store_name'] = $item['name'] ?? '';
            $item['sort'] = $item['sort_order'] ?? 0;
            $item['image'] = set_file_url($item['image'] ?? '');
            $item['slider_image'] = set_file_url($item['slider_image'] ?? []);
        }
        unset($item);

        return compact('list', 'count');
    }

    /**
     * 获取与指定分类匹配的全部分类 ID（自身 + 全部子分类 + 全部祖先分类）
     * 商品保存允许选任意层级且仅存单个 cate_id，搜索需双向展开：
     * 选父级要命中存子级的商品，选子级也要命中存父级的商品
     * 分类数量少（eb_store_category），一次取回在内存展开
     * @param int $cateId
     * @return array
     */
    protected function getCateMatchIds(int $cateId): array
    {
        $ids    = [$cateId];
        $pidMap = Db::name('store_category')->column('pid', 'id');

        // 向下展开全部子分类
        $queue = [$cateId];
        while ($queue) {
            $pid = array_pop($queue);
            foreach ($pidMap as $id => $parentPid) {
                if ((int)$parentPid === $pid && !in_array($id, $ids, true)) {
                    $ids[]   = (int)$id;
                    $queue[] = (int)$id;
                }
            }
        }

        // 向上回溯祖先分类
        $node = $cateId;
        while (isset($pidMap[$node]) && (int)$pidMap[$node] > 0) {
            $node = (int)$pidMap[$node];
            if (in_array($node, $ids, true)) {
                break;
            }
            $ids[] = $node;
        }

        return $ids;
    }

    /**
     * 获取各状态Tab商品数量统计
     * @return array
     */
    public function getTypeHeader(): array
    {
        $base = $this->centerDao->search()->where('product_type', 'normal');

        $warningBase = (clone $base)->where('is_show', 1)->where('is_del', 0);
        $this->applyWarningStockFilter($warningBase);

        return [
            ['type' => 1, 'name' => '出售中的商品', 'count' => (clone $base)->where('is_show', 1)->where('is_del', 0)->count()],
            ['type' => 2, 'name' => '仓库中的商品', 'count' => (clone $base)->where('is_show', 0)->where('is_del', 0)->count()],
            ['type' => 3, 'name' => '已售馨的商品', 'count' => (clone $base)->where('is_show', 1)->where('is_del', 0)->where('stock', 0)->count()],
            ['type' => 4, 'name' => '警戒库存商品', 'count' => $warningBase->count()],
            ['type' => 5, 'name' => '回收站的商品', 'count' => (clone $base)->where('is_del', 1)->count()],
        ];
    }

    /**
     * 按 SKU 库存筛选警戒库存商品。
     * 只有存在启用 SKU 且至少一个 SKU 库存低于系统警戒值的商品才命中。
     * @param mixed $query
     * @return void
     */
    protected function applyWarningStockFilter($query): void
    {
        $warningStock = (int)sys_config('store_stock');
        if ($warningStock <= 0) {
            $warningStock = 2;
        }

        $query->whereIn('id', function ($skuQuery) use ($warningStock) {
            $skuQuery->name('pt_normal_sku')
                ->where('is_show', 1)
                ->where('stock', '<', $warningStock)
                ->field('product_id');
        });
    }

    // ===== 商品生命周期管理（中台主表 + 扩展表同步） =====

    /**
     * 软删除商品（仅中台主表标记 is_del=1，扩展表无 is_del 字段）
     * @param int $productId
     * @return void
     */
    public function softDelete(int $productId): void
    {
        $this->centerDao->update($productId, ['is_del' => 1]);
    }

    /**
     * 批量软删除商品
     * @param array $ids 商品ID数组
     * @return void
     */
    public function batchSoftDelete(array $ids): void
    {
        foreach ($ids as $id) {
            $this->softDelete((int)$id);
        }
    }

    /**
     * 批量恢复商品（中台主表 + 扩展表同步恢复 is_del=0）
     * @param array $ids 商品ID数组
     * @return void
     */
    public function batchRecover(array $ids): void
    {
        foreach ($ids as $id) {
            $this->centerDao->update((int)$id, ['is_del' => 0]);
            $this->dao->update(['product_id' => (int)$id], ['is_del' => 0]);
        }
    }

    /**
     * 单独设置商品上下架状态
     * @param int $productId
     * @param int $isShow 0-下架 1-上架
     * @return void
     */
    public function setShow(int $productId, int $isShow): void
    {
        $this->centerDao->update($productId, ['is_show' => $isShow]);
    }

    /**
     * 批量上下架
     * @param array $ids 商品ID数组
     * @param int $isShow 0-下架 1-上架
     * @return void
     */
    public function batchSetShow(array $ids, int $isShow): void
    {
        foreach ($ids as $id) {
            $this->centerDao->update((int)$id, ['is_show' => $isShow]);
        }
    }

    /**
     * 彻底删除商品（物理删除：中台主表 + 扩展表 + SKU表 + 商品详情 + 分类关联）
     * @param int $productId
     * @return void
     */
    public function fullDelete(int $productId): void
    {
        // 同一事务内清理全部关联数据，避免部分删除后异常产生孤儿数据
        Db::transaction(function () use ($productId) {
            $this->centerDao->delete($productId);
            $this->dao->deleteByProductId($productId);
            $this->skuDao->deleteByProductId($productId);
            // 商品详情与分类关联由保存时写入，彻底删除时一并清理
            app()->make(StoreDescriptionDao::class)->delete(['product_id' => $productId]);
            app()->make(StoreProductCateDao::class)->delete(['product_id' => $productId]);
        });
    }

    // ===== 批量设置 =====

    /**
     * 批量设置分类
     * @param array $ids 商品ID数组
     * @param array $cateId 分类ID数组
     * @return void
     */
    public function batchSetCategory(array $ids, array $cateId): void
    {
        $cateStr = implode(',', $cateId);
        foreach ($ids as $id) {
            $this->centerDao->update((int)$id, ['cate_id' => $cateStr]);
        }
    }

    /**
     * 批量设置物流信息
     * @param array $ids 商品ID数组
     * @param array $data [delivery_methods, freight_config]
     * @return void
     */
    public function batchSetLogistics(array $ids, array $data): void
    {
        // 配送方式
        $deliveryMethods = !empty($data['delivery_methods']) && is_array($data['delivery_methods'])
            ? array_values(array_unique($data['delivery_methods']))
            : ['express'];

        // 运费配置：复用 FreightConfigHelper 统一规范化（兼容旧格式输入）
        $freightConfig = FreightConfigHelper::normalize($data['freight_config'] ?? null);

        // 已迁移到 eb_product_center，通过 centerDao 写入
        foreach ($ids as $id) {
            $this->centerDao->update((int)$id, [
                'delivery_methods' => $deliveryMethods,
                'freight_config'   => $freightConfig,
            ]);
        }
    }

    /**
     * 批量设置购买送积分
     * 已迁移到 eb_product_center，通过 centerDao 写入
     */
    public function batchSetGiveIntegral(array $ids, float $integral): void
    {
        foreach ($ids as $id) {
            $this->centerDao->update((int)$id, ['give_integral' => $integral]);
        }
    }

    /**
     * 批量设置购买送优惠券
     * 已迁移到 eb_product_center，通过 centerDao 写入
     */
    public function batchSetGiveCoupons(array $ids, array $couponIds): void
    {
        $couponStr = !empty($couponIds) ? implode(',', $couponIds) : '';
        foreach ($ids as $id) {
            $this->centerDao->update((int)$id, ['give_coupon_ids' => $couponStr]);
        }
    }

    /**
     * 批量设置关联用户标签
     * 已迁移到 eb_product_center，通过 centerDao 写入
     */
    public function batchSetLabel(array $ids, array $labelIds): void
    {
        $labelStr = implode(',', $labelIds);
        foreach ($ids as $id) {
            $this->centerDao->update((int)$id, ['label_list' => $labelStr]);
        }
    }

    /**
     * 批量设置商品标签
     * 已迁移到 eb_product_center，通过 centerDao 写入
     */
    public function batchSetProductLabel(array $ids, array $labelList): void
    {
        $labelStr = implode(',', $labelList);
        foreach ($ids as $id) {
            $this->centerDao->update((int)$id, ['label_list' => $labelStr]);
        }
    }

    /**
     * 批量设置送礼配置
     * 已迁移到 eb_product_center，通过 centerDao 写入
     */
    public function batchSetGift(array $ids, int $isGift, float $giftPrice): void
    {
        foreach ($ids as $id) {
            $this->centerDao->update((int)$id, [
                'is_gift'    => $isGift,
                'gift_price' => $giftPrice,
            ]);
        }
    }

    // ===== 辅助方法 =====

    /**
     * 根据优惠券ID字符串获取优惠券详情列表
     * @param string $couponIdsStr
     * @return array
     */
    protected function getCouponsByIds(string $couponIdsStr): array
    {
        if (empty($couponIdsStr)) {
            return [];
        }
        $couponIds = array_filter(array_map('intval', explode(',', $couponIdsStr)));
        if (empty($couponIds)) {
            return [];
        }
        try {
            /** @var \app\services\activity\coupon\StoreCouponIssueServices $couponService */
            $couponService = app(\app\services\activity\coupon\StoreCouponIssueServices::class);
            $list = $couponService->selectList(['id' => $couponIds, 'is_del' => 0], 'id,title,coupon_title');
            $result = [];
            foreach ($list as $item) {
                $item = is_object($item) ? $item->toArray() : $item;
                $result[] = [
                    'id' => $item['id'] ?? 0,
                    'title' => $item['coupon_title'] ?? ($item['title'] ?? ''),
                ];
            }
            return $result;
        } catch (\Throwable $e) {
            return [];
        }
    }
}
