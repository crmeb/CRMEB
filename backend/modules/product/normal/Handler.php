<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace modules\product\normal;

use app\dao\product\CenterProductDao;
use crmeb\trade\order\aftersale\AfterSaleHandlerInterface;
use crmeb\trade\product\BaseProductTypeHandler;
use modules\product\normal\dao\NormalOrderDao;
use modules\product\normal\dao\NormalProductDao;
use modules\product\normal\dao\NormalProductSkuDao;
use modules\product\normal\handler\NormalOrderAdapter;
use modules\product\normal\service\FreightConfigHelper;

/**
 * 实物商品处理器
 * 默认产品类型，当前为适配层，内部调用 app 现有服务
 * 后续逐步把 app 逻辑内化到本类
 */
class Handler extends BaseProductTypeHandler
{
    /**
     * 普通商品的类型级能力声明。
     *
     * delivery 表示该商品类型支持的能力，具体商品是否启用某种配送方式，
     * 仍由 getDeliveryCapability() 根据商品中台配置返回。
     */
    public function getCapabilities(): array
    {
        return [
            'pricing' => $this->getPricingPolicy(),
            'delivery' => [
                'express'      => true,
                'pickup'       => true,
                'door_service' => true,
            ],
            'inventory' => [
                'check_stock' => true,
            ],
            'order' => [
                'virtual'     => false,
                'status_group' => 'delivery',
            ],
        ];
    }

    /**
     * 普通商品支持完整的通用价格能力。
     */
    public function getPricingPolicy(): array
    {
        return [
            'member_discount' => true,
            'vip_discount'    => true,
            'coupon'          => true,
            'integral'        => true,
            'promotion'       => true,
        ];
    }

    /**
     * 类型标识
     */
    public function getTypeKey(): string
    {
        return 'normal';
    }

    /**
     * 订单中台 source_type
     */
    public function getOrderSourceType(): string
    {
        return 'normal_order';
    }

    /**
     * 类型名称
     */
    public function getTypeName(): string
    {
        return '实物商品';
    }

    /**
     * 额外必填字段
     */
    public function getRequiredFields(): array
    {
        return [
            'cate_id' => '请选择商品分类',
        ];
    }

    /**
     * 商品删除回调
     * 清理实物商品扩展表与 SKU 表数据
     */
    public function afterProductDelete(int $productId): void
    {
        try {
            /** @var NormalProductDao $productDao */
            $productDao = app(NormalProductDao::class);
            $productDao->delete(['product_id' => $productId]);
        } catch (\Throwable $e) {
            // 扩展表不存在时静默处理
        }

        try {
            /** @var NormalProductSkuDao $skuDao */
            $skuDao = app(NormalProductSkuDao::class);
            $skuDao->delete(['product_id' => $productId]);
        } catch (\Throwable $e) {
            // SKU 表不存在时静默处理
        }
    }

    /**
     * 活动兼容性：实物商品支持所有交易模式
     */
    public function canCombineWith(string $tradeMode): bool
    {
        return in_array($tradeMode, ['normal', 'seckill', 'combination', 'bargain', 'integral'], true);
    }

