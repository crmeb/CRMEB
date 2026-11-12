# 驱动分册 · 云打印（printer）

核对日期：2026-11-06。先读 [00-总览](00-总览-类型路由与边界.md) 第 3 节（驱动通用机制），本篇只写订单小票云打印专属内容。

## 1. 选型判定

| 需求 | 落本类型 |
| --- | --- |
| 订单支付/下单后向易联云、飞鹅等云打印机推小票 | `backend/driver/printer/{Dir}/` |
| 电子面单（快递单） | [express 分册](01-驱动-express-电子面单.md) |
| 本地 USB 打印 | 不在 driver/printer 范畴 |

云打印驱动只负责：接收**已渲染好的**打印指令字符串，调用厂商 API 提交；小票模板、订单字段组装在 `app/services/driver/printer/SystemTicketServices.php`。

## 2. 开工前必须告诉用户

小票链路在核心**写死驱动目录名**（Studly，不是 snake_case `driver_key`）：

| 位置 | 行为 |
| --- | --- |
| `SystemTicketServices::startPrint()` | `if (!in_array($name, ['YiLianYun', 'FeiEYun'], true)) throw 不支持的打印驱动` |
| `SystemTicketServices::ticketForm()` | 平台单选只有「易联云 / 飞鹅云」，字段名 `yly_*` / `fey_*` |
| 同文件 `ylyContent()` / `feyContent()` | 易联云 `<FS2>` 标签 vs 飞鹅 `<CB><BR>` 标签 |

新云打印厂商要上线，需用户同意扩展：`startPrint` 白名单、表单 `appendControl`、内容渲染分支（或抽象为配置化模板）。仅交付 `driver/printer/` 目录无法在小票流程中被选中。

另：`printer` 驱动的 `driver_key` 现役与目录名相同（`YiLianYun`、`FeiEYun`），`Printer` 门面用 `Str::studly($name)` 解析类。

## 3. 文件清单

```text
backend/driver/printer/{Dir}/
├── plugin.json
└── Driver.php    # namespace driver\printer\{Dir}; extends BasePrinter
```

| 组件 | 路径 |
| --- | --- |
| 基类 | `crmeb\services\printer\BasePrinter` |
| 门面 | `crmeb\services\printer\Printer` |
| Token 封装 | `crmeb\services\printer\AccessToken`（构造时传入 `$config`） |

现役：

| 目录 | driver_key | plugin.json 额外字段 |
| --- | --- | --- |
| `YiLianYun/` | `YiLianYun` | `driver.driver` = `YiLianYun` |
| `FeiEYun/` | `FeiEYun` | 同上 |

**推荐照抄**：`FeiEYun/` 或 `YiLianYun/`（均含 `startPrinter()`、`setPrinterContent()`）。

## 4. plugin.json

```json
{
    "key": "YourCloudPrint",
    "name": "某某云打印",
    "description": "某某云打印驱动",
    "version": "1.0.0",
    "author": "作者",
    "type": "driver",
    "path": "driver/printer/YourCloudPrint",
    "default_install": false,
    "require": { "crmeb": "^8.0", "php": "^8.0" },
    "driver": {
        "name": "某某云打印",
        "driver_key": "YourCloudPrint",
        "driver": "YourCloudPrint",
        "config_fields": [
            {
                "field": "yourcloud_api_key",
                "label": "API Key",
                "type": "text",
                "required": true,
                "group": "基础配置"
            },
            {
                "field": "yourcloud_api_secret",
                "label": "API Secret",
                "type": "password",
                "required": true,
                "group": "基础配置"
            }
        ],
        "capabilities": {
            "device_id": {
                "label": "设备编号",
                "tip": "在「打印设置」中填写 printer_id"
            }
        },
        "help_doc": {
            "title": "使用说明",
            "steps": ["注册开放平台", "填写 API 密钥", "添加打印设备并绑定驱动名 YourCloudPrint"],
            "link": ""
        }
    }
}
```

说明：

