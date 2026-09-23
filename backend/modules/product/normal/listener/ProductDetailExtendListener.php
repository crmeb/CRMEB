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

namespace modules\product\normal\listener;

use app\dao\system\store\SystemStoreDao;
use app\services\product\product\StoreProductLabelServices;
use app\services\product\product\StoreProductProtectionServices;
use think\facade\Log;

/**
 * 商品详情扩展监听器（实物商品）
 * 监听 ProductDetailExtend 事件，向 extensions 直接注入产品类型专属字段：
 * params_list / protection_list / label_list / custom_form / type_info / fulfillment_preview
 * 中台层（CenterProductServices）不再直接处理这些扩展字段，统一由本监听器注入，符合架构铁律第⑤条
 */
class ProductDetailExtendListener
{
    /**
     * 处理商品详情扩展注入
     * @param array $eventData ['extensions' => &array, 'handler' => Handler, 'product_id' => int, 'product_type' => string]
     */
    public function handle($eventData): void
    {
        // 仅处理实物商品，避免为其他产品类型重复注入
        if (($eventData['product_type'] ?? '') !== 'normal') {
            return;
        }

        // 异常容错：单监听器失败不中断整体 getDetail（文档 6.5.2）
        try {
            $this->injectExtensions($eventData);
            $this->injectDisplaySections($eventData);
        } catch (\Throwable $e) {
            Log::error('normal ProductDetailExtendListener 异常: ' . $e->getMessage());
        }
    }

    /**
     * 注入 extensions 扩展数据（旧机制，保留兼容）
     * @param array $eventData
     * @return void
     */
    private function injectExtensions(array &$eventData): void
    {
        $extensions = &$eventData['extensions'];
        $handler = $eventData['handler'];
        $productId = (int)$eventData['product_id'];

        // 通过 Handler 获取扩展字段原始数据（label_list/protection_list 为 ID 数组）
        $extend = $handler->getDetailExtend($productId);

        // custom_form / params_list：扩展表 JSON 已解码，直接注入
        $extensions['custom_form'] = $extend['custom_form'] ?? [];
        $extensions['params_list'] = $extend['params_list'] ?? [];

        // label_list：ID 数组 → 标签完整数据
        $extensions['label_list'] = $this->resolveLabels($extend['label_list'] ?? []);

        // protection_list：ID 数组 → 保障服务名称列表
        $extensions['protection_list'] = $this->resolveProtections($extend['protection_list'] ?? []);

        // type_info：卡片样式/类型标签/单位
        $extensions['type_info'] = $handler->getListExtend($productId);

        // fulfillment_preview：履约方式说明（根据 Handler 特性推导）
        $extensions['fulfillment_preview'] = $this->buildFulfillmentPreview($handler);
    }

    /**
     * 注入 display_sections 展示块（新机制，数据驱动详情页渲染）
     * 普通商品输出自提门店（store_card）：当商品支持到店自提且系统存在启用门店时，
     * 每个启用门店输出一个 store_card section（含名称/地址/电话/经纬度，支持地图导航）
     * 规格选择走原有 productWindow 弹窗体验，配送信息走原有 DIY 组件展示
     * @param array $eventData
     * @return void
     */
    private function injectDisplaySections(array &$eventData): void
    {
        $handler = $eventData['handler'];
        $productId = (int)$eventData['product_id'];

        // 仅当商品支持到店自提时才输出门店信息，避免无关商品多一次门店查询
        $capability = $handler->getDeliveryCapability($productId);
        if (empty($capability['is_pickup'])) {
            return;
        }

        // 解析系统启用中的自提门店
        $stores = $this->resolvePickupStores();
        if (empty($stores)) {
            return;
        }

        // 每个门店输出一个 store_card section，order 落在产品类型固有展示区间（10-99）
        // 标题仅首个 section 输出，避免多门店时标题重复
        foreach ($stores as $index => $store) {
            $eventData['display_sections'][] = [
                'key'       => 'store_' . $store['id'],
                'component' => 'store_card',
                'title'     => $index === 0 ? lang('自提门店') : '',
                'visible'   => true,
                'order'     => 30,
                'data'      => $store,
            ];
        }
    }

