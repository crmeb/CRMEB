<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
return [

    // 系统程序接收到发送过来的消息，通过此端口推送给对应的客户或者客服，以及后台新订单弹窗提醒
    'channel' => [
        //内部通讯监听端口
        'port' => 40003,
        //内部通讯连接地址；Docker 中由 PHP_WORKERMAN_CHANNEL_IP 指向 workerman 服务
        'ip' => env('workerman.channel_ip', '127.0.0.1'),
        //内部通讯监听地址；独立容器中监听所有网卡以便其他服务连接
        'listen_ip' => env('workerman.channel_listen_ip', '127.0.0.1'),
    ],

    // 统一长连接网关：系统所有 WebSocket 业务（后台通知 admin、客服聊天 kefu_chat、后续插件长链接…）
    // 共用此唯一对外端口，客户端通过 URL 参数 ?service=xxx 区分目标业务，
    // 业务处理器由 WebSocketServiceRegister 事件动态注册（见 app/command/Workerman.php）。
    // Nginx 反代只需配置一条 location /ws 即可永久支持全部及未来的长链接业务；
    // WSS/TLS 统一由前置反代终结，本服务始终以 ws 对内提供。
    'gateway' => [
        //协议
        'protocol' => 'websocket',
        //监听地址
        'ip' => '0.0.0.0',
        //监听端口
        'port' => 40001,
        //设置当前Worker实例启动多少个进程
        'serverCount' => 1,
    ],
];
