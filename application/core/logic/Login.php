<?php

/**
 * Created by PhpStorm.
 * User: xurongyao <763569752@qq.com>
 * Date: 2019/4/8 7:29 PM
 */

namespace app\core\logic;

use app\core\traits\LogicTrait;
use service\JsonService;

class Login
{
    use LogicTrait;

    protected  $providers=[
        \app\core\logic\routine\RoutineLogin::class,
    ];

    public static function login_ing($action)
    {
        if($action instanceof Login){
            return self::instance()->$action->login();
        }else{
            return JsonService::fail('访问的方法不存在！');
        }

    }

}