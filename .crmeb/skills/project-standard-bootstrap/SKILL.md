---
name: project-standard-bootstrap
description: 审计、建立或修复项目统一工程规范，使全栈项目具备一致的 .crmeb AI 规则、统一技能包骨架、按后端与各前端目录拆分的架构认知和核心功能开发文档、主流 AI 工具入口、backend/frontend 目录、标准命令、Docker 本地开发、跨层开发指引、测试 CI、文档和自动升级包。适用于新项目初始化、旧项目规范化、AI 接手治理、前端补齐后端与数据库能力、UniApp 多端开发和快速运行；不用于只润色单篇文档或绕过目标仓库已有约束。
---

# 项目统一工程规范

把目标项目建设成“目录统一、命令统一、规则可发现、全栈协作完整、环境可复现、变更可验证、交付可升级”的工程系统。统一的是项目契约和质量底线；语言、框架及部署实现由真实技术栈决定。

## 工作模式

- **仅审计**：运行只读审计并输出 P0/P1/P2 差距，不修改文件。
- **初始化**：为新项目生成缺失的目录、`.crmeb/` 基线、项目契约，以及按代码目录拆分的架构认知与开发文档技能。
- **审计并完善**：对已有项目增量补齐规范、技能包骨架、目录说明、脚本、文档和验证入口。
- **刷新维护**：修复事实漂移、失效命令、规则冲突、目录说明过期、迁移断链和交付缺口。

用户只要求分析时不得修改。用户要求建立、完善、统一或升级时，完成实施与验证闭环。

## 不可妥协的统一契约

1. AI 规则正文只维护在 `.crmeb/`；根 `AGENTS.md` 等工具入口只做薄指针。
2. `.crmeb/skills/` 采用统一技能包骨架：每个技能目录为 `SKILL.md` + `references/`，`assets/`、`scripts/`、`agents/` 按需存在；`SKILL.md` 只承载索引与最小流程，正文放 `references/`，不得在技能根目录散落规则正文。
3. 架构认知与核心功能开发文档必须按代码目录拆分：后端一个技能，每个可独立构建的前端应用各一个技能，与 `.crmeb/project.json` 的 `applications` 对齐；每个目录必须有「项目目录说明」，并登记到 `.crmeb/AGENTS.md` 的技能索引，使 AI 无需遍历全仓即可快速了解架构。
4. 后端业务代码位于 `backend/`；可独立构建的前端位于 `frontend/<应用名>/`。
5. 知识目录职责分离：当前事实在 `help/docs/`，AI 规则在 `.crmeb/`，过程文档与测试索引统一在 `tests/`（`plans/`、`reports/`、可分发模板）；过程目录名由契约 `directories.process` 声明，审计、初始化与检查脚本读取该字段，不硬编码目录名。
6. `.crmeb/project.json` 记录应用、服务、端口和标准命令映射，并由检查脚本防漂移。
7. 项目提供 `doctor/setup/dev/stop/status/logs/check/test/build` 等价命令；实现可映射到现有 Make、Task、npm、Gradle、Compose 或脚本。
8. 全栈变更同步检查数据、后端、API、权限、前端、测试、文档、迁移和升级包。
9. 除“仅审计”外，必须根据当前项目后端真实运行环境自动生成或完善根 `docker-compose.yml`；同时生成所需 Dockerfile、配置和 `run.sh`，挂载本地源码、隔离依赖和数据卷，并区分安全停止与破坏性重置。
10. 规则区分通用底线、技术栈 Profile 和项目专属约定，不把单一框架习惯强加给所有项目。
11. 已发布版本、迁移和升级清单不可回改；可交付变更必须判断升级包触发条件。
12. 不覆盖现有修改、不泄露凭据、不擅自清库、发布、推送或连接生产环境。
13. Codex、Claude Code、Cursor、GitHub Copilot、Gemini CLI 等工具通过各自发现入口读取同一 `.crmeb` 真源，不复制规则正文。

## 执行流程

### 1. 发现与审计

先读取目标仓库所有适用的 `AGENTS.md`、README、贡献指南、manifest、lockfile、容器、CI、迁移和测试配置，并检查 Git 状态。运行：

```bash
node <skill>/scripts/audit-project-standard.mjs <repository>
node <skill>/scripts/analyze-project-environment.mjs <repository> --write
```

环境分析器只读取 manifest、lockfile、非敏感配置样例和现有容器配置，不读取真实 `.env`。它输出运行时、应用、依赖服务、Docker 现状和动态文档缺口；结果必须由代码入口复核。事实以代码和可执行配置为准，旧文档只作线索。详细证据账本和优先级方法见 [文档与事实治理](references/documentation-governance.md)。

### 2. 建立项目契约

读取 [项目契约与标准命令](references/project-contract.md)，建立 `.crmeb/project.json`。新项目或确认需要初始化时可先运行：

