# 业务模块分册 · 分销（distribution）

核对日期：2026-11-06（按 `backend/modules/distribution/` 现役代码核对）。代码与本文冲突时以代码为准。

**先读**：[00-总览-类型路由与边界](00-总览-类型路由与边界.md) 第 4 节；领域判定：[domain-selection.md](../domain-selection.md)（扁平领域 `modules/distribution`）；事件与代理：[registration-and-contracts.md](../registration-and-contracts.md)。

强制红线：[插件开发规范](../../../../rules/插件开发规范.md)、[安全红线](../../../../rules/安全红线.md)（佣金/提现权限）。

---

## 1. 领域判定：口诀与反例

### 1.1 口诀

**分销关系、等级、事业部、佣金快照、结算与提现业务** → 单一扁平模块 `backend/modules/distribution/`（不是 `modules/distribution/xxx/` 多插件）。

与 marketing 区别：分销改变的是**订单/用户上的佣金分配与结算状态**，有强对账要求；不是单纯的「送一张券」。

### 1.2 应在本模块内扩展

- 绑粉、上下级关系变更  
- 订单支付后写佣金快照、分账比例  
- 佣金冻结/解冻、退款回滚  
- 提现申请、审核、打款触发（打款协议在 pay 驱动）  
- 用户/订单列表展示分销字段（Extend 事件）

### 1.3 反例

| 误判 | 正确落位 |
| --- | --- |
| 普通满减券活动 | `marketing` |
| 微信商户转账 API | `driver/pay`（merchantPay 能力） |
| 商品形态/库存 | `product` |
| 秒杀活动价 | `trade` |

### 1.4 扩展方式

- **优先**在现有 `distribution` 模块内加 Service/Dao/Listener。  
- 若必须独立插件化能力，仍应保持**单一分销域**，通过事件对外；不要另建 `modules/marketing/xxx` 写佣金逻辑。

---

## 2. 模块身份

| 项 | 值（真源 plugin.json） |
| --- | --- |
| path | `modules/distribution` |
| key | `distribution` |
| type | `distribution`（非 `service`） |
| 命名空间 | `modules\distribution\...` |

这是**大型扁平模块**参考范例（domain-selection：扁平领域参考 agent/distribution）。

---

## 3. 目录树（现役）

```text
backend/modules/distribution/
├── plugin.json
├── App.php                     # boot 加载 event；register 当前无 Registry
├── Installer.php               # 菜单、表、种子
├── event.php                   # ★ 分销事件网
├── listener/                   # 支付、快照、Extend、Proxy、通知
├── controller/admin|user/
├── route/admin.php | user.php
├── service/ dao/ model/
├── jobs/                       # 异步结算、注册后任务
├── crontab/                    # 解绑、结算类任务（若有）
└── validate/

frontend/admin/src/pages/plugin/distribution/
```

---

## 4. plugin.json

```json
{
    "key": "distribution",
    "name": "分销代理",
    "type": "distribution",
    "path": "modules/distribution",
    "version": "1.0.0"
}
```

扩展子功能**不要**新建第二个 plugin.json；用版本迭代与 `Installer` migrate。

---

## 5. App.php

真源：`register()` 仅注释说明事件在 `event.php`；`boot()` 标准 `loadEvent`。

若新增定时任务（如自动解绑）：

```php
$app->make(CrontabTaskRegistry::class)->register('distributionUnbind', XxxTask::class);
```

并在 Installer `seed()` 写 `system_crontab`，`cleanData()` 按 mark 删除。

---

## 6. event.php 契约（现役监听一览）

真源 `backend/modules/distribution/event.php`（节选说明）：

| 事件 | Listener 职责 |
| --- | --- |
| `OrderPaySuccess` | 支付后佣金计算/入账 |
| `OrderCreateAfter` | 下单快照（关系、比例） |
| `OrderCenterDetailExtend` | 订单详情分销字段 |
| `UserListExtend` / `UserDetailExtend` | 用户列表/详情扩展 |
| `UserPromoterCancel` | 取消推广员 |
| `OrderSpreadBrokerage` | Spread 佣金查询/计算 |
| `OrderDivisionPercent` / `OrderDivisionInfoQuery` | 事业部/分账比例 |
| `UserRegisterDistributionJob` | 注册后异步分销任务 |
| `UserExtract` / `UserExtractApply` | 提现通知 |
| `UserBrokerageChange` | 佣金变更通知 |
| `BrokerageBindingStatusChange` | 绑粉状态变更 |
| `DistributionUnbindCrontab` | 定时解绑逻辑入口 |
| `DistributionServiceProxy` | **app 层白名单代理**，避免 use modules |

新增分销规则时：

1. 优先挂现有事件（如退款事件搜 `OrderRefund` 相关触发点）。  
2. 需要 app 数据时走 Proxy，**禁止** app use distribution 类。  
3. Extend 事件只追加命名空间字段，不覆盖核心 list 项。

---

## 7. 核心业务纪律

### 7.1 快照与幂等

- 订单创建/支付：把当时的关系、等级、比例写入**快照表**（自有表），避免事后改等级影响历史订单。  
- 支付监听：同一 `order_no` 佣金入账幂等。  
- 退款：按快照回滚，禁止简单 delete 无审计。

### 7.2 结算 vs 打款

| 阶段 | 位置 |
| --- | --- |
| 佣金冻结、可提现已解冻、审核 | distribution Services |
| 微信/支付宝「付款到零钱」 | pay 驱动 `merchantPay` + PayServices 分支（可能需核心配合，见 pay 分册） |

