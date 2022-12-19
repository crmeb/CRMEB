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
use think\facade\Env;

// +----------------------------------------------------------------------
// | 日志设置
// +----------------------------------------------------------------------
return [
    // 默认日志记录通道
    'default'      => Env::get('log.channel', 'file'),
    // 日志记录级别
    'level'        => ['error', 'warning', 'fail', 'success'],
    // 日志类型记录的通道 ['error'=>'email',...]
    'type_channel' => [],
    //是否开启业务成功日志
    'success_log'  => false,
    //是否开启业务失败日志
    'fail_log'     => false,
    // 日志通道列表
    'channels'     => [
        'file' => [
            // 日志记录方式
            'type'        => 'File',
            // 日志保存目录
            'path'        => app()->getRuntimePath() . 'log' . DIRECTORY_SEPARATOR,
            // 单文件日志写入
            'single'      => false,
            // 独立日志级别
            'apart_level' => ['error', 'fail', 'success'],
            // 最大日志文件数量
            'max_files'   => 60,
            'time_format' => 'Y-m-d H:i:s',
            'format'      => '%s|%s|%s'
        ],
        // 其它日志通道配置
    ],
];