    /**
     * 解析系统启用中的自提门店列表（eb_system_store）
     * 经纬度缺失的门店无法提供地图导航，直接跳过
     * 距离由前端基于用户定位实时计算，后端不下发
     * @return array
     */
    private function resolvePickupStores(): array
    {
        try {
            $stores = app()->make(SystemStoreDao::class)->getStoreList(
                ['type' => 0],
                ['id', 'name', 'phone', 'address', 'detailed_address', 'latitude', 'longitude', 'day_time']
            );
            $result = [];
            foreach ($stores as $store) {
                if (empty($store['latitude']) || empty($store['longitude'])) {
                    continue;
                }
                $result[] = [
                    'id'               => (int)$store['id'],
                    'name'             => $store['name'] ?? '',
                    'address'          => trim(($store['address'] ?? '') . ' ' . ($store['detailed_address'] ?? '')),
                    'phone'            => $store['phone'] ?? '',
                    'lat'              => $store['latitude'],
                    'lng'              => $store['longitude'],
                    'business_hours'   => $this->formatBusinessHours($store['day_time'] ?? ''),
                    'distance'         => '',
                    'store_type'       => 'pickup',
                    'store_type_label' => lang('自提店'),
                ];
            }
            return $result;
        } catch (\Throwable $e) {
            // 门店查询失败不影响详情页整体返回
            return [];
        }
    }

    /**
     * 清洗门店营业时间（eb_system_store.day_time）
     * 格式不统一（如 "08:00:00  -  20:12:00"），统一为 "HH:MM-HH:MM"
     * @param string $dayTime
     * @return string
     */
    private function formatBusinessHours(string $dayTime): string
    {
        $dayTime = trim($dayTime);
        if ($dayTime === '') {
            return '';
        }
        // 按分隔符（-、—、逗号、空白）拆出时间点
        $parts = preg_split('/[-—,，]|\s+/', $dayTime);
        $parts = array_values(array_filter(array_map('trim', $parts), function ($p) {
            return $p !== '';
        }));
        if (count($parts) < 2) {
            return '';
        }
        // 仅取时:分，去掉秒
        $start = substr($parts[0], 0, 5);
        $end = substr(end($parts), 0, 5);
        return $start . '-' . $end;
    }

    /**
     * 标签 ID 数组 → 标签完整数据
     * @param array $ids
     * @return array
     */
    private function resolveLabels(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }
        try {
            return app()->make(StoreProductLabelServices::class)->labelListByIds($ids);
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * 保障服务 ID 数组 → 保障服务名称列表
     * @param array $ids
     * @return array
     */
    private function resolveProtections(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }
        try {
            return app()->make(StoreProductProtectionServices::class)->protectionListByIds($ids);
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * 根据 Handler 特性推导履约方式说明
     * @param mixed $handler
     * @return string
     */
    private function buildFulfillmentPreview($handler): string
    {
        if ($handler->isVirtual()) {
            return '支付后自动发货，无需物流';
        }
        $parts = [];
        if ($handler->requiresShipping()) {
            $parts[] = '快递配送';
        }
        if ($handler->supportsPickup()) {
            $parts[] = '到店自提';
        }
        return implode(' / ', $parts);
    }

    /**
     * 构建配送/履约信息 items（供 display_sections 的 info_card 使用）
     * 复用 Handler 已有的 requiresShipping/supportsPickup 方法推导
     * @param mixed $handler
     * @return array
     */
    private function buildFulfillmentItems($handler): array
    {
        $items = [];
        // 配送方式：根据 Handler 能力推导
        $methods = [];
        if ($handler->requiresShipping()) {
            $methods[] = lang('快递配送');
        }
        if ($handler->supportsPickup()) {
            $methods[] = lang('到店自提');
        }
        if (!empty($methods)) {
            $items[] = ['label' => lang('配送方式'), 'value' => implode(' / ', $methods), 'icon' => 'express'];
        }
        return $items;
    }
}
