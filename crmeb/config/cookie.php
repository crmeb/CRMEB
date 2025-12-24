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

// +----------------------------------------------------------------------
// | Cookie设置
// +----------------------------------------------------------------------
use think\facade\Env;

return [
    // cookie 保存时间
    'expire'    => 0,
    // cookie 保存路径
    'path'      => '/',
    // cookie 有效域名
    'domain'    => Env::get('cookie.domain', ''),
    // cookie 启用安全传输
    'secure'    => Env::get('cookie.secure', false),
    // httponly设置
    'httponly'  => Env::get('cookie.httponly', false),
    // 是否使用 setcookie
    'setcookie' => true,
    // CORS：允许的 Origin 白名单（完整 Origin：scheme://host[:port]），逗号分隔
    // - 优先使用该白名单；为空时退化为按 cookie.domain（同主域/子域）放行
    'cors_allowed_origins' => array_values(array_filter(array_map('trim', explode(',', Env::get('cors.allowed_origins', ''))))),
    // 跨域header
    'header'    => [
        'Access-Control-Allow-Origin'       => '*',
        'Access-Control-Allow-Headers'      => 'Authori-zation,Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-Requested-With, Form-type, Cb-lang, Invalid-zation',
        'Access-Control-Allow-Methods'      => 'GET,POST,PATCH,PUT,DELETE,OPTIONS,DELETE',
        'Access-Control-Max-Age'            =>  '1728000',
        'Access-Control-Allow-Credentials'  => 'true'
    ],
    // token名称
    'token_name' => 'Authori-zation',
];
