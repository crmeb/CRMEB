<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace modules\product\virtual;

use app\dao\product\CenterProductDao;
use app\dao\order\OrderCenterDao;
use app\services\order\OrderCenterServices;
use crmeb\trade\order\aftersale\AfterSaleHandlerInterface;
use crmeb\trade\product\BaseProductTypeHandler;
use modules\product\virtual\handler\VirtualOrderAdapter;
use modules\product\virtual\service\ProductSaveServices;
use modules\product\virtual\service\ProductServices;
use modules\product\virtual\dao\VirtualProductSkuDao;
use modules\product\virtual\dao\VirtualProductDao;
use modules\product\virtual\dao\VirtualOrderDao;

/**
 * 网盘产品处理器
 * 网盘产品无需物流，支付后自动发放网盘链接与提取码
 * 状态流程：待付款 → 自动发放 → 待评价 → 已完成
 */
class Handler extends BaseProductTypeHandler
{
    /**
     * 类型标识
     */
    public function getTypeKey(): string
    {
        return 'virtual';
    }

    /**
     * 类型名称
     */
    public function getTypeName(): string
    {
        return '网盘产品';
    }

    /**
     * 额外必填字段
     * 网盘商品不需要分类、不需要物流
     */
    public function getRequiredFields(): array
    {
        return [];
    }

    /**
     * 活动兼容性：网盘商品不参与拼团、砍价、预售，只支持 normal 和 seckill
     */
    public function canCombineWith(string $tradeMode): bool
    {
        return in_array($tradeMode, ['normal', 'seckill']);
    }

    // ===== 商品管理维度 =====

