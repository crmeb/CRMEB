# modules/ — 业务扩展层

## 层定位

`modules/` 是 CRMEB v8.0 架构的**业务扩展层**，位于 `app/` 之上，**可以调用 `app/` 和 `crmeb/` 的所有服务**。

本层存放所有可插拔的业务模块，按业务域分类组织。

## 目录结构

```
modules/
├── trade/         交易域 —— 订单管线扩展（全部实现 TradeModeHandlerInterface）
│   ├── combination/  拼团       activity_combination
│   ├── seckill/      秒杀       activity_seckill
│   ├── bargain/      砍价       activity_bargain
│   ├── integral/     积分商城   activity_integral
├── product/       商品域 —— 产品类型（全部实现 ProductTypeHandlerInterface）
│   ├── normal/       实物商品   product_normal
│   ├── virtual/      虚拟商品   product_virtual
│   └── cardkey/      卡密商品   product_cardkey
├── marketing/     运营域 —— 权益与奖励（纯事件观察者）
│   ├── signin/       签到       user_signin
│   ├── lottery/      抽奖       activity_lottery
│   ├── newuser/      新人专区   marketing_newuser
│   ├── channel_code/ 渠道码     user_channel_code
│   └── recharge/     充值       user_recharge（已与订单中台解耦，独立充值单）
├── agent/         分销代理模块
└── channel/       用户/端渠道扩展（APP/H5/小程序/公众号）
```

> 第三方驱动（支付/短信/上传等）不在 `modules/` 下，统一位于根目录 `driver/{category}/{name}/`，
> 由各 `app/services/driver/*/*DriverServices` 扫描发现，安装时按 plugin.json 的
> `path`（`driver/{category}/{name}`）落位。

`trade/`（怎么卖）× `product/`（卖什么）是两个**正交维度**，可自由组合
（同一商品既能拼团卖也能秒杀卖），各自有独立注册表：
`crmeb/trade/mode/TradeModeRegistry` 与 `crmeb/trade/product/ProductTypeRegistry`。

## 模块内部目录规范

每个模块（`{域}/{type}/`）内部的子目录命名与职责**全 modules 统一**，新增模块必须按下表搭建：

### 根文件（必备）

| 文件 | 职责 |
|------|------|
| `App.php` | 模块注册入口（`register()` 注册处理器，`boot()` 加载 `event.php`） |
| `Installer.php` | 安装/卸载/启用/禁用（建表/菜单/种子，幂等） |
| `plugin.json` | 插件元数据（`key`/`name`/`type`/`path`） |
| `event.php` | 事件监听器注册表 |
| `Handler.php` | 产品类型处理器（**仅 `product/` 域模块**，实现 `ProductTypeHandlerInterface`） |

### 子目录（按需，命名固定）

| 目录 | 命名 | 职责 | 备注 |
|------|------|------|------|
| `controller/` | — | 控制器，固定双端结构：`admin/` + `user/v1/` | |
| `route/` | — | 路由文件：`admin.php` / `user.php`（`manage.php` 可选），自动扫描加载 | |
| `service/` | — | 服务层（类名 `XxxServices`） | |
| `dao/` | — | 数据访问层（类名 `XxxDao`） | 纯事件观察者模块（如 `marketing/newuser`）可缺省 |
| `model/` | — | 模型层 | 同上可缺省 |
| `validate/` | — | 验证器 | 按需 |
| `listener/` | — | 事件监听器（类名 `XxxListener`，与 `event.php` 对应） | |
| `jobs/` | **复数** | 队列任务（继承 `BaseJobs`） | ⚠️ 单数 `job/` 已废弃（2026-08 统一迁移），禁止新建 |
| `handler/` | — | 域内处理器子目录：`trade/` 域放 `{Mode}ActivityHandler`；`product/` 域放 `{Type}OrderAdapter` + `{Type}AfterSaleHandler` | 根 `Handler.php` 为产品类型主处理器（仅 `product/` 域有） |
| `crontab/` | — | 定时任务（如拼团到期、分销员解绑） | 按需 |

### 特殊目录（已有模块使用，新模块按需参照）

| 目录 | 使用方 | 职责 |
|------|--------|------|
| `sdk/` + `notify/` | `channel/wechat_mp`、`channel/wechat_mini` | 第三方 SDK 封装 + 回调处理 |
| `pipeline/` | `product/cardkey`、`product/virtual` | 履约管线 |
| `frontend/dist` | `product/virtual` | 模块内前端构建产物 |

## 边界规则（新模块放哪？）

**一句话：改变一笔订单如何成交的 → `trade/`；决定卖什么形态商品的 → `product/`；
只发放权益、奖励行为、不改变成交的 → `marketing/`。**

"牵扯订单"要拆成三种性质再判断：

| 性质 | 含义 | 判别方法 | 归属 |
|------|------|---------|------|
| **改变成交** | 实现交易契约，订单管线主动调用它 | 实现 `TradeModeHandlerInterface`（定价/下单校验/支付前后/退款/完成等钩子） | `trade/` |
| **决定商品形态** | 决定 SKU 模型、履约方式（发货/发卡/核销）、售后规则 | 实现 `ProductTypeHandlerInterface` + `OrderTypeAdapter` | `product/` |
| **观察订单** | 监听订单事件发权益，移除后订单流程完全不受影响 | 只在 `event.php` 里 listen（如 `OrderPayLottery`） | `marketing/` |
| **自建迷你流程** | 奖品/权益发放挂在活动记录上，不创建订单 | 有自己的发放记录表，不写 `eb_order_center` | `marketing/` |

> 会员订单不在 `trade/`：已独立为 `app/` 层 `eb_member_order` 体系。

## 扩展点一览

| 扩展点 | 目录 | 解决什么 |
|--------|------|---------|
| 交易域 | `trade/` | 怎么卖：交易模式（定价策略、成团/限量规则、订单生命周期钩子） |
| 商品域 | `product/` | 卖什么：产品类型（履约形态、库存方式、售后规则） |
| 运营域 | `marketing/` | 送什么：权益与奖励（签到/抽奖/新人礼/渠道码） |
| 分销代理 | `agent/` | 分销员管理、分销等级、事业部、佣金结算 |
| 用户渠道 | `channel/` | 用户从哪来、如何识别登录、推送消息 |

## 依赖铁律

- `modules/` 内各模块之间**不可互相引用**
- 跨模块通信必须走 `crmeb/` 核心层契约或通过事件机制（`event()`）
- 每个目录都是独立插件包，包含 `plugin.json` + `Installer.php` + `App.php` + 标准 MVC 目录结构

## 自动发现机制

- 路由文件放入 `route/` 目录（`admin.php`/`user.php`/`manage.php`）后自动扫描加载
- 菜单生命周期由 `BaseModuleInstaller` 统一管理
- `plugin.json` 中的 `type` 字段决定插件分类（`mode`→trade、`product`/`product_type`→product、`marketing`→marketing）

> 历史参考：2026-08 由 `trade/mode/*` 拍平为 `trade/*`、`trade/product/*` 提升为 `product/*`，
> 模块 key 不变，plugin.json `path` 已同步。
