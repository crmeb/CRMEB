# 支付驱动扩展开发指南

## 概述

支付驱动扩展位于 `driver/pay/{Driver}/` 目录，负责对接各种第三方支付平台（支付宝、微信支付、通联支付等）。

系统通过 `crmeb\services\driver\DriverRegistry` 扫描各驱动包的 `plugin.json` 完成驱动注册，无需手动配置。

## 已有驱动

| 驱动目录 | 驱动标识 | 支付平台 | 说明 |
|---------|---------|---------|------|
| [AliPay](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/driver/pay/AliPay/) | `ali_pay` | alipay | 支付宝支付（支付宝官方SDK） |
| [WechatPay](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/driver/pay/WechatPay/) | `wechat_pay` | wechat | 微信支付V2（APIv2） |
| [V3WechatPay](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/driver/pay/V3WechatPay/) | `v3_wechat_pay` | wechat | 微信支付V3（APIv3，含商家转账到零钱） |
| [AllinPay](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/driver/pay/AllinPay/) | `allin_pay` | allinpay | 通联支付 |

## 开发步骤

### 1. 创建驱动目录

```bash
mkdir -p driver/pay/{DriverName}
```

### 2. 创建 plugin.json

```json
{
    "key": "your_driver_key",
    "name": "支付名称",
    "description": "驱动功能简述",
    "version": "1.0.0",
    "author": "CRMEB",
    "type": "driver",
    "path": "driver/pay/YourDriver",
    "require": {
        "crmeb": "^6.0",
        "php": "^8.0"
    },
    "driver": {
        "name": "支付名称",
        "channel_key": "channel_key",
        "driver_key": "your_driver_key",
        "platform": "platform",
        "icon": "icon-xxx",
        "is_builtin": true,
        "sort": 100,
        "capabilities": ["pay", "refund", "notify"],
        "required_credentials": {
            "wechat_mini": ["openid"],
            "wechat_mp": ["openid"]
        },
        "pay_type_map": {
            "wechat_mini": "JSAPI",
            "wechat_mp": "JSAPI",
            "h5": "h5",
            "app": "APP",
            "pc": "NATIVE"
        },
        "pay_status_type": {
            "default": "wechat_pay",
            "h5": "wechat_h5_pay",
            "pc": "wechat_pc_pay"
        },
        "supported_platforms": {
            "wechat_mini": "微信小程序",
            "wechat_mp": "微信公众号",
            "h5": "H5浏览器",
            "app": "APP",
            "pc": "PC端"
        },
        "config_fields": [
            {"field": "xxx_appid", "label": "AppID", "type": "text", "required": true, "group": "商户"},
            {"field": "xxx_key", "label": "API密钥", "type": "password", "required": true, "group": "商户"},
            {"field": "xxx_cert", "label": "支付证书", "type": "upload", "group": "证书"}
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
| `channel_key` | string | ✅ | 支付渠道标识 |
| `driver_key` | string | ✅ | 驱动唯一标识 |
| `platform` | string | ✅ | 支付平台（`alipay`/`wechat`/`allinpay`） |
| `icon` | string | ❌ | 图标类名 |
| `is_builtin` | bool | ❌ | 是否内置驱动 |
| `sort` | int | ❌ | 排序权重 |
| `capabilities` | array | ❌ | 能力声明（`pay`, `refund`, `notify`） |
| `required_credentials` | object | ❌ | 各平台需要的前端凭证 |
| `pay_type_map` | object | ❌ | 各客户端对应的微信支付类型映射 |
| `pay_status_type` | object | ❌ | 支付状态返回类型（前端跳转方式） |
| `default_options` | array | ❌ | 发起支付时自动传入的配置项 |
| `supported_platforms` | object | ❌ | 支持的客户端平台 |
| `config_fields` | array | ❌ | 配置字段定义 |
| `help_doc` | object | ❌ | 帮助文档 |

> 顶层字段（`key`/`name`/`description`/`version`/`author`/`type`/`path`/`require`）与通用 driver 插件一致，参见 [sms/DEVELOPMENT.md](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/driver/sms/DEVELOPMENT.md) 的字段说明表。

### 3. 创建 Driver.php

```php
<?php
namespace driver\pay\{DriverName};

