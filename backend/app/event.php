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

/** 事件定义文件
 * 调用事件示例：
 * @param mixed $event 事件名（或者类名）
 * @param mixed $args  参数
 * event($event,$args);
 * event('OrderCreateAfter',$order);
*/ 

return [
    'bind' => [],

    'listen' => [
        // ============ HTTP事件 ============
        'HttpEnd' => [\app\listener\http\HttpEndListener::class],

        // ============ 用户事件 ============
        // 事件名遵循"无 Listener 后缀"规范，类名保留 Listener 后缀
        'UserLogin' => [\app\listener\user\UserLoginListener::class],
        'UserLogout' => [\app\listener\user\UserLogoutListener::class],
        'UserRegister' => [\app\listener\user\UserRegisterListener::class, \app\listener\user\UserRegisterNoticeListener::class],
        'UserLevel' => [\app\listener\user\UserLevelListener::class],
        'UserVisit' => [\app\listener\user\UserVisitListener::class],
        'UserIntegralChange' => [\app\listener\user\UserIntegralChangeListener::class, \app\listener\user\UserIntegralChangeNoticeListener::class],
        'UserExpChange' => [\app\listener\user\UserExpChangeListener::class],
        'UserBalanceChange' => [\app\listener\user\UserBalanceChangeListener::class, \app\listener\user\UserBalanceChangeNoticeListener::class],
        // UserRecharge / UserRechargeRefund 已由 modules/marketing/recharge/event.php 自注册，勿在此重复注册
        'UserExtract' => [\app\listener\user\UserExtractListener::class],
        'UserExtractApply' => [\app\listener\user\UserExtractApplyListener::class],
        'UserRevenue' => [\app\listener\user\UserRevenueListener::class, \app\listener\user\UserRevenueNoticeListener::class],
        'UserBrokerageChange' => [\app\listener\user\UserBrokerageChangeListener::class],
        'UserCancel' => [\app\listener\user\UserCancelListener::class],

        // ============ 管理员事件 ============
        'AdminLogin' => [\app\listener\admin\AdminLoginListener::class],

        // ============ 注册中心事件（应用启动阶段触发，各监听器向对应 Registry 注册） ============
        'OrderTypeRegister' => [\app\listener\system\OrderTypeRegisterListener::class],
        // 统一长连接网关服务注册：返回实现 WsServiceHandlerInterface 的处理器类名，插件可在各自 event.php 追加监听
        'WebSocketServiceRegister' => [\app\listener\system\WsAdminServiceRegisterListener::class],

        // ============ 订单事件 ============
        // 各事件监听已迁移到 modules/product/{type}/listener/ + modules/distribution/listener/ + modules/channel/wechat_mp/listener/
        // 服务层统一使用大驼峰字符串触发事件：event('OrderPaySuccess', ...)、event('OrderCancel', ...) 等
        // 模块 event.php 以相同字符串注册监听，与触发方一一对应
        'OrderCreateAfter' => [\app\listener\order\OrderCreateAfterListener::class],
        //促销计价扩展点（价格链条位[20]）：中台在 OrderComputedServices::applyPromotion() 触发，
        //交易营销类插件（如满减/满赠）通过模块 event.php 注册监听器，以引用方式写回优惠金额与明细；此处留空占位声明
        'OrderPromotionCompute' => [],
        // ============ 用户购物车事件 ============
        'UserCart' => [\app\listener\user\UserCartListener::class],

        // ============ 商品事件 ============
        'Product' => [\app\listener\product\ProductListener::class],

        // ============ 支付事件 ============
        'Notify' => [\app\listener\pay\NotifyListener::class],

        // ============ 定时任务事件 ============
        'CrontabBeforeRun' => [\app\listener\crontab\CrontabBeforeRunListener::class],
        'CrontabAfterRun' => [\app\listener\crontab\CrontabAfterRunListener::class],
        'CrontabSuccess' => [\app\listener\crontab\CrontabSuccessListener::class],
        'CrontabFail' => [\app\listener\crontab\CrontabFailListener::class],

        /** 未支付订单提醒：发送催付通知 */
        'UnpaidOrderNotice' => [
            \app\listener\order\UnpaidOrderNoticeListener::class,
        ],
    ],

    'subscribe' => [
    ],
];
