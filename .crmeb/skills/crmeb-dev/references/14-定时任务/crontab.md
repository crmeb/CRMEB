# 系统定时任务架构（Crontab）

> 本文是 `crmeb-dev` 技能的子文档，二开流程导航见上级 [../../SKILL.md](../../SKILL.md)。内容基于实际代码整理（`backend/app/command/Timer.php`、`backend/app/services/system/crontab/`、`backend/crmeb/crontab/`、`backend/app/listener/crontab/`）。**模块（插件）定时任务的开发步骤**（任务类编写、`App::register()` 注册、Installer 幂等种子）见 [registration-and-contracts.md 的「定时任务」一节](../../../crmeb-plugin-dev/references/registration-and-contracts.md)，本文只讲系统架构与代码定位，不重复操作步骤。

## 一、整体架构：调度与执行分离

```
部署入口（三选一，可并存）
 │
 ├─ php think timer start -d           常驻调度（推荐）
 │    app/command/Timer.php → Workerman 单进程(count=1)
 │    └─ SystemCrontabServices::crontabCommandRun()
 │          ├─ 每秒 tick：刷新心跳文件 runtime/.timer
 │          ├─ 读取 eb_system_crontab（is_open=1 且 is_del=0，进程内缓存 30 秒）
 │          └─ 按 update_time 变化销毁/重建 Workerman\Crontab 触发器（timeStr 六段表达式）
 │                └─ 触发时 → CrontabProcessServices::dispatch()
 │                      ├─ pcntl_fork 子进程（防重入 flock / 并发上限 5 / 600 秒超时 alarm）
 │                      └─ 子进程：重建 DB/Cache 连接 → CrontabRunServices::run()
 │
 ├─ GET /api/crontab/run               API 兜底（无法常驻进程时由外部每分钟请求一次）
 │    └─ SystemCrontabServices::crontabApiRun() 同步执行到期任务
 │
 └─ GET /api/crontab/<单任务端点>       单任务 URL 触发（见第八节）
```

分层职责：

| 层 | 位置 | 职责 |
|----|------|------|
| 进程入口 | `backend/app/command/Timer.php` | `php think timer` 命令 → Workerman 启动调度循环 |
| 调度层 | `backend/app/services/system/crontab/SystemCrontabServices.php` | 任务列表缓存、Cron 表达式换算（`getTimerStr`）、按 `update_time` 热更新触发器 |
| 执行隔离 | `backend/app/services/system/crontab/CrontabProcessServices.php` | fork 子进程、防重入、并发/超时控制、连接重置 |
| 分发层 | `backend/app/services/system/crontab/CrontabRunServices.php` | 生命周期事件包装 + registry/本地方法双通道分发 |
| 任务注册中心 | `backend/crmeb/crontab/CrontabTaskRegistry.php` + `CrontabTaskInterface.php` | mark → 任务类 的注册与解析（core 层，无 app 依赖） |
| 持久层 | `backend/app/model/system/crontab/SystemCrontab.php`（表 `eb_system_crontab`） | 任务配置与执行记录 |
| 管理端 | `backend/app/adminapi/controller/system/SystemCrontabController.php` + `frontend/admin/src/pages/system/crontab/index.vue` | 任务 CRUD、开关、类型列表 |

`backend/crmeb/basic/BaseCrontab.php` 为遗留基类（name/cron/enable/singleton 属性），当前代码中无子类；现行任务类统一实现 `CrontabTaskInterface`。

## 二、调度流程（php think timer）

1. **命令入口**：`php think timer start|stop|restart|status [-d] [-i 秒]`。`Timer::execute()` 创建 `Workerman\Worker`（`count=1` 单进程），pid 文件 `runtime/timer.pid`，`onWorkerStart` 调用 `SystemCrontabServices::crontabCommandRun()`。
2. **每秒 tick**（`crontabCommandRun()` 内 `new Crontab('*/1 * * * * *', ...)`）做三件事：
   - 心跳：`runtime/.timer` 超过 60 秒未刷新则写入当前时间；
   - 任务列表：`getTaskList()` 进程内缓存 30 秒（刻意不读 Cache，避免父子进程共用 redis 连接），后台对任务的增删改最多延迟 30 秒生效；
   - 触发器管理：`functionName = Str::camel(mark)`（`customTimer` 记录变为 `customTimer_{id}`）；记录的 `update_time` 与已注册触发器不一致时销毁重建 `Workerman\Crontab` 实例，实现后台改周期/开关的热更新。
3. **单任务表达式**：`timeStr` 为六段 Cron（秒 分 时 日 月 周），由 `getTimerStr()` 按周期 `type` 1-8 换算并随记录落库。

## 三、执行隔离（CrontabProcessServices）

`php think timer` 是单进程事件循环，任务若在事件循环内同步执行，耗时任务会阻塞其余任务触发。派发服务实现「调度归主进程、执行归子进程」：

