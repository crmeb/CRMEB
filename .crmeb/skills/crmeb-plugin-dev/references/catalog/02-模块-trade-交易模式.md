# 业务模块分册 · 交易模式（trade）

核对日期：2026-11-06（按 `backend/modules/trade/` 现役代码核对）。代码与本文冲突时以代码为准。

**先读**：[00-总览-类型路由与边界](00-总览-类型路由与边界.md) 第 4 节；领域判定：[domain-selection.md](../domain-selection.md)；注册契约：[registration-and-contracts.md](../registration-and-contracts.md)。

强制红线：[插件开发规范](../../../../rules/插件开发规范.md)、[订单中台规范](../../../../rules/订单中台规范.md)。

---

## 1. 领域判定：口诀与反例

### 1.1 口诀

**改变「怎么卖」**——在已有 product 形态上，改变活动价、购买资格、限购/活动库存、拼团/砍价/秒杀生命周期；且**校验失败必须阻断下单** → `backend/modules/trade/{mode}/`，`App::register()` 登记 `TradeModeRegistry`。

订单创建/支付主流程通过 `TradeModeRegistry::get($modeKey)` 取 Handler，**禁止**在 `app/services/order/*` 增加 `if ($mode=='seckill')`。

### 1.2 应归入 trade

| 特征 | 现役模块 |
| --- | --- |
| 活动价覆盖 SKU 价 | `trade/seckill` |
| 成团/砍价进度、失败退款 | `trade/combination`、`trade/bargain` |
| 积分+现金混合、独立订单语义 | `trade/integral`（另注册 `integral_order` Adapter） |
| `validateOrder` / `beforeCreateOrder` 抛错即不可买 | 所有 ActivityHandler |

### 1.3 反例

| 误判 | 正确落位 |
| --- | --- |
| 支付成功送优惠券 | `marketing` |
| 新商品是「票务」形态、座位库存 | `product`（trade 只做活动价） |
| 仅后台统计、不参与下单计价 | `marketing` 或报表模块 |
| 去掉后用户仍能以活动价成交 | 说明计价逻辑还留在 app，应收回 trade Handler |

### 1.4 trade 与 marketing 分界

- 订单管线**必须**调用 Handler 完成交易校验或计价 → trade。  
- 去掉功能后订单创建/支付语义不变，只是不再发奖 → marketing。  
- `isMarketingActivity(): true` 的模式会进入 `getMarketingKeys()`，用于商品列表活动角标；**普通购买**由 app 层 `DefaultTradeModeHandler` 注册为 `normal`，且 `isMarketingActivity()` 为 false（详见 2026-10-30-02 文）。

---

## 2. 现役模块对照

| 目录 | plugin key | modeKey | 独立 OrderAdapter | 说明 |
| --- | --- | --- | --- | --- |
| `trade/seckill` | `activity_seckill` | `seckill` | 否（复用商品订单） | 活动价、时段、库存 |
| `trade/combination` | `activity_combination` | `combination` | 否 | 拼团生命周期、事件多 |
| `trade/bargain` | `activity_bargain` | `bargain` | 否 | 砍价进度 |
| `trade/integral` | `activity_integral` | `integral` | **是** `integral_order` | 积分商城 |

清单约定：

- `type`: `mode`（不是 `trade` 字符串）。  
- `key`: 现役多用 `activity_{name}`；新模块可继续此风格以对齐菜单。  
- `path`: `modules/trade/{name}`。

---

## 3. 推荐参考模块

| 场景 | 复制 | 理由 |
| --- | --- | --- |
| 活动价 + 下单校验 + 活动 CRUD | `trade/seckill` | Handler 最标准，`SeckillActivityHandler` 清晰 |
| 复杂生命周期 + 多事件 | `trade/combination` | 过期、成团、退款联动 |
| 需要独立订单表/状态 | `trade/integral` | `App::boot` 里 `OrderTypeRegistry::register` |

---

## 4. 目录树

```text
backend/modules/trade/{mode}/
├── plugin.json                 # type=mode, key=activity_{mode}
├── App.php                     # register → TradeModeRegistry
├── Installer.php
├── handler/{Mode}ActivityHandler.php   # extends BaseTradeModeHandler
├── event.php                   # 活动生命周期、OrderPay* 等
├── controller/admin|user/
├── route/admin.php | user.php
├── service/ dao/ model/ validate/
├── listener/ jobs/ crontab/
└── frontend/admin/.../plugin/activity_{mode}/

integral 额外：
└── handler/IntegralOrderAdapter.php
```

---

## 5. plugin.json 模板

