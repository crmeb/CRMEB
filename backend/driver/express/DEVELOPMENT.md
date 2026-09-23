# 电子面单驱动扩展开发指南

## 概述

电子面单驱动扩展位于 `driver/express/{Driver}/` 目录，负责对接各类电子面单打印服务（一号通、测试面单等），用于订单发货时自动打印快递面单。

系统通过 `crmeb\services\driver\DriverRegistry` 扫描各驱动包的 `plugin.json` 完成驱动注册。

## 已有驱动

| 驱动目录 | 驱动标识 | 说明 |
|---------|---------|------|
| [Yihaotong](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/driver/express/Yihaotong/) | `yihaotong` | 一号通电子面单 |
| [TestExpress](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/driver/express/TestExpress/) | `test_express` | 测试电子面单 |

## 开发步骤

### 1. 创建驱动目录

```bash
mkdir -p driver/express/{DriverName}
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
    "path": "driver/express/YourDriver",
    "driver": {
        "name": "面单驱动名称",
        "driver_key": "your_driver_key",
        "class_name": "Driver",
        "icon": "icon-xxx",
        "is_builtin": true,
        "sort": 100,
        "config_fields": [
            {
                "field": "open",
                "label": "开启电子面单",
                "type": "switch",
                "group": "接口配置",
                "tip": "开启后可在订单发货时使用电子面单打印"
            },
            {
                "field": "api_key",
                "label": "应用Key",
                "type": "text",
                "required": true,
                "group": "基础配置"
            },
            {
                "field": "api_secret",
                "label": "应用密钥",
                "type": "password",
                "required": true,
                "group": "基础配置"
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
| `name` | string | ✅ | 驱动显示名称 |
| `driver_key` | string | ✅ | 驱动唯一标识 |
| `class_name` | string | ❌ | 驱动类名，默认 `Driver` |
| `icon` | string | ❌ | 图标类名 |
| `is_builtin` | bool | ❌ | 是否内置驱动 |
| `sort` | int | ❌ | 排序权重 |
| `config_fields` | array | ❌ | 配置字段定义（后台动态渲染表单；`open` 为 switch 开关字段，控制功能启用） |
| `help_doc` | object | ❌ | 帮助文档（`{title, steps, link}`） |

> 顶层字段（`key`/`name`/`description`/`version`/`author`/`type`/`path`/`require`）为各驱动类型通用结构，完整说明见[短信驱动开发指南](../sms/DEVELOPMENT.md#pluginjson-字段说明)。

### 3. 创建 Driver.php

```php
<?php
namespace driver\express\{DriverName};

use crmeb\services\express\BaseExpress;

/**
 * {DriverName} 电子面单
 */
class Driver extends BaseExpress
{
    protected function initialize(array $config = []): void
    {
        parent::initialize($config);
    }

    // 实现面单相关业务方法
}
```

### 4. 验证驱动

```php
// 手动验证（通过驱动注册中心）
use crmeb\services\driver\DriverRegistry;

$drivers = DriverRegistry::scan('express');                              // 全部电子面单驱动
$class   = DriverRegistry::getDriverClass('express', 'your_driver_key'); // 完整类名 driver\express\YourDriver\Driver
```

## 接口规范

电子面单驱动继承自 `BaseExpress`，基类定义了面单打印所需的通用方法，子类可根据实际服务商 SDK 的能力选择性覆写。

### BaseExpress 提供的通用方法

| 方法 | 说明 | 参数 |
|------|------|------|
| `open()` | 开通面单服务 | — |
| `dump()` | 数据导出 | `$data` |
| `temp()` | 获取面单模板 | `$com` |

### 驱动的核心职责

电子面单驱动的核心职责包括：

1. **查询快递公司列表** — 获取支持的快递公司
2. **获取面单模板** — 根据快递公司编码获取面单模板
3. **打印面单** — 提交运单信息，获取面单图片/PDF 数据
4. **面单状态管理** — 查询面单打印状态、取消面单等

## 配置存储

电子面单配置应存储在对应驱动表的 `config_fields` JSON 字段中。

## 配置字段设计建议

电子面单驱动通常需要以下配置：

| 字段 | 类型 | 说明 |
|------|------|------|
| `open` | switch | 功能开关 |
| `type` | radio | 接口类型（只读时标识服务商） |
| `api_key` | text | API密钥 |
| `api_secret` | password | API密钥密码 |
| `endpoint` | text | 接口地址（可选） |

## 参考实现

- [一号通电子面单](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/driver/express/Yihaotong/) — 完整电子面单实现
- [测试电子面单](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/driver/express/TestExpress/) — 测试驱动实现