| 机制 | 实现 |
|------|------|
| 子进程执行 | `pcntl_fork`，父进程派发后立即返回事件循环；无 pcntl 扩展环境（Windows/受限容器）自动降级为同步执行 |
| 防重入 | 同一 mark 上一次未结束 → `flock`（`runtime/timer_lock/{mark}.lock`，父进程持有、SIGCHLD 回收时释放）跳过本次触发 |
| 并发上限 | 运行中子进程 ≥ 5（`MAX_CONCURRENT`）丢弃本次触发并记日志，等待下轮调度 |
| 超时看护 | 子进程内 `pcntl_alarm(600)`，超时记日志并以退出码 124 强制终止 |
| 连接重置 | fork 复制的 MySQL 连接在子进程内 `Db::connect(null, true)` 强制重建；redis 缓存 handler 主动 close |
| 子进程回收 | `SIGCHLD` 信号 + `pcntl_waitpid(WNOHANG)` 非阻塞回收，递减并发计数并释放锁 |

对业务代码的含义：`handle()` 可能因上述机制被跳过一轮、超时后再次触发，因此**任务必须幂等、可安全重入**。

## 四、分发规则（CrontabRunServices::run）

`run(int $crontabId, string $mark, ...$args)` 是三条执行路径（timer 子进程、API 兜底、单任务端点）的统一出口：

1. 派发 `CrontabBeforeRun` 事件；
2. 双通道分发：`CrontabTaskRegistry` 已注册该 mark → 实例化任务类执行 `handle()`；否则落到 `CrontabRunServices` 的同名本地方法；两者皆无 → 记日志并抛异常；
3. 结束时派发 `CrontabAfterRun` + `CrontabSuccess`/`CrontabFail`（见第六节）。

mark 在调度时经 `Str::camel()` 归一，**任务标识一律使用驼峰命名**。

系统本地 mark（`CrontabRunServices::getLocalMarkList()`，即后台「定时任务类型」下拉的系统部分）：

| mark | 名称 | 实际动作 |
|------|------|----------|
| orderCancel | 未支付自动取消订单 | 本地方法已置空，由 normal 模块任务接管（registry 优先命中） |
| takeDelivery | 订单自动收货 | `OrderTakeServices::autoTakeOrder()` |
| productReplay | 订单商品自动好评 | `OrderCenterServices::autoComment()` |
| clearPoster | 清除昨日海报 | `SystemAttachmentServices::emptyYesterdayAttachment()` |
| autoInvoice | 自动开具/冲红电子发票 | `OrderInvoiceServices::autoInvoice()/autoInvoiceRed()` |
| refundRecover | 退款一致性恢复 | `RefundOrderServices::recover(100)` |
| paymentRecover | 微信支付一致性恢复 | `PayRecordServices::recover()` |
| customTimer | 自定义定时任务 | `eval` 管理员自定义代码（仅 debug + 超管 + `filesystem.password` + 危险词校验，见 `SystemCrontabController::saveTimer()`） |
| noticeUnpaidOrder | 未支付订单提醒 | 触发 `UnpaidOrderNotice` 事件 |
| noticeMemberExpire | 会员到期提醒 | 预留 |

模块已注册的 mark（模块 `App::register()` → registry，现状清单）：

| mark | 模块 | 任务类 |
|------|------|--------|
| orderCancel | `modules/product/normal` | `NormalOrderCancelTask`（接管同名系统任务） |
| signRemind | `modules/marketing/signin` | `SignRemindTask` |
| aiAgentTask | `modules/aiagent` | `AiAgentTaskDispatcherTask` |
| pinkExpiration | `modules/trade/combination` | `PinkExpirationTask` |

**同名接管**：`run()` 中 registry 优先命中，同 mark 的本地方法不再执行。接管系统 mark 前需确认 `eb_system_crontab` 种子记录保持不变。

## 五、数据表 eb_system_crontab

建表见 `backend/public/install/crmeb_table.sql`（「eb_system_crontab：定时器」），种子见 `backend/public/install/data/`（同名注释段）；旧表 `eb_system_timer` 已更名为 `eb_system_crontab`。

关键字段与调度行为的关系：

| 字段 | 说明 |
|------|------|
| `mark` | 任务标识，与注册标识/本地方法名对齐（驼峰）；`customTimer` 前缀为自定义任务保留 |
| `type` | 周期：1=每隔秒 2=每隔分 3=每隔时 4=每隔日 5=每日 6=每周 7=每月 8=每年 |
| `week/day/hour/minute/second/month` | 周期明细参数（配合 `type` 使用） |
| `timeStr` | 换算后的六段 Cron 表达式，timer 路径直接使用 |
| `last_execution_time` / `next_execution_time` | 主要供 API 兜底路径判断到期与回写；timer 路径由 `Workerman\Crontab` 自主触发，不依赖 `next_execution_time` |
| `is_open` / `is_del` | 仅 `is_open=1 且 is_del=0` 的记录进入调度 |
| `update_time` | 触发器热更新的版本号（变化即销毁重建） |
| `customCode` | 自定义任务代码（json 编码存储） |

