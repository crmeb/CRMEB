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

// 事件定义文件
return [
    'bind'      => [

    ],

    'listen'    => [
        'AppInit'  => [],
        'HttpRun'  => [],
        'HttpEnd'  => [],
        'LogLevel' => [],
        'LogWrite' => [],
        'task_2'=>[],//2秒钟执行的方法
        'task_6'=>[],//6秒钟执行的方法
        'task_10'=>[],//10秒钟执行的方法
        'task_30'=>[],//30秒钟执行的方法
        'task_60'=>[],//60秒钟执行的方法
        'task_180'=>[],//180秒钟执行的方法
        'task_300'=>[],//300秒钟执行的方法
        'StoreProductOrderDeliveryAfter' => [], // OrderSubscribe 送货 发送模板消息 admin模块 order.StoreOrder控制器/order.combinationOrder控制器
        'StoreProductOrderDeliveryGoodsAfter' => [], // OrderSubscribe 发货 发送模板消息 admin模块 order.StoreOrder控制器/order.combinationOrder控制器
        'StoreProductOrderRefundNAfter' => [], // OrderSubscribe 订单状态不退款 发送模板消息 admin模块 order.StoreOrder控制器/order.combinationOrder控制器
        'StoreProductOrderOffline' => [], // OrderSubscribe 线下付款成功后 admin模块 order.StoreOrder控制器/order.combinationOrder控制器
        'StoreProductOrderEditAfter' => [], // OrderSubscribe 修改订单金额 admin模块 order.StoreOrder控制器/order.combinationOrder控制器
        'StoreProductOrderDistributionAfter' => [], // OrderSubscribe 修改配送信息 admin模块 order.StoreOrder控制器/order.combinationOrder控制器
        'StoreProductOrderOver' => [], // OrderSubscribe 订单全部产品评价完  models模块 store.StoreOrder Model
        'StoreOrderRegressionAllAfter' => [], // OrderSubscribe 回退所有  未支付和已退款的状态下才可以退积分退库存退优惠券  models模块 store.StoreOrder Model
        'AdminVisit' => [], // SystemSubscribe 添加管理员访问记录 admin模块 AuthController控制器
        'SystemAdminLoginAfter' => [], // SystemSubscribe 添加管理员最后登录时间和ip 添加管理员访问记录 admin模块 system.SystemAdmin控制器
        'StoreProductSetCartAfter' => [], // ProductSubscribe 加入购物车成功之后  wap模块 AuthApi控制器
        'StoreProductUserOperationConfirmAfter' => [], // ProductSubscribe 用户操作产品添加事件  用户点赞产品  用户收藏产品 Models模块 store.StoreProductRelation Model
        'StoreProductUserOperationCancelAfter' => [], // ProductSubscribe 用户操作产品取消事件  用户取消点赞产品  用户取消收藏产品 Models模块 store.StoreProductRelation Model
        'WechatMaterialAfter' => [], // MaterialSubscribe 微信公众号 图片/声音 转media 存入数据库  admin模块 wechat.WechatReplyModel
        'WechatMessageBefore' => [], // MessageSubscribe 微信消息前置操作  crmeb\services\WechatService
        'WechatEventUnsubscribeBefore' => [], // MessageSubscribe 用户取消关注公众号前置操作  crmeb\services\WechatService
        'WechatOauthAfter' => [], // UserSubscribe 微信授权成功后  wap模块 WapBasic控制器
        'InitLogin' => [], // UserSubscribe 微信授权成功后  ebapi模块 Basic控制器
        'UserLevelAfter' => [], // UserSubscribe 检查是否能成为会员  models模块 user.UserSign Model  store.StoreOrder Model user.UserBill Model
        'OrderCreated' => [], //用户订单创建成功
        'OrderPaySuccess' => [], //用户订单支付成功
        'OrderCreateAgain' => [], //用户再次下单
        'UserOrderRemoved' => [], //用户删除订单
        'UserOrderTake' => [], //用户确认收货
        'UserCommented' => [], //用户评价商品
        'RechargeSuccess' => [], //用户充值成功后
        'ImportNowMoney' => [], //用户佣金转成余额成功后
    ],

    'subscribe' => [
        crmeb\subscribes\SystemSubscribe::class,
        crmeb\subscribes\OrderSubscribe::class,
        crmeb\subscribes\ProductSubscribe::class,
        crmeb\subscribes\UserSubscribe::class,
        crmeb\subscribes\MaterialSubscribe::class,
        crmeb\subscribes\MessageSubscribe::class,
        crmeb\subscribes\TaskSubscribe::class,
    ],
];
