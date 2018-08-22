<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\admin\model\system;


use traits\ModelTrait;
use basic\ModelBasic;
use behavior\system\SystemBehavior;
use service\HookService;
use think\Session;

/**
 * Class SystemAdmin
 * @package app\admin\model\system
 */
class SystemAdmin extends ModelBasic
{
    use ModelTrait;

    protected $insert = ['add_time'];

    public static function setAddTimeAttr($value)
    {
        return time();
    }

    public static function setRolesAttr($value)
    {
        return is_array($value) ? implode(',', $value) : $value;
    }


    /**
     * 用户登陆
     * @param string $account 账号
     * @param string $pwd 密码
     * @param string $verify 验证码
     * @return bool 登陆成功失败
     */
    public static function login($account,$pwd)
    {
        $adminInfo = self::get(compact('account'));
        if(!$adminInfo) return self::setErrorInfo('登陆的账号不存在!');
        if($adminInfo['pwd'] != md5($pwd)) return self::setErrorInfo('账号或密码错误，请重新输入');
        if(!$adminInfo['status']) return self::setErrorInfo('该账号已被关闭!');
        self::setLoginInfo($adminInfo);
        HookService::afterListen('system_admin_login',$adminInfo,null,false,SystemBehavior::class);
        return true;
    }

    /**
     *  保存当前登陆用户信息
     */
    public static function setLoginInfo($adminInfo)
    {
        Session::set('adminId',$adminInfo['id']);
        Session::set('adminInfo',$adminInfo);
    }

    /**
     * 清空当前登陆用户信息
     */
    public static function clearLoginInfo()
    {
        Session::delete('adminInfo');
        Session::delete('adminId');
        Session::clear();
    }

    /**
     * 检查用户登陆状态
     * @return bool
     */
    public static function hasActiveAdmin()
    {
        return Session::has('adminId') && Session::has('adminInfo');
    }

    /**
     * 获得登陆用户信息
     * @return mixed
     */
    public static function activeAdminInfoOrFail()
    {
        $adminInfo = Session::get('adminInfo');
        if(!$adminInfo)  exception('请登陆');
        if(!$adminInfo['status']) exception('该账号已被关闭!');
        return $adminInfo;
    }

    /**
     * 获得登陆用户Id 如果没有直接抛出错误
     * @return mixed
     */
    public static function activeAdminIdOrFail()
    {
        $adminId = Session::get('adminId');
        if(!$adminId) exception('访问用户为登陆登陆!');
        return $adminId;
    }

    /**
     * @return array
     */
    public static function activeAdminAuthOrFail()
    {
        $adminInfo = self::activeAdminInfoOrFail();
        return $adminInfo->level === 0 ? SystemRole::getAllAuth() : SystemRole::rolesByAuth($adminInfo->roles);
    }

    /**
     * 获得有效管理员信息
     * @param $id
     * @return static
     */
    public static function getValidAdminInfoOrFail($id)
    {
        $adminInfo = self::get($id);
        if(!$adminInfo) exception('用户不能存在!');
        if(!$adminInfo['status']) exception('该账号已被关闭!');
        return $adminInfo;
    }

    /**
     * @param $field
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function getOrdAdmin($field = 'real_name,id',$level = 0){
        return self::where('level','>=',$level)->field($field)->select();
    }

    public static function getTopAdmin($field = 'real_name,id')
    {
        return self::where('level',0)->field($field)->select();
    }

    /**
     * @param $where
     * @return array
     */
    public static function systemPage($where){
        $model = new self;
        if($where['name'] != ''){
            $model = $model->where('account','LIKE',"%$where[name]%");
            $model = $model->where('real_name','LIKE',"%$where[name]%");
        }
        if($where['roles'] != '')
            $model = $model->where("CONCAT(',',roles,',')  LIKE '%,$where[roles],%'");
        $model = $model->where('level','=',$where['level'])->where('is_del',0);
        return self::page($model,function($admin,$key){
            $admin->roles = SystemRole::where('id','IN',$admin->roles)->column('role_name');
        },$where);
    }
}