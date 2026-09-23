# CRMEB v8 项目通用约定

本文是项目约定的唯一维护入口。正文中的代码路径相对仓库根目录，Markdown 链接相对本文。各工具入口只引导读取本文。

人工查阅与目录维护见 [README.md](README.md)。按本次任务选择规则和技能，不应整目录加载。

> 这是本仓库**所有 AI 编码工具的统一入口**：ZCode / Claude Code / Codex / Cursor / GitHub Copilot / Gemini CLI / Trae 等。
> 规则、技能、子智能体各只有一份：`.crmeb/rules/`、`.crmeb/skills/`、`.crmeb/agents/`。
> 根级薄指针（根 `AGENTS.md`、`CLAUDE.md`、`llms.txt`）为入库真实文件，内容只有指引，不承载正文，不重复维护；各工具专属目录（`.trae/`、`.claude/`、`.zcode/` 等）是指向真源的本地软链，**不要直接编辑软链里的文件**。

## 项目是什么

CRMEB v8 电商系统，三端一体：

| 区域 | 目录 | 技术栈 |
| --- | --- | --- |
| 后端 | `backend/` | PHP 8.2-8.4 + ThinkPHP 8 + MySQL 8 + Redis，多应用入口 `adminapi` / `api` / `outapi` |
| 管理后台 | `frontend/admin/` | Vue 3.5 + Element Plus 2.9 + Pinia 2 + vue-router 4 + Vite 5（纯 JS） |
| 移动端 | `frontend/uni-app/` | uni-app 官方 CLI（Vite 5）+ Vue 3 + Pinia + Sass |

本地开发默认使用宿主机环境：PHP/ThinkPHP HTTP `8011`、MySQL/Redis 按 `backend/.env` 的实际配置连接（常见为 `3306` / `6379`）。Docker Compose 是可选兼容环境（Nginx `8011`、MySQL `33061`、Redis `63791`），不作为默认或优先选项。

直接查询数据库或验证 API 前，先读取当前配置并确认实际运行的服务地址；不得仅因仓库存在 `docker-compose.yml` 就自动启动容器、进入容器或假定映射端口。只有用户明确指定 Docker、目标实例本就运行在容器中，或宿主机环境无法满足任务所需版本/扩展时，才使用 Docker，并说明实际验证环境。

## 知识资产边界

| 资产 | 位置 | 定位 | 何时读 |
| --- | --- | --- | --- |
| 各端约定 scopes | `.crmeb/scopes/` | 管理端与移动端的补充约定 | 修改对应端时 |
| 规则 rules | `.crmeb/rules/` | 法律：强制约束，违反即错误 | 改代码前 |
| 技能 skills | `.crmeb/skills/` | 手册：操作流程与最佳实践 | 做对应任务时 |
| 子智能体 agents | `.crmeb/agents/` | 员工：独立执行复杂专项任务 | 需要专项检查/构建时 |
| 项目文档 docs | `help/docs/` | 项目、架构、模块与运行说明，见 [文档索引](../help/docs/文档索引.md) | 需要特定背景资料时 |

## 硬约束（改代码前必读）

