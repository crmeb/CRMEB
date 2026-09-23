# 交易模式开发（Trade Mode）

> 本文是 `crmeb-order-dev` 技能子文档，总览见上级 [../SKILL.md](../SKILL.md)。强制条款见 [订单中台规范](../../../rules/订单中台规范.md) 与 [架构规范](../../../rules/架构规范.md)。

## 一、交易模式是什么

交易模式解决「**怎么卖**」的问题：同一件商品可以通过普通购买、秒杀、拼团、砍价、积分兑换等不同玩法卖出。它不关心商品本身的字段与履约（那是产品类型的职责），只关心：

- 谁能买（资格、限购）
- 多少钱（活动价、积分价、0 元）
- 怎么付（允许的支付方式、免费支付）
- 交易前后做什么（建团、砍价校验、积分扣减与返还）

交易模式是旧「活动（Activity）」概念的更名：注册器 `TradeModeRegistry` 等同旧版 `ActivityRegistry`，Handler 契约等同旧版 `ActivityHandlerInterface`，仅命名空间迁移到 `crmeb\trade\mode\`。

## 二、注册器与 Handler 契约

### 2.1 TradeModeRegistry

文件：[backend/crmeb/trade/mode/TradeModeRegistry.php](../../../../backend/crmeb/trade/mode/TradeModeRegistry.php)

| 方法 | 说明 |
| --- | --- |
| `register(string $modeKey, string $handlerClass)` | 注册处理器（模块在 `App.php::register()` 中调用） |
| `get(string $modeKey)` | 获取处理器实例（带缓存）；**未注册时回退 `DefaultTradeModeHandler`，保证普通购买流程永远可用** |
| `getByLegacyType(int $type)` | 按旧版数字协议解析（0=普通、1=秒杀、2=砍价、3=拼团），购物车/下单旧接口使用 |
| `allInfo()` | 所有模式的 key + 中文标签（标签由各 Handler `getLabel()` 动态提供） |
| `getMarketingKeys()` | 所有营销类模式（`isMarketingActivity()=true`），商品列表「参与活动」标签等场景使用 |
| `checkProductActivities(array $productIds)` | 批量检查商品参与了哪些交易模式 |

调用方（下单、购物车、订单详情）都通过容器取单例：`app()->make(TradeModeRegistry::class)`。

### 2.2 TradeModeHandlerInterface 方法详解

接口：[backend/crmeb/trade/mode/TradeModeHandlerInterface.php](../../../../backend/crmeb/trade/mode/TradeModeHandlerInterface.php)
基类：[backend/crmeb/trade/mode/BaseTradeModeHandler.php](../../../../backend/crmeb/trade/mode/BaseTradeModeHandler.php)（所有钩子提供默认空实现，**子类只需覆盖关心的方法**；仅身份类方法为 abstract）。

**身份与发现（abstract，必须实现）**：

| 方法 | 作用 |
| --- | --- |
| `getType(): string` | 模式唯一标识，如 `seckill` |
| `getLabel(): string` | 中文标签，如 `秒杀`（注册器 `allInfo()`、订单列表角标展示用） |
| `getProductExist(array $productIds): array` | 批量判断商品是否参与本模式，返回 `[productId => bool]` |
| `isProductInActivity(int $productId): bool` | 单个商品是否参与 |
| `getProductActivity(int $productId): ?array` | 商品参与的活动信息（无则 null） |
| `getActivityIdsList(array $productIds = [], array $field = []): array` | 活动 ID 列表 |

**协议兼容（按需覆盖）**：

| 方法 | 作用 |
| --- | --- |
| `getLegacyType(): int` | 旧版数字 type（基类默认 0=普通）；参与购物车校验的营销模式必须覆盖 |
| `isMarketingActivity(): bool` | 是否营销类模式（秒杀/砍价/拼团/积分返回 true；normal/member 返回 false） |

**价格与校验（购物车/确认单阶段调用）**：

| 方法 | 作用 |
| --- | --- |
| `checkCartStock(...)` | 购物车/立即购买时的库存与限购校验（`StoreCartServices` 调用）；返回空数组表示不参与，调用方回退普通购买；返回 `[attrInfo, unique, productInfo, activityInfo]` |
| `getActivityPrice(...)` | 活动价。返回结构：`unit_price/total_price/currency(money|integral)/currency_amount/stock/limit`；返回空数组表示不覆盖价格 |
| `validateOrder(...)` | 订单规则校验（限购、库存、资格），不满足抛 `ApiException` |
| `getOrderExtraInfo(int $activityId)` | 写入订单 `extra_info` 的模式专属信息（如拼团 ID） |

**支付（0 元/积分兑换等特殊支付的关键）**：

| 方法 | 作用 |
| --- | --- |
| `getAllowedPayTypes(int $activityId)` | 允许的支付方式，空数组=不限制 |
| `isFreePay(int $activityId)` | 是否免真实货币支付（积分兑换、0 元砍价） |
| `getFreePayType(int $activityId)` | 免费支付标识（默认 `free`），前端据此走 orderPay 完成 0 元支付 |
| `getPayRedirectUrl(int $activityId)` | 免费支付完成后的跳转地址（可含 `{order_no}` 占位符），空=走默认收银台 |
| `beforeOrderPay / onOrderPay / afterOrderPay / onOrderPayFail` | 支付前校验、支付处理（如扣积分）、支付成功后（如推进拼团状态）、支付失败回滚 |

**订单生命周期钩子（在下单/取消/退款/完成链路中被调用）**：

| 方法 | 调用时机 | 典型用途 |
| --- | --- | --- |
| `beforeCreateOrder(...)` | confirm 与 create 都会调用 | 拼团是否可参与、砍价是否完成、预售是否开始 |
| `afterOrderCreate(orderNo, activityId, orderInfo)` | 订单事务提交后 | 拼团创建/加入团记录 |
| `beforeOrderCancel / afterOrderCancel` | 取消前校验 / 取消后 | 返还积分、回滚活动库存 |
| `beforeOrderRefund / afterOrderRefund` | 退款前校验 / 退款后 | 返还积分等 |
| `onOrderComplete(...)` | 订单完成后 | 活动结算 |

**展示扩展（订单详情页）**：

| 方法 | 作用 |
| --- | --- |
| `getOrderDetailExtend(...)` | 详情扩展数据（拼团团员列表、砍价进度、消耗积分） |
| `getActivityButtons(...)` | 模式专属按钮（邀请好友、继续砍价） |

> 钩子的触发方在 app 层（`CenterOrderController`、`OrderSuccessServices` 等），app 层通过 `TradeModeRegistry` 调用 Handler，**不直接 use 模块类**，符合三层依赖铁律。

## 三、已注册的交易模式

### 3.1 注册位置

| modeKey | 模式 | Handler | 注册位置 | legacyType | 营销类 |
| --- | --- | --- | --- | --- | --- |
| `normal` | 普通购买 | `DefaultTradeModeHandler` | [app/AppService.php](../../../../backend/app/AppService.php)（boot 中注册） | 0 | 否 |
| `member` | 会员购买 | `MemberTradeModeHandler` | [app/AppService.php](../../../../backend/app/AppService.php)（boot 中注册） | 0 | 否 |
| `seckill` | 秒杀 | `SeckillActivityHandler` | [modules/trade/seckill/App.php](../../../../backend/modules/trade/seckill/App.php) | 1 | 是 |
| `bargain` | 砍价 | `BargainActivityHandler` | [modules/trade/bargain/App.php](../../../../backend/modules/trade/bargain/App.php) | 2 | 是 |
| `combination` | 拼团 | `CombinationActivityHandler` | [modules/trade/combination/App.php](../../../../backend/modules/trade/combination/App.php) | 3 | 是 |
| `integral` | 积分兑换 | `IntegralActivityHandler` | [modules/trade/integral/App.php](../../../../backend/modules/trade/integral/App.php) | — | 是 |

注册写法（模块 `App.php::register()`）：

```php
$app->make(TradeModeRegistry::class)->register('seckill', SeckillActivityHandler::class);
```

### 3.2 各模式实现要点对照

| 模式 | 覆盖的核心钩子 | 特点 |
| --- | --- | --- |
| normal | 无（基类默认即普通购买） | 兜底模式；未注册的 modeKey 也回退到此 |
| member | 会员购买逻辑 | app 层实现（`app/services/user/member/`） |
| seckill | 身份类 + `checkCartStock` + 库存扣减 | 时段库存（`ActivitySeckillSku`），限时支付 |
| bargain | 身份类 + `checkCartStock` + 库存扣减 | 依赖砍价进度（`StoreBargainUser`），可 0 元 |
| combination | 身份类 + `getActivityPrice` + `beforeCreateOrder` + `afterOrderCreate` + `afterOrderPay` + 详情扩展/按钮 | 支付成功才建团/入团；团状态经 `OrderCenterBusinessStatusResolve` 事件解析，拼团中仍是中台 `processing` |
| integral | `getActivityPrice`（currency=integral）+ `isFreePay` + `getAllowedPayTypes` + 支付链路全套 + `afterOrderCancel/afterOrderRefund` | 免真实货币支付；积分扣减在 `onOrderPay`，失败回滚在 `onOrderPayFail`，取消/退款返还 |

> 拼团/砍价的订单侧状态（拼团中、待成团等）**不进中台 `status_code`**，通过 Adapter 的 `resolveBusinessStatus` 或 `OrderCenterBusinessStatusResolve` 事件作为业务状态 badge 展示，见 [订单中台规范](../../../rules/订单中台规范.md)。

### 3.3 交易模式与订单表的关系

与产品类型不同，**当前交易模式一般不建独立订单表**：秒杀/砍价/拼团订单复用底层产品类型的订单链路（`source_type` 仍为 `normal_order` 等），活动关系通过订单 `activity_type + activity_id` 与各自活动表（`eb_store_pink`、`eb_store_bargain_user` 等）关联。积分兑换是例外：它有独立订单表 `eb_store_integral_order` 和独立适配器（`source_type = integral_order`，大类 `points`）。

## 四、从 0 到 1 新增一个交易模式

以新增「预售 presale」为例（目录骨架的完整创建流程见 [crmeb-plugin-dev](../../crmeb-plugin-dev/SKILL.md)）：

1. **创建模块目录** `modules/trade/<mode>/`（相对 `backend/`），编写 `plugin.json`（type 建议 `trade_mode`）、`Installer.php`（活动表 + 活动 SKU 表迁移）。
2. **编写 Handler** 继承 `BaseTradeModeHandler`：
   - 必须：`getType()('presale')`、`getLabel()('预售')`、身份发现四件套（`getProductExist` 等）；
   - 按需：`getLegacyType()`（若需参与购物车旧协议则约定一个数字）、`isMarketingActivity(true)`；
   - 价格与校验：`getActivityPrice`（定金/尾款规则）、`validateOrder`、`checkCartStock`；
   - 生命周期：`beforeCreateOrder`（预售是否开始）、`afterOrderCreate`（记录预售单）、`onOrderPay`/`afterOrderPay`（定金转正式）。
3. **注册**：`App.php::register()` 中 `TradeModeRegistry::register('presale', ...)`。
4. **事件监听**（`event.php`）：如需在支付成功后推进活动状态，监听 `OrderPaySuccess` 并按 `activity_type` 过滤；活动自身状态机（预售开始/结束）用模块内事件或定时任务。
5. **前端与商品关联**：商品参与模式由 Handler 的身份发现方法自动生效（商品列表「参与活动」标签、购物车校验均经注册器动态发现，无需改动调用方）。

### 验证清单

- [ ] `php think` 下可用 `RegistryCheck` 命令核对注册中心（类合法性、normal 存在、Handler 声明的兼容模式已注册）
- [ ] 未传 activity_id 时回退普通购买正常下单
- [ ] `getActivityPrice` 返回的价格进入确认单与最终核价，`validateOrder` 限购生效
- [ ] 免支付模式（如适用）：`isFreePay` 命中后 `payType='free'`，0 元支付闭环
- [ ] 支付失败路径：`onOrderPay` 扣减的资源在 `onOrderPayFail`/`afterOrderCancel` 正确返还
- [ ] 订单详情能显示 `getOrderDetailExtend` 数据与 `getActivityButtons` 按钮
- [ ] 无跨模块 `use`；app 层仅通过注册器调用 Handler

## 五、参考文件

- 注册器：[backend/crmeb/trade/mode/TradeModeRegistry.php](../../../../backend/crmeb/trade/mode/TradeModeRegistry.php)
- 契约：[TradeModeHandlerInterface.php](../../../../backend/crmeb/trade/mode/TradeModeHandlerInterface.php)、基类 [BaseTradeModeHandler.php](../../../../backend/crmeb/trade/mode/BaseTradeModeHandler.php)、兜底 [DefaultTradeModeHandler.php](../../../../backend/crmeb/trade/mode/DefaultTradeModeHandler.php)
- 完整示例：拼团 [modules/trade/combination/](../../../../backend/modules/trade/combination/)（钩子覆盖最全）、积分 [modules/trade/integral/](../../../../backend/modules/trade/integral/)（免支付 + 独立订单表）
- 调用方参考：下单 [CenterOrderController.php](../../../../backend/app/api/controller/v1/order/CenterOrderController.php)、购物车校验 [StoreCartServices.php](../../../../backend/app/services/order/StoreCartServices.php)
- 注册中心体检命令：[app/command/RegistryCheck.php](../../../../backend/app/command/RegistryCheck.php)
