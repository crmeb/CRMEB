<?php
/**
 * Created by CRMEB.
 * Copyright (c) 2017~2019 http://www.crmeb.com All rights reserved.
 * Author: liaofei <136327134@qq.com>
 * Date: 2019/4/3 16:36
 */

namespace app\core\util;

class ReturnCode
{
    //操作成功
    const SUCCESS = 200;
    //普通错误
    const ERROR = 400;
    //系统错误
    const SYSTEM_ERROR=405;
    //用户token验证成功，用户信息获取失败
    const USER_TOKEN_ERROR=402;
    //用户被禁止登录
    const USER_STATUS_ERROR=402;
    //access_token验证失效
    const ACCESS_TOKEN_TIMEOUT=-100;
    //数据库保存失败
    const DB_SAVE_ERROR = -1;
    //数据库查询失败
    const DB_READ_ERROR = -2;
    //api版本号不存在
    const EMPTY_PARAMS = -3;
    //api版本号不匹配
    const VERSION_INVALID = -4;

    public static function getConstants($code='') {
        $oClass = new \ReflectionClass(__CLASS__);
        $stants=$oClass->getConstants();
        if($code) return isset($stants[$code]) ? $stants[$code] : '';
        else return $stants;
    }

}