# 上传驱动扩展开发指南

## 概述

上传驱动扩展位于 `driver/upload/{Driver}/` 目录，负责对接多种云存储服务（本地存储、阿里云OSS、腾讯云COS、七牛云等）。

系统通过 `crmeb\services\driver\DriverRegistry` 扫描各驱动包的 `plugin.json` 完成驱动注册。

## 已有驱动

| 驱动目录 | 驱动标识 | 说明 |
|---------|---------|------|
| [Local](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/driver/upload/Local/) | `local` | 本地存储（开箱即用） |
| [Oss](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/driver/upload/Oss/) | `oss` | 阿里云OSS |
| [Cos](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/driver/upload/Cos/) | `cos` | 腾讯云COS |
| [Qiniu](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/driver/upload/Qiniu/) | `qiniu` | 七牛云 |
| [Obs](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/driver/upload/Obs/) | `obs` | 华为云OBS |
| [Jdoss](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/driver/upload/Jdoss/) | `jdoss` | 京东云OSS |
| [Tyoss](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/driver/upload/Tyoss/) | `tyoss` | 天翼云OSS |

## 开发步骤

### 1. 创建驱动目录

```bash
mkdir -p driver/upload/{DriverName}
```

### 2. 创建 plugin.json

```json
{
    "key": "your_driver_key",
    "name": "存储名称",
    "description": "驱动功能简述",
    "version": "1.0.0",
    "author": "CRMEB",
    "type": "driver",
    "path": "driver/upload/YourDriver",
    "require": {
        "crmeb": "^6.0",
        "php": "^8.0"
    },
    "driver": {
        "name": "存储名称",
        "driver_key": "your_driver_key",
        "class_name": "Driver",
        "icon": "icon-xxx",
        "is_builtin": true,
        "sort": 100,
        "domain_template": "https://{bucket}.oss-cn-hangzhou.aliyuncs.com",
        "capabilities": ["upload", "delete", "stream", "thumb", "water"],
        "config_fields": [
            {"field": "access_key", "label": "AccessKey", "type": "text", "required": true, "group": "密钥"},
            {"field": "secret_key", "label": "SecretKey", "type": "password", "required": true, "group": "密钥"},
            {"field": "bucket", "label": "存储空间", "type": "text", "required": true, "group": "存储"}
        ],
        "bucket_fields": [
            {"field": "region", "label": "地域", "type": "text", "group": "存储"}
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

| 位置 | 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|------|
| `driver` | `name` | string | ✅ | 驱动名称 |
| `driver` | `driver_key` | string | ✅ | 驱动唯一标识 |
| `driver` | `class_name` | string | ❌ | 驱动类名，默认 `Driver` |
| `driver` | `icon` | string | ❌ | 图标类名 |
| `driver` | `domain_template` | string | ❌ | 域名模板（用于生成访问地址） |
| `driver` | `is_builtin` | bool | ❌ | 是否内置 |
| `driver` | `sort` | int | ❌ | 排序 |
| `driver` | `capabilities` | array | ❌ | 能力声明（`upload` 上传 / `delete` 删除 / `stream` 流上传 / `thumb` 缩略图 / `water` 水印） |
| `driver` | `config_fields` | array | ❌ | 配置字段 |
| `driver` | `bucket_fields` | array | ❌ | 存储空间配置字段 |
| `driver` | `help_doc` | object | ❌ | 帮助文档 |

> 顶层字段（`key`/`name`/`description`/`version`/`author`/`type`/`path`/`require`）与通用 driver 插件一致，参见 [sms/DEVELOPMENT.md](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/driver/sms/DEVELOPMENT.md) 的字段说明表。

### 3. 创建 Driver.php

```php
<?php
namespace driver\upload\{DriverName};

use crmeb\services\upload\BaseUpload;

/**
 * {DriverName} 上传
 * Class Driver
 * @package driver\upload\{DriverName}
 */
class Driver extends BaseUpload
{
    protected function initialize(array $config): void
    {
        parent::initialize($config);
        // 初始化客户端
    }

    /**
     * 文件上传
     * @param string $file 表单字段名
     * @param bool $realName 是否保留原始文件名
     * @return array|bool|\StdClass
     */
    public function move(string $file = 'file', $realName = false)
    {
        // 实现文件上传逻辑
    }

    /**
     * 删除文件
     * @param string $filePath
     * @return bool
     */
    public function delete(string $filePath): bool
    {
        // 实现文件删除逻辑
    }

    /**
     * 获取文件路径
     * @param string $key
     * @return string
     */
    public function getFilePath(string $key): string
    {
        // 返回文件完整访问URL
    }
}
```

### 4. 验证驱动

```php
use crmeb\services\driver\DriverRegistry;

$drivers = DriverRegistry::scan('upload');                              // 全部上传驱动
$class   = DriverRegistry::getDriverClass('upload', 'your_driver_key'); // 完整类名 driver\upload\YourDriver\Driver
```

## 接口规范

### 必须实现的方法

| 方法 | 说明 | 返回值 |
|------|------|--------|
| `move()` | 文件上传（form表单） | `\StdClass` |
| `delete()` | 删除文件 | `bool` |
| `getFilePath()` | 获取文件访问路径 | `string` |

### 可选实现的方法

| 方法 | 说明 | 适用场景 |
|------|------|---------|
| `stream()` | 文件流上传 | 远程下载文件 |
| `down()` | 文件流下载保存 | 保存远程资源 |
| `thumb()` | 生成缩略图 | 本地存储 |
| `water()` | 添加水印 | 本地存储 |
| `image()` | 图片水印 | 本地存储 |
| `text()` | 文字水印 | 本地存储 |
| `upload()` | 文件路径上传 | 服务端文件操作 |
| `listbuckets()` | 获取存储空间列表 | 云存储 |
| `createBucket()` | 创建存储空间 | 云存储 |
| `setBucketCors()` | 设置跨域规则 | 云存储 |
| `bindDomian()` | 绑定域名 | 云存储 |
| `getDomian()` | 获取域名列表 | 云存储 |
| `getRegion()` | 获取区域列表 | 云存储 |
| `getTempKeys()` | 获取临时密钥（前端直传） | 云存储 |
| `to()` | 设置上传路径 | 链式调用 |
| `validate()` | 设置验证规则 | 链式调用 |

### 链式调用模式

上传驱动支持链式调用，新增驱动需支持：

```php
$upload = app()->make(\crmeb\services\upload\UploadService::class);
$result = $upload->to('product/2025/01')
    ->validate(['fileExt' => ['jpg','png'], 'filesize' => 2097152])
    ->move('file');
```

## 云存储客户端封装

对于云存储，建议在 `extend/` 目录中封装 SDK 客户端：

```
driver/upload/{DriverName}/
├── Driver.php
├── plugin.json
└── extend/
    └── {driver}/
        ├── Client.php       # 客户端封装
        ├── Signature.php    # 签名工具（可选）
        └── ...              # 其他辅助类
```

## 参考实现

- [本地存储 Local](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/driver/upload/Local/) — 完整上传/缩略图/水印实现
- [腾讯云COS](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/driver/upload/Cos/) — 云存储实现参考（含客户端封装）
