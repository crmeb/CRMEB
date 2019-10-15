<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/21
 */

namespace app\models\user;

use crmeb\basic\BaseModel;
use crmeb\services\WechatService;
use crmeb\traits\ModelTrait;
use crmeb\services\SystemConfigService;
use app\models\routine\RoutineQrcode;
use app\models\store\StoreCouponUser;

/**
 * TODO 微信用户Model
 * Class WechatUser
 * @package app\models\user
 */
class WechatUser extends BaseModel
{
    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'uid';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'wechat_user';

    use ModelTrait;

    public static function getOpenId($uid = ''){
        if($uid == '') return false;
        return self::where('uid',$uid)->value('routine_openid');
    }

    /**
     * TODO 用uid获得openid
     * @param $uid
     * @param string $openidType
     * @return mixed
     * @throws \Exception
     */
    public static function uidToOpenid($uid, $openidType = 'routine_openid')
    {

        $openid = self::where('uid',$uid)->value($openidType);
        return $openid;
    }

    /**
     * TODO 用openid获得uid
     * @param $openid
     * @param string $openidType
     * @return mixed
     */
    public static function openidTouid($openid, $openidType = 'openid')
    {
        return  self::where($openidType,$openid)->where('user_type','<>','h5')->value('uid');
    }

    /**
     * 小程序创建用户后返回uid
     * @param $routineInfo
     * @return mixed
     */
    public static function routineOauth($routine){
        $routineInfo['nickname'] = filterEmoji($routine['nickName']);//姓名
        $routineInfo['sex'] = $routine['gender'];//性别
        $routineInfo['language'] = $routine['language'];//语言
        $routineInfo['city'] = $routine['city'];//城市
        $routineInfo['province'] = $routine['province'];//省份
        $routineInfo['country'] = $routine['country'];//国家
        $routineInfo['headimgurl'] = $routine['avatarUrl'];//头像
//        $routineInfo[''] = $routine['code'];//临时登录凭证  是获取用户openid和session_key(会话密匙)
        $routineInfo['routine_openid'] = $routine['openId'];//openid
        $routineInfo['session_key'] = $routine['session_key'];//会话密匙
        $routineInfo['unionid'] = $routine['unionId'];//用户在开放平台的唯一标识符
        $routineInfo['user_type'] = 'routine';//用户类型
        $page = '';//跳转小程序的页面
        $spid = 0;//绑定关系uid
        $isCOde=false;
        //获取是否有扫码进小程序
        if($routine['code']){
            $info = RoutineQrcode::getRoutineQrcodeFindType($routine['code']);
            if($info){
                $spid = $info['third_id'];
                $page = $info['page'];
                $isCOde=true;
            }else{
                $spid = $routine['spid'];
            }
        }else if($routine['spid']) $spid = $routine['spid'];
        //  判断unionid  存在根据unionid判断
        if($routineInfo['unionid'] != '' && ($uid=self::where(['unionid'=>$routineInfo['unionid']])->where('user_type','<>','h5')->value('uid'))){
            self::edit($routineInfo,$uid,'uid');
            $routineInfo['code']=$spid;
            $routineInfo['isPromoter']=$isCOde;
            if($routine['login_type']) $routineInfo['login_type'] = $routine['login_type'];
            User::updateWechatUser($routineInfo,$uid);
        }else if($uid = self::where(['routine_openid'=>$routineInfo['routine_openid']])->where('user_type','<>','h5')->value('uid')){ //根据小程序openid判断
            self::edit($routineInfo,$uid,'uid');
            $routineInfo['code']=$spid;
            $routineInfo['isPromoter']=$isCOde;
            if($routine['login_type']) $routineInfo['login_type'] = $routine['login_type'];
            User::updateWechatUser($routineInfo,$uid);
        }else{
            $routineInfo['add_time'] = time();//用户添加时间
            $routineInfo = self::create($routineInfo);
            $res = User::setRoutineUser($routineInfo,$spid);
            $uid = $res->uid;
        }
        $data['page'] = $page;
        $data['uid'] = $uid;
        return $data;
    }
    /**
     * 判断是否是小程序用户
     * @param int $uid
     * @return bool|int|string
     */
    public static function isRoutineUser($uid = 0){
        if(!$uid) return false;
        return self::where('uid',$uid)->where('user_type','routine')->count();
    }

