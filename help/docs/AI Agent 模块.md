# AI Agent 模块

> 核对日期：2026-09-24。本文是 `backend/modules/aiagent/` 的功能真源，路由、服务与数据表均以模块代码为准；模块目录内的 `README.md` 只保留开发视角速览。

## 1. 模块概述

AI Agent（插件标识 `aiagent`）是默认安装的后台智能体模块，提供四块能力：

| 能力 | 说明 |
|---|---|
| AI 对话 | 后台对话工作台，支持流式（SSE）与非流式响应、工具调用循环、Skill 按需加载与确认卡交互 |
| 模型配置 | 对接 OpenAI 兼容协议与一号通 AI 两类模型（模型管理为系统能力，配置存核心表 `eb_ai_model`） |
| MCP 连接 | 连接系统内置 MCP 服务（`service_mcp` 模块）与任意远程 Streamable HTTP MCP Server，统一发现与调用工具 |
| 自动化任务 | 按周期无人值守执行指令，复用对话执行引擎，运行过程可回放，完成后邮件/企业微信通知 |

模块自带入口菜单「AI」，点击直达对话页；模型、连接器（MCP Server）、Skill 与任务管理均为对话页内的管理面板，无独立页面菜单。接口权限节点由 Installer 注册（33 个 `auth_type=2` 节点），角色按需勾选。

依赖关系：

- `service_mcp`（软依赖）：通过 HTTP JSON-RPC 调用 `POST /api/mcp`，不引用其任何类；可用性 = 模块启用 + 系统配置 `mcp_enabled=1` + `mcp_appkey` 非空。
- AI 模型：经 `app/services/ai/AiModelServices::getChatHandler()` 获取适配器，协议支持 `openai_compatible`（DeepSeek、通义千问、Kimi、GLM、豆包、OpenAI 及 vLLM/Ollama 自建网关）与 `yihaotong`（一号通，全局凭证）；模型 ID 传 0 时直接走一号通 AI，开箱可用。
- 队列：自动化任务经 `crmeb\basic\BaseJobs` 派发；未开启 `queue_open` 或缓存驱动非 Redis 时同步执行（手动「立即执行」会阻塞请求）。

## 2. 对话与工具循环

### 2.1 对话流程

入口 `AgentChatService`（`backend/modules/aiagent/service/AgentChatService.php`）：

1. 消息校验（单条上限 8000 字）→ 会话归属校验或新建会话（标题取首条消息前 40 字）；
2. 组装上下文：挂载连接器工具定义、Skill（显式选择注入全文，否则仅注入目录由模型按需加载）、历史转录（最近 50 条压缩，历史 user/assistant 各限 4000 字、tool 限 600 字）、系统提示词与 `.crmeb/AGENTS.md` 兜底规范（截断 16000 字，恒定最先注入）；
3. 工具循环：模型输出 `{"tool_call":{"name":...,"arguments":{...}}}` JSON 时解析执行（本模块不使用原生 function calling，属文本协议工具循环，兼容一号通 `<|FunctionCallBegin|>` 私有标记）；工具结果以「权威工具结果」块回注（上限 16000 字，Skill 附属资料 32000 字），直至模型给出最终答复或达到轮数上限（流式路径固定 6 轮，非流式/任务路径按 `max_steps` 3-30）；
4. 落库：会话消息含 `user` / `assistant` / `tool` / `confirm` 四种角色。

### 2.2 流式事件（SSE）

`chat_stream` / `confirm_stream` 以 `data: {json}` 帧输出，事件依次为：

| 事件 | 含义 |
|---|---|
| `start` | 会话就绪 |
| `skills` | 当前挂载的 Skill 清单（随按需加载动态更新） |
| `reasoning` | 思维链增量（`<think>` 内嵌思维链改道于此） |
| `delta` | 正文增量 |
| `tool` | 一次工具调用及结果（前端清空本轮流式前言） |
| `confirm` | 需用户确认的交互卡 |
| `followups` | 推荐追问（最多 3 个、30 字内） |
| `replace` | 正文被过滤/校正后整段替换 |
| `done` / `error` | 结束或失败 |

