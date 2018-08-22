<?php

namespace app\admin\controller;

use app\admin\model\system\SystemAdmin;
use app\admin\model\system\SystemMenus;
use app\admin\model\system\SystemRole;
use basic\SystemBasic;
use behavior\system\SystemBehavior;
use service\HookService;
use think\Url;

/**
 * 基类 所有控制器继承的类
 * Class AuthController
 * @package app\admin\controller
 */
class AuthController extends SystemBasic
{
    /**
     * 当前登陆管理员信息
     * @var
     */
    protected $adminInfo;

    /**
     * 当前登陆管理员ID
     * @var
     */
    protected $adminId;

    /**
     * 当前管理员权限
     * @var array
     */
    protected $auth = [];

    protected $skipLogController = ['index','common'];

    protected function _initialize()
    {
        parent::_initialize();
        if(!SystemAdmin::hasActiveAdmin()) return $this->redirect('Login/index');
        try{
            $adminInfo = SystemAdmin::activeAdminInfoOrFail();
        }catch (\Exception $e){
            return $this->failed(SystemAdmin::getErrorInfo($e->getMessage()),Url::build('Login/index'));
        }
        $this->adminInfo = $adminInfo;
        $this->adminId = $adminInfo['id'];
        $this->getActiveAdminInfo();
        $this->auth = SystemAdmin::activeAdminAuthOrFail();
        $this->adminInfo->level === 0 || $this->checkAuth();
        $this->assign('_admin',$this->adminInfo);
        HookService::listen('admin_visit',$this->adminInfo,'system',false,SystemBehavior::class);
    }


    protected function checkAuth($action = null,$controller = null,$module = null,array $route = [])
    {
        static $allAuth = null;
        if($allAuth === null) $allAuth = SystemRole::getAllAuth();
        if($module === null) $module = $this->request->module();
        if($controller === null) $controller = $this->request->controller();
        if($action === null) $action = $this->request->action();
        if(!count($route)) $route = $this->request->route();
        if(in_array(strtolower($controller),$this->skipLogController,true)) return true;
        $nowAuthName = SystemMenus::getAuthName($action,$controller,$module,$route);
        $baseNowAuthName =  SystemMenus::getAuthName($action,$controller,$module,[]);
        if((in_array($nowAuthName,$allAuth) && !in_array($nowAuthName,$this->auth)) || (in_array($baseNowAuthName,$allAuth) && !in_array($baseNowAuthName,$this->auth)))
            exit($this->failed('没有权限访问!'));
        return true;
    }


    /**
     * 获得当前用户最新信息
     * @return SystemAdmin
     */
    protected function getActiveAdminInfo()
    {
        $adminId = $this->adminId;
        $adminInfo = SystemAdmin::getValidAdminInfoOrFail($adminId);
        if(!$adminInfo) $this->failed(SystemAdmin::getErrorInfo('请登陆!'));
        $this->adminInfo = $adminInfo;
        SystemAdmin::setLoginInfo($adminInfo);
        return $adminInfo;
    }
}