# 驱动分册 · 机器人通知（bot）

核对日期：2026-11-06。先读 [00-总览](00-总览-类型路由与边界.md) 第 3 节（驱动通用机制），本篇只写群机器人/Webhook 通知专属内容。

## 1. 选型判定

| 需求 | 落本类型 |
| --- | --- |
| 向钉钉/企业微信/飞书群机器人发 Webhook 消息 | `backend/driver/bot/{Dir}/` |
| 短信、邮件、站内信 | 各自分册 |
| 新增「通知渠道」类型（如 Slack） | 驱动可写，但 **app 消息渠道未注册则不会被调用** |

bot 驱动只做 HTTP POST 到 Webhook；消息模板、变量替换、渠道开关在 `app/services/message/` 与 `eb_notice_template`。

## 2. 开工前必须告诉用户

| 渠道 | 是否走 bot 驱动 |
| --- | --- |
| 钉钉 | **是**：`DingTalkChannel` → `Bot::sendMarkdown('ding_talk', $webhook, ...)` |
| 企业微信 | **否**：`EntWechatChannel` 直接用 `Http::postRequest`，未经过 `driver/bot/EntWechat` |
| 飞书 | **否**：仓库有 `driver/bot/Feishu/`，但无对应 `NoticeChannel` 实现 |

因此：新装 `Feishu` 或自定义 bot 驱动，**不会**自动进入「消息通知」配置页，除非用户同意新增 `AbstractNoticeChannel` 子类并注册到通知系统。

另外 **`driver_key` 与 Bot 调用名不一致**：

| plugin.json `driver_key` | `Bot::send*` 传入名 | 目录 |
| --- | --- | --- |
| `dingtalk` | **`ding_talk`**（下划线） | `DingTalk/` |
| `ent_wechat` | 渠道未用 Bot | `EntWechat/` |
| `feishu` | 无调用方 | `Feishu/` |

`Str::studly('ding_talk')` → `DingTalk`，与目录一致；若调用方写 `dingtalk` 会找不到类。

## 3. 文件清单

```text
backend/driver/bot/{Dir}/
├── plugin.json
└── Driver.php    # namespace driver\bot\{Dir}; extends BaseBot implements BotInterface
```

| 组件 | 路径 |
| --- | --- |
| 基类 | `crmeb\services\bot\BaseBot`（`request()` POST JSON） |
| 门面 | `crmeb\services\bot\Bot`（静态 `sendText` / `sendMarkdown` / `sendCard`） |
| 接口 | `crmeb\services\bot\BotInterface` |

现役：

| 目录 | driver_key | 说明 |
| --- | --- | --- |
| `DingTalk/` | `dingtalk` | 钉钉自定义机器人 |
| `EntWechat/` | `ent_wechat` | 实现存在，通知渠道未用 |
| `Feishu/` | `feishu` | 飞书 post/交互格式 |

**推荐照抄**：`backend/driver/bot/DingTalk/`（最小三方法）。

## 4. plugin.json

```json
{
    "key": "your_bot",
    "name": "某某群机器人",
    "description": "Webhook 机器人",
    "version": "1.0.0",
    "author": "作者",
    "type": "driver",
    "path": "driver/bot/YourBot",
    "default_install": false,
    "require": { "crmeb": "^8.0", "php": "^8.0" },
    "driver": {
        "name": "某某群机器人",
        "driver_key": "your_bot",
        "sort": 100,
        "capabilities": ["send_text", "send_markdown", "send_card"],
        "config_fields": [
            {
                "field": "your_bot_webhook",
                "label": "Webhook 地址",
                "type": "text",
                "required": true,
                "group": "配置",
                "tip": "也可只在通知模板 extra 里配置"
            }
        ],
        "help_doc": {
            "title": "配置说明",
            "steps": ["在群设置添加自定义机器人", "复制 Webhook", "在通知模板中填写 extra.webhook"],
            "link": ""
        }
    }
}
```

