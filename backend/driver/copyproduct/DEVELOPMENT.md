# 商品采集驱动扩展开发指南

## 概述

商品采集驱动扩展位于 `driver/copyproduct/{Driver}/` 目录，负责对接各类商品采集服务（99API、一号通等），用于从第三方电商平台采集商品信息。

系统通过 `crmeb\services\driver\DriverRegistry` 扫描各驱动包的 `plugin.json` 完成驱动注册。

## 已有驱动

| 驱动目录 | 驱动标识 | 说明 |
|---------|---------|------|
| [Copy99api](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/driver/copyproduct/Copy99api/) | `copy99api` | 99API平台采集（支持淘宝、京东、拼多多等多平台） |
| [Yihaotong](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/driver/copyproduct/Yihaotong/) | `yihaotong` | 一号通采集（CRMEB官方服务） |

## 开发步骤

### 1. 创建驱动目录

```bash
mkdir -p driver/copyproduct/{DriverName}
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
    "path": "driver/copyproduct/YourDriver",
    "driver": {
        "name": "采集驱动名称",
        "driver_key": "your_driver_key",
        "class_name": "Driver",
        "icon": "icon-xxx",
        "sort": 100,
        "description": "驱动描述",
        "capabilities": {
            "open": true,
            "multi_platform": true,
            "cache": true
        },
        "platforms": [
            {"name": "淘宝",   "key": "taobao", "domain": "taobao.com",     "icon": "icon-taobao"},
            {"name": "天猫",   "key": "tmall",  "domain": "tmall.com",      "icon": "icon-tmall"},
            {"name": "京东",   "key": "jd",     "domain": "jd.com",         "icon": "icon-jd"},
            {"name": "拼多多", "key": "pdd",    "domain": "pinduoduo.com",  "icon": "icon-pdd"}
        ],
        "config_fields": [
            {
                "field": "apikey",
                "label": "API Key",
                "type": "password",
                "required": true,
                "group": "密钥配置",
                "description": "API Key获取方式",
                "default": ""
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
| `sort` | int | ❌ | 排序权重 |
| `description` | string | ❌ | 驱动描述（采集能力概览） |
| `capabilities` | object | ❌ | 能力声明（`open` 是否需要开通；`multi_platform` 是否支持多平台；`cache` 是否支持缓存） |
| `platforms` | array | ❌ | 支持的电商平台列表（`[{name, key, domain, icon}]`） |
| `config_fields` | array | ❌ | 配置字段定义（后台动态渲染表单） |
| `help_doc` | object | ❌ | 帮助文档（`{title, steps, link}`） |

> 顶层字段（`key`/`name`/`description`/`version`/`author`/`type`/`path`/`require`）为各驱动类型通用结构，完整说明见[短信驱动开发指南](../sms/DEVELOPMENT.md#pluginjson-字段说明)。

### 3. 创建 Driver.php

```php
<?php
namespace driver\copyproduct\{DriverName};

use crmeb\services\copyproduct\CopyProduct;

/**
 * {DriverName} 商品采集
 */
class Driver extends CopyProduct
{
    /**
     * 采集商品
     * @param string $url 商品链接
     * @param array $options 附加选项
     * @return array ['status' => true/false, 'msg' => '提示信息', 'data' => [...]]
     */
    public function copy($url, array $options = [])
    {
        // 实现商品采集逻辑
        // 1. 解析URL获取商品ID
        // 2. 调用采集API
        // 3. 返回结构化商品数据
    }

    /**
     * 获取支持的平台列表
     * @return array
     */
    public function getPlatforms()
    {
        return [
            ['name' => '淘宝', 'key' => 'taobao', 'domain' => 'taobao.com', 'icon' => 'icon-taobao'],
            ['name' => '天猫', 'key' => 'tmall', 'domain' => 'tmall.com', 'icon' => 'icon-tmall'],
            ['name' => '京东', 'key' => 'jd', 'domain' => 'jd.com', 'icon' => 'icon-jd'],
            ['name' => '拼多多', 'key' => 'pdd', 'domain' => 'pinduoduo.com', 'icon' => 'icon-pdd'],
        ];
    }

    /**
     * 获取驱动能力描述
     * @return array
     */
    public function getCapabilities()
    {
        return [
            'open' => false,           // 是否需要开通
            'multi_platform' => true,   // 是否支持多平台
            'cache' => true,           // 是否支持缓存
        ];
    }
}
```

### 4. 验证驱动

```php
// 手动验证（通过驱动注册中心）
use crmeb\services\driver\DriverRegistry;

$drivers = DriverRegistry::scan('copyproduct');                              // 全部采集驱动
$class   = DriverRegistry::getDriverClass('copyproduct', 'your_driver_key'); // 完整类名 driver\copyproduct\YourDriver\Driver
```

## 接口规范

### 必须实现的方法

| 方法 | 说明 | 参数 | 返回值 |
|------|------|------|--------|
| `copy()` | 采集商品信息 | `$url`, `$options` | `array` |

### 可选实现的方法

| 方法 | 说明 | 返回值 |
|------|------|--------|
| `getPlatforms()` | 获取支持的平台列表 | `array` |
| `getCapabilities()` | 获取驱动能力描述 | `array` |

### copy() 返回格式

采集成功后应返回以下结构化数据：

```php
[
    'status' => true,
    'data' => [
        'store_name' => '商品名称',
        'store_info' => '商品描述',
        'image' => 'https://img.example.com/1.jpg',         // 主图
        'slider_image' => ['https://img.example.com/1.jpg'], // 轮播图
        'price' => 99.00,             // 价格
        'ot_price' => 129.00,         // 原价
        'cate_id' => '分类ID',
        'unit_name' => '件',
        'temp_id' => '运费模板ID',
        'spec_type' => 0,             // 规格类型：0-单规格，1-多规格
        'attr' => [],                 // SKU数据
        'description' => '<p>商品详情HTML</p>',
        // ... 其他商品字段
    ],
]
```

### 平台识别

采集驱动通常通过商品链接中的域名自动识别平台：

```php
public function copy($url, array $options = [])
{
    $platform = $this->detectPlatform($url);
    if (!$platform) {
        return ['status' => false, 'msg' => '不支持的电商平台'];
    }
    // 根据平台调用对应的采集API
}
```

## 参考实现

- [99API 商品采集](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/driver/copyproduct/Copy99api/) — 多平台采集实现
- [一号通商品采集](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/driver/copyproduct/Yihaotong/) — 一号通采集实现
