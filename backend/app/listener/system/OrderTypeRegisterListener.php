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
declare(strict_types=1);

namespace app\listener\system;

use crmeb\trade\order\type\OrderTypeRegistry;

/**
 * 订单类型注册监听器
 *
 * 响应 AppService boot() 阶段触发的 OrderTypeRegister 事件，
 * 将 app 层内置的订单类型适配器注册到 OrderTypeRegistry。
 *
 * 各 modules 层的订单适配器由各自模块 App.php 自行注册。
 *
 * @package app\listener\system
 */
class OrderTypeRegisterListener
{
    /**
     * 处理订单类型注册事件
     * OrderTypeRegistry 为静态注册，无需传递 registry 实例
     * @return void
     */
    public function handle(): void
    {
        // 线下收银（扫码收款单的适配器）
        OrderTypeRegistry::register('offline_receipt', \app\services\order\OfflineReceiptAdapter::class);
    }
}
