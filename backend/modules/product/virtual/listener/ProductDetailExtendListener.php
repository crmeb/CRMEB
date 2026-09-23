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

namespace modules\product\virtual\listener;

use app\services\product\product\StoreProductLabelServices;
use app\services\product\product\StoreProductProtectionServices;
use think\facade\Log;

/**
 * 商品详情扩展监听器（网盘商品）
 * 监听 ProductDetailExtend 事件，向 extensions 直接注入产品类型专属字段：
 * params_list / protection_list / label_list / custom_form / type_info / fulfillment_preview
 * 网盘商品 fulfillment_preview 为"支付后自动发货，无需物流"
 */
class ProductDetailExtendListener
{
    /**
     * 处理商品详情扩展注入
     * @param array $eventData ['extensions' => &array, 'handler' => Handler, 'product_id' => int, 'product_type' => string]
     */
    public function handle($eventData): void
    {
        // 仅处理网盘商品，避免为其他产品类型重复注入
        if (($eventData['product_type'] ?? '') !== 'virtual') {
            return;
        }

        // 异常容错：单监听器失败不中断整体 getDetail（文档 6.5.2）
        try {
            $this->injectExtensions($eventData);
            $this->injectDisplaySections($eventData);
        } catch (\Throwable $e) {
            Log::error('virtual ProductDetailExtendListener 异常: ' . $e->getMessage());
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

        // fulfillment_preview：网盘产品支付成功后自动发放网盘链接与提取码
        $extensions['fulfillment_preview'] = '支付成功后自动发放网盘链接与提取码';
    }

    /**
     * 注入 display_sections 展示块（新机制，数据驱动详情页渲染）
     * 普通网盘商品暂不输出 spec（matrix block）和 fulfillment（info_card），
     * 规格选择走原有 productWindow 弹窗体验，发货说明走原有 DIY 组件展示
     * 后续如需统一展示范式可重新启用
     * @param array $eventData
     * @return void
     */
    private function injectDisplaySections(array &$eventData): void
    {
        // 暂不输出 display_sections，保留方法骨架供后续启用
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
}
