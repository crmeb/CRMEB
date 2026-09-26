# MCP 接口

> 核对日期：2026-09-21。本文描述独立插件 `service_mcp` 提供的对外 MCP 服务；它不属于开放平台 `outapi`，不使用开放平台的 appid、appsecret 或 token。

## 1. 服务概述

MCP 服务通过 JSON-RPC 2.0 over HTTP 向 AI 客户端开放 CRMEB 查询、统计、配置和消息发送工具。

| 项目 | 当前实现 |
|---|---|
| 插件 key | `service_mcp` |
| 插件目录 | `backend/modules/service/mcp/` |
| 请求地址 | `POST /api/mcp` |
| 图片直传端点 | `POST /api/mcp/upload`（multipart 表单，票据鉴权；跨机客户端回传图片字节用，见第 7 节） |
| 传输方式 | HTTP 单端点、JSON 请求/响应 |
| 访问认证 | `X-Mcp-Key` 或 `Authorization: Bearer`（账号体系是唯一认证权威） |
| 服务开关 | `mcp_enabled`（安装默认开启，在「系统配置 → MCP服务」中切换） |
| 访问密钥 | MCP 账号密钥（`eb_service_mcp_account`；所有账号（含系统账号）均按勾选工具授权，系统账号安装时初始授予全部工具） |
| 账号表 | `eb_service_mcp_account`（备注 + 密钥 + 类型 + 状态 + 工具白名单） |

插件必须已安装并启用、总开关开启且账号密钥有效，客户端才能调用。该插件默认列入安装清单，但“默认安装”不代表当前站点已经安装、启用或对公网开放。

> 系统配置 `mcp_appkey` 已不再参与认证：它降级为系统账号密钥的自动镜像（隐藏分组），仅供 AI Agent 模块内置工具源读取（modules 层禁止互引，配置键是唯一合法通道），在「MCP配置」页重置系统账号 Key 时自动同步，勿手动修改。

## 2. 后台账号与密钥管理

管理员进入「设置 → MCP服务 → MCP配置」，页面为 MCP 账号列表（账号体系是唯一认证权威，不存在独立于账号之外的全局密钥）。服务总开关 `mcp_enabled` 安装默认开启，如需整体关闭请前往「系统配置 → MCP服务」切换；MCP 配置页顶部「服务状态」卡片实时反映开关状态：

1. 点击「新增账号」，填写备注并勾选该账号可用的工具，系统自动生成账号密钥。
2. 行「接入信息」抽屉展示密钥与按该密钥实时生成的 `mcpServers` JSON，复制到客户端即可接入。
3. 「编辑权限」调整备注与工具勾选；「重置Key」作废旧密钥并生成新密钥；「删除」软删除账号，密钥立即失效；「状态」开关可临时停用账号（停用后密钥立即失效，重新启用即恢复）。
4. 所有账号只能看到并调用勾选的工具：`tools/list` 只返回勾选的工具，调用未勾选的工具返回 `当前账号无权访问工具: ...`。
5. **系统账号**（默认保留一条，类型标记「系统」）：供 AI Agent 模块内置工具源调用，与其他账号一致受工具白名单约束——安装时初始授予全部工具，管理员可「编辑权限」收紧（收紧后需在 AI Agent 的 MCP 管理页重新「同步工具」）；不支持删除，可停用——停用后密钥立即失效，AI Agent 内置工具源随之不可用。其密钥自动镜像到系统配置 `mcp_appkey`，「重置Key」后镜像同步刷新、AI Agent 自动生效；如需整体关闭所有 MCP 接入，请前往「系统配置 → MCP服务」切换服务总开关。

为不同客户端创建独立账号并只勾选需要的工具，按最小授权管理。重置、停用或删除后旧密钥立即失效，相关客户端都必须同步更新。不要把真实密钥写入仓库、文档、日志或公开工单。

「MCP工具」页面展示当前实际注册的工具、参数 schema 和执行方式。由于已启用插件可以动态追加或覆盖工具，运行时列表应以该页面或 `tools/list` 响应为准；新增工具不会自动进入已有账号的白名单（含系统账号），需在「编辑权限」中重新勾选。

