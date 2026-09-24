---
name: mcp-tools
description: 插件向 MCP 服务贡献工具的完整流程：实现 crmeb\interfaces\McpToolInterface 契约、监听 McpToolsRegister 事件注册工具、工具命名规范、description 与参数 Schema 编写规范、写工具确认声明、label/category 后台展示可选方法、异常通道现状与 curl 自测。插件需要让 AI 客户端通过 /api/mcp 调用本插件能力时阅读。
---

# 插件扩展 MCP 工具

MCP 服务由默认安装模块 `backend/modules/service/mcp/` 提供，单入口 `POST /api/mcp`（JSON-RPC 2.0，密钥认证与工具清单见 [MCP 接口](../../../../help/docs/api/MCP接口.md)）。插件通过字符串事件 `McpToolsRegister` 向其工具注册中心追加工具；工具契约是系统级接口 `crmeb\interfaces\McpToolInterface`（位于 `backend/crmeb/interfaces/`），插件实现它不构成对任何模块类的依赖。

事件条款（字符串 key、监听器只注册在本模块 `event.php`）见 [事件规范.md](../../../rules/事件规范.md) 4.11；插件边界红线见 [插件开发规范.md](../../../rules/插件开发规范.md)。本文只写操作步骤。

## 三步接入（以卡密插件 `modules/trade/cardkey/` 为例）

### 1. 创建工具类（插件自有目录，实现核心契约）

```php
<?php
// backend/modules/trade/cardkey/mcp/CardKeyOrderListTool.php
declare(strict_types=1);

namespace modules\trade\cardkey\mcp;

use app\dao\order\OrderCenterDao;
use crmeb\interfaces\McpToolInterface;

/**
 * MCP 工具：卡密订单列表（插件贡献）
 */
class CardKeyOrderListTool implements McpToolInterface
{
    /**
     * 订单中台 DAO
     * @var OrderCenterDao
     */
    private OrderCenterDao $dao;

    /**
     * 构造函数
     * @param OrderCenterDao $dao 订单中台 DAO
     */
    public function __construct(OrderCenterDao $dao)
    {
        $this->dao = $dao;
    }

    public function name(): string
    {
        return 'crmeb_cardkey_order_list';
    }

    public function description(): string
    {
        // 面向 AI 模型的“说明书”，按下方「描述与参数 Schema 编写规范」三段式撰写
        return '查询卡密类型订单列表，支持按发货状态筛选，返回订单号、商品标题与发卡状态等字段；'
            . '仅含卡密发货订单，不含普通商品订单，卡密明文不出现在列表结果中。';
    }

    public function label(): string
    {
        return '卡密订单';
    }

    public function category(): string
    {
        return '订单';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'page'  => ['type' => 'number', 'description' => '页码'],
                'limit' => ['type' => 'number', 'description' => '每页数量'],
            ],
        ];
    }

    public function execute(array $args): array
    {
        // 只调用 app/ 层 DAO/Service；返回前对手机号、密钥等敏感字段自行脱敏
    }
}
```

要点：

- 构造器注入 `app/` 层 DAO/Service（容器解析，依赖方向 `modules → app` 合法）。
- 写工具额外实现可选方法 `public function interaction(): string`，返回 `'execute'` 声明"修改系统数据、需客户端确认"；删除类写操作（不可逆移除数据）返回 `'delete'`，后台按「（删）」标注展示、AI 客户端同样需确认；不实现或返回空串视为只读。采用可选方法而非接口成员，保证已注册工具无需随核心改动。
- 建议实现可选方法 `public function label(): string` 返回简短中文名称（2~6 字，如「卡密订单」）：后台工具列表与账号权限勾选优先展示中文名，`description()` 面向 AI 模型、常为长段说明，不适合做勾选文案；不实现或返回空串时回退展示 `description()`。
- 建议实现可选方法 `public function category(): string` 返回分类名（2~6 字中文）：后台工具列表与账号权限勾选按分类分组展示。内置分类名与展示顺序由 `McpToolRegistry::CATEGORY_ORDER` 约定（用户、商品、订单、售后退款、营销、系统设置、消息通知、开发支持），按业务领域选最贴近的一个；插件也可返回自定义分类名，后台按出现顺序追加为新分组。不实现或返回空串时归入「其他」分组排在最后。
- 同类工具集中放插件 `mcp/` 目录，文件名随意（自动发现约定只对模块内置 `tools/` 目录生效，插件工具一律走事件注册）。

### 2. 创建监听器

```php
<?php
// backend/modules/trade/cardkey/listener/McpToolsRegisterListener.php
declare(strict_types=1);

namespace modules\trade\cardkey\listener;

use crmeb\interfaces\ListenerInterface;
use modules\trade\cardkey\mcp\CardKeyOrderListTool;

class McpToolsRegisterListener implements ListenerInterface
{
    /**
     * 向注册中心注册插件工具（传类名，容器延迟实例化并注入依赖）
     *
     * @param mixed $event McpToolRegistry 实例
     */
    public function handle($event): void
    {
        $event->register(CardKeyOrderListTool::class);
    }
}
```

要点：

- 监听器内只做工具注册，**不写业务逻辑**：`McpToolsRegister` 属注册中心类事件（关键级），监听器抛异常会导致整个 MCP 接口 500。
- 参数类型不写 `McpToolRegistry` 类型标注（该类在 modules 层，避免产生 use 依赖）；需要文档说明时用 docblock 全限定名。
- `register()` 同名覆盖既有工具——用于定制内置工具行为，也是命名冲突风险来源。

