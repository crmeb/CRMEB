# 业务模块分册 · 产品类型（product）

核对日期：2026-11-06（按 `backend/modules/product/` 现役代码核对）。代码与本文冲突时以代码为准。

**先读**：[00-总览-类型路由与边界](00-总览-类型路由与边界.md) 第 4 节（模块通用机制：发现、Installer、路由、打包、校验命令）。领域归属判定：[domain-selection.md](../domain-selection.md)。注册与契约细节：[registration-and-contracts.md](../registration-and-contracts.md)。

强制红线真源：[插件开发规范](../../../../rules/插件开发规范.md)、[架构规范](../../../../rules/架构规范.md)。本篇只写 product 域「怎么做」，不重复 00 里的通用安装/打包流程正文。

---

## 1. 领域判定：口诀与反例

### 1.1 口诀

**改变「卖什么」**——商品形态、SKU/库存维度、配送或履约方式、详情展示块、产品专属售后资格 → 落 `backend/modules/product/{type}/`，在 `App::register()` 里登记 `ProductTypeRegistry`（通常还要 `OrderTypeRegistry` + `AfterSaleRuleRegistry`）。

与 **trade（怎么卖）** 正交：产品 Handler 用 `canCombineWith($tradeMode)` 和 `getCompatibility()` 声明能和哪些交易模式组合；交易模式负责活动价与下单校验，产品类型不负责秒杀价。

### 1.2 应归入 product 的典型需求

| 需求特征 | 示例（现役或文档 walkthrough） |
| --- | --- |
| 新 `product_type` 值，中台 `product_center.product_type` 存你的 typeKey | `normal` / `virtual` / `cardkey`；文档中的 `course` |
| 自有扩展表存形态字段，支付后走专属履约 | 卡密池 `pt_cardkey_stock`、虚拟网盘字段 |
| 需要独立订单业务表 + 专属状态树 | `cardkey_order` + `CardkeyOrderAdapter` |
| 售后规则与实物不同（仅退款、核销后不可退等） | `CardkeyAfterSaleHandler` |

### 1.3 反例（不要做成 product）

| 误判 | 正确落位 | 原因 |
| --- | --- | --- |
| 「下单时必须校验拼团是否成团」 | `modules/trade/combination` | 改变成交契约，属于交易模式 Handler |
| 「支付成功送积分/抽奖次数」 | `modules/marketing/*` | 去掉后订单语义不变 |
| 「接微信支付 SDK」 | `driver/pay/*` | 第三方协议层 |
| 「小程序 openid 登录」 | `modules/channel/wechat_mini` | 端渠道能力 |
| 在 `app/services/order/*` 里加 `if ($type=='xxx')` | 应实现 Handler + 注册 | 零侵入：禁止改中央 Service 类型分支 |

### 1.4 仍不确定时

1. 读 [domain-selection.md](../domain-selection.md)「产品类型与交易模式」小节。  
2. 问：**禁用这个插件后，普通商品还能不能照常下单支付？** 若不能，多半是 product；若只是少发奖，是 marketing。  
3. 再问：**去掉它后活动价/限购逻辑是否仍残留在订单里？** 若是，可能是 trade 没拆干净。

---

## 2. 现役模块对照

| 目录 | `plugin.json` key | typeKey | order sourceType | 结构特点 |
| --- | --- | --- | --- | --- |
| `product/normal` | `product_normal` | `normal` | `normal_order` | 物流、运费、`ProductDetailExtend` / 列表扩展 |
| `product/virtual` | `product_virtual` | `virtual` | `virtual_order` | 自动发货、详情履约预览事件 |
| `product/cardkey` | `product_cardkey` | `cardkey` | `cardkey_order` | 卡密库存池、支付后发卡、`canCombineWith` 仅 `normal` |

命名约定（与 00 一致）：

