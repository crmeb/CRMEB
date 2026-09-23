---
name: crmeb-plugin-dev
description: 创建和修改 CRMEB 可安装插件、第三方驱动与 backend/modules 业务模块。覆盖 plugin.json 清单、Installer 生命周期、菜单种子、消息通知与渠道种子、DIY 声明、打包升级、admin UMD 资源，以及产品类型、交易模式、营销观察者、渠道、分销模块的领域归属、Handler 注册、事件、路由、定时任务、数据分层与前端契约；含插件向 MCP 服务贡献工具（McpToolInterface 契约、McpToolsRegister 事件）。凡涉及插件管理或模块业务行为均适用。
---

# CRMEB 插件开发

插件通过 `backend/modules/` 和 `backend/driver/` 下通过校验的 `plugin.json` 自动发现。模块插件通常提供 `App.php` 和 `Installer.php`；纯配置型驱动可以使用通用清单安装器。每个模块都可独立安装，并且必须与同级模块保持隔离。

## 先确定插件类型

- 业务模块插件：放在 `backend/modules/{category}/{name}/`，或采用现有的扁平结构 `backend/modules/{name}/`。先读 [references/domain-selection.md](references/domain-selection.md) 确定领域归属（product/trade/marketing/channel/agent），再读 [references/registration-and-contracts.md](references/registration-and-contracts.md) 了解注册中心、事件、路由、数据层、定时任务与前端契约。
- 插件需要让 AI 客户端通过 `/api/mcp` 调用本插件能力（新增 MCP 工具、覆盖内置工具）时，阅读 [references/mcp-tools.md](references/mcp-tools.md)。
- 插件需要发送站内信/短信/邮件/机器人等消息通知，或注册自有消息渠道时，阅读 [../crmeb-dev/references/10-消息通知/notice.md](../crmeb-dev/references/10-消息通知/notice.md)（Notice 门面、`getNoticeDefinitions()` / `getNoticeChannelDefinitions()` 种子与渠道契约）。
- 第三方驱动插件：放在 `backend/driver/{category}/{name}/`，其中 category 必须是驱动发现服务已支持的类别。
- Admin 扩展资源：需要协调后端插件生命周期与 Vue 管理端 UMD 产物，阅读 [references/admin-extension.md](references/admin-extension.md)。

创建清单、修改安装/卸载行为、新增菜单或数据、打包升级版本前，阅读 [references/manifest-and-lifecycle.md](references/manifest-and-lifecycle.md)。

## 必需结构

普通分组模块包含 `App.php`、`Installer.php`、`plugin.json`，需要监听事件时还包括 `event.php`。只创建功能实际需要的目录：`controller/`、`route/`、`service/`、`dao/`、`model/`、`validate/`、`listener/`、`handler/`、`jobs/`、`crontab/`、`pipeline/` 或 `sdk/`。

命名空间使用 `modules\{category}\{name}`。控制器使用 `controller/admin` 和/或相近模块已经采用的用户端结构。路由放在 `route/admin.php`、`route/user.php` 或 `route/manage.php`；路由加载器不会自动补充中间件，因此文件必须自行声明。

## 开发流程

