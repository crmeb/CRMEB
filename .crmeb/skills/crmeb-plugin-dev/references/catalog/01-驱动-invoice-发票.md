# 驱动分册 · 电子发票（invoice）

核对日期：2026-11-06。先读 [00-总览](00-总览-类型路由与边界.md) 第 3 节（驱动通用机制），本篇只写发票专属内容。

## 1. 选型判定

| 需求 | 落本类型 |
| --- | --- |
| 对接第三方电子发票（开具、红冲、下载 PDF） | `backend/driver/invoice/{Dir}/` |
| 只改订单发票申请 UI、税率配置 | `app/` + 系统配置 `elec_invoice_*` |

发票驱动负责与税控/服务商 HTTP 通信；订单发票记录仍在 `eb_store_order_invoice` 等业务表，由 `OrderInvoiceServices` 维护。

## 2. 开工前必须告诉用户

发票驱动可安装进 `driver/invoice/`，但**现役调用方写死一号通**：

| 位置 | 代码事实 |
| --- | --- |
| `app/services/order/OrderInvoiceServices.php` | `app()->make(Invoice::class, [yihaotong_config()])`，未读启用驱动 |
| `app/api/controller/v1/order/StoreOrderInvoiceController.php` | 同上 |
| `crmeb\services\invoice\Invoice::getDefaultDriver()` | 固定返回 `'yihaotong'` |
| `Invoice::invokeClass()` | 始终 `new AccessTokenServeService($config['yihaotong_appid'], $config['yihaotong_appsecret'])` |

新驱动要实际上线，需用户同意至少：调用方改为按 `eb_driver` 启用项实例化 `Invoice`、或传入正确 `driver_key` 与配置；门面层若新服务商不用一号通 Token，还要改 `invokeClass()` 构造方式。

只交付驱动目录时，须在交付说明中列出上述核心待办。

## 3. 文件清单

```text
backend/driver/invoice/{Dir}/
├── plugin.json
└── Driver.php    # namespace driver\invoice\{Dir}; extends BaseInvoice
```

| 组件 | 路径 |
| --- | --- |
| 基类 | `crmeb\services\invoice\BaseInvoice` |
| 门面 | `crmeb\services\invoice\Invoice`（`BaseManager`，命名空间 `\driver\invoice\`） |
| Token | 一号通用 `crmeb\services\yihaotong\AccessTokenServeService` |

现役：`backend/driver/invoice/Yihaotong/`（`driver_key=yihaotong`）。

## 4. plugin.json

```json
{
    "key": "your_invoice",
    "name": "某某电子发票",
    "description": "对接某某发票平台",
    "version": "1.0.0",
    "author": "作者",
    "type": "driver",
    "path": "driver/invoice/YourInvoice",
    "require": { "crmeb": "^8.0", "php": "^8.0" },
    "driver": {
        "name": "某某电子发票",
        "driver_key": "your_invoice",
        "sort": 100,
        "config_fields": [
            {
                "field": "your_invoice_app_id",
                "label": "AppID",
                "type": "text",
                "required": true,
                "group": "账号"
            },
            {
                "field": "your_invoice_app_secret",
                "label": "AppSecret",
                "type": "password",
                "required": true,
                "group": "账号"
            },
            {
                "field": "invoice_switch",
                "label": "发票服务开关",
                "type": "switch",
                "group": "基础",
                "default": "1"
            }
        ],
        "help_doc": {
            "title": "配置说明",
            "steps": ["开通电子发票资质", "填写密钥", "（若未改核心）仅作备用驱动"],
            "link": ""
        }
    }
}
```

第三方驱动不要写 `is_builtin: true`。一号通驱动字段 `yihaotong_appid`/`secret` 在后台只读，值来自 `sys_config`（与短信/面单一致）。

## 5. Driver.php 方法契约

```php
<?php
declare(strict_types=1);

namespace driver\invoice\YourInvoice;

use crmeb\services\invoice\BaseInvoice;

