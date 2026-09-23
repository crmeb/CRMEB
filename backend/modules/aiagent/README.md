# aiagent/ — AI Agent 模块

> **功能与接口的完整说明见 [help/docs/AI Agent 模块.md](../../../help/docs/AI%20Agent%20模块.md)（真源）**：对话与工具循环、模型/MCP/Skill 配置、自动化任务、数据表字段、接口一览、菜单权限、已知限制。
> 本文只保留模块开发视角的内容：目录结构、数据表清单、内置资产、开发约定与回归命令。

## 功能概述

后台 AI 智能体模块，提供四块能力：

- **AI 对话**：左侧会话列表 + 对话框，可选模型（默认一号通 AI），支持 SSE 流式输出、MCP 工具调用循环、危险操作确认卡、`crmeb_ask_user` 问询卡、Skill 按需加载与推荐追问。
- **模型管理**：复用核心 `ai/model/*` 路由（OpenAI 兼容协议 / 一号通协议），支持完整 URL 模式、连通性测试、默认模型设置。
- **MCP Server 管理**：登记远程 MCP Server（Streamable HTTP），同步工具清单、测试连接、启停控制；预置系统内置 MCP（`server_key=builtin`）指向 `service_mcp` 的 `/api/mcp`。
- **自动化任务**：定时无人值守执行指令并把结果推送（邮件 / 企业微信机器人），含运行记录与全过程审计回放。

会话直接挂载所选模型，不依赖独立 Agent 实体。菜单形态：一级菜单「AI」点击直接进入对话页；模型/MCP/Skill/任务管理以**遮盖层**在对话页内打开（`append-to-body` 弹窗），不作为独立子菜单。

## 目录结构

```
aiagent/
├── controller/admin/
│   ├── AiAgentController.php      # 对话（普通/流式/确认续跑）、会话、消息、配置选项
│   ├── McpServerController.php    # MCP Server CRUD + 同步/测试/状态 + mcpServers JSON 导入
│   ├── SkillController.php        # Skill CRUD + 状态切换 + 附属资料管理
│   └── TaskController.php         # 自动化任务 CRUD/启停/立即执行/运行记录
├── service/
│   ├── AgentChatService.php       # 对话主流程（工具循环 / 流式 / 确认续跑 / 质量兜底）
│   ├── AiAgentServices.php        # 会话与消息数据服务
│   ├── McpServerServices.php      # MCP Server 数据服务（密钥脱敏、地址修复、工具同步）
│   ├── McpToolService.php         # 工具发现与调用（内置 + 远程 + 对话层虚拟工具）
│   ├── SkillRepository.php        # Skill 文件读写（frontmatter 解析、附件路径校验）
│   ├── AgentTaskServices.php      # 任务与运行记录数据服务、调度扫描
│   └── AgentTaskRunnerService.php # 单次运行的执行编排（CAS 领取、交付校验、兜底投递）
├── mcp/RemoteMcpClient.php        # 远程 MCP 客户端（JSON-RPC 2.0 / Streamable HTTP）
├── jobs/AgentTaskJob.php          # 任务执行队列任务
├── crontab/AiAgentTaskDispatcherTask.php  # 调度器（mark=aiAgentTask，每分钟）
├── dao/ model/                    # 5 张自有表的 DAO 与 Model
├── route/admin.php                # adminapi 路由（aiagent 分组）
├── .crmeb/                        # 智能体自有目录（随模块发布/卸载）
│   ├── AGENTS.md                  # 内置兜底规范（恒定最先注入对话，后台不可删停）
│   └── skills/                    # 内置 Skill 与附属资料
├── App.php                        # 注册调度器定时任务
├── Installer.php                  # 安装/卸载/菜单迁移 + 内置 MCP、配置、定时任务预置
├── event.php                      # 事件监听注册（当前无监听）
└── plugin.json                    # 插件元数据（type: service，default_install）
```

## 数据表

模块自有表 5 张（`Installer::runMigrate()` 创建，卸载时 DROP）：

