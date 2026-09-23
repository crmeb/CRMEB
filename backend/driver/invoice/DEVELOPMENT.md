# 发票驱动扩展开发指南

## 概述

发票驱动扩展位于 `driver/invoice/{Driver}/` 目录，负责对接各类发票服务（一号通等），用于在线开具和管理发票。

系统通过 `crmeb\services\driver\DriverRegistry` 扫描各驱动包的 `plugin.json` 完成驱动注册。

## 已有驱动

| 驱动目录 | 驱动标识 | 说明 |
|---------|---------|------|
| [Yihaotong](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/driver/invoice/Yihaotong/) | `yihaotong` | 一号通发票服务 |

## 开发步骤

### 1. 创建驱动目录

```bash
mkdir -p driver/invoice/{DriverName}
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
    "path": "driver/invoice/YourDriver",
    "driver": {
        "name": "发票驱动名称",
        "driver_key": "your_driver_key",
        "class_name": "Driver",
        "icon": "icon-xxx",
        "is_builtin": true,
        "sort": 100,
        "config_fields": [
            {
                "field": "invoice_switch",
                "label": "发票服务开关",
                "type": "switch",
                "group": "基础",
                "tip": "开启后在订单详情中可使用发票功能"
            },
            {
                "field": "app_id",
                "label": "AppID",
                "type": "text",
                "required": true,
                "group": "账号"
            },
            {
                "field": "app_secret",
                "label": "AppSecret",
                "type": "password",
                "required": true,
                "group": "账号"
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
| `config_fields` | array | ❌ | 配置字段定义（后台动态渲染表单；发票服务开关为 switch 字段，控制功能启用） |
| `help_doc` | object | ❌ | 帮助文档（`{title, steps, link}`） |

> 顶层字段（`key`/`name`/`description`/`version`/`author`/`type`/`path`/`require`）为各驱动类型通用结构，完整说明见[短信驱动开发指南](../sms/DEVELOPMENT.md#pluginjson-字段说明)。

### 3. 创建 Driver.php

```php
<?php
namespace driver\invoice\{DriverName};

use crmeb\services\invoice\BaseInvoice;

/**
 * {DriverName} 发票服务
 */
class Driver extends BaseInvoice
{
    protected function initialize(array $config = []): void
    {
        parent::initialize($config);
    }

    /**
     * 获取发票开具页面iframe地址
     * @param array $params
     * @return array
     */
    public function invoiceIssuanceUrl(array $params = [])
    {
        return $this->accessToken->httpRequest(self::INVOICE_ISSUANCE_URL, $params);
    }

    /**
     * 下载发票
     * @param string $invoiceNum
     * @return array
     */
    public function downloadInvoice(string $invoiceNum = '')
    {
        return $this->accessToken->httpRequest(self::DOWNLOAD_INVOICE . '/' . $invoiceNum, [], 'GET');
    }

    // ... 其他方法
}
```

### 4. 验证驱动

```php
// 手动验证（通过驱动注册中心）
use crmeb\services\driver\DriverRegistry;

$drivers = DriverRegistry::scan('invoice');                              // 全部发票驱动
$class   = DriverRegistry::getDriverClass('invoice', 'your_driver_key'); // 完整类名 driver\invoice\YourDriver\Driver
```

## 接口规范

### 可选实现的方法

| 方法 | 说明 | 参数 | 返回值 |
|------|------|------|--------|
| `invoiceIssuanceUrl()` | 获取发票开具页面地址 | `$params` | `array` |
| `downloadInvoice()` | 下载发票 | `$invoiceNum` | `array` |
| `invoiceInfo()` | 查看发票详情 | `$invoiceNum` | `array` |
| `category()` | 获取商品类目 | `$params` | `array` |
| `invoiceIssuance()` | 开具发票 | `$unique`, `$params` | `array` |
| `applyRedInvoice()` | 申请红字发票 | `$params` | `array` |
| `redInvoiceIssuance()` | 开具负数发票 | `$params` | `array` |

### 发票开具流程

一个完整的发票开具流程通常包含以下步骤：

```php
// 1. 获取商品类目
$categories = $driver->category();

// 2. 获取发票开具页面（iframe方式）
$url = $driver->invoiceIssuanceUrl([
    'order_id' => $orderId,
    'amount' => $totalAmount,
    'buyer_name' => $buyerName,
]);

// 3. 提交开具请求
$result = $driver->invoiceIssuance($unique, [
    'invoice_type' => 'normal',
    'amount' => $totalAmount,
    'items' => $orderItems,
]);

// 4. 查询发票详情
$info = $driver->invoiceInfo($invoiceNum);

// 5. 下载发票PDF
$file = $driver->downloadInvoice($invoiceNum);
```

## 参考实现

- [一号通发票服务](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/driver/invoice/Yihaotong/) — 完整发票驱动实现
