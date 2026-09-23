# 创建产品类型扩展（Product Type Extension）

> 本文是 `crmeb-dev` 技能的子文档，完整二开流程导航见上级 [../../SKILL.md](../../SKILL.md)。本技能文档指导你在 CRMEB v8 架构下开发新的产品类型扩展。

## 一、产品类型扩展是什么

产品类型扩展解决「卖什么」的问题，决定：
- 商品有哪些专属字段（如实物商品的重量/运费，虚拟商品的下载链接）
- SKU 存储在哪张表、如何扣库存
- 订单如何履约、状态如何流转
- 前端详情页/列表页如何渲染

已有实现参考：
- [backend/modules/product/normal](../../../../../backend/modules/product/normal) — 实物商品
- [backend/modules/product/virtual](../../../../../backend/modules/product/virtual) — 虚拟商品
- [backend/modules/product/cardkey](../../../../../backend/modules/product/cardkey) — 卡密商品

> `coupon`（卡券）产品类型尚未实现，目录不存在。

## 二、前置约束（必须遵守）

1. **目录位置**：所有产品类型扩展必须放在 `backend/modules/product/{type}/`
2. **插件 key**：`Installer::getKey()` 与 `plugin.json` 的 key 必须一致，且使用下划线，如 `product_ticket`，不能写成 `product/ticket`
3. **依赖方向**：
   - `modules/product/{type}` 可以调用 `crmeb/` 和 `app/`
   - 绝不允许 `modules/product/{type}` 调用 `modules/` 下其他模块
   - `app/` 层绝不可反向 `use modules/product/{type}` 的类
4. **数据库操作**：业务数据操作必须使用 DAO，禁止直接 `Db::name()` 或 `Db::table()`；`Installer` 中的 `runMigrate()` / `rollbackMigrate()` / `cleanData()` 等生命周期方法除外。若需复杂查询构建器，可通过 `DAO::getDb()` 获取模型查询对象
5. **商品主表**：统一读写 `eb_product_center`，旧表 `eb_store_product` 已删除
6. **事件调用**：使用字符串 key（业务事件名，不带 `Listener` 后缀），如 `event('OrderPaySuccess', [$orderNo])`
7. **字段命名**：`eb_product_center` 新列名：`sort_order`（原 `sort`）、`original_price`（原 `ot_price`）、`virtual_sales`（原 `ficti`）
8. **扩展表命名**：建议 `pt_{type}`（商品扩展）、`pt_{type}_sku`（SKU）、`pt_{type}_order`（订单扩展）
9. **source_type**：每个产品类型必须有独立的订单 source_type，如 `ticket_order`、`hotel_order`，不能与其他类型重复
10. **use 语句清理**：移除跨层/跨模块调用后，必须同步删除无用的 `use`

## 三、标准目录结构

```
backend/modules/product/{type}/
├── App.php                          # 模块入口：注册 Handler、OrderAdapter、AfterSaleHandler
├── Handler.php                      # 产品类型处理器：继承 BaseProductTypeHandler
├── Installer.php                    # 模块安装器：继承 BaseModuleInstaller
├── plugin.json                      # 插件元信息
├── event.php                        # 事件监听器注册（可选）
├── handler/                         # 业务适配器（normal 实际采用此目录）
│   ├── {Type}OrderAdapter.php       # 订单状态适配器：继承 BaseOrderTypeAdapter
│   └── {Type}AfterSaleHandler.php   # 售后规则处理器（继承 BaseAfterSaleHandler；ticket/hotel 实际放在 aftersale/ 目录）
├── listener/                        # 事件监听器（支付/取消/退款/详情扩展等）
├── dao/
│   ├── {Type}ProductDao.php         # 商品扩展表 DAO
│   ├── {Type}ProductSkuDao.php      # SKU 表 DAO（可选）
│   └── {Type}OrderDao.php           # 订单扩展表 DAO
├── model/
│   ├── {Type}ProductModel.php       # 商品扩展表模型
│   ├── {Type}ProductSkuModel.php    # SKU 表模型（可选）
│   └── {Type}OrderModel.php         # 订单扩展表模型
├── service/
│   ├── ProductServices.php          # 商品列表/详情服务
│   ├── ProductSaveServices.php      # 商品保存服务
│   ├── OrderServices.php            # 订单服务
│   ├── OrderCreateServices.php      # 订单创建服务（可选）
│   └── CheckoutServices.php         # 购物车/结算服务（可选）
├── controller/
│   ├── admin/
│   │   ├── ProductController.php    # 后台商品管理
│   │   └── OrderController.php      # 后台订单管理
│   └── user/
│       └── ProductController.php    # 用户端商品接口（可选）
├── route/
│   ├── admin.php                    # 后台管理端路由
│   ├── user.php                     # 用户端路由
│   └── manage.php                   # 移动管理端路由（可选）
├── crontab/                         # 定时任务（可选，normal 用此目录）
├── validate/                        # 验证器（可选）
└── frontend/
    └── dist/                        # 后台管理前端 UMD 扩展（可选）
        ├── app.umd.min.js
        └── app.umd.css
```

> **OrderAdapter 目录已统一**：五个产品类型的 OrderAdapter 与 AfterSaleHandler 均放在 `handler/` 目录（历史路径 `hotel/domain`、`ticket/aftersale` 已迁移）。新产品类型沿用 `handler/` 目录。`App.php` 注册时通过 `__NAMESPACE__ . '\handler\{Type}OrderAdapter'` 指定完整命名空间。

## 四、核心概念

### 4.1 ProductTypeRegistry 与 Handler

`app/AppService.php` 在启动时向容器注入 `ProductTypeRegistry` 单例，各产品类型子模块在 `App.php` 中注册自己的 Handler：

```php
$registry = $app->make(ProductTypeRegistry::class);
$registry->register('{type}', \modules\product\{type}\Handler::class);
```

Handler 必须实现 [ProductTypeHandlerInterface](../../../../../backend/crmeb/trade/product/ProductTypeHandlerInterface.php)，通常继承 [BaseProductTypeHandler](../../../../../backend/crmeb/trade/product/BaseProductTypeHandler.php)，覆盖以下方法：

**类型标识与兼容性**：

| 方法 | 作用 | 是否必须 |
|------|------|---------|
| `getTypeKey()` | 类型唯一标识，如 `ticket`（abstract，必须实现） | 是 |
| `getTypeName()` | 类型展示名，如 `票务` | 是 |
| `getOrderSourceType()` | 订单 source_type，如 `ticket_order`，需与 OrderAdapter 一致 | 是 |
| `isVirtual()` | 是否虚拟商品（无物流） | 按需（基类有默认） |
| `requiresShipping()` | 是否需要物流配送 | 按需（基类有默认） |
| `supportsPickup()` | 是否支持到店自提 | 按需（基类有默认） |
| `canCombineWith()` | 支持的交易模式（tradeMode） | 按需 |
| `getCompatibility()` | 声明支持的交易模式列表、是否需要物流/售后（返回结构化数组） | 按需 |

**商品管理**：

| 方法 | 作用 | 是否必须 |
|------|------|---------|
| `getRequiredFields()` | 主表额外必填字段 | 按需 |
| `getSkuExtraFields()` | SKU 表额外字段定义 | 按需 |
| `afterProductSave()` | 保存商品后写扩展表 | 按需 |
| `afterProductDelete()` | 删除商品后清理扩展表 | 按需 |

**库存与规格**：

| 方法 | 作用 | 是否必须 |
|------|------|---------|
| `decStock()` | 扣减库存（abstract，必须实现） | 是 |
| `incStock()` | 回滚库存（abstract，必须实现） | 是 |
| `getSkuInfo()` | 获取下单用 SKU | 按需 |
| `getDetailSpecs()` | 商品详情规格矩阵 | 按需 |
| `getShippingInfo()` | 运费配置 | 按需 |

