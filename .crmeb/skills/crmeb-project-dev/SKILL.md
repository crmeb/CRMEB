---
name: crmeb-project-dev
description: 开发和重构 CRMEB v8 项目基础框架、ThinkPHP 后端应用、共享服务、API、数据库变更及跨端功能。适用于 backend/app、backend/crmeb、backend/driver、根目录运行配置或跨前后端功能；任务仅涉及 admin、uni-app、插件或模块时，使用对应的专项 skill。
---

# CRMEB 项目开发

从项目根目录开始工作。当旧版 README 或历史资料与当前代码冲突时，以已检入的代码为准。

## 先确定代码归属

编辑前，先沿着一个相近实现追踪路由、控制器、Service、DAO/Model 和前端调用方。将改动放到职责允许的最底层：

- `backend/crmeb/`：稳定的框架契约、注册中心和基础设施，不得依赖业务应用。
- `backend/app/`：共享业务应用层，包括 AdminAPI/API 入口、Service、DAO、Model、监听器和系统集成。
- `backend/modules/`：可选业务能力。涉及领域归属和模块规范时加载 `$crmeb-plugin-dev`。
- `backend/driver/`：第三方服务商实现，通过 `crmeb/services` 调用，控制器不得直接调用。
- `frontend/admin/`：管理端 UI 工作加载 `$crmeb-admin-dev`。
- `frontend/uni-app/`：用户侧多端开发加载 `$crmeb-uniapp-dev`。

当改动跨越后端层级、新增 API、修改持久化或引入共享契约时，阅读 [references/architecture.md](references/architecture.md)。运行项目或选择验证命令前，阅读 [references/verification.md](references/verification.md)。

## 按现有边界实现

1. 修改前确认当前请求/响应契约及全部调用方。
2. 保持控制器轻量：使用 `app\Request::getMore()` 获取输入，调用 Service，并通过 `app('json')->success()` 或 `fail()` 返回。
3. 业务编排和事务放入 `*Services`，持久化查询放入 `*Dao`，搜索器和关联关系放入 Model。
4. 扩展行为使用接口、注册中心或字符串事件。绝不能让 `app/` 依赖 `modules/` 中的具体类。
5. 保持 AdminAPI 与用户 API 客户端的响应包裹和认证约定一致。
6. 修改表结构、配置或菜单时，同步更新安装数据或插件生命周期逻辑，不能让全新安装与升级后的系统不一致。

## 项目不变量

> 以下为硬约束，**判定标准以 rules 为准**（本文不复制规范正文，仅做定位）：

- 依赖方向为 `modules -> app -> crmeb -> driver`，反向依赖与跨模块直接引用均不合法 —— [.crmeb/rules/架构规范.md](../../rules/架构规范.md)「依赖铁律」
- 普通业务数据库访问必须经 DAO/Model；直接调用 `Db::table()`/`Db::name()` 仅限 Installer、迁移和框架生命周期代码 —— [.crmeb/rules/代码规范.md](../../rules/代码规范.md) 第三节
- 后端事件使用业务字符串 key，例如 `event('OrderPaySuccess', [...])`；不得使用监听器类名或类常量 —— [.crmeb/rules/事件规范.md](../../rules/事件规范.md) 2.1
- 控制器类名和文件名以 `Controller` 结尾；控制器方法使用 camelCase —— [.crmeb/rules/代码规范.md](../../rules/代码规范.md) 〇 / 一
- 新增 PHP 类及 public/protected 方法遵循项目的中文 PHPDoc 规范 —— [.crmeb/rules/架构规范.md](../../rules/架构规范.md)「代码注释规范」
- 除非任务明确要求，不得修改 `backend/.env`、`backend/.constant`、凭据、生成的 `runtime/`、前端构建产物或锁文件 —— [.crmeb/rules/提交规范.md](../../rules/提交规范.md) 禁止入库清单

## 完整收尾

先执行语法检查和范围最小的相关构建，再验证受影响的 API 或 UI 流程。需要 HBuilderX 或外部凭据的平台构建若无法执行，必须如实说明。不得把 `./run.sh install` 或 `./run.sh delete` 当作常规验证命令，因为它们会清除本地数据。
