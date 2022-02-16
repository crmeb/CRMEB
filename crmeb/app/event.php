<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

// 事件定义文件

return [
    'bind' => [

    ],

    'listen' => [
        'AppInit' => [],
        'HttpRun' => [],
        'HttpEnd' => [],
        'LogLevel' => [],
        'LogWrite' => [],
        'StoreProductOrderDeliveryAfter' => [], // OrderSubscribe 送货 发送模板消息 admin模块 order.StoreOrder控制器/order.combinationOrder控制器
        'StoreProductOrderDeliveryGoodsAfter' => [], // OrderSubscribe 发货 发送模板消息 admin模块 order.StoreOrder控制器/order.combinationOrder控制器
        'StoreProductOrderRefundNAfter' => [], // OrderSubscribe 订单状态不退款 发送模板消息 admin模块 order.StoreOrder控制器/order.combinationOrder控制器
        'StoreProductOrderOffline' => [], // OrderSubscribe 线下付款成功后 admin模块 order.StoreOrder控制器/order.combinationOrder控制器
        'StoreProductOrderEditAfter' => [], // OrderSubscribe 修改订单金额 admin模块 order.StoreOrder控制器/order.combinationOrder控制器
        'StoreProductOrderDistributionAfter' => [], // OrderSubscribe 修改配送信息 admin模块 order.StoreOrder控制器/order.combinationOrder控制器
        'StoreProductOrderOver' => [], // OrderSubscribe 订单全部商品评价完  models模块 store.StoreOrder Model
        'SystemAdminLoginAfter' => [], // SystemSubscribe 添加管理员最后登录时间和ip 添加管理员访问记录 admin模块 system.SystemAdmin控制器
        'StoreProductSetCartAfter' => [], // ProductSubscribe 加入购物车成功之后  wap模块 AuthApi控制器
        'WechatMessageBefore' => [], // MessageSubscribe 微信消息前置操作  crmeb\services\WechatService
        'WechatEventUnsubscribeBefore' => [], // MessageSubscribe 用户取消关注公众号前置操作  crmeb\services\WechatService
        'user.login' => [\app\listener\user\Login::class], //
        'user.register' => [\app\listener\user\Register::class], //用户注册后置事件
        'wechat.auth' => [\app\listener\wechat\Auth::class], //用户授权后置事件
        'order.orderCreateAfter' => [\app\listener\order\OrderCreateAfter::class], //订单创建后置事件
        'order.orderPaySuccess' => [\app\listener\order\OrderPaySuccess::class], //订单支付成功后置事件
        'order.orderDelivery' => [\app\listener\order\OrderDelivery::class], //订单发货后置事件
        'order.orderTake' => [\app\listener\order\OrderTake::class], //订单收货后置事件
        'order.orderRefund' => [\app\listener\order\OrderRefund::class], //订单退款后置事件
        'user.userLevel' => [\app\listener\user\UserLevel::class], //用户升级事件
        'user.userVisit' => [\app\listener\user\UserVisit::class], //用户访问事件
        'notice.notice' => [\app\listener\notice\Notice::class], //通知->消息事件
    ],
    'subscribe' => [
        crmeb\subscribes\TaskSubscribe::class,//定时任务事件订阅类
    ]
];