说明：

- 钉钉渠道实际 Webhook 来自 **`eb_notice_template.extra.webhook`**（按模板配置），不是必须读 `eb_driver.config_fields`。
- `help_doc` 可为对象 `{title, steps, link}` 或链接数组（现役钉钉用数组）。

## 5. Driver.php 方法签名

```php
<?php
declare(strict_types=1);

namespace driver\bot\YourBot;

use crmeb\services\bot\BaseBot;

class Driver extends BaseBot
{
    public function sendText(string $content, array $options = []): bool
    {
        return $this->request([
            'msgtype' => 'text',
            'text' => ['content' => $content],
        ]);
    }

    public function sendMarkdown(string $title, string $content, array $options = []): bool
    {
        // 按平台文档组装 msgtype / markdown 或 post
    }

    public function sendCard(array $card, array $options = []): bool
    {
        // 钉钉 actionCard、飞书 interactive 等
    }
}
```

### 5.1 契约

| 方法 | 必填 | 返回值 |
| --- | --- | --- |
| `sendText` | 是 | `bool`，失败时 `BaseBot::request` 返回 false |
| `sendMarkdown` | 钉钉渠道需要 | 同上 |
| `sendCard` | 否 | 同上 |

`BaseBot::request()`：向 `$this->webhook` POST JSON；成功条件 `errcode===0` 或 `code===0`（兼容部分网关）。

Webhook 由调用方注入：

```php
Bot::sendMarkdown('ding_talk', $webhook, $title, $content);
// 内部 new Bot($driver); setWebhook($webhook); sendMarkdown(...)
```

### 5.2 平台消息体差异（实现时对照）

| 平台 | text | markdown |
| --- | --- | --- |
| 钉钉 | `msgtype:text` | `msgtype:markdown` + `title`/`text` |
| 企业微信 | `msgtype:text` | `msgtype:markdown`，标题常合并进 content |
| 飞书 | `msg_type:text` | 多为 `msg_type:post` 富文本 |

EntWechat 渠道在 app 层已写死 markdown 引用块格式，与 `EntWechat/Driver.php` 无关。

## 6. 配置存储

| 存储 | 用途 |
| --- | --- |
| `eb_driver` | 驱动元数据；可选默认 webhook 字段 |
| `eb_notice_template.extra` | 渠道级 Webhook：`dingtalk` → `webhook`；企业微信 → `url` |

驱动内不要持久化 Webhook 到业务表。

## 7. 调用链

```text
消息通知任务
  → Notice 分发 → DingTalkChannel::send(NoticeMessage)
  → 读 extra.webhook、渲染模板变量
  → Bot::sendMarkdown('ding_talk', $webhook, $title, $content)
  → driver\bot\DingTalk\Driver::sendMarkdown
  → BaseBot::request

企业微信
  → EntWechatChannel::send → Http::postRequest($url, ...)  // 不经过 bot 驱动
```

手动调试（不写库）：

```php
\crmeb\services\bot\Bot::sendText('ding_talk', 'https://oapi.dingtalk.com/robot/send?access_token=...', 'ping');
```

## 8. 逐步开发流程

1. 定 `driver_key` 与目录 `{Dir}`；确认将来调用方传的 studly 名（如 `ding_talk` vs `dingtalk`）。
2. 复制 `DingTalk/Driver.php`，改 JSON 结构。
3. 编写 `plugin.json`；第三方机器人 `default_install: false`。
4. `php think plugin:manifest-check`；安装插件。
5. 若需接入通知中心：与用户确认新增 Channel 类、`channelKey()`、模板 extra 字段、注册表配置（改 `app/`）。
6. 用真实 Webhook 测 `Bot::sendText` / `sendMarkdown`；钉钉注意关键词/签名校验。

## 9. 参考实现