| 表名 | 说明 |
|------|------|
| `eb_ai_agent_conversation` | 会话（agent_id 预留恒为 0、admin_id、title、is_pin 置顶） |
| `eb_ai_agent_message` | 消息（role: user/assistant/tool/confirm、content、tool_name、tool_payload） |
| `eb_ai_agent_task` | 自动化任务（指令、连接器、Skill、自主级别、调度、通知、下次运行时间） |
| `eb_ai_agent_task_run` | 任务运行记录（状态、触发方式、摘要、错误、工具次数、用量、起止时间） |
| `eb_ai_mcp_server` | MCP Server 注册表（url、api_key、headers、timeout、tools 同步缓存、server_key 唯一） |

依赖核心表 `eb_ai_model`（模型管理，**非本模块自有表**）：本模块升级时幂等补充 `full_url` 列，卸载不删除该列。

## 内置资产（.crmeb/）

| 文件 | 职责 | 生命周期 |
|------|------|---------|
| `AGENTS.md` | 兜底规范，恒定最先注入；与 Skill 冲突时以 Skill 为准 | 随代码发布，后台不可删停 |
| `skills/{key}/SKILL.md` | Skill 正文（frontmatter: name/description/status） | 可在后台增删启停 |
| `skills/{key}/*.md` | 附属资料，由模型经 `crmeb_skill_load` 按需加载 | 同上 |
| `skills/{key}/EXAMPLES.md` | 维护者回归用例，**不进入模型上下文** | 同上（保留名） |

内置 Skill：`crmeb-assistant`（业务助手）、`systemsetting`（系统设置）、`doc`（功能说明）、`emailformat`（邮件格式）、`crontab`（系统定时任务）。

## 与 service_mcp 的关系

本模块**不直接引用** `service_mcp` 模块的类（避免跨模块互引），一律经 `RemoteMcpClient` 以 HTTP 调用其 `/api/mcp` 端点。因此：

- 业务工具（商品/订单/售后/优惠券/用户/配置等 33 个）由 `service_mcp` 提供，本模块只负责发现、授权与转发。
- 内置 MCP 可用性要求 `service_mcp` 已启用 + `mcp_enabled=1`（安装默认开启）+ `mcp_appkey` 非空（`McpToolService::available()`）。
- 内置服务工具缓存为空时读取会自动补同步一次（失败 10 分钟冷却），新装站点开箱即用；也可在 MCP 管理页手动「同步工具」。
- 工具的确认交互声明（`interaction`）随「同步工具」写入缓存，`service_mcp` 侧声明变更后需重新同步内置 MCP 才生效。

## 开发约定

- Skill 标识（目录名）正则：`/^[a-z][a-z0-9_-]{0,63}$/`——小写字母开头，仅含小写字母、数字、`-`、`_`。
- Skill 路由已显式声明 `Route::pattern(['key' => '[A-Za-z0-9_-]+'])`，因全局路由变量规则不含连字符。
- Skill 附属资料的文件名经查询参数 `file` 传递（文件名可能含中文与点号），不走路由变量。
- Web 运行用户需对 `modules/aiagent/.crmeb/skills/` 具备**写权限**。
- 任务指令与 Skill 正文都会进入模型上下文；改动涉及安全边界的提示词时，同步检查 `tests/aiagent/` 的回归断言。

## 定向回归

不依赖数据库，从 `backend/` 目录执行（仓库无全局 PHPUnit 配置）：

```bash
php tests/aiagent/AgentChatFlowTest.php
php tests/aiagent/BuiltinSkillInstructionsTest.php
php tests/mcp/McpContractTest.php
```

aiagent 的两个脚本**成功时无输出、退出码 0**，失败抛异常并打印原因。

## 注意事项

- **Docker 环境**：容器内 `localhost` 指向容器自身，MCP Server 地址应使用容器网络直连（如 `http://crmeb_nginx/...`）或宿主机映射地址（如 `http://host.docker.internal:8011/...`）。
- **首次启用**：早期安装启用的 MCP 模块需禁用再启用一次以补种系统账号与 `mcp_appkey` 镜像；随后需对内置 MCP 重新「同步工具」，新增业务工具才会进入工具缓存。
- **内置地址修复**：内置 MCP 保存地址不可达时，同步/测试连接会尝试候选地址并回写可用地址。
- **队列依赖**：自动化任务依赖队列与定时器进程；`queue_open` 未开启或缓存驱动非 Redis 时，`BaseJobs` 会退化为同步执行，手动「立即执行」将阻塞 HTTP 请求。
- 安装/启用时 `seed()` 只补缺不覆盖：已存在的内置 MCP 记录、配置项与定时任务不会被改写。
