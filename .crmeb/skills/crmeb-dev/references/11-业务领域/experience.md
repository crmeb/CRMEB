---
alwaysApply: false
description: 用户经验操作规则参考 - 经验类型定义、增加/扣减操作方法、触发场景与最佳实践，新版接口方法签名与字段以实际代码为准
---
# CRMEB v8 用户经验操作规则文档

---

## 一、概述

本文档整理了 **CRMEB v8** 系统中用户经验的完整操作体系，包括经验类型定义、操作方法、使用场景及最佳实践。

**经验架构说明**：
- **经验存储**：用户经验存储在 `user` 表的 `experience` 字段
- **经验等级**：用户等级存储在 `user` 表的 `level` 字段
- **经验流水**：经验变动记录存储在 `user_bill` 表（category = 'experience'）
- **事务保障**：经验操作应使用事务确保数据一致性

---

## 二、经验类型定义

### 2.1 经验收入类型（pm = 1）

| 类型标识 | title | category | type | 说明 |
|---------|-------|----------|------|------|
| `sign_exp` | 签到经验 | experience | sign_exp | 用户签到获得经验 |
| `order_exp` | 下单经验 | experience | order_exp | 下单获得经验奖励 |
| `share_exp` | 分享经验 | experience | share_exp | 分享商品获得经验 |
| `comment_exp` | 评价经验 | experience | comment_exp | 评价商品获得经验 |
| `system_add_exp` | 系统增加经验 | experience | system_add_exp | 管理员手动增加经验 |

### 2.2 经验支出类型（pm = 0）

| 类型标识 | title | category | type | 说明 |
|---------|-------|----------|------|------|
| `system_sub_exp` | 系统减少经验 | experience | system_sub_exp | 管理员手动扣除经验 |
| `level_upgrade` | 升级消耗经验 | experience | level_upgrade | 用户升级消耗经验 |

---

## 三、核心操作方法

### 3.1 UserBillServices 经验记录方法

```php
// 通用收入记录（支持积分、余额、经验）
public function income(string $type, int $uid, $number, $balance, $link_id)

// 通用支出记录
public function expend(string $type, int $uid, $number, $balance, $link_id)
```

**使用示例**：
```php
// 签到增加经验
$userBill->income('sign_exp', $uid, 20, $newExp, 0);
```

### 3.2 UserServices 用户经验操作方法

```php
// 高精度加法（用于增加用户经验）
public function bcInc($key, string $incField, string $inc, string $keyField = null, int $acc = 2)

// 高精度减法（用于减少用户经验）
public function bcDec($key, string $incField, string $inc, string $keyField = null, int $acc = 2)

// 获取用户等级配置
public function getLevelConfig()

// 计算升级所需经验
public function getNextLevelExp(int $currentLevel)
```

---

## 四、经验操作场景汇总

### 4.1 经验增加场景

| 场景 | 触发位置 | Services文件 | 经验类型 |
|------|---------|-------------|---------|
| 用户签到 | sign() | [UserSignServices.php](../../../../../backend/modules/marketing/signin/service/UserSignServices.php) | sign_exp |
| 下单获得经验 | paySuccess() | [OrderSuccessServices.php](../../../../../backend/app/services/order/OrderSuccessServices.php)（约 L106 触发 `OrderPaySuccess` 事件） | order_exp |
| 分享商品 | userShare() | [PublicController.php](../../../../../backend/app/api/controller/v1/PublicController.php) | share_exp |
| 评价商品 | reply() | [StoreProductReplyController.php](../../../../../backend/app/adminapi/controller/product/StoreProductReplyController.php) | comment_exp |
| 管理员增加经验 | setUserInfo() | [UserServices.php](../../../../../backend/app/services/user/UserServices.php) | system_add_exp |

### 4.2 经验减少场景

| 场景 | 触发位置 | Services文件 | 经验类型 |
|------|---------|-------------|---------|
| 管理员扣除经验 | setUserInfo() | UserServices.php | system_sub_exp |
| 用户升级消耗 | upgradeLevel() | UserServices.php | level_upgrade |

---

## 五、等级升级机制

### 5.1 等级配置

等级配置存储在 `system_user_level` 表中，包含：
- `level` - 等级编号
- `name` - 等级名称
- `need_exp` - 升级所需经验
- `discount` - 等级折扣
- `icon` - 等级图标

### 5.2 升级流程

```php
// 升级检查逻辑
public function checkLevelUpgrade(int $uid, int $addExp)
{
    $user = $this->get($uid);
    $oldLevel = $user['level'];
    $newExp = bcadd((string)$user['experience'], (string)$addExp, 2);
    
    // 获取等级配置
    $levelConfig = $this->getLevelConfig();
    
    // 查找新等级
    $newLevel = $oldLevel;
    foreach ($levelConfig as $config) {
        if ($newExp >= $config['need_exp'] && $config['level'] > $newLevel) {
            $newLevel = $config['level'];
        }
    }
    
    // 如果等级提升
    if ($newLevel > $oldLevel) {
        // 更新用户等级
        $this->update($uid, ['level' => $newLevel]);
        
        // 记录升级日志
        $userBillServices = app()->make(UserBillServices::class);
        $userBillServices->expend('level_upgrade', $uid, $expUsed, $newExp, 0);
        
        // 触发等级变更事件
        event('UserLevelListener', [$uid, $oldLevel, $newLevel]);
    }
    
    return $newLevel;
}
```

### 5.3 等级权益

