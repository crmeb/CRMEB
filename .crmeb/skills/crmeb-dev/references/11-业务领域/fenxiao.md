---
alwaysApply: false
description: 分销返佣业务参考 - 返佣层级结构与计算规则说明；当前返佣链路由 OrderPaySuccess 等事件与 distribution 模块监听器接入，历史流程仅供迁移定位
---
> **⚠️ 版本说明**：当前返佣链路已中台化——订单支付成功由 `app/services/order/OrderSuccessServices.php` 触发 `OrderPaySuccess` 事件（基于 `eb_order_center` 中台表），`modules/distribution/listener/` 下监听器负责订单快照、佣金发放和用户扩展。本文保留的旧流程只供迁移定位，当前字段与行为以代码为准。

## 二、核心架构

### 2.1 返佣层级结构

```
用户下单
    ↓
一级推广人 (spread_uid) → one_brokerage
    ↓
二级推广人 (spread_two_uid) → two_brokerage
    ↓
员工 (staff_id) → staff_brokerage
    ↓
代理商 (agent_id) → agent_brokerage
    ↓
事业部 (division_id) → division_brokerage
```

### 2.2 核心服务文件

| 服务文件 | 职责 | 关键方法 |
|---------|------|---------|
| [UserBrokerageServices.php](../../../../../backend/app/services/user/UserBrokerageServices.php) | 用户佣金管理 | `brokerageIncome()`、`orderRefundBrokerageBack()` |
| [OrderCreateSnapshotListener.php](../../../../../backend/modules/distribution/listener/OrderCreateSnapshotListener.php) | 订单创建后写入分销快照 | `OrderCreateAfter` |
| [OrderPaidListener.php](../../../../../backend/modules/distribution/listener/OrderPaidListener.php) | 支付成功后的分销处理 | `OrderPaySuccess` |
| [OrderSpreadBrokerageListener.php](../../../../../backend/modules/distribution/listener/OrderSpreadBrokerageListener.php) | 返佣规则与佣金分配 | `OrderSpreadBrokerage` |

---

## 三、佣金类型配置

在 `UserBrokerageServices::$incomeData` 中定义：

| 类型标识 | title | type | pm | 说明 |
|---------|-------|------|-----|------|
| `get_self_brokerage` | 获得自购订单佣金 | self_brokerage | 1 | 用户自购获得佣金 |
| `get_brokerage` | 获得下级推广订单佣金 | one_brokerage | 1 | 一级分销佣金 |
| `get_two_brokerage` | 获得二级推广订单佣金 | two_brokerage | 1 | 二级分销佣金 |
| `get_user_brokerage` | 获得推广用户佣金 | brokerage_user | 1 | 邀请用户奖励 |
| `get_staff_brokerage` | 获得员工推广订单佣金 | staff_brokerage | 1 | 员工返佣 |
| `get_agent_brokerage` | 获得代理推广订单佣金 | agent_brokerage | 1 | 代理商返佣 |
| `get_division_brokerage` | 获得事业部推广订单佣金 | division_brokerage | 1 | 事业部返佣 |
| `get_pink_master_brokerage` | 获得拼团团长佣金 | pink_master_brokerage | 1 | 团长返佣 |
| `extract` | 佣金提现 | extract | 0 | 提现扣除 |
| `extract_fail` | 提现失败 | extract_fail | 1 | 提现失败退回 |
| `brokerage_to_nowMoney` | 佣金提现到余额 | extract_money | 0 | 转余额扣除 |
| `brokerage_refund` | 退款退佣金 | refund | 0 | 退款扣回佣金 |

---

## 四、返佣计算流程

### 4.1 订单创建时计算佣金

```php
// StoreOrderCreateServices::computeBrokerage()
// 1. 获取佣金比例配置
[$storeBrokerageRatio, $storeBrokerageTwo, $staffPercent, $agentPercent, $divisionPercent] = $divisionService->getDivisionPercent(...);

// 2. 根据配置计算各层级佣金
// 支持三种模式：
// - user_brokerage_type=0: 按支付价格返佣
// - user_brokerage_type=1: 按商品价格返佣  
// - 指定返佣金额: 商品属性中设置

// 3. 写入购物车数据
$cart['one_brokerage'] = $oneBrokerage;
$cart['two_brokerage'] = $twoBrokerage;
$cart['staff_brokerage'] = $staffBrokerage;
$cart['agent_brokerage'] = $agentBrokerage;
$cart['division_brokerage'] = $divisionBrokerage;
```

