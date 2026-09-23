# 产品类型与订单（Product Type × Order）

> 本文是 `crmeb-order-dev` 技能子文档，总览见上级 [../SKILL.md](../SKILL.md)。从**订单视角**讲解产品类型如何接入订单链路；从 0 创建产品类型的完整教程见 [crmeb-dev/系统商品说明](../../crmeb-dev/references/03-系统商品说明/product-type.md)，本文不重复。

## 一、产品类型在订单链路中的角色

产品类型解决「**卖什么**」，落到订单上就是三个问题，分别由三个组件回答：

```
                    一张订单的生命周期
   ┌────────────────────────────────────────────────┐
   │ 谁的订单？→ ProductTypeHandler（产品类型处理器）  │
   │   getOrderSourceType / decStock / createOrderExtend │
   │   afterOrder / afterPay / fulfillOrder             │
   ├────────────────────────────────────────────────┤
   │ 状态怎么算？→ OrderAdapter（订单类型适配器）       │
   │   defineStatusTree / resolveStatusCode / advance   │
   ├────────────────────────────────────────────────┤
   │ 能不能退？→ AfterSaleHandler（售后规则处理器）     │
   │   getRule / afterApply / buildRefundPlan           │
   └────────────────────────────────────────────────┘
```

- **Handler**（注册在 `ProductTypeRegistry`，key 如 `normal`）：决定订单写到哪张源表、如何扣库存、何时履约。
- **OrderAdapter**（注册在 `OrderTypeRegistry`，key 如 `normal_order`）：决定扩展表状态如何翻译成中台统一状态码；契约与机制见 [订单中台规范](../../../rules/订单中台规范.md)。
- **AfterSaleHandler**（注册在 `AfterSaleRuleRegistry`，按 `product_type` 维度）：决定售后窗口与退款规则，见 [售后与退款](order-aftersale.md)。

三者必须在模块 `App.php` 中配套注册（Handler 在 `register()`，OrderAdapter 与售后规则在 `register()`/`boot()`），参考 [modules/product/normal/App.php](../../../../backend/modules/product/normal/App.php)。

## 二、已实现产品类型与订单对照

| 类型 | 模块 | source_type | 源订单表 | 履约方式 | 是否物流 |
| --- | --- | --- | --- | --- | --- |
| 实物 normal | [modules/product/normal](../../../../backend/modules/product/normal/) | `normal_order` | `eb_pt_normal_order` | 手动发货 → 物流 → 收货（可选安装） | 是 |
| 虚拟 virtual | [modules/product/virtual](../../../../backend/modules/product/virtual/) | `virtual_order` | `eb_pt_virtual_order` | 支付后自动发放网盘链接+提取码 | 否 |
| 卡密 cardkey | [modules/product/cardkey](../../../../backend/modules/product/cardkey/) | `cardkey_order` | `eb_pt_cardkey_order` | 支付后自动发卡，退款自动回收卡密 | 否 |