class Driver extends BaseInvoice
{
    protected function initialize(array $config = []): void
    {
        parent::initialize($config);
        // 非一号通：在此初始化自有 HTTP 客户端；勿 use app\
    }

    public function invoiceIssuanceUrl(array $params = []) { /* iframe 开票页 */ }
    public function downloadInvoice(string $invoiceNum = '') { /* PDF */ }
    public function invoiceInfo(string $invoiceNum = '') { /* 详情 */ }
    public function category(array $params = []) { /* 税收分类 */ }
    public function invoiceIssuance(string $unique = '', array $params = []) { /* 开具 */ }
    public function applyRedInvoice(array $params = []) { /* 红字申请 */ }
    public function redInvoiceIssuance(array $params = []) { /* 红冲开具 */ }
}
```

### 5.1 业务实际调用的方法

| 方法 | 调用场景 | 期望返回 |
| --- | --- | --- |
| `invoiceIssuance($unique, $params)` | 自动/手动开票，`$unique` 为中台订单号 `order_no` | `['status' => 200, 'data' => ['invoice_num', 'invoice_type', 'invoice_serial_number', ...]]` |
| `redInvoiceIssuance($params)` | 退款冲红 | `['status' => 200, ...]`，参数含 **`invoiceNum`**（camelCase） |
| `invoiceIssuanceUrl` / `downloadInvoice` / `invoiceInfo` | 用户端发票详情、下载 | 与一号通 API 形状对齐或同步改 Controller |

`OrderInvoiceServices::invoicing()` 组装 `$data` 含：`invoice_type`（81/82）、`tax_id`、`is_enterprise`、`goods[]`（`cate_id`、`store_name`、`unit_price`、`num`、`tax_rate`），税率/类目来自 `sys_config('elec_invoice_cate')`、`elec_invoice_tax_rate`。

### 5.2 一号通参考

`driver/invoice/Yihaotong/Driver.php` 使用常量路径如 `v2/invoice/invoice_issuance`；全部经 `$this->accessToken->httpRequest()`。

## 6. 配置存储

| 来源 | 内容 |
| --- | --- |
| `eb_driver`（invoice） | 驱动元数据、非一号通密钥 |
| `sys_config` | `yihaotong_appid`/`secret`；`elec_invoice`、`auto_invoice`；`elec_invoice_cate`、`elec_invoice_tax_rate` |
| `yihaotong_config()` | `app/common.php` _helper，合并一号通账号 |

发票驱动**不应**把第三方密钥写入 `system_config`（一号通平台账号除外）。

## 7. 调用链

```text
自动开票定时/队列
  → OrderInvoiceServices::autoInvoice / invoicing
  → Invoice(yihaotong_config()) → yihaotong Driver

用户端发票
  → StoreOrderInvoiceController
  → Invoice(yihaotong_config()) → invoiceIssuanceUrl / downloadInvoice ...

退款冲红
  → OrderInvoiceServices::redInvoiceIssuance
  → redInvoiceIssuance(['invoiceNum' => ..., 'apply_type' => '01'])