### 3. 在插件自身 `event.php` 注册

```php
// backend/modules/trade/cardkey/event.php
return [
    'listen' => [
        'McpToolsRegister' => [
            \modules\trade\cardkey\listener\McpToolsRegisterListener::class,
        ],
    ],
];
```

`tools/list` 与 `tools/call` 每次请求都会重建注册表并触发该事件，插件启用即生效，无需缓存或额外初始化。

## 描述与参数 Schema 编写规范

`description()` 是 AI 模型选择工具、组装参数时阅读的"说明书"，写得薄会直接导致模型选错工具、传错参数。按三段式撰写，内置工具中的优秀参照是 `modules/service/mcp/tools/AfterSaleListTool.php`：

1. **用途与返回**：一句话说清查什么/做什么，列出返回的关键字段；
2. **关键参数**：筛选参数的含义、枚举值逐个列出（如订单状态码 `pending_payment` 待付款、`completed` 已完成……）、默认值与上限（如「每页数量最大 100」）；
3. **口径边界**：不返回什么（「不含收货人地址」「手机号已脱敏」）、与相近工具的区别（「与 crmeb_refund_list 按订单退款标记筛选不同」）。

反面教材是「获取商品列表」这类一句话描述——能力、枚举、边界全部丢失。工具描述的长度没有硬性上限，把模型需要的判断依据写全即可。

补充要点：

- `label()` 与 `description()` 分工：`label()` 是给人看的短中文名（2~6 字，后台工具列表与账号权限勾选展示），`description()` 是给模型看的说明书，两者不要互相替代；`category()` 只承担后台分组，不写入 `description()`。
- `description()` 用中文撰写即可，模型理解无障碍；工具名 `name()` 保持 `crmeb_` 前缀 ASCII 标识，这是调用契约，不允许中文。
- 管理端展示链路：`label`/`category`/`description` 随 `tools/list` 下发，AI Agent「同步工具」后写入工具缓存，在 MCP 管理页与对话工具清单中展示；**修改描述后需在内置 MCP 上重新同步工具才对 AI Agent 生效**（服务端 `/api/mcp` 即时生效，无缓存）。

## 强制约定

| 项 | 约定 |
| --- | --- |
| 工具命名 | `crmeb_{插件标识}_{动作}`（如 `crmeb_cardkey_order_list`）。同名注册会覆盖既有工具，命名必须带插件标识 |
| 参数 Schema | 字段 `description` 必须与 `execute()` 实现严格一致：声明的筛选能力、格式、枚举值实现里必须真的存在（如实现在就匹配订单号/商品标题，不得写"匹配收件人"）；通配符转义等校验在实现内兜底 |
| 写工具 | 必须声明 `interaction(): 'execute'`（删除类不可逆操作返回 `'delete'`，后台按「删」标注），客户端据该声明在执行前取得用户确认。写操作的业务校验与授权不得因 AI 调用而豁免（见 [安全红线.md](../../../rules/安全红线.md)） |
| 数据访问 | 读系统表走自有 DAO 只读；写数据只能调用对应核心 Service 公开方法，禁止直写系统表（红线见 [插件开发规范.md](../../../rules/插件开发规范.md) 第三节） |
| 生命周期 | 插件禁用/卸载后 `event.php` 不再加载，工具自动失效，Installer 无需任何清理逻辑 |

## 已知限制（截至 v8.1.xu）

| 限制 | 绕行方案 |
| --- | --- |
| 业务异常消息无法合规透传：`tools/call` 只对 `modules\service\mcp\McpException` 透传消息，其余异常一律返回"服务器内部错误"；而插件不能 use modules 异常类 | 业务错误以结构化数据返回（如 `['error' => '卡密已核销']`），不抛异常 |
| 脱敏工具 `SensitiveData` 在 modules 层，插件不可 use | 工具返回前自行脱敏（手机号中间四位、密钥全掩码等） |
| `McpToolRegistry` 类型标注来自 modules 层 | 监听器 `handle($event)` 不写模块类类型标注，用 docblock 说明 |

上述类的下沉是既定方向；下沉完成后本节应同步删除并更新示例。

## 自测

```bash
KEY=<mcp_appkey>
URL=http://localhost:8011/api/mcp

# 1. 握手
curl -s $URL -H "X-Mcp-Key: $KEY" -H 'Content-Type: application/json' \
  -d '{"jsonrpc":"2.0","id":1,"method":"initialize","params":{"protocolVersion":"2024-11-05"}}'

# 2. 确认工具已注册（响应应包含 crmeb_cardkey_order_list）
curl -s $URL -H "X-Mcp-Key: $KEY" -H 'Content-Type: application/json' \
  -d '{"jsonrpc":"2.0","id":2,"method":"tools/list"}'

# 3. 实际调用
curl -s $URL -H "X-Mcp-Key: $KEY" -H 'Content-Type: application/json' \
  -d '{"jsonrpc":"2.0","id":3,"method":"tools/call","params":{"name":"crmeb_cardkey_order_list","arguments":{"page":1,"limit":10}}}'
```

写工具来自插件时，先确认插件处于启用状态；禁用后重复第 2 步，响应中不应再出现该工具。