1. **后端依赖单向**：`modules → app → crmeb → driver`。`crmeb/` 不得依赖 `app/` 或 `modules/`；同级模块不得互相引用，跨模块走核心契约或字符串事件。
2. **分层与命名**：`route → Controller → Services → Dao → Model`；控制器类名/文件名以 `Controller` 结尾；构造函数注入 Service/DAO 时先声明属性并写 `@var`。
3. **事件用字符串 key**（不带 `Listener` 后缀），核心监听器在 `backend/app/event.php`，模块监听器在各模块 `event.php`。
4. **管理端样式只用 `--prev-*` 令牌**，禁止硬编码主题色；页面用卡片分块（搜索卡 + 数据卡），按钮带 `v-db-click`，权限按钮带 `v-auth`。前端红线细则见 [.crmeb/rules/前端规范.md](rules/前端规范.md)。
5. **接口契约变更要全链路同步**：后端路由/Controller/Service/DAO/Model、`frontend/admin/src/api`、`frontend/uni-app/src/api`、菜单权限、安装/升级数据。同步清单见 [.crmeb/rules/前端规范.md](rules/前端规范.md) 第三节。
6. **Git 提交遵循 [.crmeb/rules/提交规范.md](rules/提交规范.md)**：中文描述 + 英文类型前缀、按主题分批提交、禁止提交密钥与本地环境文件。
7. **插件二开零侵入**：插件/驱动/业务模块只能落 `backend/modules/`、`backend/driver/` 自有目录，禁止修改系统表与框架代码（`app/`、`crmeb/`、核心前端、安装 SQL、composer/config），扩展只走扩展点/事件/注册中心/Installer 标准设施。红线细则见 [.crmeb/rules/插件开发规范.md](rules/插件开发规范.md)。

## 规则索引（`.crmeb/rules/`）

| 文件 | 内容 | 何时读 |
| --- | --- | --- |
| [project-entry.md](rules/project-entry.md) | Trae 常驻入口，路由到项目约定 | 工具自动加载 |
| [架构规范.md](rules/架构规范.md) | 三层依赖铁律、高频违规、扩展点、事件/注释要点（精简常驻） | 任何后端改动 |
| [架构规范详解.md](rules/架构规范详解.md) | 目录结构、模块注册、消息通知、驱动后台、路由自动加载、菜单路径 | 涉及架构/扩展点时 |
| [插件开发规范.md](rules/插件开发规范.md) | 插件二开强制边界：禁改系统表与框架代码、自有目录与自有表、扩展方式、卸载可还原、插件包与升级包交付（upgrades 脚本链、版本双源一致、自动归档）、违规判定 | 开发/修改任何插件、驱动、业务模块及配套前端资源前；发布插件包/升级包时 |
| [代码规范.md](rules/代码规范.md) | 控制器命名、Services/DAO 属性注入、路由定义格式 | 写后端代码时 |
| [事件规范.md](rules/事件规范.md) | 事件机制与全量事件名清单 | 新增/修改事件时 |
| [订单中台规范.md](rules/订单中台规范.md) | 订单中台架构规则与订单流程 | 订单相关开发 |
| [前端规范.md](rules/前端规范.md) | 管理端/移动端强制红线、接口契约变更全链路同步清单 | 任何前端改动、接口契约变更时 |
| [升级包规范.md](rules/升级包规范.md) | 首个正式版本发布前暂不创建升级脚本/升级包；首发后恢复二开改动归档升级程序包：版本增量脚本、包结构与 manifest、升级包/插件包边界、交付前自动打包流程与红线 | 完成涉及后端/前端文件或数据库的二开改动、发布封包时 |
| [提交规范.md](rules/提交规范.md) | Git 提交信息格式、按主题分批提交、禁止入库清单、提交前检查（单一真源） | 每次提交前 |
| [目录与文档规范.md](rules/目录与文档规范.md) | 三类知识目录职责边界（help/docs / .crmeb / tests）、"当前事实 vs 过程产物"判定、测试脚本归属与登记要求 | 写文档、新增测试、归档过程产物时 |
| [安全红线.md](rules/安全红线.md) | 密钥不入库、权限与 IDOR 校验、支付验签幂等、上传/SQL/日志/供应链红线 | 涉及鉴权、支付、上传、SQL、日志时 |
| [数据库规范.md](rules/数据库规范.md) | 表前缀与命名、字段类型约定、软删除、索引命名、禁外键、表结构变更同步 install SQL | 建表/改表、新增 Model 字段时 |
| [接口与响应规范.md](rules/接口与响应规范.md) | 统一响应体 `{status,msg,data}`、Json 唯一出口、列表 `{list,count}`、分页与参数校验 | 新增/修改接口、调整返回结构时 |
| [日志与异常规范.md](rules/日志与异常规范.md) | 异常类选型、throw/fail/success 分层、日志通道与脱敏、禁止空 catch/调试输出 | 写报错逻辑或线上排障时 |
| [性能与缓存规范.md](rules/性能与缓存规范.md) | 列表分页、禁 SELECT */N+1、CacheService 与缓存 key、事务最小化、队列异步与重试 | 写列表/查询/缓存/队列代码时 |

