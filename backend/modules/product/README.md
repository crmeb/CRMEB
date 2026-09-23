# product/ — 产品类型扩展模块（商品域）

## 功能概述

产品类型（ProductType）决定**卖什么**，是与交易模式（`modules/trade/`，怎么卖）**正交的维度**。
每个产品类型是一个独立的插件包，包含商品的履约形态、库存管理方式、物流/配送规则、售后规则等。

产品类型 Handler 继承 `crmeb/trade/product/BaseProductTypeHandler.php`，通过 `ProductTypeRegistry` 注册。

## 目录结构

```
product/
├── cardkey/    卡密商品    product_cardkey
├── normal/     实物商品    product_normal
├── virtual/    虚拟商品    product_virtual
├── App.php     骨架入口：创建 ProductTypeRegistry 容器单例（新增类型无需改动）
└── README.md   本文件
```

## 各类型说明

| 产品类型 | 目录 | source_type | 履约方式 | 售后支持 | 售后期限 | 需物流 | 兼容交易模式 | 用户端路由 |
|---------|------|-------------|---------|---------|---------|:------:|-------------|-----------|
| 实物 | `normal/` | `normal_order`（+遗留 `store_order`） | 物流发货 | 退款/退货退款/换货/维修 | 7天 | ✅ | normal/seckill/combination/bargain/integral | route/user.php |
| 虚拟 | `virtual/` | `virtual_order` | 自动发放 | 退款 | 24小时 | ❌ | normal/seckill | route/user.php |
| 卡密 | `cardkey/` | `cardkey_order` | 卡密派发 | 退款 | 1小时 | ❌ | normal | route/user.php |

## 注册机制：一个产品类型 = 三个注册维度

每个产品类型的 `App.php` 必须完成**三项注册**，缺一不可：

```php
// modules/product/{type}/App.php
public static function register(ThinkApp $app): void
{
    // ① 产品类型 Handler —— 库存/履约/详情扩展/选择契约
    $registry = $app->make(ProductTypeRegistry::class);
    $registry->register('{type}', \modules\product\{type}\Handler::class);

    // ② 订单适配器 —— source_type → 状态树/按钮/进度推导（缺了会抛「未注册的源表类型」异常）
    OrderTypeRegistry::register('{type}_order', __NAMESPACE__ . '\handler\{Type}OrderAdapter');
}

public static function boot(ThinkApp $app): void
{
    // 加载事件监听器
    $eventFile = __DIR__ . '/event.php';
    if (is_file($eventFile)) {
        $event = include $eventFile;
        if (is_array($event)) {
            $app->loadEvent($event);
        }
    }

    // ③ 售后规则 —— 按 product_type 维度注册
    AfterSaleRuleRegistry::register('product_type', '{type}', {Type}AfterSaleHandler::class);
}
```

| # | 注册中心 | 单例/实例化来源 | 漏注册的后果 |
|---|---|---|---|
| ① | `ProductTypeRegistry` | 本目录骨架 `product/App.php` 容器 `instance()` 单例 | 类型回退 normal，详情/库存逻辑错乱 |
| ② | `OrderTypeRegistry` | 纯静态类 | `getAdapter()` 抛 `RuntimeException: 未注册的源表类型` |
| ③ | `AfterSaleRuleRegistry` | 纯静态注册 | 该类型售后规则缺失，走默认兜底 |

> 售后规则按**两个维度**聚合：`order_category`（订单大类，goods 由 `crmeb/trade/order/aftersale/GoodsOrderCategoryProvider.php` 提供）∩ `product_type`（各类型自声明），详见核心层 `crmeb/trade/order/aftersale/` 目录。

## 兼容性声明

每个产品类型通过 `getCompatibility()` 声明支持的交易模式：

```php
public function getCompatibility(): array
{
    return [
        'supported_trade_modes' => ['normal', 'seckill', 'combination'],
        'requires_shipping'     => true,
        'supports_pickup'       => true,
        'supports_after_sale'   => true,
    ];
}
```

> ⚠️ 如实说明：该声明目前是**声明性元数据**，系统**未在运行时强制校验**（订单创建不会因组合不兼容而拦截）。新增产品类型/交易模式组合时，需自行在选品与下单校验中保证一致性，并同步维护声明值供人工对齐。

## 公共结构

```
{type}/
├── aftersale/          售后规则处理器（{Type}AfterSaleHandler）
├── controller/         控制器（admin + user）
├── crontab/            定时任务（normal 有取消/自动收货）
├── dao/  model/  service/  validate/
├── handler/            {Type}OrderAdapter 订单适配器
├── listener/           事件监听器（详情扩展/支付回调/取消回库存等）
├── pipeline/           订单创建管线扩展（OrderCreatePipeline）
├── route/              路由（admin.php / user.php / manage.php，中间件自行引入）
├── App.php             模块注册入口（三项注册）
├── Handler.php         主处理器（继承 BaseProductTypeHandler）
├── Installer.php       安装/卸载
├── event.php           事件注册（key 用大驼峰业务名，禁止带 Listener 后缀）
└── plugin.json         插件元数据（key 如 product_cardkey）
```

## 新增产品类型清单（如火车票、网课）

1. **建目录** `modules/product/{type}/`，按公共结构搭建；`plugin.json` key 用 `product_{type}`
2. **写 Handler.php**：继承 `BaseProductTypeHandler`，实现 `getTypeKey()/getTypeName()/getOrderSourceType()`，按需覆写 `getRequiredFields()/afterProductSave()/getSkuInfo()/getDetailExtend()/getSelectionSpec()/decStock()/createOrderExtend()/afterOrder()/afterPay()/onCancel()/getCompatibility()`
3. **写 OrderAdapter**：继承 `BaseOrderTypeAdapter`，实现 `getSourceType()/getModelClass()/defineStatusTree()/resolveStatusCode()/resolvePayStatus()/getStatusButtons()/getProgress()`
4. **写售后处理器**：`aftersale/{Type}AfterSaleHandler`，声明可用售后类型/期限/退款标的
5. **App.php 三项注册**（见上节），`boot()` 加载 `event.php` + 注册售后规则
6. **建扩展表**：`eb_pt_{type}` / `eb_pt_{type}_sku` / `eb_pt_{type}_order`（source_type 全局唯一）；表结构由当前模块 `Installer.php::runMigrate()` 幂等创建
7. **详情页扩展**：监听 `ProductDetailExtend` 事件，向 `display_sections` 注入展示块（order 区间 10-99）、向 `extensions` 注入业态数据——前端核心零改动
8. **订单扩展**：监听 `OrderCenterListExtend` / `OrderCenterDetailExtend` 注入列表/详情扩展数据（写回 `$event['detail']['extend']['{插件标识}']`）
9. **Installer**：`runMigrate()`（建表）→ `insertMenus()`（菜单幂等，pid 动态查询）→ `seed()`
10. **验证**：安装启用 → 后台建商品 → 用户端详情/加购/下单/支付/取消/售后全链路

完整教程见 `.trae/skills/crmeb-dev/references/03-系统商品说明/product-type.md`。
