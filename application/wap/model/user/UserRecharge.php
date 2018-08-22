<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2018/01/05
 */

namespace app\wap\model\user;

use app\wap\model\user\WechatUser;
use basic\ModelBasic;
use service\WechatService;
use traits\ModelTrait;

class UserRecharge extends ModelBasic
{
    use ModelTrait;

    protected $insert = ['add_time'];

    protected function setAddTimeAttr()
    {
        return time();
    }

    public static function addRecharge($uid,$price,$recharge_type = 'weixin',$paid = 0)
    {
        $order_id = self::getNewOrderId();
        return self::set(compact('order_id','uid','price','recharge_type','paid'));
    }

    public static function getNewOrderId()
    {
        $count = (int) self::where('add_time',['>=',strtotime(date("Y-m-d"))],['<',strtotime(date("Y-m-d",strtotime('+1 day')))])->count();
        return 'wx1'.date('YmdHis',time()).(10000+$count+1);
    }

    public static function jsPay($orderInfo)
    {
        return WechatService::jsPay(WechatUser::uidToOpenid($orderInfo['uid']),$orderInfo['order_id'],$orderInfo['price'],'user_recharge','用户充值');
    }

    /**
     * //TODO用户充值成功后
     * @param $orderId
     */
    public static function rechargeSuccess($orderId)
    {
        $order = self::where('order_id',$orderId)->where('paid',0)->find();
        if(!$order) return false;
        $user = User::getUserInfo($order['uid']);
        self::beginTrans();
        $res1 = self::where('order_id',$order['order_id'])->update(['paid'=>1,'pay_time'=>time()]);
        $res2 = UserBill::income('用户余额充值',$order['uid'],'now_money','recharge',$order['price'],$order['id'],$user['now_money'],'成功充值余额'.floatval($order['price']).'元');
        $res3 = User::edit(['now_money'=>bcadd($user['now_money'],$order['price'],2)],$order['uid'],'uid');
        $res = $res1 && $res2 && $res3;
        self::checkTrans($res);
        return $res;
    }
}