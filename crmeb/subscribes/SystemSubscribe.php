<?php
namespace crmeb\subscribes;

use app\admin\model\system\SystemAdmin;
use app\admin\model\system\SystemLog;
/**
 * 后台系统事件
 * Class SystemSubscribe
 * @package crmeb\subscribes
 */
class SystemSubscribe
{

    public function handle()
    {

    }

    /**
     * 添加管理员访问记录
     * @param $event
     */
    public function onAdminVisit($event)
    {
        list($adminInfo,$type) = $event;
        if(strtolower(app('request')->controller()) != 'index') SystemLog::adminVisit($adminInfo->id,$adminInfo->account,$type);
    }

    /**
     * 添加管理员最后登录时间和ip
     * @param $event
     */
    public function onSystemAdminLoginAfter($event)
    {
        list($adminInfo) = $event;
        SystemAdmin::edit(['last_ip'=>app('request')->ip(),'last_time'=>time()],$adminInfo['id']);
    }

    /**
     * 商户注册成功之后
     * @param $event
     */
    public function onMerchantRegisterAfter($event)
    {
      list($merchantInfo) = $event;
    }

}