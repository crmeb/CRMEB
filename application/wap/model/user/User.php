<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/21
 */

namespace app\wap\model\user;


use basic\ModelBasic;
use app\core\util\SystemConfigService;
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
        //TODO 获取后台分销类型
        $storeBrokerageStatus = SystemConfigService::get('store_brokerage_statu');
        $storeBrokerageStatus = $storeBrokerageStatus ? $storeBrokerageStatus : 1;
        if($storeBrokerageStatus == 1){
            $spreadCount = self::where('uid',$spreadUid)->count();
            if($spreadCount){
                $spreadInfo = self::where('uid',$spreadUid)->find();
                if($spreadInfo->is_promoter){
                    //TODO 只有扫码才可以获得推广权限
                    if(isset($wechatUser['isPromoter'])) $data['is_promoter'] = 1;
                }
            }
        }
        return self::where('uid',$uid)->update(['spread_uid'=>$spreadUid,'spread_time'=>time()]);
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

    /**
     * TODO 一级返佣
     * @param $orderInfo
     * @return bool
     */
    public static function backOrderBrokerage($orderInfo)
    {
        //TODO 如果时营销产品不返佣金
        if(isset($orderInfo['combination_id']) && $orderInfo['combination_id']) return true;
        if(isset($orderInfo['seckill_id']) && $orderInfo['seckill_id']) return true;
        if(isset($orderInfo['bargain_id']) && $orderInfo['bargain_id']) return true;
        //TODO 支付金额减掉邮费
        $orderInfo['pay_price'] = bcsub($orderInfo['pay_price'],$orderInfo['pay_postage'],2);
        //TODO 获取购买商品的用户
        $userInfo = User::getUserInfo($orderInfo['uid']);
        //TODO 当前用户不存在 或者 没有上级 直接返回
        if(!$userInfo || !$userInfo['spread_uid']) return true;
        //TODO 获取后台分销类型  1 指定分销 2 人人分销
        $storeBrokerageStatus = SystemConfigService::get('store_brokerage_statu');
        $storeBrokerageStatus = $storeBrokerageStatus ? $storeBrokerageStatus : 1;
        //TODO 指定分销 判断 上级是否时推广员  如果不是推广员直接跳转二级返佣
        if($storeBrokerageStatus == 1){
            if(!User::be(['uid'=>$userInfo['spread_uid'],'is_promoter'=>1])) return self::backOrderBrokerageTwo($orderInfo);
        }
        //TODO 获取后台一级返佣比例
        $storeBrokerageRatio = SystemConfigService::get('store_brokerage_ratio');
        //TODO 一级返佣比例 小于等于零时直接返回 不返佣
        if($storeBrokerageRatio <= 0) return true;
        //TODO 计算获取一级返佣比例
        $brokerageRatio = bcdiv($storeBrokerageRatio,100,2);
        //TODO 成本价
        $cost = isset($orderInfo['cost']) ? $orderInfo['cost'] : 0;
        //TODO 成本价大于等于支付价格时直接返回
        if($cost >= $orderInfo['pay_price']) return true;
        //TODO 获取订单毛利
        $payPrice = bcsub($orderInfo['pay_price'],$cost,2);
        //TODO 返佣金额 = 毛利 / 一级返佣比例
        $brokeragePrice = bcmul($payPrice,$brokerageRatio,2);
        //TODO 返佣金额小于等于0 直接返回不返佣金
        if($brokeragePrice <= 0) return true;
        //TODO 获取上级推广员信息
        $spreadUserInfo = User::getUserInfo($userInfo['spread_uid']);
        //TODO 上级推广员返佣之后的金额
        $balance = bcadd($spreadUserInfo['brokerage_price'],$brokeragePrice,2);
        $mark = $userInfo['nickname'].'成功消费'.floatval($orderInfo['pay_price']).'元,奖励推广佣金'.floatval($brokeragePrice);
        self::beginTrans();
        //TODO 添加推广记录
        $res1 = UserBill::income('获得推广佣金',$userInfo['spread_uid'],'now_money','brokerage',$brokeragePrice,$orderInfo['id'],$balance,$mark);
        //TODO 添加用户余额
        $res2 = self::bcInc($userInfo['spread_uid'],'brokerage_price',$brokeragePrice,'uid');
        $res = $res1 && $res2;
        self::checkTrans($res);
        //TODO 一级返佣成功 跳转二级返佣
        if($res) return self::backOrderBrokerageTwo($orderInfo);
        return $res;
    }

    /**
     * TODO 二级推广
     * @param $orderInfo
     * @return bool
     */
    public static function backOrderBrokerageTwo($orderInfo){
        //TODO 获取购买商品的用户
        $userInfo = User::getUserInfo($orderInfo['uid']);
        //TODO 获取上推广人
        $userInfoTwo = User::getUserInfo($userInfo['spread_uid']);
        //TODO 上推广人不存在 或者 上推广人没有上级 直接返回
        if(!$userInfoTwo || !$userInfoTwo['spread_uid']) return true;
        //TODO 获取后台分销类型  1 指定分销 2 人人分销
        $storeBrokerageStatus = SystemConfigService::get('store_brokerage_statu');
        $storeBrokerageStatus = $storeBrokerageStatus ? $storeBrokerageStatus : 1;
        //TODO 指定分销 判断 上上级是否时推广员  如果不是推广员直接返回
        if($storeBrokerageStatus == 1){
            if(!User::be(['uid'=>$userInfoTwo['spread_uid'],'is_promoter'=>1]))  return true;
        }
        //TODO 获取二级返佣比例
        $storeBrokerageTwo = SystemConfigService::get('store_brokerage_two');
        //TODO 二级返佣比例小于等于0 直接返回
        if($storeBrokerageTwo <= 0) return true;
        //TODO 计算获取二级返佣比例
        $brokerageRatio = bcdiv($storeBrokerageTwo,100,2);
        //TODO 获取成本价
        $cost = isset($orderInfo['cost']) ? $orderInfo['cost'] : 0;
        //TODO 成本价大于等于支付价格时直接返回
        if($cost >= $orderInfo['pay_price']) return true;
        //TODO 获取订单毛利
        $payPrice = bcsub($orderInfo['pay_price'],$cost,2);
        //TODO 返佣金额 = 毛利 / 二级返佣比例
        $brokeragePrice = bcmul($payPrice,$brokerageRatio,2);
        //TODO 返佣金额小于等于0 直接返回不返佣金
        if($brokeragePrice <= 0) return true;
        //TODO 获取上上级推广员信息
        $spreadUserInfoTwo = User::getUserInfo($userInfoTwo['spread_uid']);
        //TODO 获取上上级推广员返佣之后余额
        $balance = bcadd($spreadUserInfoTwo['brokerage_price'],$brokeragePrice,2);
        $mark = '二级推广人'.$userInfo['nickname'].'成功消费'.floatval($orderInfo['pay_price']).'元,奖励推广佣金'.floatval($brokeragePrice);
        self::beginTrans();
        //TODO 添加返佣记录
        $res1 = UserBill::income('获得推广佣金',$userInfoTwo['spread_uid'],'now_money','brokerage',$brokeragePrice,$orderInfo['id'],$balance,$mark);
        //TODO 添加用户余额
        $res2 = self::bcInc($userInfoTwo['spread_uid'],'brokerage_price',$brokeragePrice,'uid');
        $res = $res1 && $res2;
        self::checkTrans($res);
        return $res;
    }

}