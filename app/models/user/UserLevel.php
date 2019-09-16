<?php
namespace app\models\user;

use app\models\system\SystemUserLevel;
use app\models\system\SystemUserTask;
use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

/**
 * TODO 会员等级Model
 * Class UserLevel
 * @package app\models\user
 */
class UserLevel extends BaseModel
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
    protected $name = 'user_level';

    use ModelTrait;

    /*
    * 获取用户等级人数
    * */
    public static function setUserLevelCount($uids)
    {
        $model=new self();
        if(is_array($uids)) $model=$model->where('uid','in',$uids);
        else $model=$model->where('uid',$uids);
        return $model->count();
    }

    /*
     * 设置查询初始化条件
     * @param string $alias 表别名
     * @param object $model 模型实例化对象
     * @return object
     * */
    public static function valiWhere($alias='',$model=null)
    {
        $model=is_null($model) ? new self() : $model;
        if($alias){
            $model=$model->alias($alias);
            $alias.='.';
        }
        return $model->where("{$alias}status", 1)->where("{$alias}is_del", 0);
    }
    /*
     * 设置会员等级
     * @param int $uid 用户uid
     * @param int $level_id 等级id
     * @return boolean | array
     * */
    public static function setUserLevel($uid,$level_id){
        $vipinfo=SystemUserLevel::get($level_id);
        if(!$vipinfo) return false;
        $userinfo = User::find($uid);
        if(!$userinfo) return false;
        $add_valid_time=(int)$vipinfo->valid_date*86400;
        $uservipinfo=self::valiWhere()->where('uid', $uid)->where('level_id', $level_id)->find();
        //检查是否购买过
        if($uservipinfo){
            $stay=0;
            //剩余时间
            if(time() < $uservipinfo->valid_time) $stay=$uservipinfo->valid_time-time();
            //如果购买过当前等级的会员过期了.从当前时间开始计算
            //过期时效: 剩余时间+当前会员等级时间+当前time
            $add_valid_time=$stay+$add_valid_time+time();
            $data['is_forever']=$vipinfo->is_forever;
            $data['valid_time']=$add_valid_time;
            User::where('uid',$uid)->update(['level'=>$level_id]);
            return self::where('uid', $uid)->where('level_id', $level_id)->update($data);
        }else{
            $data=[
                'is_forever'=>$vipinfo->is_forever,
                'status'=>1,
                'is_del'=>0,
                'grade'=>$vipinfo->grade,
                'uid'=>$uid,
                'add_time'=>time(),
                'level_id'=>$level_id,
                'discount'=>$vipinfo->discount,
            ];
            if($data['is_forever'])
                $data['valid_time']=0;
            else
                $data['valid_time']=$add_valid_time+time();
            $data['mark']='尊敬的用户'.$userinfo['nickname'].'在'.date('Y-m-d H:i:s',time()).'成为了'.$vipinfo['name'];
            $res=self::create($data);
            if(!$res) return false;
            User::where('uid',$uid)->update(['level'=>$level_id]);
            return $res;
        }
    }

    /*
     * 获取当前用户会员等级返回当前用户等级id
     * @param int $uid 用户uid
     * @return int 会员id
     * */
    public static function getUserLevel($uid,$grade=0)
    {
        $model = self::valiWhere();
        if ($grade) $model = $model->where('grade', '<', $grade);
        $level = $model->where('uid', $uid)->order('grade desc')->field('level_id,is_forever,valid_time,id,status,grade')->find();
        if (!$level) return false;
        if ($level->is_forever) return $level->id;
        //会员已经过期
        if (time() > $level->valid_time){
            if($level->status==1){
                $level->status=0;
                $level->save();
            }
            return self::getUserLevel($uid, $level->grade);
        }else
            //会员没有过期
            return $level->id;
    }

    /*
     * 获取会员详细信息
     * @param int $id 会员记录id
     * @param string $keyName 字段名
     * @return array
     * */
    public static function getUserLevelInfo($id,$keyName=''){
        $vipinfo=self::valiWhere('a')->where('a.id',$id)->field('l.id,a.add_time,l.discount,a.level_id,l.name,l.money,l.icon,l.is_pay,l.grade')
            ->join('__system_user_level__ l','l.id=a.level_id')->find();
        if($keyName) if(isset($vipinfo[$keyName])) return $vipinfo[$keyName]; else return '';
        return $vipinfo;
    }

    /*
     * 获取当前用户已成为的vip id
     * @param int $uid 用户id
     * @return array
     * */
    public static function getUserLevelIds($uid)
    {
       return self::valiWhere()->group('level_id')->where('uid',$uid)->order('grade asc')->column('level_id','level_id');
    }

    /*
     * 检查是否能成为会员
     * @param int $uid 用户
     * */
    public static function setLevelComplete($uid,$leveNowId=false)
    {
        $user=User::where('uid',$uid)->find();
        if(!$user) return self::setErrorInfo('没有此用户，无法检测升级会员');
        $level=self::getUserLevel($uid);
        if($level===false)
            $level_id=0;
        else
            $level_id=self::getUserLevelInfo($level,'level_id');
        $leveNowId=SystemUserLevel::getNextLevelId($level_id);
        if($leveNowId===0) return self::setErrorInfo('暂无可升会员');
        //查找当前需要升级的会员任务
        $taskAll=SystemUserTask::visibleWhere()->where('level_id',$leveNowId)->column('id','id');
        self::startTrans();
        $res2=true;
        try{
            if($level===false){
                //没有成为会员的从用户添加的时间开始算起,如果被清理过会员从清理的时间开始算起
                $add_time=$user['clean_time'] ? $user['clean_time'] :$user['add_time'];
            }else{
                $add_time=self::getUserLevelInfo($level,'add_time');
            }
            //查询并记录任务
            foreach ($taskAll as $id){
                $res=SystemUserTask::setTaskFinish($id,$uid,$add_time);
                if(!$res) return self::setErrorInfo(SystemUserTask::getErrorInfo(),true);
            }
            //获取需要成为会员的任务完成度
            if(SystemUserTask::getTaskComplete($leveNowId,$uid)){
                //设置任务已使用
                $res=SystemUserTask::setTarkStatus($leveNowId,$uid);
                if(!$res) return self::setErrorInfo('设置任务状态失败',true);
                //记录会员
                $res2=self::setUserLevel($uid,$leveNowId);
            }
            self::commitTrans();
            return $res2;
        }catch (\Exception $e){
            self::rollbackTrans();
            return self::setErrorInfo($e->getMessage());
        }
    }

}