**详情页与列表页渲染**：

| 方法 | 作用 | 是否必须 |
|------|------|---------|
| `getDetailExtend()` | 商品详情扩展字段（供监听器注入 extensions） | 按需 |
| `getListExtend()` | 列表页展示扩展（type_info：card_style/type_label/unit_name） | 按需（基类有默认） |
| `getSelectionSpec()` | 详情页选择契约（前端渲染底部栏选择区） | 按需 |
| `getCartStrategy()` | 加购策略（决定 cart_button 显隐，cart_mode: direct/cart） | 按需 |
| `getPageConfig()` | 声明式页面渲染配置（**详情页已迁移到 DIY，详情页场景不再调用**） | 按需 |

**订单与履约**：

| 方法 | 作用 | 是否必须 |
|------|------|---------|
| `fulfillOrder()` | 订单履约（发货/发卡/出票等） | 按需 |
| `getAfterSaleRule()` | 售后规则处理器（返回 AfterSaleHandlerInterface） | 按需 |
| `createOrderExtend()` | 订单创建前写扩展表，返回记录 id（**约定方法**，不在接口中） | 按需 |
| `afterOrder()` | 订单创建后回填 source_type/source_id（**约定方法**） | 按需 |
| `afterPay()` | 支付成功后自动履约（**约定方法**） | 按需 |

> **abstract 方法**：`getTypeKey()`/`decStock()`/`incStock()` 必须实现，基类抛 `RuntimeException`。其余方法基类有默认实现，按需覆盖。`createOrderExtend`/`afterOrder`/`afterPay` 是约定方法（中台通过 `method_exists()` 调用），不在接口中，新产品类型按需实现。

### 4.2 OrderTypeRegistry 与 OrderAdapter

`App.php` 的 `register()` 中注册订单适配器（静态方法）：

```php
use crmeb\trade\order\type\OrderTypeRegistry;
use crmeb\trade\order\aftersale\AfterSaleRuleRegistry;
use crmeb\trade\product\ProductTypeRegistry;

public static function register(ThinkApp $app): void
{
    // 注册产品类型 Handler（ProductTypeRegistry 单例由 app/AppService.php 在启动时注入容器）
    $registry = $app->make(ProductTypeRegistry::class);
    $registry->register('{type}', \modules\product\{type}\Handler::class);

    // 注册订单流转适配器（OrderTypeRegistry::register 是静态方法）
    OrderTypeRegistry::register('{type}_order', __NAMESPACE__ . '\handler\{Type}OrderAdapter');

    // 注册售后规则（product_type 维度，registerProductType 是便捷方法，与 normal/ticket 实际代码一致）
    AfterSaleRuleRegistry::registerProductType('{type}', \modules\product\{type}\handler\{Type}AfterSaleHandler::class);
}

public static function boot(ThinkApp $app): void
{
    // 加载事件监听器
    $eventFile = __DIR__ . '/event.php';
    if (is_file($eventFile)) {
        $app->loadEvent(include $eventFile);
    }
}
```

> **三个注册器调用方式**：`ProductTypeRegistry::register()` 是**实例方法**（需 `$app->make()` 取单例再调用），`OrderTypeRegistry::register()` 是**静态方法**，`AfterSaleRuleRegistry` 也是静态注册器。产品类型统一在 `register()` 中按 `product_type` 维度注册售后规则，推荐便捷方法 `registerProductType($typeKey, $providerClass)`（内部等价于三参数 `register('product_type', $typeKey, $providerClass)`），**不是**按 source_type 注册。详见 [normal/App.php](../../../../../backend/modules/product/normal/App.php)。

> **售后 Handler 目录已统一**：五个产品类型的 AfterSaleHandler 与 OrderAdapter 均放在 `handler/` 目录（如 `NormalAfterSaleHandler`、`TicketAfterSaleHandler`、`HotelAfterSaleHandler`）。新产品类型沿用 `handler/` 目录即可。

OrderAdapter 决定订单状态流转，接口方法（OrderTypeAdapterInterface（历史路径：`backend/crmeb/trade/order/type/OrderTypeAdapterInterface.php`） 强制约束，共 11 个）：
- `getSourceType()` — 返回 `{type}_order`
- `getOrderCategory()` / `getOrderCategoryLabel()`
- `getModelClass()` — 扩展表模型类
- `resolveStatusCode()` — 扩展状态 → 中台 `status_code`
- `resolvePayStatus()` / `resolveRefundStatus()` / `resolveBusinessStatus()`
- `getStatusButtons()` — 用户端操作按钮（可接收可选的 `$detail` 参数做动态按钮）
- `getOrderButtons()` — 订单按钮
- `getProgress()` — 订单进度条

此外，`defineStatusTree()` 是基类 BaseOrderTypeAdapter（历史路径：`backend/crmeb/trade/order/type/BaseOrderTypeAdapter.php`） 的 **abstract 方法**（不在接口中），是状态树唯一数据源，必须实现。

此外，管理端列表按扩展状态筛选时，中台会静态调用 `OrderAdapter::resolveExtensionFilter($filter)`，因此建议实现该静态方法作为筛选辅助（约定方法，不在接口中）。

> `advance(string $orderNo, string $extStatus, array $extra = [])` 是基类 BaseOrderTypeAdapter（历史路径：`backend/crmeb/trade/order/type/BaseOrderTypeAdapter.php`） 提供的实例方法，用于推进扩展表状态并自动同步中台状态、触发 onEnter 回调。通过 `{Type}OrderAdapter::instance()->advance(...)` 调用（`instance()` 是基类提供的单例方法）。

#### 扩展事件

除 `event.php` 中自定义的监听外，中台还预留了以下通用扩展点，产品类型可按需监听：

| 事件名 | 触发时机 | 用途 |
|--------|---------|------|
| `OrderCenterListExtend` | `OrderCenterServices` 查询完列表后 | 向订单列表注入额外字段 |
| `OrderCenterDetailExtend` | `OrderCenterServices` 组装完详情后 | 注入扩展数据 + 自声明渲染 section（卡密、核销码、游客信息等） |
| `OrderCenterBusinessStatusResolve` | `OrderAdapter::resolveBusinessStatus()` 内 | 允许其他模块覆盖业务状态 badge |

`OrderCenterListExtend` / `OrderCenterDetailExtend` 的 listener 接收 `$event` 数组（含 `list` 或 `detail` 引用）。ThinkPHP 的 `event()` 不会自动合并监听器的返回值，因此 listener 必须直接修改引用变量：

- `OrderCenterListExtend`：直接修改 `$event['list']` 中的列表项。
- `OrderCenterDetailExtend`：将扩展数据写入 `$event['detail']['extend']['插件标识']`（合并进 `page_config.extensions`）；**同时**将渲染配置写入 `$event['detail']['extend_sections']['插件标识']`（追加进 `page_config.sections`）。中台不硬编码插件专属 section，数据生产方自声明渲染：

```php
// 监听器内：数据 + 渲染声明一起写（props 可直接内嵌数据，旧数据仍保留在 extensions 供深度定制）
$detail['extend']['fulfillment'] = ['type' => 'cardkey', 'cards' => $cardList];
$detail['extend_sections']['fulfillment'] = [
    'component' => 'card_list',                       // 前端组件名（必填，默认 info_list）
    'props'     => ['fulfillment' => $detail['extend']['fulfillment']], // 完整 props
    'visible'   => true,                              // 默认 true
    'order'     => 60,                                // 排序，默认 500
];
```

### 4.3 Installer 生命周期

继承 `crmeb\module\BaseModuleInstaller`，实现：
- `getKey()` / `getName()` / `getType()` — `getType()` 固定返回 `product_type`
- `runMigrate()` — 建表
- `rollbackMigrate()` — 删表
- `getMenuConfig()` — 后台菜单配置
- `cleanData()` — 卸载时清理业务数据（可选）
- `runCustom()` — 安装后自定义操作（可选，常用于菜单迁移/前端部署）

