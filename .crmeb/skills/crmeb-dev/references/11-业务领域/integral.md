---
alwaysApply: false
description: 用户积分操作规则参考 - 积分类型定义、增加/扣减操作方法、触发场景与最佳实践，新版接口方法签名与字段以实际代码为准
---
# CRMEB v8 用户积分操作规则文档

---

## 一、概述

本文档整理了 **CRMEB v8** 系统中用户积分的完整操作体系，包括积分类型定义、操作方法、使用场景及最佳实践。

**积分架构说明**：
- **积分存储**：用户积分存储在 `user` 表的 `integral` 字段
- **积分流水**：积分变动记录存储在 `user_bill` 表（category = 'integral'）
- **积分冻结**：支持订单赠送积分的冻结机制
- **事务保障**：积分操作应使用事务确保数据一致性

---

## 二、积分类型定义

### 2.1 积分收入类型（pm = 1）

| 类型标识 | title | category | type | 说明 |
|---------|-------|----------|------|------|
| `sign` | 签到奖励 | integral | sign | 用户签到获得积分 |
| `system_add` | 系统增加积分 | integral | system_add | 管理员手动增加积分 |
| `product_gain` | 购买商品赠送积分 | integral | product_gain | 购买商品获得赠送积分 |
| `gain` | 下单赠送积分 | integral | gain | 下单获得赠送积分 |
| `lottery_add` | 抽奖中奖赠送积分 | integral | lottery_add | 抽奖获得积分奖励 |
| `pay_product_integral_back` | 商品退积分 | integral | pay_product_integral_back | 订单退款返还使用的积分 |
| `integral_refund` | 返还下单使用积分 | integral | integral_refund | 订单取消/退款返还积分 |

### 2.2 积分支出类型（pm = 0）

| 类型标识 | title | category | type | 说明 |
|---------|-------|----------|------|------|
| `deduction` | 积分抵扣 | integral | deduction | 下单时使用积分抵扣金额 |
| `system_sub` | 系统减少积分 | integral | system_sub | 管理员手动扣除积分 |
| `lottery_use` | 参与抽奖使用积分 | integral | lottery_use | 抽奖消耗积分 |
| `storeIntegral_use` | 积分兑换商品 | integral | storeIntegral_use | 积分商城兑换商品 |
| `order_deduction` | 扣除订单下单赠送积分 | integral | order_deduction | 退款时收回赠送积分 |

---

## 三、核心操作方法

### 3.1 UserBillServices 积分记录方法

```php
// 增加积分记录
public function incomeIntegral(int $uid, string $type, array $data)

// 扣除积分记录
public function expendIntegral(int $uid, string $type, array $data)

// 通用收入记录（支持积分、余额、经验）
public function income(string $type, int $uid, $number, $balance, $link_id)
```

**使用示例**：
```php
// 签到增加积分
$userBill->incomeIntegral($uid, 'sign', [
    'number' => 10,
    'balance' => 100,
    'title' => '签到奖励',
    'mark' => '每日签到获得10积分'
]);
```

### 3.2 UserServices 用户积分操作方法

```php
// 高精度加法（用于增加用户积分）
public function bcInc($key, string $incField, string $inc, string $keyField = null, int $acc = 2)

// 高精度减法（用于减少用户积分）
public function bcDec($key, string $incField, string $inc, string $keyField = null, int $acc = 2)
```

---

## 四、积分操作场景汇总

### 4.1 积分增加场景

| 场景 | 触发位置 | Services文件 | 积分类型 |
|------|---------|-------------|---------|
| 用户签到 | sign() | [UserSignServices.php](../../../../../backend/modules/marketing/signin/service/UserSignServices.php) | sign |
| 用户注册奖励 | register() | [UserServices.php](../../../../../backend/app/services/user/UserServices.php) | system_add |
| 管理员增加积分 | setUserInfo() | [UserServices.php](../../../../../backend/app/services/user/UserServices.php) | system_add |
| 后台调整积分 | setUserInfo() | [UserServices.php](../../../../../backend/app/services/user/UserServices.php) | system_add |
| 下单赠送积分 | paySuccess() | [OrderSuccessServices.php](../../../../../backend/app/services/order/OrderSuccessServices.php)（约 L106 触发 `OrderPaySuccess` 事件） | gain/product_gain |
| 抽奖中奖 | sendPrize() | [LuckLotteryRecordServices.php](../../../../../backend/modules/marketing/lottery/service/LuckLotteryRecordServices.php)（抽奖模块已迁移至 modules/marketing/lottery/，`app/services/activity/` 下无 lottery/） | lottery_add |
| 订单退款返还 | execute() | [RefundExecutorServices.php](../../../../../backend/app/services/order/RefundExecutorServices.php) | pay_product_integral_back |