- 清单 `type` 固定 `product_type`。
- 清单 `key` 建议 `product_{目录名}`，与 `Installer::getKey()`、菜单 `unique_auth` 前缀、管理端 `pages/plugin/<key>/` 一致。
- Handler `getTypeKey()` 与 `product_center.product_type` 存值一致（通常等于目录名，如 `cardkey`）。
- `getOrderSourceType()` 与 `OrderTypeAdapter::getSourceType()` 一致（如 `cardkey_order`）。

---

## 3. 推荐参考模块（复制谁）

| 你要做的形态 | 优先复制 | 理由 |
| --- | --- | --- |
| 数字内容、支付后自动交付、无物流 | `product/cardkey` 或 `product/virtual` | 履约在 `fulfillOrder`，库存可虚拟 |
| 实物 + 快递 + 运费模板 | `product/normal` | 运费、`requiresShipping`、列表扩展 |
| 从 0 学三维注册 | `product/cardkey/App.php` + `Handler.php` 前 120 行 | 注册最清晰、注释完整 |

复制后必改三处身份：`namespace`、`plugin.json` 的 `key`/`path`、`Handler` 的 typeKey/sourceType。不要复制 `ensureCenterTables()` 除非你的类型确实需要在中台表缺失时自愈（卡密安装器内含大段 DDL，新类型可改为依赖全局迁移，只建 `pt_*` 自有表）。

---

## 4. 标准目录树

```text
backend/modules/product/{type}/
├── plugin.json                 # key=product_{type}, type=product_type, path=modules/product/{type}
├── App.php                     # register: 三维 Registry；boot: loadEvent(event.php)
├── Installer.php               # BaseModuleInstaller：表/菜单/seed/clean
├── Handler.php                 # extends BaseProductTypeHandler
├── event.php                   # 可选：ProductDetailExtend、OrderPaySuccess 等
├── handler/
│   ├── {Type}OrderAdapter.php  # implements OrderTypeAdapterInterface
│   └── {Type}AfterSaleHandler.php
├── controller/
│   ├── admin/                  # *Controller.php
│   └── user/v1/                # 用户端（与现役模块对齐）
├── route/
│   ├── admin.php               # 挂到 /adminapi/{prefix}/
│   └── user.php                # 挂到 /api/
├── service/                    # *Services.php
├── dao/                        # *Dao.php
├── model/                      # *Model.php（或项目约定后缀）
├── validate/                   # 按需
├── listener/                   # 扩展事件监听器
├── jobs/                       # 异步履约、补发（按需）
├── crontab/                    # 定时任务（按需，须 CrontabTaskRegistry + seed）
└── pipeline/                   # 履约管线（按需，normal 等可能有）

frontend/admin/src/pages/plugin/product_{type}/   # ext.json + 源码；构建 UMD 见 admin-extension.md
```

未启用或未安装的插件：`ModuleRegistry` 不会调用 `App::register()`，`ModuleRouteLoader` 不会加载其路由。

---

## 5. plugin.json

最小可安装清单（与 `product/cardkey/plugin.json` 同结构）：

```json
{
    "key": "product_yourtype",
    "name": "你的商品类型",
    "description": "一句话说明形态与履约",
    "version": "1.0.0",
    "author": "作者",
    "type": "product_type",
    "path": "modules/product/yourtype",
    "require": {
        "crmeb": "^8.0",
        "php": "^8.0"
    }
}
```

可选扩展（按需，见 [manifest-and-lifecycle.md](../manifest-and-lifecycle.md)）：

- `diy.components`：移动端 DIY 组件白名单。
- `default_install`：是否随系统默认安装（需与产品策略一致）。

**五处 key 必须相同**：`plugin.json.key`、`Installer::getKey()`、后台菜单权限标识、前端 `pages/plugin/<key>/`、打包 zip 文件名中的 key。

首版阶段 `version` 保持 `1.0.0`，与 `Installer::getVersion()`（若覆写）一致；见架构规范「当前发布阶段」。

---