客户端断开时已生成的部分回复先落库再退出；流式输出需代理层关闭缓冲（响应头 `X-Accel-Buffering: no`）。

### 2.3 确认卡（写工具交互）

声明 `interaction: "execute"` 的写工具执行前进入 `confirm` 状态：服务端保存参数快照与当轮上下文，客户端弹确认卡；`confirm_stream` 按 `approve/reject` 续跑。状态机 `pending → approved/rejected/expired`，以消息 payload 乐观锁防重放；新用户消息会使全部 pending 卡过期。确认卡展示对 password/secret/token/api_key 等敏感字段打码。同一签名（工具名+参数）每轮对话只执行一次。

### 2.4 对话层虚拟工具

三个虚拟工具只在对话层拦截，不注册到 MCP：

| 工具 | 用途 |
|---|---|
| `crmeb_ask_user` | 模型向用户发起单选/多选/文本问询（回答经 `confirm_stream` 返回） |
| `crmeb_skill_load` | 按需加载 Skill 正文或附属资料（目录内保留名 `EXAMPLES.md` 不进入上下文） |
| `crmeb_task_create` / `crmeb_task_list` | 模型代用户创建/查询自动化任务（新建任务默认只读自主级别） |

无人值守场景不注册这三个工具（无用户可交互，且防止任务自我修改调度）。

## 3. 模型 / MCP / Skill 配置

### 3.1 模型

模型管理为系统能力（核心表 `eb_ai_model`，管理端「AI 模型管理」），字段含协议、`api_key`、`base_url`、模型标识、温度、`max_tokens`、`supports_tools` 等。对话页模型清单首位固定提供「一号通AI」虚拟项（模型 ID 0），复用全局 `yihaotong_appid/appsecret`，未配置任何模型时也可直接对话。

### 3.2 MCP Server 连接

连接器存 `eb_ai_mcp_server`，与后台管理页同源同权；对外工具名统一 `{server_key}.{tool}` 前缀，内置服务为 `builtin.*`。

- **内置 MCP（`builtin`）**：指向本系统 `POST /api/mcp`。Installer 预置该记录，URL 取 `site_url` 拼接 `/api/mcp`（官方 Docker 部署自动改用 `http://crmeb_nginx/api/mcp`）；`api_key` 留空表示运行时跟随系统配置 `mcp_appkey`（service_mcp 系统账号密钥的镜像，系统侧轮换后无需手动同步）。工具清单缓存为空时自动补同步一次（失败静默，10 分钟冷却）。
- **远程 MCP**：JSON-RPC 2.0 over Streamable HTTP（协议版本 `2024-11-05`），仅支持 HTTP 端点（拒绝 `command` 本地进程类型）；支持 `mcpServers` JSON 批量导入（Cursor / Claude Desktop 风格）。认证请求头 `X-Mcp-Key`，可附加自定义 headers，超时 3-120 秒。`tools/list` 游标分页拉取并缓存。
- **预置连接器**：`builtin`（启用）、`tavily` 联网搜索（keyless 免费模式，启用）、`amap` 高德地图（停用，需填 Key）、`memcp` 记忆库（停用，需填密钥）。
- **安全**：所有出站 URL 经 `SsrfGuard` 校验（默认仅公网；`ai_allow_private_endpoint=1` 时放行内网，云元数据地址始终禁止），DNS 解析钉扎防重绑定；headers 名称须为合法 token。
- 同步与测试：「同步工具」写回工具清单缓存（`interaction`/`label` 随缓存，service_mcp 侧声明变更后需重新同步才生效）；「测试连接」完整握手并拉取清单，不落缓存。

### 3.3 Skill 体系

