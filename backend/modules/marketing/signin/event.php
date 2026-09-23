<?php
// 事件在 modules/marketing/signin/event.php 中直接注册
// UserSignServices::sendSignRemind() 触发 event('Sign', ['sign_remind', $data])
// 由 SignRemindNoticeListener 监听并调用 Notice 统一管道发送消息
return [
    "listen" => [
        'Sign' => [
            \modules\marketing\signin\listener\SignRemindNoticeListener::class,
        ],
        // 营销服务代理（响应 app 层服务方法调用，避免直接引用 modules）
        'MarketingServiceProxy' => [
            \modules\marketing\signin\listener\MarketingServiceProxyListener::class,
        ],
    ],
    "subscribe" => []
];
