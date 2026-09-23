# dynamic-pages — 动态页面渲染引擎层

## 设计目标

将前端渲染逻辑与后端 OrderCenter 的 **订单类型扩展机制** 解耦。后端新增订单类型或活动类型时，前端**无需改动现有代码**，只需在映射表中追加一条记录，或按约定新增基元组件即可。

## 架构分层

```
dynamic-pages/
├── config/        ← 配置映射层（产品类型、卡片样式、状态文案、操作映射）
├── engines/       ← 引擎层（业务场景渲染编排，每个场景一个引擎）
└── components/    ← 组件层（表单 / 布局 / 订单 / 选择器）
```

数据流向：引擎从 API 获取数据 → 通过配置映射表选择卡片组件或基元组件 → 逐段渲染页面。

---

## 各目录说明

### api/ — 路由与操作配置

统一管理页面路由常量与订单操作处理器。可扩展的业务枚举（产品类型标签、状态文案、卡片样式等）由后端 `type_info` / 接口统一下发，前端不维护映射表。

| 文件 | 作用 | 扩展方式 |
|------|------|---------|
| `routes.js` | 页面路由常量表（统一管理所有跳转路径） | 追加路由记录 |
| `action-map.js` | 订单操作 action → 执行函数 | 追加 action → handler 映射 |

### engines/ — 页面渲染引擎

每个引擎对应一个独立业务页面，负责调用 API、编排基元组件、管理页面状态（加载/空态/错误）。

| 文件 | 对应页面 | 核心职责 |
|------|---------|---------|
| `OrderListEngine.vue` | 订单列表页 | 按订单状态分组 → 根据 `order_buttons` 渲染操作按钮 |
| `OrderDetailEngine.vue` | 订单详情页 | 渲染 `detail_components` + 履约卡片（物流/虚拟/卡密） |
| `OrderCreateEngine.vue` | 订单创建页 | 地址选择 → 商品确认 → 优惠/备注 → 提交创建 |

### components/ — 组件

最小的功能单元，按业务类型分组。每个组件职责单一、props 驱动，可被任意引擎复用。

| 分组 | 文件 | 用途 |
|------|------|------|
| **order/** | `FormBuilder.vue` | 通用表单构建器（基于后端 `custom_form` 渲染） |
| | `DeliveryTypeTabs.vue` | 配送方式切换（快递配送 / 到店自提） |
| | `OrderAddressCard.vue` | 收货地址卡片（含虚拟商品提示） |
| | `OrderProductCard.vue` | 订单确认页商品信息卡片 |
| | `OrderDiscountPanel.vue` | 优惠信息面板（优惠券 + 积分抵扣） |
| | `OrderParamsList.vue` | 商品参数列表 |
| | `OrderCustomForm.vue` | 订单自定义表单（必填信息字段） |
| | `OrderRemarkInput.vue` | 订单备注输入 |
| | `OrderInvoicePanel.vue` | 发票信息面板（不开发票/个人/企业） |
| | `OrderPriceDetail.vue` | 价格明细（总价/运费/优惠券/积分） |
| | `OrderSubmitBar.vue` | 底部提交栏（合计金额 + 确认下单） |
| | `FulfillmentCard.vue` | 履约信息卡（通用数据驱动，支持物流/网盘/卡密/酒店等） |
| | `OrderStepsBar.vue` | 订单步骤条 |
| | `OrderTimeline.vue` | 订单状态时间线 |
| | `StatusBadge.vue` | 订单状态徽章（颜色映射） |

---

## 扩展指南

### 新增基元组件

1. 在 `primitives/` 对应分组下创建 Vue 组件
2. 引擎中导入并添加到 `COMPONENT_MAP` / `detail_components` 即可

### 新增订单操作

在 `api/action-map.js` 追加 action → handler 映射。