### 4.2 订单完成时发放佣金

当前分散的发放逻辑：

```php
// 一级返佣 - backOrderBrokerage()
$userServices->bcInc($one_spread_uid, 'brokerage_price', $brokeragePrice);
$userBrokerageServices->brokerageIncome('get_brokerage', $one_spread_uid, [...]);

// 二级返佣 - backOrderBrokerageTwo()
$userServices->bcInc($spread_two_uid, 'brokerage_price', $brokeragePrice);
$userBrokerageServices->brokerageIncome('get_two_brokerage', $spread_two_uid, [...]);

// 员工返佣
$userServices->bcInc($orderInfo['staff_id'], 'brokerage_price', $orderInfo['staff_brokerage']);
$userBrokerageServices->brokerageIncome('get_staff_brokerage', $orderInfo['staff_id'], [...]);

// 代理返佣
$userServices->bcInc($orderInfo['agent_id'], 'brokerage_price', $orderInfo['agent_brokerage']);
$userBrokerageServices->brokerageIncome('get_agent_brokerage', $orderInfo['agent_id'], [...]);

// 事业部返佣
$userServices->bcInc($orderInfo['division_id'], 'brokerage_price', $orderInfo['division_brokerage']);
$userBrokerageServices->brokerageIncome('get_division_brokerage', $orderInfo['division_id'], [...]);
```

---

## 五、统一返佣发放方法设计

### 5.1 建议的统一方法

```php
/**
 * 统一发放佣金
 * @param array $orderInfo 订单信息
 * @param array $userInfo 用户信息
 * @return bool
 */
public function distributeBrokerage(array $orderInfo, array $userInfo): bool
{
    if (!sys_config('brokerage_func_status')) {
        return true;
    }
    
    /** @var UserServices $userServices */
    $userServices = app()->make(UserServices::class);
    /** @var UserBrokerageServices $userBrokerageServices */
    $userBrokerageServices = app()->make(UserBrokerageServices::class);
    
    $brokerageItems = [
        // 一级返佣
        [
            'uid' => $this->getFirstLevelUid($orderInfo, $userInfo),
            'amount' => $orderInfo['one_brokerage'] ?? 0,
            'type' => $this->isSelfOrder($orderInfo, $userInfo) ? 'get_self_brokerage' : 'get_brokerage',
            'nickname' => $userInfo['nickname'] ?? ''
        ],
        // 二级返佣
        [
            'uid' => $this->getSecondLevelUid($orderInfo, $userInfo),
            'amount' => $orderInfo['two_brokerage'] ?? 0,
            'type' => 'get_two_brokerage',
            'nickname' => ''
        ],
        // 员工返佣
        [
            'uid' => $orderInfo['staff_id'] ?? 0,
            'amount' => $orderInfo['staff_brokerage'] ?? 0,
            'type' => 'get_staff_brokerage',
            'nickname' => ''
        ],
        // 代理返佣
        [
            'uid' => $orderInfo['agent_id'] ?? 0,
            'amount' => $orderInfo['agent_brokerage'] ?? 0,
            'type' => 'get_agent_brokerage',
            'nickname' => ''
        ],
        // 事业部返佣
        [
            'uid' => $orderInfo['division_id'] ?? 0,
            'amount' => $orderInfo['division_brokerage'] ?? 0,
            'type' => 'get_division_brokerage',
            'nickname' => ''
        ],
        // 拼团团长返佣
        [
            'uid' => $orderInfo['pink_master_uid'] ?? 0,
            'amount' => $orderInfo['pink_master_brokerage'] ?? 0,
            'type' => 'get_pink_master_brokerage',
            'nickname' => ''
        ]
    ];
    
    foreach ($brokerageItems as $item) {
        if (!$item['uid'] || $item['amount'] <= 0) {
            continue;
        }
        
        // 检查是否为有效推广员
        if (!$userServices->checkUserPromoter($item['uid'])) {
            continue;
        }
        
        // 更新佣金余额
        $spreadPrice = $userServices->value(['uid' => $item['uid']], 'brokerage_price');
        $balance = bcadd($spreadPrice, $item['amount'], 2);
        $userServices->bcInc($item['uid'], 'brokerage_price', $item['amount'], 'uid');
        
        // 记录佣金流水
        $userBrokerageServices->brokerageIncome($item['type'], $item['uid'], [
            'number' => floatval($item['amount']),
            'pay_price' => $orderInfo['pay_price'] ?? 0,
            'nickname' => $item['nickname']
        ], $balance, $orderInfo['id']);
        
        // 发送通知
        $this->sendBackOrderBrokerage($orderInfo, $item['uid'], $item['amount']);
    }
    
    return true;
}
```

