# 用户事件监听器目录

## 概述

本目录包含用户生命周期各阶段的事件监听器，实现了事件驱动的业务解耦，便于二次开发扩展。

## 现有监听器列表

| 文件名 | 事件名称 | 触发时机 | 主要职责 |
|--------|---------|---------|---------|
| UserLoginListener.php | `UserLogin` | 用户登录时 | 处理用户登录后的业务（登录记录、状态更新等） |
| UserLogoutListener.php | `UserLogout` | 用户退出登录时 | 清理会话、记录日志 |
| UserRegisterListener.php | `UserRegister` | 用户注册后 | 处理用户注册后的业务（推广关系绑定等） |
| UserRegisterNoticeListener.php | `UserRegister` | 用户注册后 | 发送注册通知 |
| UserLevelListener.php | `UserLevel` | 用户等级变更时 | 处理用户等级变更后的业务（权限更新、通知等） |
| UserVisitListener.php | `UserVisit` | 用户访问页面时 | 记录用户访问行为（统计、足迹等） |
| UserIntegralChangeListener.php | `UserIntegralChange` | 用户积分变动时 | 记录积分流水、触发等级计算 |
| UserIntegralChangeNoticeListener.php | `UserIntegralChange` | 用户积分变动时 | 发送积分变动通知 |
| UserExpChangeListener.php | `UserExpChange` | 用户经验变动时 | 处理经验变动后的业务（升级检测等） |
| UserBalanceChangeListener.php | `UserBalanceChange` | 用户余额变动时 | 记录余额流水、触发相关业务 |
| UserBalanceChangeNoticeListener.php | `UserBalanceChange` | 用户余额变动时 | 发送余额变动通知 |
| UserExtractListener.php | `UserExtract` | 用户提现成功时 | 处理提现后的业务（余额扣减、记录流水等） |
| UserExtractApplyListener.php | `UserExtractApply` | 用户提交提现申请时 | 发送提现申请通知 |
| UserRevenueListener.php | `UserRevenue` | 用户收益到账时 | 处理收益相关业务 |
| UserRevenueNoticeListener.php | `UserRevenue` | 用户收益到账时 | 发送收益通知 |
| UserBrokerageChangeListener.php | `UserBrokerageChange` | 用户佣金变动时 | 记录佣金流水 |
| UserCancelListener.php | `UserCancel` | 用户注销账号时 | 清理用户数据、记录注销日志 |
| UserCartListener.php | `UserCart` | 用户购物车操作时 | 处理购物车相关业务（加入、删除、更新等） |

> **注意**：
> 1. `UserRecharge` / `UserRechargeRefund` 事件由 `modules/marketing/recharge/event.php` 自注册监听器，不在本目录。
> 2. 推广关系绑定（分销）由 `modules/distribution/listener/DistributionListener.php` 处理。

## 用户事件触发流程

```
用户注册 → UserRegister（业务 + 通知）→ DistributionListener(spread_bind 推广关系绑定)
    ↓
用户登录 → UserLogin
    ↓
用户访问 → UserVisit
    ↓
用户积分变动 → UserIntegralChange（业务 + 通知）→ UserLevel
    ↓
用户经验变动 → UserExpChange → UserLevel
    ↓
用户余额变动 → UserBalanceChange（业务 + 通知）
    ↓
用户提现申请 → UserExtractApply
    ↓
用户提现成功 → UserExtract
    ↓
用户收益 → UserRevenue（业务 + 通知）
    ↓
用户佣金变动 → UserBrokerageChange
    ↓
用户购物车操作 → UserCart
    ↓
用户注销 → UserCancel
```

## 购物车事件

购物车事件由 `UserCartListener` 统一处理：

| 事件类型 | 说明 |
|----------|------|
| add | 加入购物车 |
| delete | 删除购物车商品 |
| update | 更新购物车商品数量 |
| clear | 清空购物车 |

调用示例：

```php
// 加入购物车
event('UserCart', ['add', [
    'uid' => $uid,
    'product_id' => $productId,
    'sku_id' => $skuId,
    'cart_num' => $cartNum,
    'add_time' => time(),
]]);
```

## 命名规范

- 事件 key 不带 `Listener` 后缀：`event('UserCart', [...])`
- 监听器类名保留 `Listener` 后缀：`UserCartListener`

---

**版本**: v8.1
**最后更新**: 2026-08-18（P5 事件规范收口，对齐实际注册表）