## 6. App.php：register 与 boot

### 6.1 register（三维注册）

现役卡密示例（真源 `backend/modules/product/cardkey/App.php`）：

```php
public static function register(ThinkApp $app): void
{
    $app->make(ProductTypeRegistry::class)->register('cardkey', Handler::class);
    OrderTypeRegistry::register('cardkey_order', CardkeyOrderAdapter::class);
    AfterSaleRuleRegistry::registerProductType('cardkey', CardkeyAfterSaleHandler::class);
}
```

| 注册调用 | 回答的问题 | 漏注册的典型故障 |
| --- | --- | --- |
| `ProductTypeRegistry::register($typeKey, Handler::class)` | 中台能否创建/识别该 `product_type` | 后台选不了类型；`get()` 回退默认 Handler |
| `OrderTypeRegistry::register($sourceType, Adapter::class)` | 订单列表/详情状态、按钮、进度 | 详情状态错乱或按钮缺失 |
| `AfterSaleRuleRegistry::registerProductType($typeKey, AfterSale::class)` | 退款/退货资格与方案 | 售后入口错误或被中央默认规则覆盖 |

`OrderTypeRegistry::register` 是静态方法；`ProductTypeRegistry` 从容器 `make`（与 `AppService` 绑定单例一致）。

可选：在 `register()` 里 `$app->make(CrontabTaskRegistry::class)->register($mark, $taskClass)`（参考 marketing/signin，产品域若有定时取消/过期任务）。

### 6.2 boot（事件）

```php
public static function boot(ThinkApp $app): void
{
    $eventFile = __DIR__ . '/event.php';
    if (is_file($eventFile)) {
        $event = include $eventFile;
        if (is_array($event)) {
            $app->loadEvent($event);
        }
    }
}
```

**禁止**在 `backend/app/AppService.php` 写死本模块类名；扩展只走 Registry 与本模块 `App.php`。

---

## 7. Handler 契约（方法级）

接口：`backend/crmeb/trade/product/ProductTypeHandlerInterface.php`（32 个方法）。默认实现：`BaseProductTypeHandler.php`。**只覆盖与你形态相关的方法**，其余继承默认。

### 7.1 身份与策略（必覆盖）

| 方法 | 作用 | 卡密示例要点 |
| --- | --- | --- |
| `getTypeKey(): string` | 中台 `product_type` | `'cardkey'` |
| `getTypeName(): string` | 后台展示名 | `'卡密商品'` |
| `getOrderSourceType(): string` | 订单 source_type，与 Adapter 一致 | `'cardkey_order'` |
| `getCapabilities(): array` | 能力声明（购物车、规格等） | 按形态返回 |
| `getPricingPolicy(): array` | 计价策略提示 | 默认即可或覆盖 |
| `getRequiredFields(): array` | 创建商品必填扩展字段 | 卡密为空数组 |
| `canCombineWith(string $tradeMode): bool` | 与交易模式兼容性 | 卡密仅 `normal` |
| `getCompatibility(): array` | 结构化兼容说明 | 供前端/校验 |
| `isVirtual(): bool` | 是否虚拟 | 卡密/虚拟为 true |
| `requiresShipping(): bool` | 是否要地址物流 | normal 为 true |
| `supportsPickup(): bool` | 自提 | 按形态 |
| `supportsReturn(): bool` | 是否支持退货（能力，非规则细节） | 调用方不得硬编码 type 名 |

### 7.2 商品 CRUD 与详情

