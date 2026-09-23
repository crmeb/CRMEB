# 第三方驱动底座（driver/）

## 一、定位

`driver/` 是三层架构的最底层，只封装**与第三方服务的通信细节**（协议、鉴权、SDK 适配），不承载任何业务决策：

```
modules/（业务扩展层）→ app/（统一管理中台）→ crmeb/（核心底层）→ driver/（第三方驱动底座）
```

- **只能被 `crmeb/services` 调用**，不可反向引用 `app/`、`modules/`
- 驱动包内禁止访问数据库、禁止调用业务 Services，配置由上层注入（`initialize(array $config)`）
- 每个驱动包 = 一个自包含目录（`Driver.php` + `plugin.json`，可选 `Installer.php` + `extend/` SDK 子目录）

## 二、驱动类型总览

| 类型 | 目录 | 职责 | 基类 | 开发指南 |
|------|------|------|------|---------|
| 短信 | `driver/sms/` | 短信发送/模板/记录 | `crmeb\services\sms\BaseSms` | [sms/DEVELOPMENT.md](sms/DEVELOPMENT.md) |
| 支付 | `driver/pay/` | 收款/退款/对账 | `crmeb\services\pay\BasePay` + `PayInterface` | [pay/DEVELOPMENT.md](pay/DEVELOPMENT.md) |
| 上传存储 | `driver/upload/` | 文件存储/域名/区域 | `crmeb\services\upload\BaseUpload` | [upload/DEVELOPMENT.md](upload/DEVELOPMENT.md) |
| 邮件 | `driver/email/` | 邮件发送 | `crmeb\services\email\BaseEmail` + `EmailInterface` | [email/DEVELOPMENT.md](email/DEVELOPMENT.md) |
| 物流查询 | `driver/logistics/` | 快递轨迹查询 | `crmeb\services\logistics\BaseExpress` | [logistics/DEVELOPMENT.md](logistics/DEVELOPMENT.md) |
| 电子面单 | `driver/express/` | 面单打印 | `crmeb\services\express\BaseExpress` | [express/DEVELOPMENT.md](express/DEVELOPMENT.md) |
| 发票 | `driver/invoice/` | 发票开具/下载 | `crmeb\services\invoice\BaseInvoice` | [invoice/DEVELOPMENT.md](invoice/DEVELOPMENT.md) |
| 机器人通知 | `driver/bot/` | 群机器人消息推送 | `crmeb\services\bot\BaseBot` | [bot/DEVELOPMENT.md](bot/DEVELOPMENT.md) |
| 打印 | `driver/printer/` | 云打印机打印 | `crmeb\services\printer\BasePrinter` | [printer/DEVELOPMENT.md](printer/DEVELOPMENT.md) |
| 商品采集 | `driver/copyproduct/` | 第三方平台商品采集 | `crmeb\services\copyproduct\CopyProduct` | [copyproduct/DEVELOPMENT.md](copyproduct/DEVELOPMENT.md) |

## 三、统一扩展框架

### 3.1 驱动注册中心

所有驱动类型通过 `crmeb\services\driver\DriverRegistry` 统一注册（收口原先分散在各 Services 中的重复扫描逻辑）：

```php
use crmeb\services\driver\DriverRegistry;

$drivers = DriverRegistry::scan('sms');                    // [driver_key => 驱动信息数组]
$driver  = DriverRegistry::get('sms', 'aliyun');           // 单驱动信息，不存在返回 null
$class   = DriverRegistry::getDriverClass('sms', 'aliyun'); // driver\sms\Aliyun\Driver
```

`scan()` 返回的驱动信息数组包含：`plugin.json` 的 `driver` 子对象全量字段 + 归一化默认值 + `dir_name`（目录名）+ `namespace`（完整命名空间）。同请求内有缓存，不重复扫描磁盘。

### 3.2 plugin.json 清单规范

每个驱动包必须携带 `plugin.json`（双层结构）：