### 5.2 辅助方法

```php
/**
 * 获取一级推广人UID
 */
protected function getFirstLevelUid(array $orderInfo, array $userInfo): int
{
    $isSelfBrokerage = sys_config('is_self_brokerage', 0);
    
    if (isset($orderInfo['spread_uid']) && $orderInfo['spread_uid'] == -1) {
        return 0; // 分销订单但未设置推广人
    }
    
    if (!isset($orderInfo['spread_uid']) || !$orderInfo['spread_uid']) {
        // 兼容旧订单，使用用户的上级
        if (!$isSelfBrokerage && (!$userInfo['spread_uid'] || $userInfo['spread_uid'] == $orderInfo['uid'])) {
            return 0;
        }
        return $isSelfBrokerage ? $userInfo['uid'] : $userInfo['spread_uid'];
    }
    
    return $orderInfo['spread_uid'];
}

/**
 * 获取二级推广人UID
 */
protected function getSecondLevelUid(array $orderInfo, array $userInfo): int
{
    /** @var UserServices $userServices */
    $userServices = app()->make(UserServices::class);
    $isSelfBrokerage = sys_config('is_self_brokerage', 0);
    
    if (isset($orderInfo['spread_two_uid']) && $orderInfo['spread_two_uid'] == -1) {
        return 0;
    }
    
    if (isset($orderInfo['spread_two_uid']) && $orderInfo['spread_two_uid']) {
        return $orderInfo['spread_two_uid'];
    }
    
    // 兼容旧订单，逐级查找
    $userInfoTwo = $userServices->get((int)$userInfo['spread_uid']);
    if (!$userInfoTwo) {
        return 0;
    }
    
    if (!$isSelfBrokerage && (!$userInfoTwo['spread_uid'] || $userInfoTwo['spread_uid'] == $orderInfo['uid'])) {
        return 0;
    }
    
    return $isSelfBrokerage ? $userInfoTwo['uid'] : $userInfoTwo['spread_uid'];
}

/**
 * 判断是否为自购订单
 */
protected function isSelfOrder(array $orderInfo, array $userInfo): bool
{
    $firstUid = $this->getFirstLevelUid($orderInfo, $userInfo);
    return $firstUid == $orderInfo['uid'];
}
```

---

## 六、退款退佣流程

```php
// UserBrokerageServices::orderRefundBrokerageBack()
// 1. 查询该订单已发放的佣金记录
$where = [
    'uid' => [$order['spread_uid'], $order['spread_two_uid'], $order['staff_id'], ...],
    'type' => ['self_brokerage', 'one_brokerage', 'two_brokerage', ...],
    'link_id' => $order['id'],
    'pm' => 1
];

// 2. 扣回佣金
$userServices->bcDec($item['uid'], 'brokerage_price', $item['number'], 'uid');

// 3. 记录退款流水
$userBrokerageServices->brokerageIncome('brokerage_refund', $item['uid'], $item['number'], ...);
```

---

## 七、数据结构

### 7.1 订单表相关字段

| 字段 | 类型 | 说明 |
|------|------|------|
| `spread_uid` | int | 一级推广人UID |
| `spread_two_uid` | int | 二级推广人UID |
| `staff_id` | int | 员工UID |
| `agent_id` | int | 代理商UID |
| `division_id` | int | 事业部UID |
| `one_brokerage` | decimal | 一级佣金金额 |
| `two_brokerage` | decimal | 二级佣金金额 |
| `staff_brokerage` | decimal | 员工佣金金额 |
| `agent_brokerage` | decimal | 代理商佣金金额 |
| `division_brokerage` | decimal | 事业部佣金金额 |

### 7.2 用户表相关字段

| 字段 | 类型 | 说明 |
|------|------|------|
| `brokerage_price` | decimal | 当前佣金余额 |
| `spread_uid` | int | 上级推广人UID |
| `is_promoter` | tinyint | 是否为推广员 |
| `division_type` | tinyint | 分销类型(0普通/1事业部/2代理/3员工) |
| `division_id` | int | 所属事业部ID |
| `agent_id` | int | 所属代理商ID |

---

## 八、配置项

