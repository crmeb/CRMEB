<?php

return [
    'admin' => [
        //协议
        'protocol' => 'websocket',
        //监听地址
        'ip' => '0.0.0.0',
        //监听端口
        'port' => 20002,
        //设置当前Worker实例启动多少个进程
        'serverCount' => 1,
    ],

    'chat' => [
        //协议
        'protocol' => 'websocket',
        //监听地址
        'ip' => '0.0.0.0',
        //监听端口
        'port' => 20003,
        //设置当前Worker实例启动多少个进程
        'serverCount' => 1,
    ],

    'channel' => [
        //内部通讯监听端口
        'port' => 20012,
        //内部通讯地址
        'ip' => '127.0.0.1',
    ],

];