> `coupon`（卡券）类型规划中未实现。活动订单（秒杀/拼团/砍价）不建独立 source_type，复用底层产品类型的订单链路，活动维度经 `activity_type` 关联，见 [交易模式](trade-mode.md#3-3-交易模式与订单表的关系)。

## 三、订单链路上的产品类型扩展点

按生命周期阶段整理 Handler 上与订单相关的钩子（完整方法表见教程文档第四节）：

### 3.1 下单前

| 钩子 | 作用 | 调用方 |
| --- | --- | --- |
| `getSkuInfo(productId, skuUnique)` | 提供下单用 SKU（价格/库存/规格） | 确认单、计价 |
| `decStock / incStock` | 下单事务内锁库存 / 取消退还库存（abstract 必须实现） | create 事务、取消回滚 |
| `getShippingInfo` | 运费配置 | 计价 |
| `getDetailExtend` | 自定义表单等扩展字段（确认单展示、下单收集） | `CenterOrderController::confirm` |
| `getSelectionSpec` | 详情页选择契约（日期/场次等时段选择模型） | 商品详情 |
| `getCartStrategy` | 加购策略（direct/cart，控制购物车按钮显隐） | 购物车 |

### 3.2 下单事务内（顺序严格，见 [创建与支付](order-create-pay.md)）

| 钩子 | 作用 |
| --- | --- |
| `createOrderExtend(orderNo, cartInfo, context): int` | **事务内**写扩展订单表（如 `pt_cardkey_order`），返回记录 id 作为中台 `source_id` |
| `afterOrder(orderNo, cartInfo, context)` | 订单创建后幂等补建扩展表并回填中台 `source_type/source_id`（兼容自建 OrderCreateServices 的类型） |

### 3.3 支付与履约

| 钩子 | 作用 |
| --- | --- |
| `afterPay(orderNo, cartInfoList)` | 支付成功后自动履约（发卡/出票/发网盘链接）；由各类型自己的 `OrderPaidListener` 调用，需**幂等** |
| `fulfillOrder(...)` | 通用履约入口（接口方法） |
| （状态推进） | 履约动作经 `OrderAdapter::advance()` 写扩展表状态并同步中台，见 [订单生命周期](order-lifecycle.md) |

### 3.4 查询与展示（订单中台扩展点）

| 扩展点 | 用法 |
| --- | --- |
| `OrderAdapter::getOrderButtons/getStatusButtons/getProgress` | 详情页按钮与进度条（状态树驱动） |
| `OrderAdapter::resolveBusinessStatus` | 业务状态 badge（如「待安装」），不改变 `status_code` |
| `OrderAdapter::resolveExtensionFilter`（静态约定方法） | 管理端按扩展状态筛选时反查 order_no 列表 |
| `OrderCenterListExtend` 事件 | 向订单列表注入扩展字段（listener 直接改 `$event['list']` 引用） |
| `OrderCenterDetailExtend` 事件 | 向详情注入扩展数据 + 自声明渲染 section（`extend` + `extend_sections`，中台不硬编码插件 section） |

监听器示例：[modules/product/normal/listener/OrderCenterDetailExtendListener.php](../../../../backend/modules/product/normal/listener/OrderCenterDetailExtendListener.php)、卡密详情注入发卡数据 [modules/product/cardkey/listener/OrderCenterDetailExtendListener.php](../../../../backend/modules/product/cardkey/listener/OrderCenterDetailExtendListener.php)。

## 四、新产品类型接入订单链路最小清单

完整教程、目录结构与代码模板见 [创建产品类型](../../crmeb-dev/references/03-系统商品说明/product-type.md)，此处只列订单链路相关项：

```
□ getOrderSourceType() 与 OrderAdapter::getSourceType() 一致且全局唯一
□ decStock / incStock 实现（下单事务与取消回滚依赖）
□ createOrderExtend 返回扩展表记录 id；afterOrder 幂等并回填 source_type/source_id
□ afterPay 幂等（已终态直接 return；时间字段用 time()）
□ OrderAdapter::defineStatusTree 覆盖完整状态流转；resolveStatusCode 能从扩展表推导中台状态码
□ 按需监听 OrderPaySuccess / OrderCancel / OrderRefundSuccess（event.php，字符串 key）
□ AfterSaleRuleRegistry 按 product_type 维度注册售后 Handler（不注册则默认拒绝售后）
□ 验证：下单后 eb_order_center.source_type/source_id 正确；支付→履约→完成全链路状态推进
```

## 五、参考文件

- Handler 契约：[crmeb/trade/product/ProductTypeHandlerInterface.php](../../../../backend/crmeb/trade/product/ProductTypeHandlerInterface.php)、基类 [BaseProductTypeHandler.php](../../../../backend/crmeb/trade/product/BaseProductTypeHandler.php)
- OrderAdapter 契约：[crmeb/trade/order/type/OrderTypeAdapterInterface.php](../../../../backend/crmeb/trade/order/type/OrderTypeAdapterInterface.php)、基类 [BaseOrderTypeAdapter.php](../../../../backend/crmeb/trade/order/type/BaseOrderTypeAdapter.php)
- 标准实现：实物 [modules/product/normal/](../../../../backend/modules/product/normal/)（全流程最全）、卡密 [modules/product/cardkey/](../../../../backend/modules/product/cardkey/)（自动履约 + 退款回收）
- 完整创建教程：[crmeb-dev/03-系统商品说明](../../crmeb-dev/references/03-系统商品说明/product-type.md)