## 3. 客户端配置

通用远程 HTTP 配置示例（管理后台账号「接入信息」抽屉按账号密钥实时生成同格式代码，`name` 自动取站点名）：

```json
{
  "mcpServers": {
    "crmeb": {
      "name": "CRMEB 商城",
      "url": "https://example.com/api/mcp",
      "headers": {
        "X-Mcp-Key": "替换为访问密钥"
      },
      "timeout": 15,
      "description": "CRMEB 商城管理工具集：查询/管理商城数据（商品、订单、售后、配置等，以账号已授权工具为准）。",
      "disabled": false
    }
  }
}
```

服务端也接受标准 Bearer 认证：

```http
Authorization: Bearer <账号密钥>
```

客户端配置项名称因软件而异；无论使用哪种客户端，最终都应向站点的 `/api/mcp` 发出携带认证头的 HTTP POST 请求。

## 4. 协议调用

所有有结果的请求都应包含唯一 `id`。不带 `id` 的 notification 当前返回 HTTP 204，不执行工具调用。

### 4.1 初始化

```bash
curl 'https://example.com/api/mcp' \
  -H 'Content-Type: application/json' \
  -H 'X-Mcp-Key: YOUR_MCP_APPKEY' \
  --data '{
    "jsonrpc": "2.0",
    "id": 1,
    "method": "initialize",
    "params": {"protocolVersion": "2025-06-18"}
  }'
```

服务端支持 `2024-11-05`、`2025-03-26`、`2025-06-18`。请求版本在支持列表中时原样返回；其他版本当前回退到 `2024-11-05`。初始化响应声明 `tools` 能力，服务名为 `crmeb-mcp-server`。

### 4.2 获取工具列表

```bash
curl 'https://example.com/api/mcp' \
  -H 'Content-Type: application/json' \
  -H 'X-Mcp-Key: YOUR_MCP_APPKEY' \
  --data '{"jsonrpc":"2.0","id":2,"method":"tools/list"}'
```

返回的每个工具包含：

- `name`：工具名称；
- `description`：用途与业务口径；
- `inputSchema`：参数 JSON Schema；
- `interaction`：可选，值为 `execute` 时表示写操作，客户端应在执行前取得用户确认；
- `label`：可选，简短中文名称，供管理后台工具列表与账号权限勾选展示，未实现 `label()` 方法的工具不返回该字段；
- `category`：可选，业务分类名（内置工具为 用户/商品/订单/售后退款/营销/系统设置/消息通知/开发支持 之一），管理后台按该字段分组展示工具列表与权限勾选项；插件工具可返回自定义分类名（按出现顺序追加为新分组），未实现 `category()` 方法的工具不返回该字段，后台归入「其他」分组。

### 4.3 调用只读工具

```bash
curl 'https://example.com/api/mcp' \
  -H 'Content-Type: application/json' \
  -H 'X-Mcp-Key: YOUR_MCP_APPKEY' \
  --data '{
    "jsonrpc": "2.0",
    "id": 3,
    "method": "tools/call",
    "params": {
      "name": "crmeb_product_list",
      "arguments": {"page": 1, "limit": 10}
    }
  }'
```

工具执行结果放在 `result.content[0].text`，其值是 JSON 字符串。客户端需要先读取 MCP content，再按业务需要解析其中的 JSON。

### 4.4 调用写工具

```bash
curl 'https://example.com/api/mcp' \
  -H 'Content-Type: application/json' \
  -H 'X-Mcp-Key: YOUR_MCP_APPKEY' \
  --data '{
    "jsonrpc": "2.0",
    "id": 4,
    "method": "tools/call",
    "params": {
      "name": "crmeb_config_update",
      "arguments": {}
    }
  }'
```

示例中的空参数不会构成有效业务调用；实际参数必须以 `tools/list` 返回的 `inputSchema` 为准。MCP 入口授权不替代工具内部的参数校验、业务权限、幂等和审计。

