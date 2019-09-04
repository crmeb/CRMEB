<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2018/01/05
 */

namespace app\ebapi\model\user;

use app\core\model\user\UserBill;
use basic\ModelBasic;
use app\core\util\MiniProgramService;
use traits\ModelTrait;

/** 用户充值
 * Class UserRecharge
 * @package app\ebapi\model\user
 */
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
        $order_id = self::getNewOrderId($uid);
        return self::set(compact('order_id','uid','price','recharge_type','paid'));
    }

    public static function getNewOrderId($uid = 0)
    {
        if(!$uid) return false;
        $count = (int) self::where('add_time',['>=',strtotime(date("Y-m-d"))],['<',strtotime(date("Y-m-d",strtotime('+1 day')))])->count();
        return 'wx1'.date('YmdHis',time()).(10000+$count+$uid);
    }

    public static function jsPay($orderInfo)
    {
        return MiniProgramService::jsPay(WechatUser::uidToOpenid($orderInfo['uid']),$orderInfo['order_id'],$orderInfo['price'],'user_recharge','用户充值');
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

    /*
     * 导入佣金到余额
     * @param int uid 用户uid
     * @param string $price 导入金额
     * */
    public static function importNowMoney($uid,$price){
        $user = User::getUserInfo($uid);
        self::beginTrans();
        try{
            if($price > $user['brokerage_price']) return self::setErrorInfo('转入金额不能大于当前佣金！');
            $res1 = User::bcInc($uid,'now_money',$price,'uid'); //增余额
            $res3 = User::bcDec($uid,'brokerage_price',$price,'uid');//减佣金
            $res2 = UserBill::expend('用户佣金转入余额',$uid,'now_money','recharge',$price,0,$user['now_money'],'成功转入余额'.floatval($price).'元');
            $res = $res2 && $res1 && $res3;
            self::checkTrans($res);
            return $res;
        }catch (\Exception $e){
            self::rollbackTrans();
            return self::setErrorInfo($e->getMessage());
        }
    }
}