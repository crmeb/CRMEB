<?php
/**
 * Created by CRMEB.
 * Copyright (c) 2017~2019 http://www.crmeb.com All rights reserved.
 * Author: liaofei <136327134@qq.com>
 * Date: 2019/3/27 21:42
 */

namespace app\core\model\user;

use traits\ModelTrait;
use basic\ModelBasic;
/**
 * 用户等级完成任务记录 model
 * Class UserTaskFinish
 * @package app\core\model\user
 */

class UserTaskFinish extends ModelBasic
{
    use ModelTrait;
    /*
     * 设置任务完成情况
     * @param int $uid 用户uid
     * @param int $task_id 任务id
     * @return Boolean
     * */
    public static function setFinish($uid,$task_id)
    {
        $add_time=time();
        if(self::be(['uid'=>$uid,'task_id'=>$task_id])) return true;
        return self::set(compact('uid','task_id','add_time'));
    }
}