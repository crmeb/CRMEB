# CRMEB 队列任务目录

## 一、概述

`crmeb/app/jobs/` 目录是 CRMEB 项目队列任务类的代码目录，负责所有异步任务的编写和调度。

## 二、核心概念

### 2.1 队列任务作用

| 功能 | 说明 |
|------|------|
| **异步处理** | 将耗时任务放入队列，不阻塞主线程 |
| **延迟处理** | 指定任务在一定时间后执行（如短信提醒） |
| **分布式处理** | 任务可分布到不同服务器处理 |
| **失败重试** | 支持自动重试机制，保证任务可靠性 |

### 2.2 架构设计

```
┌─────────────────────────────────────────────────────────────────┐
│                        队列任务架构                            │
├─────────────────────────────────────────────────────────────────┤
│                                                               │
│  业务代码                                                       │
│     │                                                          │
│     ▼                                                          │
│  XxxJob::dispatch([$data])                                     │
│     │                                                          │
│     ├─ 队列模式（Redis）                                        │
│     │     ↓                                                    │
│     │  Redis 队列                                               │
│     │     ↓                                                    │
│     │  php think queue:listen                                   │
│     │     ↓                                                    │
│     │  XxxJob->fire() → XxxJob->doJob()                        │
│     │                                                          │
│     └─ 同步模式（无队列）                                        │
│           ↓                                                    │
│        XxxJob->doJob() 直接执行                                 │
│                                                               │
└─────────────────────────────────────────────────────────────────┘
```

### 2.3 基类说明

所有任务类继承自 `BaseJobs`：

```php
use crmeb\basic\BaseJobs;

class MyJob extends BaseJobs
{
    public function doJob($param1, $param2)
    {
        // 业务逻辑
    }
}
```

## 三、目录结构

```
jobs/
├── notice/           # 通知类任务
│   ├── EnterpriseWechatJob.php   # 企业微信消息
│   ├── PrintJob.php              # 小票打印
│   ├── SmsJob.php                # 短信发送
│   └── SyncMessageJob.php        # 消息同步
├── DistributionJob.php                  # 代理相关任务
├── AutoCommentJob.php            # 自动评价
├── CheckQueueJob.php             # 队列检查
├── LiveJob.php                   # 直播相关
├── MiniOrderJob.php              # 小程序订单
├── OrderExpressJob.php           # 订单物流
├── OrderInvoiceJob.php           # 订单开票
├── OrderJob.php                  # 订单任务
├── OfflineReceiptJob.php         # 线下收银
├── OutPushJob.php                # 外部推送
├── PinkJob.php                   # 拼团任务
├── PosterJob.php                 # 海报生成
├── ProductCopyJob.php            # 商品复制
├── ProductLogJob.php             # 商品日志
├── ProductStockJob.php           # 商品库存
├── RefundOrderJob.php            # 退款订单
├── OrderReceiveJob.php           # 订单自动收货
├── TaskJob.php                   # 定时任务
├── TemplateJob.php               # 模板消息
├── ThemeExportJob.php            # 主题导出
├── TranslateJob.php              # 翻译任务
├── UnpaidOrderCancelJob.php      # 未支付订单取消
├── UnpaidOrderSend.php           # 未支付订单提醒
├── UpgradeJob.php                # 升级任务
├── UserJob.php                   # 用户相关任务
└── README.md                     # 本说明文档
```

## 四、创建新任务

### 4.1 步骤

1. **创建任务类**：继承 `BaseJobs`
2. **实现 doJob 方法**：编写业务逻辑
3. **调用任务**：使用 `dispatch()` 方法

### 4.2 示例

```php
// 1. 创建任务类
class MyJob extends BaseJobs
{
    public function doJob($data)
    {
        // 业务逻辑
        Log::info('执行任务: ' . json_encode($data));
        return true;
    }
}

// 2. 调用任务
MyJob::dispatch([$data]);                    // 立即执行（队列模式）或同步执行
MyJob::dispatch('customMethod', [$arg1]);    // 调用指定方法
MyJob::dispatchSecs(60, 'doJob', [$data]);   // 延迟 60 秒执行
```

## 五、任务调用方式

### 5.1 基本调用

| 方式 | 说明 |
|------|------|
| `Job::dispatch([$data])` | 数组参数，调用 `doJob` 方法 |
| `Job::dispatch('method', [$args])` | 字符串方法名，调用指定方法 |
| `Job::dispatchSecs($secs, 'method', [$args])` | 延迟执行 |

### 5.2 队列模式 vs 同步模式

```php
// 配置决定执行模式
// config/queue.php 或系统配置 'queue_open'

// 队列模式（推荐生产环境）
// 需要 Redis 缓存驱动
sys_config('queue_open') == 1 && Env::get('cache.driver') == 'redis'
// → 任务加入 Redis 队列，由队列进程异步执行

// 同步模式（开发环境）
// 无 Redis 或队列未开启
// → 任务立即同步执行
```

## 六、注意事项

### 6.1 性能优化

- 避免在 `doJob` 中执行长时间阻塞操作
- 大文件处理建议分块处理
- 数据库操作使用批量操作

### 6.2 死循环防护

系统已内置死循环防护机制：

```php
// 在 doJob 中调用同一任务会被拦截
public function doJob()
{
    MyJob::dispatch([]);  // ⚠️ 会被记录警告并跳过（防止死循环）
}
```

### 6.3 队列命令

```bash
# 启动队列监听
php think queue:listen

# 启动队列工作（一次性）
php think queue:work

# 指定队列名
php think queue:listen --queue=high_priority

# 延迟队列
php think queue:listen --delay=0 --memory=128 --timeout=60
```

## 七、任务职责说明

| 任务类 | 职责 |
|--------|------|
| `UserJob` | 用户相关异步任务 |
| `OrderJob` | 订单相关异步任务 |
| `TemplateJob` | 微信模板消息发送 |
| `SmsJob` | 短信发送 |
| `PrintJob` | 小票打印 |
| `ProductLogJob` | 商品访问记录 |
| `UnpaidOrderCancelJob` | 未支付订单自动取消 |
| `PinkJob` | 拼团相关任务 |
| `MiniOrderJob` | 小程序订单同步 |
| `ThemeExportJob` | 主题导出 |

---

**版本**: v1.0  
**创建日期**: 2026-06-03  
**适用范围**: CRMEB v8