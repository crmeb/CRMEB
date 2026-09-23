# 邮件驱动扩展开发指南

## 概述

邮件驱动扩展位于 `driver/email/{Driver}/` 目录，负责对接各类邮件发送服务（SMTP、阿里云邮件推送、QQ邮箱等）。

系统通过 `crmeb\services\driver\DriverRegistry` 扫描各驱动包的 `plugin.json` 完成驱动注册。**邮件驱动状态互斥**，同时只能启用一个驱动。

## 已有驱动

| 驱动目录 | 驱动标识 | 说明 |
|---------|---------|------|
| [Smtp](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/driver/email/Smtp/) | `smtp` | SMTP 通用邮件（标准 SMTP 协议） |
| [AliyunDm](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/driver/email/AliyunDm/) | `aliyun_dm` | 阿里云邮件推送（DirectMail API） |
| [Qq](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/driver/email/Qq/) | `qq` | QQ邮箱（SMTP 方式） |

## 开发步骤

### 1. 创建驱动目录

```bash
mkdir -p driver/email/{DriverName}
```

### 2. 创建 plugin.json

```json
{
    "key": "your_driver_key",
    "name": "邮件驱动名称",
    "description": "驱动功能简述",
    "version": "1.0.0",
    "author": "CRMEB",
    "type": "driver",
    "path": "driver/email/YourDriver",
    "require": {
        "crmeb": "^6.0",
        "php": "^8.0"
    },
    "driver": {
        "name": "邮件驱动名称",
        "driver_key": "your_driver_key",
        "icon": "icon-xxx",
        "is_builtin": true,
        "sort": 100,
        "capabilities": ["send"],
        "config_fields": [
            {"field": "host", "label": "SMTP服务器", "type": "text", "required": true, "group": "服务器"},
            {"field": "port", "label": "SMTP端口", "type": "text", "group": "服务器", "default": "465"},
            {"field": "username", "label": "邮箱地址", "type": "text", "required": true, "group": "账号"},
            {"field": "password", "label": "密码/授权码", "type": "password", "required": true, "group": "账号"},
            {"field": "from_name", "label": "发件人名称", "type": "text", "group": "账号", "default": "CRMEB"}
        ],
        "help_doc": {
            "title": "使用说明",
            "steps": ["步骤1", "步骤2"],
            "link": "https://example.com"
        }
    }
}
```

#### plugin.json driver 子对象字段说明

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| `name` | string | ✅ | 驱动名称 |
| `driver_key` | string | ✅ | 驱动唯一标识 |
| `icon` | string | ❌ | 图标类名 |
| `is_builtin` | bool | ❌ | 是否内置 |
| `sort` | int | ❌ | 排序 |
| `capabilities` | array | ❌ | 能力声明 |
| `config_fields` | array | ❌ | 配置字段 |
| `help_doc` | object | ❌ | 帮助文档 |

> 顶层字段（`key`/`name`/`description`/`version`/`author`/`type`/`path`/`require`）与通用 driver 插件一致，参见 [sms/DEVELOPMENT.md](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/driver/sms/DEVELOPMENT.md) 的字段说明表。

### 3. 创建 Driver.php

```php
<?php
declare(strict_types=1);

namespace driver\email\{DriverName};

use crmeb\services\email\BaseEmail;
use crmeb\services\email\EmailInterface;

/**
 * {DriverName} 邮件驱动
 */
class Driver extends BaseEmail implements EmailInterface
{
    protected array $config = [];

    /**
     * 初始化配置
     */
    protected function initialize(array $config): void
    {
        $this->config = $config;
    }

    /**
     * 发送邮件
     * @param string $to 收件人
     * @param string $subject 主题
     * @param string $content 内容（HTML格式）
     * @param array $options 附加选项
     * @return bool
     */
    public function send(string $to, string $subject, string $content, array $options = []): bool
    {
        // 实现邮件发送逻辑
    }
}
```

### 4. 验证驱动

```php
use crmeb\services\driver\DriverRegistry;

$drivers = DriverRegistry::scan('email');                              // 全部邮件驱动
$class   = DriverRegistry::getDriverClass('email', 'your_driver_key'); // 完整类名 driver\email\YourDriver\Driver
```

## 接口规范

### 必须实现的方法

| 方法 | 说明 | 参数 | 返回值 |
|------|------|------|--------|
| `send()` | 发送邮件 | `$to`, `$subject`, `$content`, `$options` | `bool` |

### 传递多个收件人

```php
// 批量发送时循环调用 send()，或在单次调用中实现批量
$emails = ['a@example.com', 'b@example.com'];
foreach ($emails as $email) {
    $driver->send($email, '主题', '内容');
}
```

## 配置存储

邮件驱动配置存储在 `eb_email_driver` 表的 `config_fields` JSON 字段中，**禁止写入 `system_config` 表**。

## 最佳实践

### 阿里云邮件推送（API 方式）

阿里云邮件推送通过 HTTP API 调用，需要实现签名算法：

```php
public function send(string $to, string $subject, string $content, array $options = []): bool
{
    // 1. 构建请求参数
    $params = [
        'Action' => 'SingleSendMail',
        'AccessKeyId' => $this->config['access_key_id'],
        // ...
    ];
    // 2. 生成签名
    $params['Signature'] = $this->generateSignature($params, $secret);
    // 3. 发送 HTTP 请求
    $response = Http::get($url . '?' . http_build_query($params));
    // 4. 处理响应
}
```

### SMTP 方式

SMTP 方式推荐使用第三方库（如 PHPMailer、Symfony Mailer）实现，配置字段通常包含：

| 字段 | 说明 |
|------|------|
| `host` | SMTP 服务器地址 |
| `port` | SMTP 端口（465/SSL，587/TLS） |
| `username` | 邮箱账号 |
| `password` | 密码或授权码 |
| `from_name` | 发件人显示名称 |

### 错误处理

```php
public function send(...): bool
{
    if (empty($to)) {
        return $this->setError('收件人不能为空');
    }
    try {
        // 发送逻辑
    } catch (\Throwable $e) {
        return $this->setError('邮件发送异常: ' . $e->getMessage());
    }
}
```

## 参考实现

- [阿里云邮件推送](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/driver/email/AliyunDm/) — HTTP API 方式实现
- [QQ邮箱](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/driver/email/Qq/) — SMTP 方式实现
- [SMTP通用](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/driver/email/Smtp/) — 通用 SMTP 实现
