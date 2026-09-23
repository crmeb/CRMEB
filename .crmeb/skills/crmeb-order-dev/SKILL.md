---
name: crmeb-order-dev
description: CRMEB v8 订单开发手册。围绕订单中台详细讲解交易模式、产品类型与订单全生命周期（创建、支付、履约、收货、评价、完成、取消、售后退款）的开发流程、代码入口与扩展点。开发或修改交易模式（秒杀/拼团/砍价/积分兑换）、产品类型订单链路、订单事件监听、订单状态流转、售后退款时使用。触发词：订单开发、交易模式、订单生命周期、订单状态、下单支付、发货收货、售后退款。
---

# CRMEB 订单开发

本技能是订单域开发手册：讲清楚「一张订单从哪里来、经历什么、由谁处理、在哪里扩展」。强制条款（架构、状态码定义、注册机制约束）以规则为准，本文只写操作流程、代码定位与最佳实践。

## 必读规则

| 规则 | 内容 | 何时读 |
| --- | --- | --- |
| [订单中台规范](../../rules/订单中台规范.md) | 中台架构、OrderAdapter 适配器、统一状态码、注册机制（强制条款） | 任何订单开发前 |
| [事件规范](../../rules/事件规范.md) | 事件字符串 key 调用、SafeEvent 分级隔离、订单事件全量清单 | 新增/修改订单事件时 |
| [架构规范](../../rules/架构规范.md) | 三层依赖铁律 `modules → app → crmeb → driver` | 任何后端改动前 |

## 订单体系全景：一张订单的三个维度

一张订单由三个相互独立的维度共同描述，各自由一套注册器管理：

```
   产品类型（卖什么）              交易模式（怎么卖）
   ProductTypeRegistry            TradeModeRegistry
   normal    实物商品              normal       普通购买（app 内置）
   virtual   虚拟商品              member       会员购买（app 内置）
   cardkey   卡密商品              seckill      秒杀
                                   bargain      砍价
                                   combination  拼团
                                   integral     积分兑换
              │                        │
              └───────────┬────────────┘
                          ▼
              一张订单（eb_order_center）
        order_no + product_type + activity_type + source_type
                          │
                          ▼
        订单中台（统一状态 / 聚合查询 / 售后规则）
        OrderTypeRegistry → OrderAdapter（状态翻译）
        AfterSaleRuleRegistry → 售后规则（product_type × activity_type）
```

三个维度各自的职责边界：

- **产品类型**决定：订单写到哪张源表（`pt_{type}_order`）、如何扣/还库存、如何履约（发货/发卡/出票/核销）、售后规则。见 [产品类型与订单](references/product-type.md)。
- **交易模式**决定：价格如何计算（活动价/积分价）、下单前后校验（限购/资格）、支付方式限制（含 0 元/积分支付）、下单与支付的后置回调（如拼团建团）。见 [交易模式](references/trade-mode.md)。
- **订单中台**负责：统一状态码、聚合列表、详情入口、售后规则解析；不持有任何业务逻辑，业务全部在模块内。

## 订单生命周期地图

```
 创建 ───► 支付 ───► 履约/发货 ───► 收货 ───► 评价 ───► 完成
  │          │            │                        ▲
  │          │            └─ 安装类商品：待安装 ───┘
  ├─ 取消（用户主动 / 超时自动，回滚库存）
  └────────── 售后：申请 → 审核 → 退款 ──► 已退款（任意已支付节点可进入）
```

各阶段的入口代码、事件与扩展点见分篇文档：

| 文档 | 内容 |
| --- | --- |
| [order-lifecycle.md](references/order-lifecycle.md) | 全周期总览：状态机、`advance()` 推进机制、订单事件地图 |
| [order-create-pay.md](references/order-create-pay.md) | 创建与支付：confirm/create 双接口、防重与事务、paySuccess 链路 |
| [order-flow.md](references/order-flow.md) | 履约流转：发货、收货、评价、完成、取消双路径、定时任务 |
| [order-aftersale.md](references/order-aftersale.md) | 售后与退款：规则双维度解析、申请/审核/退款执行、幂等 |

## 按任务路由

| 任务 | 读什么 |
| --- | --- |
| 新增/修改交易模式（秒杀、拼团类玩法） | [trade-mode.md](references/trade-mode.md) |
| 新增产品类型并打通订单链路 | [product-type.md](references/product-type.md) + [创建产品类型完整教程](../crmeb-dev/references/03-系统商品说明/product-type.md) |
| 理解/扩展订单状态流转 | [order-lifecycle.md](references/order-lifecycle.md) + [订单中台规范](../../rules/订单中台规范.md) |
| 修改下单/支付流程 | [order-create-pay.md](references/order-create-pay.md) |
| 发货/收货/评价/取消逻辑 | [order-flow.md](references/order-flow.md) |
| 售后规则、退款流程 | [order-aftersale.md](references/order-aftersale.md) |
| 用户端订单页面对接（uni-app） | [crmeb-dev 用户端订单文档](../crmeb-dev/SKILL.md#用户端业务流程) |
| 从 0 创建插件/模块骨架 | [crmeb-plugin-dev](../crmeb-plugin-dev/SKILL.md) |

## 代码定位速查

路径相对 `backend/`：

| 职责 | 位置 |
| --- | --- |
| 注册器契约（交易模式/产品类型/订单类型/售后规则） | `crmeb/trade/`（子目录 `mode/`、`product/`、`order/`） |
| 下单接口（confirm/create/take/comment/applyRefund） | `app/api/controller/v1/order/CenterOrderController.php` |
| 中台服务（列表/详情/状态同步/createRecord） | `app/services/order/OrderCenterServices.php`、`OrderCenterUserServices.php` |
| 支付成功处理 | `app/services/order/OrderSuccessServices.php` |
| 发货/收货服务 | `app/services/order/OrderDeliveryServices.php`、`OrderTakeServices.php` |
| 售后与退款服务 | `app/services/order/AfterSaleServices.php`、`RefundOrderServices.php`、`RefundEffectServices.php` |
| 生命周期公共逻辑（取消回滚、收货奖励等） | `app/services/order/OrderLifecycleServices.php` |
| 计价服务 | `app/services/order/OrderComputedServices.php` |
| 产品类型实现 | `modules/product/{normal,virtual,cardkey}/` |
| 交易模式实现 | `modules/trade/{seckill,combination,bargain,integral}/` |
| 超时取消 | `app/jobs/UnpaidOrderCancelJob.php` + 各产品类型 `crontab/` |