```json
{
    "key": "activity_yourmode",
    "name": "你的活动",
    "description": "交易模式：活动价与下单校验",
    "version": "1.0.0",
    "author": "作者",
    "type": "mode",
    "path": "modules/trade/yourmode",
    "require": { "crmeb": "^8.0", "php": "^8.0" }
}
```

可选 `diy.components`（如秒杀列表组件名与 DIY 数据节点一致）。

---

## 6. App.php

### 6.1 标准（复用商品订单）

```php
public static function register(\think\App $app): void
{
    $app->make(TradeModeRegistry::class)
        ->register('seckill', SeckillActivityHandler::class);
}
```

### 6.2 独立订单类型（integral 模式）

`trade/integral/App.php` 真源：

- `register()`：只注册 `TradeModeRegistry`。  
- `boot()`：`OrderTypeRegistry::register('integral_order', IntegralOrderAdapter::class)` + `loadEvent`。

**规则**：复用普通商品订单的活动（秒杀/拼团）**不要**虚构新 `source_type`；只有订单语义与 normal 完全不同（积分单、0 元纯积分）才注册 Adapter。

### 6.3 启动顺序（理解即可）

`AppService::boot()` 先 `PluginManager` 触发各模块 `register`，再注册 app 内置 `normal` / `member`。不要试图在模块里覆盖 `normal` key。

---

## 7. TradeModeHandler 契约（方法级）

接口：`backend/crmeb/trade/mode/TradeModeHandlerInterface.php`（29 方法）。基类：`BaseTradeModeHandler`。

### 7.1 身份与展示

| 方法 | 说明 |
| --- | --- |
| `getType(): string` | modeKey，如 `seckill` |
| `getLabel(): string` | 中文标签，供 `allInfo()` 下拉 |
| `getLegacyType(): int` | 旧版数字 type 映射，`getByLegacyType()` 用 |
| `isMarketingActivity(): bool` | true → 进入 `getMarketingKeys()` |

### 7.2 商品与活动查询

| 方法 | 说明 |
| --- | --- |
| `getProductExist(array $productIds): array` | 各商品是否参与本模式 |
| `isProductInActivity(int $productId): bool` | 单商品 |
| `getProductActivity(int $productId): ?array` | 活动摘要（列表角标） |
| `getActivityIdsList(...): array` | 批量活动 ID |

### 7.3 计价与校验（核心）

| 方法 | 说明 |
| --- | --- |
| `getActivityPrice(int $productId, string $skuUnique, int $activityId): array` | 活动价；返回结构对齐 seckill |
| `validateOrder(...): void` | **失败抛异常**，阻断下单 |
| `checkCartStock(...): array` | 购物车数量校验 |
| `beforeCreateOrder(...): void` | 创建前最后闸门 |
| `getOrderExtraInfo(int $activityId): array` | 写入订单扩展 |
| `getAllowedPayTypes` / `isFreePay` / `getFreePayType` / `getPayRedirectUrl` | 支付限制 |

### 7.4 订单生命周期钩子

| 方法 | 典型用途 |
| --- | --- |
| `afterOrderCreate` | 占活动库存、开团 |
| `beforeOrderPay` / `onOrderPay` / `afterOrderPay` | 支付成功扣活动量、成团 |
| `onOrderPayFail` | 释放占用 |
| `beforeOrderCancel` / `afterOrderCancel` | 回滚活动库存 |
| `beforeOrderRefund` / `afterOrderRefund` | 活动侧回滚 |
| `onOrderComplete` | 确认收货后活动完结 |
| `getOrderDetailExtend` / `getActivityButtons` | 详情展示与按钮 |

未支持的能力保持基类默认；**可选展示失败可降级，校验失败不可降级**（registration-and-contracts.md）。

---

## 8. 与 product 的协作

- 下单前：中台先解析 `product_type` → Product Handler 取 SKU，再解析 `trade_mode` + `activity_id` → Trade Handler 计价与 `validateOrder`。  
- Product Handler 的 `canCombineWith($tradeMode)` 必须声明是否允许该模式；trade 侧仍要做活动库存校验。  
- 不要在 trade Handler 里改商品扩展表结构；活动数据放 trade 自有表（如 `store_seckill`）。

---

## 9. event.php

trade 模块常用事件（以 combination/seckill 为准，新增模式按需挂载）：

- 订单支付/取消/退款相关（与 Handler 钩子互补，适合通知、队列）。  
- 活动过期：`crontab` + 监听器更新活动状态。

事件 key 为**字符串**（无 `Listener` 后缀），与 `app/event.php` 触发名一致。见 [事件规范](../../../../rules/事件规范.md)。

---

## 10. Installer

