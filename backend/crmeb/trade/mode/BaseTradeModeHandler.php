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

namespace crmeb\trade\mode;

/**
 * 交易模式处理器抽象基类
 * 为所有交易模式处理器提供下单生命周期钩子的默认空实现
 * 子类只需覆写关心的方法即可
 * 注意：此类等同于旧版 BaseActivityHandler，仅命名空间变更
 */
abstract class BaseTradeModeHandler implements TradeModeHandlerInterface
{
    /**
     * 获取交易模式标识
     * @return string
     */
    abstract public function getType(): string;

    /**
     * 获取交易模式中文标签
     * @return string
     */
    abstract public function getLabel(): string;

    /**
     * 检查商品是否参与该交易模式
     * @param array $productIds 商品ID数组
     * @return array
     */
    abstract public function getProductExist(array $productIds): array;

    /**
     * 检查单个商品是否参与该交易模式
     * @param int $productId 商品ID
     * @return bool
     */
    abstract public function isProductInActivity(int $productId): bool;

    /**
     * 获取商品的交易模式信息
     * @param int $productId 商品ID
     * @return array|null
     */
    abstract public function getProductActivity(int $productId): ?array;

    /**
     * 获取交易模式ID列表
     * @param array $productIds 商品ID数组
     * @param array $field 需要返回的字段
     * @return array
     */
    abstract public function getActivityIdsList(array $productIds = [], array $field = []): array;

    /**
     * 获取交易模式价
     * 默认返回空数组，表示不参与价格覆盖
     * @param int $productId 中台商品ID
     * @param string $skuUnique SKU唯一码
     * @param int $activityId 交易模式ID
     * @return array
     */
    public function getActivityPrice(int $productId, string $skuUnique, int $activityId): array
    {
        return [];
    }

    /**
     * 校验订单是否满足交易模式规则
     * 默认不限制
     * @param int $productId 中台商品ID
     * @param string $skuUnique SKU唯一码
     * @param int $num 购买数量
     * @param int $activityId 交易模式ID
     * @param int $uid 用户ID
     */
    public function validateOrder(int $productId, string $skuUnique, int $num, int $activityId, int $uid): void
    {
    }

    /**
     * 获取订单 extra_info 中需要记录的交易模式信息
     * @param int $activityId 交易模式ID
     * @return array
     */
    public function getOrderExtraInfo(int $activityId): array
    {
        return [];
    }

    /**
     * 获取该交易模式允许的支付方式
     * 默认不限制
     * @param int $activityId 交易模式ID
     * @return array
     */
    public function getAllowedPayTypes(int $activityId): array
    {
        return [];
    }

    /**
     * 是否不需要真实货币支付
     * 默认false
     * @param int $activityId 交易模式ID
     * @return bool
     */
    public function isFreePay(int $activityId): bool
    {
        return false;
    }

    /**
     * 免费支付使用的支付方式标识
     * 默认 'free'，各交易模式可覆盖
     * @param int $activityId 交易模式ID
     * @return string
     */
    public function getFreePayType(int $activityId): string
    {
        return 'free';
    }

    /**
     * 免费支付完成后的跳转地址
     * 默认空字符串（走默认收银台流程），各交易模式可覆盖
     * @param int $activityId 交易模式ID
     * @return string
     */
    public function getPayRedirectUrl(int $activityId): string
    {
        return '';
    }

    /**
     * 创建订单前校验
     * @param int $productId 中台商品ID
     * @param string $skuUnique SKU唯一码
     * @param int $num 购买数量
     * @param int $activityId 交易模式ID
     * @param int $uid 用户ID
     */
    public function beforeCreateOrder(int $productId, string $skuUnique, int $num, int $activityId, int $uid): void
    {
    }

    /**
     * 创建订单后回调
     * @param string $orderNo 订单号
     * @param int $activityId 交易模式ID
     * @param array $orderInfo 订单信息
     */
    public function afterOrderCreate(string $orderNo, int $activityId, array $orderInfo): void
    {
    }

