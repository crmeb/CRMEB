<?php
declare(strict_types=1);

namespace app\services\user\member;

use app\services\user\UserServices;
use crmeb\trade\mode\BaseTradeModeHandler;
use crmeb\exceptions\ApiException;

/**
 * 会员交易模式处理器
 * 处理会员购买、续费等交易场景
 */
class MemberTradeModeHandler extends BaseTradeModeHandler
{
    /**
     * @var MemberShipServices
     */
    protected MemberShipServices $memberShipServices;

    /**
     * @var UserServices
     */
    protected UserServices $userServices;

    public function __construct()
    {
        $this->memberShipServices = app()->make(MemberShipServices::class);
        $this->userServices = app()->make(UserServices::class);
    }

    /**
     * 获取交易模式标识
     * @return string
     */
    public function getType(): string
    {
        return 'member';
    }

    /**
     * 获取交易模式中文标签
     * @return string
     */
    public function getLabel(): string
    {
        return '会员购买';
    }

    /**
     * 会员交易不涉及商品，返回空
     * @param array $productIds
     * @return array
     */
    public function getProductExist(array $productIds): array
    {
        return [];
    }

    /**
     * 会员交易不涉及具体商品
     * @param int $productId
     * @return bool
     */
    public function isProductInActivity(int $productId): bool
    {
        return false;
    }

    /**
     * 会员交易无商品活动信息
     * @param int $productId
     * @return array|null
     */
    public function getProductActivity(int $productId): ?array
    {
        return null;
    }

    /**
     * 会员交易无活动ID列表
     * @param array $productIds
     * @param array $field
     * @return array
     */
    public function getActivityIdsList(array $productIds = [], array $field = []): array
    {
        return [];
    }

    /**
     * 获取会员等级价格
     * @param int $productId 会员等级ID
     * @param string $skuUnique SKU唯一码
     * @param int $activityId 活动ID（同等级ID）
     * @return array
     */
    public function getActivityPrice(int $productId, string $skuUnique, int $activityId): array
    {
        $levelId = $activityId ?: $productId;
        $memberLevel = $this->memberShipServices->getApiList(['id' => $levelId])->first();

        if (!$memberLevel) {
            return [];
        }

        return [
            'unit_price'      => (string)($memberLevel['price'] ?? 0),
            'total_price'     => (string)($memberLevel['price'] ?? 0),
            'currency'        => 'money',
            'currency_amount' => 0,
            'stock'           => 1,
            'limit'           => 1,
        ];
    }

    /**
     * 校验订单是否满足会员规则
     * @param int $productId 会员等级ID
     * @param string $skuUnique SKU唯一码
     * @param int $num 购买数量
     * @param int $activityId 活动ID
     * @param int $uid 用户ID
     * @throws ApiException
     */
    public function validateOrder(int $productId, string $skuUnique, int $num, int $activityId, int $uid): void
    {
        $levelId = $activityId ?: $productId;

        // 验证会员等级是否存在
        $memberLevel = $this->memberShipServices->getApiList(['id' => $levelId])->first();
        if (!$memberLevel) {
            throw new ApiException('会员等级不存在');
        }

        // 会员购买数量限制为1
        if ($num > 1) {
            throw new ApiException('会员购买数量限制为1');
        }

        // 验证用户是否已拥有更高等级
        $userInfo = $this->userServices->getUserInfo($uid);
        if ($userInfo['member_level'] ?? 0 >= $levelId) {
            throw new ApiException('当前会员等级已高于或等于目标等级');
        }
    }

    /**
     * 获取订单额外信息
     * @param int $activityId 会员等级ID
     * @return array
     */
    public function getOrderExtraInfo(int $activityId): array
    {
        $memberLevel = $this->memberShipServices->getApiList(['id' => $activityId])->first();
        return [
            'level_id'    => $activityId,
            'level_title' => $memberLevel['title'] ?? '',
            'vip_day'     => $memberLevel['vip_day'] ?? 0,
        ];
    }

    /**
     * 支付成功后激活会员等级
     * @param string $orderNo 订单号
     * @param int $activityId 会员等级ID
     * @param array $orderInfo 订单信息
     */
    public function afterOrderPay(string $orderNo, int $activityId, array $orderInfo): void
    {
        $uid = $orderInfo['uid'] ?? 0;
        $levelId = $activityId;

        // 获取会员等级信息
        $memberLevel = $this->memberShipServices->getApiList(['id' => $levelId])->first();
        if (!$memberLevel) {
            return;
        }

        // 激活会员等级（设置过期时间）
        $this->userServices->setMemberOverdueTime(
            $memberLevel['vip_day'] ?? 0,
            $uid,
            2,
            $memberLevel['type'] ?? 'month'
        );
    }

    /**
     * 退款后回调
     * 会员退款后可能需要降级
     * @param string $orderNo 订单号
     * @param int $activityId 交易模式ID
     * @param array $orderInfo 订单信息
     */
    public function afterOrderRefund(string $orderNo, int $activityId, array $orderInfo): void
    {
        // 根据业务需要实现退款后的会员等级降级逻辑
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
        $levelId = $activityId ?: ($orderInfo['level_id'] ?? 0);
        $memberLevel = $this->memberShipServices->getApiList(['id' => $levelId])->first();

        return [
            'level_id'    => $levelId,
            'level_title' => $memberLevel['title'] ?? '',
            'vip_day'     => $memberLevel['vip_day'] ?? 0,
        ];
    }
}
