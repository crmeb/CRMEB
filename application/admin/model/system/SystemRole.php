<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/13
 */

namespace app\admin\model\system;

use traits\ModelTrait;
use basic\ModelBasic;

/**
 * 身份管理 model
 * Class SystemRole
 * @package app\admin\model\system
 */
class SystemRole extends ModelBasic
{
    use ModelTrait;

    public static function setRulesAttr($value)
    {
        return is_array($value) ? implode(',',$value) : $value;
    }

    /**
     * 选择管理员身份
     * @param int $level
     * @param string $field
     * @return array
     */
    public static function getRole($level = 0 ,$field='id,role_name')
    {
        return self::where('status',1)->where('level','=',$level)->column($field);
    }


    public static function rolesByAuth($rules)
    {
        if(empty($rules)) return [];
        $rules = self::where('id','IN',$rules)->where('status','1')->column('rules');
        $rules = array_unique(explode(',',implode(',',$rules)));
        $_auth = SystemMenus::all(function($query) use($rules){
            $query->where('id','IN',$rules)
                ->where('controller|action','<>','')
                ->field('module,controller,action,params');
        });
        return self::tidyAuth($_auth?:[]);
    }

    public static function getAllAuth()
    {
        static $auth = null;
        $auth === null  && ($auth = self::tidyAuth(SystemMenus::all(function($query){
            $query->where('controller|action','<>','')->field('module,controller,action,params');
        })?:[]));
        return $auth;
    }

    protected static function tidyAuth($_auth)
    {
        $auth = [];
        foreach ($_auth as $k=>$val){
            $auth[] =  SystemMenus::getAuthName($val['action'],$val['controller'],$val['module'],$val['params']);
        }
        return $auth;
    }


    public static function systemPage($where){
        $model = new self;
        if($where['role_name'] != '') $model = $model->where('role_name','LIKE',"%$where[role_name]%");
        if($where['status'] != '') $model = $model->where('status',$where['status']);
        $model->where('level','=',bcadd($where['level'],1,0));
        return self::page($model,(function($item,$key){
            $item->rules = SystemMenus::where('id','IN',$item->rules)->column('menu_name');
        }),$where);
    }

}