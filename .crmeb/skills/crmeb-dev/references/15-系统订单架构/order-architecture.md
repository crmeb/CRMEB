# 系统订单架构（订单中台）

> 本文是 `crmeb-dev` 技能的子文档，二开流程导航见上级 [../../SKILL.md](../../SKILL.md)。内容基于实际代码整理（`backend/crmeb/trade/order/`、`backend/app/services/order/`、`backend/app/model/order/`、两端控制器与路由、前端订单页面）。本文定位是**系统架构代码地图**：只给代码定位、入口与排查路径；机制讲解、统一状态码与强制约束以 [订单中台规范](../../../../rules/订单中台规范.md) 为唯一真源；各阶段的**开发流程、扩展点与最佳实践**见专项技能 [crmeb-order-dev](../../../crmeb-order-dev/SKILL.md)（见文末延伸阅读）。用户端订单页面操作教程见 [05-用户端开发](../05-用户端开发/)。

## 一、整体分层与调用链

```
用户端 uni-app                    管理端 admin
pages/center/order/*              pages/order/orderCenter/*
      │                                 │
      ▼                                 ▼
POST /api/center/order/*          GET /adminapi/order/center/*
api/route/center.php              adminapi/route/order.php
      │                                 │
      ▼                                 ▼
CenterOrderController             OrderCenterController
（api/controller/v1/order/）       （adminapi/controller/order/）
      │                                 │
      ▼                                 ▼
OrderCenterUserServices ──继承──▶ OrderCenterServices
      │                                 │
      ├─ 下单：ProductTypeRegistry 找 Handler
      │        → AbstractOrderCreatePipeline 子类管道
      │        → 写源订单表 + createRecord() 写 eb_order_center
      ├─ 列表：直查 eb_order_center，经 OrderCenterListExtend 事件注入扩展数据
      └─ 详情/状态：Adapter 实时重推导 + OrderCenterDetailExtend 事件
```

配套注册中心（均在 `crmeb/trade/` 核心层，模块在 `App.php::boot()` 注册）：

| 注册中心 | 位置 | 职责 |
| --- | --- | --- |
| `OrderTypeRegistry` | `backend/crmeb/trade/order/type/OrderTypeRegistry.php` | `sourceType → Adapter` 映射，状态推导入口 |
| `ProductTypeRegistry` | `backend/crmeb/trade/product/ProductTypeRegistry.php` | 产品类型 Handler 注册（下单履约路由） |
| `TradeModeRegistry` | `backend/crmeb/trade/mode/TradeModeRegistry.php` | 交易模式注册（下单确认页组合） |
| `AfterSaleRuleRegistry` | `backend/crmeb/trade/order/aftersale/AfterSaleRuleRegistry.php` | 售后处理规则注册 |

一张订单的三个维度（产品类型 × 交易模式 × 中台 Adapter）与已注册类型的完整清单（sourceType、源表、Adapter、注册位置），见 [订单中台规范](../../../../rules/订单中台规范.md)，本文不重复。

## 二、后端代码定位

### 核心层 `backend/crmeb/trade/order/`

| 文件 | 职责 |
| --- | --- |
| `type/OrderTypeAdapterInterface.php` | 适配器接口；状态/支付/退款**常量单一数据源**（`OrderCenter` 模型仅做常量委托） |
| `type/BaseOrderTypeAdapter.php` | 适配器基类，公共推导与按钮/进度条实现 |
| `type/OrderTypeRegistry.php` | 静态注册表：`register()` / `getAdapter()` / `resolveStatusCode()` / `getCategoryMap()` |
| `type/OrderCenterSyncInterface.php` | 状态同步契约，crmeb 层定义、app 层 `OrderCenterServices` 实现（依赖反转） |
| `type/Button.php`、`type/OrderFlowNode.php` | 状态按钮 / 生命周期进度条节点值对象 |
| `create/AbstractOrderCreatePipeline.php` | 下单管道抽象基类，持有 `BaseProductTypeHandler`，子类按产品类型定义 `defineSteps()` |
| `aftersale/AfterSaleHandlerInterface.php`、`BaseAfterSaleHandler.php` | 售后处理器接口与基类 |
| `aftersale/AfterSaleApplicationValidatorInterface.php`、`RefundEffectContextInterface.php` | 售后申请校验、退款生效上下文契约 |

### 应用层

| 位置 | 内容 |
| --- | --- |
| `backend/app/model/order/OrderCenter.php` | 中台模型（表 `eb_order_center`），状态常量委托给 `OrderTypeAdapterInterface` |
| `backend/app/services/order/OrderCenterServices.php` | 中台核心服务：`createRecord` / `syncStatus` / `updateRecord` / `syncFromSource` / `getDetail`（`loadSourceOrderInfo` + `loadOrderItems`）/ `getAdminList` / `getStatistics` / `updateRemark` / `delete` / `getUserList` |
| `backend/app/services/order/OrderCenterUserServices.php` | 用户端服务（继承上者）：`getList` / `getUserStatistics` / `cancelOrder` / `removeOrder` / `againOrder` / 下单缓存与 Redis 锁（`cacheOrderInfo`、`acquireOrderCacheLock`） |
| `backend/app/services/order/OfflineReceiptAdapter.php` | app 层适配器（线下收银记录，由监听器注册） |
| `backend/app/listener/system/OrderTypeRegisterListener.php` | app 层默认订单类型注册入口（`offline_receipt`） |
| `backend/app/services/order/` 其余 | 交易支撑服务：`AfterSaleServices`、`RefundOrderServices`、`RefundExecutorServices`、`RefundEffectServices`、`rule/AfterSaleRuleResolver`、`OrderTakeServices`（收货）、`OrderSuccessServices`、`OrderDeliveryServices`（发货）、`OrderWriteOffServices`（核销）、`OrderComputedServices`（金额计算）、`OrderLifecycleServices`、`StoreCartServices`、`OrderInvoiceServices` |