一个 Skill 即 `.crmeb/skills/{key}/SKILL.md` 一个文件：frontmatter（`name/description/status`）为元信息，正文为注入模型的工作规则。**存储即文件系统，无数据表**。

- key 正则 `^[a-z][a-z0-9_-]{0,63}$`；文件写入上限 200KB（临时文件 + rename 原子写）；注入模型时截断 32768 字节。
- 附属资料：目录内除 `SKILL.md` 与保留名 `EXAMPLES.md` 外的 `.md` 文件，由模型经 `crmeb_skill_load` 的 `file` 参数按需加载（realpath 校验防目录穿越）。
- 与 `.crmeb/AGENTS.md` 的关系：AGENTS.md 恒定最先注入作兜底规范；Skill 更具体，冲突时以 Skill 为准。

**编写要求**（新增或修改 Skill 时遵循）：

- **职责边界**：技能只写工具 `description`/`inputSchema` 承载不了的**流程编排、查证顺序、失败自愈与展示规范**；参数级约束（枚举、格式、校验规则）一律在 MCP 工具侧维护，禁止在技能中复述，避免双源漂移；
- **结构**：开头一句话圈定适用范围与涉及工具；正文按任务阶段组织（查证 → 决策确认 → 执行 → 复核），配「参数构造要点」「常见报错与处理」「边界与引导」小节；有固定输出形态的附可直接套用的模板；
- **语气**：简体中文指令式，每条规则可执行、可判定（「必须/禁止/先…再…」），不写背景科普；
- **篇幅**：正文建议 8KB 以内——正文随每次对话注入，所有启用技能共享上下文预算，超过 32768 字节会被截断；
- **frontmatter**：`name` 中文短名（后台与技能目录展示），`description` 单行概括「做什么 + 何时触发」（供模型按需加载判断），`status` 1 启用 / 0 停用；
- **变更登记**：新增/删除内置 Skill 须同步本节清单表与模块 README 的「内置资产」；
- **回归**：涉及提示词的改动按模块 README「定向回归」人工过一遍对话工具循环与确认卡。

- 内置 7 个 Skill（`backend/modules/aiagent/.crmeb/skills/`）：

| key | 名称 | 职责 |
|---|---|---|
| `crmeb-assistant` | CRMEB 业务助手 | 用 `builtin.*` 工具查询商品/订单/售后/优惠券/用户数据，并核对各统计工具口径差异 |
| `user` | 用户管理 | 用户写操作（资料/备注/分组/标签/等级/付费会员）的查证流程、覆盖语义、批量核对与边界 |
| `coupon` | 优惠券管理 | 创建优惠券的查证与确认流程、参数构造要点、报错自愈；停用删除引导后台；发放效果统计口径 |
| `crontab` | 系统定时任务 | 区分系统定时任务与 AI 自动化任务两类概念，指引任务菜单位置 |
| `doc` | 功能说明 | 功能归属/配置入口/支持状态查证；附件 `功能索引.md` 为历史版本功能速查（仅明确问及时加载） |
| `emailformat` | 邮件格式 | 约束 `builtin.crmeb_email_send` 正文必须为内联样式简单 HTML |
| `systemsetting` | 系统设置 | 查询/修改系统配置的用法与确认卡授权流程 |

Skill 管理依赖 Web 运行用户对模块 `.crmeb/skills/` 目录的写权限；删除 Skill 会清理目录内全部文件。

## 4. 自动化任务

### 4.1 任务配置

任务存 `eb_ai_agent_task`，字段含指令（instruction）、模型、连接器、Skill、调度、自主级别、轮数（3-30）与软超时（60-1800 秒）、通知渠道（邮件 / 企业微信群机器人）。

调度类型：每隔 N 分钟（1-43200）、每日 HH:MM、每周（周几|HH:MM）、每月（几日|HH:MM，仅 1-28）、每年（月|日|HH:MM）。

