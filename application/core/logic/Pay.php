<?php

namespace app\core\logic;

use app\core\util\MiniProgramService;
use think\Request;

/**
 * Created by PhpStorm.
 * User: xurongyao <763569752@qq.com>
 * Date: 2019/4/8 5:48 PM
 */
class Pay
{
    public static function notify(){
        $request=Request::instance();
        switch (strtolower($request->param('notify_type','wenxin'))){
            case 'wenxin':
                break;
            case 'routine': //小程序支付回调
                MiniProgramService::handleNotify();
                break;
            case 'alipay':
                break;
            default:
                echo 121;
                break;
        }
    }
}