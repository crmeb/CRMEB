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

return [
    // 默认使用的数据库连接配置
    'default'         => Env::get('database.driver', 'mysql'),

    // 数据库连接配置信息
    'connections'     => [
        'mysql' => [
            // 数据库类型
            'type'            => Env::get('database.type', 'mysql'),
            // 服务器地址
            'hostname'        => Env::get('database.hostname', '127.0.0.1'),
            // 数据库名
            'database'        => Env::get('database.database', ''),
            // 用户名
            'username'        => Env::get('database.username', 'root'),
            // 密码
            'password'        => Env::get('database.password', ''),
            // 端口
            'hostport'        => Env::get('database.hostport', '3306'),
            // 连接dsn
            'dsn'             => '',
            // 数据库连接参数
            'params'          => [],
            // 数据库编码默认采用utf8
            'charset'         => Env::get('database.charset', 'utf8'),
            // 数据库表前缀
            'prefix'          => Env::get('database.prefix', 'eb_'),
            // 数据库调试模式
            'debug'           => Env::get('database.debug', true),
            // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
            'deploy'          => 0,
            // 数据库读写是否分离 主从式有效
            'rw_separate'     => false,
            // 读写分离后 主服务器数量
            'master_num'      => 1,
            // 指定从服务器序号
            'slave_no'        => '',
            // 是否严格检查字段是否存在
            'fields_strict'   => true,
            // 是否需要进行SQL性能分析
            'sql_explain'     => false,
            // Builder类
            'builder'         => '',
            // Query类
            'query'           => '',
            // 是否需要断线重连
            'break_reconnect' => true,
        ],
        'redis' =>[
            'type' => 'redis',
            // 服务器地址
            'hostname'        => Env::get('redis.hostname', '127.0.0.1'),
            // 数据库名
            'database'        => Env::get('redis.database', ''),
            // 密码
            'password'        => Env::get('redis.password', ''),
            // 端口
            'hostport'        => Env::get('redis.hostport', '6379'),

            'timeout'         => Env::get('redis.timeout', 0),

            'prefix'          => Env::get('redis.prefix', 'eb_'),
        ]
        // 更多的数据库配置信息
    ],

    // 自定义时间查询规则
    'time_query_rule' => [],
    // 自动写入时间戳字段
    'auto_timestamp'  => 'timestamp',
    // 时间字段取出后的默认时间格式
    'datetime_format' => 'Y-m-d H:i:s',
];