use crmeb\services\pay\BasePay;
use crmeb\services\pay\PayInterface;

/**
 * {DriverName} 支付
 */
class Driver extends BasePay implements PayInterface
{
    protected function initialize(array $config): void
    {
        // 初始化配置（appid、密钥等）
    }

    /**
     * 创建订单发起支付
     * @param string $orderId 订单号
     * @param string $totalFee 金额（元）
     * @param string $attach 附加数据
     * @param string $body 商品描述
     * @param string $detail 商品详情
     * @param array $options 附加选项（platform, openid, quitUrl, returnUrl 等）
     * @return mixed 支付响应
     */
    public function create(string $orderId, string $totalFee, string $attach, string $body, string $detail, array $options = [])
    {
        // 实现支付创建逻辑
    }

    /**
     * 发起退款
     * @param string $outTradeNo 商户订单号
     * @param array $options 退款选项（totalAmount, refund_id 等）
     * @return mixed 退款响应
     */
    public function refund(string $outTradeNo, array $options = [])
    {
        // 实现退款逻辑
    }

    /**
     * 查询退款状态
     * @param string $outTradeNo 商户订单号
     * @param string $outRequestNo 退款请求号
     * @param array $other 其他参数
     * @return mixed 查询结果
     */
    public function queryRefund(string $outTradeNo, string $outRequestNo, array $other = [])
    {
        // 实现退款查询逻辑
    }

    /**
     * 支付异步回调处理
     * @param string $type 回调类型
     * @return mixed 处理结果
     */
    public function handleNotify(string $type = '')
    {
        // 实现回调处理逻辑
    }
}
```

### 4. 验证驱动

系统自动扫描，无需手动注册。在后台「设置 → 支付方式」中即可看到新驱动。

```php
// 手动验证（通过驱动注册中心）
use crmeb\services\driver\DriverRegistry;

$drivers = DriverRegistry::scan('pay');                              // 全部支付驱动
$class   = DriverRegistry::getDriverClass('pay', 'your_driver_key');  // 完整类名 driver\pay\YourDriver\Driver
```

## 接口规范

### 必须实现的方法

| 方法 | 说明 | 关键参数 |
|------|------|---------|
| `create()` | 创建订单发起支付 | `$orderId`, `$totalFee`, `$options['platform']` |
| `refund()` | 发起退款 | `$outTradeNo`, `$options['totalAmount']`, `$options['refund_id']` |
| `queryRefund()` | 查询退款状态 | `$outTradeNo`, `$outRequestNo` |
| `handleNotify()` | 支付异步回调处理 | `$type` |

### 可选实现的方法

| 方法 | 说明 | 适用场景 |
|------|------|---------|
| `merchantPay()` | 企业支付到零钱 | 微信支付V2旧版商家转账 |

### 异步回调处理

回调处理是整个支付驱动的核心环节，必须按以下流程实现：

```php
public function handleNotify(string $type = '')
{
    // 1. 接收回调数据
    $data = file_get_contents('php://input');
    
    // 2. 验证签名
    if (!$this->verifySign($data)) {
        return 'fail';
    }
    
    // 3. 提取订单号
    $orderNo = $data['out_trade_no'];
    
    // 4. 触发订单支付成功事件
    event('OrderPaySuccess', [$orderNo]);
    
    // 5. 返回成功响应
    return 'success';
}
```

## 配置存储

支付驱动配置存储在 `eb_pay_channel` 表的 `config_fields` JSON 字段中，**禁止写入 `system_config` 表**。

## 参考实现

- [支付宝支付 AliPay](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/driver/pay/AliPay/) — 标准支付驱动实现
- [微信支付V3](file:///Users/xurongyao/web/crmeb_bz/crumb-bz-v6.0/crmebbz/backend/driver/pay/V3WechatPay/) — 含多平台映射、服务商模式
