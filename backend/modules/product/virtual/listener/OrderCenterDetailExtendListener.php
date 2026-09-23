<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace modules\product\virtual\listener;

use crmeb\interfaces\ListenerInterface;
use modules\product\virtual\dao\VirtualProductSkuDao;
use think\facade\Log;

/**
 * 订单中台详情扩展监听器（网盘产品）
 * 监听 OrderCenterDetailExtend 事件，向已支付订单详情注入履约信息：
 * 网盘链接 download_url + 提取码 download_password，前端订单详情自动渲染"网盘信息"卡片。
 * 网盘信息已下移到 SKU 级：优先取下单快照（attr_info），缺失时按订单 SKU 唯一编码查表兜底。
 */
class OrderCenterDetailExtendListener implements ListenerInterface
{
    /**
     * 处理订单中台详情扩展事件
     * @param mixed $event ['detail' => &array, 'source_type' => string, 'source_id' => int]
     * @return void
     */
    public function handle($event): void
    {
        $detail = &$event['detail'];
        if (empty($detail)) {
            return;
        }

        // 仅处理网盘产品订单，其他产品类型由各自的监听器负责
        if (($detail['product_type'] ?? '') !== 'virtual') {
            return;
        }

        // 异常容错：单个监听器失败不影响订单详情整体渲染
        try {
            // 未支付订单不展示网盘链接（支付成功后才发放）
            if ((int)($detail['pay_status'] ?? 0) !== 1) {
                return;
            }

            // 优先取下单快照中的 SKU 级网盘信息（attr_info 为 Handler::getSkuInfo 返回值）
            $items = $detail['extra_info']['items'] ?? [];
            $item = $items[0] ?? [];
            $attrInfo = $item['attr_info'] ?? [];
            $downloadUrl = (string)($attrInfo['download_url'] ?? '');
            $downloadPassword = (string)($attrInfo['download_password'] ?? '');

            // 快照缺失时，按订单 SKU 唯一编码（= eb_pt_virtual_sku.id）查表兜底
            if ($downloadUrl === '' && !empty($attrInfo['unique']) && is_numeric($attrInfo['unique'])) {
                try {
                    $skuDao = app(VirtualProductSkuDao::class);
                    $skuInfo = $skuDao->getOne(['id' => (int)$attrInfo['unique']]);
                    if ($skuInfo) {
                        if (is_object($skuInfo)) $skuInfo = $skuInfo->toArray();
                        $downloadUrl = (string)($skuInfo['download_url'] ?? '');
                        $downloadPassword = (string)($skuInfo['download_password'] ?? '');
                    }
                } catch (\Throwable $e) {
                    // 查表失败忽略，继续走下方空值判断
                }
            }

            // 网盘链接为空时不展示（SKU 未配置发货内容）
            if ($downloadUrl === '') {
                return;
            }

            // component 键由模块自决：网盘产品使用 download_link 组件渲染（中台只透传不映射）
            $detail['extend']['fulfillment'] = [
                'type'              => 'virtual',
                'component'         => 'download_link',
                'download_url'      => $downloadUrl,
                'download_password' => $downloadPassword,
            ];
            // 自声明渲染 section（中台不再硬编码插件 section，数据生产方自声明）
            $detail['extend_sections']['fulfillment'] = [
                'component' => 'download_link',
                'props'     => ['fulfillment' => $detail['extend']['fulfillment']],
                'visible'   => true,
                'order'     => 60,
            ];
        } catch (\Throwable $e) {
            Log::error('[Virtual] OrderCenterDetailExtend 异常: ' . $e->getMessage());
        }
    }
}