种子写入必须幂等，现有范式为 `INSERT ... SELECT ... WHERE NOT EXISTS`：系统侧见 `crmeb_data.sql` 的 `refundRecover`，模块侧见 `backend/modules/marketing/signin/Installer.php` 的 `signRemind`。**registry 里注册了 mark 但表中无开启记录时，任务不会被 timer 加载。**

## 六、生命周期事件与日志

`CrontabRunServices::run()` 在执行前后派发字符串事件（绑定见 `backend/app/event.php`，监听器在 `backend/app/listener/crontab/`）：

| 事件 | 参数 | 监听器 |
|------|------|--------|
| `CrontabBeforeRun` | `[$crontabId, $crontabName, $mark]` | `CrontabBeforeRunListener` |
| `CrontabAfterRun` | `[..., $executeTime, $duration]` | `CrontabAfterRunListener` |
| `CrontabSuccess` | 同 AfterRun | `CrontabSuccessListener`（写 info 日志） |
| `CrontabFail` | `[..., $executeTime, $errorMessage]` | `CrontabFailListener` |

过程日志（`crontabLog`）受 `backend/config/log.php` 的 `timer_log` 开关（默认 false）控制，写入 `crontab` 通道；任何路径的执行失败统一 `Log::error`。事件机制的强制约定见 [事件规范](../../../../rules/事件规范.md)。

## 七、部署与健康检查

- 启停：`php think timer start -d`（stop/restart/status），与队列、Workerman 网关并列的后台常驻服务，部署细节见 [install-deploy.md 第六节](../06-安装部署/install-deploy.md)。
- 心跳文件 `runtime/.timer`：
  - timer 进程启动时写入，每秒 tick 内超过 60 秒过期才重写；
  - `crontabApiRun()` 与 `GET /api/crontab/check`（设计为约 6 秒一次的外部探测）也会写入；
  - 管理员登录时 `AdminLoginListener` 校验其 70 秒内新鲜度；`app/adminapi/controller/PublicController.php` 的环境检测据此展示「定时任务：开启」。
- **API 兜底链路**（无法常驻 timer 进程的环境）：外部每分钟请求 `GET /api/crontab/run` → `crontabApiRun()` 遍历 `next_execution_time < now` 的开启任务，同步执行并回写 last/next 时间。行为约束与常驻进程一致（幂等、异常记录）。
- 运行时产物：`runtime/timer.pid`（进程 pid）、`runtime/.timer`（心跳）、`runtime/timer_lock/`（防重入锁，排查"任务不执行"时先看残留锁）。

## 八、单任务 URL 端点（api 层）

`backend/app/api/route/v1.php` 的 `mark=crontab` 分组提供单任务触发端点，控制器为 `app/api/controller/v1/CrontabController.php`。模块能力一律通过 `CrontabTaskRegistry` 或字符串事件代理调用（如 `orderUnpaidCancel()` 走 registry、`pinkExpiration()` 发 `ActivityDataProxy` 事件），**不允许 app 层直接引用 modules 类**。现有端点：`crontab/order_cancel`、`crontab/pink_expiration`、`crontab/distribution_unbind`、`crontab/live_product_status`、`crontab/live_room_status`、`crontab/take_delivery`、`crontab/advance_off`、`crontab/product_replay`、`crontab/clear_poster`，以及总入口 `crontab/run`、探测 `crontab/check`。

## 九、管理后台入口

- 路由：`backend/app/adminapi/route/system.php` 的 `system/crontab/list|mark|info/:id|save|del/:id|set_open/:id/:is_open`；
- 页面：`frontend/admin/src/pages/system/crontab/index.vue`（API 封装在 `frontend/admin/src/api/system.js`）；
- 「定时任务类型」下拉来自 `SystemCrontabServices::getMarkList()` = 系统本地清单 + registry 全量信息（`CrontabRunServices::getMarkList()`），因此模块注册的 mark 会自动出现在后台类型列表。

## 十、开发核对清单

新增或修改定时任务时按序自查：

1. 任务类实现 `crmeb\crontab\CrontabTaskInterface`（四方法：`getMark/getName/getDescription/handle`），放模块 `crontab/` 目录；`getMark()` 驼峰且不与现有 mark 冲突（接管同名 mark 需显式确认）。
2. `eb_system_crontab` 存在同 mark 记录且 `is_open=1`（Installer 幂等种子，卸载时清理）；否则 timer 不会加载。
3. `handle()` 幂等、可安全重入；业务异常先记录日志再按需抛出（抛出才会触发 `CrontabFail`）。
4. 需要过程日志时打开 `log.timer_log`；子进程环境无响应输出，禁止 dd/dump。
5. 排查顺序：`php think timer status` → `runtime/.timer` 新鲜度 → `eb_system_crontab` 的 is_open/mark/timeStr → `runtime/timer_lock/` 残留锁 → 日志（`crontab` 通道与 error）。

模块任务的完整操作步骤（任务类模板、注册代码、种子 SQL）见 [registration-and-contracts.md](../../../crmeb-plugin-dev/references/registration-and-contracts.md)；架构铁律与性能约束见 [架构规范](../../../../rules/架构规范.md)、[性能与缓存规范](../../../../rules/性能与缓存规范.md)。