| 配置项 | 说明 | 默认值 |
|--------|------|--------|
| `brokerage_func_status` | 分销功能开关 | 1 |
| `brokerage_level` | 分销层级(1/2) | 2 |
| `is_self_brokerage` | 是否开启自购返佣 | 0 |
| `user_brokerage_type` | 返佣计算方式(0支付价/1商品价) | 0 |
| `store_brokerage_ratio` | 一级返佣比例 | 0 |
| `store_brokerage_two` | 二级返佣比例 | 0 |

---

## 九、已知问题清单

### 严重问题

| # | 问题 | 文件 | 说明 | 状态 |
|---|------|------|------|------|
| 1 | 佣金转余额缺少外层事务 | UserRechargeServices.php | `importNowMoney` 中 `moneyIncome`(加余额) 和 `brokerageIncome`(扣佣金) 不在同一事务中，若后者失败会导致余额增加但佣金未扣减 | **已修复** |
| 2 | 退款退佣缺少事务 | UserBrokerageServices.php | `orderRefundBrokerageBack` 中 `bcDec` 和 `saveAll` 之间无事务保护，余额扣了但流水可能没写入 | **已修复** |
| 3 | 退款退佣未使用 `brokerageIncome` 统一入口 | UserBrokerageServices.php | 直接操作数据库，逻辑重复，且 `balance` 基于过时快照计算 | **已修复** |
| 4 | 秒杀返佣逻辑BUG | StoreOrderTakeServices.php | `checkCommissionStatus` 硬编码秒杀不返佣，但后台和订单创建都支持秒杀 `is_commission` 配置，导致开了返佣的秒杀活动佣金永远无法发放 | **已修复** |
| 5 | 指定返佣金额时员工/代理/事业部佣金为0 | StoreOrderCreateServices.php | `is_sub=1` 分支只计算了一级二级佣金，`staff/agent/division` 保持0 | **已修复** |
| 6 | `UserBillServices::orderRefundBrokerageBack` 是死代码 | UserBillServices.php | 与 `UserBrokerageServices` 中同名方法功能重复，若误调用会导致双重扣款 | **已修复** |

### 中等问题

| # | 问题 | 文件 | 说明 | 状态 |
|---|------|------|------|------|
| 7 | `brokerageIncome` 的 `balance` 记录不准确 | UserBrokerageServices.php | `$userInfo['brokerage_price']` 是事务开头查询的快照，同一事务内多次调用同一用户时，后续 `balance` 基于旧值计算 | **已修复** |
| 8 | 缺少防重复返佣机制 | StoreOrderTakeServices.php | `distributeBrokerage` 无幂等性保护，并发收货可能导致重复返佣 | **已修复** |
| 9 | 子订单退佣遗漏员工/代理/事业部/团长佣金 | UserBrokerageServices.php | 子订单回退只处理了一级和二级佣金 | **已修复** |
| 10 | `getSpreadUid` 返回 -1 被写入订单 | StoreOrderCreateServices.php | `if ($spread_uid)` 对 -1 为真，导致无效值写入 `spread_uid` 字段 | **已修复** |
| 11 | 拼团 `is_commission=0` 时团长佣金也被跳过 | StoreOrderTakeServices.php | `checkCommissionStatus` 返回 false 时整个 `distributeBrokerage` 被跳过，团长佣金也无法发放 | **已修复** |
| 12 | `BaseDao::bc` 先读后写非原子操作 | BaseDao.php | `bcInc`/`bcDec` 底层是 `get→计算→save`，非 `UPDATE SET field=field+N`，并发下可能丢失更新 | 待优化（影响范围大） |
| 13 | 退佣余额不足时差额无记录无追回 | UserBrokerageServices.php | 用户已提现佣金时，退佣只扣到0，差额部分无日志无追回机制 | 已覆盖（brokerageIncome 内部 bcDec 余额不足时抛异常） |

### 低等问题

| # | 问题 | 说明 | 状态 |
|---|------|------|------|
| 14 | `getFirstLevelUid` 缺少 `spread_uid == -1` 的防御性判断 | 依赖 `checkCommissionStatus` 先行执行 | **已修复** |
| 15 | 员工/代理/事业部/团长返佣缺少到账通知 | 一级二级有 `sendBackOrderBrokerage`，其他层级没有 | 待确认（业务需求） |
| 16 | 邀新佣金外层事务将通知与佣金绑定 | 通知失败会导致佣金回滚，金融操作不应因通知失败而回滚 | 待优化 |
| 17 | 佣金比例总和可能超过100% | 缺乏校验，极端配置下佣金总额可能超过订单金额 | 待优化 |

---

**版本**: v6.0
**创建日期**: 2026-06-06
**适用范围**: CRMEB v8