| 方法 | 作用 |
| --- | --- |
| `afterProductSave(int $productId, array $data): void` | 保存后写扩展表 |
| `afterProductDelete(int $productId): void` | 删商品清理扩展数据 |
| `saveFromAdmin(int $id, array $data): ?int` | 后台保存入口（复杂形态可委托 Service） |
| `getAdminExtendInfo(int $productId): array` | 后台编辑页扩展块 |
| `getDetailExtend(int $productId): array` | 详情扩展（也可用事件 `ProductDetailExtend`） |
| `getDetailSpecs(int $productId): array` | SKU 规格列表 |
| `getListExtend(int $productId): array` | 列表卡片扩展 |
| `getPageConfig(string $scene, string $appVersion): array` | 端上页面配置/sections |
| `getOrderFormComponents(): array` | 下单表单额外组件 |
| `getSelectionSpec(): array` | 选品规格说明 |
| `getCartStrategy(): array` | 加购策略 |
| `getSkuExtraFields(): array` | SKU 编辑器额外字段 |

### 7.3 库存与 SKU

| 方法 | 作用 |
| --- | --- |
| `getSkuInfo(int $productId, string $skuUnique): ?array` | 下单/购物车取价与库存 |
| `decStock` / `incStock` | 扣减/回滚库存（卡密可能扣「未使用卡」计数） |
| `getShippingInfo(int $productId): array` | 运费相关（实物） |

### 7.4 订单生命周期

| 方法 | 作用 |
| --- | --- |
| `fulfillOrder(string $orderNo, array $orderInfo): bool` | 支付后履约（发卡、开权限、发网盘链接） |
| `getOrderExtend(string $orderNo): array` | 订单详情扩展 |
| `getOrderDetailRoute(string $orderNo): string` | 端上详情路由 |
| `getAfterSaleRule(): AfterSaleHandlerInterface` | 可委托独立 AfterSaleHandler 类 |

履约失败应打日志并返回 `false`；是否阻断支付回调由中台策略决定，Handler 内不要直接改支付状态。

### 7.5 扩展数据：优先事件而非改 app

现役 `product/virtual/event.php` 监听：

- `ProductDetailExtend` — 详情 `extensions.*`
- `OrderCenterListExtend` / `OrderCenterDetailExtend` — 订单列表/详情

`SafeEvent` 对部分扩展事件做了安全包装；监听器内捕获业务异常，**不要让无关请求整体 500**（除非该事件属于强制校验）。

---

## 8. OrderTypeAdapter（方法级）

接口：`backend/crmeb/trade/order/type/OrderTypeAdapterInterface.php`。

| 方法 | 作用 |
| --- | --- |
| `getSourceType(): string` | 静态，与 Handler `getOrderSourceType()` 一致 |
| `getModelClass(): string` | 源订单 Model（如 `pt_cardkey_order` 对应 Model） |
| `getOrderCategory()` / `getOrderCategoryLabel()` | 订单分类展示 |
| `resolveStatusCode(array $sourceOrder): string` | 映射到中台统一 status_code |
| `resolvePayStatus` / `resolveRefundStatus` | 支付/退款状态 |
| `resolveBusinessStatus(array $sourceOrder): ?array` | 业务子状态（可选） |
| `getStatusButtons(string $statusCode): array` | 按状态可用操作 |
| `getOrderButtons(array $detail): array` | 详情页按钮 |
| `getProgress(array $detail): array` | 进度条节点 |
| `advance(string $orderNo, string $extStatus, array $extra): void` | 推进业务状态（确认收货、核销等） |

Adapter 只翻译**已有源表**记录，不要在 Adapter 里创建订单；创建在 `fulfillOrder` 或支付监听里完成。

---

## 9. AfterSaleHandler（方法级）

接口：`AfterSaleHandlerInterface`（规则 + 生命周期 + 退款方案）。

核心方法：

- `getRule($orderCenter, $sourceOrder): array` — `allow`、`available_types`、`deadline` 等。
- `buildRefundPlan(...): array` — 退款方案固化，恢复任务不重算。
- `beforeRefundSubmit` / `afterRefundComplete` 等 — 冻结卡密、撤销权益。

产品维度注册在 `AfterSaleRuleRegistry::registerProductType($typeKey, $class)`，与 trade 模式维度规则分开。

---

## 10. Installer：表、菜单、seed、清理

