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
namespace app;

use crmeb\plugin\ModuleRegistry;
use crmeb\plugin\PluginManager;
use crmeb\services\schema\SchemaPageRegistry;
use crmeb\utils\Json;
use crmeb\trade\mode\TradeModeRegistry;
use crmeb\trade\mode\DefaultTradeModeHandler;
use crmeb\trade\order\type\OrderCenterSyncInterface;
use crmeb\trade\product\ProductQueryInterface;
use crmeb\trade\product\ProductTypeRegistry;
use app\services\user\member\MemberTradeModeHandler;
use app\services\order\OrderCenterServices;
use app\dao\product\CenterProductDao;
use think\Service;

class AppService extends Service
{

    public $bind = [
        'json' => Json::class,
    ];

    /**
     * 服务注册
     * 屏蔽 E_DEPRECATED，防止部分第三方库(guzzle3 等)在 PHP 8.1+ 下的废弃接口告警被框架转为异常
     * (EasyWeChat 已升级 6.x,不再触发 PHP 8.1+ 弃用告警)
     */
    public function register()
    {
        error_reporting(E_ALL & ~E_DEPRECATED);

        // 注册交易模式注册中心为单例
        $this->app->bind(TradeModeRegistry::class, function () {
            return new TradeModeRegistry();
        });

        // 注册通用页面提供者注册中心为单例（约定式扫描 modules 下各插件 schema 页面）
        $this->app->bind(SchemaPageRegistry::class, function () {
            return new SchemaPageRegistry();
        });

        // 注册产品类型注册中心为单例（有状态，供各产品类型子模块注册 Handler）
        $this->app->instance(ProductTypeRegistry::class, new ProductTypeRegistry());

        // 绑定订单中台状态同步契约到 app 层实现（依赖反转：crmeb 核心层通过接口调用 app 层）
        $this->app->bind(OrderCenterSyncInterface::class, OrderCenterServices::class);

        // 绑定商品查询契约到 app 层实现（依赖反转：crmeb 核心层通过接口调用 app 层 dao）
        $this->app->bind(ProductQueryInterface::class, CenterProductDao::class);
    }

    public function boot()
    {
        defined('DS') || define('DS', DIRECTORY_SEPARATOR);

        $basePath = $this->app->getRootPath();

        // 模块扫描注册（基础设施，执行各模块 App::register() + boot()，加载模块事件）
        PluginManager::register($this->app, $basePath . 'modules/');
        $moduleRegistry = new ModuleRegistry($this->app);
        $moduleRegistry->registerAll($basePath . 'modules/');

        // 创建注册中心单例
        $tradeModeRegistry = $this->app->make(TradeModeRegistry::class);

        // app 层内置交易模式直接注册（普通购买 + 会员），modules 层交易模式由各自模块 App.php 注册
        $tradeModeRegistry->register('normal', DefaultTradeModeHandler::class);
        $tradeModeRegistry->register('member', MemberTradeModeHandler::class);

        // 统一触发注册事件：modules 层监听器各自注册订单类型
        // 新增订单类型时，只需在对应模块加监听器，无需修改本文件
        event('OrderTypeRegister');
    }
}