## 五、从 0 到 1 创建一个产品类型

### 5.1 创建目录与文件

以创建「票务」产品类型 `ticket` 为例：

```bash
mkdir -p backend/modules/product/ticket/{handler,dao,model,service,controller/admin,controller/user,route}
touch backend/modules/product/ticket/App.php
touch backend/modules/product/ticket/Handler.php
touch backend/modules/product/ticket/Installer.php
touch backend/modules/product/ticket/plugin.json
touch backend/modules/product/ticket/event.php
```

### 5.2 编写 plugin.json

```json
{
    "key": "product_ticket",
    "name": "票务",
    "description": "票务产品类型扩展",
    "version": "1.0.0",
    "author": "CRMEB Team",
    "type": "product_type",
    "path": "modules/product/ticket",
    "require": {
        "crmeb": "^6.0",
        "php": "^8.0"
    }
}
```

### 5.3 编写 App.php

```php
<?php
declare(strict_types=1);

namespace modules\product\ticket;

use crmeb\trade\order\type\OrderTypeRegistry;
use crmeb\trade\order\aftersale\AfterSaleRuleRegistry;
use crmeb\trade\product\ProductTypeRegistry;
use modules\product\ticket\handler\TicketAfterSaleHandler;
use think\App as ThinkApp;

/**
 * 票务产品类型模块入口
 */
class App
{
    public static function register(ThinkApp $app): void
    {
        // 注册产品类型 Handler
        $registry = $app->make(ProductTypeRegistry::class);
        $registry->register('ticket', \modules\product\ticket\Handler::class);

        // 注册订单适配器
        OrderTypeRegistry::register('ticket_order', __NAMESPACE__ . '\handler\TicketOrderAdapter');

        // 注册售后规则（product_type 维度）：门票仅支持仅退款
        AfterSaleRuleRegistry::registerProductType('ticket', TicketAfterSaleHandler::class);
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
```

### 5.4 编写 Handler.php（最小实现）

```php
<?php
declare(strict_types=1);

namespace modules\product\ticket;

use crmeb\trade\product\BaseProductTypeHandler;

/**
 * 票务商品处理器
 * 无物流，支付后出票完成
 */
class Handler extends BaseProductTypeHandler
{
    public function getTypeKey(): string
    {
        return 'ticket';
    }

    public function getTypeName(): string
    {
        return '票务';
    }

    public function getOrderSourceType(): string
    {
        return 'ticket_order';
    }

    public function getRequiredFields(): array
    {
        return [
            'cate_id' => '请选择商品分类',
        ];
    }

    public function canCombineWith(string $tradeMode): bool
    {
        return in_array($tradeMode, ['normal']);
    }

    public function afterProductSave(int $productId, array $data): void
    {
        $dao = app(\modules\product\ticket\dao\TicketProductDao::class);
        $extendData = [
            'product_id' => $productId,
            'venue'      => $data['venue'] ?? '',
            'show_time'  => $data['show_time'] ?? 0,
        ];
        $exist = $dao->getOne(['product_id' => $productId]);
        if ($exist) {
            $dao->update($exist['id'], $extendData);
        } else {
            $dao->save($extendData);
        }
    }

    public function afterProductDelete(int $productId): void
    {
        app(\modules\product\ticket\dao\TicketProductDao::class)->delete(['product_id' => $productId]);
    }

    public function getSkuInfo(int $productId, string $skuUnique): ?array
    {
        $dao = app(\modules\product\ticket\dao\TicketProductSkuDao::class);
        $sku = $skuUnique
            ? $dao->getOne(['unique_code' => $skuUnique, 'product_id' => $productId])
            : $dao->getOne(['product_id' => $productId]);
        if (!$sku) return null;
        $sku = is_object($sku) ? $sku->toArray() : $sku;

        return [
            'id'       => $sku['id'] ?? 0,
            'unique'   => $sku['unique_code'] ?? '',
            'price'    => $sku['price'] ?? 0,
            'ot_price' => $sku['original_price'] ?? 0,
            'cost'     => $sku['cost'] ?? 0,
            'stock'    => $sku['stock'] ?? 0,
            'image'    => $sku['image'] ?? '',
            'suk'      => $sku['suk'] ?? '默认',
        ];
    }

    public function getDetailExtend(int $productId): array
    {
        $dao = app(\modules\product\ticket\dao\TicketProductDao::class);
        $extend = $dao->getOne(['product_id' => $productId]);
        if (!$extend) return [];
        $extend = is_object($extend) ? $extend->toArray() : $extend;

        return [
            'venue'     => $extend['venue'] ?? '',
            'show_time' => $extend['show_time'] ?? 0,
        ];
    }

    public function getListExtend(int $productId): array
    {
        return [
            'card_style' => 'standard',
            'type_label' => '票务',
            'unit_name'  => '张',
        ];
    }

    public function decStock(int $productId, string $skuUnique, int $num): bool
    {
        $dao = app(\modules\product\ticket\dao\TicketProductSkuDao::class);
        $sku = $dao->getOne(['product_id' => $productId, 'unique_code' => $skuUnique]);
        if (!$sku) return false;
        $sku = is_object($sku) ? $sku->toArray() : $sku;
        return $dao->decStock(['id' => $sku['id']], $num);
    }

    public function getDetailSpecs(int $productId): array
    {
        // 单规格示例
        $dao = app(\modules\product\ticket\dao\TicketProductSkuDao::class);
        $sku = $dao->getOne(['product_id' => $productId]);
        $sku = is_object($sku) ? $sku->toArray() : ($sku ?: []);

        return [
            'productAttr' => [[
                'attr_name'   => '规格',
                'attr_values' => ['默认'],
                'attr_value'  => [['attr' => '默认', 'check' => false, 'pic' => $sku['image'] ?? '']],
            ]],
            'productValue' => [
                '默认' => [
                    'suk'      => '默认',
                    'price'    => $sku['price'] ?? '0.00',
                    'stock'    => $sku['stock'] ?? 0,
                    'image'    => $sku['image'] ?? '',
                    'unique'   => $sku['unique_code'] ?? '',
                    'cost'     => $sku['cost'] ?? '0.00',
                    'ot_price' => $sku['original_price'] ?? '0.00',
                    'bar_code' => '',
                    'is_show'  => 1,
                ],
            ],
        ];
    }

    /**
     * 声明式页面渲染配置
     * 注意：详情页已迁移到 DIY + display_sections 架构，详情页场景不再调用此方法。
     * 返回空数组表示使用中台默认（DIY 渲染）。业态展示通过 ProductDetailExtend 监听器
     * 向 display_sections 注入 spec/info_card/store_card/time_card 实现。
     * 此方法仅保留下单页或其他声明式场景备用。
     */
    public function getPageConfig(string $scene, string $appVersion = '1.0.0'): array
    {
        // 详情页场景：返回空数组，使用 DIY 渲染（normal/Handler.php 同样返回空数组）
        // 若需为下单页等声明式场景提供配置，可在此按 scene 返回 sections
        return [];
    }
}
```