- `driver.driver` 与 `driver_key`、目录名保持一致（历史契约，打印设备表 `driver` 列存此名）。
- `capabilities.device_id` 供后台展示设备 SN 说明；业务不强制解析。

## 5. Driver.php 方法契约

```php
<?php
declare(strict_types=1);

namespace driver\printer\YourCloudPrint;

use crmeb\services\printer\BasePrinter;

class Driver extends BasePrinter
{
    protected function initialize(array $config): void {}

    public function setPrinterContent($content, $times = 1): self
    {
        $this->times = $times;
        $this->printerContent = $content;
        return $this;
    }

    public function startPrinter()
    {
        if (!$this->printerContent) {
            return $this->setError('Missing print');
        }
        // 调厂商 API；成功返回 array（含任务 id）；失败 setError 返回 false
    }
}
```

### 5.1 必填方法

| 方法 | 说明 |
| --- | --- |
| `setPrinterContent(string $content, int $times)` | 链式设置内容与联数 |
| `startPrinter()` | 提交打印；易联云返回 `['body'=>['id'=>...]]`；飞鹅返回 `['data'=>...]` |

### 5.2 基类能力

- `setJobId($key)`：任务去重，重试沿用（`SystemTicketServices` 传入 task hash）。
- `isSubmissionUncertain()` / `getError()`：提交结果不明时避免重复打单。
- 构造：`(string $name, AccessToken $accessToken, string $configFile)`，`AccessToken` 从 `$config` 映射字段（见 `createPrinter` 传入的 `$configData`）。

### 5.3 SystemTicketServices 传入的配置键

| 驱动名 | `$configData` 键 |
| --- | --- |
| `YiLianYun` | `partner`, `clientId`, `apiKey`, `terminal`（来自 `yly_user_id` 等 + 设备 `printer_id`） |
| `FeiEYun` | `feyUser`, `feyUkey`, `feySn` |

新驱动若接入核心，需在此映射增加分支。

## 6. 配置与数据表

| 存储 | 用途 |
| --- | --- |
| `eb_driver`（printer） | 驱动级 API 账号 |
| `eb_system_ticket` / 打印设备表（见 `PrinterChannelDao`） | 设备 `driver` 列、`printer_id`、`times`、`print_content` 模板 JSON |
| `eb_printer_task` | 提交去重、状态 `pending/submitted/failed/uncertain` |

设备级凭据（如 SN）在「小票打印」菜单配置，不在 `plugin.json`。

## 7. 调用链

```text
支付/下单事件
  → PrintJob / StoreIntegralOrderServices 等
  → SystemTicketServices::startPrint($order, $product, $print_type)
  → 查启用设备 → 按 driver 分组读 eb_driver 配置
  → ylyContent / feyContent 渲染 HTML 标签串
  → new Printer($name, $configData)
  → setJobId → setPrinterContent($content, $times) → startPrinter()
  → PrinterTaskDao 记录 submitted / failed
```

手动打印：`$print_type === true` 打全部启用设备。

## 8. 逐步开发流程

1. 定目录名 `{Dir}`（即将来 `eb_printer_channel.driver` 的值）。
2. 实现 `Driver.php`：对照厂商 API 文档完成 `startPrinter()`。
3. 编写 `plugin.json`，`key`/`driver_key`/`driver` 与目录一致。
4. 静态检查；安装并启用驱动插件。
5. **未改核心**：用脚本直接 `new Printer('{Dir}', $config)` 测通 API。
6. **已改核心**：扩展 `startPrint`、`ticketForm`、`xxxContent`；后台添加设备并选新驱动。
7. 下单/支付测自动打印；看 `eb_printer_task` 状态与打印机实收。

## 9. 参考实现

- `driver/printer/YiLianYun/Driver.php`：授权刷新、error 18/14 重试、`submissionUncertain`。
- `driver/printer/FeiEYun/Driver.php`：飞鹅签名与 SN。
- `app/services/driver/printer/SystemTicketServices.php`：`ylyContent`/`feyContent` 字段与订单快照。

## 10. 打包（`.plugin-packages/`）