    /**
     * 支付前校验
     * @param string $orderNo 订单号
     * @param int $activityId 交易模式ID
     * @param array $orderInfo 订单信息
     */
    public function beforeOrderPay(string $orderNo, int $activityId, array $orderInfo): void
    {
    }

    /**
     * 支付处理
     * @param string $orderNo 订单号
     * @param int $activityId 交易模式ID
     * @param array $orderInfo 订单信息
     */
    public function onOrderPay(string $orderNo, int $activityId, array $orderInfo): void
    {
    }

    /**
     * 支付成功后回调
     * @param string $orderNo 订单号
     * @param int $activityId 交易模式ID
     * @param array $orderInfo 订单信息
     */
    public function afterOrderPay(string $orderNo, int $activityId, array $orderInfo): void
    {
    }

    /**
     * 支付失败回调
     * 用于回滚 onOrderPay 中已经执行的资源扣减（如积分、库存等）
     * @param string $orderNo 订单号
     * @param int $activityId 交易模式ID
     * @param array $orderInfo 订单信息
     */
    public function onOrderPayFail(string $orderNo, int $activityId, array $orderInfo): void
    {
    }

    /**
     * 取消前校验
     * @param string $orderNo 订单号
     * @param int $activityId 交易模式ID
     * @param array $orderInfo 订单信息
     */
    public function beforeOrderCancel(string $orderNo, int $activityId, array $orderInfo): void
    {
    }

    /**
     * 取消后回调
     * @param string $orderNo 订单号
     * @param int $activityId 交易模式ID
     * @param array $orderInfo 订单信息
     */
    public function afterOrderCancel(string $orderNo, int $activityId, array $orderInfo): void
    {
    }

    /**
     * 退款前校验
     * @param string $orderNo 订单号
     * @param int $activityId 交易模式ID
     * @param array $orderInfo 订单信息
     */
    public function beforeOrderRefund(string $orderNo, int $activityId, array $orderInfo): void
    {
    }

    /**
     * 退款后回调
     * @param string $orderNo 订单号
     * @param int $activityId 交易模式ID
     * @param array $orderInfo 订单信息
     */
    public function afterOrderRefund(string $orderNo, int $activityId, array $orderInfo): void
    {
    }

    /**
     * 订单完成回调
     * @param string $orderNo 订单号
     * @param int $activityId 交易模式ID
     * @param array $orderInfo 订单信息
     */
    public function onOrderComplete(string $orderNo, int $activityId, array $orderInfo): void
    {
    }

    /**
     * 获取订单详情扩展数据
     * @param string $orderNo 订单号
     * @param int $activityId 交易模式ID
     * @param array $orderInfo 订单信息
     * @return array
     */
    public function getOrderDetailExtend(string $orderNo, int $activityId, array $orderInfo): array
    {
        return [];
    }

    /**
     * 获取交易模式专属按钮
     * @param string $orderNo 订单号
     * @param int $activityId 交易模式ID
     * @param array $orderInfo 订单信息
     * @return array
     */
    public function getActivityButtons(string $orderNo, int $activityId, array $orderInfo): array
    {
        return [];
    }

    /**
     * 获取旧版数字 type 协议
     * 默认 0 表示普通购买，子类参与购物车库存校验时需覆写为对应数字类型
     * @return int
     */
    public function getLegacyType(): int
    {
        return 0;
    }

    /**
     * 是否为营销类交易模式（可参与活动）
     * 默认 false（普通购买、会员购买等基础交易模式），
     * 营销活动处理器（秒杀/砍价/拼团/积分等）覆写返回 true，
     * 商品列表「参与活动」标签、商品操作活动检查等场景仅针对营销类交易模式
     * @return bool
     */
    public function isMarketingActivity(): bool
    {
        return false;
    }

    /**
     * 购物车阶段库存校验
     * 默认返回空数组，表示不参与购物车校验，由调用方走普通购买逻辑
     * @param int $uid 用户ID
     * @param int $cartNum 购买数量
     * @param string $unique SKU唯一码
     * @param int $activityId 交易模式ID
     * @param int $productId 中台商品ID
     * @return array
     */
    public function checkCartStock(int $uid, int $cartNum, string $unique, int $activityId, int $productId): array
    {
        return [];
    }
}