不要在 distribution 里直接 curl 支付；走已有 Pay 门面。

### 7.3 与 product/trade

- 商品是否参与分销：读 `product_center.is_sub` 等中台字段，由 product Handler/中台维护，分销模块读快照。  
- 活动订单：commission 基数 usually 实付；活动价由 trade 已写入订单项，分销 listener 读 order_item 快照。

---

## 8. Installer

- 分销等级、规则、菜单、默认配置：自有表 + seed。  
- 菜单挂核心分销父级（与现役 Installer `getMenuConfig` 对齐）。  
- 卸载：清理佣金配置菜单、自有扩展表（**注意**：生产卸载通常禁止，仅隔离库验证）。

---

## 9. 路由与 Controller

- Admin：分销员、等级、事业部、佣金明细、提现审核。  
- User：推广中心、佣金、提现申请、海报/链接。  
- 权限：敏感操作 `v-auth` + 后端角色校验；提现审核防 IDOR（仅可操作本人或授权范围）。

---

## 10. 前端契约

- 管理端：`pages/plugin/distribution/` UMD 扩展。  
- 移动端：推广海报、佣金页；扩展数据走 API，不 fork 核心「我的」页类型分支。  
- 订单/用户详情扩展：后端 Extend 事件注入，前端读 `extensions.distribution_*`。

---

## 11. 逐步扩展（在 distribution 内）

1. 读清 `event.php` 现有网，避免重复监听同一事件做冲突逻辑。  
2. 新规则需要的新表：`Installer::runMigrate()` 幂等。  
3. 业务写在 `service/`，listener 薄（解析 payload → 调 Service）。  
4. 若 app 需调用：扩展 `DistributionServiceProxyListener` 白名单。  
5. 列表/详情：Extend listener 只追加字段。  
6. 通知：复用 `UserBrokerageChange` 等或新增模块内触发 + Notice 种子。  
7. 退款/售后：找订单退款成功触发点挂回滚 listener。  
8. 对账脚本/导出：Admin API，禁止裸 SQL 在 Controller。  
9. 验收 + 打包整个 `modules/distribution`（通常与客户定制一起交付）。

---

## 12. 验收清单

### 12.1 静态

```bash
cd backend && php think plugin:manifest-check && php think arch:check
```

### 12.2 全链路（隔离库）

- [ ] 绑粉/注册：关系正确、循环绑定拒绝  
- [ ] 下单：`OrderCreateAfter` 快照存在  
- [ ] 支付：`OrderPaySuccess` 佣金与比例正确、幂等  
- [ ] 退款：佣金回滚与订单状态一致  
- [ ] 提现：审核流完整；打款走 pay（若启用）  
- [ ] 订单/用户 Extend：前后端字段一致  
- [ ] 禁用分销插件：核心商城可下单（无 fatal）；代理返回空  
- [ ] 卸载：菜单与自有数据按策略清理（仅测试库）  

### 12.3 对账

- [ ] 佣金流水 = 订单实付 × 比例（抽样）  
- [ ] 同一 order_no 无重复入账  

---

## 13. 打包

分销通常为单一大模块：打包 `backend/modules/distribution/` + admin plugin 前端 → `.plugin-packages/distribution-{version}.zip`。

子功能不应拆成多个 key  unless 产品明确要求独立装卸（现役无此拆分）。

---

## 14. 禁止事项

- 禁止同级模块 `use modules\distribution\*`（应 event/Proxy）。  
- 禁止在 marketing 模块写佣金入账。  
- 禁止改 `order_center` 核心字段语义来「带佣金」；用快照表或 Extend。  
- 禁止提现接口跳过权限校验。  
- 禁止把密钥写进 plugin.json。

---

## 15. 排错

| 现象 | 查 |
| --- | --- |
| 无佣金 | OrderPaySuccess 是否触发；listener 是否启用；商品 is_sub |
| 重复佣金 | 幂等键；支付回调重试 |
| 详情无分销字段 | OrderCenterDetailExtend 监听；插件启用 |
| app 调不到分销 | DistributionServiceProxy 白名单 method |

---

## 16. 与 marketing / trade 边界（小结）

```text
trade     → 成交价、活动库存
marketing → 券/积分/抽奖（弱对账）
distribution → 关系 + 佣金快照 + 结算/提现（强对账）
```

---

## 17. 参考阅读

- 订单中台扩展：[订单中台规范](../../../../rules/订单中台规范.md)  
- 支付打款：[01-驱动-pay-支付](01-驱动-pay-支付.md) merchantPay 节  
- 真源事件列表：`backend/modules/distribution/event.php`

---

## 18. DistributionServiceProxy 使用约定

app 层需要读分销配置、判断用户是否推广员、查询某订单快照时，应触发 `DistributionServiceProxy`，由 `DistributionServiceProxyListener` 按 **method 白名单** 分发到 `modules/distribution/service/*`。

插件二开扩展分销能力时：

1. 在 listener 中增加 case，不要新增 app → modules 的 use。  
2. 请求体建议固定 `{ "method": "xxx", "params": { ... } }` 形状，与现役实现对齐后再扩展。  
3. 未安装/禁用分销模块时 listener 不注册，app 侧必须容忍空结果。

这与 `MarketingServiceProxy`、`ChannelServiceProxy` 同构，是扁平大模块对外唯一推荐的 programmatic 入口（除 Extend 事件外）。