> **详情页渲染已迁移到 DIY**：[normal/Handler.php:578](../../../../../backend/modules/product/normal/Handler.php#L578) 的 `getPageConfig('detail')` 返回空数组。详情页布局由运营通过后台 DIY 编辑器配置（`theme.detail_data`），业态内容由 `ProductDetailExtend` 监听器向 `display_sections` 注入。新产品类型只需实现监听器输出 display_sections，无需在 Handler 中返回 sections 配置。详见 [用户端产品详情接口](../05-用户端开发/user-product-detail.md)。

### 5.5 编写 Installer.php（最小实现）

```php
<?php
declare(strict_types=1);

namespace modules\product\ticket;

use crmeb\module\BaseModuleInstaller;

/**
 * 票务产品类型安装器
 */
class Installer extends BaseModuleInstaller
{
    public function getKey(): string  { return 'product_ticket'; }
    public function getName(): string { return '票务'; }
    public function getType(): string { return 'product_type'; }

    protected function runMigrate(): void
    {
        if (!$this->hasTable('pt_ticket')) {
            $this->exec("CREATE TABLE IF NOT EXISTS `{$this->table('pt_ticket')}` (
                `id` int unsigned NOT NULL AUTO_INCREMENT,
                `product_id` int NOT NULL DEFAULT 0 COMMENT '商品ID',
                `venue` varchar(256) NOT NULL DEFAULT '' COMMENT '演出场馆',
                `show_time` int NOT NULL DEFAULT 0 COMMENT '演出时间',
                PRIMARY KEY (`id`),
                UNIQUE KEY `uk_product_id` (`product_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$this->charset} COLLATE={$this->collation} COMMENT='票务商品扩展表';");
        }

        if (!$this->hasTable('pt_ticket_sku')) {
            $this->exec("CREATE TABLE IF NOT EXISTS `{$this->table('pt_ticket_sku')}` (
                `id` int unsigned NOT NULL AUTO_INCREMENT,
                `product_id` int NOT NULL DEFAULT 0 COMMENT '商品ID',
                `unique_code` varchar(32) NOT NULL DEFAULT '' COMMENT '唯一码',
                `suk` varchar(256) NOT NULL DEFAULT '' COMMENT '规格名称',
                `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '售价',
                `original_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '原价',
                `cost` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '成本价',
                `stock` int NOT NULL DEFAULT 0 COMMENT '库存',
                `image` varchar(256) NOT NULL DEFAULT '' COMMENT 'SKU图片',
                `add_time` int NOT NULL DEFAULT 0,
                PRIMARY KEY (`id`),
                KEY `idx_product_id` (`product_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$this->charset} COLLATE={$this->collation} COMMENT='票务商品SKU表';");
        }

        if (!$this->hasTable('pt_ticket_order')) {
            $this->exec("CREATE TABLE IF NOT EXISTS `{$this->table('pt_ticket_order')}` (
                `id` int unsigned NOT NULL AUTO_INCREMENT,
                `order_no` varchar(32) NOT NULL DEFAULT '' COMMENT '订单号',
                `product_id` int NOT NULL DEFAULT 0 COMMENT '商品ID',
                `uid` int NOT NULL DEFAULT 0 COMMENT '用户ID',
                `status` varchar(32) NOT NULL DEFAULT 'pending_payment' COMMENT '状态',
                `paid` tinyint NOT NULL DEFAULT 0 COMMENT '是否支付 0否 1是',
                `pay_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '实际支付金额',
                `pay_time` int NOT NULL DEFAULT 0 COMMENT '支付时间',
                `complete_time` int NOT NULL DEFAULT 0 COMMENT '完成时间',
                `add_time` int NOT NULL DEFAULT 0,
                PRIMARY KEY (`id`),
                KEY `idx_order_no` (`order_no`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$this->charset} COLLATE={$this->collation} COMMENT='票务订单扩展表';");
        }
    }

    protected function rollbackMigrate(): void
    {
        $this->exec("DROP TABLE IF EXISTS `{$this->table('pt_ticket_order')}`;");
        $this->exec("DROP TABLE IF EXISTS `{$this->table('pt_ticket_sku')}`;");
        $this->exec("DROP TABLE IF EXISTS `{$this->table('pt_ticket')}`;");
    }

    protected function getMenuConfig(): array
    {
        // 父菜单 ID 由 BaseModuleInstaller 内置的 getMenuPid() 动态查询，避免硬编码 pid
        $productPid = $this->getMenuPid('商品', 'product');

        return [
            [
                'pid'           => $productPid,
                'menu_name'     => '票务',
                'controller'    => 'ticketProduct',
                'action'        => 'index',
                'module'        => 'admin',
                'menu_path'     => '/product/ticket',
                'unique_auth'   => 'admin-ticket-product',
                'mark'          => '票务管理',
                'icon'          => 'el-icon-Tickets',
                'sort'          => 30,
                'is_show'       => 1,
                'is_show_path'  => 1,
                'access'        => 1,
                'auth_type'     => 1,
            ],
            [
                'pid'           => '@票务',
                'menu_name'     => '商品管理',
                'controller'    => 'ticketProduct',
                'action'        => 'index',
                'module'        => 'admin',
                'api_url'       => 'ticket/product/list',
                'menu_path'     => '/product/ticket/list',
                'unique_auth'   => 'admin-ticket-product-list',
                'mark'          => '票务商品列表',
                'icon'          => 'el-icon-s-goods',
                'sort'          => 1,
                'is_show'       => 1,
                'is_show_path'  => 1,
                'access'        => 1,
                'auth_type'     => 1,
            ],
            [
                'pid'           => '@票务',
                'menu_name'     => '订单管理',
                'controller'    => 'ticketOrder',
                'action'        => 'index',
                'module'        => 'admin',
                'api_url'       => 'ticket/order/list',
                'menu_path'     => '/product/ticket/order/list',
                'unique_auth'   => 'admin-ticket-order-list',
                'mark'          => '票务订单列表',
                'icon'          => 'el-icon-s-order',
                'sort'          => 2,
                'is_show'       => 1,
                'is_show_path'  => 1,
                'access'        => 1,
                'auth_type'     => 1,
            ],
            // 隐藏权限菜单略...
        ];
    }

    protected function cleanData(): int
    {
        $count = 0;
        $count += \think\facade\Db::table($this->table('pt_ticket_order'))->whereRaw('1=1')->delete();
        $count += \think\facade\Db::table($this->table('pt_ticket_sku'))->whereRaw('1=1')->delete();
        $count += \think\facade\Db::table($this->table('pt_ticket'))->whereRaw('1=1')->delete();
        return $count;
    }
}
```

### 5.6 编写 OrderAdapter（最小实现）

```php
<?php
declare(strict_types=1);

namespace modules\product\ticket\handler;

use crmeb\trade\order\type\BaseOrderTypeAdapter;
use crmeb\trade\order\type\Button;
use crmeb\trade\order\type\OrderFlowNode;
use modules\product\ticket\dao\TicketOrderDao;
use modules\product\ticket\model\TicketOrderModel;

/**
 * 票务订单适配器
 */
class TicketOrderAdapter extends BaseOrderTypeAdapter
{
    public const EXT_STATUS_PENDING_PAYMENT = 'pending_payment';
    public const EXT_STATUS_COMPLETED       = 'completed';
    public const EXT_STATUS_CANCELED        = 'canceled';

    public static function getSourceType(): string
    {
        return 'ticket_order';
    }

    public static function getOrderCategory(): string
    {
        return 'goods';
    }

    public static function getOrderCategoryLabel(): string
    {
        return '商品订单';
    }

    public static function getModelClass(): string
    {
        return TicketOrderModel::class;
    }

    protected function defineStatusTree(): array
    {
        return [
            OrderFlowNode::make(self::EXT_STATUS_PENDING_PAYMENT)
                ->centerStatus(self::STATUS_PENDING_PAYMENT)
                ->label('待支付')
                ->userButtons([
                    Button::make('取消订单', 'cancelOrder'),
                    Button::primary('去支付', 'payOrder'),
                ]),

            OrderFlowNode::make(self::EXT_STATUS_COMPLETED)
                ->centerStatus(self::STATUS_COMPLETED)
                ->label('已完成')
                ->userButtons([
                    Button::primary('再次购买', 'repurchase'),
                ]),

            OrderFlowNode::make(self::EXT_STATUS_CANCELED)
                ->centerStatus(self::STATUS_CANCELED)
                ->label('已取消')
                ->userButtons([
                    Button::primary('再次购买', 'repurchase'),
                ]),
        ];
    }

