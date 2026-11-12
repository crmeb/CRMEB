# 业务模块分册 · 营销玩法（marketing）

核对日期：2026-11-06（按 `backend/modules/marketing/` 现役代码核对）。代码与本文冲突时以代码为准。

**先读**：[00-总览-类型路由与边界](00-总览-类型路由与边界.md) 第 4 节；领域判定：[domain-selection.md](../domain-selection.md)；事件与代理：[registration-and-contracts.md](../registration-and-contracts.md)。

强制红线：[插件开发规范](../../../../rules/插件开发规范.md)、[事件规范](../../../../rules/事件规范.md)。

---

## 1. 领域判定：口诀与反例

### 1.1 口诀

**观察用户/订单行为发放奖励**，或运行**不改变成交契约**的独立运营活动 → `backend/modules/marketing/{name}/`。

核心特征：卸载或禁用后，**商品仍可按原价下单、支付、履约**；只是不再累计抽奖次数、不再发新人券、不再签到送积分。

### 1.2 应归入 marketing

| 场景 | 现役模块 | 触发方式 |
| --- | --- | --- |
| 抽奖次数、开奖 | `marketing/lottery` | `OrderPayLottery`、`OrderComment` 等 |
| 签到奖励 | `marketing/signin` | 用户行为 API + 定时提醒 |
| 新人注册礼 | `marketing/newuser` | `UserRegister` |
| 充值活动（充值单语义若独立需单独设计） | `marketing/recharge` | 充值成功事件 |
| 渠道码统计 | `marketing/channel_code` | 扫码/绑定事件 |

### 1.3 反例

| 误判 | 正确落位 |
| --- | --- |
| 秒杀价、拼团价必须在下单时生效 | `trade` + `TradeModeRegistry` |
| 卖新形态商品（卡密、课程） | `product` |
| 微信小程序登录 | `channel` |
| 分销佣金快照 | `distribution` |

### 1.4 与 trade 的硬分界

若移除模块后，用户仍能以「活动价」完成支付，说明成交价逻辑还留在 core 或 product，**不是** marketing 问题，应把计价迁回 trade Handler。

---

## 2. 现役模块对照

| 目录 | plugin key | type | App::register | 复杂度 |
| --- | --- | --- | --- | --- |
| `marketing/lottery` | `activity_lottery` | `marketing` | 空 | 高（后台+事件+通知） |
| `marketing/signin` | `user_signin` | `marketing` | CrontabTaskRegistry | 中 |
| `marketing/newuser` | `marketing_newuser` | `marketing` | 空 | 低（单监听） |
| `marketing/recharge` | `user_recharge` | `marketing` | 视实现 | 中 |
| `marketing/channel_code` | `user_channel_code` | `marketing` | 视实现 | 中 |

说明：现役 key 命名不统一（`activity_*` / `user_*` / `marketing_*`）。**新模块建议** `marketing_{name}` 或对齐业务已有菜单前缀，但五处 key 必须自洽。

---

## 3. 推荐参考

| 目标 | 参考 |
| --- | --- |
| 完整后台 + 多事件 + 服务代理 | `marketing/lottery` |
| 最小事件插件 | `marketing/newuser`（仅 `UserRegister`） |
| 定时任务 + seed crontab | `marketing/signin` |

---

## 4. 目录树

```text
backend/modules/marketing/{name}/
├── plugin.json                 # type=marketing
├── App.php                     # register 多为空或仅 CrontabTaskRegistry
├── Installer.php
├── event.php                   # ★ 核心：listen 映射
├── listener/                   # ★ 核心：*Listener.php
├── controller/admin|user/      # 有运营后台时
├── route/
├── service/ dao/ model/ validate/
├── jobs/                       # 异步发奖、批量通知
├── crontab/                    # 签到提醒等
└── schema/                     # 少数模块放 SQL 片段（按需）

frontend/admin/src/pages/plugin/{key}/
```

**没有**统一的 `MarketingRegistry`；不要臆造 Handler 接口，以**事件订阅 + 自有表**为主。

---

## 5. plugin.json

```json
{
    "key": "marketing_example",
    "name": "示例营销",
    "description": "不改变订单成交契约的奖励/活动",
    "version": "1.0.0",
    "author": "作者",
    "type": "marketing",
    "path": "modules/marketing/example",
    "require": { "crmeb": "^8.0", "php": "^8.0" }
}
```

---

## 6. App.php

### 6.1 典型（仅事件）

`marketing/lottery/App.php`：`register()` 为空，仅 `boot()` 加载 `event.php`。

### 6.2 含定时任务

`marketing/signin/App.php`：

```php
public static function register(ThinkApp $app): void
{
    $app->make(CrontabTaskRegistry::class)->register('signRemind', SignRemindTask::class);
}
```

任务类实现 `CrontabTaskInterface`：`getMark()`、`getName()`、`getDescription()`、`handle()`（幂等）。

---

## 7. event.php 契约

格式（ThinkPHP）：

```php
return [
    'listen' => [
        'OrderPaySuccess' => [
            \modules\marketing\example\listener\OrderPaidListener::class,
        ],
    ],
];
```

### 7.1 常用触发事件（节选，完整见事件规范）

| 事件名 | 典型用途 |
| --- | --- |
| `UserRegister` | 新人礼（newuser） |
| `OrderPaySuccess` / `OrderPayLottery` | 支付后发奖（lottery 用专用名） |
| `OrderComment` | 评价后抽奖次数 |
| `MarketingServiceProxy` | app 层方法代理，避免 use modules |

