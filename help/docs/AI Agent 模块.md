# AI Agent 模块

> 核对日期：2026-09-16。本文说明 `aiagent` 模块**当前代码实现**与用户可见功能，事实来源为仓库代码（路径与行号可核对）。强制开发约定以 [AI 开发入口](../../.crmeb/AGENTS.md) 索引的规则为准，本文不复制规则正文。

本文中的「模块」即后台插件体系中的可安装单元（`plugin.json`），两个词指同一对象。

## 1. 模块定位

| 项目 | 值 |
|---|---|
| 模块 key | `aiagent` |
| 名称 | AI Agent |
| 类型 | `service` |
| 版本 | `1.0.0` |
| 清单标记 | `default_install=true`（新装自动安装，可卸载/禁用；未标记 `required`） |
| 代码位置 | `backend/modules/aiagent` |
| 管理端资源 | `frontend/admin/src/pages/plugin/aiagent` |
| 清单依赖声明 | `crmeb ^8.0`、`php ^8.0`（**未声明**对其他插件的依赖） |
| 数据表 | 5 张自有表，见[第 8 节](#8-数据表) |

模块提供三块能力：后台 **AI 对话工作台**、对话能力的**运行配置**（模型 / MCP 连接器 / Skill 规则文件）、以及**无人值守自动化任务**。

### 1.1 能力边界

- **模块自身不提供业务工具。** 商品、订单、售后、优惠券、用户、系统配置等工具由 `service_mcp` 模块的内置工具注册表提供（33 个，见 [5.3 节](#53-内置业务工具清单33-个)）。本模块通过 HTTP 调用其 `/api/mcp` 端点，**不直接引用该模块的类**，以满足「同级模块不得互相引用」的约束。
- **模型协议实现属核心层**：协议工厂与实现在 `backend/crmeb/services/ai`，模型登记业务在 `backend/app/services/ai`，模型表 `eb_ai_model` 与路由 `ai/model/*` 属核心 AI 能力域。本模块只消费这些能力，并提供其菜单权限节点（见 [10.2 节](#102-权限节点范围)）。
- **仅面向管理员**。对话、任务、Skill 全部挂管理员身份（`admin_id`），模块**不提供用户端路由**，商城用户不接触本模块。
- 模型与工具配置均需单独验收权限与外部调用，模型凭据不落前端。

### 1.2 依赖关系

```text
管理端页面 frontend/admin/src/pages/plugin/aiagent
      │  /adminapi/aiagent/*
      ▼
backend/modules/aiagent ──── HTTP POST /api/mcp ────▶ backend/modules/service/mcp
      │  RemoteMcpClient（JSON-RPC 2.0）                    ▲ 33 个业务工具
      │                                                     │
      │  模型调用                                            需同时满足：
      ▼                                                      service_mcp 已启用
crmeb/services/ai（OpenAI 兼容协议 / 一号通 AI）              + mcp_enabled=1
                                                            + mcp_appkey 非空
```

内置工具是**软依赖**：不满足上述三个条件时，仅「系统内置 MCP」不可用，后台登记的第三方远程 MCP Server 不受影响。

## 2. 功能总览

| 功能 | 用户入口 | 说明 |
|---|---|---|
| AI 对话 | 一级菜单「AI」（点击直达对话页） | 会话管理 + 流式对话 + 工具调用 + 交互卡片 |
| 模型管理 | 对话页左侧「模型管理」遮盖层 | 登记模型、连通性测试、设为默认、启停 |
| MCP Server 管理 | 对话页左侧「MCP管理」遮盖层 | 登记远程 MCP、同步工具、测试连接、启停 |
| Skill 管理 | 对话页左侧「Skill管理」遮盖层 | 以 Markdown 文件维护注入模型的规则，支持附属资料 |
| 自动化任务 | 对话页左侧「自动化任务」遮盖层 | 定时无人值守执行指令并把结果推送出去 |

四个「管理页」都是对话页内的**遮盖层**（`append-to-body` 弹窗形态），**不是独立菜单**——避免侧边栏堆叠。对话页左侧导航在遮盖层打开时仍可切换。

模型管理与对话使用**两套不同的接口前缀**：模型走核心路由 `ai/model/*`，其余走模块路由 `aiagent/*`，详见[第 9 节](#9-接口一览)。

## 3. AI 对话

### 3.1 会话与消息

- 会话按管理员隔离，只能读写自己的会话（`assertConversation` 逐次校验归属）。
- 新建会话时，标题取首条用户消息截断 40 字；支持**置顶**（`is_pin`）与**重命名**。
- 消息记录 `role` 取 `user` / `assistant` / `tool` / `confirm` 四类；`tool_payload` 存工具参数或结果 JSON。
- 新用户消息到达时，该会话内旧的**待确认操作自动过期**，避免旧确认卡与新上下文错位。
- 对话历史经 `buildTranscript` 组装为纯文本转录：保留最近若干可见问答、**锚定本轮的原始用户问题**，过长的工具链只保留摘要，避免上下文被历史工具结果挤满。

### 3.2 单轮对话流程

| 步骤 | 行为 |
|---|---|
| 1 | 校验或创建会话，写入 `user` 消息，过期旧的待确认操作 |
| 2 | 组装上下文：连接器（未显式指定时**自动挂载全部可用项**）、Skill、工具定义与白名单、系统提示词、历史转录、模型驱动 |
| 3 | 进入**工具调用循环**，最多 6 轮（`MAX_STEPS`） |
| 4 | 工具轮结束或达到上限后，追加一次**强制最终答复**请求（提示词要求不再输出工具 JSON） |
| 5 | 落库 `assistant` 消息，返回结果 |

工具调用循环每一轮的处理：

1. 解析模型输出中的 `{"tool_call":{"name":"...","arguments":{}}}` 纯 JSON 约定；同时兼容并剥离一号通平台私有标记 `<|FunctionCallBegin|>` / `<|FunctionCallEnd|>`。
2. **白名单校验**：只允许调用本轮挂载的工具。模型幻觉出的工具名不会被执行，而是回传可用清单引导纠正。
3. **相同调用去重**：工具名 + 参数完全相同的调用只允许尝试一次，防止模型对无效工具原样重试耗尽配额。
4. **确认拦截**：命中需确认的工具时落 `confirm` 消息并暂停返回，等待用户在页面操作（见 [3.4 节](#34-交互卡片)）。
5. **本地虚拟工具**在 MCP 分发之前被拦截处理：`crmeb_skill_load`（Skill 按需加载）、`crmeb_task_create`（创建自动化任务）、`crmeb_task_list`（查询任务清单）。它们不注册到 MCP，永不真实执行。
6. 其余工具经 `RemoteMcpClient` 转发到对应 MCP Server 执行，结果回填上下文继续下一轮。

非流式接口 `chat` 返回 `conversation_id`、`content`、`reasoning`、`model`、`usage`、`tool_calls`、`skills`、`followups`。命中确认卡时改为返回 `confirm` 对象（含 `message_id`、`tool_name`、`arguments`、`interaction`、`display`），`content` 为空。

### 3.3 流式对话（SSE）

`chat_stream` 与 `confirm_stream` 均以 `text/event-stream` 逐块下发，事件载荷为 JSON。服务端显式设置 `X-Accel-Buffering: no` 关闭 nginx 代理缓冲，并清空输出缓冲、取消脚本超时。

| 事件 | 载荷 | 前端行为 |
|---|---|---|
| `start` | `conversation_id` | 新会话首次对话时回写会话 ID |
| `skills` | `skills`（已加载 Skill 清单） | 整体替换，用于渲染 Skill 标签 |
| `reasoning` | `text` | 思维链增量；首次到达时自动展开「深度思考」折叠块 |
| `delta` | `text` | 正文增量 |
| `tool` | `name`、`arguments`、`result` | 把已有正文转入思考区，记录「调用 xxx」标签 |
| `confirm` | `message_id`、`tool_name`、`arguments`、`interaction`、`display` | 插入确认卡/问询卡，本次流式结束等待用户操作 |
| `done` | `tool_calls`、`skills` | 结束；`tool_calls` 为后端权威清单，整体覆盖前端累积值 |
| `followups` | `questions` | 渲染推荐追问胶囊 |
| `replace` | `content` | 正文被过滤、清洗或事实校正后**原位替换**已输出内容 |
| `error` | `msg` | 由控制器捕获异常后下发，前端统一提示 |

### 3.4 交互卡片

**执行确认卡**（`interaction=execute`）适用于会修改数据或对外发送的工具。当前内置写工具 4 个：`builtin.crmeb_config_update`、`builtin.crmeb_email_send`、`builtin.crmeb_qywechat_send`、`builtin.crmeb_driver_config_update`；远程 MCP 工具若在 `tools/list` 中声明了 `interaction`，同步后同样生效。

- 卡片标题与参数优先取模型输出的 `display`（中文标题 + 中文参数说明）；模型未提供时用工具定义的中文描述与入参 Schema 兜底，避免卡片直出工具名与英文字段名。
- 当参数内容以 `<` 开头（HTML 富文本正文类，如发邮件）时，卡片提供 **iframe 沙箱内容预览**。
- 用户**确认**后经 `confirm_stream` 续跑：**按确认时的快照重建上下文并以快照参数执行，不重新让模型生成参数**；已加载的 Skill 一并预载，模型无需重复加载。
- 用户**拒绝**执行类卡片时不下发模型调用，直接返回固定话术，系统不做任何修改。

**问询卡**（`interaction=ask_user`，虚拟工具 `crmeb_ask_user`）用于需要用户做主观选择、补充条件或拍板的场合。支持 `single` 单选、`multi` 多选、`text` 开放输入，选项 2–4 个并可允许自定义输入。用户提交的答案被规范化为工具结果（选项 value 映射回 value+label，附自定义文本，自定义文本上限 500 字）回传模型继续；跳过时回传「未回答，请基于已有信息继续，不要重复追问」。

**正文提问兜底**：若模型没有调用 `crmeb_ask_user`，而是直接在正文里向用户提问，服务端由 `looksLikeQuestionToUser` 判定后**强制把该回复转为问询卡片**，保证前端一定弹出输入框。判定宁缺勿滥：正文超过 300 字、或含表格/标题/代码块等数据特征时视为完整答复，不转换。

### 3.5 回答质量保障机制

这些机制是模块相对「裸调模型」的主要增值点：

| 机制 | 作用 |
|---|---|
| 当前时间锚点注入 | 模型自身无法获知日期，注入系统时间避免照抄文档示例日期（无人值守下尤为关键） |
| 输出纪律约束 | 禁止正文出现内心独白、犹豫与自我纠错；正文第一句必须是正式回答或工具调用 JSON |
| 事实校正（grounded 校验） | 工具确实返回了非空数据、但模型声称「未查到/列表为空」时，先要求模型重读权威工具结果；仍失败则**直接下发系统确认的工具结果 JSON**，不让模型把有数据的结论说成没有 |
| 叙述中断续跑 | 正文只说了「接下来我将…」而未交付实际内容时，注入提示要求继续（最多 2 次，防死循环） |
| 空响应重试 | 上游 HTTP 200 但零正文零思考时透明重试 1 次；「有思考无正文」则提示推理模型可能耗尽 `max_tokens` |
| 残缺工具 JSON 自愈 | 输出被长度截断导致工具调用 JSON 无法解析时，注入系统提示要求重新输出（限 1 次），仍失败则优雅兜底 |
| 私有标记剥离 | 剥离 `<|FunctionCallBegin|>` 等平台私有函数调用标记与 `think` 内联思考块 |
| 推荐追问 | 回答后由模型产出最多 3 条相关追问（`{"followups":[...]}`），前端渲染为可直接点击发送的胶囊 |

### 3.6 会话管理

会话列表（按更新倒序，含置顶）、消息记录读取、删除会话、修改会话（标题 / 置顶）。`is_pin` 未传时以 `-1` 表示「不修改」，与「取消置顶 0」区分。

## 4. 模型管理

本功能由**核心 AI 能力域**实现，模块提供页面入口与权限节点。

### 4.1 模型登记

模型记录存于核心表 `eb_ai_model`（由安装 SQL 创建，**不是**模块自有表，卸载不删除）。字段含名称、协议、模型 ID、接口地址、密钥、能力标记（工具调用/视觉）、上下文窗口、`max_tokens`、采样温度、默认标记、排序、状态。

模块对 `eb_ai_model` 只做一处结构变更：安装/升级时若缺 `full_url` 列则补上（`tinyint`，默认 0，注释「完整URL模式：1=请求地址已是完整接口路径，不再补全 /chat/completions」）。该列用于兼容「服务端点已给出完整路径」的厂商。

### 4.2 支持的协议

| 协议 | 说明 |
|---|---|
| `openai_compatible` | OpenAI 兼容协议，覆盖 DeepSeek、通义千问、Kimi、智谱 GLM、豆包、OpenAI 及 vLLM/Ollama 等自建网关。每条模型自带 `api_key` |
| `yihaotong` | 一号通 AI，凭据复用全局配置（系统内置「一号通登录/设置」写入的 `yihaotong_appid` / `yihaotong_appsecret`），无需在表中登记记录 |

### 4.3 默认模型与「一号通AI」

- 对话的 `model_id = 0` 表示**系统内置一号通模型**，直接走一号通协议，**不依赖任何登记记录**，因此开箱即可对话。
- 存在登记的默认模型时，一号通项在对话页模型列表首位退化为普通选项（名称显示「一号通AI」），不再标记默认。
- 未显式指定模型时，取 `status=1 AND is_default=1` 的登记模型；查不到则退化为启用中排序最前的一条。
- 停用模型会同时清除其默认标记。默认模型**无缓存层**，每次实时查库。

### 4.4 管理端操作

列表（按名称/模型 ID、协议、状态筛选）、新增、编辑、删除、启停、设为默认、排序、连通性测试。**保存时会先执行一次真实连通性测试**，`ok !== 1` 则报错且不落库（会消耗少量模型 Token）。密钥字段编辑时留空表示保持原密钥不变。

## 5. MCP Server 管理

模块把「AI 能用哪些工具」抽象为 MCP Server 记录（`eb_ai_mcp_server`），工具统一经 `RemoteMcpClient` 以 **JSON-RPC 2.0 over Streamable HTTP** 调用。

### 5.1 工具来源与命名

工具对外名称统一为 `{server_key}.{工具名}`：

| 来源 | server_key | 说明 |
|---|---|---|
| 系统内置 MCP | `builtin` | 指向本系统的 `/api/mcp` 端点，提供 33 个业务工具；工具名形如 `builtin.crmeb_product_list` |
| 第三方远程 MCP | 登记时的配置名 | 后台登记并同步工具后可用 |

后台登记并同步工具后可用。

### 5.2 内置 MCP（`builtin`）的特殊行为

- **`api_key` 留空**表示运行时跟随系统配置 `mcp_appkey`，无需在 MCP 记录里重复填密钥。
- 可用性要求三者同时满足：`service_mcp` 模块已启用、`mcp_enabled = 1`（安装默认开启，见「系统配置 → MCP服务」）、`mcp_appkey` 非空。不满足时该项从工具来源中剔除。
- **地址自动修复**：默认地址为 `site_url + /api/mcp`（Docker 部署下为 `http://crmeb_nginx/api/mcp`）。同步或测试连接时若保存地址不可达，会依次尝试候选地址（容器网络地址 → `site_url` → 当前请求域名），连通后**回写**可用地址。
- **交互声明随工具缓存下发**：工具是否需确认由 `tools/list` 返回的 `interaction` 字段决定，随「同步工具」写入缓存。因此 `service_mcp` 侧新增或变更交互声明后，需要在内置 MCP 上**重新同步工具**才生效；旧缓存中无声明的工具视为可直接执行。

安装时预置 3 条记录：`builtin`（系统内置 MCP，启用）、`tavily`（第三方搜索 MCP，启用，无密钥模式）、`amap`（高德地图 MCP，停用，地址内需替换为你自己的 Key）。**预置只补缺不覆盖**——已存在的记录不会被改写。

### 5.3 内置业务工具清单（33 个）

按业务域分组（`backend/modules/service/mcp/tools/`，自动发现）。标 **写** 的 4 个工具带 `interaction=execute`，调用前需用户确认。

| 业务域 | 工具（对话中的完整名为 `builtin.` + 名称） |
|---|---|
| 商品与分类 | `crmeb_product_list`、`crmeb_product_detail`、`crmeb_product_statistics`、`crmeb_product_ranking`、`crmeb_inventory_alert`、`crmeb_category_list`、`crmeb_category_detail` |
| 订单与物流 | `crmeb_order_list`、`crmeb_order_detail`、`crmeb_order_statistics`、`crmeb_order_logistics`、`crmeb_order_express_list` |
| 售后与退款 | `crmeb_after_sale_list`、`crmeb_after_sale_statistics`、`crmeb_refund_list`、`crmeb_refund_detail` |
| 优惠券 | `crmeb_coupon_list`、`crmeb_coupon_claim_list`、`crmeb_coupon_claim_statistics`、`crmeb_coupon_holding_statistics`、`crmeb_coupon_usage_statistics` |
| 用户 | `crmeb_user_list`、`crmeb_user_detail`、`crmeb_user_statistics` |
| 系统配置 | `crmeb_config_list`、`crmeb_config_tab_list`、**`crmeb_config_update`（写）** |
| 菜单与模块 | `crmeb_menu_list`、`crmeb_module_list` |
| 第三方驱动 | `crmeb_driver_list`、**`crmeb_driver_config_update`（写）** |
| 消息推送 | **`crmeb_email_send`（写）**、**`crmeb_qywechat_send`（写）** |

工具结果中的凭证类字段会脱敏（按 `input_type=password` 或字段名匹配 secret/key/token/webhook 等规则输出 `******`），手机号按前 3 + `****` + 后 4 输出。

### 5.4 对话层虚拟工具（不注册到 MCP）

| 工具 | 交互 | 作用 |
|---|---|---|
| `crmeb_ask_user` | `ask_user` | 向用户提问，弹出选择卡/输入框，答案回传模型 |
| `crmeb_skill_load` | 无（直接执行） | 按需加载 Skill 全文或其附属资料 |
| `crmeb_task_create` | `execute`（确认后创建） | 把周期性诉求落成自动化任务 |
| `crmeb_task_list` | 无（直接执行） | 查询当前管理员的自动化任务清单 |

### 5.5 登记与运维操作

- **JSON 配置导入**：支持直接粘贴 Cursor / Claude Desktop 风格的 `{"mcpServers":{...}}` 配置，新增时可一次导入多条（事务内）；编辑时只允许一条。`disabled: true` 映射为停用。
- **不支持本地进程类型**：配置含 `command` 会明确报错，仅提供远程 `url`（Streamable HTTP）接入。
- **测试连接**：执行完整 `initialize` 握手并拉取工具清单，返回耗时与工具数量，**不回写**工具缓存。
- **同步工具**：拉取远端全量工具清单并回写缓存（记 `tools_sync_time`）。修改 `server_key` 会清空已同步工具，因为工具对外名称的前缀随之失效。
- **密钥保护**：列表与详情不返回明文密钥；更新时留空或回传占位符 `__KEEP__` 表示保持原密钥。
- 删除 Server 后其远程工具立即对 AI 对话不可用。

### 5.6 远程 MCP 客户端行为

`RemoteMcpClient`（`mcp/RemoteMcpClient.php`）实现要点：协议版本 `2024-11-05`；请求头同时声明 `application/json` 与 `text/event-stream`，响应兼容**纯 JSON 与 SSE 两种载荷**；自动完成 `initialize` 握手、`Mcp-Session-Id` 会话保持（进程内握手一次）；`tools/list` 支持游标分页（最多 10 页，防御死循环）；鉴权密钥经 `X-Mcp-Key` 请求头传递；请求超时按 Server 记录配置（夹取 3–120 秒，默认 15）。远端返回 `isError` 时抛出可读错误。

## 6. Skill 管理

Skill 是**以文件维护、注入模型上下文的工作规则**（Markdown 指南、流程说明、口径约定等）。

### 6.1 文件存储约定

- 存储即文件系统，不走数据库：`.crmeb/skills/{key}/SKILL.md`（相对模块目录，随模块发布与卸载）。
- **key 规则**：`/^[a-z][a-z0-9_-]{0,63}$/` —— 小写字母开头，仅含小写字母、数字、`-`、`_`，最长 64 位。key 直接作为目录名。
- frontmatter 字段：`name`（显示名）、`description`（说明）、`status`（0 停用 / 1 启用）。正文即注入模型的规则内容，单文件上限 32768 字节。
- **附属资料**：Skill 目录下除 `SKILL.md` 与 `EXAMPLES.md` 之外的 `.md` 文件。附属资料**不随对话注入**，由模型经 `crmeb_skill_load` 的 `file` 参数按需加载（同一附件幂等，只加载一次）。`EXAMPLES.md` 约定为维护者回归用例，**不进入模型上下文**。
- 文件名经安全校验：禁止路径分隔符与 `..`，并以 `realpath` 前缀校验兜底防目录穿越。
- 停用后不进入目录也不被注入；删除会移除文件与空目录；Skill 与附件删除均不可恢复（前端有二次确认）。
- Web 运行用户需对 Skill 目录具备**写权限**。

### 6.2 注入语义

| 场景 | 行为 |
|---|---|
| 对话中**显式选择** Skill | 语义为「确定要用」，Skill 全文**直接注入**系统提示词 |
| 对话中**未指定** Skill | 不做全文注入，只下发**Skill 目录**（标识 \| 名称 \| 说明）并注册 `crmeb_skill_load` 工具，模型判断问题相关后按需加载全文——避免全部 Skill 正文占用 token |
| 确认卡续跑 | 恢复确认前已加载的 Skill（收窄到当前目录内） |

### 6.3 规则注入顺序

系统提示词按下述顺序组装，Skill 与兜底规则冲突时**以 Skill 为准**（Skill 更具体）：

1. 当前系统时间锚点（无人值守时更前置）
2. 无人值守执行者角色提示（仅自动化任务）
3. **`.crmeb/AGENTS.md` 兜底规范**——随模块发布、**不可在后台删除或停用**，恒定最先注入，同时兼容 AGENTS.md 开放标准供 Codex、Cursor 等外部 AI 编码工具读取
4. 已加载的 Skill 全文
5. Skill 目录（未显式选择时）
6. 输出纪律与推荐追问约定
7. 可用工具清单与工具调用格式约定

### 6.4 内置 Skill（5 个）

内置 Skill 随模块发布，可在后台编辑、启停，但停用或删除会削弱对应场景的回答质量。

| key | 名称 | 作用 |
|---|---|---|
| `crmeb-assistant` | CRMEB 业务助手 | 用已授权工具查询商品、订单、真实售后、优惠券与用户数据，明确各统计口径的边界（如商品行支付金额不等于订单实付、优惠券发行配置不等于用户持券量） |
| `systemsetting` | 系统设置 | 查询与修改系统配置：按配置类型（radio/checkbox/select/text/upload）构造值，经确认卡授权后复核；凭证类只说明「已配置/未配置」 |
| `doc` | 功能说明 | 功能归属、配置入口与支持状态判断：以当前已安装模块、菜单及配置为准；附 CRMEB 标准版 v6 历史速查 `功能索引.md` 供历史文档检索（**不代表当前站点功能清单**） |
| `emailformat` | 邮件格式 | 调用发邮件工具时生成美观专业的 HTML 正文：说明系统邮件按 HTML 渲染、不转换 Markdown 的事实，并给出邮件客户端兼容的排版规范与模板 |
| `crontab` | 系统定时任务 | 区分「系统定时任务」（未支付自动取消、自动收货等）与「AI 自动化任务」两类概念，查证后台入口与代码可登记的任务及排查边界 |

`doc` 的附属资料为 `功能索引.md`，`systemsetting` 的 `EXAMPLES.md` 为维护者回归用例。

### 6.5 辅助能力

Skill 编辑抽屉内置「AI 生成/优化正文」与「AI 生成描述」，走非流式 `ai/chat` 接口，生成结果可预览后再插入或替换编辑区。

## 7. AI 自动化任务

让 AI 按计划**无人值守**执行指令，并把结果推送给配置的收件人（如「每天把昨日经营数据发我邮箱」）。

### 7.1 任务定义

| 字段 | 含义 |
|---|---|
| `name` | 任务名称（≤100 字） |
| `instruction` | 任务指令：无人值守时下发给模型的完整说明（纯文本），需写明数据口径、时间范围与交付动作 |
| `model_id` | 模型（0 = 系统默认） |
| `server_ids` | 连接器 ID JSON，**空 = 运行时自动挂载全部可用连接器** |
| `skill_keys` | 挂载的 Skill 标识 JSON（显式选择语义 = 全文注入） |
| `autonomy` | 自主级别：1 只读 / 2 需审批 / 3 全自动 |
| `schedule_type` + `schedule_value` | 调度类型与取值 |
| `max_steps` | 单次运行最大工具轮数（保存时夹取 3–30，默认 10） |
| `timeout` | 单次运行软超时秒数（夹取 60–1800，默认 300） |
| `notify_channels` | 完成通知渠道 JSON：`is_email` / `is_ent_wechat` |
| `notify_email` | 收件邮箱，勾选邮件渠道时必填且校验合法性 |
| `status` | 0 停用 / 1 启用 |
| `next_run_time` | 下次运行时间；为 0 的任务不会被扫描 |

任务**没有「类型」字段**，能力差异只体现在指令内容、挂载的连接器与 Skill 上；代码中也没有报告模板机制。

### 7.2 调度

**两种触发方式**：调度触发（`trigger_type=1`）与手动立即执行（`trigger_type=2`）。**没有事件触发**。

调度**不是 cron 表达式**，而是结构化的 `schedule_type` + `schedule_value`：

| type | 含义 | 取值格式 | 校验 |
|---|---|---|---|
| 1 | 每隔 N 分钟 | 分钟数 | 1–43200（最长 30 天） |
| 2 | 每天 | `HH:MM` | `^([01]?\d\|2[0-3]):([0-5]\d)$` |
| 3 | 每周 | `周几\|HH:MM` | 周几 1–7（1 = 周一） |
| 4 | 每月 | `几日\|HH:MM` | 几日 **1–28** |
| 5 | 每年 | `月\|日\|HH:MM` | 月 1–12、日 1–28 |

下次运行时间由 `computeNextRunTime` 推导；已过或恰为当前秒时顺延；无法解析则置 0（不参与扫描）。

**调度器**：以系统定时任务形式注册，任务标识 `aiAgentTask`，安装时写入 `eb_system_crontab`（`0 */1 * * * *`，即**每分钟**执行一次），由 timer 进程唤起。每分钟扫描 `status=1 AND next_run_time>0 AND next_run_time<=now` 的任务（单轮上限 50 条），**先推进 `next_run_time` 并记 `last_run_time`，再创建运行记录并入队**。单条任务异常只记日志、不中断本轮。调度器还会回收「孤儿运行」（超过任务超时 + 120 秒仍未结束的 `running` 记录标记为失败）。

**入队**：`AgentTaskJob::dispatch('doJob', [$runId])`。注意当全局队列未开启（`queue_open != 1`）或缓存驱动不是 Redis 时，框架会退化为**在发起进程内同步执行**——此时手动「立即执行」的 HTTP 请求会被整个模型工具循环阻塞。

### 7.3 执行流程

1. 读取运行记录，**状态即锁**：非 `running` 直接返回。
2. 数据库 CAS 领取（`claimRunning` 写入 `started_at`），并发重复投递只有一个赢家。
3. 为本次运行**创建独立审计会话**（标题形如「【自动任务】任务名 月-日 时:分」），全过程消息可在运行详情中回放。
4. 组装运行时选项：无人值守开关、自主级别、轮数上限、软超时 deadline。
5. 组装提示词：任务指令 + 追加段落声明收件邮箱（防止模型编造收件人）。
6. 调用对话引擎执行（工具循环最多 `max_steps` 轮，超 deadline 后转入强制最终答复）。
7. 聚合结果：摘要正文、token 用量、工具调用次数；超过软超时则状态记为 `timeout`（**结论仍保留有效**）。
8. **交付校验与兜底投递**：若工具轨迹中没有成功的交付类工具调用（`builtin.crmeb_email_send` / `builtin.crmeb_qywechat_send`），则由执行器**按通知渠道兜底投递**；兜底也失败时写入错误说明。
9. 失败/超时时按通知配置**额外发一次提醒**（独立 try-catch，不影响终态）。
10. 落库终态：状态、摘要（截断 60000 字）、错误（截断 1000 字）、工具调用数、token 用量、结束时间。

### 7.4 自主级别与无人值守的安全处理

无人值守没有可交互的用户，因此**所有需确认的写工具都不会落确认卡**。三条机制互相配合：

| 自主级别 | 工具可见性 | 说明 |
|---|---|---|
| 1 只读（**默认**） | 剔除全部需确认写工具，**仅保留交付类**（邮件 / 企业微信推送） | 交付类工具的收件目标由服务端配置与任务描述约束 |
| 2 需审批 | **运行时降级为只读** | 审批链路尚未实现（见[第 14 节](#14-已知限制与待实现项)） |
| 3 全自动 | 保留全部工具，可以真实修改业务数据 | 请谨慎授权 |

此外：

- 无人值守模式**不注册** `crmeb_ask_user`、`crmeb_task_create`、`crmeb_task_list`，提示词层同时声明提问工具不可用，模型须按合理默认值继续执行——既因为无人应答，也防止任务自我修改调度。
- 提示词要求模型**优先调用工具完成实际动作**，涉及数据类结果必须先查询再交付，涉及送达必须调用交付工具，禁止只输出方案说明或用占位描述充当结果。

### 7.5 运行记录

运行记录状态：`running` 执行中 / `success` 成功 / `failed` 失败 / `timeout` 超时。记录含触发方式（调度 / 手动）、开始与结束时间、耗时（出参动态计算）、工具调用次数、token 用量、结果摘要或错误原因、关联审计会话。**运行详情可回放该次运行的全过程会话消息**（含工具调用与结果），并提供「在对话中查看」入口。

**失败不自动重试**：队列任务自捕获异常并返回成功，避免 LLM 多轮调用产生重复 Token 消耗；单次失败不影响后续周期（`next_run_time` 已在入队前推进）。代码中**没有**「连续失败自动停用任务」的逻辑。

### 7.6 从对话创建任务

用户在对话中表达周期性诉求（如「每天早上把昨日经营数据发我邮箱」）时，模型调用 `crmeb_task_create` 落成任务定义，可经确认卡授权后创建。该路径创建的任务固定为**只读自主级别、创建即启用**，连接器留空（执行时自动挂载全部可用）。邮件渠道必填收件邮箱，未知时模型先用 `crmeb_ask_user` 询问。`crmeb_task_list` 用于回答「我有哪些定时任务」。

## 8. 数据表

5 张表全部由模块 Installer 以 `CREATE TABLE IF NOT EXISTS` 创建，属模块自有表（不在核心安装 SQL 中，卸载时 DROP）。

| 表 | 说明 | 关键索引 |
|---|---|---|
| `eb_ai_agent_conversation` | 会话（`admin_id`、`title`、`is_pin` 置顶） | `idx_agent_admin(agent_id, admin_id)`、`idx_update_time` |
| `eb_ai_agent_message` | 消息（`role` user/assistant/tool/confirm、`content`、`tool_name`、`tool_payload`） | `idx_conversation(conversation_id, id)` |
| `eb_ai_agent_task` | 自动化任务（见 [7.1 节](#71-任务定义)字段清单） | `idx_status_next(status, next_run_time)`、`idx_admin` |
| `eb_ai_agent_task_run` | 任务运行记录（状态、触发方式、摘要、错误、工具次数、用量、起止时间） | `idx_task(task_id, id)`、`idx_status` |
| `eb_ai_mcp_server` | MCP Server 注册表（`server_key` 唯一、`url`、`api_key`、`headers`、`timeout`、`tools` 同步缓存） | `uk_server_key`、`idx_status` |

另对**核心表** `eb_ai_model` 增量补列 `full_url`（不属于模块自有表，卸载不删除该列）。

`eb_ai_agent_task` 的 `schedule_type` 列注释只写到「4=每月」，而代码与前端均已支持 type=5（每年），以代码为准。

## 9. 接口一览

除特别说明外，均挂在 `/adminapi` 前缀下，使用管理端四件套中间件（跨域、管理员令牌、角色权限、操作日志）。

### 9.1 对话与会话（`aiagent/*`）

| 方法 | 路径 | 说明 |
|---|---|---|
| GET | `aiagent/options` | 对话选项：可用模型、内置 MCP 可用性、连接器清单、启用中的 Skill |
| GET | `aiagent/conversations` | 当前管理员的会话列表 |
| GET | `aiagent/messages/:conversation_id` | 会话消息记录 |
| DELETE | `aiagent/conversation/:id` | 删除会话 |
| PUT | `aiagent/conversation/:id` | 修改会话（标题 / 置顶） |
| POST | `aiagent/chat` | 发送对话消息（非流式） |
| POST | `aiagent/chat_stream` | 流式对话（SSE） |
| POST | `aiagent/confirm_stream` | 交互卡片处理（确认/拒绝后续跑，SSE） |

### 9.2 模型管理（核心路由 `ai/model/*`）

`list`、`options`、`protocols`、`detail/:id`、`save`、`update/:id`、`delete/:id`、`set_status/:id`、`set_default/:id`、`sort`、`test`。

### 9.3 MCP Server（`aiagent/mcp_server*`）

`mcp_servers`（列表）、`mcp_server`（新增）、`mcp_server/:id`（详情/编辑/删除）、`mcp_server/:id/status`（启停）、`mcp_server/:id/sync`（同步工具）、`mcp_server/:id/test`（测试连接）。新增与编辑也可通过 `config` 字段提交 mcpServers JSON。

### 9.4 Skill（`aiagent/skill*`）

`skills`（列表）、`skill`（新增）、`skill/:key`（详情/编辑/删除）、`skill/:key/status`（启停）、`skill/:key/attachments`（附属资料列表）、`skill/:key/attachment`（附属资料详情/保存/删除，文件名经 `file` 查询参数传递，避免路由变量编码问题）。Skill key 允许连字符，路由已显式声明变量规则。

### 9.5 自动化任务（`aiagent/task*`）

| 方法 | 路径 | 说明 |
|---|---|---|
| GET | `aiagent/tasks` | 当前管理员的任务列表（分页） |
| POST / PUT | `aiagent/task[/:id]` | 创建 / 编辑 |
| GET | `aiagent/task/:id` | 详情 |
| DELETE | `aiagent/task/:id` | 删除（事务内连同运行历史一起删除） |
| PUT | `aiagent/task/:id/status` | 启停（启用时重推 `next_run_time`） |
| POST | `aiagent/task/:id/run` | 立即执行（任务必须为启用状态） |
| GET | `aiagent/task/:id/runs` | 该任务的运行列表 |
| GET | `aiagent/task_runs` | 全量运行记录（当前管理员的全部任务） |
| GET | `aiagent/task_run/:id` | 运行详情（含审计会话消息回放） |

## 10. 菜单与权限

### 10.1 菜单结构

安装时写入 `eb_system_menus`，共 **47 条**：

- **1 条一级菜单**：名称「AI」，路径 `/aiagent/index`，权限标识 `aiagent-manage`，图标 `cpu`，排序 5。点击直接进入对话页。
- **5 条分组容器**（对话管理 / 模型管理 / MCP Server 管理 / Skill 管理 / 自动化任务）：`is_show_path=0`，用作菜单管理中的权限归类，**不进侧边栏与授权树**。
- **41 条接口权限节点**（`auth_type=2`）：覆盖对话、模型、MCP、Skill、自动化任务的接口，其中模型管理节点的路径指向核心 `ai/model/*` 接口。

菜单形态的设计意图：**一级菜单「AI」点击直接进入对话页**，模型/MCP/Skill/任务管理在对话页内以遮盖层打开，因此不为它们保留侧边栏子菜单。

菜单写入是**幂等的**：按 `menu_key` 稳定标识匹配，命中则逐字段比对、有差异才更新，未命中才插入。因此升级改动路由、父级或排序**无需卸载重装**。

### 10.2 权限节点范围

权限节点只写入 `eb_system_menus`。角色鉴权读取 `auth_type=2` 且 `api_url` 非空的节点（`api_url` 中的 `<id>` / `<key>` 占位符与运行时路由规则对齐）。**未登记为 `auth_type=2` 的接口直接放行、不受角色管控**——当前属于此类的有：`aiagent/chat_stream`、`aiagent/confirm_stream`、`aiagent/task_runs`、`aiagent/mcp_server/:id/test`、`ai/model/options`、`ai/model/sort`。

### 10.3 菜单迁移

安装与「禁用→启用」时都会先执行菜单迁移，再写入标准菜单，用于消化历史版本差异：

| 迁移 | 效果 |
|---|---|
| 旧根菜单改名 | 「AI Agent」根菜单统一为「AI」，避免重新插入产生两条顶级菜单 |
| 分组形态修正 | 历史 `/aiagent` 分组菜单改为 `/aiagent/index`（旧形态点击后前端无路由会白屏） |
| 旧名修正 | 「智能体管理」改为「AI 对话」 |
| 旧接口修正 | 去掉会话列表接口中的 `<agent_id>` 占位符 |
| 删除独立页面入口 | 物理删除 `/aiagent/model`、`/aiagent/mcp`、`/aiagent/skill` 三个页面菜单（已改为遮盖层） |
| `auth_type` 修正 | 历史按 `auth_type=3` 写入的接口节点修正为 `2`——核心鉴权只识别 `auth_type=2`，否则这些接口从未纳入角色管控 |

## 11. 管理端页面

全部资源位于 `frontend/admin/src/pages/plugin/aiagent`。

| 文件 | 作用 |
|---|---|
| `pages/index.vue` | **唯一的真实页面**：AI 对话页（左会话栏 + 右消息区/输入区，管理页以遮盖层打开） |
| `components/ConversationPanel.vue` | 会话列表、置顶/重命名/删除、四个管理入口 |
| `components/ChatInput.vue` | 输入区：模型切换 popover、快捷场景标签、发送/停止按钮 |
| `components/ModelManage.vue` | 模型登记（列表、筛选、表单、保存前连通性测试） |
| `components/McpManage.vue` | MCP 服务卡片、JSON 配置弹窗、测试连接/同步/启停 |
| `components/SkillManage.vue` | Skill 表格与编辑抽屉、附属资料管理、AI 写作 |
| `components/TaskManage.vue` | 任务卡片、表单、运行历史与运行详情回放 |
| `components/ConfirmCard.vue` | 执行确认卡（含 HTML 内容沙箱预览） |
| `components/AskCard.vue` | 问询卡（单选/多选/文本 + 自定义输入） |
| `components/AiWriteModal.vue` | Skill 正文/描述的 AI 写作弹窗（走 `ai/chat_stream`） |
| `composables/useChatStream.js` | SSE 事件处理与回复占位管理 |
| `api.js` | 全部 AI 接口封装（含 SSE 手写读取与 401/403 错误映射） |
| `routes.js` / `ext.json` / `index.umd.js` | 静态路由、插件元数据与 UMD 入口 |

对话页交互要点：消息区渲染 Markdown（`html: false` 防 XSS）；「深度思考」折叠块展示思维链、Skill 标签与「调用 xxx」工具标签；右侧操作区支持复制、重新生成（仅最后一条回复）；回答完成后展示推荐追问胶囊；流式中发送按钮变为停止按钮，终止后保留已输出内容。历史消息回显时**过滤 `tool` 消息与带工具名的中间态记录**，只保留面向用户的问答与卡片。

## 12. 安装、启用与卸载

- 安装、卸载、启用、禁用走标准模块管理流程（后台插件管理）。模块是**默认安装**模块，但**非必需模块**，允许卸载与禁用。
- `runMigrate()` 全部幂等：建表用 `CREATE TABLE IF NOT EXISTS`，补列前先查 `information_schema`。卸载的 `rollbackMigrate()` 以 `IF EXISTS` 逆序 DROP 5 张自有表。
- 安装与启用时都会执行 `seed()`：确保内置 MCP 记录与配置/定时任务存在（**只补缺、不覆盖已有记录**）。
- 安装写入的附加数据：系统配置分组「AI 自动化任务」、配置项 `aiagent_qywechat_webhook`（企业微信机器人 Webhook，自动化任务推送用）、系统定时任务 `aiAgentTask`。模块**不写驱动字典**（类型是 `service` 而非 `driver`），也不写 `model_checkbox` 显示配置。
- **禁用**：隐藏菜单，保留表与数据；重新启用会恢复菜单显示并补种缺失数据。禁用后路由、事件与处理器停止加载。
- **卸载**：清理 5 张自有表数据与菜单、删除定时任务与配置分组、写卸载日志、DROP 5 张表。**不删除** `eb_ai_model` 及其 `full_url` 列，也不影响核心表数据。
- 模块没有版本化升级脚本目录，升级通过幂等的 `runMigrate()` + 菜单迁移 + `seed()` 完成。

## 13. 依赖与前置条件

| 依赖 | 性质 | 不满足时的表现 |
|---|---|---|
| `service_mcp` 模块已安装启用 | 软依赖（内置工具） | 系统内置 MCP 不可用，仅第三方远程 MCP 可用；同步/测试会给出明确提示 |
| `mcp_enabled = 1` | 系统配置 | 同上 |
| `mcp_appkey` 非空 | 系统配置 | 同上 |
| 一号通凭证 `yihaotong_appid` / `yihaotong_appsecret` | 系统配置 | 「一号通AI」模型不可用，需改用登记模型或在后台完成一号通登录/设置 |
| 队列与定时器进程 | 运行时 | 自动化任务不会被调度执行（`queue_open` 未开启或缓存非 Redis 时退化为同步执行） |
| Skill 目录写权限 | 文件系统 | Skill 无法保存/删除 |

历史站点注意：早期安装的 MCP 模块需要**禁用再启用一次**以补种系统账号与 `mcp_appkey` 镜像；随后需对内置 MCP 重新「同步工具」，新增的业务工具才会进入 AI Agent 的工具缓存。内置服务读取工具时若发现缓存为空会**自动补同步一次**（失败后 10 分钟内不重试），新装站点开箱即用。

**Docker 环境注意**：容器内 `localhost` 指向容器自身，MCP Server 地址应使用容器网络直连（如 `http://crmeb_nginx/...`）或宿主机映射地址（如 `http://host.docker.internal:8011/...`）。

## 14. 已知限制与待实现项

以下均为代码可核实的事实，供使用与验收时参考：

1. **自主级别「需审批」（2）未实现审批链路**，运行时按只读执行。前端该项已置灰标注「即将上线」。
2. 「全自动任务仅超管可创建」只存在于代码注释，**没有对应的角色或超管校验**。
3. 自动化任务**没有同一任务的运行互斥**：若单次运行超过调度间隔（如分钟级任务 + 长耗时指令），扫描会继续创建新的运行记录。
4. `ai_agent_task_run.confirm_message_id`（注释为「对话创建任务场景」）**没有任何写入非 0 值的代码**。
5. 有注释称交付类工具的收件目标受「工具端许可白名单硬约束」，但 `service_mcp` 侧**未找到对应白名单实现**（实际约束是任务描述中的收件邮箱）。
6. 全局队列未开启或缓存非 Redis 时，「立即执行」接口会**同步阻塞**执行完整模型工具循环，但响应文案仍为「已加入执行队列」。
7. 前端 `aiTaskRunListApi`（对应 `aiagent/task_runs` 全量运行记录）**已定义但无调用点**，界面上只能按任务查看运行历史。
8. 模型管理表单**没有独立的连通性测试按钮**，测试只在保存流程中隐式执行；表单中也没有可编辑的「协议」输入项（新增时固定为 `openai_compatible`）。
9. 任务运行详情页的「在对话中查看」跳转使用哈希形式 URL，而管理端路由模式为 history，**未找到消费该查询参数的代码**，实际效果需实测确认。
10. 上述 [10.2 节](#102-权限节点范围)列出的 6 个接口未登记权限节点，任何管理员角色均可调用。

## 15. 定向回归

模块与工具层的定向回归为**独立 PHP 脚本**（非 PHPUnit，仓库无全局 PHPUnit 配置），从 `backend/` 目录执行，不依赖数据库：

```bash
php tests/aiagent/AgentChatFlowTest.php
php tests/aiagent/BuiltinSkillInstructionsTest.php
php tests/mcp/McpContractTest.php
```

覆盖点：历史上下文选取与工具结果裁剪、Skill 按需加载进入下一轮规则、重复加载幂等、中文截断的 UTF-8 安全；5 个内置 Skill 的元信息完整性、内置规则中 MCP 工具名的 `builtin.` 前缀与工具存在性校验、历史功能与业务统计口径回归、v6 附件加载与 `EXAMPLES.md` 不外泄。

两个 aiagent 脚本**成功时无输出且退出码为 0**，失败时抛出异常并打印原因——判断是否通过请看退出码。工具契约测试会打印通过信息。

## 16. 事实来源

| 信息 | 代码位置 |
|---|---|
| 模块元数据 | `backend/modules/aiagent/plugin.json` |
| 运行时注册 | `backend/modules/aiagent/App.php`（注册定时任务）、`event.php`（当前无监听者） |
| 表结构与预置数据 | `backend/modules/aiagent/Installer.php` |
| 对话主流程 | `backend/modules/aiagent/service/AgentChatService.php` |
| 工具发现与调用 | `backend/modules/aiagent/service/McpToolService.php` |
| MCP Server 管理 | `backend/modules/aiagent/service/McpServerServices.php` |
| Skill 文件读写 | `backend/modules/aiagent/service/SkillRepository.php` |
| 任务与运行 | `backend/modules/aiagent/service/AgentTaskServices.php`、`AgentTaskRunnerService.php`、`jobs/AgentTaskJob.php`、`crontab/AiAgentTaskDispatcherTask.php` |
| 远程 MCP 客户端 | `backend/modules/aiagent/mcp/RemoteMcpClient.php` |
| 接口路由 | `backend/modules/aiagent/route/admin.php` |
| 内置兜底规范与 Skill | `backend/modules/aiagent/.crmeb/AGENTS.md`、`.crmeb/skills/**` |
| 业务工具与 MCP 端点 | `backend/modules/service/mcp/`（工具在 `tools/`，端点在 `controller/McpController.php`） |
| 模型协议与登记 | `backend/crmeb/services/ai/`、`backend/app/services/ai/`、`backend/app/adminapi/route/ai.php` |
| 管理端页面 | `frontend/admin/src/pages/plugin/aiagent/` |

## 17. 文档维护约定

出现以下变更时，请在同一提交中更新本文：

| 代码变更 | 需更新内容 |
|---|---|
| 新增/删除内置 Skill 或兜底规则 | [6.4 节](#64-内置-skill5-个)与 [15 节](#15-定向回归) |
| 新增业务工具或写工具 | [5.3 节](#53-内置业务工具清单33-个)（同时核对 `service_mcp` 侧工具表） |
| 新增/修改对话、任务、MCP、Skill 接口 | [第 9 节](#9-接口一览)与 [10.1 节](#101-菜单结构)的权限节点 |
| 数据表、字段或索引变化 | [第 8 节](#8-数据表)（同时更新[数据库设计](数据库设计.md)） |
| 调度类型、自主级别或安全策略变化 | [第 7 节](#7-ai-自动化任务)与 [14 节](#14-已知限制与待实现项) |
| 模块依赖或前置配置变化 | [第 13 节](#13-依赖与前置条件)（同时更新[模块说明](模块与驱动.md)） |

维护原则：本文描述**当前实现**，与 [模块与驱动](模块与驱动.md)、[项目功能](项目功能.md) 的分工是「本文为 AI Agent 的功能与接口详述，那两处保留模块清单级别的一句话定位并链接本文」，不重复维护同一份清单。已知限制如实记录，不因发布需要隐藏。
