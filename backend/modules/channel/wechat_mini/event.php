<?php
/**
 * 微信小程序渠道事件注册
 * 事件名使用大驼峰字符串，与服务层 event() 触发保持一致
 */
return [
    'listen' => [
        // 小程序 API 代理调用
        'MiniProgramApiCall' => [
            \modules\channel\wechat_mini\listener\MiniProgramApiCallListener::class,
        ],
        // 小程序码生成（推广海报等场景调用）
        'MiniProgramCodeGenerate' => [
            \modules\channel\wechat_mini\listener\MiniProgramCodeGenerateListener::class,
        ],
        // 渠道服务代理（响应 app 层服务方法调用，避免直接引用 modules）
        'ChannelServiceProxy' => [
            \modules\channel\wechat_mini\listener\ChannelServiceProxyListener::class,
        ],
    ],
];