## 5. 内置工具

当前自动发现 57 个内置工具，其中 54 个业务工具、3 个开发知识工具。标记“写”的工具还会返回 `interaction: "execute"`。业务工具均声明 `category` 分类（用户、商品、订单、售后退款、营销、系统设置、消息通知、开发支持），后台「MCP 工具」列表与账号权限勾选按分类分组展示。

| 业务域 | 工具 |
|---|---|
| 商品与分类 | `crmeb_product_list`、`crmeb_product_detail`、`crmeb_product_statistics`、`crmeb_product_ranking`、`crmeb_inventory_alert`、`crmeb_category_list`、`crmeb_category_detail` |
| 订单与物流 | `crmeb_order_list`、`crmeb_order_detail`、`crmeb_order_statistics`、`crmeb_order_logistics`、`crmeb_order_express_list` |
| 售后与退款 | `crmeb_after_sale_list`、`crmeb_after_sale_statistics`、`crmeb_refund_list`、`crmeb_refund_detail` |
| 优惠券 | `crmeb_coupon_list`、`crmeb_coupon_create`（写）、`crmeb_coupon_claim_list`、`crmeb_coupon_claim_statistics`、`crmeb_coupon_holding_statistics`、`crmeb_coupon_usage_statistics` |
| 用户 | `crmeb_user_list`、`crmeb_user_detail`、`crmeb_user_statistics` |
| 系统配置 | `crmeb_config_list`、`crmeb_config_tab_list`、`crmeb_config_update`（写）、`crmeb_upload_image`（写）、`crmeb_upload_ticket` |
| 菜单与模块 | `crmeb_menu_list`、`crmeb_module_list` |
| 第三方驱动 | `crmeb_driver_list`、`crmeb_driver_config_update`（写） |
| 消息发送 | `crmeb_email_send`（写）、`crmeb_qywechat_send`（写） |
| 开发知识 | `crmeb_dev_docs_search`、`crmeb_dev_route_search`、`crmeb_dev_table_schema` |

工具参数、返回字段和统计口径以运行时 `tools/list` 的描述及对应工具实现为准。业务工具会对密码、token、密钥、Webhook、手机号等敏感字段进行脱敏，但调用方仍应按敏感业务数据管理返回内容。

## 6. 插件扩展工具

`service_mcp` 先自动发现自身 `tools/*Tool.php`，再触发字符串事件 `McpToolsRegister`。其他已启用插件可以监听该事件，注册实现 `crmeb\interfaces\McpToolInterface` 的工具；同名工具以后注册者为准。

官方插件当前贡献的工具：

| 插件 | 工具 |
|---|---|
| 实物商品（product_normal） | `crmeb_product_normal_read`（读）、`crmeb_product_normal_create`（写，新增单规格实物商品）、`crmeb_product_normal_update`（写，按字段合并更新，仅单规格可改价） |

`crmeb_product_normal_read/create/update` 组合支持“一张照片建品/改品”流程：调用方 AI 客户端自行完成图片识别与文案生成，读取商品现状后按字段写回，服务端不内置任何 AI 调用。AI 生成的图片需先入库为站点资产（写入系统当前配置的存储方式并登记后台素材库）：跨机部署（AI 客户端与商城服务器不同机）走「票据直传」链路——先调 `crmeb_upload_ticket` 获取票据与本机可执行的 curl 命令，客户端直传 `POST /api/mcp/upload` 拿到暂存引用 `ref`，再把 `ref` 作为 `source` 调 `crmeb_upload_image` 完成正式入库（详见第 7 节）。`crmeb_upload_image` 的来源按上下文成本从低到高：直传暂存引用 → 生图接口 http(s) URL（服务端下载落站，上限 10MB）→ 站点已有图片相对路径（校验存在后原样返回并补全元数据）→ 本地白名单路径（本机 Agent 先把生图文件放入 `runtime/upload`，可用系统配置 `mcp_upload_local_dirs` 追加白名单，仅 AI 与服务器同机部署时可用）→ base64 data URI 内联（仅作无法发起本机 HTTP 上传时的最后应急，以纯文本经过对话上下文，解码后超过 512KB 拒绝并提示改用直传）。仅支持 jpg/jpeg/png/gif/webp；同内容按 md5 去重；单次最多 20 张、单密钥每分钟最多 60 张；批量中个别失败不影响其他图片，结果按 uploaded/failed 逐条返回，成功项含 url/att_id/宽高/大小/md5/dedupe_hit。商品工具的 `slider_image`（数组，整组写入）与 `description` 内 `<img>` 只接受换取到的站点图片地址。

