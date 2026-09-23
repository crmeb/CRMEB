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

use crmeb\basic\BaseService;
use app\services\product\CenterProductServices;
use modules\product\virtual\Handler;
use modules\product\virtual\dao\VirtualProductSkuDao;
use app\services\product\product\StoreDescriptionServices;

/**
 * 网盘商品保存服务
 * 直接写入 eb_product 中台主表
 *
 * SKU 由本服务内部自动生成（单规格默认 SKU），前端不传 spec_type/items/attrs
 */
class ProductSaveServices extends BaseService
{
    /**
     * @var CenterProductServices
     */
    protected $centerProductServices;

    /**
     * @var Handler
     */
    protected $typeHandler;

    protected function initialize(): void
    {
        $this->centerProductServices = $this->app->make(CenterProductServices::class);
        $this->typeHandler = $this->app->make(Handler::class);
    }

    /**
     * 保存商品（新增/编辑统一入口）
     * @param int $id
     * @param array $data
     * @return int 商品ID
     */
    public function save(int $id, array $data): int
    {
        $data = $this->fillDefaults($data);
        $this->validate($data);
        $data = $this->prepareData($data);

        $centerData = $this->buildCenterProductData('virtual', $data);

        $productId = $this->transaction(function () use ($id, $data, $centerData) {
            if ($id > 0) {
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
            $this->typeHandler->afterProductSave($productId, $data);
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
        if (empty($description)) {
            return;
        }
        $descService = app(StoreDescriptionServices::class);
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
            'store_info'      => '',
            'cate_id'         => [1],
            'label_id'        => [],
            'slider_image'    => [],
            'image'           => '',
            'recommend_image' => '',
            'price'           => 0,
            'ot_price'        => 0,
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
            'product_type'    => 'virtual',
            'is_virtual'      => 1,
            'logistics'       => [],
            'freight'         => 1,
            'temp_id'         => 0,
            'weight'          => 0,
            'volume'          => 0,
            'custom_form'     => [],
            'params_list'     => [],
            'label_list'      => [],
            'protection_list' => [],
            'recommend'       => [],
            'recommend_list'  => [],
            'activity'        => ['默认', '秒杀', '砍价', '拼团'],
            'coupon_ids'      => [],
            'type'            => 0,
            'postage'         => 0,
            'keyword'         => '',
            'sort'            => 0,
            'unit_name'       => '件',
            // 视频
            'video_link'      => '',
            'video_open'      => 0,
            'seletVideo'      => 0,
            // 网盘扩展字段
            'download_url'      => '',
            'download_password' => '',
            'is_auto_send'      => 1,
        ];

        foreach ($defaults as $key => $value) {
            if (!isset($data[$key])) {
                $data[$key] = $value;
            }
        }

        // 网盘信息已下移到 SKU 级，商品级不再保存 download_url/download_password
        unset($data['download_url'], $data['download_password']);

        return $data;
    }

    /**
     * 数据校验
     * @param array $data
     * @return void
     */
    protected function validate(array $data): void
    {
        if (empty($data['store_name'])) {
            throw new \RuntimeException('请输入商品名称');
        }
        if (empty($data['slider_image']) || count($data['slider_image']) < 1) {
            throw new \RuntimeException('请选择商品轮播图');
        }
        if ((int)($data['is_auto_send'] ?? 1) !== 1) {
            throw new \RuntimeException('网盘商品必须开启支付后自动发货');
        }
        $attrs = $data['attrs'] ?? [];
        if (!is_array($attrs) || empty($attrs)) {
            throw new \RuntimeException('请配置网盘商品 SKU');
        }
        foreach ($attrs as $attr) {
            if (!is_array($attr) || !is_numeric($attr['price'] ?? null) || (float)$attr['price'] < 0
                || !is_numeric($attr['stock'] ?? null) || (int)$attr['stock'] < 0
                || trim((string)($attr['download_url'] ?? '')) === '') {
                throw new \RuntimeException('每个网盘 SKU 必须配置合法价格、库存和下载链接');
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

        // 从 oneFormValidate 取 vip_price、brokerage 等值
        $oneForm = $data['oneFormValidate'][0] ?? [];
        $brokerage = (float)($oneForm['brokerage'] ?? 0);
        $brokerageTwo = (float)($oneForm['brokerage_two'] ?? 0);
        $vipPrice = (float)($oneForm['vip_price'] ?? 0);

        // 未携带 attrs（异常兜底）时，自动生成单规格默认 SKU
        if (empty($data['attrs']) || !is_array($data['attrs'])) {
            $data['attrs'] = [[
                'price'             => $data['price'] ?? 0,
                'ot_price'          => $data['ot_price'] ?? 0,
                'stock'             => $data['stock'] ?? 0,
                'cost'              => $data['cost'] ?? 0,
                'pic'               => $data['image'] ?: '',
                'brokerage'         => $brokerage,
                'brokerage_two'     => $brokerageTwo,
                'vip_price'         => $vipPrice,
                'detail'            => ['规格' => '默认'],
                'download_url'      => '',
                'download_password' => '',
            ]];
        }

        return $data;
    }

    /**
     * 构建中台商品主表数据
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

        // 多规格时前端不填顶层价格/库存，统一取第一条 SKU 作为主表展示值
        $firstAttr = $data['attrs'][0] ?? [];
        $price   = (float)($firstAttr['price'] ?? $data['price'] ?? 0);
        $otPrice = (float)($firstAttr['ot_price'] ?? $data['ot_price'] ?? $price);
        $stock   = (int)($firstAttr['stock'] ?? $data['stock'] ?? 0);

        $intro = $data['store_info'] ?? '';
        if (empty($intro) && !empty($data['description'])) {
            $intro = strip_tags((string) $data['description']);
            $intro = mb_substr($intro, 0, 256);
        }

        // 处理 recommend_list
        $recommendList = !empty($data['recommend_list']) ? implode(',', array_column($data['recommend_list'], 'product_id')) : '';

        // 兼容前端两种提交格式：主商品管理使用 is_sub 数组（1=分销，0=会员价），插件管理使用 is_sub/is_vip 标量
        if (is_array($data['is_sub'] ?? [])) {
            $isSub = in_array(1, $data['is_sub']) ? 1 : 0;
            $isVip = in_array(0, $data['is_sub']) ? 1 : 0;
        } else {
            $isSub = intval($data['is_sub'] ?? 0);
            $isVip = intval($data['is_vip'] ?? 0);
        }

        // 处理预售时间
        $presale = intval($data['presale'] ?? 0);
        $presaleStartTime = $presale && !empty($data['presale_time']) ? strtotime($data['presale_time'][0]) : 0;
        $presaleEndTime = $presale && !empty($data['presale_time']) ? strtotime($data['presale_time'][1]) : 0;

        // 处理物流
        $logistics = !empty($data['logistics']) && is_array($data['logistics']) ? implode(',', $data['logistics']) : '';

        // 推荐标签 is_hot/is_benefit/is_new/is_good/is_best 已删除；recommend/recommend_list/recommend_image 已迁移到中台主表 eb_product_center

        // 处理运费
        $freight = intval($data['freight'] ?? 1);
        $tempId = $freight == 2 ? 0 : (int) ($data['temp_id'] ?? 0);
        $postage = $freight == 3 ? 0 : (float) ($data['postage'] ?? 0);

        return [
            'product_type'       => $productType,
            // 规格类型（0=单规格 1=多规格），编辑回显依赖该字段决定渲染单/多规格
            'spec_type'          => intval($data['spec_type'] ?? 0),
            'name'               => $data['store_name'] ?? '',
            'intro'              => $intro,
            'keyword'            => $data['keyword'] ?? '',
            'image'              => $data['image'] ?? '',
            'slider_image'       => !empty($data['slider_image']) && is_array($data['slider_image']) ? json_encode($data['slider_image']) : ($data['slider_image'] ?? ''),
            'price'              => $price,
            'original_price'     => $otPrice,
            'stock'              => $stock,
            'virtual_sales'      => $data['ficti'] ?? 0,
            'sales'              => 0,
            'cate_id'            => $cateId,
            'sort_order'         => $data['sort'] ?? 0,
            'unit_name'          => $data['unit_name'] ?? '件',
            'is_show'            => $data['is_show'] ?? 0,
            'is_del'             => 0,
            // 会员价/佣金
            'is_sub'             => $isSub,
            'is_vip'             => $isVip,
            'vip_product'        => intval($data['vip_product'] ?? 0),
            // 预售
            'presale'            => $presale,
            'presale_start_time' => $presaleStartTime,
            'presale_end_time'   => $presaleEndTime,
            // 限购
            'is_limit'           => intval($data['is_limit'] ?? 0),
            'limit_type'         => intval($data['limit_type'] ?? 0),
            'limit_num'          => intval($data['limit_num'] ?? 0),
            'min_qty'            => intval($data['min_qty'] ?? 1),
            // 物流运费
            'logistics'          => $logistics,
            'freight'            => $freight,
            'temp_id'            => $tempId,
            'postage'            => $postage,
            // 列表推荐
            'recommend_list'     => $recommendList,
            // 标签/参数/保障/自定义表单
            'label_list'         => !empty($data['label_list']) && is_array($data['label_list']) ? implode(',', $data['label_list']) : '',
            'params_list'        => !empty($data['params_list']) ? json_encode($data['params_list']) : '',
            'protection_list'    => !empty($data['protection_list']) && is_array($data['protection_list']) ? implode(',', $data['protection_list']) : '',
            'custom_form'        => !empty($data['custom_form']) ? json_encode($data['custom_form']) : '',
            // 积分
            'give_integral'      => floatval($data['give_integral'] ?? 0),
            // 视频
            'video_link'         => $data['video_link'] ?? '',
            // 商品类型
            'product_type'       => $data['product_type'] ?? 'virtual',
        ];
    }

    /**
     * 写网盘商品专属 SKU 表
     * 支持多规格：按 sku_name（规格值逗号拼接）幂等匹配，更新存在的、插入新增的、删除被移除的
     * 网盘链接/提取码从各 SKU 的 detail 维度单独读取，逐条写入
     * @param int $productId
     * @param array $data
     * @return void
     */
    protected function saveSku(int $productId, array $data): void
    {
        /** @var VirtualProductSkuDao $skuDao */
        $skuDao = app(VirtualProductSkuDao::class);

        // 已存在的 SKU：按 sku_name 映射，用于幂等更新（保留 id，避免购物车/订单唯一编码失效）
        $existMap = [];
        try {
            $existList = $skuDao->selectList(['product_id' => $productId], 'id,sku_name,is_default', 0, 0, 'id ASC');
            if ($existList) {
                foreach ($existList->toArray() as $row) {
                    $existMap[$row['sku_name']] = $row;
                }
            }
        } catch (\Throwable $e) {
            // 存量表无数据时忽略
        }

        // 先统一重置默认标记，循环中首条 SKU 置为默认
        if (!empty($existMap)) {
            $skuDao->update(['product_id' => $productId], ['is_default' => 0]);
        }

        $attrs = $data['attrs'] ?? [];
        if (empty($attrs) || !is_array($attrs)) {
            $attrs = [[
                'price'             => $data['price'] ?? 0,
                'stock'             => $data['stock'] ?? 0,
                'detail'            => ['规格' => '默认'],
                'download_url'      => '',
                'download_password' => '',
            ]];
        }

        $keepIds = [];
        $isFirst = true;
        foreach ($attrs as $attr) {
            $detail = $attr['detail'] ?? [];
            if (!is_array($detail) || empty($detail)) {
                $detail = ['规格' => '默认'];
            }
            // 规格值保序拼接为 sku_name（与 getDetailSpecs 的 suk 及 attr_format 分隔符一致）
            $skuName = implode(',', array_map('strval', array_values($detail)));
            if ($skuName === '') $skuName = '默认';

            $skuData = [
                'product_id'        => $productId,
                'sku_name'          => $skuName,
                'sku_attrs'         => json_encode($detail, JSON_UNESCAPED_UNICODE),
                'price'             => (float)($attr['price'] ?? $data['price'] ?? 0),
                'ot_price'          => (float)($attr['ot_price'] ?? $data['ot_price'] ?? 0),
                'cost'              => (float)($attr['cost'] ?? $data['cost'] ?? 0),
                'stock'             => (int)($attr['stock'] ?? $data['stock'] ?? 0),
                'image'             => (string)($attr['pic'] ?? $attr['image'] ?? ''),
                'download_url'      => (string)($attr['download_url'] ?? ''),
                'download_password' => (string)($attr['download_password'] ?? ''),
                'is_default'        => $isFirst ? 1 : 0,
                'add_time'          => time(),
            ];

            if (isset($existMap[$skuName])) {
                $skuDao->update($existMap[$skuName]['id'], $skuData);
                $keepIds[] = (int)$existMap[$skuName]['id'];
            } else {
                $skuData['sales'] = 0;
                $model = $skuDao->save($skuData);
                $keepIds[] = (int)($model->id ?? 0);
            }
            $isFirst = false;
        }

        // 删除本次未涉及的旧 SKU（规格被移除时清理）
        $deleteIds = [];
        foreach ($existMap as $row) {
            if (!in_array((int)$row['id'], $keepIds, true)) {
                $deleteIds[] = (int)$row['id'];
            }
        }
        if (!empty($deleteIds)) {
            $skuDao->delete(['id' => $deleteIds]);
        }
    }
}
