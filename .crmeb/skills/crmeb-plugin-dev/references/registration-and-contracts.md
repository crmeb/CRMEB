# 模块注册与契约

## 自动发现

`backend/app/AppService.php` 启动插件和模块发现。每个已启用模块的 `App.php` 提供静态 `register(think\App $app)` 和 `boot(think\App $app)` 方法。

`register()` 用于注册 Handler、注册中心、容器绑定、命令或其他使用前必须完成的配置。`boot()` 用于引入 `event.php` 并调用 `$app->loadEvent()`。

不得向 `app/AppService.php` 添加具体模块引用或模块注册清单。

## 产品类型注册

产品模块通常需要注册三个维度：

```php
$app->make(ProductTypeRegistry::class)->register('ticket', Handler::class);
OrderTypeRegistry::register('ticket_order', TicketOrderAdapter::class);
AfterSaleRuleRegistry::registerProductType('ticket', TicketAfterSaleHandler::class);
```

主 `Handler` 继承 `BaseProductTypeHandler`，按需提供类型身份、订单 source type、兼容性、SKU/库存行为、商品保存/详情行为和订单生命周期钩子。

订单 Adapter 提供状态树、支付/业务状态、按钮、进度和来源 Model 映射。售后 Handler 声明可用类型、时间/履约限制和退款行为。遗漏必要注册可能导致核心错误回退到其他产品或订单类型。

使用 `ProductDetailExtend`、`OrderCenterListExtend`、`OrderCenterDetailExtend`、计价和售后扩展事件丰富共享载荷，不得修改中央类型分支。

## 交易模式注册

从容器获取单例：

```php
$app->make(TradeModeRegistry::class)
    ->register('seckill', SeckillActivityHandler::class);
```

继承 `BaseTradeModeHandler`，只覆盖实际支持的钩子。校验失败必须阻止非法购买；只有现有基础契约明确允许时，可选展示/数据丰富失败才能降级。

使用独立订单的交易模式还需要注册 `OrderTypeRegistry` Adapter。复用普通商品订单的活动型模式不得虚构 source type。

交易模式负责活动 unit price 和校验，不得把模式专属分支插入 app 层中央订单 Service。

## 事件驱动模块

`event.php` 返回 ThinkPHP 事件配置：

```php
return [
    'listen' => [
        'OrderPaySuccess' => [
            \modules\marketing\example\listener\OrderPaidListener::class,
        ],
    ],
];
```

事件使用业务字符串 key 和明确载荷。监听器为代理查询返回数据时，参考 agent/channel 代理监听器定义白名单和稳定请求/响应结构。扩展共享数据时，只按引用传递有意开放的字段，并写入带命名空间的扩展 key。

监听器必须能处理模块的可选状态。除非事件属于必需校验步骤，否则不能因扩展异常让无关核心请求直接失败。

## 路由与控制器

模块路由从 `route/admin.php`、`route/user.php` 和可选 `route/manage.php` 自动扫描。每个文件导入 `think\facade\Route`，指向控制器类/方法，并声明全部所需中间件。

Admin 路由组通常包含跨域、admin token、角色和日志中间件。用户路由沿用相近模块对应的 API 初始化/认证中间件。路由路径、Installer `api_url`、HTTP 方法、前端 API 函数和控制器 camelCase 方法必须一致。

控制器文件名和类名以 `Controller` 结尾。保持控制器轻量，并为注入的 Service 属性声明 `@var` PHPDoc。

## 持久化与异步任务

使用标准目录和命名：

- `service/XxxServices.php`
- `dao/XxxDao.php`
- `model/XxxModel.php`，或相近领域已经采用的 Model 后缀
- `validate/XxxValidate.php`
- `jobs/XxxJob.php`
- `crontab/XxxTask.php`

普通持久化使用 DAO/Model API。模块 Installer 管理模块表、种子、配置、菜单和清理。保持整数时间戳及可配置表前缀/字符集。

队列任务放在复数 `jobs/`。队列载荷应包含稳定标识，不得序列化 Service 或 Model 实例。定时任务必须幂等，并且重试安全。

## 定时任务

模块定时任务通过核心注册中心接入 `php think timer` 调度：

1. 任务类放在 `crontab/`，实现 `crmeb\crontab\CrontabTaskInterface` 的四个方法：`getMark()`（任务标识，驼峰命名，`customTimer` 前缀为系统自定义任务保留）、`getName()`、`getDescription()`、`handle()`。参考 `modules/marketing/signin/crontab/SignRemindTask.php`。
2. 在模块 `App::register()` 中注册：

   ```php
   $app->make(CrontabTaskRegistry::class)->register('signRemind', SignRemindTask::class);
   ```

3. 需要被调度的任务必须在 `system_crontab` 表有一条记录（`mark` 与注册标识一致、周期 `type`、`is_open=1`），由 Installer `seed()` 幂等写入，卸载时清理；缺失记录时任务不会被 timer 进程加载。
4. `CrontabRunServices::run()` 按 mark 优先分发注册中心任务，同名 mark 可接管系统本地方法（如 `product/normal` 的 `NormalOrderCancelTask` 接管 `orderCancel`）；接管前确认原种子记录的 mark 保持不变。

执行环境约束（真源 `backend/crmeb/crontab/` 与 `backend/app/services/system/crontab/`）：任务经 `pcntl_fork` 派发到独立子进程执行；同一 mark 上一次未结束时跳过本次触发；单任务最长 600 秒超时强制终止；并发子进程上限 5。`handle()` 必须幂等、可安全重入，内部捕获业务异常并按 `log.timer_log` 开关写日志。无法常驻 timer 进程的部署环境存在 `crontabApiRun()` API 兜底链路，行为要求相同。

## 前端契约

- 可移除的 Admin UI 位于 `frontend/admin/src/pages/plugin/<key>`，并与插件菜单权限一致。UMD 构建与部署见 [admin-extension.md](admin-extension.md)。
- Uni-app 订单页面消费 Adapter 生成的语义状态、按钮、`page_config.sections` 和 extensions。
- 商品详情消费后端 `display_sections` 以及通用商品/SKU 字段。

新增产品类型或交易模式时，优先向现有前端契约添加数据，不修改核心前端。只有现有组件体系无法表达全新交互时才添加前端基础组件。

## 领域验证

| 模块类型 | 关键流程 |
|---|---|
| 产品 | 创建/编辑商品、详情、SKU/库存、购物车、下单、支付、取消、履约、售后 |
| 交易 | 创建活动、资格、价格、限额/库存、下单/支付/取消、生命周期过期、状态展示 |
| 营销 | 触发事件、幂等奖励、限制、记录、通知、禁用状态 |
| 渠道 | 客户端检测、认证/登录、凭据、SDK 失败、代理调用、通知、禁用状态 |
| 分销 | 关系变更、佣金快照/计算、结算、列表/详情扩展 |

同时验证安装/启用/禁用/卸载、路由到控制器解析、事件注册、菜单权限，并确保不存在 `app -> modules` 或同级模块直接引用。
