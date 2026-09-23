# 物流查询驱动扩展开发指南

## 概述

物流查询驱动扩展位于 `driver/logistics/{Driver}/` 目录，负责对接各类物流轨迹查询服务（阿里云物流API、一号通等），用于跟踪订单物流状态。

系统通过 `crmeb\services\driver\DriverRegistry` 扫描各驱动包的 `plugin.json` 完成驱动注册。

## 已有驱动

| 驱动目录 | 驱动标识 | 说明 |
|---------|---------|------|
| [AliyunExpress](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/driver/logistics/AliyunExpress/) | `aliyun_express` | 阿里云物流查询API |
| [Yihaotong](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/driver/logistics/Yihaotong/) | `yihaotong` | 一号通物流查询 |

## 开发步骤

### 1. 创建驱动目录

```bash
mkdir -p driver/logistics/{DriverName}
```

### 2. 创建 plugin.json

```json
{
    "key": "your_driver_key",
    "name": "物流驱动名称",
    "description": "驱动功能简述",
    "version": "1.0.0",
    "author": "CRMEB",
    "type": "driver",
    "path": "driver/logistics/YourDriver",
    "require": {
        "crmeb": "^6.0",
        "php": "^8.0"
    },
    "driver": {
        "name": "物流驱动名称",
        "driver_key": "your_driver_key",
        "class_name": "Driver",
        "icon": "icon-xxx",
        "is_builtin": true,
        "sort": 100,
        "capabilities": ["query"],
        "config_fields": [
            {
                "field": "app_code",
                "label": "AppCode",
                "type": "text",
                "required": true,
                "group": "基础配置",
                "tip": "API 访问密钥"
            }
        ],
        "help_doc": [
            {"title": "物流查询服务说明", "url": "https://docs.example.com"}
        ]
    }
}
```

#### plugin.json driver 子对象字段说明

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| `name` | string | ✅ | 驱动名称 |
| `driver_key` | string | ✅ | 驱动唯一标识 |
| `class_name` | string | ❌ | 驱动类名，默认 `Driver` |
| `icon` | string | ❌ | 图标类名 |
| `is_builtin` | bool | ❌ | 是否内置 |
| `sort` | int | ❌ | 排序 |
| `capabilities` | array | ❌ | 能力声明（`query` 物流查询） |
| `config_fields` | array | ❌ | 配置字段 |
| `help_doc` | array | ❌ | 帮助文档 |

> 顶层字段（`key`/`name`/`description`/`version`/`author`/`type`/`path`/`require`）与通用 driver 插件一致，参见 [sms/DEVELOPMENT.md](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/driver/sms/DEVELOPMENT.md) 的字段说明表。

### 3. 创建 Driver.php

```php
<?php
namespace driver\logistics\{DriverName};

use crmeb\services\logistics\BaseExpress;
use crmeb\utils\Http;

/**
 * {DriverName} 物流查询
 */
class Driver extends BaseExpress
{
    protected static array $api = [
        'query' => 'https://api.example.com/query',
    ];

    /**
     * 查询物流轨迹
     * @param string $no 物流单号
     * @param string $type 快递公司编码
     * @param string $appCode API密钥
     * @return bool|mixed
     */
    public function query(string $no = '', string $type = '', string $appCode = '')
    {
        if (!$appCode) {
            return false;
        }
        // 调用物流API
        $res = Http::getRequest(self::$api['query'], compact('no', 'type'), [
            'Authorization:APPCODE ' . $appCode,
        ]);
        return json_decode($res, true) ?: false;
    }

    // 可选方法
    public function open() {}
    public function dump($data) {}
    public function temp(string $com) {}
}
```

### 4. 验证驱动

```php
use crmeb\services\driver\DriverRegistry;

$drivers = DriverRegistry::scan('logistics');                              // 全部物流驱动
$class   = DriverRegistry::getDriverClass('logistics', 'your_driver_key'); // 完整类名 driver\logistics\YourDriver\Driver
```

## 接口规范

### 必须实现的方法

| 方法 | 说明 | 参数 | 返回值 |
|------|------|------|--------|
| `query()` | 查询物流轨迹 | `$no`, `$type`, `$appCode` | `bool\|mixed` |

### 可选方法

| 方法 | 说明 |
|------|------|
| `open()` | 开通物流服务 |
| `dump()` | 数据导出 |
| `temp()` | 获取模板 |

### 响应格式

query() 方法应返回包含以下信息的数组：

```php
[
    'status' => 'success',  // 查询状态
    'data' => [
        [
            'time' => '2025-01-01 10:00:00',  // 节点时间
            'desc' => '快件已到达【杭州分拨中心】',  // 节点描述
            'location' => '杭州市',  // 节点位置（可选）
        ],
    ],
    'exp_name' => '顺丰快递',  // 快递公司名称
    'exp_no' => 'SF1234567890',  // 运单号
]
```

如果查询失败，应返回 `false` 或包含 `error` 字段的数组。

## 配置存储

物流查询配置应存储在对应驱动表的 `config_fields` JSON 字段中，**禁止写入 `system_config` 表**。

## 参考实现

- [阿里云物流查询](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/driver/logistics/AliyunExpress/) — 阿里云市场API方式
- [一号通物流查询](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/driver/logistics/Yihaotong/) — 一号通API方式
