<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2023 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

/** 事件定义文件
 * 调用事件示例：
 * @param mixed $event 事件名（或者类名）
 * @param mixed $args  参数
 * event($event,$args);
 * event('OrderCreateAfterListener',$order);
*/ 

return [
    'bind' => [],

    'listen' => [
        'AppInit' => [],
        'HttpRun' => [],
        'HttpEnd' => [\app\listener\http\HttpEndListener::class], //HTTP请求结束回调事件
        'LogLevel' => [],
        'LogWrite' => [],
        'QueueStartListener' => [\app\listener\queue\QueueStartListener::class],
        'UserLoginListener' => [\app\listener\user\LoginListener::class],
        'AdminLoginListener' => [\app\listener\admin\AdminLoginListener::class],//管理员登录
        'UserRegisterListener' => [\app\listener\user\RegisterListener::class], //用户注册后置事件
        'WechatAuthListener' => [\app\listener\wechat\AuthListener::class], //用户授权后置事件
        'OrderCreateAfterListener' => [\app\listener\order\OrderCreateAfterListener::class], //订单创建后置事件
        'OrderPaySuccessListener' => [\app\listener\order\OrderPaySuccessListener::class], //订单支付成功后置事件
        'OrderDeliveryListener' => [\app\listener\order\OrderDeliveryListener::class], //订单发货后置事件
        'OrderTakeListener' => [\app\listener\order\OrderTakeListener::class], //订单收货后置事件
        'OrderRefundCreateAfterListener' => [\app\listener\order\OrderRefundCreateAfterListener::class], //售后单生成后置事件
        'OrderRefundCancelAfterListener' => [\app\listener\order\OrderRefundCancelAfterListener::class], //售后单取消后置事件
        'OutPushListener' => [\app\listener\out\OutPushListener::class], //对外推送事件
        'UserLevelListener' => [\app\listener\user\UserLevelListener::class], //用户升级事件
        'UserVisitListener' => [\app\listener\user\UserVisitListener::class], //用户访问事件
        'NoticeListener' => [\app\listener\notice\NoticeListener::class], //通知->消息事件
        'CustomNoticeListener' => [\app\listener\notice\CustomNoticeListener::class], //通知->自定义消息发送事件
        'NotifyListener' => [\app\listener\pay\NotifyListener::class],//支付异步回调
        'OrderShippingListener' => [\app\listener\order\OrderShippingListener::class],//小程序发货管理
        'CustomEventListener' => [\app\listener\CustomEventListener::class],//自定义事件
    ],
];


