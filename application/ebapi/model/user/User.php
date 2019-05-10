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
        $userinfo=self::where('uid',$uid)->find();
        if($userinfo->spread_uid){
            return self::edit([
                'nickname'=>$wechatUser['nickname']?:'',
                'avatar'=>$wechatUser['headimgurl']?:'',
            ],$uid,'uid');
        }else {
            $data=[
                'nickname' => $wechatUser['nickname'] ?: '',
                'avatar' => $wechatUser['headimgurl'] ?: '',
                'is_promoter' =>$userinfo->is_promoter,
                'spread_uid' => 0,
                'spread_time' =>0,
                'last_time' => time(),
                'last_ip' => Request::instance()->ip(),
            ];
            if(isset($wechatUser['code']) && !$userinfo->is_promoter && $wechatUser['code']){
                $data['is_promoter']=1;
                $data['spread_uid']=$wechatUser['code'];
                $data['spread_time']=time();
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
            'is_promoter'=>$spread_uid || $storeBrokerageStatu != 1 ? 1: 0,
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
     * 小程序用户一级分销
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
        //支付金额减去邮费
        $orderInfo['pay_price'] = bcsub($orderInfo['pay_price'],$orderInfo['pay_postage'],2);
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
     * 小程序 二级推广
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