### 4.2 积分减少场景

| 场景 | 触发位置 | Services文件 | 积分类型 |
|------|---------|-------------|---------|
| 积分抵扣下单 | createOrder() | StoreOrderCreateServices.php | deduction |
| 管理员扣除积分 | setUserInfo() | UserServices.php | system_sub |
| 后台调整积分 | edit() | StoreManageServices.php | system_sub |
| 抽奖消耗 | lottery() | LuckLotteryServices.php | lottery_use |
| 积分兑换商品 | createOrder() | StoreIntegralOrderServices.php | storeIntegral_use |
| 退款收回赠送积分 | integralAndCouponBack() | StoreOrderRefundServices.php | order_deduction |

---

## 五、积分冻结机制

### 5.1 冻结规则

系统支持订单赠送积分的冻结功能，配置项为 `integral_frozen`（单位：天）。

```php
// 在 UserBillServices::income() 方法中
if ($type == 'pay_give_integral' || $type == 'order_give_integral') {
    $integral_frozen = sys_config('integral_frozen');
    if ($integral_frozen) {
        $data['frozen_time'] = $data['add_time'] + ($integral_frozen * 86400);
    }
}
```

### 5.2 冻结期间的积分使用

冻结的积分不计入可用积分：

```php
// 计算可用积分
$usable = bcsub(
    (string)$userInfo['integral'], 
    (string)$userBillServices->getBillSum(['uid' => $userInfo['uid'], 'is_frozen' => 1]), 
    0
);
```

### 5.3 冻结解除

订单退款时会清除对应积分的冻结状态：

```php
// 清除积分冻结
$userBillServices->update(['link_id' => $order['id']], ['frozen_time' => 0]);
```

---

## 六、积分抵扣规则

### 6.1 抵扣比例

积分与人民币的兑换比例由配置项 `integralRatio` 控制。

### 6.2 抵扣上限

```php
// 积分抵扣上限
$integralMaxNum = sys_config('integral_max_num', 200);
if ($integralMaxNum > 0 && $usable > $integralMaxNum) {
    $integral = $integralMaxNum;
} else {
    $integral = $usable;
}
```

### 6.3 抵扣计算

```php
// 计算抵扣金额
$deductionPrice = (float)bcmul((string)$integral, (string)$other['integralRatio'], 2);
if ($deductionPrice < $payPrice) {
    $payPrice = bcsub((string)$payPrice, (string)$deductionPrice, 2);
    $usedIntegral = $integral;
} else {
    $deductionPrice = $payPrice;
    $usedIntegral = (int)ceil(bcdiv((string)$payPrice, (string)$other['integralRatio'], 2));
    $payPrice = 0;
}
```

---

## 七、事务封装方案

### 7.1 统一积分操作方法

在 `UserBillServices` 中封装事务性积分操作方法：

