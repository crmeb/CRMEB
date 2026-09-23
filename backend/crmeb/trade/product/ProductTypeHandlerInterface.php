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

/**
 * 产品类型处理器接口
 * 每个产品类型必须实现此接口，定义商品/订单/状态机的扩展点
 */
interface ProductTypeHandlerInterface
{
    /**
     * 类型标识（唯一）
     * @return string
     */
    public function getTypeKey(): string;

    /**
     * 类型名称（展示用）
     * @return string
     */
    public function getTypeName(): string;

    /**
     * 获取产品类型能力声明，订单中台只依赖能力而不判断具体类型。
     * @return array
     */
    public function getCapabilities(): array;

    /**
     * 价格能力开关。订单中台据此决定是否执行各类优惠。
     * @return array
     */
    public function getPricingPolicy(): array;

    /**
     * 声明额外必填字段（主表字段，扩展表自行校验）
     * 返回格式：['field' => '错误提示']
     * @return array
     */
    public function getRequiredFields(): array;

    // ===== 商品管理维度 =====

    /**
     * 商品保存后回调（写扩展表）
     * @param int $productId
     * @param array $data
     * @return void
     */
    public function afterProductSave(int $productId, array $data): void;

    /**
     * 商品删除回调
     * @param int $productId
     * @return void
     */
    public function afterProductDelete(int $productId): void;

    // ===== SKU 维度 =====

    /**
     * 获取下单用 SKU 信息
     * 各产品类型从自己的 SKU 表读取，统一返回标准格式
     * @param int $productId 中台商品ID
     * @param string $skuUnique 规格唯一编码，空则取默认SKU
     * @return array|null 标准格式 ['id','unique','price','ot_price','cost','stock','image','suk']，无则 null
     */
    public function getSkuInfo(int $productId, string $skuUnique): ?array;

    // ===== 活动兼容性维度 =====

    /**
     * 该产品类型是否可与某交易模式组合
     * @param string $tradeMode normal/seckill/combination/bargain/advance
     * @return bool
     */
    public function canCombineWith(string $tradeMode): bool;

    /**
     * 获取商品详情扩展数据
     * 产品类型专属的展示字段（protection_list/label_list/params_list/custom_form 等）
     * @param int $productId
     * @return array
     */
    public function getDetailExtend(int $productId): array;

    // ===== 库存管理维度 =====

    /**
     * 扣减库存
     * 各产品类型从自己的 SKU 表扣减库存
     * @param int $productId 中台商品ID
     * @param string $skuUnique 规格唯一编码
     * @param int $num 扣减数量
     * @return bool
     */
    public function decStock(int $productId, string $skuUnique, int $num): bool;

    /**
     * 回滚库存
     * 各产品类型从自己的 SKU 表回滚库存
     * @param int $productId 中台商品ID
     * @param string $skuUnique 规格唯一编码
     * @param int $num 回滚数量
     * @return bool
     */
    public function incStock(int $productId, string $skuUnique, int $num): bool;

    // ===== 物流维度 =====

    /**
     * 获取产品运费/物流配置
     * 从产品类型扩展表读取，供订单计算运费使用
     * @param int $productId
     * @return array { freight: int, temp_id: int, postage: float, weight: float, volume: float }
     */
    public function getShippingInfo(int $productId): array;

    // ===== 商品详情规格维度 =====

    /**
     * 获取商品详情规格数据
     * 返回 productAttr（规格定义）和 productValue（SKU矩阵），用于前端规格选择器渲染
     * 单规格商品返回 [['attr_name'=>'规格','attr_values'=>['默认'],...]],
     *                  ['默认'=>['price'=>...,'stock'=>...,...]]
     * 无规格需求的类型（如卡密、优惠券）返回空数组
     * @param int $productId 中台商品ID
     * @return array ['productAttr' => [...], 'productValue' => [...]]
     */
    public function getDetailSpecs(int $productId): array;

    // ===== 移动端页面渲染维度 =====

    /**
     * 获取列表页扩展数据
     * 返回 type_info 字段，包含卡片样式、类型标签、单位等展示层信息
     * @param int $productId
     * @return array { card_style: string, type_label: string, unit_name: string, ... }
     */
    public function getListExtend(int $productId): array;

    /**
     * 获取页面渲染配置
     * 返回 page_config 声明式 JSON，移动端根据此配置动态渲染页面
     * @param string $scene 场景标识：detail（详情页）| list（列表页）
     * @param string $appVersion 小程序版本号，用于版本协商降级
     * @return array { sections: [...], bottom_bar: {...}, submit_api: string }
     */
    public function getPageConfig(string $scene, string $appVersion = '1.0.0'): array;

    // ===== 订单中台维度 =====

    /**
     * 获取订单中台的 source_type
     * 每个产品类型对应一种订单源表类型，如 'normal_order'、'virtual_order'
     * @return string
     */
    public function getOrderSourceType(): string;

    /**
     * 获取订单详情路由。
     * @param string $orderNo 订单号
     * @return string
     */
    public function getOrderDetailRoute(string $orderNo): string;

