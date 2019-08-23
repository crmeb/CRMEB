<?php
/**
 * Created by CRMEB.
 * Copyright (c) 2017~2019 http://www.crmeb.com All rights reserved.
 * Author: liaofei <136327134@qq.com>
 * Date: 2019/4/3 9:13
 */


namespace app\core\model\user;

use traits\ModelTrait;
use basic\ModelBasic;

class User extends ModelBasic
{
    use ModelTrait;

    /*
     * 获取会员是否被清除过的时间
     * */
    public static function getCleanTime($uid)
    {
        $user=self::where('uid',$uid)->field(['add_time','clean_time'])->find();
        if(!$user) return 0;
        return $user['clean_time'] ? $user['clean_time'] : $user['add_time'];
    }
}