<?php
namespace app\admin\model\user;

use app\admin\model\system\SystemUserLevel;
use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;

/**
 * 用户管理 model
 * Class User
 * @package app\admin\model\user
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

    public static function setWhere($where,$alias='',$userAlias='u.',$model=null)
    {
        $model=is_null($model) ? new self() : $model;
        if($alias){
            $model=$model->alias($alias);
            $alias.='.';
        }
        if(isset($where['nickname']) && $where['nickname']!='') $model=$model->where("{$userAlias}nickanme",$where['nickname']);
        if(isset($where['level_id']) && $where['level_id']!='') $model=$model->where("{$alias}level_id",$where['level_id']);
        return $model->where("{$alias}status", 1)->where("{$alias}is_del", 0);
    }
    /*
     * 查询用户vip列表
     * @param array $where
     * */
    public static function getUserVipList($where)
    {
        $data=self::setWhere($where,'a')->group('a.uid')->order('grade desc')
            ->field('a.*,u.nickname,u.avatar')
            ->join('__user__ u','a.uid=u.uid')->page((int)$where['page'],(int)$where['limit'])->select();
        $data=count($data) ? $data->toArray() : [];
        foreach ($data as &$item){
            $info=SystemUserLevel::where('id',$item['level_id'])->find();
            if($info){
                $item['name']=$info['name'];
                $item['icon']=$info['icon'];
            }
            $item['is_forever']= $item['is_forever'] ? '永久会员':'限时会员';
            $item['valid_time']=$item['is_forever'] ? '永久':date('Y-m-d H:i:s',$item['valid_time']);
        }
        $count=self::setWhere($where,'a')->group('a.level_id')->order('grade desc')->join('__user__ u','a.uid=u.uid')->count();
        return compact('data','count');
    }

    /*
     * 清除会员等级
     * @paran int $uid
     * @paran boolean
     * */
    public static function cleanUpLevel($uid)
    {
        self::rollbackTrans();
        $res=false !== self::where('uid', $uid)->update(['is_del'=>1]);
        $res= $res && UserTaskFinish::where('uid', $uid)->delete();
        if($res){
            User::where('uid', $uid)->update(['clean_time'=>time()]);
            self::commitTrans();
            return true;
        }else{
            self::rollbackTrans();
            return self::setErrorInfo('清除失败');
        }
    }

}