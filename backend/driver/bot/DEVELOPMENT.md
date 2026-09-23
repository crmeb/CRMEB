# 机器人通知驱动扩展开发指南

## 概述

机器人通知驱动扩展位于 `driver/bot/{Driver}/` 目录，负责对接各类机器人通知服务（钉钉、企业微信、飞书等），用于向群机器人发送系统通知消息。

系统通过 `crmeb\services\driver\DriverRegistry` 扫描各驱动包的 `plugin.json` 完成驱动注册。

## 已有驱动

| 驱动目录 | 驱动标识 | 说明 |
|---------|---------|------|
| [DingTalk](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/driver/bot/DingTalk/) | `dingtalk` | 钉钉自定义机器人 |
| [EntWechat](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/driver/bot/EntWechat/) | `ent_wechat` | 企业微信机器人 |
| [Feishu](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/driver/bot/Feishu/) | `feishu` | 飞书自定义机器人 |

## 开发步骤

### 1. 创建驱动目录

```bash
mkdir -p driver/bot/{DriverName}
```

### 2. 创建 plugin.json

```json
{
    "key": "your_driver_key",
    "name": "驱动名称",
    "description": "驱动功能简述",
    "version": "1.0.0",
    "author": "CRMEB",
    "type": "driver",
    "path": "driver/bot/YourDriver",
    "driver": {
        "name": "机器人名称",
        "driver_key": "your_driver_key",
        "class_name": "Driver",
        "icon": "icon-xxx",
        "is_builtin": true,
        "sort": 100,
        "capabilities": ["send_text", "send_markdown", "send_card"],
        "config_fields": [
            {
                "field": "webhook",
                "label": "Webhook地址",
                "type": "text",
                "required": true,
                "group": "配置",
                "tip": "机器人的Webhook地址"
            }
        ],
        "help_doc": [
            {
                "title": "配置文档",
                "url": "https://docs.example.com"
            }
        ]
    }
}
```

#### plugin.json driver 子对象字段说明

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| `name` | string | ✅ | 驱动显示名称 |
| `driver_key` | string | ✅ | 驱动唯一标识 |
| `class_name` | string | ❌ | 驱动类名，默认 `Driver` |
| `icon` | string | ❌ | 图标类名 |
| `is_builtin` | bool | ❌ | 是否内置驱动 |
| `sort` | int | ❌ | 排序权重 |
| `capabilities` | array | ❌ | 能力声明（`send_text` 文本消息；`send_markdown` Markdown 消息；`send_card` 卡片消息） |
| `config_fields` | array | ❌ | 配置字段定义（后台动态渲染表单） |
| `help_doc` | array | ❌ | 帮助文档（`[{title, url}]` 链接数组） |

> 顶层字段（`key`/`name`/`description`/`version`/`author`/`type`/`path`/`require`）为各驱动类型通用结构，完整说明见[短信驱动开发指南](../sms/DEVELOPMENT.md#pluginjson-字段说明)。

### 3. 创建 Driver.php

```php
<?php
declare(strict_types=1);

namespace driver\bot\{DriverName};

use crmeb\services\bot\BaseBot;

/**
 * {DriverName} 机器人
 */
class Driver extends BaseBot
{
    /**
     * 发送文本消息
     * @param string $content 文本内容
     * @param array $options 附加选项
     * @return bool
     */
    public function sendText(string $content, array $options = []): bool
    {
        // 实现文本消息发送
        return $this->request([
            'msgtype' => 'text',
            'text' => ['content' => $content],
        ]);
    }

    /**
     * 发送 Markdown 消息
     * @param string $title 标题
     * @param string $content Markdown 内容
     * @param array $options 附加选项
     * @return bool
     */
    public function sendMarkdown(string $title, string $content, array $options = []): bool
    {
        // 实现 Markdown 消息发送
    }

    /**
     * 发送卡片消息
     * @param array $card 卡片数据
     * @param array $options 附加选项
     * @return bool
     */
    public function sendCard(array $card, array $options = []): bool
    {
        // 实现卡片消息发送
    }
}
```

### 4. 验证驱动

```php
// 手动验证（通过驱动注册中心）
use crmeb\services\driver\DriverRegistry;

$drivers = DriverRegistry::scan('bot');                              // 全部机器人驱动
$class   = DriverRegistry::getDriverClass('bot', 'your_driver_key'); // 完整类名 driver\bot\YourDriver\Driver
```

## 接口规范

### 必须实现的方法

| 方法 | 说明 | 参数 | 返回值 |
|------|------|------|--------|
| `sendText()` | 发送纯文本消息 | `$content`, `$options` | `bool` |

### 可选实现的方法

| 方法 | 说明 | 返回值 |
|------|------|--------|
| `sendMarkdown()` | 发送 Markdown 格式消息 | `bool` |
| `sendCard()` | 发送交互式卡片消息 | `bool` |

### 消息格式差异

不同平台的消息格式差异较大，需要适配各平台的 API 规范：

| 平台 | text 格式 | markdown 格式 | card 格式 |
|------|-----------|---------------|-----------|
| 钉钉 | `msgtype: text` | `msgtype: markdown` | `msgtype: actionCard` |
| 企业微信 | `msgtype: text` | `msgtype: markdown`（无标题，需合并） | `msgtype: news` |
| 飞书 | `msg_type: text` | `msg_type: post`（富文本） | `msg_type: interactive` |

## Webhook 请求

所有机器人驱动通过 BaseBot 基类的 `request()` 方法发送 Webhook 请求，基类已封装 HTTP 请求逻辑，子类只需构建消息体：

```php
// 基类 request() 方法已处理：
// - 向 webhook URL 发送 POST 请求
// - 自动设置 Content-Type: application/json
// - 处理响应状态码
// - 返回 bool 结果

// 子类只需构建消息体数组
return $this->request([
    'msgtype' => 'text',
    'text' => ['content' => $content],
]);
```

## 参考实现

- [钉钉机器人](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/driver/bot/DingTalk/) — 标准机器人实现
- [企业微信机器人](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/driver/bot/EntWechat/) — Markdown 标题合并到内容的适配
- [飞书机器人](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/driver/bot/Feishu/) — 飞书 post 富文本格式适配