1. 选择同类型、同领域中最接近的现有插件，端到端追踪注册、安装生命周期、路由、Service、事件、admin 页面和 uni-app 调用方，再有意识地修改身份、路径、类和资源归属。
2. 先定义稳定的 Handler 或事件契约，再在其后实现持久化、履约和 UI 细节。在模块 `App::register()` 中注册 Handler，在 `App::boot()` 中加载 `event.php`。
3. 业务逻辑放入 `service/`，查询放入 `dao/`，搜索器/关联放入 `model/`，请求验证放入 `validate/`。与 `app/` 和同级扩展通信时使用核心接口、注册中心或字符串事件；模块可以调用 `app/` 和 `crmeb/`，`app/` 及其他模块均不得直接引用本模块。
4. 确保 `plugin.json` 能通过 `PluginManifest::fromFile()`：`key`、`name`、`type`、`version` 和 `path` 必须是有效字符串，声明路径必须与实际目录一致。模块 key 遵循相近模块约定（如 `product_cardkey`、`activity_seckill` 下划线格式），`plugin.json`、Installer、事件、菜单权限和前端扩展 key 必须一致。
5. `Installer::getKey()` 必须兼容现有记录使用的清单旧 key/path。当旧 key 可能产生歧义时，管理边界优先使用 canonical ID。
6. 安装、种子写入、菜单插入、启用和禁用操作必须幂等；基类 `onEnable()` 默认会重新执行 `seed()`，种子逻辑必须经得起重复调用；清理范围只能覆盖本插件拥有的资源。
7. 插件提供 uni-app 独立页面时，用基类 `ensurePageLinkCategory()` / `seedPageLinks()` 把页面注册进 DIY 链接选择器，卸载用 `cleanPageLinks()` 清理；插件自带 DIY 组件时在清单声明 `diy.components`（见 manifest-and-lifecycle.md 的 DIY 两节）。
8. 依赖关系写入清单 `require`，不得用分散的运行时判断重复实现。
9. 使用插件间共享表时，覆写 `sharedTables()` 声明准入，卸载用 `dropSharedTableIfUnclaimed()` 仲裁删表，`cleanTable()` 只按归属条件清理自身数据行（表需同步声明进 `getOwnedTables()`）；协议细则见 [.crmeb/rules/架构规范详解.md](../../rules/架构规范详解.md)「插件间共享表准入协议」。
10. 保持前端契约数据驱动：商品 `display_sections`、订单 `page_config`、订单按钮/状态和扩展载荷，应让 admin/uni-app 无需增加中央类型分支即可渲染新能力。新增产品类型或交易模式时，优先向现有前端契约添加数据，不修改核心前端。
11. 先按 [架构规范.md](../../rules/架构规范.md)「当前发布阶段（强约束）」确定版本与安装策略。进入发布后维护阶段才按升级流程提升版本号；需要数据迁移时提供完整的 `upgrades/{from}_to_{to}.php` 链；纯新增种子数据可由幂等 `onEnable()` 补齐。
12. 验证发现、安装、启用、禁用、重新启用、卸载和重新安装，再测试领域生命周期。涉及生产数据的插件只能在隔离数据库中测试破坏性生命周期操作。涉及订单时，覆盖该模块参与的创建、支付、取消、履约、退款/售后、列表和详情流程。

## 安全与边界

> 插件二开的强制红线（禁改系统表与框架代码、自有目录与自有表、扩展方式、卸载可还原）真源为 [.crmeb/rules/插件开发规范.md](../../rules/插件开发规范.md)，本节只列高频操作注意项。

- `cleanData()` 中绝不能使用无条件范围的 `DELETE`、`UPDATE` 或清理查询。
- 不得删除共享核心表，也不得删除其他插件拥有的共享菜单或配置记录。
- 覆盖生命周期钩子时必须调用 `parent::onEnable()`/`parent::onDisable()`。
- 驱动设置属于驱动清单和驱动配置存储，不得随意写入 `system_config`。
- 不得通过手工修改插件安装日志或缓存来模拟成功；应调用公开的生命周期 Service 或管理 API。
- 插件自有缓存使用 `crmeb\services\PluginCacheService`，由服务自动隔离 key 与 tag；不得调用 `CacheService::clear()` / `clearAll()` 清理共享缓存池。修改核心共享数据后，通过对应核心 Service 的精确失效接口清理相关 key。
- 事件调用使用不带 `Listener` 后缀的业务字符串 key。监听器类保留该后缀，并在 `event.php` 中注册。监听器扩展共享载荷时，使用现有引用数据结构和命名空间 key；不得在 `app/` Service 中把模块专属字段摊平。
- 队列任务放在复数 `jobs/`，不得使用已废弃的单数 `job/` 目录。
- 产品类型与交易模式互相正交。只发奖励的观察者不得放入 trade，履约类型也不得实现为营销活动。
- 绝不能向 `app/AppService.php` 添加硬编码模块映射；模块 `App.php` 和注册事件才是扩展机制。
- 上传、安装、卸载和升级会修改文件或数据。只有任务明确授权对应生命周期操作时才能执行。

## 完成检查

验证 PHP 和 JSON 语法、清单发现、依赖解析、清单/Installer 身份、菜单/API 权限一致性、路由到控制器解析、安装幂等性、Handler 注册、事件加载、DAO 查询、自有资源清理和前端资源路径。涉及订单时覆盖完整业务流程。打包内容只能是包含唯一有效 `plugin.json` 的插件根目录，绝不能包含本地密钥、运行时数据或无关仓库文件。
