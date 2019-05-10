<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */
namespace app\admin\model\user;
use app\admin\model\system\SystemUserLevel;
use traits\ModelTrait;
use basic\ModelBasic;
/**
 * 用户管理 model
 * Class User
 * @package app\admin\model\user
 */
class UserLevel extends ModelBasic
{
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
        return $model->where(["{$alias}status"=>1,"{$alias}is_del"=>0]);
    }
    /*
     * 查询用户vip列表
     * @param array $where
     * */
    public static function getUserVipList($where)
    {
        $data=self::setWhere($where,'a')->group('a.uid')->order('grade desc')
            ->field(['a.*','u.nickname','u.avatar'])
            ->join('__USER__ u','a.uid=u.uid')->page((int)$where['page'],(int)$where['limit'])->select();
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
        $count=self::setWhere($where,'a')->group('a.level_id')->order('grade desc')->join('__USER__ u','a.uid=u.uid')->count();
        return compact('data','count');
    }

}