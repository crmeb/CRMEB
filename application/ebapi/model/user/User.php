<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/21
 */

namespace app\ebapi\model\user;

use app\core\model\user\UserBill;
use app\ebapi\model\store\StoreOrder;
use basic\ModelBasic;
use app\core\util\SystemConfigService;
use think\Request;
use think\Session;
use traits\ModelTrait;

/**
 * 用户
 * Class User
 * @package app\ebapi\model\user
 */
class User extends ModelBasic
{
    use ModelTrait;

    public static function updateWechatUser($wechatUser,$uid)
    {
        $userInfo = self::where('uid',$uid)->find();
        if($userInfo->spread_uid){
            return self::edit([
                'nickname'=>$wechatUser['nickname']?:'',
                'avatar'=>$wechatUser['headimgurl']?:'',
            ],$uid,'uid');
        }else {
            $data=[
                'nickname' => $wechatUser['nickname'] ?: '',
                'avatar' => $wechatUser['headimgurl'] ?: '',
                'is_promoter' =>$userInfo->is_promoter,
                'spread_uid' => 0,
                'spread_time' =>0,
                'last_time' => time(),
                'last_ip' => Request::instance()->ip(),
            ];
            //TODO 获取后台分销类型
            $storeBrokerageStatus = SystemConfigService::get('store_brokerage_statu');
            $storeBrokerageStatus = $storeBrokerageStatus ? $storeBrokerageStatus : 1;
            if(isset($wechatUser['code']) && $wechatUser['code']){
                if($storeBrokerageStatus == 1){
                    $spreadCount = self::where('uid',$wechatUser['code'])->count();
                    if($spreadCount){
                        $spreadInfo = self::where('uid',$wechatUser['code'])->find();
                        if($spreadInfo->is_promoter){
                            //TODO 只有扫码才可以获得推广权限
//                            if(isset($wechatUser['isPromoter'])) $data['is_promoter'] = $wechatUser['isPromoter'] ? 1 : 0;
                        }
                    }
                }
                $spreadInfo = self::where('uid',$wechatUser['code'])->find();
                if($spreadInfo->spread_uid != $uid && $wechatUser['code'] != $uid){
                    $data['spread_uid'] = $wechatUser['code'];
                    $data['spread_time'] = time();
                }
            }
            return self::edit($data, $uid, 'uid');
        }
    }




    /**
     * 小程序用户添加
     * @param $routineUser
     * @param int $spread_uid
     * @return object
     */
    public static function setRoutineUser($routineUser,$spread_uid = 0){
        self::beginTrans();
        $res1 = true;
        if($spread_uid) $res1 = self::where('uid',$spread_uid)->inc('spread_count',1);
        $storeBrokerageStatu = SystemConfigService::get('store_brokerage_statu') ? : 1;//获取后台分销类型
        $res2 = self::set([
            'account'=>'rt'.$routineUser['uid'].time(),
            'pwd'=>md5(123456),
            'nickname'=>$routineUser['nickname']?:'',
            'avatar'=>$routineUser['headimgurl']?:'',
            'spread_uid'=>$spread_uid,
//            'is_promoter'=>$spread_uid || $storeBrokerageStatu != 1 ? 1: 0,
            'spread_time'=>$spread_uid ? time() : 0,
            'uid'=>$routineUser['uid'],
            'add_time'=>$routineUser['add_time'],
            'add_ip'=>Request::instance()->ip(),
            'last_time'=>time(),
            'last_ip'=>Request::instance()->ip(),
            'user_type'=>$routineUser['user_type']
        ]);
        $res = $res1 && $res2;
        self::checkTrans($res);
        return $res2;
    }

    /**
     * 获得当前登陆用户UID
     * @return int $uid
     */
    public static function getActiveUid()
    {
        $uid = null;
        $uid = Session::get('LoginUid');
        if($uid) return $uid;
        else return 0;
    }

    /**
     * TODO 查询当前用户信息
     * @param $uid  $uid 用户编号
     * @param string $field $field 查询的字段
     * @return array
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getUserInfo($uid,$field = '')
    {
        if(strlen(trim($field))) $userInfo = self::where('uid',$uid)->field($field)->find();
        else  $userInfo = self::where('uid',$uid)->find();
        if(!$userInfo) return false;
        return $userInfo->toArray();
    }

    /**
     * 判断当前用户是否推广员
     * @param int $uid
     * @return bool
     */
    public static function isUserSpread($uid = 0){
        if(!$uid) return false;
        $status = (int)SystemConfigService::get('store_brokerage_statu');
        $isPromoter = true;
        if($status == 1) $isPromoter = self::where('uid',$uid)->value('is_promoter');
        if($isPromoter) return true;
        else return false;
    }