    /**
     * 获取下单用 SKU 信息
     * 实物商品从独立 SKU 表 eb_pt_normal_sku 读取
     * @param int $productId 中台商品ID
     * @param string $skuUnique 规格唯一编码，空则取默认SKU
     * @return array|null
     */
    public function getSkuInfo(int $productId, string $skuUnique): ?array
    {
        try {
            /** @var NormalProductSkuDao $normalSkuDao */
            $normalSkuDao = app(NormalProductSkuDao::class);

            // 指定 SKU 时必须精准匹配，不能在唯一码失效时回退到另一 SKU，避免错价/错库存。
            if ($skuUnique !== '') {
                $skuInfo = $normalSkuDao->getOne([
                    'unique_code' => $skuUnique,
                    'product_id'  => $productId,
                    'is_show'     => 1,
                ]);
                if (empty($skuInfo)) {
                    return null;
                }
            } else {
                // 未指定规格时优先取默认 SKU，再回退到第一条展示中的 SKU。
                $skuInfo = $normalSkuDao->getOne([
                    'product_id'        => $productId,
                    'is_show'           => 1,
                    'is_default_select' => 1,
                ]) ?: $normalSkuDao->getOne(['product_id' => $productId, 'is_show' => 1]);
            }
            if (empty($skuInfo)) {
                return null;
            }

            if (is_object($skuInfo)) {
                $skuInfo = $skuInfo->toArray();
            }

            // SKU suk 可能为 JSON 字符串（如 {"颜色":"黑色"} 或 []），解析为可读规格文本
            $suk = (string)($skuInfo['suk'] ?? '');
            $sukParsed = json_decode($suk, true);
            if (is_array($sukParsed)) {
                $suk = empty($sukParsed) ? '默认' : implode(' / ', array_values($sukParsed));
            } else {
                $suk = $suk !== '' ? $suk : '默认';
            }

            return [
                'id'            => $skuInfo['id'] ?? 0,
                'unique'        => $skuInfo['unique_code'] ?? '',
                'price'         => $skuInfo['price'] ?? 0,
                'ot_price'      => $skuInfo['original_price'] ?? 0,
                'cost'          => $skuInfo['cost'] ?? 0,
                'stock'         => $skuInfo['stock'] ?? 0,
                'image'         => $skuInfo['image'] ?? '',
                'suk'           => $suk,
                'vip_price'     => $skuInfo['vip_price'] ?? 0,
                'brokerage'     => $skuInfo['brokerage'] ?? 0,
                'brokerage_two' => $skuInfo['brokerage_two'] ?? 0,
                'weight'        => $skuInfo['weight'] ?? 0,
                'volume'        => $skuInfo['volume'] ?? 0,
                'bar_code'      => $skuInfo['bar_code'] ?? '',
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    // ===== 订单管理维度（适配层，调用 app 现有服务）=====

    /**
     * 生成核销码（自提订单使用）
     * 公共静态方法，供所有调用方直接使用
     * @return string 8位随机数字
     */
    public static function generateVerifyCode(): string
    {
        return str_pad((string)random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
    }

    /**
     * 创建订单扩展表记录
     * 写 eb_pt_normal_order，返回记录 id，供调用方在写中台表前拿到 source_id
     * store_id/verify_code/shipping_type 已归属中台表实体列，扩展表不双写
     * @param string $orderNo 订单号
     * @param array $cartInfo 购物车信息
     * @param array $context 上下文（pay_price、total_price 等）
     * @return int 扩展表记录 id
     */
    public function createOrderExtend(string $orderNo, array $cartInfo, array $context): int
    {
        // 查询商品扩展表获取安装配置
        $hasInstallation = 0;
        try {
            $productExt = app(NormalProductDao::class)
                ->getOne(['product_id' => $cartInfo['product_id'] ?? 0]);
            if ($productExt) {
                $hasInstallation = (int)($productExt['has_installation'] ?? 0);
            }
        } catch (\Throwable $e) {
            // 扩展表不存在时静默处理，默认为不需要安装
        }

        // 计算价格
        $payPrice = $context['pay_price'] ?? $cartInfo['pay_price'] ?? 0;
        $totalPrice = $context['total_price'] ?? $cartInfo['total_price'] ??
            (($cartInfo['truePrice'] ?? 0) * ($cartInfo['cart_num'] ?? 1));
        $shippingType = (int)($context['shipping_type'] ?? 1);
        $deliveryType = $shippingType === 2 ? 'pickup' : ($shippingType === 3 ? 'door' : 'express');

        // 写扩展表 eb_pt_normal_order
        // 注意：store_id/verify_code/shipping_type 已归属中台表实体列，扩展表不双写；
        // 但 delivery_type 仍保存在扩展表，供列表/详情回填展示文案。
        /** @var NormalOrderDao $orderDao */
        $orderDao = app(NormalOrderDao::class);
        $result = $orderDao->save([
            'order_no'          => $orderNo,
            'product_id'        => $cartInfo['product_id'] ?? 0,
            'uid'               => $cartInfo['uid'] ?? 0,
            'pay_price'         => $payPrice,
            'total_price'       => $totalPrice,
            'status'            => NormalOrderAdapter::EXT_STATUS_PENDING_PAYMENT,
            'has_installation'  => $hasInstallation,
            'activity_type'     => $context['activity_type'] ?? '',
            'delivery_type'     => $deliveryType,
            'add_time'          => time(),
        ]);

        return $result->id ?? 0;
    }

    /**
     * 下单后处理
     * 写扩展表 + 确保中台表 source_type / source_id 正确
     * 新流程：先调用 createOrderExtend 写扩展表，再用扩展表ID写中台表，此处仅做幂等校验
     * 旧流程：扩展表不存在时创建，中台表 source_id 为 0 或错误时回填
     */
    public function afterOrder(string $orderNo, array $cartInfo, array $context): void
    {
        // 写扩展表（如果已存在则幂等跳过）
        /** @var NormalOrderDao $orderDao */
        $orderDao = app(NormalOrderDao::class);
        $exist = $orderDao->getOne(['order_no' => $orderNo], 'id');
        if (!$exist) {
            $this->createOrderExtend($orderNo, $cartInfo, $context);
            $exist = $orderDao->getOne(['order_no' => $orderNo], 'id');
        }
        $extendId = $exist['id'] ?? 0;
        if (!$extendId) {
            return;
        }

        // 确保中台表 source_type / source_id 正确
        // 仅当 source_id 为 0 或与扩展表不一致时才回填（兼容旧流程）
        $centerDao = app(\app\dao\order\OrderCenterDao::class);
        $center = $centerDao->getOne(['order_no' => $orderNo], 'source_id');
        if ($center && (int)($center['source_id'] ?? 0) !== $extendId) {
            $centerDao->update(['order_no' => $orderNo], [
                'source_type' => NormalOrderAdapter::getSourceType(),
                'source_id'   => $extendId,
            ]);
        }
    }

    /**
     * 详情扩展数据
     * 商品级通用展示字段（custom_form / params_list / label_list / protection_list）
     * 优先从商品中台主表 eb_product_center 读取；中台主表为空时回退到 eb_pt_normal 兼容旧数据。
     */
    public function getDetailExtend(int $productId): array
    {
        try {
            /** @var CenterProductDao $centerProductDao */
            $centerProductDao = app(CenterProductDao::class);
            $product = $centerProductDao->getProductField($productId, ['custom_form', 'params_list', 'label_list', 'protection_list']);
            if ($product && is_object($product)) {
                $product = $product->toArray();
            }
            if (empty($product)) {
                return [];
            }

            $result = [
                'custom_form'     => [],
                'params_list'     => [],
                'label_list'      => [],
                'protection_list' => [],
            ];

            if (!empty($product['custom_form'])) {
                $result['custom_form'] = is_string($product['custom_form'])
                    ? (json_decode($product['custom_form'], true) ?: [])
                    : $product['custom_form'];
            }
            if (!empty($product['params_list'])) {
                $result['params_list'] = is_string($product['params_list'])
                    ? (json_decode($product['params_list'], true) ?: [])
                    : $product['params_list'];
            }
            if (!empty($product['label_list'])) {
                $result['label_list'] = array_map('intval', explode(',', $product['label_list']));
            }
            if (!empty($product['protection_list'])) {
                $result['protection_list'] = array_map('intval', explode(',', $product['protection_list']));
            }

            // 兼容旧数据：扩展表字段已迁移到中台主表，无需回退 eb_pt_normal

            return $result;
        } catch (\Throwable $e) {
            return [];
        }
    }

    // ===== 物流维度 =====

    /**
     * 获取配送能力（统一入口，减少调用方出错概率）
     * @param int $productId 商品ID
     * @return array ['is_express', 'is_pickup', 'is_door_service', 'door_postage', 'shipping_info']
     */
    public function getDeliveryCapability(int $productId): array
    {
        try {
            /** @var CenterProductDao $centerDao */
            $centerDao = app(CenterProductDao::class);
            $center = $centerDao->getProductField($productId, ['delivery_methods', 'freight_config']);
            if ($center && is_object($center)) {
                $center = $center->toArray();
            }
            if (!$center) {
                return [
                    'is_express' => false,
                    'is_pickup' => false,
                    'is_door_service' => false,
                    'door_postage' => 0.00,
                    'shipping_info' => ['freight' => 0, 'temp_id' => 0, 'postage' => 0, 'weight' => 0, 'volume' => 0],
                ];
            }

            // 配送方式枚举数组（从 eb_product_center 读取）
            $deliveryMethods = $center['delivery_methods'] ?? ['express'];
            if (is_string($deliveryMethods)) {
                $deliveryMethods = json_decode($deliveryMethods, true) ?: [];
            }
            $deliveryMethods = is_array($deliveryMethods) ? $deliveryMethods : [];
            $isExpress = in_array('express', $deliveryMethods);
            $isPickup = in_array('pickup', $deliveryMethods);
            $isDoorService = in_array('door_service', $deliveryMethods);

            // 运费配置（新双节点格式：express + door_service）
            $freightConfig = FreightConfigHelper::normalize(
                $center['freight_config'] ?? FreightConfigHelper::DEFAULT_CONFIG
            );
            // express 节点：按 type 分支处理（free/fixed/template）
            $expressConfig = $freightConfig['express'] ?? ['type' => 'template'];
            $expressType = (string)($expressConfig['type'] ?? 'template');
            $freight = 1; // 默认包邮（express.type=free）
            $tempId = 0;
            $postage = 0.00;
            if ($expressType === 'fixed') {
                $freight = 2;
                $postage = (float)($expressConfig['postage'] ?? 0);
            } elseif ($expressType === 'template') {
                $freight = 3;
                $tempId = (int)($expressConfig['temp_id'] ?? 0);
            }
            // 上门费用：从 door_service 节点读取，type=free 时强制 0
            $doorConfig = $freightConfig['door_service'] ?? ['type' => 'free', 'postage' => 0];
            $doorPostage = ($doorConfig['type'] ?? 'free') === 'fixed'
                ? (float)($doorConfig['postage'] ?? 0)
                : 0.00;

            // weight/volume 仍从 eb_pt_normal 读取（实物专属字段）
            $extendModel = app(NormalProductDao::class)->getByProductId($productId, ['weight', 'volume']);
            $extendWeight = 0;
            $extendVolume = 0;
            if ($extendModel && is_object($extendModel)) {
                $extendData = $extendModel->toArray();
                $extendWeight = (float)($extendData['weight'] ?? 0);
                $extendVolume = (float)($extendData['volume'] ?? 0);
            }

            return [
                'is_express' => $isExpress,
                'is_pickup' => $isPickup,
                'is_door_service' => $isDoorService,
                'door_postage' => $doorPostage,
                'shipping_info' => [
                    'freight' => $freight,
                    'temp_id' => $tempId,
                    'postage' => $postage,
                    'weight'  => $extendWeight,
                    'volume'  => $extendVolume,
                ],
            ];
        } catch (\Throwable $e) {
            return [
                'is_express' => false,
                'is_pickup' => false,
                'is_door_service' => false,
                'door_postage' => 0.00,
                'shipping_info' => ['freight' => 0, 'temp_id' => 0, 'postage' => 0, 'weight' => 0, 'volume' => 0],
            ];
        }
    }

    /**
     * 获取运费配置
     * 从实物商品扩展表 eb_pt_normal 读取运费相关字段
     * 使用新的 freight_config JSON 字段
     */
    public function getShippingInfo(int $productId): array
    {
        $capability = $this->getDeliveryCapability($productId);
        return $capability['shipping_info'];
    }

    // ===== 库存管理维度 =====

    /**
     * 扣减库存
     * 实物商品从 eb_pt_normal_sku 表扣减（仅扣 stock，避免 inc sales 字段缺失）
     * SKU 扣减成功后同步中台主表冗余总库存（列表页读 eb_product_center.stock；销量在支付成功时累加）
     */
    public function decStock(int $productId, string $skuUnique, int $num): bool
    {
        try {
            /** @var NormalProductSkuDao $dao */
            $dao = app(NormalProductSkuDao::class);
            $skuInfo = $this->getSkuInfo($productId, $skuUnique);
            if (empty($skuInfo)) {
                return false;
            }
            // 直接扣 stock，不依赖 decStockIncSales（避免 inc sales 字段缺失）
            if (!$dao->decStock((int)($skuInfo['id'] ?? 0), $num)) {
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
     * 实物商品向 eb_pt_normal_sku 表回滚 stock，并同步中台主表冗余总库存
     */
    public function incStock(int $productId, string $skuUnique, int $num): bool
    {
        try {
            /** @var NormalProductSkuDao $dao */
            $dao = app(NormalProductSkuDao::class);
            // 回滚既有订单时 SKU 可能已被下架，按唯一码精准读取但不限制 is_show。
            if ($skuUnique !== '') {
                $skuInfo = $dao->getOne(['unique_code' => $skuUnique, 'product_id' => $productId]);
            } else {
                $skuInfo = $dao->getOne(['product_id' => $productId, 'is_default_select' => 1])
                    ?: $dao->getOne(['product_id' => $productId]);
            }
            if (empty($skuInfo)) {
                return false;
            }
            if (is_object($skuInfo)) {
                $skuInfo = $skuInfo->toArray();
            }
            if (!$dao->incStock((int)($skuInfo['id'] ?? 0), $num)) {
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
     * 实物商品从 eb_pt_normal_sku 表构建：多规格返回完整 SKU 矩阵，单规格返回默认规格
     * @param int $productId
     * @return array
     */
    public function getDetailSpecs(int $productId): array
    {
        try {
            /** @var CenterProductDao $centerProductDao */
            $centerProductDao = app(CenterProductDao::class);
            $product = $centerProductDao->getProductField($productId, ['spec_type']);
            if ($product && is_object($product)) {
                $product = $product->toArray();
            }
        } catch (\Throwable $e) {
            $product = null;
        }
        $specType = $product ? (int)($product['spec_type'] ?? 0) : 0;

        try {
            /** @var NormalProductSkuDao $normalSkuDao */
            $normalSkuDao = app(NormalProductSkuDao::class);

            if ($specType) {
                // 多规格
                $skuList = $normalSkuDao->selectList(['product_id' => $productId, 'is_show' => 1]);

                $attrMap = [];
                $productValue = [];

                foreach ($skuList as $sku) {
                    if (is_object($sku)) $sku = $sku->toArray();
                    $suk    = $sku['suk'] ?? '';
                    $detail = $suk ? json_decode($suk, true) : ['规格' => '默认'];
                    if (!is_array($detail)) $detail = ['规格' => '默认'];

                    foreach ($detail as $attrName => $attrVal) {
                        if (!isset($attrMap[$attrName])) {
                            $attrMap[$attrName] = ['values' => []];
                        }
                        if (!in_array($attrVal, $attrMap[$attrName]['values'])) {
                            $attrMap[$attrName]['values'][] = $attrVal;
                        }
                    }

                    $sukKey = implode(',', array_values($detail));
                    $productValue[$sukKey] = [
                        'suk'       => $sukKey,
                        'price'     => $sku['price'] ?? '0.00',
                        'vip_price' => $sku['vip_price'] ?? '0.00',
                        'stock'     => $sku['stock'] ?? 0,
                        'image'     => $sku['image'] ?? '',
                        'unique'    => $sku['unique_code'] ?? '',
                        'cost'      => $sku['cost'] ?? '0.00',
                        'ot_price'  => $sku['original_price'] ?? '0.00',
                        'bar_code'  => $sku['bar_code'] ?? '',
                        'is_show'   => $sku['is_show'] ?? 1,
                    ];
                }

                $productAttr = [];
                foreach ($attrMap as $attrName => $map) {
                    $productAttr[] = [
                        'attr_name'   => $attrName,
                        'attr_values' => $map['values'],
                        'attr_value'  => array_map(function ($v) {
                            return ['attr' => $v, 'check' => false, 'pic' => ''];
                        }, $map['values']),
                    ];
                }

                return ['productAttr' => $productAttr, 'productValue' => $productValue];
            } else {
                // 单规格
                $sku = $normalSkuDao->getOne(['product_id' => $productId, 'is_show' => 1, 'is_default_select' => 1])
                    ?: $normalSkuDao->getOne(['product_id' => $productId, 'is_show' => 1]);
                if (is_object($sku)) $sku = $sku->toArray();

                $skuPrice    = $sku['price'] ?? '0.00';
                $skuVipPrice = $sku['vip_price'] ?? '0.00';
                $skuStock    = $sku['stock'] ?? 0;
                $skuImage    = $sku['image'] ?? '';
                $skuUnique   = $sku['unique_code'] ?? '';
                $skuCost     = $sku['cost'] ?? '0.00';
                $skuOtPrice  = $sku['original_price'] ?? '0.00';

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
                        'suk'       => '默认',
                        'price'     => $skuPrice,
                        'vip_price' => $skuVipPrice,
                        'stock'     => $skuStock,
                        'image'     => $skuImage,
                        'unique'    => $skuUnique,
                        'cost'      => $skuCost,
                        'ot_price'  => $skuOtPrice,
                        'bar_code'  => '',
                        'is_show'   => 1,
                    ],
                ];

                return ['productAttr' => $productAttr, 'productValue' => $productValue];
            }
        } catch (\Throwable $e) {
            return ['productAttr' => [], 'productValue' => []];
        }
    }

    // ===== 移动端页面渲染维度 =====

    /**
     * 列表扩展数据
     * 实物商品使用标准卡片样式
     */
    public function getListExtend(int $productId): array
    {
        return [
            'card_style' => 'standard',
            'type_label' => '实物商品',
            'unit_name'  => '件',
        ];
    }

    /**
     * 页面渲染配置
     * 实物商品使用中台默认 sections 配置（CenterProductServices::buildDefaultPageConfig），
     * 业态差异通过 ProductDetailExtend 监听器向 data.extensions 注入 params_list/protection_list/label_list/custom_form 等字段
     * 返回空数组表示走默认配置；特殊业态（票务/酒店）可在此覆盖返回完整 sections
     */
    public function getPageConfig(string $scene, string $appVersion = '1.0.0'): array
    {
        return [];
    }

    // ===== 售后维度 =====

    /**
     * 获取售后规则处理器
     * 实物商品支持完整售后流程（仅退款/退货退款/换货/补发/维修），7天无理由
     * 规则与生命周期回调统一由 NormalAfterSaleHandler 提供
     * @return AfterSaleHandlerInterface
     */
    public function getAfterSaleRule(): AfterSaleHandlerInterface
    {
        return new \modules\product\normal\handler\NormalAfterSaleHandler();
    }

    /**
     * 获取源表订单扩展数据
     * 返回 pt_normal_order 扩展表记录，供售后规则判定（如 product_id 用于保修期过滤）
     * @param string $orderNo 订单号
     * @return array
     */
    public function getOrderExtend(string $orderNo): array
    {
        try {
            $orderDao = app(NormalOrderDao::class);
            $record = $orderDao->getOne(['order_no' => $orderNo]);
            return is_object($record) ? $record->toArray() : ($record ?: []);
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * 获取兼容性声明
     * 实物商品支持所有交易模式，需要物流，支持售后
     * @return array
     */
    public function getCompatibility(): array
    {
        return [
            'supported_trade_modes' => ['normal', 'seckill', 'combination', 'bargain', 'integral'],
            'requires_shipping'     => true,
            'supports_pickup'       => true,
            'supports_after_sale'   => true,
        ];
    }

    /**
     * 选择契约（normal 实物：规格矩阵选择 + 可编辑弹窗 + SKU 必选 + 件为单位）
     * 显式声明，作为契约参考样本
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
     * 加购策略契约（normal 实物：双按钮 + 多品 + 默认文案）
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
     * 委托 normal 商品服务读取扩展表字段（含 weight/volume 实物专属字段）
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
     * 委托 normal 商品保存服务完成主表 + 中台表 + 扩展表的事务保存
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