    public function resolveStatusCode(array $sourceOrder): string
    {
        $status = (string) ($sourceOrder['status'] ?? '');
        if ($status === self::EXT_STATUS_CANCELED) {
            return self::STATUS_CANCELED;
        }
        if ($status === self::EXT_STATUS_COMPLETED) {
            return self::STATUS_COMPLETED;
        }
        return self::STATUS_PENDING_PAYMENT;
    }

    public function resolvePayStatus(array $sourceOrder): int
    {
        $status = (string) ($sourceOrder['status'] ?? '');
        return $status === self::EXT_STATUS_COMPLETED
            ? self::PAY_PAID
            : self::PAY_UNPAID;
    }

    public function resolveRefundStatus(array $sourceOrder): int
    {
        return 0;
    }

    public function resolveBusinessStatus(array $sourceOrder): ?array
    {
        return null;
    }

    public static function resolveExtensionFilter(array $filter): array
    {
        /** @var TicketOrderDao $dao */
        $dao = app()->make(TicketOrderDao::class);
        return $dao->columnByFilters($filter, 'order_no') ?: ['__NONE__'];
    }

    public function getProgress(array $detail): array
    {
        $statusCode = $detail['status_code'] ?? '';
        $nodes = [
            ['title' => '提交订单', 'key' => 'submit', 'time_field' => 'add_time'],
            ['title' => '支付成功', 'key' => 'pay', 'time_field' => 'pay_time'],
            ['title' => '出票完成', 'key' => 'ticket', 'time_field' => 'take_time'],
            ['title' => '订单完成', 'key' => 'complete', 'time_field' => ''],
        ];

        $isCompleted = $statusCode === self::STATUS_COMPLETED;
        $isCanceled  = $statusCode === self::STATUS_CANCELED;
        $isAfterSale = $statusCode === self::STATUS_AFTER_SALE;
        $isRefunded  = $statusCode === self::STATUS_REFUNDED;

        foreach ($nodes as $i => &$node) {
            $time = $node['time_field'] ? (int)($detail[$node['time_field']] ?? 0) : 0;
            $node['time']      = $time ? date('Y-m-d H:i:s', $time) : '';
            $node['completed'] = $time > 0;
            $node['active']    = false;
        }
        unset($node);

        // 完成节点只有 completed 状态才算完成，不能仅依赖 pay_time
        $nodes[count($nodes) - 1]['completed'] = $isCompleted;

        // pending_payment 强制当前节点为提交订单，清空后续完成状态
        if ($statusCode === self::STATUS_PENDING_PAYMENT) {
            $current = 0;
            foreach ($nodes as $i => &$node) {
                if ($i > 0) $node['completed'] = false;
            }
            unset($node);
        } elseif ($isCanceled) {
            $current = 0;
        } elseif ($isRefunded || $isAfterSale) {
            $current = 1;
        } elseif ($isCompleted) {
            $current = count($nodes) - 1;
        } elseif ((int)($detail['take_time'] ?? 0) > 0) {
            $current = 2;
        } elseif ((int)($detail['pay_time'] ?? 0) > 0) {
            $current = 1;
        } else {
            $current = 0;
        }

        if (isset($nodes[$current])) {
            $nodes[$current]['active'] = true;
        }

        return [
            'current' => $current,
            'status'  => $statusCode,
            'nodes'   => $nodes,
        ];
    }
}
```

### 5.7 事件监听器（可选）

`event.php`：

```php
<?php
return [
    'listen' => [
        // 注意：中台触发的事件名为 'OrderPaySuccess'（不带 Listener 后缀），
        // 与 app/event.php 中注册的 key 保持一致。
        'OrderPaySuccess' => [
            \modules\product\ticket\listener\OrderPaidListener::class,
        ],
        'OrderCancel' => [
            \modules\product\ticket\listener\OrderCancelListener::class,
        ],
    ],
];
```

`listener/OrderPaidListener.php` 示例：

```php
<?php
declare(strict_types=1);

namespace modules\product\ticket\listener;

use app\dao\order\OrderCenterDao;
use modules\product\ticket\handler\TicketOrderAdapter;

/**
 * 票务支付成功监听
 */
class OrderPaidListener
{
    public function handle($event): void
    {
        [$orderInfo] = $event;
        $orderNo = $orderInfo['order_id'] ?? '';
        if (!$orderNo) return;

        // 仅处理本类型的中台订单，防止误触发
        $centerDao = app(OrderCenterDao::class);
        $meta = $centerDao->getOne(['order_no' => $orderNo], 'product_type,status_code');
        if (!$meta || ($meta['product_type'] ?? '') !== 'ticket') {
            return;
        }

        // 幂等：已终态不再处理
        $statusCode = $meta['status_code'] ?? '';
        if (in_array($statusCode, ['completed', 'refunded', 'canceled'], true)) {
            return;
        }

        // 推进扩展表状态并自动同步中台状态、回调 onEnter
        TicketOrderAdapter::instance()->advance($orderNo, TicketOrderAdapter::EXT_STATUS_COMPLETED, [
            'paid'      => 1,
            'pay_price' => $orderInfo['pay_price'] ?? 0,
            'pay_time'  => time(),
        ]);
    }
}
```

## 六、订单创建流程约定方法

以下方法**不属于** `ProductTypeHandlerInterface`，但订单中台会通过 `method_exists()` 在合适的时机调用。新产品类型按需实现即可。

### 6.1 `createOrderExtend()` — 创建订单扩展记录

在 `OrderCenter` 写入中台表之前调用，返回扩展表记录 id，作为中台表 `source_id`：

```php
public function createOrderExtend(string $orderNo, array $cartInfo, array $context): int
{
    $dao = app(\modules\product\ticket\dao\TicketOrderDao::class);
    $result = $dao->save([
        'order_no'   => $orderNo,
        'product_id' => $cartInfo['product_id'] ?? 0,
        'uid'        => $cartInfo['uid'] ?? 0,
        'status'     => TicketOrderAdapter::EXT_STATUS_PENDING_PAYMENT,
        'add_time'   => time(),
    ]);
    return $result->id ?? 0;
}
```

### 6.2 `afterOrder()` — 下单后处理

订单创建后调用，用于幂等写扩展表并回填 `eb_order_center.source_type` 和 `source_id`。

> 注意：`CenterOrderController::create()` 已先调用 `createOrderExtend()` 并在写入中台表时带上 `source_id`，因此大多数类型只需在 `afterOrder()` 里做回填校验；若你的类型走的是自己的 `OrderCreateServices`，则可能需要在 `afterOrder()` 里补写扩展表。

```php
public function afterOrder(string $orderNo, array $cartInfo, array $context): void
{
    $dao = app(\modules\product\ticket\dao\TicketOrderDao::class);
    $exist = $dao->getOne(['order_no' => $orderNo], 'id');
    if (!$exist) {
        $this->createOrderExtend($orderNo, $cartInfo, $context);
        $exist = $dao->getOne(['order_no' => $orderNo], 'id');
    }
    $extendId = $exist['id'] ?? 0;
    if (!$extendId) return;

    $centerDao = app(\app\dao\order\OrderCenterDao::class);
    $centerDao->update(['order_no' => $orderNo], [
        'source_type' => TicketOrderAdapter::getSourceType(),
        'source_id'   => $extendId,
    ]);
}
```

### 6.3 `afterPay()` — 支付成功履约

`OrderPaySuccess` 事件触发后，各类型自己的 `OrderPaySuccessListener` 会调用 `Handler::afterPay()` 完成自动履约（如虚拟商品自动发货、卡密自动发卡、票务自动出票）。

```php
public function afterPay(string $orderNo, array $cartInfoList): void
{
    $orderDao = app(\modules\product\ticket\dao\TicketOrderDao::class);

    // 幂等检查
    $order = $orderDao->getOne(['order_no' => $orderNo]);
    if (!$order || ($order['status'] ?? '') !== TicketOrderAdapter::EXT_STATUS_PENDING_PAYMENT) {
        return;
    }

    // 更新扩展表状态为已完成
    // 时间字段使用 time() 保持与表字段类型（int）一致，避免使用 date('Y-m-d H:i:s')
    $orderDao->update(['order_no' => $orderNo], [
        'status'        => TicketOrderAdapter::EXT_STATUS_COMPLETED,
        'paid'          => 1,
        'pay_time'      => time(),
        'complete_time' => time(),
    ]);

    // 通过适配器 advance() 同步中台状态、触发 onEnter 回调
    TicketOrderAdapter::instance()->advance($orderNo, TicketOrderAdapter::EXT_STATUS_COMPLETED);
}
```

## 七、路由自动加载

在 `backend/modules/product/{type}/route/admin.php` 中定义后台路由：

```php
<?php
use think\facade\Route;
use app\http\middleware\AllowOriginMiddleware;
use app\adminapi\middleware\AdminAuthTokenMiddleware;

