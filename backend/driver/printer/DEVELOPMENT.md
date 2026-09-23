# 打印驱动扩展开发指南

## 概述

打印驱动扩展位于 `driver/printer/{Driver}/` 目录，负责对接各类云打印服务（飞鹅云、易联云等），用于订单小票打印。

系统通过 `crmeb\services\driver\DriverRegistry` 扫描各驱动包的 `plugin.json` 完成驱动注册。

## 已有驱动

| 驱动目录 | 驱动标识 | 说明 |
|---------|---------|------|
| [FeiEYun](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/driver/printer/FeiEYun/) | — | 飞鹅云打印机 |
| [YiLianYun](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/driver/printer/YiLianYun/) | — | 易联云打印机 |

## 开发步骤

### 1. 创建驱动目录

```bash
mkdir -p driver/printer/{DriverName}
```

### 2. 创建 plugin.json

```json
{
    "key": "YourDriver",
    "name": "打印驱动名称",
    "description": "驱动描述",
    "version": "1.0.0",
    "author": "CRMEB",
    "type": "driver",
    "path": "driver/printer/YourDriver",
    "require": {
        "crmeb": "^6.0",
        "php": "^8.0"
    },
    "driver": {
        "name": "打印驱动名称",
        "driver_key": "YourDriver",
        "driver": "YourDriver",
        "icon": "",
        "description": "驱动描述",
        "config_fields": [
            {
                "field": "api_key",
                "label": "API Key",
                "type": "text",
                "required": true,
                "group": "基础配置",
                "tip": "输入说明"
            },
            {
                "field": "api_secret",
                "label": "API 密钥",
                "type": "password",
                "required": true,
                "group": "基础配置"
            },
            {
                "field": "device_sn",
                "label": "设备编号",
                "type": "text",
                "required": true,
                "group": "设备配置",
                "tip": "打印机设备编号（SN）"
            }
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
| `driver` | string | ✅ | 驱动目录名（打印驱动按目录名索引） |
| `icon` | string | ❌ | 图标 |
| `description` | string | ❌ | 驱动描述 |
| `config_fields` | array | ❌ | 配置字段 |
| `help_doc` | object | ❌ | 帮助文档 |

> 顶层字段（`key`/`name`/`description`/`version`/`author`/`type`/`path`/`require`）与通用 driver 插件一致，参见 [sms/DEVELOPMENT.md](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/driver/sms/DEVELOPMENT.md) 的字段说明表。

### 3. 创建 Driver.php

```php
<?php
namespace driver\printer\{DriverName};

use crmeb\services\printer\BasePrinter;

/**
 * {DriverName} 打印驱动
 */
class Driver extends BasePrinter
{
    /**
     * 初始化
     * @param array $config
     */
    protected function initialize(array $config): void
    {
        // 初始化配置
    }

    /**
     * 开始打印
     * @return bool|mixed|string
     * @throws \Exception
     */
    public function startPrinter()
    {
        if (!$this->printerContent) {
            return $this->setError('Missing print');
        }
        // 调用打印API
        $response = $this->accessToken->postRequest('http://api.example.com/open/', [
            'content' => $this->printerContent,
            'times' => $this->times,
        ]);
        $res = json_decode($response, true);
        if ($res['msg'] === 'ok') {
            return $res;
        }
        return $this->setError($res['msg']);
    }

    /**
     * 设置打印内容
     * @param string $content 打印内容
     * @param int $times 打印次数
     * @return $this
     */
    public function setPrinterContent($content, $times = 1): self
    {
        $this->times = $times;
        $this->printerContent = $content;
        return $this;
    }
}
```

### 4. 验证驱动

```php
use crmeb\services\driver\DriverRegistry;

$drivers = DriverRegistry::scan('printer');                              // 全部打印驱动
$class   = DriverRegistry::getDriverClass('printer', 'your_driver_key'); // 完整类名 driver\printer\YourDriver\Driver
```

## 接口规范

### 必须实现的方法

| 方法 | 说明 | 返回值 |
|------|------|--------|
| `startPrinter()` | 开始打印 | `bool\|mixed\|string` |

### 打印内容格式

打印内容通常包含以下要素，需按各打印服务商要求的格式组装：

```
// 小票打印内容示例
<center>**订单小票**</center>
--------------------------------
商品名称      数量    价格
--------------------------------
商品A         1      ¥29.90
商品B         2      ¥19.80
--------------------------------
合计：               ¥69.50
--------------------------------
订单号：ORD202501011234
时间：2025-01-01 10:00:00
```

## 典型配置字段

打印驱动通常需要以下配置：

| 字段 | 类型 | 说明 |
|------|------|------|
| `user_id` | text | 用户ID/开发者ID |
| `app_id` | text | 应用ID |
| `app_secret` | password | 应用密钥 |
| `device_sn` | text | 打印机设备编号（SN） |
| `enable_voice` | switch | 语音播报开关 |

## 参考实现

- [飞鹅云打印机](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/driver/printer/FeiEYun/) — 飞鹅云API方式
- [易联云打印机](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/driver/printer/YiLianYun/) — 易联云API方式