| 等级 | 权益说明 |
|------|---------|
| 普通会员 | 基础购物权限 |
| 白银会员 | 95折优惠 |
| 黄金会员 | 9折优惠 + 专属客服 |
| 钻石会员 | 85折优惠 + 专属客服 + 优先发货 |
| 至尊会员 | 8折优惠 + 专属客服 + 优先发货 + 生日礼包 |

---

## 六、事务封装方案

### 6.1 统一经验操作方法

在 `UserBillServices` 中封装事务性经验操作方法：

```php
/**
 * 事务性修改用户经验并记录流水
 * @param int $uid 用户ID
 * @param string $type 操作类型：add(增加) / sub(减少)
 * @param float|int $amount 经验数量
 * @param string $billType 流水类型
 * @param string $title 标题
 * @param string $mark 备注
 * @param int $linkId 关联ID（如订单ID）
 * @param array $extraData 额外数据
 * @return bool
 * @throws \Exception
 */
public function transactionExperience(int $uid, string $type, $amount, string $billType, 
    string $title, string $mark = '', int $linkId = 0, array $extraData = [])
{
    /** @var UserServices $userServices */
    $userServices = app()->make(UserServices::class);
    
    return $this->transaction(function () use (
        $uid, $type, $amount, $billType, $title, $mark, $linkId, $extraData, $userServices
    ) {
        // 获取当前用户经验
        $userInfo = $userServices->get($uid, ['experience', 'level']);
        if (!$userInfo) {
            throw new AdminException('用户不存在');
        }
        
        $oldExp = $userInfo['experience'];
        $newExp = 0;
        
        // 更新用户经验
        if ($type === 'add') {
            $newExp = bcadd((string)$oldExp, (string)$amount, 2);
            $res = $userServices->update($uid, ['experience' => $newExp]);
        } else {
            if (bccomp((string)$oldExp, (string)$amount, 2) < 0) {
                throw new AdminException('经验不足');
            }
            $newExp = bcsub((string)$oldExp, (string)$amount, 2);
            $res = $userServices->update($uid, ['experience' => $newExp]);
        }
        
        if (!$res) {
            throw new AdminException('经验更新失败');
        }
        
        // 记录经验流水
        $data = array_merge([
            'uid' => $uid,
            'category' => 'experience',
            'type' => $billType,
            'title' => $title,
            'mark' => $mark,
            'number' => $amount,
            'balance' => $newExp,
            'pm' => $type === 'add' ? 1 : 0,
            'link_id' => $linkId,
            'status' => 1,
            'add_time' => time(),
        ], $extraData);
        
        if (!$this->dao->save($data)) {
            throw new AdminException('经验记录写入失败');
        }
        
        // 检查等级升级（仅增加经验时）
        if ($type === 'add') {
            $userServices->checkLevelUpgrade($uid, $amount);
        }
        
        return true;
    });
}
```

### 6.2 使用示例

```php
// 示例1：签到增加经验
$userBillServices->transactionExperience(
    $uid,
    'add',
    20,
    'sign_exp',
    '签到经验',
    '每日签到获得20经验'
);

// 示例2：下单获得经验
$userBillServices->transactionExperience(
    $uid,
    'add',
    50,
    'order_exp',
    '下单经验',
    '购买商品获得50经验',
    $orderId
);

// 示例3：管理员调整经验
$userBillServices->transactionExperience(
    $uid,
    'add',
    100,
    'system_add_exp',
    '系统增加经验',
    '管理员手动增加100经验'
);
```

---

## 七、事件触发

### 7.1 等级变更事件

```php
// 触发等级变更事件
event('UserLevelListener', [$uid, $oldLevel, $newLevel]);
```

**事件参数**：
- `$uid` - 用户ID
- `$oldLevel` - 变更前等级
- `$newLevel` - 变更后等级

### 7.2 经验变动通知

```php
// 经验变动通知（可选）
event('NoticeListener', [[
    'user_type' => $user['user_type'],
    'uid' => $uid,
    'number' => $amount,
    'type' => $type,
], 'experience_change']);
```

---

## 八、数据一致性保障

### 8.1 事务使用原则

1. **必须使用事务**：经验修改和流水记录必须在同一事务中
2. **异常回滚**：任何一步失败都应回滚所有操作
3. **状态检查**：操作前检查用户状态和经验值

### 8.2 幂等性保障

```php
// 避免重复处理（以签到为例）
$today = date('Y-m-d');
$count = $userBillServices->count([
    'category' => 'experience', 
    'type' => 'sign_exp', 
    'uid' => $uid,
    'add_time' => ['>', strtotime($today)]
]);
if (!$count) {
    // 执行签到操作
}
```

---

## 九、最佳实践

### 9.1 经验操作规范

| 场景 | 推荐方法 | 说明 |
|------|---------|------|
| 增加经验 | `transactionExperience('add', ...)` | 使用事务方法 |
| 减少经验 | `transactionExperience('sub', ...)` | 使用事务方法 |
| 批量操作 | 使用 `saveAll()` | 批量写入流水 |
| 等级检查 | 在增加经验后调用 `checkLevelUpgrade()` | 自动处理升级 |

### 9.2 性能优化建议

1. **缓存等级配置**：减少数据库查询
2. **异步处理非关键逻辑**：使用队列处理通知等非核心操作
3. **批量更新**：合并多次经验变动为一次操作

---

**版本**: v6.0
**创建日期**: 2026-06-05
**最后更新**: 2026-06-05
**适用范围**: CRMEB v8
**文档位置**: [.crmeb/skills/crmeb-dev/references/11-业务领域/experience.md](experience.md)