自主级别：

| 值 | 含义 | 现状 |
|---|---|---|
| 1 | 只读（默认） | 需确认写工具对模型不可见 |
| 2 | 需审批 | **一期未实现**，保存时直接拒绝该取值 |
| 3 | 全自动 | 保留全部工具并豁免确认，仅超级管理员（level=0）可设 |

### 4.2 调度与执行链路

1. **调度扫描**：定时任务 `aiAgentTask`（`eb_system_crontab` 预置记录，每分钟）调用 `AgentTaskServices::dispatchDueTasks()`：回收孤儿运行 → 取到期任务（单次上限 50）→ **先推进 next_run_time 再入队**（保证扫描幂等）→ 创建运行记录（唯一索引防重复投递）→ `AgentTaskJob` 入队。timer 进程内毫秒级完成，LLM 多轮调用全部在队列容器执行。
2. **执行**：`AgentTaskRunnerService::run()` 为队列唯一入口——数据库 CAS 领取（仅 `running 且 started_at=0` 可写 started_at，并发只有一个赢家）→ 新建独立审计会话（运行记录回写 conversation_id，全过程可回放）→ 以 `unattended=true` 与任务自主级别复用 `AgentChatService::chat()` 执行 → 软超时到点强制收尾并标记 `timeout`。
3. **交付保障**：主路径为模型自主调用交付类工具（邮件/企业微信，收件目标受工具端白名单硬约束）；运行结束校验工具轨迹，未交付则按通知渠道兜底投递（邮件走 `EmailChannel`，企业微信直连 Webhook 配置 `aiagent_qywechat_webhook`）；失败/超时同样发送失败提醒。
4. **状态机**：`running → success / failed / timeout`；运行记录含摘要、错误、工具调用次数、token 用量与起止时间。队列 Job 恒定领取即删除（不走队列级重试），失败由状态机标记并通知，避免 LLM 多轮重复消耗。
5. **无人值守邮件白名单**：收件人仅允许任务登记的 `notify_email` 与系统配置 `aiagent_email_recipients`，两者皆无则拒绝发送。

## 5. 数据表

模块自有 5 张表（安装 `CREATE TABLE IF NOT EXISTS` 幂等创建，卸载 DROP）：

| 表 | 用途 | 关键字段 |
|---|---|---|
| `eb_ai_agent_conversation` | 会话 | `admin_id`（归属）、`title`、`is_pin`（存量安装自动补列） |
| `eb_ai_agent_message` | 消息 | `conversation_id`、`role`（user/assistant/tool/confirm）、`content`、`tool_name`、`tool_payload`（工具参数/结果/确认卡快照 JSON） |
| `eb_ai_agent_task` | 自动化任务 | `instruction`、`schedule_type/schedule_value`、`autonomy`、`max_steps`、`timeout`、`notify_channels/notify_email`、`next_run_time` |
| `eb_ai_agent_task_run` | 运行记录 | `task_id`、`conversation_id`（回放）、`status`、`trigger_type`（1 调度/2 手动）、`summary`、`error`、`tool_calls`、`usage`；唯一索引 `uk_task_trigger` 防重复投递 |
| `eb_ai_mcp_server` | MCP 连接器 | `server_key`（唯一，工具命名空间前缀）、`url`、`api_key`、`headers`、`tools`（清单缓存）、`tools_sync_time` |

依赖的核心表（非自有）：`eb_ai_model`（模型）、`eb_system_crontab`（调度记录）、`eb_system_config` / `eb_system_config_tab`（配置）、`eb_system_menus`（菜单权限）。

模块预置的系统配置（「AI 自动化任务」配置分组）：`aiagent_qywechat_webhook`（企业微信群机器人 Webhook）、`ai_allow_private_endpoint`（是否允许内网 AI/MCP 地址，默认 0）、`aiagent_email_recipients`（无人值守邮件收件白名单）。

## 6. 接口与权限