    /**
     * 获取下单用 SKU 信息
     * 网盘商品从独立 SKU 表 eb_pt_virtual_sku 读取（列：sku_name/price/stock/sales）
     * @param int $productId 中台商品ID
     * @param string $skuUnique 规格唯一编码，空则取默认SKU
     * @return array|null
     */
    public function getSkuInfo(int $productId, string $skuUnique): ?array
    {
        try {
            $virtualSkuDao = app(VirtualProductSkuDao::class);

            // getDetailSpecs() 返回的 unique 值为 sku 的 id
            // 因此按 id 精准匹配，未指定则取默认或第一条
            if ($skuUnique) {
                if (is_numeric($skuUnique)) {
                    $skuInfo = $virtualSkuDao->getOne(['id' => (int)$skuUnique, 'product_id' => $productId]);
                } else {
                    $skuInfo = $virtualSkuDao->getOne(['sku_name' => $skuUnique, 'product_id' => $productId]);
                }
                if (empty($skuInfo)) {
                    return null;
                }
            }
            if (empty($skuInfo)) {
                $skuInfo = $virtualSkuDao->getOne(['product_id' => $productId, 'is_default' => 1]);
            }
            if (empty($skuInfo)) {
                $skuInfo = $virtualSkuDao->getOne(['product_id' => $productId]);
            }
            if (empty($skuInfo)) {
                // 从产品中心表读取价格和库存作为最终 fallback
                $centerDao = app(CenterProductDao::class);
                $centerProduct = $centerDao->get($productId);
                if ($centerProduct) {
                    if (is_object($centerProduct)) $centerProduct = $centerProduct->toArray();
                    return [
                        'id'       => 0,
                        'unique'   => (string)$productId,
                        'price'    => $centerProduct['price'] ?? 0,
                        'ot_price' => $centerProduct['price'] ?? 0,
                        'cost'     => '0.00',
                        'stock'    => $centerProduct['stock'] ?? 0,
                        'image'    => $centerProduct['image'] ?? '',
                        'suk'      => '默认',
                    ];
                }
                return null;
            }

            if (is_object($skuInfo)) {
                $skuInfo = $skuInfo->toArray();
            }

            // 规格图：SKU 有图用 SKU 图，无图 fallback 商品主图
            $skuImage = (string)($skuInfo['image'] ?? '');
            if ($skuImage === '') {
                try {
                    $center = app(CenterProductDao::class)->get($productId, ['image']);
                    if (is_object($center)) $center = $center->toArray();
                    $skuImage = (string)($center['image'] ?? '');
                } catch (\Throwable $e) {
                    $skuImage = '';
                }
            }

            return [
                'id'                => $skuInfo['id'] ?? 0,
                'unique'            => (string)$skuInfo['id'],  // 使用 id 作为唯一标识
                'price'             => $skuInfo['price'] ?? 0,
                'ot_price'          => $skuInfo['price'] ?? 0,
                'cost'              => '0.00',
                'stock'             => $skuInfo['stock'] ?? 0,
                'image'             => $skuImage,
                'suk'               => $skuInfo['sku_name'] ?? '默认',
                // SKU 级网盘信息：随下单快照存入订单，订单详情直接展示
                'download_url'      => $skuInfo['download_url'] ?? '',
                'download_password' => $skuInfo['download_password'] ?? '',
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * 商品保存后回调
     * 网盘链接/提取码已下移到 SKU 级（eb_pt_virtual_sku），商品级扩展表仅保留全局"自动发货"开关
     */
    public function afterProductSave(int $productId, array $data): void
    {
        $extendData = [
            'product_id'   => $productId,
            'is_auto_send' => $data['is_auto_send'] ?? 1,
        ];

        $dao = app(VirtualProductDao::class);
        $exist = $dao->getOne(['product_id' => $productId]);
        if ($exist) {
            $dao->update($exist['id'], $extendData);
        } else {
            $dao->save($extendData);
        }
    }

    /**
     * 商品删除回调
     * 清理扩展表数据
     */
    public function afterProductDelete(int $productId): void
    {
        $dao = app(VirtualProductDao::class);
        $dao->delete(['product_id' => $productId]);
        // SKU 数据（含网盘链接/提取码）随商品彻底删除一并清理
        app(VirtualProductSkuDao::class)->delete(['product_id' => $productId]);
    }

    /**
     * 网盘商品为网盘类型，无需物流
     * @return bool
     */
    public function isVirtual(): bool
    {
        return true;
    }

    /**
     * 网盘商品发放后不支持退货，仅支持退款（与 VirtualAfterSaleHandler 的 refund_only 售后规则一致）
     * @return bool
     */
    public function supportsReturn(): bool
    {
        return false;
    }

    /**
     * 网盘商品 SKU 编辑页额外字段
     * 网盘链接/提取码下移到 SKU 级，多规格时每个 SKU 单独设置
     * 中台 buildSkuData 依据该声明自动渲染表格列、回填与保存
     * @return array
     */
    public function getSkuExtraFields(): array
    {
        return [
            ['key' => 'download_url', 'label' => '网盘链接', 'slot' => 'fictitious', 'new_default' => ''],
            ['key' => 'download_password', 'label' => '提取码', 'slot' => 'fictitious', 'new_default' => ''],
        ];
    }

    /**
     * 详情扩展数据
     * 从 eb_product 中台主表读取 custom_form、params_list 等展示字段
     */
    public function getDetailExtend(int $productId): array
    {
        try {
            /** @var CenterProductDao $centerProductDao */
            $centerProductDao = app(CenterProductDao::class);
            $product = $centerProductDao->getProductField($productId, ['custom_form', 'params_list', 'label_list', 'protection_list']);
            if (!$product) return [];
            if (is_object($product)) {
                $product = $product->toArray();
            }

            return [
                'custom_form'     => !empty($product['custom_form']) ? (json_decode($product['custom_form'], true) ?: []) : [],
                'params_list'     => !empty($product['params_list']) ? (json_decode($product['params_list'], true) ?: []) : [],
                'label_list'      => !empty($product['label_list']) ? array_map('intval', explode(',', $product['label_list'])) : [],
                'protection_list' => !empty($product['protection_list']) ? array_map('intval', explode(',', $product['protection_list'])) : [],
            ];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * 订单中台 source_type
     */
    public function getOrderSourceType(): string
    {
        return 'virtual_order';
    }

    // ===== 订单管理维度 =====

    /**
     * 创建订单扩展表记录
     * 写 eb_pt_virtual_order，返回记录 id，供调用方在写中台表前拿到 source_id
     * @param string $orderNo 订单号
     * @param array $cartInfo 购物车信息
     * @param array $context 上下文
     * @return int 扩展表记录 id
     */
    public function createOrderExtend(string $orderNo, array $cartInfo, array $context): int
    {
        $orderDao = app(VirtualOrderDao::class);
        $result = $orderDao->save([
            'order_no'   => $orderNo,
            'product_id' => $cartInfo['product_id'] ?? 0,
            'uid'        => $cartInfo['uid'] ?? 0,
            'status'     => VirtualOrderAdapter::EXT_STATUS_PENDING_PAYMENT,
            'add_time'   => time(),
        ]);

        return (int)($result->id ?? 0);
    }

    /**
     * 下单后处理
     * 写网盘商品订单扩展表（幂等：已存在则跳过）
     */
    public function afterOrder(string $orderNo, array $cartInfo, array $context): void
    {
        $orderDao = app(\modules\product\virtual\dao\VirtualOrderDao::class);
        $exist = $orderDao->getOne(['order_no' => $orderNo], 'id');
        if ($exist) {
            return;
        }
        $orderDao->save([
            'order_no'   => $orderNo,
            'product_id' => $cartInfo['product_id'] ?? 0,
            'uid'        => $cartInfo['uid'] ?? 0,
            'status'     => VirtualOrderAdapter::EXT_STATUS_PENDING_PAYMENT,
            'add_time'   => time(),
        ]);
    }

    /**
     * 支付成功履约
     * 网盘商品：自动发货，更新状态为待评价，并同步中台状态
     */
    public function afterPay(string $orderNo, array $cartInfoList): void
    {
        $centerServices = app(OrderCenterServices::class);
        $centerServices->transaction(function () use ($orderNo, $cartInfoList, $centerServices): void {
            $center = app(OrderCenterDao::class)->search([], false)->where('order_no', $orderNo)->lock(true)->find();
            if (!$center || ($center['product_type'] ?? '') !== 'virtual' || (int)($center['pay_status'] ?? 0) !== 1) {
                throw new \RuntimeException('订单尚未支付或订单不存在');
            }
            $orderDao = app(VirtualOrderDao::class);
            $order = $orderDao->search([], false)->where('order_no', $orderNo)->lock(true)->find();
            if (!$order || in_array($order['status'], [VirtualOrderAdapter::EXT_STATUS_CANCELED, VirtualOrderAdapter::EXT_STATUS_REFUNDED], true)) {
                throw new \RuntimeException('订单当前状态不可发放网盘资源');
            }
            // 支付回调可能重复到达；资源已发放的订单保持当前状态不重复处理。
            if (in_array($order['status'], [
                VirtualOrderAdapter::EXT_STATUS_PENDING_REVIEW,
                VirtualOrderAdapter::EXT_STATUS_COMPLETED,
            ], true)) {
                return;
            }
            if (empty($cartInfoList)) {
                throw new \RuntimeException('订单网盘资源快照缺失，暂不能发放资源');
            }
            foreach ($cartInfoList as $item) {
                $attrInfo = $item['attr_info'] ?? [];
                if (empty($attrInfo['download_url'])) {
                    throw new \RuntimeException('网盘商品未配置下载链接，暂不能发放资源');
                }
            }
            $fulfilledAt = time();
            $orderDao->update(['order_no' => $orderNo], [
                'status'    => VirtualOrderAdapter::EXT_STATUS_PENDING_REVIEW,
                'send_time' => (int)($order['send_time'] ?? 0) ?: $fulfilledAt,
            ]);
            // 虚拟商品自动发放等同于发货和收货，写入时间后自动评价任务才能识别待评价订单。
            $centerServices->syncStatus($orderNo, VirtualOrderAdapter::STATUS_PENDING_REVIEW, [
                'delivery_time' => (int)($center['delivery_time'] ?? 0) ?: $fulfilledAt,
                'take_time'     => (int)($center['take_time'] ?? 0) ?: $fulfilledAt,
            ]);
        });
    }

    /**
     * 退款处理
     */
    public function onRefund(string $orderNo, float $refundAmount, array $context): void
    {
        $orderDao = app(\modules\product\virtual\dao\VirtualOrderDao::class);
        $orderDao->update(['order_no' => $orderNo], [
            'status' => VirtualOrderAdapter::EXT_STATUS_REFUNDED,
        ]);

        // 同步中台订单状态
        $centerServices = app(\app\services\order\OrderCenterServices::class);
        $centerServices->syncStatus($orderNo, \app\model\order\OrderCenter::STATUS_REFUNDED);
    }

    // ===== 库存管理维度 =====

    /**
     * 扣减库存
     * 网盘商品从 eb_pt_virtual_sku 表扣减（使用 id 或 product_id 匹配）
     * SKU 扣减成功后同步中台主表冗余总库存（列表页读 eb_product_center.stock；销量在支付成功时累加）
     */
    public function decStock(int $productId, string $skuUnique, int $num): bool
    {
        try {
            $dao = app(\modules\product\virtual\dao\VirtualProductSkuDao::class);
            $where = ['product_id' => $productId];
            if ($skuUnique) {
                $where['id'] = (int)$skuUnique;
            }
            $skuInfo = $dao->getOne($where);
            if (empty($skuInfo)) {
                return false;
            }
            if (is_object($skuInfo)) {
                $skuInfo = $skuInfo->toArray();
            }
            $result = $dao->decStockSafely((int)$skuInfo['id'], $num);
            if (!$result) {
                return false;
            }
            app(CenterProductDao::class)->syncStock($productId, -$num);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * 回滚库存
     * 网盘商品向 eb_pt_virtual_sku 表回滚 stock 与 sales，并同步中台主表冗余总库存
     */
    public function incStock(int $productId, string $skuUnique, int $num): bool
    {
        try {
            $dao = app(\modules\product\virtual\dao\VirtualProductSkuDao::class);
            $where = ['product_id' => $productId];
            if ($skuUnique) {
                $where['id'] = (int)$skuUnique;
            }
            $skuInfo = $dao->getOne($where);
            if (empty($skuInfo)) {
                return false;
            }
            if (is_object($skuInfo)) {
                $skuInfo = $skuInfo->toArray();
            }
            $result = $dao->incStockDecSales(['id' => $skuInfo['id']], $num);
            if (!$result) {
                return false;
            }
            app(CenterProductDao::class)->syncStock($productId, $num);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    // ===== 商品详情规格维度 =====

    /**
     * 获取详情规格数据
     * 网盘商品从 eb_pt_virtual_sku 表读取全部 SKU，动态构建规格组与规格值
     * 每条 productValue 含 SKU 级网盘信息（download_url/download_password），供中台编辑回显与用户端选规格
     * @param int $productId
     * @return array
     */
    public function getDetailSpecs(int $productId): array
    {
        try {
            /** @var \modules\product\virtual\dao\VirtualProductSkuDao $virtualSkuDao */
            $virtualSkuDao = app(\modules\product\virtual\dao\VirtualProductSkuDao::class);
            $skuList = $virtualSkuDao->selectList(['product_id' => $productId], '*', 0, 0, 'is_default DESC, id ASC');
            $skuList = $skuList ? $skuList->toArray() : [];
        } catch (\Throwable $e) {
            $skuList = [];
        }

        if (empty($skuList)) {
            // SKU 表无数据时，从产品中心表读取价格和库存
            try {
                $centerDao = app(\app\dao\product\CenterProductDao::class);
                $center = $centerDao->get($productId);
                if (is_object($center)) $center = $center->toArray();
            } catch (\Throwable $e) {
                $center = null;
            }
            $skuPrice   = $center['price'] ?? '0.00';
            $skuStock   = $center['stock'] ?? 0;
            $skuImage   = $center['image'] ?? '';
            $skuUnique  = (string)$productId;
            $skuCost    = '0.00';
            $skuOtPrice = $center['price'] ?? '0.00';

            $productAttr = [
                [
                    'attr_name'   => '规格',
                    'attr_values' => ['默认'],
                    'attr_value'  => [
                        ['attr' => '默认', 'check' => false, 'pic' => $skuImage],
                    ],
                ],
            ];
            $productValue = [
                '默认' => [
                    'suk'               => '默认',
                    'price'             => $skuPrice,
                    'stock'             => $skuStock,
                    'image'             => $skuImage,
                    'unique'            => $skuUnique,
                    'cost'              => $skuCost,
                    'ot_price'          => $skuOtPrice,
                    'bar_code'          => '',
                    'download_url'      => '',
                    'download_password' => '',
                    'is_show'           => 1,
                ],
            ];

            return ['productAttr' => $productAttr, 'productValue' => $productValue];
        }

        // 解析全部 SKU：规格名映射（attrMap）+ 规格值映射（productValue）
        // 规格图：SKU 有图用 SKU 图，无图 fallback 商品主图
        $mainImage = '';
        try {
            $center = app(CenterProductDao::class)->get($productId, ['image']);
            if (is_object($center)) $center = $center->toArray();
            $mainImage = $center['image'] ?? '';
        } catch (\Throwable $e) {
            $mainImage = '';
        }
        $attrMap = [];
        $productValue = [];
        foreach ($skuList as $sku) {
            $skuAttrs = json_decode($sku['sku_attrs'] ?? '', true);
            // 兼容存量单规格数据（sku_attrs 为空时按 sku_name 兜底）
            if (!is_array($skuAttrs) || empty($skuAttrs)) {
                $skuAttrs = ['规格' => $sku['sku_name'] ?: '默认'];
            }
            // 规格值保序拼接为 suk（与 attr_format 英文逗号分隔一致，供中台编辑回显匹配）
            $suk = implode(',', array_map('strval', array_values($skuAttrs)));
            if ($suk === '') $suk = '默认';
            $skuImage = (string)($sku['image'] ?? '');
            $skuImage = $skuImage !== '' ? $skuImage : $mainImage;

            foreach ($skuAttrs as $attrName => $attrValue) {
                if (!isset($attrMap[$attrName])) $attrMap[$attrName] = [];
                if (!in_array($attrValue, $attrMap[$attrName], true)) $attrMap[$attrName][] = $attrValue;
            }

            $productValue[$suk] = [
                'suk'               => $suk,
                'price'             => $sku['price'] ?? '0.00',
                'stock'             => $sku['stock'] ?? 0,
                'image'             => $skuImage,
                'unique'            => (string)($sku['id'] ?? ''),
                'cost'              => $sku['cost'] ?? '0.00',
                'ot_price'          => $sku['ot_price'] ?? $sku['price'] ?? '0.00',
                'bar_code'          => '',
                'download_url'      => $sku['download_url'] ?? '',
                'download_password' => $sku['download_password'] ?? '',
                'is_show'           => 1,
            ];
        }

        // 构建 productAttr 规格组：规格按钮图取包含该规格值的首条 SKU 图
        $specImageMap = [];
        foreach ($skuList as $sku) {
            $skuAttrs = json_decode($sku['sku_attrs'] ?? '', true);
            if (!is_array($skuAttrs) || empty($skuAttrs)) {
                $skuAttrs = ['规格' => $sku['sku_name'] ?: '默认'];
            }
            $skuImage = (string)($sku['image'] ?? '');
            if ($skuImage === '') continue;
            foreach ($skuAttrs as $attrValue) {
                if (!isset($specImageMap[$attrValue])) {
                    $specImageMap[$attrValue] = $skuImage;
                }
            }
        }
        $productAttr = [];
        foreach ($attrMap as $attrName => $attrValues) {
            $productAttr[] = [
                'attr_name'   => $attrName,
                'attr_values' => $attrValues,
                'attr_value'  => array_map(function ($v) use ($specImageMap) {
                    return ['attr' => $v, 'check' => false, 'pic' => $specImageMap[$v] ?? ''];
                }, $attrValues),
            ];
        }
        if (empty($productAttr)) {
            $productAttr = [[
                'attr_name'   => '规格',
                'attr_values' => ['默认'],
                'attr_value'  => [['attr' => '默认', 'check' => false, 'pic' => '']],
            ]];
        }

        return ['productAttr' => $productAttr, 'productValue' => $productValue];
    }

    // ===== 移动端页面渲染维度 =====

    /**
     * 列表扩展数据
     * 网盘商品使用标准卡片样式，无物流、电子凭证
     */
    public function getListExtend(int $productId): array
    {
        return [
            'card_style' => 'standard',
            'type_label' => '网盘产品',
            'unit_name'  => '件',
        ];
    }

    /**
     * 页面渲染配置
     * 网盘商品使用中台默认 sections 配置，bottom_bar 的 cart_button 由默认配置按 product_type 自动判断（virtual 不显示）
     * 网盘商品履约提示（自动发货）通过 ProductDetailExtend 监听器注入 extensions.fulfillment_preview
     */
    public function getPageConfig(string $scene, string $appVersion = '1.0.0'): array
    {
        return [];
    }

    // ===== 履约维度 =====

    /**
     * 订单履约
     * 网盘商品：更新状态为待评价，并同步中台状态
     * @param string $orderNo 订单号
     * @param array $orderInfo 订单完整信息
     * @return bool
     */
    public function fulfillOrder(string $orderNo, array $orderInfo): bool
    {
        try {
            $orderDao = app(VirtualOrderDao::class);
            $fulfilledAt = time();
            $orderDao->update(['order_no' => $orderNo], [
                'status'    => VirtualOrderAdapter::EXT_STATUS_PENDING_REVIEW,
                'send_time' => $fulfilledAt,
            ]);

            // 网盘商品履约后进入待评价，同步中台订单状态。
            $centerServices = app(\app\services\order\OrderCenterServices::class);
            $centerServices->syncStatus($orderNo, VirtualOrderAdapter::STATUS_PENDING_REVIEW, [
                'delivery_time' => $fulfilledAt,
                'take_time'     => $fulfilledAt,
            ]);

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    // ===== 售后维度 =====

    /**
     * 获取售后规则处理器
     * 网盘商品：支付后自动发货，仅支持退款（不支持退货），需在发放前申请
     * 规则与生命周期回调统一由 VirtualAfterSaleHandler 提供
     * @return AfterSaleHandlerInterface
     */
    public function getAfterSaleRule(): AfterSaleHandlerInterface
    {
        return new \modules\product\virtual\handler\VirtualAfterSaleHandler();
    }

    /**
     * 获取源表订单扩展数据
     * 返回 pt_virtual_order 扩展表记录，供售后规则判定
     * @param string $orderNo 订单号
     * @return array
     */
    public function getOrderExtend(string $orderNo): array
    {
        try {
            $orderDao = app(VirtualOrderDao::class);
            $record = $orderDao->getOne(['order_no' => $orderNo]);
            return is_object($record) ? $record->toArray() : ($record ?: []);
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * 获取兼容性声明
     * 网盘商品不支持拼团/砍价/预售，无需物流，支持有限售后
     * @return array
     */
    public function getCompatibility(): array
    {
        return [
            'supported_trade_modes' => ['normal', 'seckill'],
            'requires_shipping'     => false,
            'supports_pickup'       => false,
            'supports_after_sale'   => true,
        ];
    }

    /**
     * 选择契约（virtual 虚拟：规格矩阵选择 + 可编辑弹窗 + SKU 必选 + 件为单位）
     * @return array
     */
    public function getSelectionSpec(): array
    {
        return [
            'selection_model'     => 'matrix',
            'confirm_window'      => 'editable',
            'required_selections' => ['sku'],
            'cart_unit'           => 'item',
        ];
    }

    /**
     * 加购策略契约（virtual 虚拟：双按钮 + 多品 + 默认文案）
     * @return array
     */
    public function getCartStrategy(): array
    {
        return [
            'cart_mode'   => 'cart',
            'multi_item'  => true,
            'button_text' => '',
        ];
    }

    // ===== 管理端中台维度 =====

    /**
     * 管理端商品编辑扩展信息
     * 委托 virtual 商品服务读取扩展表字段（如 is_auto_send 自动发货配置）
     * @param int $productId 商品ID
     * @return array
     */
    public function getAdminExtendInfo(int $productId): array
    {
        /** @var ProductServices $productServices */
        $productServices = app(ProductServices::class);
        return $productServices->getExtendInfo($productId);
    }

    /**
     * 管理端保存转发
     * 委托 virtual 商品保存服务完成主表 + 中台表 + 扩展表的事务保存
     * @param int $id 商品ID（0 为新增）
     * @param array $data 商品数据
     * @return int|null
     */
    public function saveFromAdmin(int $id, array $data): ?int
    {
        /** @var ProductSaveServices $productSaveServices */
        $productSaveServices = app(ProductSaveServices::class);
        return $productSaveServices->save($id, $data);
    }
}
