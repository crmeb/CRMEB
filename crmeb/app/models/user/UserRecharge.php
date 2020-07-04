<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2018/01/05
 */

namespace app\models\user;

use crmeb\basic\BaseModel;
use crmeb\services\MiniProgramService;
use crmeb\services\WechatService;
use crmeb\traits\ModelTrait;

/**
 * TODO 用户充值
 * Class UserRecharge
 * @package app\models\user
 */
class UserRecharge extends BaseModel
{
    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'user_recharge';

    use ModelTrait;

    protected $insert = ['add_time'];

    protected function setAddTimeAttr()
    {
        return time();
    }

    /**
     * 创建充值订单
     * @param $uid
     * @param $price
     * @param string $recharge_type
     * @param int $paid
     * @return UserRecharge|bool|\think\Model
     */
    public static function addRecharge($uid, $price, $recharge_type = 'weixin', $give_price = 0, $paid = 0)
    {
        $order_id = self::getNewOrderId($uid);
        if (!$order_id) return self::setErrorInfo('订单生成失败！');
        $add_time = time();
        return self::create(compact('order_id', 'uid', 'price', 'recharge_type', 'paid', 'add_time', 'give_price'));
    }

    /**
     * 生成充值订单号
     * @param int $uid
     * @return bool|string
     */
    public static function getNewOrderId($uid = 0)
    {
        if (!$uid) return false;
        $count = (int)self::where('uid', $uid)->where('add_time', '>=', strtotime(date("Y-m-d")))->where('add_time', '<', strtotime(date("Y-m-d", strtotime('+1 day'))))->count();
        return 'wx' . date('YmdHis', time()) . (10000 + $count + $uid);
    }

    /**
     * 充值js支付
     * @param $orderInfo
     * @return array|string
     * @throws \Exception
     */
    public static function jsPay($orderInfo)
    {
        return MiniProgramService::jsPay(WechatUser::uidToOpenid($orderInfo['uid']), $orderInfo['order_id'], $orderInfo['price'], 'user_recharge', '用户充值');
    }

    /**
     * 微信H5支付
     * @param $orderInfo
     * @return mixed
     */
    public static function wxH5Pay($orderInfo)
    {
        return WechatService::paymentPrepare(null, $orderInfo['order_id'], $orderInfo['price'], 'user_recharge', '用户充值', '', 'MWEB');
    }

    /**
     * 公众号支付
     * @param $orderInfo
     * @return array|string
     * @throws \Exception
     */
    public static function wxPay($orderInfo)
    {
        return WechatService::jsPay(WechatUser::uidToOpenid($orderInfo['uid'], 'openid'), $orderInfo['order_id'], $orderInfo['price'], 'user_recharge', '用户充值');
    }

    /**
     * //TODO用户充值成功后
     * @param $orderId
     */
    public static function rechargeSuccess($orderId)
    {
        $order = self::where('order_id', $orderId)->where('paid', 0)->find();
        if (!$order) return false;
        $user = User::getUserInfo($order['uid']);
        self::beginTrans();
        $price = bcadd($order['price'], $order['give_price'], 2);
        $res1 = self::where('order_id', $order['order_id'])->update(['paid' => 1, 'pay_time' => time()]);
        $mark = '成功充值余额' . floatval($order['price']) . '元' . ($order['give_price'] ? ',赠送' . $order['give_price'] . '元' : '');
        $res2 = UserBill::income('用户余额充值', $order['uid'], 'now_money', 'recharge', $order['price'], $order['id'], $user['now_money'], $mark);
        $res3 = User::edit(['now_money' => bcadd($user['now_money'], $price, 2)], $order['uid'], 'uid');
        $res = $res1 && $res2 && $res3;
        self::checkTrans($res);
        event('RechargeSuccess', [$order]);
        return $res;
    }

    /**
     * 导入佣金到余额
     * @param $uid 用户uid
     * @param $price 导入金额
     * @return bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function importNowMoney($uid, $price)
    {
        $user = User::getUserInfo($uid);
        self::beginTrans();
        try {
            $broken_time = intval(sys_config('extract_time'));
            $search_time = time() - 86400 * $broken_time;

            //返佣 +
            $brokerage_commission = UserBill::where(['uid' => $uid, 'category' => 'now_money', 'type' => 'brokerage'])
                ->where('add_time', '>', $search_time)
                ->where('pm', 1)
                ->sum('number');
            //退款退的佣金 -
            $refund_commission = UserBill::where(['uid' => $uid, 'category' => 'now_money', 'type' => 'brokerage'])
                ->where('add_time', '>', $search_time)
                ->where('pm', 0)
                ->sum('number');
            $broken_commission = bcsub($brokerage_commission, $refund_commission, 2);
            if ($broken_commission < 0)
                $broken_commission = 0;
            $commissionCount = bcsub($user['brokerage_price'], $broken_commission, 2);
            if ($price > $commissionCount) return self::setErrorInfo('转入金额不能大于可提现佣金！');
            $res1 = User::bcInc($uid, 'now_money', $price, 'uid');
            $res3 = User::bcDec($uid, 'brokerage_price', $price, 'uid');
            $res2 = UserBill::expend('用户佣金转入余额', $uid, 'now_money', 'recharge', $price, 0, $user['now_money'], '成功转入余额' . floatval($price) . '元');
            $extractInfo = [
                'uid' => $uid,
                'real_name' => $user['nickname'],
                'extract_type' => 'balance',
                'extract_price' => $price,
                'balance' => bcsub($user['brokerage_price'], $price, 2),
                'add_time' => time(),
                'status' => 1
            ];
            $res4 = UserExtract::create($extractInfo);
            $res = $res2 && $res1 && $res3;
            self::checkTrans($res);
            if ($res) {
                event('ImportNowMoney', [$uid, $price]);
            }
            return $res;
        } catch (\Exception $e) {
            self::rollbackTrans();
            return self::setErrorInfo($e->getMessage());
        }
    }
}