路由前缀 `/adminapi/aiagent`，中间件链：跨域 → 管理员登录（`AdminAuthTokenMiddleware`）→ 角色权限（`AdminCheckRoleMiddleware`，按 `system_menus` 中 `auth_type=2` 且 `api_url` 匹配）→ 操作日志。数据层会话/任务一律按 `admin_id` 归属校验。

| 分组 | 方法与路径 | 用途 |
|---|---|---|
| 对话 | GET `/options` | 模型/连接器/Skill 选项 |
| | GET `/conversations`、GET `/messages/:conversation_id` | 会话列表 / 消息明细 |
| | DELETE `/conversation/:id`、PUT `/conversation/:id` | 删除 / 改标题与置顶（仅本人） |
| | POST `/chat` | 非流式对话 |
| | POST `/chat_stream` | SSE 流式对话 |
| | POST `/confirm_stream` | SSE 处理确认/问询卡片 |
| 任务 | GET `/tasks`、POST `/task`、GET/PUT/DELETE `/task/:id` | 任务 CRUD（分页、仅本人） |
| | PUT `/task/:id/status` | 启停 |
| | POST `/task/:id/run` | 立即执行（入队） |
| | GET `/task/:id/runs`、GET `/task_runs`、GET `/task_run/:id` | 运行历史 / 全量记录 / 运行详情（含会话回放） |
| 连接器 | GET `/mcp_servers`、POST `/mcp_server`、GET/PUT/DELETE `/mcp_server/:id` | 连接器 CRUD（POST 支持 mcpServers JSON 导入） |
| | PUT `/mcp_server/:id/status` | 启停 |
| | POST `/mcp_server/:id/sync`、POST `/mcp_server/:id/test` | 同步工具清单 / 连通性测试 |
| Skill | GET `/skills`、POST `/skill`、GET/PUT/DELETE `/skill/:key` | Skill CRUD（文件系统） |
| | PUT `/skill/:key/status` | 启停 |
| | GET/POST/DELETE `/skill/:key/attachment`、GET `/skill/:key/attachments` | 附属资料管理（文件名经查询参数 `file` 传递） |

路由变量约定：Skill key 使用全局 pattern `[A-Za-z0-9_-]+`（连字符不受默认 `[\w.]+` 限制）；Skill 相关接口 `:key` 即目录名。

## 7. 已知限制

| 限制 | 说明 |
|---|---|
| 「需审批」自主级别未实现 | `autonomy=2` 保存时直接拒绝，审批链路待后续版本 |
| MCP 仅支持远程 Streamable HTTP | 拒绝 `command` 本地进程类型 |
| 流式对话工具轮数固定 | 流式路径单次最多 6 轮（不读任务配置）；自动化任务走非流式路径，受 `max_steps` 3-30 控制 |
| 工具交互声明依赖缓存 | `interaction` 随「同步工具」写入缓存，service_mcp 侧变更后需重新同步内置 MCP |
| 队列降级阻塞 | 未开启队列时手动「立即执行」同步等待完成 |
| Docker 回环地址不可达 | 容器内 localhost 指向容器自身；内置地址自动候选修复，自定义连接器需填容器网络地址 |
| 早期安装的 service_mcp 兼容 | 需禁用再启用一次以补种系统账号与 `mcp_appkey` 镜像，随后重新「同步工具」 |
| 上下文限长 | 用户消息 8000 字；Skill 正文/附属资料 32768 字节；AGENTS.md 16000 字；工具结果 16000 字（附属资料 32000） |
| Skill 管理需文件写权限 | Web 运行用户对模块 `.crmeb/skills/` 需可写 |

## 8. 相关文档

- MCP 服务与内置工具清单：[MCP 接口](api/MCP接口.md)
- 模块与驱动体系：[模块与驱动](模块与驱动.md)
- 模块开发视角速览：`backend/modules/aiagent/README.md`
