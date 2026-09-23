# 系统插件管理机制（Module Registry / Installer 生命周期 / 清单与身份）

> 本文是 `crmeb-dev` 技能的子文档，完整二开流程导航见上级 [../../SKILL.md](../../SKILL.md)。
> 本文描述**系统如何管理插件**（发现、注册、状态判定、安装/卸载/启用/禁用、上传升级）的现状与代码定位；
> **如何开发/打包一个插件**见 [crmeb-plugin-dev 技能](../../../crmeb-plugin-dev/SKILL.md) 及其 [清单与生命周期手册](../../../crmeb-plugin-dev/references/manifest-and-lifecycle.md)。

## 一、总体架构：两条主线

插件管理由**运行时加载**与**后台生命周期管理**两条主线组成，通过 `plugin.json` 与 `eb_module_install_log` 衔接：

```
运行时加载（每次请求启动）                     后台生命周期管理（低频操作）
──────────────────────────                  ──────────────────────────────
AppService::boot                             PluginController（HTTP 入口）
  ├─ PluginManager::register                   └─ ModuleInstaller（app 层调度器）
  │    └─ ExtensionRegistry::discover               ├─ PluginManifestRepository（清单发现）
  │      （只读元数据，不加载业务代码）                ├─ PluginDependencyResolver（依赖图）
  └─ ModuleRegistry::registerAll                     └─ BaseModuleInstaller（crmeb 层生命周期基类）
       ├─ 扫描 modules/**/App.php                          │
       ├─ ModuleStateChecker::isActive ◄───────────────────┘ 写 status/enabled
       │  （查 eb_module_install_log 白名单）                    到 module_install_log
       └─ 执行 App::register() + App::boot()
```

### 核心组件与代码定位

| 组件 | 代码位置 | 职责 |
| --- | --- | --- |
| 模块注册中心 | [backend/crmeb/plugin/ModuleRegistry.php](../../../../../backend/crmeb/plugin/ModuleRegistry.php) | 启动期扫描 `modules/**/App.php`，执行 `register()/boot()` |
| 安装状态检查器 | [backend/crmeb/plugin/ModuleStateChecker.php](../../../../../backend/crmeb/plugin/ModuleStateChecker.php) | 查询 `eb_module_install_log` 白名单（`status=1 AND enabled=1`），进程内缓存 |
| 清单仓库 | [backend/crmeb/plugin/PluginManifestRepository.php](../../../../../backend/crmeb/plugin/PluginManifestRepository.php) | 全系统 `plugin.json` 的**唯一发现入口** |
| 清单模型 | [backend/crmeb/plugin/PluginManifest.php](../../../../../backend/crmeb/plugin/PluginManifest.php) | `plugin.json` 统一只读模型 + 严格校验 |
| 复合身份 | [backend/crmeb/plugin/PluginIdentity.php](../../../../../backend/crmeb/plugin/PluginIdentity.php) | `canonicalId = {kind}:{category}:{key}`，`legacyKey` 兼容旧标识 |
| 依赖解析器 | [backend/crmeb/plugin/PluginDependencyResolver.php](../../../../../backend/crmeb/plugin/PluginDependencyResolver.php) | 依赖图校验、版本约束、正反向生命周期保护 |
| 路由加载器 | [backend/crmeb/plugin/ModuleRouteLoader.php](../../../../../backend/crmeb/plugin/ModuleRouteLoader.php) | 按场景加载模块路由文件，未激活模块路由不加载 |
| 插件管理门面 | [backend/crmeb/plugin/PluginManager.php](../../../../../backend/crmeb/plugin/PluginManager.php) + [ExtensionRegistry.php](../../../../../backend/crmeb/plugin/ExtensionRegistry.php) | 供后台管理列表使用的元数据注册中心（不加载业务代码） |
| 通用驱动安装器 | [backend/crmeb/plugin/ManifestDriverInstaller.php](../../../../../backend/crmeb/plugin/ManifestDriverInstaller.php) | 无独立迁移/菜单的纯配置型驱动的默认 Installer |
| 生命周期基类 | [backend/crmeb/module/BaseModuleInstaller.php](../../../../../backend/crmeb/module/BaseModuleInstaller.php)（命名空间 `crmeb\module`） | 建表、菜单、种子、日志、共享表协议等生命周期基础设施 |
| 安装调度器 | [backend/app/services/system/module/ModuleInstaller.php](../../../../../backend/app/services/system/module/ModuleInstaller.php) | 发现 Installer、标识归一、调度 install/uninstall/enable/disable |
| 安装日志表迁移 | [backend/app/services/system/module/CreateModuleInstallLogTable.php](../../../../../backend/app/services/system/module/CreateModuleInstallLogTable.php) | 创建/补齐 `eb_module_install_log` |
| HTTP 入口 | [backend/app/adminapi/controller/plugin/PluginController.php](../../../../../backend/app/adminapi/controller/plugin/PluginController.php) + [backend/app/adminapi/route/plugin.php](../../../../../backend/app/adminapi/route/plugin.php) | 插件列表/安装/卸载/启用/禁用/上传接口 |
| 命令行检查 | [backend/app/command/RequiredPluginCheck.php](../../../../../backend/app/command/RequiredPluginCheck.php)、[PluginManifestCheck.php](../../../../../backend/app/command/PluginManifestCheck.php) | `plugin:required-check` / `plugin:manifest-check` |