继承 `crmeb\module\BaseModuleInstaller`。

### 10.1 必实现

| 方法 | 要求 |
| --- | --- |
| `getKey()` / `getName()` / `getType()` | 与 plugin.json 一致；`getType()` 返回 `product_type` |
| `runMigrate()` | `CREATE TABLE IF NOT EXISTS`，表名建议 `pt_{type}_*` 或业务前缀；**DDL 用 `$this->exec()`，不要包在大事务里** |
| `rollbackMigrate()` | 卸载删自有表 |
| `getMenuConfig()` | 菜单树；挂核心父级用 `parent_key: core:admin-product` 等 |

### 10.2 可选

- `seed()`：字典、定时任务 `system_crontab`、DIY `seedPageLinks`。
- `cleanData()`：按 mark/分类名删 seed，禁删系统表。
- `onEnable()` / `onDisable()`：必须 `parent::onEnable()`。

卡密安装器还调用 `ensureCenterTables()`：在新环境补 `product_center` 等中台表。新插件是否复制这段取决于部署是否保证全局迁移已跑；**不要对系统表做 ALTER 破坏其他类型**。

### 10.3 菜单与权限

- 可见菜单 `auth_type=1`。
- 接口权限 `auth_type=2`，必须有 `api_url` + `methods`，与 `route/admin.php` 一致。
- 按钮 `auth_type=3` + `unique_auth`。

路由示例前缀（卡密）：`/adminapi/cardkey/product/list` → `ProductController::list`。

---

## 11. 路由与 Controller 契约

- 文件：`route/admin.php`、`route/user.php`；`use think\facade\Route`。
- Admin 中间件：对齐同域模块（`AllowOriginMiddleware`、`AdminAuthTokenMiddleware`、角色、日志）。
- 控制器：**类名/文件名以 `Controller` 结尾**；构造注入 Service 时声明 `@var`。
- 响应：统一 `{status,msg,data}`；列表 `{list,count}`。

路径、HTTP 方法、Controller 方法名（camelCase）、Installer 菜单 `api_url`、前端 `api/*.js` 函数名五处对齐。

---

## 12. 前端契约

- 管理端：仅改 `frontend/admin/src/pages/plugin/product_{type}/`（`ext.json` + 页面 + `npm run build` 产出 UMD）。见 [admin-extension.md](../admin-extension.md)。
- 移动端：优先消费 Handler 的 `getPageConfig`、`display_sections`、订单 Adapter 的 `page_config.sections`；**不要改** uni-app 核心订单页 type 分支。
- 独立页：源码放 `pages-modules/<标识>/`，用法见 [uniapp-pages.md](../uniapp-pages.md)；Installer `ensurePageLinkCategory` + `seedPageLinks` 登记同一条路径。DIY 组件写 `plugin.json` → `diy.components`。

---

## 13. 逐步落地（建议顺序）

1. **定 typeKey、sourceType、plugin key**（如 `course` / `course_order` / `product_course`）。
2. **复制参考模块目录**，改 namespace 与三处身份。
3. **写 plugin.json + Installer::getKey/getType**，空菜单先能装。
4. **设计自有表**（扩展表 + 可选订单业务表），`runMigrate()` 幂等。
5. **实现 Handler 最小集**：身份、`getSkuInfo`、`decStock`/`incStock`、`fulfillOrder`、虚拟/物流布尔。
6. **App::register 三维注册**，本地启用插件后跑 `php think registry:check`。
7. **实现 OrderAdapter 状态树**（至少：待付款→待发货/已完成→售后）。
8. **实现 AfterSaleHandler::getRule** 与必要退款钩子。
9. **后台 route + Controller + Service**：建品、列表、详情。
10. **event.php**（若需详情/列表扩展）。
11. **管理端 plugin 页面 + api 封装**。
12. **全链路手工验收**（下节）。
13. **静态检查 + 打包** `.plugin-packages/`。

