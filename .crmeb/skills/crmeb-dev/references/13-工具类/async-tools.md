# 异步与并发工具类使用说明（Queue / Lock / SnowflakeIdGenerator）

> 二开高频三件套：消息队列投递、Redis 分布式锁、唯一单号生成。源码均在 `backend/crmeb/utils/`。

> **强制规范真源在 `.crmeb/rules/性能与缓存规范.md`**：队列 `dispatch()` 的同步降级行为与重试策略、缓存统一走 `CacheService`、并发用 `lock/setMutex`、事务最小化等**判定标准以规则文件为准**。本文只承载「工具类怎么用、源码在哪」的操作细节，不重复规则正文。

---

## 一、Queue 消息队列投递

源码：`backend/crmeb/utils/Queue.php`（链式魔法方法封装 think-queue）。业务侧**推荐经 `crmeb\traits\QueueTrait` 使用**，不直接操作 Queue。

### 1.1 标准用法：Job 类 + QueueTrait（推荐）

```php
<?php
namespace app\jobs\order;

use crmeb\traits\QueueTrait;

class OrderPushJob
{
    use QueueTrait;

    // 可选：指定队列名，默认 null 走默认队列
    protected static function queueName() { return 'order'; }

    // 默认执行方法（->dispatch() 不传方法名时调用 doJob）
    public function doJob(int $orderId)
    {
        // ... 耗时业务
        return true;   // 返回 true 表示任务完成
    }
}

// 投递（任意业务位置）
OrderPushJob::dispatch('doJob', [$orderId]);            // 指定方法
OrderPushJob::dispatch([$orderId]);                      // 数组形式直接传给 doJob
OrderPushJob::dispatchSecs(60, 'doJob', [$orderId]);     // 延迟 60 秒执行
OrderPushJob::dispatch('doJob', [$orderId], 'order');    // 指定队列名
```

### 1.2 关键降级行为（必知）

`QueueTrait` 内部判断（`crmeb/traits/QueueTrait.php:43`）：

```php
if (sys_config('queue_open', 0) == 1 && Env::get('cache.driver', 'redis') == 'redis') {
    // 走异步队列
} else {
    // ★ 降级：直接 new 实例同步执行
}
```

| 条件 | `dispatch()` | `dispatchSecs()` |
|------|-------------|------------------|
| 队列开启（`queue_open=1` 且 cache 为 redis） | 异步执行 | 延迟异步执行 |
| 队列未开启 | **同步直接执行**（功能不丢） | **静默丢弃**（不执行！） |

> ⚠️ 依赖延迟执行的功能（如超时取消）不能只依赖 `dispatchSecs`，需保证生产环境开启队列；本地调试不开启队列时延迟任务不生效。

### 1.3 Queue 原生链式 API

```php
use crmeb\utils\Queue;

Queue::instance()
    ->job(\app\jobs\XxxJob::class)   // 任务类名（必填）
    ->do('doJob')                     // 执行方法，默认 doJob
    ->data($a, $b)                    // 任务参数（可变参数）
    ->errorCount(3)                   // 失败重试次数，默认 3
    ->secs(30)                        // 延迟秒数，>0 走 later
    ->log('日志描述')                  // 失败日志回调/描述
    ->setQueueName('order')           // 队列名
    ->push();                         // 投递（失败自动重投 1 次，再失败记 error 日志）
```

真实先例：`backend/crmeb/basic/BaseJobs.php:53`、`backend/crmeb/traits/QueueTrait.php:44`。

常驻队列进程见 [06-安装部署/install-deploy.md](../06-安装部署/install-deploy.md)（`php think queue:listen` 等）。

---

## 二、Lock Redis 分布式锁

源码：`backend/crmeb/utils/Lock.php`。基于 Redis `SET NX EX`，unlock 用 Lua 脚本校验 value 防误删。

### 2.1 推荐入口：CacheService::lock()

```php
use crmeb\services\CacheService;

$res = CacheService::lock('order_pay_' . $orderNo, function () use ($orderNo) {
    // 临界区业务（如扣库存、支付回调幂等处理）
    return true;
}, 6);   // 锁过期时间（秒），默认 6
```

内部实现（`backend/crmeb/services/CacheService.php:177-184`）：

- cache 驱动为 **file 时直接执行闭包不加锁**（单机文件环境无并发意义）
- redis 时经 `app()->make(Lock::class)->exec($key, $fn, $ex)` 加锁执行，**finally 保证解锁**

### 2.2 Lock 原生方法

| 方法 | 说明 |
|------|------|
| `exec($key, $fn, $ex = 6)` | **推荐**。阻塞加锁（递归重试，usleep 200）→ 执行闭包 → finally 解锁 |
| `tryLock($key, $value = '1', $ex = 6)` | 单次尝试，`SET lock_{key} value NX EX`，成功 true / 被占用 false |
| `lock($key, $value = '1', $ex = 6)` | 阻塞式重试加锁 |
| `unlock($key, $value = '1')` | Lua 校验 value 一致才删除（防误删他人锁） |

### 2.3 典型场景

- 支付回调幂等（防止微信/支付宝重复通知并发处理）
- 秒杀/限量商品扣库存
- 定时任务多实例防重入

---

## 三、SnowflakeIdGenerator 唯一单号生成

源码：`backend/crmeb/utils/SnowflakeIdGenerator.php`。雪花算法生成全局唯一 ID，**底层公共工具、不依赖业务表**，订单中台、充值等统一取号入口。

### 3.1 API

```php
// 静态方法：返回 "前缀 + 雪花ID"
SnowflakeIdGenerator::generate(string $prefix = 'wx'): string;
```

- 唯一性保障：cache 驱动为 redis 时用 `RedisSequenceResolver`；为 file 时用 `FileLockResolver`（锁目录 `runtime/snowflake/`）

### 3.2 使用示例（真实调用）

```php
use crmeb\utils\SnowflakeIdGenerator;

$orderNo  = SnowflakeIdGenerator::generate('wx');   // 订单号（默认前缀 wx）
$refundNo = SnowflakeIdGenerator::generate('tk');   // 退款单号
$rechargeNo = SnowflakeIdGenerator::generate('cz'); // 充值单号
```

真实先例：
- `backend/app/services/user/UserRechargeServices.php:188,287,347`（tk 退款单 / cz 充值单）
- `backend/app/services/order/OrderCenterServices.php:1910`（订单中台统一取号）
- `backend/app/services/activity/integral/StoreIntegralOrderServices.php:277`（积分订单）

### 3.3 规范要点

- 新业务单号一律走 `generate()`，**禁止** `time() + rand()` 拼单号（高并发必重复）
- 前缀自定义时建议 2~4 个小写字母，语义化（如 `wx` 微信订单 / `tk` 退款 / `cz` 充值）