插件停用或卸载后，其事件监听器不再加载，对应工具会自动从 `tools/list` 消失。开发流程和约束见 [插件扩展 MCP 工具](../../../.crmeb/skills/crmeb-plugin-dev/references/mcp-tools.md)。

## 7. 图片直传端点

`POST /api/mcp/upload` 是跨机场景的图片字节带外入口：AI 客户端（WorkBuddy 等）在自己机器上生成图片后，由客户端本机直接发出 multipart 请求把字节交给服务器暂存——不占用对话上下文（不塞 base64），也不要求服务器能读到客户端磁盘（不走本地路径），是「生成图片 → 写入商城」零人工闭环的推荐链路。

三步时序（全部由 AI 客户端自主完成）：

1. 调 MCP 工具 `crmeb_upload_ticket`（无参数）获取票据：返回 `ticket`（10 分钟有效）、`upload_url`，以及 `command` 字段——可直接在本机执行的一条 curl 命令；
2. 客户端本机执行上传命令（或同构的 multipart 请求），把生成好的图片传上去：

   ```bash
   curl -s -F "ticket=<票据>" -F "files[]=@/本机图片路径.png" https://example.com/api/mcp/upload
   ```

   响应 `data.received[].ref` 为暂存引用（形如 `runtime/upload/mcp_recv/up_xxx.png`）；单次最多 20 张、单张不超过 10MB，仅支持 jpg/jpeg/png/gif/webp；
3. 把 `ref` 作为 `source` 调 `crmeb_upload_image` 完成正式入库（去重、素材库登记、限流、审计均收敛在该工具内），再用返回的站点地址调用商品工具写库。入库失败只需重试工具调用，不必重传文件。

鉴权与边界：

- 直传端点只认票据、不要求 `X-Mcp-Key`：避免长期密钥进入 AI 客户端的命令、对话上下文与命令历史；票据服务端只存 sha256 摘要，10 分钟过期后重新调用工具获取即可（不做单次消费，重试友好）。票据只能由有权调用 `crmeb_upload_ticket` 的账号获取，端点同样受服务总开关 `mcp_enabled` 约束。
- 端点只做「收字节 + 暂存」：暂存文件 1 小时未被入库会被机会式清理，正式入库必须经 `crmeb_upload_image`。
- 失败口径：票据缺失或无效返回 401；非图片、超 10MB 等逐条进 `data.failed[]`，不影响同批其他图片；单账号每分钟最多 60 张，超限返回 429；单批超过 20 张返回 400（超过服务端 `max_file_uploads` 上限时同样明确报错，不会静默丢弃）。
- 生产环境请经 HTTPS 调用并限制可信来源（见第 9 节）。

## 8. 错误响应

除 notification 返回 HTTP 204 外，当前接口通常以 HTTP 200 返回 JSON-RPC 成功或错误对象，调用方必须检查 `error`，不能只判断 HTTP 状态码。

| JSON-RPC code | 场景 | 常见 message |
|---:|---|---|
| `-32700` | 请求体不是有效 JSON 对象 | `Parse error` |
| `-32600` | 服务未开启、缺少密钥、密钥错误或账号已停用 | 对应认证失败原因 |
| `-32601` | 不支持的方法 | `Method not found: ...` |
| `-32603` | 未知工具、账号无权访问工具、可安全透传的业务异常或系统异常 | 业务提示或 `服务器内部错误` |

系统异常只在服务端日志中保留文件、行号和堆栈，响应不会暴露内部细节。