    /**
     * @param int $uid
     * @return int
     */
    public static function isUserStatus($uid = 0){
        if(!$uid) return 0;
        $user = User::getUserInfo($uid);
        return $user['status'];
    }

    /**
     * .添加新用户
     * @param $openid
     * @return object
     */
    public static function setNewUser($openid)
    {
        $userInfo = WechatService::getUserInfo($openid);
        if(!isset($userInfo['subscribe']) || !$userInfo['subscribe'] || !isset($userInfo['openid']))
            exception('请关注公众号!');
        $userInfo['tagid_list'] = implode(',',$userInfo['tagid_list']);
        //判断 unionid 是否存在
        if(isset($userInfo['unionid'])){
            $wechatInfo = self::where('unionid',$userInfo['unionid'])->find();
            if($wechatInfo){
                return self::edit($userInfo,$userInfo['unionid'],'unionid');
            }
        }
        self::beginTrans();
        $wechatUser = self::create(is_object($userInfo) ? $userInfo->toArray() : $userInfo);
        if(!$wechatUser){
            self::rollbackTrans();
            exception('用户储存失败!');
        }
        if(!User::setWechatUser($wechatUser)){
            self::rollbackTrans();
            exception('用户信息储存失败!');
        }
        self::commitTrans();
        self::userFirstSubGiveCoupon($openid);
        return $wechatUser;
    }

    /**
     * TODO 关注送优惠券
     * @param $openid
     */
    public static function userFirstSubGiveCoupon($openid)
    {
        $couponId = SystemConfigService::get('wechat_first_sub_give_coupon');
        if($couponId) StoreCouponUser::addUserCoupon(self::openidToUid($openid),$couponId);
    }

    public static function userTakeOrderGiveCoupon($uid)
    {
        $couponId = SystemConfigService::get('store_order_give_coupon');
        if($couponId) StoreCouponUser::addUserCoupon($uid,$couponId);
    }

    /**
     * 更新用户信息
     * @param $openid
     * @return bool
     */
    public static function updateUser($openid)
    {
        $userInfo = WechatService::getUserInfo($openid);
        $userInfo['tagid_list'] = implode(',',$userInfo['tagid_list']);
        return self::edit($userInfo->toArray(),$openid,'openid');
    }

    /**
     * 用户存在就更新 不存在就添加
     * @param $openid
     */
    public static function saveUser($openid)
    {
        self::be($openid,'openid') == true ? self::updateUser($openid) : self::setNewUser($openid);
    }

    /**
     * 用户取消关注
     * @param $openid
     * @return bool
     */
    public static function unSubscribe($openid)
    {
        return self::edit(['subscribe'=>0],$openid,'openid');
    }

    /**
     * TODO 用uid获得Unionid
     * @param $uid
     * @return mixed
     */
    public static function uidToUnionid($uid)
    {
        return self::where('uid',$uid)->value('unionid');
    }

    /**
     * TODO 获取用户信息
     * @param $openid
     * @param $openidType
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getWechatInfo($openid, $openidType)
    {
        if(is_numeric($openid)) $openid = self::uidToOpenid($openid);
        $wechatInfo = self::where($openidType,$openid)->find();
        if(!$wechatInfo) {
            self::setNewUser($openid);
            $wechatInfo = self::where($openidType,$openid)->find();
        }
        if(!$wechatInfo) exception('获取用户信息失败!');
        return $wechatInfo->toArray();
    }
}