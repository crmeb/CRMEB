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
    // 视图输出字符串内容替换
    'view_replace_str'       => [
        '{__ADMIN_PATH}'=>PUBILC_PATH.'/system/',
        '{__FRAME_PATH}'=>PUBILC_PATH.'/system/frame/',
        '{__PLUG_PATH}'=>PUBILC_PATH.'/static/plug/',
        '{__MODULE_PATH}'=>PUBILC_PATH.'/system/module/',
        '{__STATIC_PATH}'=>PUBILC_PATH.'/static/',
        '{__PUBLIC_PATH}'=>PUBILC_PATH.'/'
    ],
    'system_wechat_tag' => '_system_wechat'
];