```bash
cd backend
php -l driver/printer/YourCloudPrint/Driver.php
php think plugin:manifest-check
zip -r ../.plugin-packages/driver-printer-YourCloudPrint-1.0.0.zip driver/printer/YourCloudPrint/
```

## 11. 验收清单

- [ ] `driver_key` 与目录名一致（打印类常用 Studly PascalCase）
- [ ] `setPrinterContent` + `startPrinter` 可用；失败走 `setError`
- [ ] 无 `use app\`
- [ ] `DriverRegistry::scan('printer')` 可见
- [ ] **改核心后**：后台可选新平台；支付后小票任务 `submitted`
- [ ] 重复回调/重试不重复打单（task_key 去重）

## 12. 常见失败现象

| 现象 | 原因 |
| --- | --- |
| 配置了驱动但从不出票 | 设备 `driver` 不在 YiLianYun/FeiEYun 白名单 |
| `小票订单号或打印时机无效` | `print_type` 非 1/2/true |
| `打印机设备ID为空` | 设备未填 SN/`printer_id` |
| `云打印提交失败` | 返回非 array；密钥错；标签内容非法 |
| 任务 `uncertain` | 网络超时但可能已打单；勿盲目重发 |
| `Driver [yi_lian_yun] not supported` | 门面 default 配置与目录名不一致；应传 `YiLianYun` |
| 设备配置与驱动表字段对不上 | `yly_*`/`fey_*` 字段名固定 |

## 13. 与 express 云打印机 siid

电子面单 `siid`（快递云打印）在 express 配置链，与小票 `driver/printer` 无关。勿混淆 `config_export_siid` 与小票设备 SN。

## 14. 打印模板 JSON（`print_content`）

设备表存储的模板经 `PrinterChannelServices::normalizePrintContent()` 归一化，常见键：

| 键 | 含义 |
| --- | --- |
| `header` | 页眉店铺名 |
| `delivery` | 配送/收货信息块 |
| `buyer_remarks` | 买家备注 |
| `goods` | 数组，含 `0` 表格 / `1` 规格编码等 |
| `pay` / `order` | 支付项、订单号/时间项 |
| `code` / `code_url` | 二维码 |
| `show_notice` / `notice_content` | 底部公告 |

`ylyContent`/`feyContent` 在 `SystemTicketServices` 内硬编码渲染逻辑；新驱动若接入核心，需复制一份标签规则或抽象共享。

## 15. 队列与触发时机

| print_type | 含义 |
| --- | --- |
| `1` | 支付后（默认） |
| `2` | 下单后 |
| `true` | 手动打印全部启用设备 |

`PrintJob` 队列消费时组装 `$order`、`$product` 快照；字段缺失会导致模板空白但不会选错驱动。

## 16. 本地验证

```bash
cd backend && php -r 'require "vendor/autoload.php"; (new think\App(getcwd()."/"))->initialize();
$p = new \crmeb\services\printer\Printer("YiLianYun", ["partner"=>"","clientId"=>"","apiKey"=>"","terminal"=>""]);
echo get_class($p), PHP_EOL;'
```

替换为 `FeiEYun` 与对应 config 键名测类加载。完整 `startPrinter` 需有效云端账号。

## 17. 核心改造最小集合（用户同意后）

- [ ] `startPrint()` 白名单增加 `{Dir}`
- [ ] `ticketForm()` 增加 radio 分支与表单字段
- [ ] 新增 `{dir}Content()` 或参数化标签引擎
- [ ] `createPrinter()` 传入的 `$configData` 键与 `AccessToken`/驱动一致
- [ ] 文档告知运维在「打印设置」选新 `driver` 列值

## 18. AccessToken 与配置映射

`Printer` 门面构造 `AccessToken($name, $configFile, $config)`；易联云/飞鹅在 `AccessToken` 内按 `$name` 分支读取 `partner`/`feyUser` 等。新驱动通常要在 `crmeb/services/printer/AccessToken.php` 增加分支——这又属于核心改动，需单独授权；或驱动在 `initialize()` 内忽略 AccessToken 自行签名（与现役模式差异较大，优先与用户确认架构）。