## 技能索引（`.crmeb/skills/`）

先按任务选择一个主技能；跨端任务补充对应端技能，管理端页面/样式任务同时加载 `crmeb-admin-ui`。需要二开流程导航或不能确定扩展点时再加载 `crmeb-dev`，普通专项修改不要求先读总览。

技能路径为 `.crmeb/skills/<技能名>/SKILL.md`。原生技能面板未列出时，直接读取真源并执行。规则按规则索引的“何时读”加载，不假设工具会自动加载链接的全部正文。不要通过不同软链重复加载同一份内容。

支持 MCP 的工具可连接内置开发知识服务（`service_mcp` 模块，端点 `POST /api/mcp`）：`crmeb_dev_docs_search`（检索本文档体系）、`crmeb_dev_route_search`（路由定义检索）、`crmeb_dev_table_schema`（运行库表结构查询）。接入方式见根 `README.md`「连接本项目的 MCP 开发工具」；工具实现位于 `backend/modules/service/mcp/tools/`，新增 `*Tool.php` 即自动注册。

| 技能 | 何时用 |
| --- | --- |
| `crmeb-project-dev` | 项目框架、`backend/app`、`backend/crmeb`、`backend/driver`、共享服务、API、数据库变更、跨端功能 |
| `crmeb-plugin-dev` | 可安装插件、驱动与 `backend/modules/` 业务模块：`plugin.json`、Installer 生命周期、菜单、种子、打包、admin UMD 资源、领域归属、Handler 注册、事件路由、定时任务、消息通知、MCP 工具扩展 |
| `crmeb-order-dev` | 订单开发手册：交易模式、产品类型与订单中台，订单全生命周期（创建/支付/履约/收货/评价/取消/售后退款）开发说明 | 订单、交易模式、支付、售后、订单状态相关开发 |
| `crmeb-admin-dev` | 管理后台页面/组件/接口/路由/插件页面开发 |
| `crmeb-admin-ui` | 管理端 UI 规范：设计令牌、布局、页面骨架、组件与交互、深色模式、评审清单 |
| `crmeb-uniapp-dev` | 移动端 `frontend/uni-app` 页面、API 封装、条件编译、分包、动态渲染 |
| `crmeb-order-dev` | 订单中台全生命周期开发手册：交易模式、产品类型订单链路、订单事件、状态流转、售后退款 |
| `crmeb-dev` | 二开总览与流程导航（含 `references/` 分类子文档，如创建插件/驱动/产品类型/用户端页面） |

## 子智能体索引（`.crmeb/agents/`）

子智能体用于复杂多步骤专项任务。支持原生子智能体的工具可调用已注册角色；其他工具直接读取 `.crmeb/agents/<名称>.md`，由当前智能体执行该流程，不声称已创建或调用独立子智能体。

| 子智能体 | 职责 | 典型触发 |
| --- | --- | --- |
| `crmeb-arch-checker` | 扫描架构违规：三层依赖铁律、跨模块互引、残留 use、事件/注释规范。机器化入口：`php think arch:check`（CI 拦截严重违规），人工巡检按本文档流程 | 「架构检查」「违规扫描」「依赖铁律检查」 |
| `crmeb-module-builder` | 从 0 到 1 创建标准模块（插件、产品类型、驱动、交易模式、营销观察者）。脚手架入口：`php think make:module <name> --label=中文名 --type=service` 生成合规骨架 | 「创建插件」「新增产品类型」「开发模块」「二开」 |
| `crmeb-route-validator` | 校验路由定义与控制器方法映射、扫描路由缺陷、回归验证 | 「路由测试」「接口映射验证」「路由检查」 |

