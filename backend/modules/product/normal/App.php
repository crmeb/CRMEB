<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace modules\product\normal;

use crmeb\trade\order\type\OrderTypeRegistry;
use crmeb\trade\product\ProductTypeRegistry;
use crmeb\trade\order\aftersale\AfterSaleRuleRegistry;
use crmeb\crontab\CrontabTaskRegistry;
use modules\product\normal\crontab\NormalAdvanceOffTask;
use modules\product\normal\crontab\NormalOrderCancelTask;
use modules\product\normal\handler\NormalAfterSaleHandler;
use modules\product\normal\handler\NormalOrderAdapter;
use think\App as ThinkApp;

/**
 * 普通实物商品插件入口
 * 注册产品类型 Handler、订单流转与售后规则，加载本模块事件监听器
 */
class App
{
    public static function register(ThinkApp $app): void
    {
        // 注册产品类型 Handler（ProductTypeRegistry 单例由 app/AppService.php 在启动时注入容器）
        $app->make(ProductTypeRegistry::class)->register('normal', Handler::class);

        // 注册订单流转（NormalOrderAdapter 替代旧的 NormalOrderAdapter）
        OrderTypeRegistry::register('normal_order', NormalOrderAdapter::class);

        // 注册售后规则（product_type 维度，规则提供者与生命周期回调合一）
        AfterSaleRuleRegistry::registerProductType('normal', NormalAfterSaleHandler::class);

        // 注册定时任务（mark 与 eb_system_crontab 种子 orderCancel 对齐：系统定时器每30秒触发未支付自动取消）
        $app->make(CrontabTaskRegistry::class)->register('orderCancel', NormalOrderCancelTask::class);

        // 注册定时任务（mark 与 eb_system_crontab 种子 advanceOff 对齐：预售商品到期自动下架）
        $app->make(CrontabTaskRegistry::class)->register('advanceOff', NormalAdvanceOffTask::class);
    }

    public static function boot(ThinkApp $app): void
    {
        // 加载本模块事件监听器
        $eventFile = __DIR__ . '/event.php';
        if (is_file($eventFile)) {
            $event = include $eventFile;
            if (is_array($event)) {
                $app->loadEvent($event);
            }
        }
    }
}