## 9. 安全与上线检查

- 只通过 HTTPS 对外提供服务，限制可信来源、请求大小、频率和并发。
- 按客户端最小授权：为不同客户端创建独立账号并只勾选需要的工具。
- 密钥疑似泄漏时立即在后台重置，并更新全部合法客户端。
- 写工具调用前由客户端明确展示工具名和参数，取得用户确认。
- 邮件、企业微信、URL、文件、SQL 和配置写入等能力需要额外检查目标范围与业务幂等。
- 记录请求 ID、工具名、调用方、耗时和结果摘要，日志不得记录完整密钥或敏感返回值。
- 不要把商城用户 token、管理员 token 或开放平台凭据当作 MCP 密钥使用。
- 生产环境仅开放确实需要的网络入口；无需外部访问时保持内网可达。

## 10. 常见问题

| 现象 | 排查方向 |
|---|---|
| 提示插件未安装或未启用 | 在插件管理中安装并启用 `service_mcp` |
| `MCP 服务未开启` | 在「系统配置 → MCP服务」中开启服务 |
| `该账号已停用，请联系管理员启用` | 账号被「状态」开关停用，在「MCP配置」页重新启用 |
| `缺少访问密钥` / `访问密钥错误` | 检查 `X-Mcp-Key` 或 Bearer 头，确认没有多余空格、密钥未被重置或账号未被删除 |
| `当前账号无权访问工具: ...` | 账号密钥只允许勾选的工具，在「MCP配置 → 编辑权限」中勾选该工具后重试 |
| 客户端 `tools/list` 缺少某些工具 | 账号白名单未勾选（系统账号同样受白名单约束，在「编辑权限」中勾选）；勾选后仍缺失则检查提供工具的插件是否启用 |
| `Method not found` | 当前仅支持 `initialize`、`tools/list`、`tools/call` |
| 工具不存在 | 重新执行 `tools/list`；确认提供该工具的插件已启用 |
| 客户端只能使用本地 command 类型 | 选择支持远程 HTTP MCP 的客户端或版本，本服务不提供 stdio 启动命令 |
| AI Agent 看不到新工具 | 系统账号白名单已勾选的前提下，在 AI Agent 的 MCP 服务管理中重新执行「同步工具」 |
| 直传端点返回 `401` | 票据缺失或已过期（有效期 10 分钟）：重新调用 `crmeb_upload_ticket` 获取新票据与新命令，不要复用旧命令 |
| 直传端点返回 `400` 提示超过服务端限制 | 单批文件数超过 PHP `max_file_uploads`（默认 20，与工具单次上限一致），拆分为每批不超过 20 张后重试 |
| 直传端点返回 `429` | 单账号每分钟最多 60 张，稍后重试或分批上传 |

## 11. 实现位置

| 内容 | 路径 |
|---|---|
| 路由 | `backend/modules/service/mcp/route/user.php` |
| 协议与认证 | `backend/modules/service/mcp/controller/McpController.php` |
| 图片直传端点（multipart 收字节 + 暂存 GC） | `backend/modules/service/mcp/controller/McpUploadController.php` |
| 直传票据签发/校验 | `backend/modules/service/mcp/service/McpUploadTicketServices.php` |
| 工具注册中心 | `backend/modules/service/mcp/McpToolRegistry.php` |
| 内置工具 | `backend/modules/service/mcp/tools/` |
| 账号列表页（MCP配置） | `backend/modules/service/mcp/schema/AccountListPage.php` |
| 账号服务 / DAO / 模型 | `backend/modules/service/mcp/service/McpAccountServices.php`、`dao/McpAccountDao.php`、`model/McpAccount.php` |
| 后台工具列表 | `backend/modules/service/mcp/schema/ToolListPage.php` |
| 安装与配置项 | `backend/modules/service/mcp/Installer.php` |
| 插件工具契约 | `backend/crmeb/interfaces/McpToolInterface.php` |

AI Agent 如何把本服务作为内置工具源使用，见 [AI Agent 模块](../AI%20Agent%20模块.md)。