- 活动表、参与记录表：`runMigrate()` 幂等 DDL。  
- 菜单挂 `core:admin-marketing` 或现役 seckill 同级父级。  
- **定时任务**：过期关闭、自动取消未付款团单  
  - `App::register` → `CrontabTaskRegistry::register($mark, $class)`  
  - `seed()` 写 `system_crontab`（`mark` 一致、`is_open=1`）  
  - `cleanData()` 按 mark 删除  
  - 参考 `marketing/signin` 与 `product/normal` 的 `NormalOrderCancelTask` 接管说明（registration-and-contracts.md 定时任务节）

---

## 11. 路由与前后端

- Admin 路由组名常与 mode 相关，如 `/adminapi/seckill/...`。  
- 用户端：活动列表、详情、开团/参团 API；参数含 `activity_id` / `time_id` 等与 Handler 一致。  
- 前端 uni-app：优先用接口返回的活动价与按钮，核心下单页继续按 `trade_mode` 的既有数据渲染；新交互优先扩展 `page_config`。确需独立活动页时放 `pages-modules/<标识>/`，用法见 [uniapp-pages.md](../uniapp-pages.md)。

管理端 plugin 目录：`frontend/admin/src/pages/plugin/activity_{mode}/`。

---

## 12. 逐步落地

1. 定 **modeKey**（英文小写）、**plugin key**（`activity_*`）、是否与 normal 共用订单。  
2. 复制 **seckill** 骨架，改 Handler 类名与表结构。  
3. 设计活动表 + 参与/库存表，`Installer` 建表。  
4. 实现 Handler：`getType/getLabel/isMarketingActivity` → `getProductExist` → **`getActivityPrice` + `validateOrder`**。  
5. `App::register` 注册；`registry:check` 通过。  
6. 实现活动 CRUD 与用户端参与 API。  
7. 覆盖支付/取消/退款钩子（至少与库存/名额一致）。  
8. 过期 **crontab**（若活动有时间窗）。  
9. `event.php` 补监听（通知、异步）。  
10. 管理端页面 + DIY 链接 seed。  
11. 全链路验收。  
12. 打包 `.plugin-packages/`。

---

## 13. 全链路验收

### 13.1 静态

```bash
cd backend && php think plugin:manifest-check && php think registry:check
```

trade 专属：`registry:check` 应看到 modeKey 已注册；integral 还应看到 `integral_order` Adapter。

### 13.2 活动 + 订单

- [ ] 创建活动并绑定商品；列表/详情展示活动价  
- [ ] 未满足资格时 `validateOrder` 阻断（前端收到明确错误）  
- [ ] 下单金额与 `getActivityPrice` 一致；支付回调后活动库存一致  
- [ ] 取消/超时释放占用；退款后活动侧回滚  
- [ ] 商品列表活动标签仅出现在 `getMarketingKeys()` 包含的模式  
- [ ] 与 product `canCombineWith` 冲突时被拒绝  

### 13.3 生命周期

- [ ] 安装/启用/禁用/卸载（隔离库）；禁用后不能以该 mode 下单  

---

## 14. 打包与交付

只打包 `backend/modules/trade/{mode}/` 与对应 admin plugin 前端；zip 至 `.plugin-packages/`，文件名含 key 与 version。不改核心升级包。

---

## 15. 禁止事项

- 禁止在 `OrderCenterServices` 等中央 Service 写 mode 分支。  
- 禁止把纯发奖逻辑注册进 `TradeModeRegistry`（应用 marketing 事件）。  
- 禁止为「只是打折」虚构独立 `source_type`（除非 integral 类独立订单）。  
- 禁止 trade 模块 use 其他 trade/product 模块类；共享逻辑上收 crmeb 或事件。  
- 禁止校验失败仍返回成功或静默改价（安全与对账风险）。

---

## 16. 排错速查

| 现象 | 查 |
| --- | --- |
| 活动价未生效 | 下单参数是否带 `activity_id`；`getActivityPrice` 是否被调用 |
| 支付后活动库存未扣 | `onOrderPay` / `afterOrderPay`；事务是否回滚 |
| 所有商品都显示活动标 | 是否误用 Default 的 `getProductExist`；应用 `getMarketingKeys()` 过滤 |
| mode 未注册 | 插件未启用；`App::register` 未执行 |
| 旧接口 type=1 无效 | 实现 `getLegacyType()` 与 `getByLegacyType` 映射 |

---

## 17. 阅读延伸

- 注册中心行为：`backend/crmeb/trade/mode/TradeModeRegistry.php`  
- 内置 normal/member：`backend/app/AppService.php` boot 段  
- 订单如何取 Handler：在 `OrderCenterServices` / 下单 Service 中搜 `TradeModeRegistry`
