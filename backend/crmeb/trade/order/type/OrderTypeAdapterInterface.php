<?php
declare(strict_types=1);

namespace crmeb\trade\order\type;

/**
 * 订单类型适配器接口
 * 定义各订单类型的完整契约：状态码 + 状态推导 + 分类标识 + 操作按钮 + 模型类
 *
 * 同时作为状态常量唯一数据源 —— 实现本接口即可获得所有状态码常量
 *
 * ========================== 统一状态码体系 ==========================
 *
 * status_code              │ 含义     │ 说明
 * ─────────────────────────┼──────────┼─────────────────────────────────
 * STATUS_PENDING_PAYMENT   │ 待支付   │ 订单创建，等待支付
 * STATUS_PROCESSING        │ 进行中   │ 已支付，履约中（待发货/待收货/待使用/待安装）
 * STATUS_PENDING_REVIEW    │ 待评价   │ 履约完成，等待用户评价
 * STATUS_COMPLETED         │ 已完成   │ 已评价，订单终结
 * STATUS_CANCELED          │ 已取消   │ 订单取消，终结
 * STATUS_AFTER_SALE        │ 售后中   │ 退款/退货/换货/维修处理中（eb_after_sale 表记录详情）
 * STATUS_REFUNDED          │ 已退款   │ 退款完成，订单终结
 * STATUS_DELETED           │ 已删除   │ 软删除（is_del=1），订单终结
 *
 * ========================== 辅助维度 ==========================
 *
 * 维度         │ 字段            │ 值
 * ────────────┼────────────────┼───────────────────────────
 * 是否支付     │ pay_status     │ PAY_UNPAID/PAY_PAID/PAY_PARTIAL_REFUND/PAY_FULL_REFUND
 * 是否发货     │ status_code    │ STATUS_PROCESSING + delivery_time
 * 是否评价     │ status_code    │ STATUS_PENDING_REVIEW（待评）/ STATUS_COMPLETED（已评）
 * 是否完成     │ status_code    │ STATUS_COMPLETED
 * 是否删除     │ is_del         │ 0/1
 * 是否退款     │ pay_status     │ PAY_PARTIAL_REFUND/PAY_FULL_REFUND
 * 是否售后中   │ status_code    │ STATUS_AFTER_SALE
 */
interface OrderTypeAdapterInterface
{
    // ======================== 订单状态码 ========================

    /** 待支付 — 数据库字段 eb_order_center.status_code */
    const STATUS_PENDING_PAYMENT  = 'pending_payment';
    /** 进行中 — 已支付，履约中 */
    const STATUS_PROCESSING       = 'processing';
    /** 待评价 — 履约完成，等待用户评价 */
    const STATUS_PENDING_REVIEW   = 'pending_review';
    /** 已完成 — 已评价，订单终结 */
    const STATUS_COMPLETED        = 'completed';
    /** 已取消 — 订单取消，终结 */
    const STATUS_CANCELED         = 'canceled';
    /** 售后中 — 退款/退货/换货/维修处理中 */
    const STATUS_AFTER_SALE       = 'after_sale';
    /** @deprecated 使用 STATUS_AFTER_SALE 替代，保留兼容历史数据 */
    const STATUS_REFUNDING        = 'refunding';
    /** 已退款 — 退款完成，订单终结 */
    const STATUS_REFUNDED         = 'refunded';
    /** 已删除 — 软删除（is_del=1），订单终结 */
    const STATUS_DELETED          = 'deleted';

    // ======================== 扩展表状态码 ========================

    /** 扩展表已取消状态 — 各 Adapter 状态树的 canceled 节点标识 */
    const EXT_STATUS_CANCELED = 'canceled';

    // ======================== 支付状态码 ========================

    /** 未支付 — 数据库字段 eb_order_center.pay_status */
    const PAY_UNPAID          = 0;
    /** 已支付 */
    const PAY_PAID            = 1;
    /** 部分退款 */
    const PAY_PARTIAL_REFUND  = 2;
    /** 全额退款 */
    const PAY_FULL_REFUND     = 3;

    // ======================== 退款状态码 ========================

    /** 未退款 — 数据库字段 eb_order_center.refund_status */
    const REFUND_NONE      = 0;
    /** 退款申请中 */
    const REFUND_APPLYING  = 1;
    /** 退款成功 */
    const REFUND_SUCCESS   = 2;

    // ======================== 映射表 ========================