> 注意：模块 Installer 基类位于 `crmeb\module\BaseModuleInstaller`（`crmeb/module/BaseModuleInstaller.php`）；驱动层不可依赖 app 层，故基类下沉到 `crmeb/` 层，不得引用 `app\services\` 下的旧表述。

## 二、清单体系：plugin.json 与复合身份

### 发现范围

[PluginManifestRepository::all()](../../../../../backend/crmeb/plugin/PluginManifestRepository.php#L25-L58) 用 glob 扫描四类位置（各 1~3 级目录）：

```
modules/*/plugin.json
modules/*/*/plugin.json
modules/*/*/*/plugin.json
driver/*/*/plugin.json
```

单个清单损坏只记 `error_log` 并跳过，不阻断其他插件（安装/升级入口另行直接调用 `PluginManifest::fromFile` 严格校验）。

### PluginManifest 校验规则

[PluginManifest::fromFile()](../../../../../backend/crmeb/plugin/PluginManifest.php#L27-L92) 强制校验：

1. **必填字段**：`key`、`name`、`type`、`version`、`path` 均为非空字符串；
2. **path 格式**：必须匹配 `^(modules|driver)/[A-Za-z0-9_.-]+(/...){0,2}$`，禁止 `..`；
3. **path 与目录一致性**：`path` 推导的真实目录必须与 `plugin.json` 所在目录一致，否则抛异常；
4. **驱动清单**：`kind=driver` 时必须包含 `driver.driver_key`。

清单还承载三个顶层开关字段（只读顶层，不读驱动 `config_fields.required`）：

| 字段 | 语义 |
| --- | --- |
| `required: true` | 核心保护模块：不允许卸载/禁用，全新安装必装 |
| `default_install: true` | 默认随系统安装，但允许后台卸载/禁用 |
| `require: {key: 约束}` | 依赖声明；`php` / `crmeb` 为平台约束，其余为插件依赖 |

### 复合身份 canonicalId

[PluginIdentity](../../../../../backend/crmeb/plugin/PluginIdentity.php) 生成全局唯一标识，避免模块与驱动同名 key 冲突：

| kind | key 来源 | canonicalId 示例 |
| --- | --- | --- |
| `module` | 三级目录取后两段 `_` 连接，否则用 `plugin.json.key` | `module:trade:seckill` |
| `driver` | 必须取 `driver.driver_key` | `driver:pay:allin_pay` |

`legacyKey`（`plugin.json.key` 原值）保留给数据库、接口和路由兼容使用。`module_install_log` 的存储键规则见 [PluginDependencyResolver::storageKey()](../../../../../backend/crmeb/plugin/PluginDependencyResolver.php#L158-L163)：驱动存 `canonicalId`，模块存 `legacyKey`。

## 三、启动期：运行时注册

入口 [backend/app/AppService.php#L69-L92](../../../../../backend/app/AppService.php#L69-L92)（`boot()`）：

```php
PluginManager::register($this->app, $basePath . 'modules/');  // ExtensionRegistry::discover 只读元数据
$moduleRegistry = new ModuleRegistry($this->app);
$moduleRegistry->registerAll($basePath . 'modules/');          // 扫描 App.php 并执行 register()/boot()
```

### ModuleRegistry 扫描规则

[ModuleRegistry::registerAll()](../../../../../backend/crmeb/plugin/ModuleRegistry.php#L43-L89)：

1. **三级扫描**：`modules/{name}/App.php`、`modules/{name}/{sub}/App.php`、`modules/{name}/{sub}/{sub2}/App.php` 均可注册；
2. **状态白名单**：目录下存在含 `key` 的 `plugin.json` → 必须通过 `ModuleStateChecker::isActive($key)`；**无 `plugin.json` 的父骨架模块**（如 `modules/trade/App.php`）一律放行；
3. **执行顺序**：先 `register()`（注册 Handler 等）再 `boot()`（加载模块 `event.php`）；
4. **启动期隔离**：`class_exists()` 触发 autoload 可能抛 `ParseError`，与 `register/boot` 的任何 `Throwable` 一并 try 保护——只记 error 日志（含 file/line）并跳过该模块，**不阻断其他模块与全站启动**。

### ModuleStateChecker 状态判定

[ModuleStateChecker](../../../../../backend/crmeb/plugin/ModuleStateChecker.php) 是运行时唯一的状态依据：

| 要点 | 行为 |
| --- | --- |
| 活跃判定 | `eb_module_install_log` 中 `status=1 AND enabled=1` 的 `module_key` 集合 |
| 缓存 | 进程内静态缓存，一次请求只查一次库；`reload()` 清理（表结构探测结果不清理） |
| 表不存在兜底 | `isActive()` 返回 `true`（放行），避免迁移未跑导致系统崩溃；`activeKeys()` 快照则返回 `[]` |
| 查询失败兜底 | 按**空集合**处理（严格白名单：未查到的模块不加载） |
| 路由反查 | `isModuleRouteActive($routeFile)`：`dirname(dirname($routeFile))` 反查 `plugin.json` 取 key；路径不符或 key 推导失败一律放行 |

## 四、模块路由加载

路由加载统一走 [ModuleRouteLoader::load($scene)](../../../../../backend/crmeb/plugin/ModuleRouteLoader.php#L42-L60)，挂载点：

| 场景 | 挂载位置 |
| --- | --- |
| `user`（用户端 api 应用） | [backend/app/api/route/v1.php#L504](../../../../../backend/app/api/route/v1.php) |
| `admin`（后台 adminapi 应用） | [backend/app/adminapi/route/route.php#L67](../../../../../backend/app/adminapi/route/route.php) |

- 扫描 `modules/*/route/{scene}.php` 与 `modules/*/*/route/{scene}.php`；
- 逐文件经 `ModuleStateChecker::isModuleRouteActive()` 过滤——**禁用/未安装模块的路由不加载**，避免"路由在、Handler 全缺"的脑裂；
- 单个路由文件抛错仅记日志跳过，不阻断全站；
- [inactiveModuleHint()](../../../../../backend/crmeb/plugin/ModuleRouteLoader.php#L74-L93) 供 miss 路由兜底：URL 首段命中某模块目录、模块有路由文件但未激活时，返回插件 key 与名称，向客户端提示"插件未安装"。

## 五、后台生命周期管理

### HTTP 接口

[backend/app/adminapi/route/plugin.php](../../../../../backend/app/adminapi/route/plugin.php)（需 `AdminAuthTokenMiddleware` + `AdminCheckRoleMiddleware`）：

| 方法 | 路径 | 行为 |
| --- | --- | --- |
| GET | `/adminapi/plugin/list`（可带 `?type=`） | 插件列表与安装/启用状态 |
| POST | `/adminapi/plugin/install`（`plugin_key`） | 安装 |
| POST | `/adminapi/plugin/uninstall` | 卸载 |
| POST | `/adminapi/plugin/enable` / `disable` | 启用 / 禁用 |
| POST | `/adminapi/plugin/upload`（form 字段 `plugin_package`） | 上传 ZIP 安装包 / 升级 |

`PluginController::initialize()` 每次进入先执行 `CreateModuleInstallLogTable::up()` 确保日志表及 `enabled`、`online_version` 字段存在。

### 调度器 ModuleInstaller

[ModuleInstaller（app 层）](../../../../../backend/app/services/system/module/ModuleInstaller.php) 的职责：

**Installer 发现**（[getInstallers()](../../../../../backend/app/services/system/module/ModuleInstaller.php#L87-L99) → [registerFromManifest()](../../../../../backend/app/services/system/module/ModuleInstaller.php#L111-L144)），按序尝试：

1. 反射加载 `plugin.json` 同目录 `Installer.php`：遍历 `get_declared_classes()` 按**文件真实路径 + `ModuleInstallerInterface` 实现**双重匹配，未加载则 `include_once` 后重试（避免 `modules/` 同命名空间类重复声明）；
2. fallback：用 `plugin.json.path` 推导类名 `\modules\...\Installer`；
3. 驱动且无独立 Installer → 使用通用 [ManifestDriverInstaller](../../../../../backend/crmeb/plugin/ManifestDriverInstaller.php)。

**标识归一**（[resolveKey()](../../../../../backend/app/services/system/module/ModuleInstaller.php#L244-L259)）：`canonicalId`、`plugin.json.path`、`legacyKey`、`Installer::getKey()` 四种标识都登记进 `identifierMap`；同一标识映射到多个 `canonicalId` 时进入歧义名单，调用时直接抛异常要求使用复合标识。

**生命周期调度**（[install()](../../../../../backend/app/services/system/module/ModuleInstaller.php#L268-L282) / [uninstall()](../../../../../backend/app/services/system/module/ModuleInstaller.php#L539-L555) / [enable()](../../../../../backend/app/services/system/module/ModuleInstaller.php#L560-L585) / [disable()](../../../../../backend/app/services/system/module/ModuleInstaller.php#L590-L617)）：

- 调用前后分别执行 `PluginDependencyResolver` 校验与 `assertNotRequired()` 核心模块保护（`required=true` 拒绝卸载/禁用）；
- 状态变更后统一清理缓存：`ModuleStateChecker::reload()` + `DriverRegistry::resetCache()`；Controller 侧再调 `PluginManager::resetCache()`；
- 调度器**不再包裹外层事务**——Installer 基类内部已按 DDL/DML 自行划分事务边界，外层事务会与之冲突。

### 生命周期基类 BaseModuleInstaller

[crmeb/module/BaseModuleInstaller.php](../../../../../backend/crmeb/module/BaseModuleInstaller.php)（抽象基类，位于 `crmeb\module` 命名空间）实现完整流程：

#### 安装 install()

```
1. isInstalled() → 已安装则 skipped（天然幂等）
2. checkRequirements() 前置检查
3. runMigrate()                # DDL，事务外（MySQL DDL 隐式提交事务）
4. ensureSharedOwnershipColumns()  # 存量库补齐 owner_plugin_id / menu_key /
                                   # shared_table_registry / agreement 字段与索引
