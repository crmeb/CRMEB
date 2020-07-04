<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 事件定义文件
return [
    'bind'      => [

    ],

    'listen'    => [
        'AppInit'  => [],
        'HttpRun'  => [],
        'HttpEnd'  => [],
        'LogLevel' => [],
        'LogWrite' => [],
    ],

    'subscribe' => [
        crmeb\subscribes\SystemSubscribe::class,//后台系统事件订阅类
        crmeb\subscribes\OrderSubscribe::class,//订单事件订阅类
        crmeb\subscribes\ProductSubscribe::class,//产品事件订阅类
        crmeb\subscribes\UserSubscribe::class,//用户事件订阅类
        crmeb\subscribes\MaterialSubscribe::class,//素材事件订阅类
        crmeb\subscribes\MessageSubscribe::class,//消息事件订阅类
        crmeb\subscribes\TaskSubscribe::class,//定时任务事件订阅类
    ],
];