    /**
     * 状态码 → 中文标签
     * @var array<string, string>
     */
    const STATUS_MAP = [
        self::STATUS_PENDING_PAYMENT => '待支付',
        self::STATUS_PROCESSING      => '进行中',
        self::STATUS_PENDING_REVIEW  => '待评价',
        self::STATUS_COMPLETED       => '已完成',
        self::STATUS_CANCELED        => '已取消',
        self::STATUS_AFTER_SALE      => '售后中',
        self::STATUS_REFUNDING       => '退款中',
        self::STATUS_REFUNDED        => '已退款',
        self::STATUS_DELETED         => '已删除',
    ];

    /**
     * 支付状态 → 中文标签
     * @var array<int, string>
     */
    const PAY_STATUS_MAP = [
        self::PAY_UNPAID         => '未支付',
        self::PAY_PAID           => '已支付',
        self::PAY_PARTIAL_REFUND => '部分退款',
        self::PAY_FULL_REFUND    => '全额退款',
    ];

    /**
     * 退款状态 → 中文标签
     * @var array<int, string>
     */
    const REFUND_STATUS_MAP = [
        self::REFUND_NONE     => '无退款',
        self::REFUND_APPLYING => '退款中',
        self::REFUND_SUCCESS  => '已退款',
    ];

    // ======================== 接口方法 ========================

    /**
     * 获取源订单类型标识
     * @return string 返回源表类型标识（如 normal_order、virtual_order、cardkey_order）
     */
    public static function getSourceType(): string;

    /**
     * 推导统一状态码
     * 将源订单状态转换为订单中台统一状态码
     * @param array $sourceOrder 源订单数据数组
     * @return string 返回统一状态码
     */
    public function resolveStatusCode(array $sourceOrder): string;

    /**
     * 推导支付状态
     * @param array $sourceOrder 源订单数据数组
     * @return int 返回支付状态码
     */
    public function resolvePayStatus(array $sourceOrder): int;

    /**
     * 推导退款状态
     * @param array $sourceOrder 源订单数据数组
     * @return int 返回退款状态码
     */
    public function resolveRefundStatus(array $sourceOrder): int;

    /**
     * 推导业务状态
     * 返回订单的业务流程状态（如待发货、待收货、拼团中等）
     * 通过事件机制允许扩展模块覆盖默认业务状态
     * @param array $sourceOrder 源订单数据数组
     * @return array|null 返回业务状态数组或null
     */
    public function resolveBusinessStatus(array $sourceOrder): ?array;

    /**
     * 获取订单大类标识
     * @return string 返回订单大类标识（如 goods/points/member/recharge）
     */
    public static function getOrderCategory(): string;

    /**
     * 获取订单大类中文标签
     * @return string 返回订单大类中文名称（如商品订单/积分订单）
     */
    public static function getOrderCategoryLabel(): string;

    /**
     * 获取源订单模型类名
     * 各模块在适配器中声明自身的扩展订单模型类，避免 app/ 层硬编码 modules/ 层引用
     * @return string 返回完整模型类名
     */
    public static function getModelClass(): string;

    /**
     * 获取指定状态码对应的操作按钮列表
     * 从 STATUS_TREE 中读取各状态的静态按钮定义
     * @param string $statusCode 统一状态码
     * @return array 按钮数组
     */
    public function getStatusButtons(string $statusCode): array;

    /**
     * 获取订单操作按钮
     * 根据订单状态和类型特性，返回可执行的操作按钮列表
     * @param array $detail 订单详情数据（含 status_code、delivery_time 等）
     * @return array 按钮数组 [['text'=>..., 'action'=>..., 'params'=>..., 'primary'=>bool], ...]
     */
    public function getOrderButtons(array $detail): array;

    /**
     * 获取订单生命周期进度条
     * 不同订单类型拥有不同的生命周期节点，由适配器自行定义
     * @param array $detail 订单详情数据
     * @return array 进度条结构
     */
    public function getProgress(array $detail): array;

    /**
     * 推进订单状态
     * 一步完成：扩展表 status 推进 + onEnter 回调 + 中台 status_code 同步
     * @param string $orderNo 订单号
     * @param string $extStatus 扩展表状态码（如 EXT_STATUS_CANCELED）
     * @param array $extra 额外字段（如 cancel_time）
     * @return void
     */
    public function advance(string $orderNo, string $extStatus, array $extra = []): void;
}