```bash
node <skill>/scripts/init-project-standard.mjs <repository> --dry-run
node <skill>/scripts/init-project-standard.mjs <repository>
```

初始化工具只创建缺失项，不覆盖现有文件。生成后必须根据真实项目补全命令、应用、服务和端口，禁止把草稿契约当成已完成。

### 3. 建立规则体系

按任务读取相关参考，不要一次加载全部：

- 目录、模块边界和迁移：[目录与架构](references/directory-and-architecture.md)
- 全栈功能修改：[全栈开发流程](references/fullstack-workflow.md)
- 前端开发者跨到后端和数据库：[前端到后端与数据库](references/frontend-backend-database-bridge.md)
- 后端分层、事务、任务：[后端规范](references/backend-standard.md)
- 前端应用、状态、UI：[前端规范](references/frontend-standard.md)
- API、数据库和契约：[API 与数据库](references/api-and-database.md)
- 安全、日志、性能和运维：[安全与可观测性](references/security-and-observability.md)
- 测试、质量门禁和 CI：[测试与 CI](references/testing-and-ci.md)
- Docker 本地运行：[Docker 本地开发](references/docker-local-development.md)
- 项目文档完整度：[文档覆盖矩阵](references/documentation-coverage.md)
- 版本迁移和升级包：[自动升级包](references/upgrade-package.md)
- 最终验收和旧项目迁移：[迁移与验收](references/migration-and-acceptance.md)
- 多 AI 工具发现与单一真源：[AI 工具适配](references/ai-tool-adapters.md)

把通用规则模板从 `assets/project-template/.crmeb/` 增量适配到目标项目。规则必须结合真实框架补充，不能保留与实现冲突的模板语句。

### 4. 生成架构认知与目录说明技能

系统必须自动、快速地让 AI 与新成员了解项目架构，不依赖人工口述或全仓遍历。在 `.crmeb/skills/` 下按代码目录建立技能，目录职责与分层方法见 [目录与架构](references/directory-and-architecture.md)：

- **按目录拆分**：后端对应一个技能；每个可独立构建的前端应用各对应一个技能（如管理端、移动端、Web 端），与 `.crmeb/project.json` 的 `applications` 一一对应，技能名可从应用 ID 派生。
- **每个目录必须有「项目目录说明」**：目录树、逐目录职责、关键文件与入口、请求与数据流转链路、依赖边界、禁止放置的内容。
- **每个目录必须有核心功能开发文档**：该端核心功能的入口、路由或 API、业务层、数据模型、扩展点、依赖服务，以及测试与验证命令。
- **统一骨架**：技能目录为 `SKILL.md` + `references/`；`SKILL.md` 只做索引与最小流程，正文按主题拆到 `references/`，需要时再补 `assets/`、`scripts/`、`agents/`。
- **骨架来源与元数据**：从 `assets/app-skill-skeleton/` 复制并替换占位符（`{{SKILL_NAME}}`、`{{APP_ID}}`、`{{APP_PATH}}`、`{{RUNTIME}}`）；`SKILL.md` frontmatter 必须声明 `name`（与目录同名）、`description`、`appPath`、`directoryGuide`、`coreFeatures`，后两者指向技能内真实存在的文档。

初始化脚本会按 `applications` 自动生成技能骨架并登记 `.crmeb/AGENTS.md` 技能索引。脚本只搭骨架，正文必须由 AI 按当前代码补全；占位符或 TODO 未清除会被校验标为未完成。内容必须从当前代码、路由、manifest 和测试核对，禁止照搬模板或旧文档；架构事实变化时同步更新对应目录说明。已存在的技能只做增量补全，不覆盖用户已有改动。

### 5. 加载技术栈 Profile

只加载检测到的技术栈：

- PHP：[profiles/php.md](references/profiles/php.md)
- Node.js 后端：[profiles/node.md](references/profiles/node.md)
- Python：[profiles/python.md](references/profiles/python.md)
- Java：[profiles/java.md](references/profiles/java.md)
- Go：[profiles/go.md](references/profiles/go.md)
- Vue：[profiles/vue.md](references/profiles/vue.md)
- React：[profiles/react.md](references/profiles/react.md)
- UniApp：[profiles/uniapp.md](references/profiles/uniapp.md)

Profile 用于发现权威版本、命令、常见目录和验证工具，不替代项目自身配置。

### 6. 强制生成 Docker 本地运行环境

初始化、审计并完善或刷新维护时，不论仓库原来是否存在 Docker，都必须先从后端 manifest、lockfile、版本文件、启动入口、扩展依赖、配置样例和外部服务提取真实运行要求，然后自动生成或语义完善根 `docker-compose.yml`。不得要求使用者手工选择通用 PHP、Node、Python、Java 或 Go 模板。

