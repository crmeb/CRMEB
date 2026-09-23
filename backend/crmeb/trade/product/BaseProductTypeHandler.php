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

namespace crmeb\trade\product;

use crmeb\trade\order\aftersale\AfterSaleHandlerInterface;
use crmeb\trade\order\aftersale\BaseAfterSaleHandler;
use think\facade\Log;

/**
 * 产品类型处理器基类
 * 提供默认实现，子类按需覆盖
 *
 * 订单状态常量统一引用 app\model\order\OrderCenter 作为唯一数据源
 */
abstract class BaseProductTypeHandler implements ProductTypeHandlerInterface
{
    /**
     * 默认使用订单中台统一详情路由。
     * @param string $orderNo 订单号
     * @return string
     */
    public function getOrderDetailRoute(string $orderNo): string
    {
        return '/pages/center/order/detail/index?order_no=' . urlencode($orderNo);
    }
    /**
     * 默认能力声明。
     * @return array
     */
    public function getCapabilities(): array
    {
        return [
            'pricing' => $this->getPricingPolicy(),
            'delivery' => ['express' => true, 'pickup' => true, 'door_service' => false],
            'inventory' => ['check_stock' => true],
            'order' => ['virtual' => false, 'status_group' => 'delivery'],
        ];
    }

    /**
     * 默认所有价格优惠能力开启，特殊商品类型覆写此方法关闭能力。
     * @return array
     */
    public function getPricingPolicy(): array
    {
        return [
            'member_discount' => true,
            'vip_discount' => true,
            'coupon' => true,
            'integral' => true,
            'promotion' => true,
        ];
    }
    /**
     * 默认额外必填字段（空，子类按需覆盖）
     */
    public function getRequiredFields(): array
    {
        return [];
    }

    /**
     * 默认商品保存后回调（空实现）
     */
    public function afterProductSave(int $productId, array $data): void
    {
        // 子类按需实现
    }

    /**
     * 默认商品删除回调（空实现）
     */
    public function afterProductDelete(int $productId): void
    {
        // 子类按需实现
    }

    // ===== SKU 维度 =====

    /**
     * 默认 SKU 获取（回退到商品中台主表 eb_product_center）
     * 已迁移到独立 SKU 表的产品类型应覆盖此方法
     * @param int $productId 中台商品ID
     * @param string $skuUnique 规格唯一编码，空则取默认SKU
     * @return array|null
     */
    public function getSkuInfo(int $productId, string $skuUnique): ?array
    {
        try {
            // 通过接口契约反转依赖，避免核心层反向依赖 app 层 dao
            /** @var ProductQueryInterface $centerProductDao */
            $centerProductDao = app()->make(ProductQueryInterface::class);
            $product = $centerProductDao->getProductField($productId, ['id', 'price', 'ot_price', 'cost', 'stock', 'image']);
            if (empty($product)) {
                return null;
            }
            if (is_object($product)) {
                $product = $product->toArray();
            }

            return [
                'id'       => (int)($product['id'] ?? 0),
                'unique'   => $skuUnique ?: '',
                'price'    => $product['price'] ?? 0,
                'ot_price' => $product['ot_price'] ?? 0,
                'cost'     => $product['cost'] ?? 0,
                'stock'    => (int)($product['stock'] ?? 0),
                'image'    => $product['image'] ?? '',
                'suk'      => '默认',
            ];
        } catch (\Throwable $e) {
            // 记录错误日志，避免 DB/SQL 故障被静默吞掉后表现为"商品不存在"，增加排查成本
            Log::error('[ProductType] getSkuInfo 查询失败: ' . $e->getMessage(), [
                'product_id' => $productId,
                'sku_unique' => $skuUnique,
                'type_key'   => $this->getTypeKey(),
            ]);
            return null;
        }
    }

    /**
     * 默认活动兼容性（子类应覆盖）
     */
    public function canCombineWith(string $tradeMode): bool
    {
        return false;
    }

    /**
     * 默认详情扩展数据（空）
     */
    public function getDetailExtend(int $productId): array
    {
        return [];
    }

    /**
     * 默认列表扩展数据
     * 子类覆盖：返回卡片样式、类型标签、单位名等信息
     */
    public function getListExtend(int $productId): array
    {
        return [
            'card_style' => 'standard',
            'type_label' => '商品',
            'unit_name'  => '件',
        ];
    }

    // ===== 商品详情规格维度 =====

