<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace crmeb\trade\mode;

/**
 * 交易模式处理器接口
 * 定义所有交易模式（即旧称"活动"）必须实现的方法
 * 注意：此接口等同于旧版 ActivityHandlerInterface，仅命名空间变更
 */
interface TradeModeHandlerInterface
{
    /**
     * 获取交易模式标识
     * @return string 例如：seckill, bargain, combination
     */
    public function getType(): string;

    /**
     * 获取交易模式中文标签
     * @return string 例如：秒杀, 砍价, 拼团
     */
    public function getLabel(): string;

    /**
     * 检查商品是否参与该交易模式
     * @param array $productIds 商品ID数组
     * @return array 返回商品ID为key的数组，值为true/false
     *               例如：[1 => true, 2 => false]
     */
    public function getProductExist(array $productIds): array;

    /**
     * 检查单个商品是否参与该交易模式
     * @param int $productId 商品ID
     * @return bool
     */
    public function isProductInActivity(int $productId): bool;

    /**
     * 获取商品的交易模式信息
     * @param int $productId 商品ID
     * @return array|null 交易模式信息，无则返回null
     */
    public function getProductActivity(int $productId): ?array;

    /**
     * 获取交易模式ID列表
     * @param array $productIds 商品ID数组，为空则获取所有
     * @param array $field 需要返回的字段
     * @return array 返回交易模式ID列表
     */
    public function getActivityIdsList(array $productIds = [], array $field = []): array;

    /**
     * 获取交易模式价（活动价）
     * 处理器根据规则返回商品在该交易模式下的价格、库存、限购等信息
     * @param int $productId 中台商品ID
     * @param string $skuUnique SKU唯一码
     * @param int $activityId 交易模式ID
     * @return array 返回价格信息
     *               [
     *                 'unit_price' => '10.00',    // 单价（人民币），普通购买时使用
     *                 'total_price' => '20.00',   // 总价（人民币）
     *                 'currency' => 'money',      // 货币类型：money / integral
     *                 'currency_amount' => 0,     // 当 currency=integral 时，表示积分数量
     *                 'stock' => 100,             // 库存
     *                 'limit' => 1,               // 每人限购，0表示不限购
     *               ]
     */
    public function getActivityPrice(int $productId, string $skuUnique, int $activityId): array;

    /**
     * 校验订单是否满足交易模式规则
     * @param int $productId 中台商品ID
     * @param string $skuUnique SKU唯一码
     * @param int $num 购买数量
     * @param int $activityId 交易模式ID
     * @param int $uid 用户ID
     * @throws \crmeb\exceptions\ApiException 不满足时抛出异常
     */
    public function validateOrder(int $productId, string $skuUnique, int $num, int $activityId, int $uid): void;

    /**
     * 获取订单 extra_info 中需要记录的交易模式信息
     * @param int $activityId 交易模式ID
     * @return array
     */
    public function getOrderExtraInfo(int $activityId): array;

    /**
     * 获取该交易模式允许的支付方式
     * @param int $activityId 交易模式ID
     * @return array 空数组表示不限制，否则只允许指定方式
     */
    public function getAllowedPayTypes(int $activityId): array;

    /**
     * 是否不需要真实货币支付（如积分兑换、0元砍价）
     * @param int $activityId 交易模式ID
     * @return bool
     */
    public function isFreePay(int $activityId): bool;

    /**
     * 免费支付使用的支付方式标识
     * isFreePay 返回 true 时生效，前端据此调用 orderPay 接口完成 0 元支付
     * @param int $activityId 交易模式ID
     * @return string 支付方式标识，默认 'free'
     */
    public function getFreePayType(int $activityId): string;

    /**
     * 免费支付完成后的跳转地址
     * 地址中可含 {order_no} 占位符，前端替换为实际订单号
     * @param int $activityId 交易模式ID
     * @return string 跳转地址，空字符串表示走默认收银台流程
     */
    public function getPayRedirectUrl(int $activityId): string;

    /**
     * 创建订单前校验
     * 例如：砍价是否已完成、预售是否开始、拼团是否可参与
     * @param int $productId 中台商品ID
     * @param string $skuUnique SKU唯一码
     * @param int $num 购买数量
     * @param int $activityId 交易模式ID
     * @param int $uid 用户ID
     */
    public function beforeCreateOrder(int $productId, string $skuUnique, int $num, int $activityId, int $uid): void;

    /**
     * 创建订单后回调
     * 例如：拼团记录创建、砍价订单关联
     * @param string $orderNo 订单号
     * @param int $activityId 交易模式ID
     * @param array $orderInfo 订单信息
     */
    public function afterOrderCreate(string $orderNo, int $activityId, array $orderInfo): void;

    /**
     * 支付前校验
     * 例如：秒杀是否还有库存、拼团是否未过期
     * @param string $orderNo 订单号
     * @param int $activityId 交易模式ID
     * @param array $orderInfo 订单信息
     */
    public function beforeOrderPay(string $orderNo, int $activityId, array $orderInfo): void;

