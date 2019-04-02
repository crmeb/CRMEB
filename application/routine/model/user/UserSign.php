<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2018/02/28
 */

namespace app\routine\model\user;


use basic\ModelBasic;
use service\SystemConfigService;
use think\Model;

class UserSign
{
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
     * @return Model
     */
    public static function userSignBillWhere($uid)
    {
        return UserBill::where(['uid'=>$uid,'category'=>'integral','type'=>'sign']);
    }

    public static function sign($userInfo)
    {
        $uid = $userInfo['uid'];
        $min = SystemConfigService::get('sx_sign_min_int')?:0;
        $max = SystemConfigService::get('sx_sign_max_int')?:5;
        $integral = rand($min,$max);
        ModelBasic::beginTrans();
        $res1 = UserBill::income('用户签到',$uid,'integral','sign',$integral,0,bcadd($userInfo['integral'],$integral,2),'签到获得'.floatval($integral).'积分');
        $res2 = User::bcInc($uid,'integral',$integral,'uid');
        $res = $res1 && $res2;
        ModelBasic::checkTrans($res);
        if($res)
            return $integral;
        else
            return false;
    }
    public static function signFoodie($uid,$sign_list){
        ModelBasic::beginTrans();
        $user=User::where(['uid'=>$uid,'status'=>1])->field(['integral','sign_num','sign_time','sign_count'])->find();
        if(!$user) return false;
        $sign_num=0;
        //检测昨天是否签到
        if(User::where(['uid'=>$uid,'status'=>1])->whereTime('sign_time','yesterday')->count()){
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
        $user->sign_time=time();
        $user->sign_num+=1;
        $user->sign_count+=1;
        if($user->sign_num == count($sign_list)){
            $res1 = UserBill::income('连续签到奖励',$uid,'integral','sign',$sign_num,0,bcadd($user->integral,$sign_num),'签到获得'.floatval($sign_num).'积分');
        }else{
            $res1 = UserBill::income('用户累计签到第'.$user->sign_count.'天',$uid,'integral','sign',$sign_num,0,bcadd($user->integral,$sign_num),'签到获得'.floatval($sign_num).'积分');
        }
        $res2= User::bcInc($uid,'integral',$sign_num,'uid');
        $res3=$user->save();
        $res = $res1 && $res2 && $res3;
        ModelBasic::checkTrans($res);
        if($res)
            return $sign_num;
        else
            return false;
    }

    public static function getSignLog($where,$gruop = false){
        $userbill = UserBill::where(['uid'=>$where['uid'],'category'=>'integral'])->order('add_time desc');
        $userinfo = [];
        if($gruop == false){
            $list = $userbill->where('type','sign')->page((int)$where['page'],(int)$where['limit'])->select();
            count($list) && $list=$list->toArray();
            $page=$where['page']+1;
            foreach ($list as &$item) $item['add_time']=date('Y-m-d H:i:s',$item['add_time']);
        }else{
            $list=$userbill->where('type','in','sign,clear,recommend')->field(['FROM_UNIXTIME(add_time,"%Y-%m") as time','group_concat(id SEPARATOR ",") ids'])
                ->group('time')
                ->order('time asc')
                ->page((int)$where['page'],(int)$where['limit'])
                ->select();
            count($list) && $list=$list->toArray();
            $sign_list = [];
            foreach ($list as $item){
                $value['month']=self::getUtf8Month($item['time']);
                $value['list']=($val=UserBill::where('id','in',$item['ids'])->order('add_time desc')
                    ->field(['FROM_UNIXTIME(add_time,"%Y/%m/%d %H:%i:%s") as time','title','number'])
                    ->select()) && count($val) ? $val->toArray() : [];
                $sign_list[]=$value;
            }
            $list = $sign_list;
            if($where['page']==1){
                //总共积分
                $userinfo['integral'] = User::where(['uid' => $where['uid']])->value('integral');
                //昨日积分
                $userinfo['yesterday_integral'] = UserBill::where(['uid' => $where['uid'], 'category' => 'integral'])->where('type','in',['sign','clear','recommend'])->value('number');
                //本周积分
                $userinfo['week_integral'] = UserBill::where(['uid' => $where['uid'], 'category' => 'integral'])->where('type','in',['sign','clear','recommend'])->whereTime('add_time', 'week')->value('number');
                //排名
                $userinfo['ranking'] = User::where('integral', '>', $userinfo['integral'])->count();
                $userinfo['ranking'] += 1;
            }
            $page=$where['page']+1;
        }
        return compact('list','page','userinfo');
    }

    public static function getUtf8Month($time){
        $num=['01'=>'一', '02'=>'二', '03'=>'三', '04'=>'四', '05'=>'五',
            '06'=>'六', '07'=>'七', '08'=>'八', '09'=>'九','10'=>'十','11'=>'十一','12'=>'十二'];
        list($year,$month)=explode('-',$time);
        return isset($num[$month]) ? $num[$month].'月' : '';
    }

    /*
     * 等级计算 返回当前等级
     * */
    public static function getdiscount(array $sign_deploy,$integral){
        $grade_name='';
        $discount=0;
        $sign_grade=0;
        $pic='';
        $site_url=SystemConfigService::get('site_url');
        foreach ($sign_deploy as $item){
            list($min,$max)=strstr($item['sign_num'],'-') ? explode('-',$item['sign_num']) : [$item['sign_num'],''];
            if($integral <= $min && $max==''){
                $grade_name=$item['sign_name'];
                $discount=bcdiv($item['discount'],10,1);
                $sign_grade=$item['sign_grade'];
                $pic=$site_url.$item['pic'];
                break;
            }else if($integral >= $min && $integral <= $max) {
                $grade_name = $item['sign_name'];
                $discount = bcdiv($item['discount'], 10, 1);
                $sign_grade = $item['sign_grade'];
                $pic = $site_url . $item['pic'];
                break;
            }else if($integral > $max && $integral > $min && $max!=''){
                $count=count($sign_deploy);
                $grade_name=$sign_deploy[$count-1]['sign_name'];
                $discount=bcdiv($sign_deploy[$count-1]['discount'],10,1);
                $sign_grade=$sign_deploy[$count-1]['sign_grade'];
                $pic=$site_url.$sign_deploy[$count-1]['pic'];
                break;
            }
        }
        $pic=strstr($pic,'s_') ? str_replace('s_','',$pic) : $pic;
        return [$grade_name,$discount,$sign_grade,$pic];
    }

}