Route::group('ticket', function () {
    Route::get('product/list', [\modules\product\ticket\controller\admin\ProductController::class, 'list']);
    Route::post('product', [\modules\product\ticket\controller\admin\ProductController::class, 'save']);
    Route::get('order/list', [\modules\product\ticket\controller\admin\OrderController::class, 'list']);
})->middleware([
    AllowOriginMiddleware::class,
    AdminAuthTokenMiddleware::class,
])->option(['mark' => 'ticket', 'mark_name' => '票务管理']);
```

用户端路由写在 `route/user.php`，移动管理端写在 `route/manage.php`。模块路由由入口文件通过 `glob` 自动扫描加载，无需手动 `include`。

## 八、前端实现说明

产品类型扩展的前端实现分为「后台管理端」和「用户端」两部分。本 skill 只给出接口和关键对接点，详细实现请查看 `references/` 目录下的专题文档（当前文件即位于 references/ 下）。

### 8.1 后台管理端前端扩展

后台管理页面（商品列表、添加/编辑商品、订单列表、订单详情）通过 **UMD 扩展包** 注入到 `frontend/admin/src/pages/system/plugin/PageContainer.vue` 中运行。

- 扩展资源路径：`public/admin/static/extensions/{typeKey}/`
- 必须文件：`app.umd.min.js` + `app.umd.css`
- 全局挂载名：`window.__ext_{typeKey}`
- 开发模式支持 HMR，生产环境部署在 `Installer` 中完成

详细内容见 [后台管理端前端扩展](../04-前端扩展/backend-frontend.md)。

### 8.2 用户端产品列表

> **现状说明**：商品列表目前仍由传统列表页承载，**未迁移到 center 引擎架构**（订单侧已完成 center 化，商品侧尚未跟进）。后端中台列表接口已就绪，前端列表页仍是旧版页面。

- 列表页：pages/goods/goods_list/index.vue（当前路径：`frontend/uni-app/src/pages/goods/goods_list/index.vue`）
- 搜索页：pages/goods/goods_search/index.vue（当前路径：`frontend/uni-app/src/pages/goods/goods_search/index.vue`）
- 前端 API 封装：api/product-center.js（当前路径：`frontend/uni-app/src/api/product-center.js`）（`getCenterProductList` 调用 `/products`）
- 后端接口（两路并存）：
  - `GET /api/products` — [v1.php](../../../../../backend/app/api/route/v1.php) 中 `CenterProductController::productList`（前端实际调用）
  - `GET /api/center/product/list` — [center.php](../../../../../backend/app/api/route/center.php) 中 `CenterProductController::list`（中台统一入口，预留）

产品类型只需实现 `Handler::getListExtend()` 注入 `type_info`，即可控制卡片标签、单位、样式等。

详细内容见 [用户端产品列表实现](../05-用户端开发/user-product-list.md)。

### 8.3 用户端产品详情

> **现状说明**：商品详情页已迁移到 **DIY + display_sections 架构**，但页面路径仍是传统的 `goods_details`，**未迁入 `pages/center/`**，也没有独立的 `ProductDetailEngine`。详情页通过 `<PageDesign>` DIY 组件渲染，由后端 `display_sections` 数据驱动。

- 详情页：pages/goods_details/index.vue（当前路径：`frontend/uni-app/src/pages/goods_details/index.vue`）
- DIY 渲染组件：subpackage/diyComponents/pageDesign.vue（当前路径：`frontend/uni-app/src/subpackage/diyComponents/pageDesign.vue`）（通过 `displaySections` prop 驱动）
- 前端 API 封装：api/store.js（当前路径：`frontend/uni-app/src/api/store.js`）（`getProductDetail` 调用 `product/detail/{id}`）
- 后端接口：`GET /api/product/detail/{id}` — [v1.php](../../../../../backend/app/api/route/v1.php) 中 `CenterProductController::detail`（注意：**不是** `/api/center/product/detail/{id}`）

产品类型需要实现：
- `Handler::getDetailExtend()` — 注入扩展字段（供监听器汇聚到 `extensions`）
- `Handler::getDetailSpecs()` — 返回规格矩阵（多规格类型必填）
- `ProductDetailExtend` 事件监听器 — 向 `display_sections` 注入业态专属 block（spec/info_card/store_card/time_card 等）

> **`getPageConfig()` 不再用于详情页**：详情页布局由运营通过后台 DIY 编辑器配置，业态内容由监听器注入 `display_sections`。Handler 的 `getPageConfig('detail')` 应返回空数组（参考 [normal/Handler.php](../../../../../backend/modules/product/normal/Handler.php)）。详见 5.4 节说明。

详细内容见 [用户端产品详情实现](../05-用户端开发/user-product-detail.md)。

### 8.4 用户端创建订单

新中台下单页：
- 页面：`frontend/uni-app/src/pages/center/order/create/index.vue`
- 引擎：`frontend/uni-app/src/dynamic-pages/engines/OrderCreateEngine.vue`
- 接口：`GET /api/center/order/confirm` + `POST /api/center/order/create`

产品类型需要实现：
- `Handler::getSkuInfo()` / `getShippingInfo()` / `decStock()`
- `Handler::getDetailExtend()` — 返回自定义表单
- `Handler::createOrderExtend()` / `afterOrder()` — 创建扩展记录并回填 `source_type`/`source_id`

详细内容见 [用户端创建订单实现](../05-用户端开发/user-order-create.md)。

### 8.5 用户端订单列表

新中台订单列表页（沿用旧路径 `pages/goods/order_list/index.vue`，由 OrderListEngine 渲染）：
- 页面：`frontend/uni-app/src/pages/goods/order_list/index.vue`（注意：`pages/center/order/` 下无 `list/` 目录）
- 引擎：`frontend/uni-app/src/dynamic-pages/engines/OrderListEngine.vue`
- 接口：`GET /api/center/order/list`

产品类型主要通过 `OrderAdapter` 提供状态推导、操作按钮、扩展筛选。

详细内容见 [用户端订单列表实现](../05-用户端开发/user-order-list.md)。

### 8.6 用户端订单详情

新中台订单详情页：
- 页面：`frontend/uni-app/src/pages/center/order/detail/index.vue`
- 引擎：`frontend/uni-app/src/dynamic-pages/engines/OrderDetailEngine.vue`
- 接口：`GET /api/center/order/detail/{orderNo}`

产品类型主要通过 `OrderAdapter` 提供：
- 状态文本与操作按钮
- 进度条 `getProgress()`
- 扩展数据（通过 `OrderCenterDetailExtend` 事件注入）

详细内容见 [用户端订单详情实现](../05-用户端开发/user-order-detail.md)。

## 附一：中台统一状态码

`eb_order_center.status_code` 使用中台统一状态码，`OrderAdapter::resolveStatusCode()` 负责把扩展表状态映射到这些值：

| 状态码 | 中文 | 说明 |
|--------|------|------|
| `pending_payment` | 待支付 | 订单已创建但未支付 |
| `processing` | 进行中 | 已支付，履约中（包含待发货、待收货、待安装等子状态） |
| `pending_review` | 待评价 | 履约完成，等待用户评价 |
| `completed` | 已完成 | 订单终结 |
| `canceled` | 已取消 | 订单取消 |
| `after_sale` | 售后中 | 存在进行中的退款/售后 |
| `refunded` | 已退款 | 退款完成 |

`processing` 状态在管理端/用户端可能细分为多个业务状态（通过 `business_status` badge 展示），但筛选接口统一用 `processing`。

## 附二：自定义表单存储方案

### 存储位置

自定义表单数据统一存储在 **`eb_order_center.custom_form`** JSON 字段中，不是订单扩展表（如 `pt_ticket_order`）。

### 写入时机

1. 商品层面：自定义表单定义（字段标题、类型、是否必填）随商品通用展示字段统一写入 `eb_product_center.custom_form`，由中台商品服务维护。
2. 订单层面：用户下单时，前端提交对象格式（如 `{"custom_field_0":"张三"}`），`CenterOrderController::create()` 根据商品定义将其转换为结构化数组，再随 `OrderCenterServices::createRecord()` 写入 `eb_order_center.custom_form`。
3. 读取时，`OrderCenterServices::getDetail()` 通过 `parseExtraInfo()` 解析 JSON，并调用 `transformCustomForm()` 做最终格式兜底，保证老数据兼容。

> 关键原则：**写入时即结构化**，不要依赖详情页读取时的转换逻辑来“纠正”数据。

### 数据结构

中台表保存的结构化为数组格式，每个元素包含字段标识、展示标题和用户提交值：

```json
[
  {"label": "custom_field_0", "title": "观众姓名", "value": "张三"},
  {"label": "custom_field_1", "title": "观众手机", "value": "13800138000"}
]
```

前端提交时为对象格式 `{"custom_field_0": "张三"}`，由 `CenterOrderController::create()` 自动根据商品定义的 `custom_form` 转换为上述结构化数组后写入中台表。

### 设计合理性

| 维度 | 评估 | 说明 |
|------|------|------|
| 数据归属 | 合理 | 自定义表单属于订单级信息，与订单强绑定 |
| 读取效率 | 高 | 随 `eb_order_center` 一并读取，无需 JOIN 扩展表 |
| 存储效率 | 高 | JSON 列按需存储，空订单为 `NULL` |
| 扩展性 | 好 | 不同产品类型字段数量、标题不同，JSON 可灵活容纳 |
| 查询能力 | 有限 | 不适合按自定义表单字段值做高频筛选或全文检索 |

### 适用边界

- **当前方案适用**：订单详情展示、售后查看、导出等读多写少的场景。
- **需要演进的情况**：若后续需要按自定义表单字段值进行筛选、统计或建立索引，应拆分为独立的 `eb_order_custom_form` 关系表（`order_no + field_key + field_label + field_value`），并通过事件或中台服务统一维护。

### 产品类型扩展需要做的事

产品类型 Handler 只需在 `getDetailExtend()` 中返回商品定义的 `custom_form` 字段描述，中台下单流程会自动完成收集、转换和存储：

```php
public function getDetailExtend(int $productId): array
{
    // 从 eb_product_center 读取 custom_form 定义
    return [
        'custom_form' => [...],
    ];
}
```

无需在 `createOrderExtend()` 或扩展表中重复保存 `custom_form`。

> **避免双写**：`custom_form`、`params_list`、`label_list`、`protection_list` 属于商品通用展示字段，
> 统一由中台商品服务写入 `eb_product_center`，产品类型扩展表（如 `eb_pt_normal`）不应再重复存储这些字段，
> 以免出现数据不一致。

## 九、测试清单

开发完成后，按以下清单验证：

- [ ] 插件 key 与 `plugin.json` 一致，使用下划线命名
- [ ] `App.php` 正确注册 Handler 和 OrderAdapter
- [ ] `Handler::getOrderSourceType()` 与 `OrderAdapter::getSourceType()` 一致且唯一
- [ ] `Handler::createOrderExtend()` 返回扩展表记录 id
- [ ] `Handler::afterOrder()` 幂等，且正确回填 `source_type` / `source_id`
- [ ] `Handler::afterPay()` 幂等，支付成功后推进扩展表并同步中台状态
- [ ] `OrderAdapter::defineStatusTree()` 覆盖完整状态流转
- [ ] `OrderAdapter::resolveStatusCode()` 能正确推导中台 `status_code`
- [ ] `OrderAdapter::getProgress()` 对 `pending_payment` 强制 current=0 且清空后续完成状态
- [ ] 订单创建后 `eb_order_center` 的 `source_type` / `source_id` 正确
- [ ] 订单详情接口能正确返回 progress 和操作按钮
- [ ] 移动端订单详情接口不返回 `timeline`
- [ ] 路由能通过自动扫描加载
- [ ] 后台菜单正确挂载到「商品」下，`activeMenu` 放在子路由 meta 并使用路径格式
- [ ] 安装/卸载/启用/禁用生命周期正常
- [ ] 无跨模块 `use`、无反向 `app/` 调用 `modules/`、无无用 `use`
- [ ] 业务数据库操作必须使用 DAO，禁止在 Service / Handler / Controller 中直接调用 `Db::name()` / `Db::table()`（Installer 生命周期方法除外）
- [ ] 事件监听 key 与中台触发 key 完全一致（如 `'OrderPaySuccess'` 而非 `'OrderPaySuccessListener'`）
- [ ] 扩展表时间字段使用 `time()`，不使用 `date('Y-m-d H:i:s')`

## 十、常见问题

### Q1: 订单详情缺少 progress 数据
A: 检查 `OrderAdapter::getProgress()` 是否实现，并确认 `OrderCenterServices` 在组装详情时调用了适配器的 `getProgress()`。

### Q2: 订单状态显示错误
A: 检查 `OrderAdapter::resolveStatusCode()` 推导逻辑，以及支付回调时是否先写扩展表 status 再同步中台 status_code。

### Q3: 下单后 source_type 不正确
A: 检查三点：
1. `Handler::getOrderSourceType()` 是否覆盖并返回与 `OrderAdapter::getSourceType()` 一致的值；
2. `Handler::createOrderExtend()` 是否返回了有效的扩展表记录 id；
3. `Handler::afterOrder()` 是否将 `source_type` / `source_id` 正确回填到中台表。

### Q4: 库存扣减失败
A: 确认 `Handler::decStock()` 覆盖实现，并且 SKU 表的列名与代码一致。

### Q5: 后台菜单未显示或未高亮
A: 菜单 `pid` 必须使用 `getMenuPid('商品', 'product')` 动态查询；父菜单的 `menu_path` 不能为空；详情页/编辑页的 `activeMenu` 必须放在子路由 `meta` 中并使用路径格式。

## 十一、参考文件

- 注册中心单例注入：backend/app/AppService.php（`register()` 中 `instance(ProductTypeRegistry::class)`，模块扫描入口 `PluginManager::register()` / `ModuleRegistry::registerAll()`）
- 实物商品完整实现：[backend/modules/product/normal/](../../../../../backend/modules/product/normal)
- 虚拟商品完整实现：[backend/modules/product/virtual/](../../../../../backend/modules/product/virtual)
- Handler 基类：[backend/crmeb/trade/product/BaseProductTypeHandler.php](../../../../../backend/crmeb/trade/product/BaseProductTypeHandler.php)
- Handler 接口：[backend/crmeb/trade/product/ProductTypeHandlerInterface.php](../../../../../backend/crmeb/trade/product/ProductTypeHandlerInterface.php)
- OrderAdapter 基类：backend/crmeb/trade/order/type/BaseOrderTypeAdapter.php
- 安装器基类：backend/crmeb/module/BaseModuleInstaller.php
- 架构铁律：[../../../../rules/架构规范.md](../../../../rules/架构规范.md)（常驻精简版）；架构详解（目录结构/注册机制）：[../../../../rules/架构规范详解.md](../../../../rules/架构规范详解.md)

## 十二、详细专题文档

- [后台管理端前端扩展](../04-前端扩展/backend-frontend.md)
- [用户端产品列表实现](../05-用户端开发/user-product-list.md)
- [用户端产品详情实现](../05-用户端开发/user-product-detail.md)
- [用户端购物车实现](../05-用户端开发/user-cart.md)
- [用户端创建订单实现](../05-用户端开发/user-order-create.md)
- [用户端订单列表实现](../05-用户端开发/user-order-list.md)
- [用户端订单详情实现](../05-用户端开发/user-order-detail.md)

## 十三、架构债务与改进方向

### 13.1 已修复的共性违规

在本次复查中，已集中修复以下高频违规点，新扩展开发时应引以为戒：

- **直接 Db 调用**：`OrderCenterServices`、`NormalOrderAdapter`、`NormalAfterSaleHandler`、`OrderController`、`Listeners`、`CenterProductServices`、`CenterProductController` 等已改为通过 DAO 操作数据库。
- **事件类常量调用**：`StoreOrderCreateServices`、`StoreOrderRefundServices`、`StoreOrderTakeServices`、`UserExtractServices`、`UserMoneyServices`、`UserBillServices`、`PayTransferNotifyServices`、`UserRechargeServices` 等已将 `event(XxxListener::class, ...)` 改为字符串 key。
- **时间字段类型**：订单/售后相关的时间字段统一使用 `time()` 整型，避免与 `int` 字段类型不匹配。
- **支付驱动配置越界写入**：`PayChannelServices` 已移除向 `system_config` 表写入/读取支付配置的回退逻辑，配置只保存在 `eb_pay_channel.config_fields`。

### 13.2 仍需全局治理的跨层/跨模块耦合

以下问题属于历史遗留或需要更大范围重构，本次未全部改动，但新扩展应避免再引入：

- **`app/` 层大量 `use modules/...`**：如 `CenterProductServices` 引入 `NormalProductSkuDao`、`PublicController` 引入 `DeliveryServiceServices` 等。理想方案是把产品类型相关的 SKU/运费/履约逻辑下沉到 `Handler` 接口，中台通过 `ProductTypeRegistry` 调用，而不是直接 `use` 模块类。
- **`modules/` 层跨模块引用**：如 `modules/trade/integral/service/StoreIntegralServices.php` 使用 `NormalProductSkuDao`；`modules/channel/wechat_mp/service/MessageServices.php` 直接引用各活动 Services。应通过 `crmeb/` 核心契约或事件机制通信。
- **`app/services/system/SystemCrudServices.php`** 使用 `Db::raw('CURRENT_TIMESTAMP')` 和字符串时间，建议统一收口到 DAO 并使用 `time()`。

### 13.3 自定义表单后续演进建议

当前 `eb_order_center.custom_form` JSON 方案适合读多写少、无需按字段筛选的场景。若业务出现以下需求，应演进到独立关系表：

- 按某个自定义字段值筛选订单；
- 对自定义字段做统计、聚合或全文检索；
- 需要为字段值建立索引以满足性能要求。

演进表设计参考：

```sql
CREATE TABLE `eb_order_custom_form` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `order_no` varchar(64) NOT NULL COMMENT '订单号',
  `field_key` varchar(64) NOT NULL COMMENT '字段标识',
  `field_label` varchar(255) NOT NULL COMMENT '字段标题',
  `field_value` text COMMENT '字段值',
  `add_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_order_no` (`order_no`),
  KEY `idx_field_key_value` (`field_key`,`field_value`(64))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='订单自定义表单关系表';
