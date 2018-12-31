<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/30
 */

namespace behavior\system;

use app\admin\model\system\SystemAdmin;
use app\admin\model\system\SystemLog;
use think\Request;

/**
 * 系统后台行为
 * Class SystemBehavior
 * @package behavior\system
 */
class SystemBehavior
{
    public static function adminVisit($adminInfo,$type = 'system')
    {
        if(strtolower(Request::instance()->controller()) != 'index') SystemLog::adminVisit($adminInfo->id,$adminInfo->account,$type);
    }

    public static function systemAdminLoginAfter($adminInfo)
    {
        SystemAdmin::edit(['last_ip'=>Request::instance()->ip(),'last_time'=>time()],$adminInfo['id']);
    }

    /**
     * 商户注册成功之后
     */
    public static function merchantRegisterAfter($merchantInfo)
    {

    }

}