    /**
     * 默认规格数据（空数组）
     * 卡密/优惠券等无需规格选择的类型直接使用基类默认值
     * 实物/虚拟商品等需要规格选择的类型必须覆盖此方法
     * @param int $productId
     * @return array
     */
    public function getDetailSpecs(int $productId): array
    {
        return [
            'productAttr' => [],
            'productValue' => [],
        ];
    }

    // ===== 库存管理维度 =====

    /**
     * 默认库存扣减（各产品类型必须覆盖此方法）
     */
    public function decStock(int $productId, string $skuUnique, int $num): bool
    {
        throw new \RuntimeException('decStock() not implemented for product type: ' . $this->getTypeKey());
    }

    /**
     * 默认库存回滚（各产品类型必须覆盖此方法）
     */
    public function incStock(int $productId, string $skuUnique, int $num): bool
    {
        throw new \RuntimeException('incStock() not implemented for product type: ' . $this->getTypeKey());
    }

    /**
     * 默认运费配置（空实现，实物商品应覆盖）
     */
    public function getShippingInfo(int $productId): array
    {
        return [
            'freight' => 0,
            'temp_id' => 0,
            'postage'  => 0,
            'weight'   => 0,
            'volume'   => 0,
        ];
    }

    /**
     * 默认页面渲染配置
     * 子类覆盖：返回不同场景下的 page_config，移动端按此声明式渲染
     * @param string $scene 场景标识：detail（详情页）| list（列表页）
     * @param string $appVersion 小程序版本号，用于版本协商降级
     * @return array
     */
    public function getPageConfig(string $scene, string $appVersion = '1.0.0'): array
    {
        // 通用默认配置：实物商品标准页面
        if ($scene === 'detail') {
            return [
                'sections' => [
                    ['key' => 'carousel', 'component' => 'image_carousel', 'props' => ['images' => '$.storeInfo.slider_image']],
                    ['key' => 'product_info', 'component' => 'product_info', 'props' => ['storeInfo' => '$.storeInfo']],
                    ['key' => 'sku', 'component' => 'sku_selector', 'props' => ['productAttr' => '$.productAttr', 'productValue' => '$.productValue']],
                    ['key' => 'params', 'component' => 'params_list', 'props' => ['params' => '$.storeInfo.params_list']],
                    ['key' => 'description', 'component' => 'rich_text', 'props' => ['content' => '$.storeInfo.description']],
                ],
                'bottom_bar' => [
                    'text'   => '立即购买',
                    'action' => 'submitOrder',
                ],
                'submit_api' => 'center/order/create',
                'submit_headers' => [
                    'X-Product-Type' => $this->getTypeKey(),
                ],
            ];
        }
        return [];
    }

    // ===== 订单中台维度 =====

    /**
     * 默认订单源类型
     * 子类必须覆盖，返回对应 OrderStatusAdapter 的 source_type
     * @return string
     * @throws \LogicException 子类未覆盖时抛出，避免静默写入错误的 source_type
     */
    public function getOrderSourceType(): string
    {
        throw new \LogicException(static::class . ' 必须覆盖 getOrderSourceType()，返回对应 OrderTypeAdapter 的 source_type');
    }

    /**
     * 默认非虚拟商品（实物商品无需覆盖）
     * @return bool
     */
    public function isVirtual(): bool
    {
        return false;
    }

    /**
     * 是否需要物流配送
     * 非虚拟商品默认需要配送，子类可覆盖
     * @return bool
     */
    public function requiresShipping(): bool
    {
        return !$this->isVirtual();
    }

    /**
     * 是否支持到店自提
     * 需要配送的商品默认支持到店自提，子类可覆盖
     * @return bool
     */
    public function supportsPickup(): bool
    {
        return !$this->isVirtual();
    }

    /**
     * 是否支持退货（退货退款售后）
     * 默认支持退货（实物商品），不支持退货的产品类型（卡密/网盘/酒店/门票等）由子类覆盖为 false
     * @return bool
     */
    public function supportsReturn(): bool
    {
        return true;
    }

    /**
     * 默认 SKU 编辑页无额外字段
     * 卡密/优惠券等虚拟类型可覆盖返回自身字段
     * @return array
     */
    public function getSkuExtraFields(): array
    {
        return [];
    }

    // ===== 履约维度 =====

    /**
     * 默认订单履约实现
     * 实物商品：默认返回 true（等待商家手动发货）
     * 虚拟商品：子类必须覆盖此方法实现自动发放逻辑
     * @param string $orderNo 订单号
     * @param array $orderInfo 订单完整信息
     * @return bool
     */
    public function fulfillOrder(string $orderNo, array $orderInfo): bool
    {
        // 默认实现：实物商品无需自动履约，等待商家手动发货
        return true;
    }