```json
{
    "key": "driver_key",
    "name": "驱动名称",
    "description": "驱动功能简述",
    "version": "1.0.0",
    "author": "CRMEB",
    "type": "driver",
    "path": "driver/{type}/{DriverName}",
    "require": { "crmeb": "^6.0", "php": "^8.0" },
    "driver": {
        "name": "驱动显示名称",
        "driver_key": "driver_key",
        "class_name": "Driver",
        "icon": "icon-xxx",
        "is_builtin": true,
        "sort": 100,
        "capabilities": [],
        "config_fields": [],
        "help_doc": {}
    }
}
```

- **顶层**：插件元信息，由 Installer 生命周期消费（安装/卸载/升级展示）
- **`driver` 子对象**：驱动全量描述字段，Registry 全量透传不做白名单；标准键（`name`/`class_name`/`icon`/`capabilities`/`config_fields`/`help_doc`/`sort`/`is_builtin`）缺省时由注册中心补默认值；各类型可有扩展字段（如 pay 的 `channel_key`/`pay_type_map`，upload 的 `domain_template`/`bucket_fields`，copyproduct 的 `platforms`）
- `key`（顶层）与 `driver_key`（driver 子对象）必须一致
- 完整字段说明见各类型 DEVELOPMENT.md，通用顶层字段见[短信驱动开发指南](sms/DEVELOPMENT.md#pluginjson-字段说明)

### 3.3 Installer 生命周期（可选）

驱动包需要执行安装态动作（建表、写入字典/配置）时，在包内提供 `Installer.php`，**原位生效**，系统不复制驱动文件到其他目录。

## 四、选型标准

新增第三方对接能力时，按以下顺序判定落位：

### 判定 1：是否属于既有 10 类驱动类型？

**是** → 在对应 `driver/{type}/` 下新增驱动包（如新增聚合支付通道 → `driver/pay/NewPay/`）。

判定特征：
- 能力本质是「换一个服务商做同一件事」（换短信通道、换 OSS 厂商、换支付渠道）
- 上层业务流程不变，只有通信细节变化
- 配置形态为「后台可切换 + 动态表单」（`config_fields` 渲染）

### 判定 2：不属于既有类型，是纯通信对接吗？

**是** → 评估新增驱动类型目录 `driver/{type}/`：
- 仅当它是对接「外部服务协议」且被多个业务方复用时才值得开新类型（如未来的 OCR、风控）
- 单一业务专用、无复用场景 → 不开新类型，放到 `crmeb/services/` 对应服务内实现

**否（涉及业务规则/流程编排）** → 不放 driver 层，改用业务扩展点：

| 需求本质 | 扩展点 | 落位 |
|---------|--------|------|
| 卖什么/库存/运费/履约流程 | 产品类型 | `modules/product/{type}/` |
| 怎么卖/定价/验证/售后规则 | 交易模式 | `modules/trade/{type}/` |
| 用户从哪来/如何识别登录 | 用户渠道 | `modules/channel/{端}/` |
| 抽奖/直播/签到等运营活动 | 营销玩法 | `modules/marketing/{type}/` |

> 简单判据：**写的是"怎么和第三方说话" → driver 层；写的是"业务上怎么办" → modules 扩展点。**

### 判定 3：驱动包内部的取舍

- **SDK 体积大**（如 COS/OBS 扩展）→ 放驱动包内 `extend/` 子目录，与主工程隔离
- **需要安装态动作** → 提供 `Installer.php`（原位生效，禁止文件复制）
- **配置字段** → 全部声明在 `plugin.json` 的 `config_fields`，后台动态渲染；密钥类字段用 `password` 类型；从其他配置自动读取的字段标记 `readonly`
- **能力差异化** → 通过 `capabilities` 声明（如短信的 `template`/`record`，机器人的 `send_markdown`/`send_card`），上层按能力降级而非硬编码判断驱动

## 五、开发流程

1. 在 `driver/{type}/{DriverName}/` 创建目录
2. 编写 `plugin.json`（先抄同类型现有驱动的结构再改）
3. 继承对应基类实现 `Driver.php`
4. 用 `DriverRegistry::scan()` 验证注册
5. 参考各类型 [DEVELOPMENT.md](#二驱动类型总览) 的接口规范与参考实现
