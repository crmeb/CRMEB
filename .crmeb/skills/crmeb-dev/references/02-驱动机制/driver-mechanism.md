---
name: driver-mechanism
description: CRMEB 系统驱动机制说明：driver 底座定位与分层依赖、plugin.json 双层清单、eb_driver 统一字典注册、DriverRegistry 运行时读取与生命周期过滤、BaseManager 门面调用链、后台管理层目录与配置存储、插件化驱动原位安装。需要理解驱动如何被发现、注册、启用与调用，或排查驱动不生效时使用。
---

# 系统驱动机制说明

> 本文描述驱动体系的**机制现状与代码定位**：驱动如何被发现、注册、启用、调用。创建新驱动的操作要点见 [crmeb-plugin-dev/references/driver-plugin.md](../../../crmeb-plugin-dev/references/driver-plugin.md)；扩展点选型与分层铁律以[架构规范](../../../../rules/架构规范.md)、[架构规范详解](../../../../rules/架构规范详解.md)为准。

## 一、驱动的定位与分层

`backend/driver/` 是三层架构最底层，只封装与第三方服务的通信细节（协议、鉴权、SDK 适配），不承载业务决策：

```
modules/（业务扩展层）→ app/（统一管理中台）→ crmeb/（核心底层）→ driver/（第三方驱动底座）
```

- 驱动包内禁止访问数据库、禁止调用业务 Services，配置由上层在实例化时注入
- `crmeb/` 层持有门面与注册中心，但门面只按命名空间解析驱动类，不反向感知 app 层
- 判定标准：写的是「怎么和第三方说话」→ driver 层；写的是「业务上怎么办」→ modules 扩展点（完整选型流程见 `backend/driver/README.md` 第四节）

## 二、驱动类型与驱动包结构

现役 10 类驱动，一级目录即类型，目录名与 `eb_driver.driver_type` 一致：

| 类型 | 目录 | 运行基类（crmeb 层） |
|------|------|---------------------|
| 支付 | `driver/pay/` | `crmeb\services\pay\BasePay` + `PayInterface` |
| 短信 | `driver/sms/` | `crmeb\services\sms\BaseSms` |
| 邮件 | `driver/email/` | `crmeb\services\email\BaseEmail` |
| 上传存储 | `driver/upload/` | `crmeb\services\upload\BaseUpload` |
| 物流查询 | `driver/logistics/` | `crmeb\services\logistics\BaseExpress` |
| 电子面单 | `driver/express/` | `crmeb\services\express\BaseExpress` |
| 发票 | `driver/invoice/` | `crmeb\services\invoice\BaseInvoice` |
| 云打印 | `driver/printer/` | `crmeb\services\printer\BasePrinter` |
| 机器人通知 | `driver/bot/` | `crmeb\services\bot\BaseBot` |
| 商品采集 | `driver/copyproduct/` | `crmeb\services\copyproduct\CopyProduct` |

每个驱动包是自包含目录，标准结构：

```
driver/{type}/{DriverName}/
├── Driver.php      # 驱动实现类（类名默认 Driver，继承上表基类）
├── plugin.json     # 双层清单（发现与注册的唯一元数据来源）
├── Installer.php   # 可选：需要安装态动作时提供（原位生效，无文件复制）
└── extend/         # 可选：大体积 SDK 隔离目录（如 upload/Cos/extend/cos/）
```

`plugin.json` 双层结构（全量示例见 `backend/driver/README.md` 3.2 节）：

- **顶层**为插件元信息：`key`/`name`/`type: "driver"`/`path`/`version`/`require`/`default_install` 等，由 Installer 生命周期消费
- **`driver` 子对象**承载驱动全量描述：`driver_key`（唯一标识，必须与顶层 `key` 一致）、`name`、`class_name`、`icon`、`is_builtin`、`sort`、`capabilities`（能力声明）、`config_fields`（后台动态表单定义）、`help_doc`，以及各类型扩展字段（pay 的 `channel_key`/`pay_type_map`、upload 的 `domain_template`/`bucket_fields`/`thumb_watermark_config`、copyproduct 的 `platforms`）
- 注册中心对 `driver` 子对象**全量透传**，不维护字段白名单

## 三、核心组件一览