    /**
     * 支付处理
     * 例如：积分扣减、定金转正式订单
     * @param string $orderNo 订单号
     * @param int $activityId 交易模式ID
     * @param array $orderInfo 订单信息
     */
    public function onOrderPay(string $orderNo, int $activityId, array $orderInfo): void;

    /**
     * 支付成功后回调
     * 例如：拼团状态推进、秒杀库存扣减、砍价状态更新
     * @param string $orderNo 订单号
     * @param int $activityId 交易模式ID
     * @param array $orderInfo 订单信息
     */
    public function afterOrderPay(string $orderNo, int $activityId, array $orderInfo): void;

    /**
     * 支付失败回调
     * 用于回滚 onOrderPay 中已经执行的资源扣减（如积分、库存等）
     * @param string $orderNo 订单号
     * @param int $activityId 交易模式ID
     * @param array $orderInfo 订单信息
     */
    public function onOrderPayFail(string $orderNo, int $activityId, array $orderInfo): void;

    /**
     * 取消前校验
     * @param string $orderNo 订单号
     * @param int $activityId 交易模式ID
     * @param array $orderInfo 订单信息
     */
    public function beforeOrderCancel(string $orderNo, int $activityId, array $orderInfo): void;

    /**
     * 取消后回调
     * 例如：返还积分、回滚库存
     * @param string $orderNo 订单号
     * @param int $activityId 交易模式ID
     * @param array $orderInfo 订单信息
     */
    public function afterOrderCancel(string $orderNo, int $activityId, array $orderInfo): void;

    /**
     * 退款前校验
     * @param string $orderNo 订单号
     * @param int $activityId 交易模式ID
     * @param array $orderInfo 订单信息
     */
    public function beforeOrderRefund(string $orderNo, int $activityId, array $orderInfo): void;

    /**
     * 退款后回调
     * 例如：返还积分、回滚库存
     * @param string $orderNo 订单号
     * @param int $activityId 交易模式ID
     * @param array $orderInfo 订单信息
     */
    public function afterOrderRefund(string $orderNo, int $activityId, array $orderInfo): void;

    /**
     * 订单完成回调
     * @param string $orderNo 订单号
     * @param int $activityId 交易模式ID
     * @param array $orderInfo 订单信息
     */
    public function onOrderComplete(string $orderNo, int $activityId, array $orderInfo): void;

    /**
     * 获取订单详情扩展数据
     * 例如：拼团显示团员列表、砍价显示砍价进度、积分显示消耗积分
     * @param string $orderNo 订单号
     * @param int $activityId 交易模式ID
     * @param array $orderInfo 订单信息
     * @return array
     */
    public function getOrderDetailExtend(string $orderNo, int $activityId, array $orderInfo): array;

    /**
     * 获取交易模式专属按钮
     * 例如：拼团显示"邀请好友"、砍价显示"继续砍价"
     * @param string $orderNo 订单号
     * @param int $activityId 交易模式ID
     * @param array $orderInfo 订单信息
     * @return array
     */
    public function getActivityButtons(string $orderNo, int $activityId, array $orderInfo): array;

    /**
     * 获取旧版数字 type 协议
     * 兼容购物车/下单接口的旧版数字交易类型（0=普通、1=秒杀、2=砍价、3=拼团、6=预售）
     * 由 TradeModeRegistry::getByLegacyType() 用于将旧版数字 type 解析为交易模式处理器
     * @return int 旧版数字 type，默认 0 表示普通购买
     */
    public function getLegacyType(): int;

    /**
     * 是否为营销类交易模式（可参与活动）
     * 基础交易模式（普通购买、会员购买等）返回 false，
     * 营销活动（秒杀/砍价/拼团/积分等）返回 true，
     * 商品列表「参与活动」标签、商品操作活动检查等场景仅针对营销类交易模式，
     * 新增营销活动由 Handler 自行声明，无需改动调用方
     * @return bool
     */
    public function isMarketingActivity(): bool;

    /**
     * 购物车阶段库存校验（加购/立即购买时调用）
     * 各交易模式在此校验活动有效性、限购数量与 SKU 库存，不满足时抛出 ApiException
     * 返回空数组表示该交易模式不参与购物车校验，由调用方回退到普通购买逻辑
     * @param int $uid 用户ID
     * @param int $cartNum 购买数量
     * @param string $unique SKU唯一码
     * @param int $activityId 交易模式ID（秒杀/砍价/拼团活动ID，普通购买为0）
     * @param int $productId 中台商品ID
     * @return array 返回 [attrInfo, unique, productInfo, activityInfo]
     *               activityInfo 为交易模式附加信息（如砍价的 bargainUserInfo），无则为空数组
     */
    public function checkCartStock(int $uid, int $cartNum, string $unique, int $activityId, int $productId): array;
}