## 各工具如何读取

根级入库薄指针（内容只有指引，正文唯一真源是本文）：

| 入口 | 服务对象 |
| --- | --- |
| 根 `AGENTS.md` | 支持 AGENTS.md 标准的工具：Codex / Cursor / Gemini CLI 等 |
| `CLAUDE.md` | Claude Code |
| `llms.txt` | 支持 llms.txt 的工具与网页抓取类 AI |

工具专属目录（`.zcode/`、`.claude/`、`.trae/`、`.agents/` 等）为 Git 忽略的可选本地目录，通过软链指向 `.crmeb/` 实现原生技能 / 子智能体注册；`.devcontainer/` 同为 Git 忽略的可选本地目录，供需要容器化开发者自建（Dev Containers 只识别仓库根的 `.devcontainer/` 或 `.devcontainer.json`）。以上均不自动生成，缺失不作为错误。

规则及子智能体格式并非所有工具通用，跨工具兜底是读取本文及本文索引指定的真源文件。

## 维护约定（防腐规则）

> **rules 与 skills 的唯一判定标准**：**"违反它算不算错误？"** 算 → 写进 `.crmeb/rules/`（法律，违反即缺陷）；只是"更省事 / 更规范的做法" → 写进 `.crmeb/skills/`（手册，操作流程）。**同一事实只有一个真源**：rules 承载强制条款，skills 只写"怎么做"，需要引用规则时**只放链接、不复制正文**；`references/` 子文档同样适用——可保留判定清单、现状说明、代码定位，但不得复述规则条款。

1. **内容只有一份**：规则改 `.crmeb/rules/`，技能改 `.crmeb/skills/`，子智能体改 `.crmeb/agents/`。软链目录里的文件就是同一份，不要复制正文，禁止双份维护。
2. **代码变更影响规范时**，同步更新对应 rules/skills；**修改或删除规范时**，全局搜索引用（本文件索引表、各端 AGENTS.md、`help/docs/`）核对，不留死链。
3. **新增资产必须登记索引**：技能/子智能体建 `.crmeb/<类型>/<kebab-case-name>/`（或单文件），frontmatter 写 `name`（与目录名一致）+ `description`（做什么 + 何时用 + 触发词），细节放 `references/`，并更新本文件对应表格。`references/` 子文档若带 frontmatter，其 `description` 必须非空（由 `php think docs:check` 校验，error 级阻断）。
4. **项目说明维护在 `help/docs/`**：新增或移动文档时更新对应索引与引用；资料应标明适用范围，旧计划与报告不作为当前待办或验收证明，一次性临时产物不入库。
5. **提交前核对开发资料**：运行 `php think docs:check`（本地链接 / frontmatter / 索引登记 / 引用路径）和 `node scripts/check-doc-facts.mjs`（代码事实数量 / 根入口链接）；可选工具目录缺失不作为错误，也不应自动创建。入口须指向现有真源，Windows 应启用开发者模式及 Git 软链支持。静态资料完好不证明业务已完成或已测试。

## 改动前先读

- 后端：[.crmeb/rules/架构规范.md](rules/架构规范.md) + 对应技能。
- 管理端：`.crmeb/skills/crmeb-admin-dev/SKILL.md` + `.crmeb/skills/crmeb-admin-ui/SKILL.md` + [.crmeb/scopes/admin.md](scopes/admin.md)。
- 移动端：`.crmeb/skills/crmeb-uniapp-dev/SKILL.md` + [.crmeb/scopes/uni-app.md](scopes/uni-app.md)。
- 二开改动完成（涉及文件或数据库变更）：首个正式版本发布前暂不归档升级程序包；首发后按 [.crmeb/rules/升级包规范.md](rules/升级包规范.md) 自动归档。插件改动不入升级包，按 [.crmeb/rules/插件开发规范.md](rules/插件开发规范.md) 第七节自动归档插件包（含 upgrades 迁移脚本与版本双源递增）。