---

## 14. 全链路验收

### 14.1 静态（不写库也可）

```bash
cd backend
php think plugin:manifest-check
php think registry:check
php think arch:check    # 只看新增违规
php think event:check   # 监听类存在
```

### 14.2 生命周期（隔离库）

- [ ] 后台安装 → 启用 → 菜单与路由可访问  
- [ ] 禁用 → 路由不加载、中台不崩溃  
- [ ] 卸载 → 自有表/菜单/seed 清理（在隔离库测）

### 14.3 业务（产品专属）

- [ ] 后台创建该 `product_type` 商品并上架  
- [ ] 详情 API 含扩展块（Handler 或 `ProductDetailExtend`）  
- [ ] 加购/下单：库存扣减正确；不兼容的 trade 模式被 `canCombineWith` 拒绝  
- [ ] 支付成功 → `fulfillOrder` 执行（卡密发出/权限开通）  
- [ ] 订单中心：状态、按钮、进度与 Adapter 一致  
- [ ] 售后：规则与退款回滚库存/权益正确  
- [ ] 禁用本插件后，其他 product 类型不受影响  

联调地址见工作区规则：管理端 `http://localhost:1619/admin`，接口基址 `http://192.168.1.169`。

---

## 15. 打包到 `.plugin-packages/`

与 [插件开发规范](../../../../rules/插件开发规范.md) 第七节一致：

1. 确认改动仅在 `backend/modules/product/{type}/` 与 `frontend/admin/src/pages/plugin/product_{type}/`。  
2. 前端扩展先构建 UMD。  
3. 压缩**插件根目录**（含 `plugin.json`），输出到仓库根 `.plugin-packages/{key}-{version}.zip`（目录 gitignore，现包现用）。  
4. 交付说明写：key、版本、参考模块、验收项、是否依赖其他插件。

---

## 16. 禁止事项（product 加强版）

- 禁止修改 `backend/app/`、`backend/crmeb/`、核心 uni-app/admin 源码、安装 SQL、composer、config。  
- 禁止 `modules\product\foo` use `modules\product\bar` 或 `modules\trade\*`；跨域走事件或 crmeb 契约。  
- 禁止在 Handler 里写活动价/秒杀库存（属于 trade Handler）。  
- 禁止虚构 `source_type`：中台已有 `normal_order` 时，活动型卖法用 trade 模式，不要为活动单独造 product type。  
- 禁止把密钥、第三方账号写进 plugin.json 或代码常量。  
- 禁止跳过三维注册中的任一维「以后再加」——上线前 `registry:check` 会报错或运行时不一致。

---

## 17. 排错速查

| 现象 | 优先查 |
| --- | --- |
| 后台没有「商品类型」选项 | 插件未启用；`ProductTypeRegistry` 未 register；typeKey 与保存时不一致 |
| 下单报默认类型错误 | Handler `getSkuInfo` 返回 null；stock 未同步 |
| 支付后未发卡 | `fulfillOrder` 是否实现；订单 source 表是否创建；支付事件是否触发履约 |
| 订单详情按钮不对 | Adapter `getOrderButtons` / status 映射；source 表 status 字段值 |
| 售后入口灰色 | `AfterSaleRuleRegistry` 未注册；`getRule.allow=false` |
| 路由 404 | 插件未启用；`route/admin.php` 前缀与菜单不一致 |

---

## 18. 与 trade / marketing 协作边界（小结）

```text
product Handler  →  SKU、库存、履约、产品售后、详情 sections
trade Handler    →  activityId、活动价、validateOrder、活动生命周期
marketing 监听   →  OrderPaySuccess 等发奖，不改 order_item 成交价
```

新增「课程商品 + 课程限时价」：product 模块管内容与开通，trade 模块管限时价与资格，两者通过 `canCombineWith` 与 trade 校验联动，**不要在 product Handler 里写秒杀 SQL**。