### 7.2 lottery 真源示例

`marketing/lottery/event.php` 注册：

- `OrderPayLottery`、`OrderComment`、`OrderCommentLotteryCheck`  
- `MarketingServiceProxy`  
- `Lottery`（通知链）

新增营销能力时，**优先复用已有事件名**；若需新事件，须在 `app` 层合法触发点 `event('YourEvent', $payload)` —— 这通常属于核心改动，插件二开应尽量避免；改用已有钩子或 Proxy 模式。

### 7.3 监听器编写纪律

- 幂等：同一订单/同一用户同一活动只发一次（唯一键/状态机）。  
- 失败：除强制校验外，捕获异常打日志，**不要**让无关 API 整体失败。  
- 扩展共享数组：用命名空间 key（如 `extensions.marketing_lottery`），勿覆盖核心字段。  
- 代理监听：参考 `MarketingServiceProxyListener`，白名单方法 + 稳定请求/响应结构。

---

## 8. Service 代理模式（与 app 协作）

app 层不得 `use modules\marketing\*`。需要读抽奖次数、签到状态时：

1. app Service 内 `event('MarketingServiceProxy', $request)` 或专用 Query 事件。  
2. marketing 模块 `listener` 识别 `method` / `action` 白名单，返回数据。  
3. 未安装插件时 app 得到空/default，流程继续。

distribution、channel 有同构的 `DistributionServiceProxy`、`ChannelServiceProxy`。

---

## 9. Installer

- **自有表**：奖励记录、活动配置、库存奖品等，`eb_{业务}_*` 或模块约定前缀。  
- **菜单**：营销父级 `parent_key: core:admin-marketing`（与 lottery 对齐）。  
- **seed**：字典、通知模板（若模块内定义 `getNoticeDefinitions` 类——视现役模块而定）、`system_crontab`。  
- **cleanData**：删本插件菜单、链接分类、crontab mark、自有配置行。

充值类（recharge）可能涉及支付 attach 路由，仍不得改 `PayServices` 核心分支，通过已有充值流程 + 事件扩展。

---

## 10. 路由与 Controller

- 运营配置、记录查询、手动补发：Admin API。  
- C 端：抽奖、签到、领取 — User API。  
- 中间件、响应体规范同 00 总览；权限 `auth_type=2` 与路由一致。

---

## 11. 前端契约

- 管理端：plugin 页面配置规则与记录。  
- 移动端：新独立页放 `pages-modules/`，放入后的跳转与装修见 [uniapp-pages.md](../uniapp-pages.md)。弹窗/浮层优先由 API 返回开关与次数。  
- DIY：`diy.components` 声明组件名；管理端组件 `pluginComponent: true`。

---

## 12. 逐步落地

1. 明确**触发点**（注册/支付/评价）与**奖励物**（积分、券、次数）。  
2. 确认不属于 trade 计价 → 读 domain-selection 再动手。  
3. 复制 **newuser** 或 **lottery** 骨架。  
4. 设计自有表 + 幂等键（`uid+order_id+activity_id` 等）。  
5. 写 `event.php` + `listener`（先实现主路径）。  
6. `Installer` 菜单 + migrate。  
7. 若需定时：`crontab` + Registry + seed。  
8. 若 app 要查询：加 Proxy listener，文档化 request 结构。  
9. Admin/User 路由与页面。  
10. 通知（可选）：监听模块内 Services 触发的 `Lottery` 类事件或 Notice 种子。  
11. 验收：禁用插件后核心下单不受影响。  
12. 打包。

---

## 13. 验收清单

### 13.1 静态

```bash
cd backend && php think plugin:manifest-check && php think event:check
```

### 13.2 行为

- [ ] 触发事件后奖励正确入账（表记录 + 用户可见）  
- [ ] 重复触发不重复发（幂等）  
- [ ] 并发/重试安全（数据库唯一约束或 CAS）  
- [ ] 禁用插件：触发点不 500；app 代理返回空  
- [ ] 卸载：自有表/菜单/定时任务清理（隔离库）  

### 13.3 边界

- [ ] 未把活动价写进 marketing listener  
- [ ] 未 use 其他 marketing/trade 模块  
- [ ] 无 `app/`、`crmeb/` diff  

---

## 14. 打包

压缩 `modules/marketing/{name}/` + admin plugin 前端 → `.plugin-packages/`。详见插件开发规范第七节。

---

## 15. 禁止事项

- 禁止用 marketing 伪装 trade（在 listener 里改 order_item 价格）。  
- 禁止监听器抛未捕获异常导致支付回调失败（除非产品明确要求强一致且用户同意改 core）。  
- 禁止直接写 `system_config` 存活动规则（用自有表 + Installer seed）。  
- 禁止跨模块 use；需要能力走事件/Proxy。  

---

## 16. 排错

| 现象 | 查 |
| --- | --- |
| 从不触发 | 事件名拼写；插件是否启用；触发方是否 `event()` |
| 重复发奖 | 幂等键；事务边界 |
| 后台看不到菜单 | getMenuConfig；安装是否成功 |
| 定时未跑 | system_crontab 是否有 mark；is_open；CrontabTaskRegistry 是否 register |

---

## 17. 概念图

```text
app Services ──event('OrderPayLottery')──► marketing/lottery/listener
                      │
                      └──► 写 lottery 次数表（不改 order_center.pay_price）

trade/seckill ──► 改成交价（validateOrder + getActivityPrice）
marketing     ──► 改用户权益（积分/券/次数），不改成交价
```
