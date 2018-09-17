<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/28
 */

namespace app\admin\model\system;


use traits\ModelTrait;
use basic\ModelBasic;
use think\Request;
use app\admin\model\system\SystemMenus;
use app\admin\model\system\SystemAdmin;

/**
 * 管理员操作记录
 * Class SystemLog
 * @package app\admin\model\system
 */
class SystemLog extends ModelBasic
{
    use ModelTrait;

    protected $insert = ['add_time'];

    protected function setAddTimeAttr()
    {
        return time();
    }


    /**
     * 管理员访问记录
     * @param Request $request
     */
    public static function adminVisit($adminId,$adminName,$type)
    {
        $request = Request::instance();
        $module = $request->module();
        $controller = $request->controller();
        $action = $request->action();
        $route = $request->route();
        $data = [
            'method'=>$request->method(),
            'admin_id'=>$adminId,
            'admin_name'=>$adminName,
            'path'=>SystemMenus::getAuthName($action,$controller,$module,$route),
            'page'=>SystemMenus::getVisitName($action,$controller,$module,$route)?:'未知',
            'ip'=>$request->ip(),
            'type'=>$type
        ];
        return self::set($data);
    }

    /**
     * 手动添加管理员当前页面访问记录
     * @param array $adminInfo
     * @param string $page 页面名称
     * @return object
     */
    public static function setCurrentVisit($adminInfo, $page)
    {
        $request = Request::instance();
        $module = $request->module();
        $controller = $request->controller();
        $action = $request->action();
        $route = $request->route();
        $data = [
            'method'=>$request->method(),
            'admin_id'=>$adminInfo['id'],
            'path'=>SystemMenus::getAuthName($action,$controller,$module,$route),
            'page'=>$page,
            'ip'=>$request->ip()
        ];
        return self::set($data);
    }

    /**
     * 获取管理员访问记录
     * */
    public static function systemPage($where = array()){
        $model = new self;
        $model = $model->alias('l');
        if($where['pages'] !== '') $model = $model->where('l.page','LIKE',"%$where[pages]%");
        if($where['admin_id'] != '')
            $adminIds = $where['admin_id'];
        else
            $adminIds = SystemAdmin::where('level','>=',$where['level'])->column('id');
        $model = $model->where('l.admin_id','IN',$adminIds);
        if($where['data'] !== ''){
            list($startTime,$endTime) = explode(' - ',$where['data']);
            $model = $model->where('l.add_time','>',strtotime($startTime));
            $model = $model->where('l.add_time','<',strtotime($endTime));
        }
        $model->where('l.type','system');
        $model = $model->order('l.id desc');
        return self::page($model,$where);
    }
    /**
     * @day
     * 删除超过90天的日志
     */
    public static function deleteLog($day = 90){
        $model = new self;
        $times = $day*86400;
        $model->where('add_time','<',time()-$times);
        $model->delete();
    }
}