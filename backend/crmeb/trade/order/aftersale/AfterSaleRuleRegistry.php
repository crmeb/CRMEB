<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace crmeb\trade\order\aftersale;

/**
 * 售后规则注册器
 * 按「维度 + 类型标识」二维注册售后规则提供者
 *
 * 维度说明（C7 降维后为两维）：
 * - product_type    产品类型（normal/virtual/cardkey/hotel/ticket）→ 决定能否售后、有哪些售后类型可选
 * - activity_type   活动类型（combination/seckill/bargain）→ 附加约束条件
 *
 * 合并策略见 AfterSaleRuleResolver：一票否决 + 类型交集
 *
 * 未注册 product_type 规则的订单（integral_order/会员单/充值单）默认拒绝售后——
 * 刻意固化：积分/会员/充值三类不支持统一售后流程（refundIntegral 能力已随旧服务下线）
 */
class AfterSaleRuleRegistry
{
    /** @var array<string, array<string, string>> dimension → typeKey → 提供者类名 */
    protected static array $providers = [];

    /**
     * 注册售后规则提供者
     * @param string $dimension 维度：product_type/activity_type
     * @param string $typeKey 该维度下的类型标识（如 product_type 下的 normal/virtual）
     * @param string $providerClass 提供者完整类名（必须实现 AfterSaleHandlerInterface）
     * @throws \InvalidArgumentException
     */
    public static function register(string $dimension, string $typeKey, string $providerClass): void
    {
        if (!is_subclass_of($providerClass, AfterSaleHandlerInterface::class)) {
            throw new \InvalidArgumentException(
                "售后规则提供者 [{$providerClass}] 必须实现 " . AfterSaleHandlerInterface::class
            );
        }
        static::$providers[$dimension][$typeKey] = $providerClass;
    }

    /**
     * 注册产品类型维度规则（便捷方法）
     * @param string $typeKey 产品类型标识（如 normal/virtual/cardkey）
     * @param string $providerClass 提供者完整类名（必须实现 AfterSaleHandlerInterface）
     * @return void
     */
    public static function registerProductType(string $typeKey, string $providerClass): void
    {
        static::register('product_type', $typeKey, $providerClass);
    }

    /**
     * 注册活动类型维度规则（便捷方法）
     * @param string $typeKey 活动类型标识（如 seckill/combination/bargain）
     * @param string $providerClass 提供者完整类名（必须实现 AfterSaleHandlerInterface）
     * @return void
     */
    public static function registerActivityType(string $typeKey, string $providerClass): void
    {
        static::register('activity_type', $typeKey, $providerClass);
    }

    /**
     * 获取规则提供者实例
     * @param string $dimension 维度
     * @param string $typeKey 类型标识
     * @return AfterSaleHandlerInterface|null
     */
    public static function getProvider(string $dimension, string $typeKey): ?AfterSaleHandlerInterface
    {
        if (!isset(static::$providers[$dimension][$typeKey])) {
            return null;
        }
        return app()->make(static::$providers[$dimension][$typeKey]);
    }

    /**
     * 获取指定维度下的所有类型标识
     * @param string $dimension 维度
     * @return array<string>
     */
    public static function getTypeKeysByDimension(string $dimension): array
    {
        return array_keys(static::$providers[$dimension] ?? []);
    }

    /**
     * 获取全部注册信息（维度 → 类型 → 类名），供调试/后台展示
     * @return array
     */
    public static function getRegistered(): array
    {
        return static::$providers;
    }
}