```php
/**
 * 事务性修改用户积分并记录流水
 * @param int $uid 用户ID
 * @param string $type 操作类型：add(增加) / sub(减少)
 * @param float|int $amount 积分数量
 * @param string $billType 流水类型
 * @param string $title 标题
 * @param string $mark 备注
 * @param int $linkId 关联ID（如订单ID）
 * @param array $extraData 额外数据
 * @return bool
 * @throws \Exception
 */
public function transactionIntegral(int $uid, string $type, $amount, string $billType, 
    string $title, string $mark = '', int $linkId = 0, array $extraData = [])
{
    /** @var UserServices $userServices */
    $userServices = app()->make(UserServices::class);
    
    return $this->transaction(function () use (
        $uid, $type, $amount, $billType, $title, $mark, $linkId, $extraData, $userServices
    ) {
        // 获取当前用户积分
        $userInfo = $userServices->get($uid, ['integral']);
        if (!$userInfo) {
            throw new AdminException('用户不存在');
        }
        
        $oldIntegral = $userInfo['integral'];
        $newIntegral = 0;
        
        // 更新用户积分
        if ($type === 'add') {
            $newIntegral = bcadd((string)$oldIntegral, (string)$amount, 2);
            $res = $userServices->update($uid, ['integral' => $newIntegral]);
        } else {
            if (bccomp((string)$oldIntegral, (string)$amount, 2) < 0) {
                throw new AdminException('积分不足');
            }
            $newIntegral = bcsub((string)$oldIntegral, (string)$amount, 2);
            $res = $userServices->update($uid, ['integral' => $newIntegral]);
        }
        
        if (!$res) {
            throw new AdminException('积分更新失败');
        }
        
        // 记录积分流水
        $data = array_merge([
            'uid' => $uid,
            'category' => 'integral',
            'type' => $billType,
            'title' => $title,
            'mark' => $mark,
            'number' => $amount,
            'balance' => $newIntegral,
            'pm' => $type === 'add' ? 1 : 0,
            'link_id' => $linkId,
            'status' => 1,
            'add_time' => time(),
        ], $extraData);
        
        // 处理积分冻结
        if (($billType === 'product_gain' || $billType === 'gain') && !empty($extraData['frozen_time'])) {
            $data['frozen_time'] = $extraData['frozen_time'];
        }
        
        if (!$this->dao->save($data)) {
            throw new AdminException('积分记录写入失败');
        }
        
        // 触发积分变动事件
        event('UserIntegralChangeListener', [$uid, $type, $amount, $newIntegral]);
        
        return true;
    });
}
```

### 7.2 使用示例

```php
// 示例1：签到增加积分
$userBillServices->transactionIntegral(
    $uid,
    'add',
    10,
    'sign',
    '签到奖励',
    '每日签到获得10积分'
);

// 示例2：下单使用积分抵扣
$userBillServices->transactionIntegral(
    $uid,
    'sub',
    50,
    'deduction',
    '积分抵扣',
    '购买商品使用50积分抵扣',
    $orderId,
    ['deduction_price' => 5.00]
);

// 示例3：订单赠送积分（带冻结）
$integral_frozen = sys_config('integral_frozen');
$frozen_time = $integral_frozen ? time() + ($integral_frozen * 86400) : 0;

$userBillServices->transactionIntegral(
    $uid,
    'add',
    100,
    'product_gain',
    '购买商品赠送积分',
    '购买商品赠送100积分',
    $orderId,
    ['frozen_time' => $frozen_time]
);
```

---

## 八、事件触发

### 8.1 积分变动事件

```php
// 触发积分变动事件
event('UserIntegralChangeListener', [$uid, $changeType, $amount, $balance]);
```

**事件参数**：
- `$uid` - 用户ID
- `$changeType` - 变动类型：'add' / 'sub'
- `$amount` - 变动数量
- `$balance` - 变动后余额

### 8.2 通知事件

```php
// 积分到账通知
event('NoticeListener', [[
    'user_type' => $user['user_type'],
    'now_money' => $newBalance,
    'pm' => 1,
    'number' => $amount,
], 'integral_accout']);
```

---

## 九、数据一致性保障

### 9.1 事务使用原则

1. **必须使用事务**：积分修改和流水记录必须在同一事务中
2. **异常回滚**：任何一步失败都应回滚所有操作
3. **状态检查**：操作前检查用户状态和积分余额

### 9.2 幂等性保障

```php
// 避免重复处理（以订单退款为例）
$count = $userBillServices->count([
    'category' => 'integral', 
    'type' => 'integral_refund', 
    'link_id' => $order['id']
]);
if (!$count) {
    // 执行退款操作
}
```

---

## 十、最佳实践

### 10.1 积分操作规范

| 场景 | 推荐方法 | 说明 |
|------|---------|------|
| 增加积分 | `transactionIntegral('add', ...)` | 使用事务方法 |
| 减少积分 | `transactionIntegral('sub', ...)` | 使用事务方法 |
| 批量操作 | 使用 `saveAll()` | 批量写入流水 |
| 积分冻结 | 在 `extraData` 中传入 `frozen_time` | 自动处理冻结逻辑 |

### 10.2 性能优化建议

1. **避免频繁操作**：合并多次积分变动为一次操作
2. **缓存可用积分**：减少数据库查询
3. **异步处理非关键逻辑**：使用队列处理通知等非核心操作

---

**版本**: v6.0
**创建日期**: 2026-06-04
**最后更新**: 2026-06-05
**适用范围**: CRMEB v8
**文档位置**: [.crmeb/skills/crmeb-dev/references/11-业务领域/integral.md](integral.md)
