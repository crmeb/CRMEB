<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------


return [
    'session'                => [
        // SESSION 前缀
        'prefix'         => 'admin',
        // 驱动方式 支持redis memcache memcached
        'type'           => '',
        // 是否自动开启 SESSION
        'auto_start'     => true,
    ],
    'app_debug'              => false,
    // 应用Trace
    'app_trace'              => false,

    'exception_handle' =>\basic\AdminException::class,
    'empty_controller' =>'Index',
    // 视图输出字符串内容替换
    'view_replace_str'       => [
        '{__ADMIN_PATH}'   => PUBILC_PATH.'system/',//后台
        '{__FRAME_PATH}'   => PUBILC_PATH.'system/frame/',//H+框架
        '{__PLUG_PATH}'    => PUBILC_PATH.'static/plug/',//前后台通用
        '{__MODULE_PATH}'  => PUBILC_PATH.'system/module/',//后台功能模块
        '{__STATIC_PATH}'  => PUBILC_PATH.'static/',//全站通用
        '{__PUBLIC_PATH}'  => PUBILC_PATH,//静态资源路径
    ]
];