各产品类型 Adapter 落在 `backend/modules/product/{type}/handler/`，交易类在 `backend/modules/trade/{mode}/handler/`，均在各自 `App.php::boot()` 调用 `OrderTypeRegistry::register()`；完整对照表见 [订单中台规范](../../../../rules/订单中台规范.md)。

### 控制器与路由

| 端 | 控制器 | 路由文件 | 关键端点 |
| --- | --- | --- | --- |
| 管理端 | `backend/app/adminapi/controller/order/OrderCenterController.php` | `backend/app/adminapi/route/order.php`（「订单中台核心接口」段）、`backend/app/adminapi/route/center.php` | `order/center/list`、`stat`、`detail/:id`、`remark`、`delete`、`categories`、`filter_options` |
| 用户端 | `backend/app/api/controller/v1/order/CenterOrderController.php` | `backend/app/api/route/center.php`（`center/order/*`）、`backend/app/api/route/v1.php`（cancel/take/express/refund 等） | `center/order/list`、`stat`、`detail/:orderNo`、`confirm`、`computed`、`create`（统一下单） |

## 三、前端代码定位

| 端 | 文件 | 说明 |
| --- | --- | --- |
| 管理端列表页 | `frontend/admin/src/pages/order/orderCenter/index.vue` | 订单中台统一列表（搜索卡 + 数据卡） |
| 管理端详情页 | `frontend/admin/src/pages/order/orderCenter/detail.vue` | 详情 + 状态进度条 + 操作按钮（按钮/进度条由 Adapter `getOrderButtons`/`getProgress` 下发） |
| 管理端 API | `frontend/admin/src/api/orderCenter.js` | `/order/center/list|stat|detail|remark|delete|categories|filter_options` |
| 用户端统一订单页 | `frontend/uni-app/src/pages/center/order/`（`create/`、`detail/`、`aftersale/`） | 统一下单/详情/售后入口，操作教程见 [05-用户端开发](../05-用户端开发/) |
| 用户端 API | `frontend/uni-app/src/api/`（order 相关封装） | 对接 `center/order/*` 与 v1 订单操作端点 |
| 遗留页面 | `frontend/uni-app/src/pages/goods/order_list/`、`order_details/` 等 | 旧商品订单页面，新功能一律走 `pages/center/order/` |

## 四、事件与状态流转要点

- 业务动作（支付/发货/收货/退款/核销）更新源订单表后，调用 `OrderCenterServices::syncStatus(orderNo, statusCode, extra)` 同步中台；详情页永远以 Adapter 实时重推导结果为准，中台存储值仅是最近一次同步结果。
- 列表聚合扩展事件：`OrderCenterListExtend`（`getAdminList` 内触发）；详情扩展事件：`OrderCenterDetailExtend`（`getDetail` 内触发）；计价扩展事件：`OrderPricingExtend`。事件清单见 [事件规范](../../../../rules/事件规范.md)。

## 五、常见任务定位

| 任务 | 落点 |
| --- | --- |
| 新增订单类型 | 源表 + Model → 实现 `BaseOrderTypeAdapter` 子类 → 模块 `App.php::boot()` 注册 → 管理端筛选经 `categories`/`filter_options` 自动出现；强制边界见 [订单中台规范](../../../../rules/订单中台规范.md)，完整步骤见 [crmeb-order-dev/产品类型与订单](../../../crmeb-order-dev/references/product-type.md) |
| 列表数据不对 | `OrderCenterServices::getAdminList()` → 检查对应模块 `OrderCenterListExtend` 监听 |
| 详情状态与源表不一致 | 从 `source_type` 找到 Adapter → 看 `resolveStatusCode` 推导优先级 |
| 修改状态码/文案 | 只改 `OrderTypeAdapterInterface` 常量与 `STATUS_MAP`（模型是委托引用，禁止两处定义） |
| 新增操作按钮/进度条节点 | Adapter 的 `getStatusButtons` / `getOrderButtons` / `getProgress`（值对象 `Button`、`OrderFlowNode`） |
| 订单号生成 | `OrderCenterServices::getNewOrderId()`（充值订单改用 `crmeb\utils\SnowflakeIdGenerator`，见规范说明） |

## 六、延伸阅读（订单域开发手册）

| 主题 | 文档（crmeb-order-dev 技能） |
| --- | --- |
| 全周期状态机、`advance()` 推进、订单事件地图 | [order-lifecycle.md](../../../crmeb-order-dev/references/order-lifecycle.md) |
| 下单与支付（confirm/create、防重事务、paySuccess 链路） | [order-create-pay.md](../../../crmeb-order-dev/references/order-create-pay.md) |
| 履约流转（发货/收货/评价/完成/取消双路径） | [order-flow.md](../../../crmeb-order-dev/references/order-flow.md) |
| 售后与退款（规则解析、执行、幂等） | [order-aftersale.md](../../../crmeb-order-dev/references/order-aftersale.md) |
| 交易模式开发（秒杀/拼团/砍价/积分兑换） | [trade-mode.md](../../../crmeb-order-dev/references/trade-mode.md) |
| 产品类型订单链路 | [product-type.md](../../../crmeb-order-dev/references/product-type.md) |