```

迁移时应保持 `eb_order_center.custom_form` 作为冗余快照，同时由中台服务或事件监听器维护关系表，避免读逻辑大面积改动。

## 十四、参考模板对照

| 维度 | normal 实物 | virtual 网盘 | cardkey 卡密 | hotel 酒店 | ticket 门票 |
|------|------------|-------------|-------------|-----------|------------|
| source_type | normal_order | virtual_order | cardkey_order | hotel_order | ticket_order |
| 是否虚拟 | 否 | 是 | 是 | 否 | 否 |
| 是否物流 | 是 | 否 | 否 | 否（入住核销） | 否（电子票核销） |
| 交易模式 | 全部 | normal/seckill | 自定义 | 自定义 | 自定义 |
| 售后期限 | 7 天 | 24 小时 | 1 小时 | 独立规则 | 独立规则 |
| 履约方式 | 手动发货 | 自动发放网盘链接+提取码 | 自动发卡/卡密回收 | 房态预订 | 电子票 |
| 参考亮点 | 标准全流程 | 自定义 pipeline + SKU 额外字段 | 无需规格 | 选择契约（temporal） | 选择契约（temporal） |

**选择参考模板**：实物履约 → 参考 `normal`；自动发放型 → 参考 `virtual` / `cardkey`；时段/资源型（酒店、门票、预约服务）→ 参考 `hotel` / `ticket` 的 `getSelectionSpec()`（temporal 选择模型）。

## 十五、安装与验证

### 15.1 安装（后台模块管理）

```
GET  /adminapi/setting/module/list?type=product_type   # 查看产品类型安装状态
POST /adminapi/setting/module/install                  # body: module_key=product_{type}
POST /adminapi/setting/module/uninstall                # body: module_key=product_{type}
```

### 15.2 验证清单

- [ ] `php think route:list` 能看到 `adminapi/{type}/*`、`api/{type}/*` 路由
- [ ] 后台菜单「商品 → {类型名}」正常显示并可进入
- [ ] 新增商品：中台表写入 + `eb_pt_{type}` / `eb_pt_{type}_sku` 扩展表写入
- [ ] 下单：`eb_order_center`（source_type={type}_order）+ `eb_pt_{type}_order` 双写
- [ ] 支付 → 履约 → 收货 → 完成全链路状态推进正确
- [ ] 售后申请 / 审核 / 退款按售后规则生效
- [ ] 商品删除后扩展表与 SKU 表数据被清理（`afterProductDelete`）

## 十六、新增类型 Checklist（速查）

```
□ 1. modules/product/{type}/ 目录创建（MVC + handler + listener + route + crontab）
□ 2. Handler.php 实现（getTypeKey/getTypeName/getOrderSourceType/getCompatibility/isVirtual/decStock/incStock/fulfillOrder）
□ 3. App.php：register() 注册 Handler + OrderTypeRegistry；boot() 加载 event.php + AfterSaleRuleRegistry
□ 4. Installer.php：getKey/getName/getType + runMigrate（幂等建表）+ getMenuConfig + rollbackMigrate + deployFrontendAssets
□ 5. Model / DAO：pt_{type} / pt_{type}_sku / pt_{type}_order 三组
□ 6. handler/{Type}OrderAdapter.php：状态机 + 状态树 + 操作按钮
□ 7. handler/{Type}AfterSaleHandler.php：售后规则
□ 8. event.php + listener/：订单生命周期 + 售后 + 商品详情扩展监听器
□ 9. route/admin.php + manage.php + user.php（控制器以 Controller 结尾，real_name 齐全）
□ 10. service/ProductSaveServices：中台 + SKU + 扩展表写入链路
□ 11. 前端：管理端 UMD 扩展 + 移动端 page_config / selection / cart 契约
□ 12. 安装并跑通验证清单（见十五、15.2）
```
