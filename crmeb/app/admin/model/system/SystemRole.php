<?php
/**
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/13
 */

namespace app\admin\model\system;

use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;

/**
 * 身份管理 model
 * Class SystemRole
 * @package app\admin\model\system
 */
class SystemRole extends BaseModel
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
    protected $name = 'system_role';

    use ModelTrait;

    public static function setRulesAttr($value)
    {
        return is_array($value) ? implode(',',$value) : $value;
    }

    /**
     * 选择管理员身份
     * @param int $level
     * @return array
     */
    public static function getRole($level = 0)
    {

        return self::where('status',1)->where('level',$level)->column('role_name','id');
    }


    public static function rolesByAuth($rules)
    {
        if(empty($rules)) return [];
        $rules = self::where('id','IN',$rules)->where('status','1')->column('rules','id');
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
        if(strlen(trim($where['role_name']))) $model = $model->where('role_name','LIKE',"%$where[role_name]%");
        if(strlen(trim($where['status']))) $model = $model->where('status',$where['status']);
        $model = $model->where('level',bcadd($where['level'],1,0));
        return self::page($model,(function($item){
            $item->rules = SystemMenus::where('id','IN',$item->rules)->column('menu_name','id');
        }),$where);
    }

}