    /**
     * TODO 一级返佣
     * @param $orderInfo
     * @return bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
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
        $res2 = true;
        $res = $res1 && $res2;
        self::checkTrans($res1);
        //TODO 一级返佣成功 跳转二级返佣
        if($res) return self::backOrderBrokerageTwo($orderInfo);
        return $res;
    }

    /**
     * TODO 二级推广
     * @param $orderInfo
     * @return bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
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
        //TODO 添加用户佣金金额
        $res2 = self::bcInc($userInfoTwo['spread_uid'],'brokerage_price',$brokeragePrice,'uid');
        $res = $res1 && $res2;
        self::checkTrans($res);
        return $res;
    }

    /*
     *  获取推荐人
     * @param int $two_uid
     * @param int $first
     * @param int $limit
     * @return array
     * */
    public static function getSpreadList($uid,$first,$limit)
    {
        $list=self::where('spread_uid',$uid)->field('uid,nickname,avatar,add_time')->limit($first,$limit)->order('add_time DESC')->select();
        foreach ($list as $k=>$user){
            $list[$k]['add_time'] = date('Y/m/d',$user['add_time']);
            $list[$k]['price'] = StoreOrder::getUserPrice($user['uid']);
        }
        $count = self::where('spread_uid',$uid)->field('uid,nickname,avatar,add_time')->count();
        $data['count'] = $count;
        $data['list'] = $list;
        return $data;
    }

    /*
     * 获取某个用户的下级uid
     * @param int $uid 用户uid
     * @return array
     * */
    public static function getOneSpreadUid($uid)
    {
        return self::where('spread_uid',$uid)->column('uid');
    }

    /*
     * 修改个人信息
     * */
    public static function editUser($avatar,$nickname,$uid)
    {
        return self::edit(['avatar'=>$avatar,'nickname'=>$nickname],$uid,'uid');
    }
    /**
     * TODO 获取推广人数 一级
     * @param int $uid
     * @return bool|int|string
     */
    public static function getSpreadCount($uid = 0){
        if(!$uid) return false;
        return self::where('spread_uid',$uid)->count();
    }

    public static function setUserSpreadCount($uid){
        self::where('uid',$uid)->update(['spread_count'=>self::getSpreadCount($uid)]);
    }

    /**
     * TODO 获取推广人数 二级
     * @param int $uid
     * @return bool|int|string
     */
    public static function getSpreadLevelCount($uid = 0){
        if(!$uid) return false;
        $uidSubordinate = self::where('spread_uid',$uid)->column('uid');
        if(!count($uidSubordinate)) return 0;
        return self::where('spread_uid','IN',implode(',',$uidSubordinate))->count();
    }

    /**
     * 获取用户下级推广人
     * @param int $uid  当前用户
     * @param int $grade 等级  0  一级 1 二级
     * @param string $orderBy  排序
     * @return array|bool|void
     */
    public static function getUserSpreadGrade($uid = 0,$grade = 0,$orderBy = '',$keyword = '',$offset = 0,$limit = 20){
        if(!$uid) return [];
        $gradeGroup = [0,1];
        if(!in_array($grade,$gradeGroup)) return self::setErrorInfo('等级错误');
        $userStair = self::where('spread_uid',$uid)->column('uid');
        if(!count($userStair)) return [];
        if($grade == 0) return self::getUserSpreadCountList(implode(',',$userStair),$orderBy,$keyword,$offset,$limit);
        $userSecondary = self::where('spread_uid','in',implode(',',$userStair))->column('uid');
        return self::getUserSpreadCountList(implode(',',$userSecondary),$orderBy,$keyword,$offset,$limit);
    }

    /**
     * 获取团队信息
     * @param string $uid
     * @param string $orderBy
     * @param string $keyword
     */
    public static function getUserSpreadCountList($uid, $orderBy = '',$keyword = '',$offset = 0,$limit = 20)
    {
        $model = new self;
        if($orderBy==='') $orderBy='u.add_time desc';
        $model = $model->alias(' u');
        $model = $model->join('StoreOrder o','u.uid=o.uid','LEFT');
        $model = $model->where('u.uid','IN',$uid);
        $model = $model->field("u.uid,u.nickname,u.avatar,from_unixtime(u.add_time,'%Y/%m/%d') as time,u.spread_count as childCount,COUNT(o.id) as orderCount,SUM(o.pay_price) as numberCount");
        if(strlen(trim($keyword))) $model = $model->where('u.nickname|u.phone','like',"%$keyword%");
        $model = $model->group('u.uid');
        $model = $model->order($orderBy);
        $model = $model->limit($offset,$limit);
        $list = $model->select();
        if($list) return $list->toArray();
        else return [];
    }
}