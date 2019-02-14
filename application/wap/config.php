<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: honor <763569752@qq.com>
// +----------------------------------------------------------------------

return [
    // 默认控制器名
    'default_controller'     => 'Index',
    // 默认操作名
    'default_action'         => 'index',
    // 自动搜索控制器
    'controller_auto_search' => true,
    'session'                => [
        // SESSION 前缀
        'prefix'         => 'wap',
        // 驱动方式 支持redis memcache memcached
        'type'           => '',
        // 是否自动开启 SESSION
        'auto_start'     => true,
    ],
    'template'               => [
        // 模板引擎类型 支持 php think 支持扩展
        'type'         => 'Think',
        // 模板路径
        'view_path'    => APP_PATH .'wap/view/first/',
        // 模板后缀
        'view_suffix'  => 'html',
        // 模板文件名分隔符
        'view_depr'    => DS,
        // 模板引擎普通标签开始标记
        'tpl_begin'    => '{',
        // 模板引擎普通标签结束标记
        'tpl_end'      => '}',
        // 标签库标签开始标记
        'taglib_begin' => '{',
        // 标签库标签结束标记
        'taglib_end'   => '}',
    ],
    // 视图输出字符串内容替换
    'view_replace_str'       => [
        '{__PLUG_PATH}'=>PUBILC_PATH.'static/plug/',
        '{__STATIC_PATH}'=>PUBILC_PATH.'static/',
        '{__PUBLIC_PATH}'=>PUBILC_PATH,
        '{__WAP_PATH}'=>PUBILC_PATH.'wap/first/'
    ],
    //wap异常处理
    'exception_handle' => \app\wap\controller\WapException::class,
    'empty_controller' =>'AuthController'
];