- `driver/bot/DingTalk/Driver.php`：三种 msgtype。
- `driver/bot/Feishu/Driver.php`：post 富文本。
- `app/services/message/channel/DingTalkChannel.php`：唯一生产调用链。

## 10. 打包（`.plugin-packages/`）

```bash
cd backend
php -l driver/bot/YourBot/Driver.php
php think plugin:manifest-check
zip -r ../.plugin-packages/driver-bot-your_bot-1.0.0.zip driver/bot/YourBot/
```

## 11. 验收清单

- [ ] 四名字对齐；`Bot::send*` 使用的 driver 字符串与 `Str::studly` 结果能解析到目录
- [ ] 三方法签名符合 `BotInterface`
- [ ] 无业务层 `use`；Webhook 不打印到 Log
- [ ] 安装后 `DriverRegistry::scan('bot')` 可见
- [ ] **仅驱动**：CLI/脚本调用 Bot 静态方法成功
- [ ] **接通知渠道后**：模板开启钉钉、填 webhook，平台通知能收到

## 12. 常见失败现象

| 现象 | 原因 |
| --- | --- |
| `Bot driver [dingtalk] not supported` | 调用应用 `dingtalk` 而非 `ding_talk` |
| 驱动安装但通知发不出 | 未建 NoticeChannel；或模板未配 `extra.webhook` |
| `钉钉 webhook 未配置` | 模板 extra 空 |
| `钉钉机器人返回发送失败` | 关键词不匹配、IP 白名单、签名错误；`request()` 只返回 false |
| 企业微信改了 EntWechat 驱动无效 | 渠道不走驱动 |
| 飞书驱动无效果 | 无飞书 Channel |

## 13. 安全与日志

- Webhook URL 含 access_token，禁止写入提交说明、禁止 `Log::info` 全量 URL。
- 失败时 `DingTalkChannel` 抛 `NoticeSendException`，由通知任务记录；驱动层保持返回 bool，不吞异常外的业务逻辑。

## 14. NoticeChannel 接入要点（需改 app 时）

若用户要求新机器人走「消息通知」：

1. 新增 `app/services/message/channel/XxxChannel.php` 继承 `AbstractNoticeChannel`。
2. 实现 `channelKey()`（对应模板表开关字段，如 `is_dingtalk`）、`getExtraFields()`（webhook/url）。
3. `send()` 内调用 `Bot::sendMarkdown('{studly_name}', $webhook, ...)`，**studly 名与目录一致**。
4. 在通知渠道注册表（与现有 DingTalk/EntWechat 同级）登记该类。
5. 管理端模板配置页需能编辑 extra 字段（通常已有通用机制）。

企业微信若改为走驱动：可重构 `EntWechatChannel` 调 `Bot::sendMarkdown('ent_wechat', ...)`，与 `driver/bot/EntWechat` 对齐，属于核心改造。

## 15. 本地验证

```bash
cd backend && php -r 'require "vendor/autoload.php"; (new think\App(getcwd()."/"))->initialize();
foreach (["dingtalk","ding_talk"] as $t) {
  try { echo $t, " => ", (new \crmeb\services\bot\Bot($t))->sendText("test"), " (class ok)\n"; }
  catch (\Throwable $e) { echo $t, " => ", $e->getMessage(), "\n"; }
}'
```

预期：`ding_talk` 可实例化；`dingtalk` 可能报 not supported（说明调用名必须用下划线形式）。

## 16. capabilities 说明

`send_text` / `send_markdown` / `send_card` 声明供后台展示；`DingTalkChannel` 仅使用 markdown。卡片消息需业务侧额外调用 `Bot::sendCard`，当前通知模板未默认支持。

## 17. 与 `plugin.json` key 不一致案例

`driver/bot/DingTalk/` 的 `key` 为 `dingtalk`，目录为 `DingTalk`。复合标识安装时用 `driver:bot:dingtalk`。切勿把目录改成小写 `dingtalk/`，Linux PSR-4 会加载失败。
