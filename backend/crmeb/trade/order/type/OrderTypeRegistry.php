<?php
declare(strict_types=1);

namespace crmeb\trade\order\type;

/**
 * 订单类型注册器
 * 负责管理各业务模块的订单类型适配器注册
 * 采用主动注册模式，由各模块在其App.php中调用register()方法完成注册
 */
class OrderTypeRegistry
{
    /**
     * 已注册的适配器映射表
     * @var array<string, string> 键为sourceType，值为适配器类名
     */
    protected static array $adapters = [];

    /**
     * 分类映射缓存
     * @var array<string, string>|null
     */
    protected static ?array $categoryMapCache = null;

    /**
     * 注册适配器
     * 由各模块在register()/boot()阶段调用，完成订单类型的主动注册
     * @param string $sourceType 源订单类型标识
     * @param string $adapterClass 适配器完整类名
     */
    public static function register(string $sourceType, string $adapterClass): void
    {
        static::$adapters[$sourceType] = $adapterClass;
        static::$categoryMapCache = null;
    }

    /**
     * 获取适配器实例
     * 通过容器实例化适配器，支持依赖注入
     * @param string $sourceType 源订单类型标识
     * @return OrderTypeAdapterInterface 返回适配器实例
     * @throws \RuntimeException 当源类型未注册时抛出异常
     */
    public static function getAdapter(string $sourceType): OrderTypeAdapterInterface
    {
        if (!isset(static::$adapters[$sourceType])) {
            throw new \RuntimeException("未注册的源表类型: {$sourceType}");
        }
        $class = static::$adapters[$sourceType];
        return $class::instance();
    }

    /**
     * 推导统一状态码
     * 根据源订单类型和数据推导订单中台统一状态码
     * @param string $sourceType 源订单类型标识
     * @param array $sourceOrder 源订单数据数组
     * @return string 返回统一状态码
     */
    public static function resolveStatusCode(string $sourceType, array $sourceOrder): string
    {
        return static::getAdapter($sourceType)->resolveStatusCode($sourceOrder);
    }

    /**
     * 推导支付状态
     * 根据源订单类型和数据推导支付状态
     * @param string $sourceType 源订单类型标识
     * @param array $sourceOrder 源订单数据数组
     * @return int 返回支付状态码
     */
    public static function resolvePayStatus(string $sourceType, array $sourceOrder): int
    {
        return static::getAdapter($sourceType)->resolvePayStatus($sourceOrder);
    }

    /**
     * 推导退款状态
     * 根据源订单类型和数据推导退款状态
     * @param string $sourceType 源订单类型标识
     * @param array $sourceOrder 源订单数据数组
     * @return int 返回退款状态码
     */
    public static function resolveRefundStatus(string $sourceType, array $sourceOrder): int
    {
        return static::getAdapter($sourceType)->resolveRefundStatus($sourceOrder);
    }

    /**
     * 推导业务状态
     * 根据源订单类型和数据推导业务流程状态
     * @param string $sourceType 源订单类型标识
     * @param array $sourceOrder 源订单数据数组
     * @return array|null 返回业务状态数组或null
     */
    public static function resolveBusinessStatus(string $sourceType, array $sourceOrder): ?array
    {
        return static::getAdapter($sourceType)->resolveBusinessStatus($sourceOrder);
    }

    /**
     * 获取订单大类映射表
     * 从所有已注册适配器中聚合分类信息，支持缓存避免重复计算
     * @return array<string, string> 返回[分类标识 => 中文标签]的映射数组
     */
    public static function getCategoryMap(): array
    {
        if (static::$categoryMapCache !== null) {
            return static::$categoryMapCache;
        }

        $categoryMap = [];
        foreach (static::$adapters as $adapterClass) {
            $category = $adapterClass::getOrderCategory();
            $label = $adapterClass::getOrderCategoryLabel();
            if ($category && $label) {
                $categoryMap[$category] = $label;
            }
        }

        static::$categoryMapCache = $categoryMap;
        return $categoryMap;
    }

    /**
     * 根据源类型获取订单大类
     * @param string $sourceType 源订单类型标识
     * @return string 返回订单大类标识
     */
    public static function getCategoryBySourceType(string $sourceType): string
    {
        if (!isset(static::$adapters[$sourceType])) {
            throw new \RuntimeException("未注册的源表类型: {$sourceType}");
        }
        return static::$adapters[$sourceType]::getOrderCategory();
    }

    /**
     * 获取已注册的源类型列表
     * @return array<string> 返回所有已注册的源类型标识数组
     */
    public static function getRegisteredSourceTypes(): array
    {
        return array_keys(static::$adapters);
    }
}
