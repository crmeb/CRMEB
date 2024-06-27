<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2023 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\services\order;


use app\dao\order\StoreOrderDao;
use app\services\activity\lottery\LuckLotteryServices;
use app\services\activity\combination\StorePinkServices;
use app\services\BaseServices;
use app\services\pay\PayServices;
use crmeb\exceptions\ApiException;

/**
 * Class StoreOrderSuccessServices
 * @package app\services\order
 * @method getOne(array $where, ?string $field = '*', array $with = []) 获取去一条数据
 */
class StoreOrderSuccessServices extends BaseServices
{
    /**
     *
     * StoreOrderSuccessServices constructor.
     * @param StoreOrderDao $dao
     */
    public function __construct(StoreOrderDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 0元支付
     * @param array $orderInfo
     * @param int $uid
     * @return bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function zeroYuanPayment(array $orderInfo, int $uid, string $payType = PayServices::YUE_PAY)
    {
        if ($orderInfo['paid']) {
            throw new ApiException(410265);
        }
        return $this->paySuccess($orderInfo, $payType);//余额支付成功
    }

    /**
     * 支付成功
     * @param array $orderInfo
     * @param string $paytype
     * @param array $other
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function paySuccess(array $orderInfo, string $paytype = PayServices::WEIXIN_PAY, array $other = [])
    {
        $updata = ['paid' => 1, 'pay_type' => $paytype, 'pay_time' => time()];
        $orderInfo['pay_time'] = $updata['pay_time'];
        $orderInfo['pay_type'] = $paytype;
        if ($other && isset($other['trade_no'])) {
            $updata['trade_no'] = $other['trade_no'];
        }
        /** @var StoreOrderCartInfoServices $orderInfoServices */
        $orderInfoServices = app()->make(StoreOrderCartInfoServices::class);
        $orderInfo['storeName'] = $orderInfoServices->getCarIdByProductTitle((int)$orderInfo['id']);
        $res1 = $this->dao->update($orderInfo['id'], $updata);
        $resPink = true;
        if ($orderInfo['combination_id'] && $res1 && !$orderInfo['refund_status']) {
            /** @var StorePinkServices $pinkServices */
            $pinkServices = app()->make(StorePinkServices::class);
            /** @var StoreOrderServices $orderServices */
            $orderServices = app()->make(StoreOrderServices::class);
            $resPink = $pinkServices->createPink($orderServices->tidyOrder($orderInfo, true));//创建拼团
        }
        //缓存抽奖次数 除过线下支付
        if (isset($orderInfo['pay_type']) && $orderInfo['pay_type'] != 'offline') {
            /** @var LuckLotteryServices $luckLotteryServices */
            $luckLotteryServices = app()->make(LuckLotteryServices::class);
            $luckLotteryServices->setCacheLotteryNum((int)$orderInfo['uid'], 'order');
        }
        $orderInfo['send_name'] = $orderInfo['real_name'];
        //订单支付成功后置事件
        event('OrderPaySuccessListener', [$orderInfo]);
        //用户推送消息事件
        event('NoticeListener', [$orderInfo, 'order_pay_success']);
        //支付成功给客服发送消息
        event('NoticeListener', [$orderInfo, 'admin_pay_success_code']);
        // 推送订单
        event('OutPushListener', ['order_pay_push', ['order_id' => (int)$orderInfo['id']]]);

        //自定义消息-订单支付成功
        $orderInfo['time'] = date('Y-m-d H:i:s');
        $orderInfo['phone'] = $orderInfo['user_phone'];
        event('CustomNoticeListener', [$orderInfo['uid'], $orderInfo, 'order_pay_success']);

        //自定义事件-订单支付
        event('CustomEventListener', ['order_pay', [
            'uid' => $orderInfo['uid'],
            'id' => (int)$orderInfo['id'],
            'order_id' => $orderInfo['order_id'],
            'real_name' => $orderInfo['real_name'],
            'user_phone' => $orderInfo['user_phone'],
            'user_address' => $orderInfo['user_address'],
            'total_num' => $orderInfo['total_num'],
            'pay_price' => $orderInfo['pay_price'],
            'pay_postage' => $orderInfo['pay_postage'],
            'deduction_price' => $orderInfo['deduction_price'],
            'coupon_price' => $orderInfo['coupon_price'],
            'store_name' => $orderInfo['storeName'],
            'add_time' => date('Y-m-d H:i:s', $orderInfo['add_time']),
        ]]);

        $res = $res1 && $resPink;
        return false !== $res;
    }

}