```

启用开关：`sys_config('elec_invoice') == 1` 且订单发票记录状态满足业务条件。

## 8. 逐步开发流程

1. 定 `driver_key` 与目录名 `{Dir}`。
2. 复制 `Yihaotong/Driver.php`，替换 API 路径与签名。
3. 若服务商不用一号通 Token：在 `initialize()` 内自建客户端；**同时**规划核心改造 `Invoice::invokeClass()`（需用户授权）。
4. 编写 `plugin.json`，`config_fields` 与 `initialize()` 读取字段一致。
5. 静态检查、后台安装驱动。
6. **未改核心**：仅验证 `DriverRegistry::scan('invoice')` 与手动 `app()->make(Invoice::class, [$driverKey, ...])` 实验（可能因 Token 构造失败）。
7. **已改核心**：切换启用驱动，走完整开票/红冲回归。

## 9. 参考实现

- `backend/driver/invoice/Yihaotong/`：完整 API 常量与方法。
- `backend/driver/invoice/DEVELOPMENT.md`：方法列表（以代码为准）。

## 10. 打包（`.plugin-packages/`）

```bash
cd backend
php -l driver/invoice/YourInvoice/Driver.php
php think plugin:manifest-check
zip -r ../.plugin-packages/driver-invoice-your_invoice-1.0.0.zip driver/invoice/YourInvoice/
```

## 11. 验收清单

- [ ] 目录、`key`、`driver_key`、`path` 四者对齐
- [ ] 无 `use app\` / `use modules\`
- [ ] `invoiceIssuance` / `redInvoiceIssuance` 返回含 `status` 字段，成功为 200
- [ ] 红冲参数使用 `invoiceNum` 键名
- [ ] 清单无真实密钥
- [ ] **核心已改**：启用新驱动后自动开票写入 `invoice_num`
- [ ] **核心未改**：行为仍为 yihaotong（符合预期说明）

## 12. 常见失败现象

| 现象 | 原因 |
| --- | --- |
| 安装了新驱动但从不开票 | 调用方仍 `yihaotong_config()` + 默认 driver |
| `缺少一号通凭证` | 未配置平台账号；或非一号通驱动却被一号通 Token 构造 |
| 开票失败无前台提示 | `invoicing()` catch 后只写 Log |
| 红冲报发票状态有误 | 订单发票记录 `unique_num`/`is_invoice` 不满足 |
| `Driver [x] not supported` | 目录名与 `Str::studly(driver_key)` 不一致 |
| 税率/类目错误 | `elec_invoice_*` 系统配置未设 |

## 13. 与其他一号通能力

短信、面单、采集、物流查询均可共用 `yihaotong_appid`/`secret`；发票驱动 `initialize()` 与它们相同，从 `sys_config` 读取。独立发票服务商应使用 `eb_driver.config_fields`，并在核心改造后由 `buildDriverConfig` 类逻辑传入驱动。

## 14. 系统配置项（业务侧，非驱动表）

| sys_config 键 | 作用 |
| --- | --- |
| `elec_invoice` | 总开关，关则不走开票 |
| `auto_invoice` | 是否自动开票 / 自动冲红任务 |
| `elec_invoice_cate` | 默认税收分类编码（写入 goods.cate_id） |
| `elec_invoice_tax_rate` | 默认税率 |

改这些键只影响组装参数，不替换驱动实现；插件开发者一般无需改 install SQL。

## 15. 本地验证

```bash
cd backend && php -r 'require "vendor/autoload.php"; (new think\App(getcwd()."/"))->initialize();
echo crmeb\services\driver\DriverRegistry::getDriverClass("invoice", "yihaotong"), PHP_EOL;'
```

手动实例化（需一号通账号已配置）：

```bash
cd backend && php -r 'require "vendor/autoload.php"; (new think\App(getcwd()."/"))->initialize();
$cfg = yihaotong_config();
$inv = app()->make(\crmeb\services\invoice\Invoice::class, [$cfg]);
echo get_class($inv), PHP_EOL;'
```

未改核心前，即使 `DriverRegistry::scan('invoice')` 有多行，生产开票仍走默认 yihaotong。

## 16. 核心改造检查清单（用户同意后）

- [ ] `OrderInvoiceServices` / `StoreOrderInvoiceController` 读取启用 invoice 驱动
- [ ] `Invoice::invokeClass()` 按驱动构造 Token/HTTP，而非写死一号通
- [ ] `getDefaultDriver()` 与后台启用联动
- [ ] 用户端下载/iframe 字段与新区块对齐
- [ ] 红冲仍传 `invoiceNum` camelCase

## 17. 失败排障补充

| 日志/现象 | 排查 |
| --- | --- |
| `自动开具发票失败` + Log | 一号通 HTTP 401、goods 类目为空 |
| `unique_num` 为空 | `invoiceIssuance` 未返回 200 或异常被 catch |
| iframe 空白 | URL 字段与前端取值不一致 |
| 插件启用但无效果 | 未改核心时的预期行为，交付说明须写明 |