| 组件 | 代码位置 | 职责 |
|------|---------|------|
| 门面基类 | `backend/crmeb/basic/BaseManager.php` | 运行时门面：按名称解析并实例化驱动类，`__call` 代理调用 |
| 类型门面 | `backend/crmeb/services/{type}/{Type}.php` | `pay/Pay`、`sms/Sms`、`email/Email`、`upload/Upload`、`bot/Bot`、`invoice/Invoice`、`logistics/Express`、`printer/Printer`（8 个；无门面的类型由 app 层服务直调） |
| 驱动基类 | `backend/crmeb/services/{type}/Base*.php` | 驱动运行基座，继承 `crmeb\basic\BaseStorage`，构造后回调 `initialize(array $config)` |
| 注册中心 | `backend/crmeb/services/driver/DriverRegistry.php` | 运行时驱动字典：读 `eb_driver` 表并过滤插件生命周期状态 |
| 状态检查器 | `backend/crmeb/plugin/ModuleStateChecker.php` | 批量判断插件是否「已安装且已启用」（`eb_module_install_log`） |
| 清单仓库 | `backend/crmeb/plugin/PluginManifestRepository.php` | 安装期发现：glob 扫描 `plugin.json` 并解析为 `PluginManifest` |
| 生命周期安装器 | `backend/crmeb/module/BaseModuleInstaller.php`、`backend/crmeb/plugin/ManifestDriverInstaller.php` | 安装/卸载/启停编排；驱动安装时写入 `eb_driver` 字典 |
| 模块安装器（app 层） | `backend/app/services/system/module/ModuleInstaller.php` | 对外编排入口：批量安装、`installBundledDrivers()`、启停与卸载 |

## 四、注册机制（安装期 → 运行时字典）

驱动的元数据流：**plugin.json（磁盘）→ eb_driver（字典表）→ DriverRegistry（运行时读取）**。目录只在安装前被发现，运行时不再扫描目录。

### 4.1 发现：PluginManifestRepository

`PluginManifestRepository::all()` 通过 glob 扫描清单：

- `modules/{1~3 层}/plugin.json` + `driver/*/*/plugin.json`
- 复合标识（`canonicalId`，形如 `driver:sms:yunxin`）重复时抛异常；运行时单个清单损坏仅 `error_log` 并跳过，不阻断其他驱动（安装/升级入口则保持严格校验）
- `drivers(?category)` 返回 `kind === 'driver'` 的清单集合

### 4.2 安装：写生命周期日志 + eb_driver 字典

两条安装路径，最终都走 `BaseModuleInstaller::install()`：

1. **随系统安装**：`ModuleInstaller::installBundledDrivers()` 只安装 `default_install=true` 的驱动（多为内置驱动）；未标记的驱动（需单独申请密钥的第三方服务）由后台按需手动安装
2. **后台手动安装**：管理端插件管理触发 `ModuleInstaller::install($pluginId)`

驱动类型的 Installer 有两种形态：

- **通用形态** `ManifestDriverInstaller`：无独立迁移、无菜单，仅消费 plugin.json 元数据（绝大多数内置驱动走此形态）
- **自定义形态**：驱动包内自带 `Installer.php`（继承 `BaseModuleInstaller`，如 `driver/sms/yunxin/Installer.php`），可执行安装态动作；`getType()` 必须返回 `'driver'`