先预览，再写入缺失文件：

```bash
node <skill>/scripts/generate-docker-environment.mjs <repository>
node <skill>/scripts/generate-docker-environment.mjs <repository> --write-missing
```

脚本只负责有把握的机械生成。如果脚本无法识别某种框架、启动命令、扩展或服务，AI 必须继续检查技术栈 Profile、代码入口、CI 和已有运行脚本，亲自完成 `docker-compose.yml`，不能把“脚本不支持”作为交付结果。已有 Compose 必须保留有效配置并按当前后端环境更新；不能仅因为文件已经存在就跳过。

最终 Compose 至少覆盖后端应用、实际必需的数据库/缓存、真实后台进程和健康检查；前端是否进入 Compose 由当前项目开发方式决定。Dockerfile、Nginx、入口脚本等辅助代码统一放入 `docker/`，根 `run.sh` 提供统一命令。生成后补全 `.crmeb/project.json` 的 Docker、服务、命令和文档字段。

### 7. 文档、运行与交付

使用 [文档模板](references/templates.md) 和 [文档覆盖矩阵](references/documentation-coverage.md)，根据环境分析结果建立根入口、项目概览、开发配置、架构、前后端、API、数据库、测试、Docker、部署、运维、安全、排障、备份恢复和升级文档。正式事实、AI 规则、过程记录和测试索引职责分离：过程记录写入契约 `directories.process` 声明的目录（缺省 `tests/`，即与测试索引同目录）；不得用空壳文档冒充完成。

必须扫描 `.crmeb/project.json` 和环境分析器识别出的每个后端、管理端、Web、UniApp、小程序或其他独立应用，逐一根据当前 manifest、构建配置、入口/路由、环境样例、API 客户端、测试和 CI 更新应用目录 `README.md` 与 `help/docs/applications/<应用ID>.md`。不能只更新总 README 或用一份前端说明覆盖多个项目。代码变更后重新核对受影响应用；无需改文档时也要在交付报告中说明检查依据。同一应用在 `.crmeb/skills/` 下的目录说明与核心功能开发文档，必须与该应用的 `README.md`、`help/docs/applications/<应用ID>.md` 事实一致，三者不得互相矛盾。

Docker 环境必须至少验证 Compose 解析、镜像构建、服务健康、源码挂载、前后端访问、测试和数据保留。触发升级交付时，生成 manifest、迁移、发布 payload、校验和和回滚说明，并在隔离环境验证旧版到新版。

### 8. 机器验收

运行：

```bash
node <skill>/scripts/check-project-standard.mjs <repository>
```

再执行 `.crmeb/project.json` 声明的 `check`、`test`、`build` 和必要 smoke 命令。根据风险逐级报告文本检查、静态检查、构建测试、运行验证和生命周期验证，不得夸大覆盖范围。

检查脚本已校验技能骨架、`appPath` 应用覆盖、`directoryGuide` 与 `coreFeatures` 的存在性及索引登记；文档内容是否与当前代码一致仍需逐项核对，占位符或 TODO 未清除即视为未完成，不得用脚本通过代替。

## 完成标准

- 新开发者或 AI 能在 5 分钟内找到规则、配置和最短启动路径。
- `.crmeb/skills/` 每个技能符合统一骨架（`SKILL.md` + `references/`）；后端与每个独立前端应用都有对应技能，技能与 `.crmeb/project.json` 的 `applications` 一一对应。
- 每个代码目录都有经当前代码核对的「项目目录说明」与核心功能开发文档；新 AI 会话无需遍历全仓即可定位任一核心功能的入口链路。
- 同一应用的技能文档、应用目录 `README.md` 与 `help/docs/applications/<应用ID>.md` 事实一致，没有跨应用混用。
- 标准命令可从仓库根执行或有明确映射，失败信息能定位缺少的环境或服务。
- 后端、前端、API 和数据库改动有一致的全栈同步与测试清单。
- Docker 日常启动不删除数据，破坏性重置需要明确动作和确认。
- 非“仅审计”任务已经根据当前后端运行环境自动生成或更新根 `docker-compose.yml`；缺失该文件或仅保留通用占位模板不得声明完成。
- `docker-compose.yml`、`run.sh` 和 `docker/` 与当前项目真实运行时、版本、服务、端口和进程一致，不保留未经核对的模板值。
- 文档覆盖由项目能力动态计算；数据库、UniApp、worker、升级或多前端存在时，对应文档不可缺省。
- 后端及每个独立前端应用都有从当前代码核对过的就近 README 和详细说明，命令、端口、API、构建与发布事实没有跨项目混用。
- CI 执行与本地相同的 `check/test/build` 契约。
- 触发升级的改动可生成可追溯、可校验、经过验证的候选升级包。
- 自动检查通过；未通过项、动态验证缺口和需负责人决策的内容明确列出。
