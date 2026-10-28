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

namespace app\services\order;


use app\dao\order\OrderCenterDao;
use app\dao\product\CenterProductDao;
use crmeb\basic\BaseService;
use app\services\user\member\MemberCardServices;
use app\services\user\UserBillServices;
use app\services\user\UserServices;
use app\services\order\AfterSaleServices;
use app\model\order\OrderCenter;
use crmeb\exceptions\ApiException;
use crmeb\utils\Str;
use crmeb\trade\product\ProductTypeRegistry;
use think\facade\Log;

/**
 * 订单收货
 * Class OrderTakeServices
 * @package app\services\order
 * @method get(int $id, ?array $field = []) 获取一条
 */
class OrderTakeServices extends BaseService
{
    /**
     * @var OrderCenterDao
     */
    protected $dao;

    /**
     * 构造方法
     * OrderTakeServices constructor.
     * @param OrderCenterDao $dao
     */
    public function __construct(OrderCenterDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 小程序订单服务收货
     * @param $merchant_trade_no
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     *
     * @date 2023/05/18
     * @author yyw
     */
    public function miniOrderTakeOrder($merchant_trade_no)
    {
        // 微信小程序购物订单回调收货：中台订单无 merchant_trade_no/pid 承载（旧表字段），
        // 查不到对应订单时安全早退，避免 SQL Unknown column / 未定义方法断裂
        $order = $this->dao->getOne(['order_no' => $merchant_trade_no]);
        if (!$order) {
            Log::warning('[OrderTake] miniOrderTakeOrder 未匹配中台订单，trade_no=' . $merchant_trade_no);
            return true;
        }
        if (is_object($order)) {
            $order = $order->toArray();
        }
        $this->takeOrder($order['order_no'], $order['uid']);
    }

    /**
     * 用户订单收货
     * @param string $uni 订单号
     * @param int $uid 用户 ID
     * @return array 订单数据
     */
    public function takeOrder(string $uni, int $uid)
    {
        $order = $this->dao->getOne(['order_no' => $uni]);
        if (!$order) {
            throw new ApiException('订单不存在');
        }
        if (is_object($order)) {
            $order = $order->toArray();
        }
        // 订单归属人可确认收货；送礼订单的领取人也可确认收货
        if ((int)($order['uid'] ?? 0) !== $uid) {
            $extra = $order['extra_info'] ?? [];
            if (is_string($extra)) {
                $extra = json_decode($extra, true) ?: [];
            }
            $giftUid = (int)($order['gift_uid'] ?? $extra['gift']['gift_uid'] ?? ($extra['gift_uid'] ?? 0));
            if ($giftUid !== $uid) {
                throw new ApiException('订单不存在');
            }
        }
        // 售后拦截：存在处理中售后单不可确认收货（售后引擎统一判断）
        $orderIsRefund = app()->make(AfterSaleServices::class)->hasActiveByOrderId((int)$order['id']);
        if($orderIsRefund){
            throw new ApiException('订单退款中，不能收货');
        }
        $isNormalProduct = (string)($order['product_type'] ?? '') === 'normal'
            || (string)($order['source_type'] ?? '') === 'normal_order';
        if ($isNormalProduct) {
            // 普通实物必须在已发货（pending_receive）阶段确认收货，防止待发货订单被提前完成或重复发放权益。
            try {
                $normalExtend = app()->make(ProductTypeRegistry::class)->get('normal')->getOrderExtend($order['order_no']);
                if ((int)($order['take_rewards_given'] ?? 0) === 1
                    && in_array((string)($normalExtend['status'] ?? ''), ['pending_install', 'pending_review', 'completed'], true)) {
                    return $order;
                }
                if (($normalExtend['status'] ?? '') !== 'pending_receive') {
                    throw new ApiException('订单尚未发货或已确认收货');
                }
            } catch (ApiException $e) {
                throw $e;
            } catch (\Throwable $e) {
                throw new ApiException('订单状态异常，暂不能收货');
            }
        }
        // 中台状态检查：已支付进行中（待发货/已发货统一 processing 粒度）才可确认收货
        if ($order['status_code'] !== OrderCenter::STATUS_PROCESSING
            || !in_array((int)$order['pay_status'], [OrderCenter::PAY_PAID, OrderCenter::PAY_PARTIAL_REFUND], true)) {
            throw new ApiException('订单状态错误');
        }
        // 普通实物订单需要由产品类型监听器继续分流到“待评价/待安装”；不能在这里提前标记 completed。
        $takeStatus = $isNormalProduct ? OrderCenter::STATUS_PROCESSING : OrderCenter::STATUS_COMPLETED;
        $res = $this->storeProductOrderUserTakeDelivery($order, true, $takeStatus);
        if (!$res) {
            throw new ApiException('收货失败');
        }
        // 普通实物由 OrderTakeListener 按安装配置推进状态；其他产品类型保持原有完成逻辑。
        if (!$isNormalProduct) {
            try {
                \crmeb\trade\order\type\OrderTypeRegistry::getAdapter($order['source_type'] ?? '')
                    ->advance($order['order_no'], 'completed');
            } catch (\Throwable $e) {
                Log::warning('[OrderTake] advance ext completed failed: ' . $e->getMessage());
            }
            try {
                app()->make(OrderLifecycleServices::class)->logComplete($order);
            } catch (\Throwable $e) {
                Log::warning('[OrderTake] logComplete failed: ' . $e->getMessage());
            }
        }
        return $order;
    }

    /**
     * 订单确认收货
     * @param $order
     * @param bool $isTran 是否开启事务
     * @param string|null $takeStatus 用户收货时在订单锁内同步的状态
     * @return bool
     */
    public function storeProductOrderUserTakeDelivery($order, bool $isTran = true, ?string $takeStatus = null)
    {
        // DAO get() 返回 Model（如核销链路直接传入），事务内 array_replace 要求 array
        if (is_object($order)) {
            $order = $order->toArray();
        }
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $userInfo = $userServices->get((int)$order['uid']);
        // get() 返回 Model，赠送积分/经验等方法签名要求 array
        if (is_object($userInfo)) {
            $userInfo = $userInfo->toArray();
        }
        // 从订单中台 extra_info.items 组装商品标题（旧 cart_info 表已废弃）
        /** @var OrderCenterDao $centerDao */
        $centerDao = app()->make(OrderCenterDao::class);
        $centerOrder = $centerDao->get(['order_no' => $order['order_no'] ?? ($order['order_id'] ?? '')], ['extra_info']);
        $storeName = '';
        if ($centerOrder) {
            $extra = $centerOrder['extra_info'] ?? [];
            if (is_string($extra)) {
                $extra = json_decode($extra, true) ?: [];
            }
            foreach ($extra['items'] ?? [] as $item) {
                $name = $item['store_name'] ?? '';
                if ($name !== '') {
                    $storeName .= $name . '|';
                }
            }
            $storeName = $storeName !== '' ? substr($storeName, 0, -1) : '';
        }
        $storeTitle = Str::substrUTf8($storeName, 20, 'UTF-8', '');

        $res = $this->transaction(function () use (&$order, $userInfo, $storeTitle, $takeStatus) {
            $centerDao = app()->make(OrderCenterDao::class);
            $locked = $centerDao->getByIdForUpdate((int)$order['id']);
            if (!$locked) {
                throw new ApiException('订单不存在');
            }
            $order = array_replace($order, $locked->toArray());
            $order['order_id'] = $order['order_no'];
            if (!in_array((int)$order['pay_status'], [OrderCenter::PAY_PAID, OrderCenter::PAY_PARTIAL_REFUND], true)) {
                throw new ApiException('订单当前状态不能发放收货奖励');
            }
            if ((int)$order['take_rewards_given'] === 1) {
                return true;
            }
            if ($takeStatus !== null) {
                if ($order['status_code'] !== OrderCenter::STATUS_PROCESSING
                    || app()->make(AfterSaleServices::class)->hasActiveByOrderId((int)$order['id'])) {
                    throw new ApiException('订单状态已变化，请刷新后重试');
                }
                $order['take_time'] = time();
                if (!app()->make(OrderCenterServices::class)->syncStatus($order['order_no'], $takeStatus, [
                    'take_time' => $order['take_time'],
                ])) {
                    throw new ApiException('收货失败');
                }
            }
            //赠送积分（佣金发放已由分销插件在支付环节处理）
            $res1 = $this->gainUserIntegral($order, $userInfo, $storeTitle);
            //经验
            $res3 = $this->gainUserExp($order, $userInfo);
            if (!($res1 && $res3)) {
                throw new ApiException('收货失败');
            }
            app()->make(OrderRefundRewardServices::class)->reconcile($order, (string)($order['refund_price'] ?? '0'));
            if (!$centerDao->update((int)$order['id'], ['take_rewards_given' => 1])) {
                throw new \RuntimeException('收货奖励幂等标记更新失败');
            }
            return true;
        }, $isTran);

        if ($res) {
            try {
                // 收货成功后置队列（消息发送已移至 OrderTakeListener）
                event('OrderTake', [$order, $userInfo, $storeTitle]);
            } catch (\Throwable $exception) {
                Log::error('[OrderTake] 收货后置事件失败 order_no=' . $order['order_no'] . ' type=' . get_class($exception));
                throw $exception;
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * 赠送积分
     * @param $order
     * @param $userInfo
     * @param $storeTitle
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function gainUserIntegral($order, $userInfo, $storeTitle)
    {
        $res2 = $res3 = false;
        $integral = 0;
        if (!$userInfo) {
            return true;
        }
        // 营销产品送积分
        if (isset($order['combination_id']) && $order['combination_id']) {
            return true;
        }
        if (isset($order['seckill_id']) && $order['seckill_id']) {
            return true;
        }
        if (isset($order['bargain_id']) && $order['bargain_id']) {
            return true;
        }
        /** @var UserBillServices $userBillServices */
        $userBillServices = app()->make(UserBillServices::class);
        // 购买商品送积分：中台订单无 gain_integral 实体列，从 extra_info.items 的商品级
        // give_integral 快照按数量聚合（下单时由 buildOrderItemsForExtra 写入）
        $extraInfo = $order['extra_info'] ?? [];
        if (is_string($extraInfo)) {
            $extraInfo = json_decode($extraInfo, true) ?: [];
        }
        $items = $extraInfo['items'] ?? [];
        // 修复前下的旧订单快照无 give_integral 字段，回查中台商品当前配置兜底
        $missingIds = [];
        foreach ($items as $item) {
            if (!array_key_exists('give_integral', $item) && (int)($item['product_id'] ?? 0) > 0) {
                $missingIds[$item['product_id']] = true;
            }
        }
        $productGiveMap = [];
        if ($missingIds) {
            /** @var CenterProductDao $centerProductDao */
            $centerProductDao = app()->make(CenterProductDao::class);
            $productGiveMap = $centerProductDao->getColumn([['id', 'in', array_keys($missingIds)]], 'give_integral', 'id') ?: [];
        }
        $gainIntegral = '0';
        foreach ($items as $item) {
            $give = array_key_exists('give_integral', $item)
                ? (float)($item['give_integral'] ?? 0)
                : (float)($productGiveMap[$item['product_id'] ?? 0] ?? 0);
            if ($give > 0) {
                $num = max(1, (int)($item['cart_num'] ?? 1));
                $gainIntegral = bcadd($gainIntegral, bcmul((string)$give, (string)$num, 0), 0);
            }
        }
        if ((float)$gainIntegral > 0) {
            // 购买商品送积分
            $res2 = $userBillServices->billIntegralIncome('pay_give_integral', $order['uid'], $gainIntegral, $order['id']);
        } else {
            $res2 = true;
        }
        $order_integral = 0;
        // 计算订单赠送积分 按支付比例计算赠送
        $order_give_integral = sys_config('order_give_integral');
        if ($order['pay_price'] && $order_give_integral) {
            //会员消费返积分翻倍
            if ($userInfo['is_money_level'] > 0) {
                //看是否开启消费返积分翻倍奖励
                /** @var MemberCardServices $memberCardService */
                $memberCardService = app()->make(MemberCardServices::class);
                $integral_rule_number = $memberCardService->isOpenMemberCard('integral');
                if ($integral_rule_number) {
                    $order_integral = bcmul((string)$order['pay_price'], (string)$integral_rule_number, 2);
                }
            }
            $order_integral = bcmul((string)$order_give_integral, (string)($order_integral ?: $order['pay_price']), 0);
            // 订单赠送积分
            $res3 = $userBillServices->billIntegralIncome('order_give_integral', $order['uid'], $order_integral, $order['id']);
        } else {
            $res3 = true;
        }
        $give_integral = (int)$order_integral + (int)$gainIntegral;
        if ($give_integral > 0 && $res2 && $res3) {
            //触发积分变动事件（消息发送已移至 UserIntegralChangeListener；中台无 gain_integral 列，累计值以 bill 流水为准不再回写）
            event('UserIntegralChange', [(int)$order['uid'], $order, $storeTitle, $give_integral, $order_integral]);

            return true;
        }
        return true;
    }

    /**
     * 赠送经验
     * @param $order
     * @param $userInfo
     * @return bool
     */
    public function gainUserExp($order, $userInfo)
    {
        if (!$userInfo) {
            return true;
        }
        //用户等级是否开启
        if (!sys_config('member_func_status', 1)) {
            return true;
        }
        /** @var UserBillServices $userBillServices */
        $userBillServices = app()->make(UserBillServices::class);
        $order_exp = 0;
        $res = true;
        // 下单送经验
        $order_give_exp = sys_config('order_give_exp');
        if ($order['pay_price'] && $order_give_exp) {
            $order_exp = bcmul($order_give_exp, (string)$order['pay_price'], 2);
            $res = $userBillServices->billExpIncome('order_give_exp', $order['uid'], $order_exp, $order['id']);
        }
        return $res;
    }

    /**
     * 自动收货
     * 发货超过配置天数（system_delivery_time，0 为关闭）的已支付进行中订单自动确认收货；
     * 逐单复用用户手动收货链路 takeOrder()，共享其发货校验、售后拦截与收货奖励幂等守卫
     * @return bool
     */
    public function autoTakeOrder()
    {
        $systemDeliveryTime = sys_config('system_delivery_time', 0);
        //0为取消自动收货功能
        if ($systemDeliveryTime == 0) {
            return true;
        }
        $deadline = bcsub((string)time(), bcmul((string)$systemDeliveryTime, '86400'));
        // 已发货（delivery_time 有值）且超过自动收货期限的进行中订单；待发货/未支付订单不带 delivery_time，天然排除
        $orderList = $this->dao->getDb()
            ->where('status_code', OrderCenter::STATUS_PROCESSING)
            ->where('pay_status', 'in', [OrderCenter::PAY_PAID, OrderCenter::PAY_PARTIAL_REFUND])
            ->where('is_del', 0)
            ->where('delivery_time', '>', 0)
            ->where('delivery_time', '<=', $deadline)
            ->field('id,order_no,uid')
            ->select()->toArray();
        foreach ($orderList as $order) {
            try {
                $this->takeOrder((string)$order['order_no'], (int)$order['uid']);
            } catch (ApiException $e) {
                // 业务守卫拦截（未发货/退款中/已收货等）属正常跳过，记 warning 供运营核对
                Log::warning('[autoTakeOrder] 跳过订单 order_no=' . $order['order_no'] . '，原因：' . $e->getMessage());
            } catch (\Throwable $e) {
                Log::error('自动收货失败,订单号:' . $order['order_no'] . ',失败原因：' . $e->getMessage() . '|' . $e->getFile() . '|' . $e->getLine());
            }
        }
        return true;
    }

}