安装时 `BaseModuleInstaller::installDriverRecord()`（约 [L1329](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/crmeb/module/BaseModuleInstaller.php#L1329)）把 plugin.json 的 `driver` 子对象写入 `eb_driver`：

- `namespace = 'driver\\' . category . '\\' . 目录名`（如 `driver\sms\yunxin`）
- `owner_plugin_id = canonicalId`（驱动记录与插件实例的归属绑定）
- `status` 初始值 = `is_builtin ? 1 : 0`
- `ON DUPLICATE KEY UPDATE` 覆盖元数据列但**不覆盖 `status`**——重装/升级保留管理员的启用状态

### 4.3 eb_driver 统一驱动字典

历史沿革：2026-08 起 8 张分类型驱动表合并为统一 `eb_driver`，当前建表语句见 `backend/public/install/crmeb_table.sql`。要点：

- 唯一键 `uk_type_driver (driver_type, driver_key)`；索引 `idx_status_sort`、`idx_owner_plugin_id`
- `namespace`/`owner_plugin_id` 两列由安装流程维护，是「字典记录 ↔ 插件实例」的关联键
- 大字段均为 JSON：`capabilities`、`config_fields`、`help_doc`、`bucket_fields`、`thumb_watermark_config`、`platforms`
- **支付不在 `eb_driver` 体系内**：支付方式独立使用 `eb_pay_channel` 字典（`channel_key` 唯一，含 `driver` 列、`config_fields`、平台关联表 `eb_pay_channel_platform`），由 `app/services/driver/pay/` 系列服务管理

### 4.4 运行时读取：DriverRegistry + ModuleStateChecker

`DriverRegistry::scan($type)` 的过滤管线：

1. 查 `eb_driver` where `driver_type = $type`，按 `sort desc, id asc` 排序
2. 逐行校验 `owner_plugin_id`：为空（历史无归属记录）或 `ModuleStateChecker::isDriverActive()` 为 false 的记录**直接跳过**
3. JSON 字段解码 + 标准键归一化补默认值（`class_name=Driver`、空数组、`sort=0` 等），附加 `dir_name`/`plugin_id`/`plugin_key`/`namespace`
4. 返回 `[driver_key => 信息数组]`，写入进程内静态缓存（同请求不重复查库）

`ModuleStateChecker::isActive($moduleKey)` 的判定依据是 `eb_module_install_log` 中 `status=1 AND enabled=1` 的 `module_key` 集合：

- 集合懒加载 + 进程内缓存，一次请求只查一次库
- 安装日志表不存在时**兜底放行**（返回 true），避免迁移未跑导致系统崩溃
- 驱动与普通模块统一使用同一张日志表、同一套判定，`isDriverActive()` 只是语义别名

配套 API：`DriverRegistry::get($type, $key)`（单驱动信息，无则 null）、`getDriverClass($type, $key)`（返回 `namespace\class_name`，无则 null）。

### 4.5 生命周期联动与缓存重置

插件安装/卸载/启用/禁用（`ModuleInstaller` 的 `install`/`uninstall`/`enable`/`disable` 及批量方法）完成后统一执行：

```php
ModuleStateChecker::reload();     // 清活跃模块 key 缓存
DriverRegistry::resetCache();     // 清驱动字典缓存
```

因此驱动被禁用/卸载后，下一次 `scan()` 立即不可见，无需重启。

## 五、调用机制（运行时）

### 5.1 门面调用链

业务代码通过容器取得类型门面并传参：

```php
$sms = app()->make(Sms::class, [$driverKey, $config]);
$sms->send($phone, $smsId, $data);   // __call 代理到驱动实例
```

`BaseManager` 内部流程（`backend/crmeb/basic/BaseManager.php`）：

```
__call($method, $arguments)
  → driver()                        # 名称优先级：显式传参 > $this->name > getDefaultDriver()
  → getDriver($name)                # 门面实例级缓存 $this->drivers[$name]
  → createDriver($name)
      → create{Studly}Driver()      # 门面可定义同名工厂方法优先接管
      → resolveClass($type)         # 解析完整类名
      → invokeClass($class)         # 容器 invokeClass，注入 [name, config, configFile]
  → $driver->{$method}(...$arguments)
```

关键细节：

- **构造参数的弹性**：`invokeClass` 只显式传 `[name, config, configFile]` 三个参数，驱动构造函数中其余带类型提示的依赖（如 `BaseSms` 的 `AccessTokenServeService`）由容器自动解析——因此各类型驱动基类的构造签名可以不同
- **默认驱动**：`getDefaultDriver()` 由各门面实现，如 `Pay` 读 `Config::get('pay.default', 'wechat_pay')`
- **类解析兼容两种目录结构**：`Pay` 门面重写 `resolveClass()`，优先子目录 `driver\pay\{Studly}\Driver`（新结构），兼容平级 `driver\pay\{Studly}`（旧结构）；其余门面使用 `BaseManager` 默认解析（`namespace + Str::studly($type)`）
- **配置文件名推导**：`configFile` = 门面类名小写（反射获取），用于 `setHandleType()` 按整数索引取 `config/{configFile}.stores` 中的驱动名（兼容旧式按序号选驱动）

### 5.2 端到端示例：短信发送

以「消息通知 → 短信渠道」为例，完整链路覆盖了字典、门面、驱动三层（`backend/app/services/message/channel/SmsChannel.php`）：

```
SmsChannel::send()
  → SmsDriverServices::getEnabledDriver()      # eb_driver + driver_type='sms' + status=1（互斥，至多一条）
  → getDriverConfig($driver)                   # config_fields[].{field,value} → 扁平配置数组
  → app()->make(Sms::class, [$driverKey, $config])
  → BaseManager::__call('send', ...) 
  → driver\sms\{DriverName}\Driver::send()     # 驱动实例，仅与第三方 API 通信
```

管理端也有不经字典直接指定驱动的用法（如一号通开放平台接口）：

```php
app()->make(Sms::class, [yihaotong_config()])->setSign($sign)->open();
```

### 5.3 缓存层级汇总

| 缓存 | 位置 | 粒度 | 失效方式 |
|------|------|------|---------|
| 活跃模块 key | `ModuleStateChecker::$activeKeys` | 进程/请求 | `reload()`（生命周期动作后自动调用） |
| 驱动字典 | `DriverRegistry::$cache[type]` | 进程/请求 | `resetCache()`（生命周期动作后自动调用） |
| 清单集合 | `PluginManifestRepository::$manifests` | 实例 | 重新实例化仓库 |
| 驱动实例 | 门面 `$this->drivers[$name]` | 门面实例 | 门面实例回收（容器内多为单例，注意配置变更后需重新 make 并传新 config） |

## 六、后台管理层

### 6.1 目录映射

驱动后台管理代码按类型归档（以 pay 为例，规则详见[架构规范详解「第三方驱动后台管理目录规范」](../../../../rules/架构规范详解.md)）：

| 层 | 路径 |
|----|------|
| 控制器 | `app/adminapi/controller/driver/{type}/`（继承 `AuthController`） |
| 路由 | `app/adminapi/route/driver.php`，分组 `option(['parent' => 'driver', 'cate_name' => '...'])` |
| Services | `app/services/driver/{type}/`（继承 `BaseService`） |
| DAO | `app/dao/driver/{type}/` |
| Model | `app/model/driver/{type}/` |

### 6.2 模型模式：同一张表，按类型分模型

合并后所有驱动模型都指向 `eb_driver` 表，靠**全局作用域**隔离类型。以 [SmsDriver.php](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/app/model/driver/sms/SmsDriver.php) 为例：

- `DRIVER_TYPE = 'sms'` 常量 + `scopeDriverType` 全局作用域：查询自动追加 `where driver_type='sms' AND owner_plugin_id <> ''`（管理后台只呈现已安装、有归属的驱动）
- 插入/更新自动填充 `driver_type`
- `config_fields` 访问器自动 JSON 编解码
- 另有通用模型 `app/model/driver/Driver.php`（不带类型作用域），供跨类型场景（如 MCP 驱动管理工具）使用

### 6.3 管理服务模式

各类型管理服务（`app/services/driver/{type}/*DriverServices.php`）呈现一致的方法面，以 [SmsDriverServices.php](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/app/services/driver/sms/SmsDriverServices.php) 为例：

| 方法 | 行为 |
|------|------|
| `getList()` | DB 记录 ∪ `DriverRegistry::scan()` 结果；目录中有而字典无的条目标记 `not_synced` 展示 |
| `getDetail()` | 字典记录详情；`config_fields` 为空时回退从扫描结果取字段定义 |
| `saveConfig()` | 配置值写入 `config_fields` JSON 各字段的 `value` 属性 |
| `updateStatus()` | 状态切换；短信/邮件为**互斥模式**（`disableAllExcept`，启用一个自动禁用其余）；上传等为并存模式 |
| `syncDrivers()` | 以扫描结果为准做增/改/删同步；更新时保留已有 `value` 与 `status` |
| `getEnabledDriver()` | 供运行时取当前启用驱动（短信互斥取一条；上传可多条） |

### 6.4 配置存储规则

| 场景 | 存储位置 |
|------|---------|
| 通用驱动配置 | `eb_driver.config_fields` JSON 的 `value` 属性（后台按 `config_fields` 定义动态渲染表单） |
| 支付渠道配置 | `eb_pay_channel`（渠道维度）+ `eb_pay_channel_platform`（各端 pay_type），不进 `eb_driver` |
| 一号通账号 | `sys_config`（`yihaotong_appid`/`yihaotong_appsecret`）；驱动管理页将其渲染为只读并自动回填 |
| 上传通用配置 | `eb_driver`（upload 类型，含 `thumb_watermark_config` 等专属列） |

驱动配置一律不入 `eb_system_config`（强制约束见[架构规范](../../../../rules/架构规范.md)）。

## 七、插件化驱动（原位生效）

插件化驱动与底座驱动共用同一套注册、调用、后台管理机制，唯一差异是**随插件生命周期进出**：

- **原位生效**：驱动目录即安装位置（`driver/{type}/{DriverName}/`），安装/启停**不做文件复制**（历史版本曾将 `modules/drivers/` 下开发的驱动复制部署到 `driver/`，现已废弃该模式）
- 简单驱动直接使用通用 `ManifestDriverInstaller`（如需参考自带 Installer 的写法，见 `driver/sms/yunxin/Installer.php`：仅记录生命周期日志，无建表、无菜单）
- 卸载时 `BaseModuleInstaller::removeDriverRecord()` 删除字典记录：按 `owner_plugin_id` 删除，并兼容清理升级前 `owner_plugin_id=''` 的旧归属记录
- 禁用（`enabled=0`）不删记录，仅让 `ModuleStateChecker` 过滤掉，`eb_driver.status` 保留

## 八、验证与排障

### 快速验证

```bash
# tinker / 临时路由中执行
\crmeb\services\driver\DriverRegistry::scan('sms');                 // 字典 + 生命周期过滤后的可用驱动
\crmeb\services\driver\DriverRegistry::getDriverClass('sms', 'aliyun'); // driver\sms\Aliyun\Driver
\crmeb\plugin\ModuleStateChecker::activeKeys();                     // 当前已安装且已启用的插件 key
```

### 常见问题定位

| 现象 | 排查点 |
|------|--------|
| `scan()` 看不到驱动 | ① `eb_driver` 无记录 → 未安装，检查 `eb_module_install_log` 与 `installBundledDrivers()`/手动安装；② `owner_plugin_id` 为空或对应插件未启用；③ `driver_type` 与目录名不一致 |
| `getDriverClass()` 返回 null | `driver_key` 拼写不一致；注意 `plugin.json` 顶层 `key` 必须与 `driver.driver_key` 一致 |
| 实例化报 class not exists | `namespace` 规则为 `driver\{type}\{目录名}`，目录名与 `Driver.php` 类名（默认 `Driver`）不匹配；composer dumpautoload 后重试 |
| 后台列表出现 `not_synced` | 目录存在但 `eb_driver` 无字典记录 → 执行 `syncDrivers()` 或走插件安装 |
| 启用了驱动但运行时仍走旧通道 | 短信/邮件互斥模式下确认旧驱动 `status` 已被 `disableAllExcept` 置 0；检查门面调用处传入的 `driverKey` |
| 生命周期动作后数据未刷新 | 确认动作走的是 `ModuleInstaller` 编排（其内部会调用两个缓存 reset）；自行写库不会触发缓存重置 |
| 支付相关查 `eb_driver` 无果 | 支付方式走 `eb_pay_channel` 字典体系，与 `eb_driver` 平行 |

### 检查清单（改动驱动体系时）

- [ ] 新增驱动：`plugin.json` 双层结构完整、`driver_key` 唯一、`Driver.php` 继承对应基类（操作要点见 [driver-plugin.md](../../../crmeb-plugin-dev/references/driver-plugin.md)）
- [ ] 新增驱动类型：`eb_driver.driver_type` 注释、门面（如需）、驱动基类、`app/services/driver/{type}/` 管理四层同步补齐
- [ ] 修改 `installDriverRecord()`/`removeDriverRecord()` 时确认 `eb_driver` 列集合与 `crmeb_table.sql`、增量迁移三者一致
- [ ] 涉及启停/卸载的逻辑变更后，保留 `ModuleStateChecker::reload()` + `DriverRegistry::resetCache()` 调用
- [ ] 支付能力变更同步检查 `eb_pay_channel` / `eb_pay_channel_platform` 与 `app/services/driver/pay/`

## 九、相关文档

- 选型与开发指南：`backend/driver/README.md`（底座总览）、各 `backend/driver/{type}/DEVELOPMENT.md`（类型级字段表与接口规范）
- 插件清单、驱动接入与生命周期：[crmeb-plugin-dev/references/manifest-and-lifecycle.md](../../../crmeb-plugin-dev/references/manifest-and-lifecycle.md)
- 系统插件管理机制（驱动作为插件的清单校验、身份、安装升级、数据表视角）：[01-插件机制/module-mechanism.md](../01-插件机制/module-mechanism.md)
- 强制约束：[架构规范](../../../../rules/架构规范.md)、[架构规范详解](../../../../rules/架构规范详解.md)、[数据库规范](../../../../rules/数据库规范.md)
