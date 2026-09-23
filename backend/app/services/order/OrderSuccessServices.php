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
use crmeb\basic\BaseService;
use app\services\pay\PayServices;
use app\services\pay\PayRecordServices;
use crmeb\exceptions\ApiException;

/**
 * 订单支付成功处理
 * Class OrderSuccessServices
 * @package app\services\order
 * @method getOne(array $where, ?string $field = '*', array $with = []) 获取去一条数据
 */
class OrderSuccessServices extends BaseService
{
    /**
     * @var OrderCenterDao
     */
    protected $dao;

    /**
     * OrderSuccessServices constructor.
     * @param OrderCenterDao $dao
     */
    public function __construct(OrderCenterDao $dao)
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
            throw new ApiException('该订单已支付');
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
        if (!\crmeb\services\pay\PaymentDispatch::collecting()) {
            $orderNo = (string)($orderInfo['order_no'] ?? $orderInfo['order_id'] ?? '');
            return app()->make(PayRecordServices::class)->complete($orderNo, 'product',
                (string)($other['trade_no'] ?? ''), function (array $fresh) use ($orderInfo, $paytype, $other) {
                    if ((int)$fresh['pay_status'] !== 0) return true;
                    if (in_array($fresh['status_code'], ['canceled', 'deleted', 'refunded'], true)) throw new ApiException('订单已取消');
                    return $this->paySuccess(array_replace($orderInfo, $fresh), $paytype, $other);
                });
        }
        $updata = ['paid' => 1, 'pay_type' => $paytype, 'pay_time' => time()];
        $orderInfo['pay_time'] = $updata['pay_time'];
        $orderInfo['pay_type'] = $paytype;
        if ($other && isset($other['trade_no'])) {
            $updata['trade_no'] = $other['trade_no'];
        }

        $orderNo = $orderInfo['order_id'] ?? '';
        /** @var OrderCenterServices $centerServices */
        $centerServices = app()->make(OrderCenterServices::class);

        // 直接更新中台表订单状态
        $statusData = [
            'pay_status' => 1,
            'pay_type'   => $paytype,
            'pay_time'   => time(),
        ];
        if (!empty($other['trade_no'])) {
            $extra = $orderInfo['extra_info'] ?? [];
            if (is_string($extra)) {
                $extra = json_decode($extra, true) ?: [];
            }
            $extra['trade_no'] = $other['trade_no'];
            $extra['pay_uid'] = (int)($orderInfo['payment_context']['payer_uid'] ?? $orderInfo['uid']);
            if (isset($extra['payment'])) {
                $extra['payment']['transaction_id'] = $other['trade_no'];
            }
            $orderInfo['extra_info'] = $extra;
            $statusData['extra_info'] = json_encode($extra, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        }
        $res1 = $centerServices->syncStatus($orderNo, 'processing', $statusData);
        if (!$res1) throw new \RuntimeException('订单支付状态更新失败');

        $resPink = true;
        if ($orderInfo['combination_id'] && $res1 && !$orderInfo['refund_status']) {
            // 通过 ActivityDataProxy 事件代理调用拼团创建（避免直接引用 modules 层）
            $_pinkResults = event('ActivityDataProxy', [
                'mode' => 'combination',
                'sub_service' => 'pink',
                'action' => 'createPink',
                'args' => [$orderInfo],
            ]);
            $resPink = proxy_event_result($_pinkResults, false);
            if (!$resPink) throw new \RuntimeException('拼团支付处理失败');
        }
        //订单支付成功事件（插件通过监听 'OrderPayLottery' 处理抽奖缓存等）
        event('OrderPayLottery', [$orderInfo]);
        $orderInfo['send_name'] = $orderInfo['real_name'];
        //订单支付成功后置事件（通知/小票打印等监听器处理）
        event('OrderPaySuccess', [$orderInfo]);
        // 推送订单
        event('OutPush', ['order_pay_push', ['order_id' => (int)$orderInfo['id']]]);

        $res = $res1 && $resPink;
        return false !== $res;
    }

}
