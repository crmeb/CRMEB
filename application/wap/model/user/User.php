<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/21
 */

namespace app\wap\model\user;


use basic\ModelBasic;
use service\SystemConfigService;
use think\Request;
use think\response\Redirect;
use think\Session;
use think\Url;
use traits\ModelTrait;

class User extends ModelBasic
{
    use ModelTrait;

    protected $insert = ['add_time','add_ip','last_time','last_ip'];

    protected function setAddTimeAttr($value)
    {
        return time();
    }

    protected function setAddIpAttr($value)
    {
        return Request::instance()->ip();
    }

    protected function setLastTimeAttr($value)
    {
        return time();
    }

    protected function setLastIpAttr($value)
    {
        return Request::instance()->ip();
    }

    public static function setWechatUser($wechatUser,$spread_uid = 0)
    {
        return self::set([
            'account'=>'wx'.$wechatUser['uid'].time(),
            'pwd'=>md5(123456),
            'nickname'=>$wechatUser['nickname']?:'',
            'avatar'=>$wechatUser['headimgurl']?:'',
            'spread_uid'=>$spread_uid,
            'uid'=>$wechatUser['uid'],
            'user_type'=>'wechat'
        ]);

    }

    public static function updateWechatUser($wechatUser,$uid)
    {
        return self::edit([
            'nickname'=>$wechatUser['nickname']?:'',
            'avatar'=>$wechatUser['headimgurl']?:''
        ],$uid,'uid');
    }

    public static function setSpreadUid($uid,$spreadUid)
    {
        return self::where('uid',$uid)->update(['spread_uid'=>$spreadUid]);
    }


    public static function getUserInfo($uid)
    {
        $userInfo = self::where('uid',$uid)->find();
        if(!$userInfo) exception('读取用户信息失败!');
        return $userInfo->toArray();
    }

    /**
     * 获得当前登陆用户UID
     * @return int $uid
     */
    public static function getActiveUid()
    {
        $uid = null;
        if(Session::has('loginUid','wap')) $uid = Session::get('loginUid','wap');
        if(!$uid && Session::has('loginOpenid','wap') && ($openid = Session::get('loginOpenid','wap')))
            $uid = WechatUser::openidToUid($openid);
        if(!$uid) exit(exception('请登陆!'));
        return $uid;
    }

    /** //TODO 一级返佣
     * @param $orderInfo
     * @return bool
     */
    public static function backOrderBrokerage($orderInfo)
    {
        $userInfo = User::getUserInfo($orderInfo['uid']);
        if(!$userInfo || !$userInfo['spread_uid']) return true;
        $storeBrokerageStatu = SystemConfigService::get('store_brokerage_statu') ? : 1;//获取后台分销类型
        if($storeBrokerageStatu == 1){
            if(!User::be(['uid'=>$userInfo['spread_uid'],'is_promoter'=>1])) return true;
        }
        $brokerageRatio = (SystemConfigService::get('store_brokerage_ratio') ?: 0)/100;
        if($brokerageRatio <= 0) return true;
        $cost = isset($orderInfo['cost']) ? $orderInfo['cost'] : 0;//成本价
        if($cost > $orderInfo['pay_price']) return true;//成本价大于支付价格时直接返回
        $brokeragePrice = bcmul(bcsub($orderInfo['pay_price'],$cost,2),$brokerageRatio,2);
        if($brokeragePrice <= 0) return true;
        $mark = $userInfo['nickname'].'成功消费'.floatval($orderInfo['pay_price']).'元,奖励推广佣金'.floatval($brokeragePrice);
        self::beginTrans();
        $res1 = UserBill::income('获得推广佣金',$userInfo['spread_uid'],'now_money','brokerage',$brokeragePrice,$orderInfo['id'],0,$mark);
        $res2 = self::bcInc($userInfo['spread_uid'],'now_money',$brokeragePrice,'uid');
        $res = $res1 && $res2;
        self::checkTrans($res);
        if($res) self::backOrderBrokerageTwo($orderInfo);
        return $res;
    }

    /**
     * //TODO 二级推广
     * @param $orderInfo
     * @return bool
     */
    public static function backOrderBrokerageTwo($orderInfo){
        $userInfo = User::getUserInfo($orderInfo['uid']);
        $userInfoTwo = User::getUserInfo($userInfo['spread_uid']);
        if(!$userInfoTwo || !$userInfoTwo['spread_uid']) return true;
        $storeBrokerageStatu = SystemConfigService::get('store_brokerage_statu') ? : 1;//获取后台分销类型
        if($storeBrokerageStatu == 1){
            if(!User::be(['uid'=>$userInfoTwo['spread_uid'],'is_promoter'=>1]))  return true;
        }
        $brokerageRatio = (SystemConfigService::get('store_brokerage_two') ?: 0)/100;
        if($brokerageRatio <= 0) return true;
        $cost = isset($orderInfo['cost']) ? $orderInfo['cost'] : 0;//成本价
        if($cost > $orderInfo['pay_price']) return true;//成本价大于支付价格时直接返回
        $brokeragePrice = bcmul(bcsub($orderInfo['pay_price'],$cost,2),$brokerageRatio,2);
        if($brokeragePrice <= 0) return true;
        $mark = '二级推广人'.$userInfo['nickname'].'成功消费'.floatval($orderInfo['pay_price']).'元,奖励推广佣金'.floatval($brokeragePrice);
        self::beginTrans();
        $res1 = UserBill::income('获得推广佣金',$userInfoTwo['spread_uid'],'now_money','brokerage',$brokeragePrice,$orderInfo['id'],0,$mark);
        $res2 = self::bcInc($userInfoTwo['spread_uid'],'now_money',$brokeragePrice,'uid');
        $res = $res1 && $res2;
        self::checkTrans($res);
        return $res;
    }

}