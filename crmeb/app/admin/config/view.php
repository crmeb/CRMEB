<?php
// +----------------------------------------------------------------------
// | 模板设置
// +----------------------------------------------------------------------

return [
    // 模板后缀
    'view_suffix'  => 'php',
    // 模板路径
    'view_path'    => app_path('view'),
    // 视图输出字符串内容替换
    'tpl_replace_string'       => [
        '{__PUBLIC_PATH}' =>  '/',              //public 目录
        '{__STATIC_PATH}' =>  '/static/',       //全局静态目录
        '{__PLUG_PATH}'   =>  '/static/plug/',  //全局静态插件
        '{__ADMIN_PATH}'  =>  '/system/',        //后台目录
        '{__FRAME_PATH}'  =>  '/system/frame/',  //后台框架
        '{__MODULE_PATH}' =>  '/system/module/', //后台模块
    ]
];
