<?php
/**
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/28
 */

namespace app\admin\model\system;


use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;

/**
 * 管理员操作记录
 * Class SystemLog
 * @package app\admin\model\system
 */
class SystemLog extends BaseModel
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
    protected $name = 'system_log';

    use ModelTrait;

    protected $insert = ['add_time'];

    protected function setAddTimeAttr()
    {
        return time();
    }


    /**
     * 管理员访问记录
     *
     * @param $adminId
     * @param $adminName
     * @param $type
     * @return SystemLog|\think\Model
     */
    public static function adminVisit($adminId,$adminName,$type)
    {
        $request = app('request');
        $module = $request->app();
        $controller = $request->controller();
        $action = $request->action();
        $route = $request->route();
        self::startTrans();
        try{
            $data = [
                'method'=>$request->app(),
                'admin_id'=>$adminId,
                'add_time'=>time(),
                'admin_name'=>$adminName,
                'path'=>SystemMenus::getAuthName($action,$controller,$module,$route),
                'page'=>SystemMenus::getVisitName($action,$controller,$module,$route)?:'未知',
                'ip'=>$request->ip(),
                'type'=>$type
            ];
            $res = self::create($data);
            if($res){
                self::commit();
                return true;
            }else{
                self::rollback();
                return false;
            }
        }catch (\Exception $e){
            self::rollback();
            return self::setErrorInfo($e->getMessage());
        }

    }

    /**
     * 手动添加管理员当前页面访问记录
     * @param array $adminInfo
     * @param string $page 页面名称
     * @return object
     */
    public static function setCurrentVisit($adminInfo, $page)
    {
        $request = app('request');
        $module = $request->app();
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
        return self::create($data);
    }

    /**
     * 获取管理员访问记录
     * @param array $where
     * @return array
     */
    public static function systemPage($where = array()){
        $model = new self;
        $model = $model->alias('l');
        if($where['pages'] !== '') $model = $model->where('l.page','LIKE',"%$where[pages]%");
        if($where['path'] !== '') $model = $model->where('l.path','LIKE',"%$where[path]%");
        if($where['ip'] !== '') $model = $model->where('l.ip','LIKE',"%$where[ip]%");
        if($where['admin_id'] != '')
            $adminIds = $where['admin_id'];
        else
            $adminIds = SystemAdmin::where('level','>=',$where['level'])->column('id','id');
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
     * 删除超过90天的日志
     * @throws \Exception
     */
    public static function deleteLog(){
        $model = new self;
        $model->where('add_time','<',time()-7776000);
        $model->delete();
    }
}