    /**
     * 是否为虚拟商品（无需物流、自动发货）
     * 用于下单/发货/退款等流程判断
     * @return bool
     */
    public function isVirtual(): bool;

    /**
     * 是否需要物流配送（快递配送）
     * 实物商品返回 true，虚拟商品返回 false
     * @return bool
     */
    public function requiresShipping(): bool;

    /**
     * 是否支持到店自提
     * 实物商品默认返回 true，虚拟商品返回 false
     * @return bool
     */
    public function supportsPickup(): bool;

    /**
     * 是否支持退货（退货退款售后）
     * 各产品类型独立声明：实物商品默认支持，虚拟类（卡密/网盘/酒店/门票等）按履约特性覆盖为 false，
     * 后续新增产品类型按自身售后能力声明，调用方（订单中台等）不得按类型名硬编码判断
     * @return bool
     */
    public function supportsReturn(): bool;

    /**
     * 获取 SKU 属性编辑页扩展字段定义
     * 返回规格矩阵中需要额外展示的字段列表，由产品类型自身决定
     * @return array 每项包含 key、title、slot 等前端渲染配置
     */
    public function getSkuExtraFields(): array;

    // ===== 履约维度 =====

    /**
     * 订单履约
     * 根据产品类型执行不同的履约逻辑：
     * - 实物商品：创建发货单，等待物流发货
     * - 虚拟商品（卡密/优惠券）：发放商品给用户
     * - 自定义产品类型：执行自定义履约逻辑
     * @param string $orderNo 订单号
     * @param array $orderInfo 订单完整信息
     * @return bool 是否履约成功
     */
    public function fulfillOrder(string $orderNo, array $orderInfo): bool;

    // ===== 售后维度 =====

    /**
     * 获取售后规则处理器
     * 各产品类型返回自己的售后规则实现
     * @return AfterSaleHandlerInterface
     */
    public function getAfterSaleRule(): AfterSaleHandlerInterface;

    /**
     * 获取源表订单扩展数据
     * 售后规则解析时，通过此方法读取各产品类型扩展订单表数据
     * （如卡密 send_time、网盘发放状态、酒店入住时间），供售后规则判定
     * @param string $orderNo 订单号
     * @return array 扩展表订单数据，无则返回空数组
     */
    public function getOrderExtend(string $orderNo): array;

    /**
     * 获取兼容性声明
     * 返回该产品类型支持的交易模式列表和其他兼容性信息
     * @return array ['supported_trade_modes' => [...], 'requires_shipping' => bool, 'supports_pickup' => bool, 'supports_after_sale' => bool]
     */
    public function getCompatibility(): array;

    // ===== 选择与加购契约维度 =====

    /**
     * 获取选择契约
     * 声明详情页的选择模型、确认弹窗模式、必选项、加购单位
     * 前端按此契约动态渲染详情页选择区与加购流程
     * @return array {
     *     selection_model:     string, // 选择模型：matrix（规格矩阵）| temporal（日期时段）| none（无选择）| custom（自定义）
     *     confirm_window:     string, // 确认弹窗：editable（可编辑切换）| readonly（只读回显）| none（无弹窗）
     *     required_selections: array, // 必选项 key 列表，如 ['sku'] | ['calendar','time_slots','price_list']
     *     cart_unit:          string  // 加购单位：item（件）| day（天/租期）| weight（重量）| cycle（周期）
     * }
     */
    public function getSelectionSpec(): array;

    /**
     * 获取加购策略契约
     * 声明加购模式、是否支持多品、按钮自定义文案
     * 前端 productBottom 按此契约动态渲染底部按钮栏
     * @return array {
     *     cart_mode:   string,  // 加购模式：cart（双按钮：加入购物车+立即购买）| direct（单按钮：直接下单）
     *     multi_item:  bool,    // 是否支持多品加购
     *     button_text: string   // 主按钮自定义文案，空表示用前端默认文案
     * }
     */
    public function getCartStrategy(): array;

    /**
     * 获取下单表单组件声明
     * 声明该类型下单流程需要的扩展表单组件 key（如 date_range 日期范围、booking 预约表单），
     * 中台默认 page_config 按声明渲染对应组件，不得硬编码产品类型判断
     * @return array 组件 key 列表，如 ['date_range', 'booking']
     */
    public function getOrderFormComponents(): array;

    // ===== 管理端中台维度 =====

    /**
     * 获取管理端商品编辑所需的扩展信息
     * 中台 CenterProductServices 编辑回显时调用，补充各类型扩展表字段（如 normal 的 weight/volume）
     * @param int $productId 商品ID
     * @return array 扩展信息字段，无扩展信息时返回空数组
     */
    public function getAdminExtendInfo(int $productId): array;

    /**
     * 管理端保存商品时转发到类型模块的保存服务
     * 中台 CenterProductServices 保存时调用；返回 null 表示该类型未提供模块化保存，中台走遗留保存路径
     * @param int $id 商品ID（0 为新增）
     * @param array $data 商品数据
     * @return int|null 保存后的商品ID；null 表示不支持模块化保存
     */
    public function saveFromAdmin(int $id, array $data): ?int;

}