5. 开启事务（DML）：
   insertMenus() → addToModelCheckbox() → seed()
   → claimSharedTables() → claimSharedResources()（水位线认领）
   → installDriverRecord()（type=driver 时写驱动字典）
   → logInstall()（status=1, enabled=1，安装即启用）
```

#### 卸载 uninstall()

```
1. ensureSharedOwnershipColumns()   # 事务外补齐字段
2. 未安装 → 仅清理残留菜单后返回
3. 事务内：
   cleanData() → removeMenus() → releaseSharedTables()
   → removeOwnedSharedResources() → removeDriverRecord() → logUninstall()（status=0）
4. 事务外：removeFromModelCheckbox() → rollbackMigrate()（DROP TABLE）
```

卸载**不删除插件目录**（`backupPluginFiles()` 目前处于注释停用状态）。

#### 启用/禁用默认行为

| 回调 | 默认实现 |
| --- | --- |
| `onEnable()` | `ensureSharedOwnershipColumns()` + `insertMenus()` + `addToModelCheckbox()` + **幂等补跑 `seed()`** + `claimSharedTables()`；禁用→启用一次即可补齐升级新增的种子数据 |
| `onDisable()` | `hideMenus()`（`is_show=0, is_show_path=0`，保留记录）+ `removeFromModelCheckbox()` |

子类覆写时必须调用 `parent::onEnable()` / `parent::onDisable()` 保留默认行为。

#### 菜单管理（menu_key 稳定标识机制）

当前菜单体系以**稳定标识**为幂等基础（[insertMenus()](../../../../../backend/crmeb/module/BaseModuleInstaller.php#L508-L577)、[normalizeMenu()](../../../../../backend/crmeb/module/BaseModuleInstaller.php#L589-L642)）：

- 每条菜单生成 `menu_key = {ownerPluginId}:menu:{显式key 或 sha1(身份七元组)}`，身份七元组 = `auth_type | unique_auth | api_url | methods | controller | action | menu_name`（`menu_path` 不参与，避免动态回填导致身份漂移）；同一插件内 `menu_key` 冲突在写库前直接失败；
- `system_menus` 增加 `owner_plugin_id`（插件归属）与 `menu_key`（`uk_menu_key` 函数唯一索引）两列，存量库由 `ensureMenuKeyInfrastructure()` 回填；
- **父子解析**：优先 `parent_key` 引用稳定标识（`getMenuPidByKey()`，`core:` 前缀可回退 `unique_auth` 查询）；`pid=@菜单名` 仅保留为同批次兼容语法；父菜单不存在时**中止安装**，禁止静默挂到顶级；
- **幂等写入**：按 `menu_key` 查找；存量无归属菜单按旧身份（auth_type 唯一键）认领并补齐 `menu_key`/`owner_plugin_id`；已存在的菜单做**字段级差异同步**（路由、父级、排序等变更后无需卸载重装）；
- **auth_type 语义以核心权限校验 `SystemRoleServices::verifyAuth` 为准**（见 [SystemRoleServices.php#L99](../../../../../backend/app/services/system/admin/SystemRoleServices.php#L99) 与基类 `menuQuery()` 注释）：

| auth_type | 类型 | 唯一键 | 说明 |
| --- | --- | --- | --- |
| 1 | 菜单 | `menu_name` + `menu_path` | 侧边栏可见菜单 |
| 2 | **接口权限** | `menu_name` + `api_url` + `methods` | `verifyAuth` **仅识别此类节点管控接口**；带 `api_url` 的权限项必须声明 `auth_type=2`，否则接口不受管控 |
| 3 | **操作按钮** | `menu_name` + `unique_auth` | 仅展示层按钮权限，不参与接口鉴权 |

> ⚠️ crmeb-plugin-dev/plugin.md 中「auth_type=2 按钮 / 3 接口」的表格与当前代码相反，以本文与代码为准。

- **删除策略**：`removeMenus()` 按 `owner_plugin_id` **物理删除**（无归属旧菜单按完整历史身份兼容匹配，避免误删）；系统原生菜单场景保留 `softDeleteMenus()`（`is_del=1`）；`showMenus()/hideMenus()` 同步操作 `is_show` 与 `is_show_path` 两个字段，`restoreMenus()` 恢复 `is_del=0`。

#### 其他内置能力

| 能力 | 方法组 | 说明 |
| --- | --- | --- |
| model_checkbox | `getModelCheckboxKey()` / `addToModelCheckbox()` / `removeFromModelCheckbox()` | 管理独立模块在 `eb_system_config.model_checkbox` 的显示开关，默认空串表示不受控 |
| 核心表白名单 | `CORE_SHARED_TABLES` + [cleanTable()](../../../../../backend/crmeb/module/BaseModuleInstaller.php#L1126-L1152) | `cleanTable()` 仅允许清理 `getOwnedTables()` 声明的自有表，核心共享表（菜单/配置/用户/中台订单等）一律拒绝 |
| 插件间共享表准入 | `sharedTables()` / `claimSharedTables()` / `releaseSharedTables()` / `dropSharedTableIfUnclaimed()` | 仅 `SHARED_TABLE_CATALOG`（如 `store_activity`）内的表可声明共享；登记到 `eb_shared_table_registry`（`uk_table_plugin`），卸载时仲裁——仍有其他插件占用则拒绝 DROP |
| 公共资源归属 | `captureSharedResourceWatermarks()` / `claimSharedResources()` / `removeOwnedSharedResources()` | 以安装前 `max(id)` 为水位线，把 `seed()` 新增到 `system_menus / system_config / system_config_tab` 的公共数据自动标记 `owner_plugin_id`，卸载时按归属清理 |
| 驱动字典 | `installDriverRecord()` / `removeDriverRecord()` | `type=driver` 安装时把 `plugin.json` 的 `driver` 元数据写入统一 `eb_driver` 字典（含 `namespace`、`owner_plugin_id`、`capabilities`、`config_fields` 等），运行时不再扫描目录 |
| 页面链接 | `ensurePageLinkCategory()` / `seedPageLinks()` / `cleanPageLinks()` | DIY 链接选择器数据的幂等 seed 与清理 |
| 协议 | `seedAgreements()` / `cleanAgreements()` | 插件协议的幂等 seed（含旧数据认领）与清理 |

### 涉及的数据表

| 表 | 关键字段/约束 | 用途 |
| --- | --- | --- |
| `eb_module_install_log` | `module_key`（`uk_module_key`）、`status`（1已装/0已卸）、`enabled`、`version`、`online_version` | 插件安装状态的**唯一真源** |
| `eb_shared_table_registry` | `table_name` + `owner_plugin_id`（`uk_table_plugin`） | 插件间共享表准入登记 |
| `eb_system_menus` | `menu_key`（`uk_menu_key`）、`owner_plugin_id` | 菜单稳定标识与插件归属 |
| `eb_system_config` / `eb_system_config_tab` | `owner_plugin_id` | 配置项/分组归属 |
| `eb_driver` | `owner_plugin_id`、`namespace` | 统一驱动字典 |

## 六、上传安装与升级（PluginController::upload）

[PluginController::upload()](../../../../../backend/app/adminapi/controller/plugin/PluginController.php#L127-L323) 处理 ZIP 包：

### ZIP 安全校验（validateZipEntries）

- 包体 ≤ 50MB，条目数 1~2000，解压后总量 ≤ 150MB；
- 禁止目录穿越（`..`）、绝对路径、盘符、`"\0"`、符号链接；
- 必须且只能包含一个 `plugin.json`，且只能位于 ZIP 根目录或一级插件目录。

### 目标目录按 type 决定

| type | 目标目录 |
| --- | --- |
| `product` / `product_type` | `modules/product/{name}` |
| `activity` / `mode` | `modules/trade/{name}` |
| `user` | `modules/user/{name}` |
| `channel` | `modules/channel/{name}` |
| `marketing` | `modules/marketing/{name}` |
| `driver` | `driver/{category}/{name}`，类别仅允许 `pay/sms/upload/express/logistics/invoice/email/bot/copyproduct/printer`（有对应发现服务才会被扫描） |
| 其他 | `modules/{name}` |

### 首次安装

复制到目标目录 → `PluginManifest::fromFile()` 严格校验（失败删除目标目录）→ `PluginManager::resetCache()` 使新插件可被发现 → 返回元数据（后台再手动/自动调用 `install`）。

### 已存在目录 = 升级

1. 校验同版本线：`path` 一致、`key` 等于当前 `legacyKey`、`type` 一致、**新版本必须更高**；
2. [runUpgradeMigrations()](../../../../../backend/app/services/system/module/ModuleInstaller.php#L290-L362)：执行升级包内 `upgrades/{from}_to_{to}.php` 迁移链（脚本必须返回可调用对象，从暂存目录加载以避免替换运行中的类；无脚本视为纯代码升级，有断链则显式失败）；
3. 旧目录备份到 `backend/backup/.plugin_backup/{name}_{时间戳}/` 后替换文件；新清单校验失败自动回滚备份；
4. `updateInstalledVersion()` 更新 `module_install_log.version`。

## 七、全新安装与必需插件

| 方法 | 行为 |
| --- | --- |
| `getRequiredPlugins()` / `getDefaultInstallPlugins()` | 收集 `required=true` / `default_install=true` 的模块清单（缺 Installer 或标识重复即抛异常） |
| `installRequired()` / `installDefault()` | 逐个依赖校验后安装（Installer 幂等，向导中断重试安全），最后统一 reload 缓存 |
| `installBundledDrivers()` | 安装 `default_install=true` 的驱动插件 |
| `getFreshInstallPlan()` | 新装向导的逐项安装计划（必需插件 → 默认插件 → 默认驱动），由 [backend/public/install/index.php](../../../../../backend/public/install/index.php) 消费 |

## 八、命令行检查工具

```bash
php think plugin:manifest-check            # 清单/复合身份/Installer key 与 type 一致性 + 依赖图，不访问数据库
php think plugin:required-check            # required 插件是否可安装、已安装且已启用
php think plugin:required-check --manifest-only   # 仅校验清单与 Installer，适合 CI
```

## 九、排错定位表

| 现象 | 排查入口 |
| --- | --- |
| 插件业务逻辑不执行（Handler 未注册） | 日志搜 `模块 [x] 未安装或未启用` → 查 `eb_module_install_log` 的 `status/enabled`；或搜 `启动加载失败，已跳过` 定位插件语法/运行错误 |
| 后台插件列表缺插件 | `PluginManifestRepository` 扫描失败（error_log）；核对 `plugin.json` 必填字段、`path` 与目录一致性 |
| 安装报「插件标识存在歧义」 | `identifierMap` 冲突，改用 canonicalId，或消除跨类型重复的 legacy key |
| 卸载/禁用报「核心模块不允许」 | `plugin.json` 的 `required=true` 保护 |
| 启用/卸载报「仍被已启用/已安装插件依赖」 | [PluginDependencyResolver](../../../../../backend/crmeb/plugin/PluginDependencyResolver.php) 反向依赖保护，先处理依赖方 |
| 安装报「请先安装/启用依赖插件」 | 正向依赖校验，先装依赖并核对版本约束（支持 `^` `~` `>=` 等比较符） |
| 访问插件接口提示未安装 | `ModuleRouteLoader::inactiveModuleHint()` 命中；确认模块已安装启用，或 URL 首段与模块目录名一致 |
| 菜单错挂/残留 | `menu_key`/`parent_key` 稳定标识机制；旧数据按 auth_type 唯一键认领，`removeMenus()` 按 `owner_plugin_id` 清理 |
| 共享表 DROP 失败/拒绝 | `dropSharedTableIfUnclaimed()` 仲裁：`eb_shared_table_registry` 中仍有其他插件占用 |

## 十、相关文档

- 创建与打包插件（Installer 编写、plugin.json 字段、菜单与 SQL 去重）：[crmeb-plugin-dev/references/manifest-and-lifecycle.md](../../../crmeb-plugin-dev/references/manifest-and-lifecycle.md)
- 架构与扩展点边界：[架构规范](../../../../rules/架构规范.md)、[架构规范详解](../../../../rules/架构规范详解.md)
- 事件机制与监听器注册：[事件规范](../../../../rules/事件规范.md)
