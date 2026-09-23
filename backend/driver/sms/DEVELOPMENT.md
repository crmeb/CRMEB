# 短信驱动扩展开发指南

## 概述

短信驱动扩展位于 `driver/sms/{Driver}/` 目录，负责对接各类短信服务商。

系统通过 `crmeb\services\driver\DriverRegistry` 扫描各驱动包的 `plugin.json` 完成驱动注册。**短信驱动状态互斥**，同时只能启用一个驱动。

## 已有驱动

| 驱动目录 | 驱动标识 | 说明 |
|---------|---------|------|
| [Aliyun](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/driver/sms/Aliyun/) | `aliyun` | 阿里云短信（阿里云官方SDK） |
| [Yihaotong](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/driver/sms/Yihaotong/) | `yihaotong` | 一号通短信（CRMEB官方服务） |
| [Tencent](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/driver/sms/Tencent/) | `tencent` | 腾讯云短信 |
| [Chuanglan](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/driver/sms/Chuanglan/) | `chuanglan` | 创蓝短信 |
| [Yunxin](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/driver/sms/yunxin/) | `yunxin` | 云信短信（第三方插件包） |

## 开发步骤

### 1. 创建驱动目录

```bash
mkdir -p driver/sms/{DriverName}
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
    "path": "driver/sms/YourDriver",
    "require": {
        "crmeb": "^6.0",
        "php": "^8.0"
    },
    "driver": {
        "name": "驱动名称",
        "driver_key": "your_driver_key",
        "class_name": "Driver",
        "icon": "icon-xxx",
        "is_builtin": true,
        "sort": 100,
        "capabilities": ["send", "record", "template", "apply", "open", "modify", "info"],
        "config_fields": [
            {
                "field": "xxx_SignName",
                "label": "短信签名",
                "type": "text",
                "required": true,
                "group": "基础配置",
                "tip": "短信签名，需在平台审核通过"
            },
            {
                "field": "xxx_AccessKeyId",
                "label": "AccessKeyId",
                "type": "text",
                "required": true,
                "group": "密钥配置"
            },
            {
                "field": "xxx_AccessKeySecret",
                "label": "AccessKeySecret",
                "type": "password",
                "required": true,
                "group": "密钥配置"
            }
        ],
        "help_doc": {
            "title": "开通方法",
            "link": "https://example.com",
            "steps": [
                "第一步：注册账号",
                "第二步：申请短信签名和模板",
                "第三步：获取 API 密钥",
                "第四步：在系统中填写配置并启用"
            ]
        }
    }
}
```

#### plugin.json 字段说明

| 位置 | 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|------|
| 顶层 | `key` | string | ✅ | 插件标识，与 `driver.driver_key` 保持一致 |
| 顶层 | `name` 等 | string | ✅ | 插件元信息（description/version/author，Installer 生命周期展示用） |
| 顶层 | `type` | string | ✅ | 固定为 `driver` |
| 顶层 | `path` | string | ✅ | 驱动包相对路径 |
| 顶层 | `require` | object | ❌ | 依赖声明（crmeb/php 版本） |
| `driver` | `name` | string | ✅ | 驱动显示名称 |
| `driver` | `driver_key` | string | ✅ | 驱动唯一标识 |
| `driver` | `class_name` | string | ❌ | 驱动类名，默认 `Driver` |
| `driver` | `icon` | string | ❌ | 图标类名 |
| `driver` | `is_builtin` | bool | ❌ | 是否内置驱动 |
| `driver` | `sort` | int | ❌ | 排序权重 |
| `driver` | `capabilities` | array | ❌ | 能力声明（`send` 发送短信必须；`record` 发送记录查询；`template` 模板管理；`apply` 模板申请；`open` 开通服务；`modify` 修改签名；`info` 账号信息） |
| `driver` | `config_fields` | array | ❌ | 配置字段定义（后台动态渲染表单） |
| `driver` | `help_doc` | object | ❌ | 帮助文档 |

### 3. 创建 Driver.php

```php
<?php
namespace driver\sms\{DriverName};

use crmeb\services\sms\BaseSms;

/**
 * Class Driver
 * @package driver\sms\{DriverName}
 */
class Driver extends BaseSms
{
    protected $SignName = '';
    protected $AccessKeyId = '';
    protected $AccessKeySecret = '';

    /**
     * 初始化配置
     * @param array $config
     */
    protected function initialize(array $config = []): void
    {
        parent::initialize($config);
        $this->SignName = $config['xxx_SignName'] ?? '';
        $this->AccessKeyId = $config['xxx_AccessKeyId'] ?? '';
        $this->AccessKeySecret = $config['xxx_AccessKeySecret'] ?? '';
    }

    /**
     * 发送短信
     * @param string $phone 手机号
     * @param string $templateId 模板ID
     * @param array $data 模板变量
     * @return array|bool
     */
    public function send(string $phone, string $templateId, array $data = [])
    {
        // 实现短信发送逻辑
    }

    // 可选实现以下方法
    public function open() {}
    public function modify(string $phone, string $code, ?string $sign = null) {}
    public function info() {}
    public function temps(int $page = 0, int $limit = 10, int $type = 1) {}
    public function apply(string $title, string $content, int $type) {}
    public function applys(int $tempType, int $page, int $limit) {}
    public function record($record_id) {}
}
```

### 4. 验证驱动

```php
// 手动验证（通过驱动注册中心）
use crmeb\services\driver\DriverRegistry;

$drivers = DriverRegistry::scan('sms');                              // 全部短信驱动
$class   = DriverRegistry::getDriverClass('sms', 'your_driver_key');  // 完整类名 driver\sms\YourDriver\Driver
```

## 接口规范

### 必须实现的方法

| 方法 | 说明 | 参数 | 返回值 |
|------|------|------|--------|
| `send()` | 发送短信 | `$phone`, `$templateId`, `$data` | `array\|bool` |

### 可选实现的方法

| 方法 | 说明 |
|------|------|
| `open()` | 开通短信服务 |
| `modify()` | 修改签名 |
| `info()` | 获取账号信息 |
| `temps()` | 获取短信模板列表 |
| `apply()` | 申请短信模板 |
| `applys()` | 获取模板申请记录 |
| `record()` | 获取发送记录 |

## 配置存储

短信驱动配置存储在 `eb_sms_driver` 表的 `config_fields` JSON 字段中，**禁止写入 `system_config` 表**。

## 最佳实践

### 配置字段命名规范

```php
'config_fields' => [
    ['field' => '{driver_key}_SignName',     'label' => '短信签名',   'type' => 'text'],
    ['field' => '{driver_key}_AccessKeyId',   'label' => 'AccessKey', 'type' => 'text'],
    ['field' => '{driver_key}_AccessKeySecret', 'label' => 'Secret', 'type' => 'password'],
],
```

### 错误处理

```php
if (empty($phone)) {
    return $this->setError('电话号码不能为空');
}

try {
    // 调用第三方 API
    $result = $api->send($phone, ...);
    if (!$result) {
        return $this->setError('发送失败');
    }
    return ['id' => $result['requestId'], 'content' => json_encode($data)];
} catch (\Exception $e) {
    return $this->setError($e->getMessage());
}
```

## 参考实现

- [阿里云短信](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/driver/sms/Aliyun/) — 完整短信发送实现
- [一号通短信](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/driver/sms/Yihaotong/) — 全功能实现（含模板管理、开通服务）