    // ===== 售后维度 =====

    /**
     * 默认售后规则处理器
     * 返回基础售后处理器，子类应覆盖返回自己的实现
     * @return AfterSaleHandlerInterface
     */
    public function getAfterSaleRule(): AfterSaleHandlerInterface
    {
        return new class extends BaseAfterSaleHandler {
            /**
             * 默认售后规则：允许售后，支持退款/退货退款
             * @param array $orderCenter
             * @param array $sourceOrder
             * @return array
             */
            public function getRule(array $orderCenter, array $sourceOrder): array
            {
                return [
                    'allow'           => true,
                    'reject_reason'   => '',
                    'available_types' => ['refund_only', 'return_refund'],
                    'refund_target'   => 'user',
                    'max_refund_num'  => 0, // 0 表示不限制
                    'deadline'        => 7, // 7天无理由
                ];
            }

            public function afterApply(string $afterSaleNo, array $data): void {}

            public function afterAudit(string $afterSaleNo, array $data): void {}

            public function afterRefund(string $afterSaleNo, float $amount, array $context): void {}

            public function afterCancel(string $afterSaleNo): void {}
        };
    }

    /**
     * 默认源表订单扩展数据（空）
     * 子类应覆盖返回自身扩展订单表记录（如 pt_cardkey_order、pt_virtual_order）
     * @param string $orderNo 订单号
     * @return array
     */
    public function getOrderExtend(string $orderNo): array
    {
        return [];
    }

    /**
     * 默认兼容性声明
     * 返回产品类型支持的交易模式列表和其他兼容性信息
     * @return array
     */
    public function getCompatibility(): array
    {
        $requiresShipping = !$this->isVirtual();
        return [
            'supported_trade_modes' => ['normal'],
            'requires_shipping'     => $requiresShipping,
            'supports_pickup'       => $requiresShipping,
            'supports_after_sale'   => true,
        ];
    }

    // ===== 选择与加购契约维度（v1.2 新增） =====

    /**
     * 默认选择契约
     * 默认值等于 normal 实物商品现状：规格矩阵选择 + 可编辑弹窗 + SKU 必选 + 件为单位
     * 各产品类型按需覆盖此方法声明自身选择模型
     * @return array {
     *     selection_model:     string, // 选择模型：matrix（规格矩阵）| temporal（日期时段）| none（无选择）| custom（自定义）
     *     confirm_window:      string, // 确认弹窗：editable（可编辑切换）| readonly（只读回显）| none（无弹窗）
     *     required_selections: array, // 必选项 key 列表
     *     cart_unit:           string  // 加购单位：item|day|weight|cycle
     * }
     */
    public function getSelectionSpec(): array
    {
        return [
            'selection_model'     => 'matrix',    // 规格矩阵选择
            'confirm_window'      => 'editable',  // 规格弹窗可编辑切换
            'required_selections' => ['sku'],
            'cart_unit'           => 'item',
        ];
    }

    /**
     * 默认加购策略契约
     * 默认值等于 normal 实物商品现状：双按钮 + 多品 + 默认文案
     * 各产品类型按需覆盖此方法声明自身加购策略
     * @return array {
     *     cart_mode:   string,  // 加购模式：cart（双按钮）| direct（单按钮直接下单）
     *     multi_item:  bool,    // 是否支持多品加购
     *     button_text: string   // 主按钮自定义文案，空表示用前端默认文案
     * }
     */
    public function getCartStrategy(): array
    {
        return [
            'cart_mode'   => 'cart',   // 双按钮：加入购物车 + 立即购买
            'multi_item'  => true,
            'button_text' => '',       // 空表示用前端默认文案
        ];
    }

    /**
     * 默认下单表单组件声明（无扩展组件）
     * 需要日期范围、预约表单等扩展下单组件的类型（如酒店）由子类覆盖声明
     * @return array
     */
    public function getOrderFormComponents(): array
    {
        return [];
    }

    // ===== 管理端中台维度 =====

    /**
     * 默认无管理端扩展信息
     * 未覆盖此方法的类型（如 ticket/hotel）等价于扩展服务不存在，中台跳过扩展字段补充
     * @param int $productId 商品ID
     * @return array
     */
    public function getAdminExtendInfo(int $productId): array
    {
        return [];
    }

    /**
     * 默认不支持模块化保存
     * 未覆盖此方法的类型返回 null，中台走遗留保存路径
     * @param int $id 商品ID（0 为新增）
     * @param array $data 商品数据
     * @return int|null
     */
    public function saveFromAdmin(int $id, array $data): ?int
    {
        return null;
    }
}
