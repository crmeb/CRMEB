<?php
namespace app\models\user;

use crmeb\basic\BaseModel;
use crmeb\services\SystemConfigService;
use crmeb\traits\ModelTrait;

/**
 * TODO  用户签到模型 Model
 * Class UserSign
 * @package app\models\user
 */
class UserSign extends BaseModel
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
    protected $name = 'user_sign';

    use ModelTrait;
    /*
     * 设置签到数据
     * @param int $uid 用户uid
     * @param string $title 签到说明
     * @param int $number 签到获得积分
     * @param int $balance 签到前剩余积分
     * @return object
     * */
    public static function setSignData($uid,$title='',$number=0,$balance=0)
    {
        $add_time=time();
        return self::create(compact('uid','title','number','balance','add_time')) && UserBill::income($title,$uid,'integral','sign',$number,0,$balance,$title);
    }

    /*
     * 分页获取用户签到数据
     * @param int $uid 用户uid
     * @param int $page 页码
     * @param int $limit 显示多少条
     * @return array
     * */
    public static function getSignList($uid,$page,$limit)
    {
        if(!$limit) return [];
        if($page){
            return UserBill::where('a.category','integral')
                ->where('a.type','sign')
                ->where('a.status',1)
                ->where('a.uid',$uid)
                ->alias('a')
                ->join("__user__ u",'u.uid=a.uid')
                ->order('a.add_time desc')
                ->field('FROM_UNIXTIME(a.add_time,"%Y-%m-%d") as add_time,a.title,a.number')
                ->page((int)$page,(int)$limit)
                ->select();
        }else{
            return UserBill::where('a.category','integral')
                ->where('a.type','sign')
                ->where('a.status',1)
                ->where('a.uid',$uid)
                ->alias('a')
                ->join("__user__ u",'u.uid=a.uid')
                ->order('a.add_time desc')
                ->field('FROM_UNIXTIME(a.add_time,"%Y-%m-%d") as add_time,a.title,a.number')
                ->select();
        }
    }

    /*
     * 获取用户累计签到次数
     * @Parma int $uid 用户id
     * @return int
     * */
    public static function getSignSumDay($uid)
    {
        return self::where('uid', $uid)->count();
    }

    /*
     * 获取用户今天是否签到
     * @param int $uid
     * */
    public static function getToDayIsSign($uid)
    {
        return self::where('uid', $uid)->whereTime('add_time','today')->count() ? true : false;
    }

    /*
     * 获取用户昨天是否签到
     * @param int $uid
     * */
    public static function getYesterDayIsSign($uid)
    {
        return self::where('uid', $uid)->whereTime('add_time','yesterday')->count() ? true : false;
    }

    /*
     * 获取签到配置
     * @param string
     * */
    public static function getSignSystemList($key='sign_day_num')
    {
        return \crmeb\services\GroupDataService::getData($key) ? : [];
    }

    /*
     * 用户签到
     * @param int $uid 用户uid
     * @return boolean
     * */
    public static function sign($uid)
    {
        $sign_list=self::getSignSystemList();
        if(!count($sign_list)) return self::setErrorInfo('请先配置签到天数');
        $user=User::where('uid',$uid)->find();
        $sign_num=0;
        //检测昨天是否签到
        if(self::getYesterDayIsSign($uid)){
            if($user->sign_num > (count($sign_list) -1)) $user->sign_num=0;
        }else{
            //如果昨天没签到,回退到第一天
            $user->sign_num=0;
        }
        foreach ($sign_list as $key=>$item){
            if($key==$user->sign_num){
                $sign_num=$item['sign_num'];
                break;
            }
        }
        $user->sign_num+=1;
        if($user->sign_num == count($sign_list))
            $res1 = self::setSignData($uid,'连续签到奖励',$sign_num,$user->integral);
        else
//            $res1 = self::setSignData($uid,'用户连续签到第'.(self::getSignSumDay($uid)+1).'天',$sign_num,$user->integral);
            $res1 = self::setSignData($uid,'签到奖励',$sign_num,$user->integral);
        $res2= User::bcInc($uid,'integral',$sign_num,'uid');
        $res3=$user->save();
        $res = $res1 && $res2 && $res3!==false;
        BaseModel::checkTrans($res);
        event('UserLevelAfter',[$user]);
        if($res)
            return $sign_num;
        else
            return false;
    }

    /*
     * 获取签到列表按月加载
     * @param int $uid 用户uid
     * @param int $page 页码
     * @param int $limit 显示多少条
     * @return array
     * */
    public static function getSignMonthList($uid,$page=1,$limit=8)
    {
        if(!$limit) return [];
        if($page){
            $list = UserBill::where('uid', $uid)
                ->where('category', 'integral')
                ->where('type', 'sign')
                ->field('FROM_UNIXTIME(add_time,"%Y-%m") as time,group_concat(id SEPARATOR ",") ids')
                ->group('time')
                ->order('time DESC')
                ->page($page, $limit)
                ->select();
        }else{
            $list = UserBill::where('uid', $uid)
                ->where('category', 'integral')
                ->where('type', 'sign')
                ->field('FROM_UNIXTIME(add_time,"%Y-%m") as time,group_concat(id SEPARATOR ",") ids')
                ->group('time')
                ->order('time DESC')
                ->select();
        }
        $data=[];
        foreach ($list as $key=>&$item){
            $value['month'] = $item['time'];
            $value['list'] = UserBill::where('id','in',$item['ids'])->field('FROM_UNIXTIME(add_time,"%Y-%m-%d") as add_time,title,number')->order('add_time DESC')->select();
            array_push($data,$value);
        }
        return $data;
    }

    public static function checkUserSigned($uid)
    {
        return UserBill::be(['uid'=>$uid,'add_time'=>['>',strtotime('today')],'category'=>'integral','type'=>'sign']);
    }

    public static function userSignedCount($uid)
    {
        return self::userSignBillWhere($uid)->count();
    }

    /**
     * @param $uid
     * @return UserBill
     */
    public static function userSignBillWhere($uid)
    {
        return UserBill::where('uid', $uid)->where('category', 'integral')->where('type', 'sign');
    }

    public static function signEbApi($userInfo)
    {
        $uid = $userInfo['uid'];
        $min = SystemConfigService::get('sx_sign_min_int')?:0;
        $max = SystemConfigService::get('sx_sign_max_int')?:5;
        $integral = rand($min,$max);
        BaseModel::beginTrans();
        $res1 = UserBill::income('用户签到',$uid,'integral','sign',$integral,0,$userInfo['integral'],'签到获得'.floatval($integral).'积分');
        $res2 = User::bcInc($uid,'integral',$integral,'uid');
        $res = $res1 && $res2;
        BaseModel::checkTrans($res);
        if($res)
            return $integral;
        else
            return false;
    }
}