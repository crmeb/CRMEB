<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace modules\product\virtual;

use crmeb\trade\order\type\OrderTypeRegistry;
use crmeb\trade\order\aftersale\AfterSaleRuleRegistry;
use crmeb\trade\product\ProductTypeRegistry;
use modules\product\virtual\handler\VirtualAfterSaleHandler;
use modules\product\virtual\handler\VirtualOrderAdapter;
use think\App as ThinkApp;

/**
 * 网盘商品插件入口
 * 注册产品类型 Handler、订单流转与售后规则，加载本模块事件监听器
 */
class App
{
    public static function register(ThinkApp $app): void
    {
        // 注册产品类型 Handler（ProductTypeRegistry 单例由 app/AppService.php 在启动时注入容器）
        $app->make(ProductTypeRegistry::class)->register('virtual', Handler::class);

        // 注册订单流转定义
        OrderTypeRegistry::register('virtual_order', VirtualOrderAdapter::class);

        // 注册售后规则（product_type 维度）：网盘仅支持退款，发放后不可退
        AfterSaleRuleRegistry::registerProductType('virtual', VirtualAfterSaleHandler::class);
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
