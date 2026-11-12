# 驱动分册 · 电子面单（express）

核对日期：2026-11-06。先读 [00-总览](00-总览-类型路由与边界.md) 第 3 节（驱动通用机制），本篇只写电子面单专属内容。

## 1. 选型判定

| 需求 | 落本类型 | 说明 |
| --- | --- | --- |
| 发货时向快递商下单、取运单号/面单图 | `backend/driver/express/{Dir}/` + **同名** `backend/driver/logistics/{Dir}/` | 运行时类只从 `logistics` 命名空间加载 |
| 仅查物流轨迹 | [logistics 分册](01-驱动-logistics-物流查询.md) | 不必注册 `express` 类型 |
| 改发货流程、订单字段 | `app/` 业务 | 超出纯驱动范围，先问用户 |

电子面单是「插件元数据在 `express`，执行代码在 `logistics`」的双目录模型，这是当前架构的硬约束，不是可选优化。

## 2. 开工前必须告诉用户

| 约束 | 说明 |
| --- | --- |
| **双目录** | `crmeb\services\logistics\Express` 的 `$namespace = '\\driver\\logistics\\'`。`ExpressDriverServices::createExpressService($driverKey)` 只会实例化 `driver\logistics\{Studly}\Driver`，不会读 `driver\express\` 下的 PHP 类 |
| **express 表** | `eb_driver` 中 `driver_type=express` 一行负责后台配置、启用、`ExpressDriverServices::scanDrivers()` |
| **快递公司绑定** | `eb_express_company.driver_key` 可把某公司绑到你的驱动；未绑定时回退 `yihaotong` |
| **全局开关** | `ExpressConfigServices` / 系统「电子面单」总开关关闭时，`OrderDeliveryServices` 直接拒绝电子面单发货 |

除双目录外，面单链路**没有**像 logistics 查询那样的 `driver_key` 白名单；新 `driver_key` 只要 logistics 侧类存在且 `eb_driver` 启用，即可被 `createExpressService()` 加载。但若 `dump()` 返回字段与现役不一致，仍可能要改 `OrderDeliveryServices` 读取 `$dump['kuaidinum']` 等逻辑——交付前与产品确认返回契约。

## 3. 文件清单

```text
backend/driver/express/{Dir}/          # 安装、后台、plugin.json（driver_type=express）
├── plugin.json
└── Driver.php                         # 可与 logistics 同步；运行时默认不加载此类

backend/driver/logistics/{Dir}/        # 必须：Express 门面实际加载
├── plugin.json                        # driver_type=logistics（若也要轨迹查询/一号通同款）
└── Driver.php                         # namespace driver\logistics\{Dir}
```

现役仅 **Yihaotong**（`driver_key=yihaotong`）：

| 路径 | 作用 |
| --- | --- |
| `driver/express/Yihaotong/` | 后台电子面单驱动配置、默认安装 |
| `driver/logistics/Yihaotong/` | `open/temp/express/query/dump` 等全部 API |

**推荐照抄**：以 `logistics/Yihaotong/Driver.php` 为真源，express 侧 `Driver.php` 与之保持一致或仅保留 express 目录的 `plugin.json` + 指向说明（现役两份代码并行维护）。

## 4. plugin.json（express 类型）

```json
{
    "key": "your_express",
    "name": "某某电子面单",
    "description": "对接某某面单 API",
    "version": "1.0.0",
    "author": "作者",
    "type": "driver",
    "path": "driver/express/YourExpress",
    "require": { "crmeb": "^8.0", "php": "^8.0" },
    "driver": {
        "name": "某某电子面单",
        "driver_key": "your_express",
        "sort": 100,
        "config_fields": [
            {
                "field": "your_express_api_key",
                "label": "API Key",
                "type": "text",
                "required": true,
                "group": "账号"
            },
            {
                "field": "your_express_api_secret",
                "label": "API Secret",
                "type": "password",
                "required": true,
                "group": "账号"
            },
            {
                "field": "open",
                "label": "开启电子面单",
                "type": "switch",
                "group": "接口配置",
                "default": "1"
            }
        ],
        "help_doc": {
            "title": "使用说明",
            "steps": ["在快递商开放平台开通面单", "填写密钥", "同步快递公司并绑定驱动", "配置寄件人/云打印机 siid"],
            "link": ""
        }
    }
}
```

logistics 侧若单独注册查询能力，另建 `driver/logistics/YourExpress/plugin.json`，`driver_key` 与 express **相同**，`capabilities` 可含 `query`（见 logistics 分册）。

一号通 express 清单含只读字段 `yihaotong_appid` / `yihaotong_appsecret`，值由 `ExpressDriverServices::getDetail()` 从 `sys_config` 注入，**不写入** driver 表。

## 5. Driver.php 核心方法

继承 `crmeb\services\logistics\BaseExpress`（**logistics 命名空间下的类**）。

| 方法 | 调用方 | 契约摘要 |
| --- | --- | --- |
| `open()` | 后台开通服务 | 返回服务商原始结构或 bool |
| `temp(string $com)` | 面单模板选择 | 按快递公司编码返回模板列表 |
| `express(int $type, int $page, int $limit)` | `ExpressServices::getPlatExpress()` | 同步 `eb_express` 快递公司字典 |
| `query(...)` | 可选，与 logistics 查询共用 | 见 logistics 分册 |
| `dump(array $data)` | **`OrderDeliveryServices` 电子面单发货** | 成功需含 **`kuaidinum`**（运单号） |

### 5.1 `dump($data)` 入参（业务组装）

`OrderDeliveryServices` 传入字段（节选）：

| 字段 | 含义 |
| --- | --- |
| `com` | 快递公司编码 |
| `to_name` / `to_tel` / `to_addr` | 收件人（订单用户） |
| `from_name` / `from_tel` / `from_addr` | 寄件人（后台表单） |
| `temp_id` | 面单模板 ID |
| `siid` | 云打印机编号（公司级优先） |
| `count` | 商品件数 |
| `cargo` | 商品标题摘要 |
| `order_id` | 订单号 |

一号通驱动还会读 `eb_express` 行上的 `partner_id`、`net` 等扩展字段（见 `logistics/Yihaotong/Driver.php` 的 `dump()`）。

### 5.2 `dump()` 返回值

业务代码：

```php
$dump = $expressService->dump($expData);
$orderInfo->delivery_id = $dump['kuaidinum'];
```

新驱动必须保证成功时存在 `kuaidinum` 键；其他键按需扩展，但不要改已有键名除非同步改 `app/`。

## 6. 配置存储

| 表 / 配置 | 用途 |
| --- | --- |
| `eb_driver`（express） | 驱动元数据、`config_fields` + `value` |
| `eb_express` | 平台快递公司；`syncExpress()` 从默认驱动的 `express()` 拉取 |
| `eb_express_company` | 公司级 `driver_key`、`siid` |
| `sys_config` | 一号通账号；全局 `config_export_siid` 等 |

`ExpressDriverServices::buildDriverConfig()` 把 `config_fields` 转为关联数组传入 `Express` 门面。

## 7. 调用链

```text
管理端电子面单发货（express_record_type=2）
  → OrderDeliveryServices::delivery(...)
  → ExpressDriverServices::createExpressServiceByCode($delivery_code)
       → getDriverKeyByCode()  // eb_express_company 或 yihaotong
       → app()->make(Express::class, [$driverKey, $config])
  → driver\logistics\{Studly}\Driver::dump($expData)

同步快递公司
  → ExpressServices::getPlatExpress()
  → createExpressService()  // 默认 yihaotong
  → express(0, 0, 1000)

模板列表
  → ExportController / ExpressTemplateController
  → createExpressService(...) → temp($com)
```

相关：`StoreIntegralOrderServices` 等模块发货同样注入 `ExpressDriverServices`。

## 8. 逐步开发流程

1. 确定 `driver_key`（如 `my_express`）与 `{Dir}=MyExpress`。
2. 创建 **`driver/logistics/MyExpress/`**：实现 `Driver.php`（从 Yihaotong 复制改 API）。
3. 创建 **`driver/express/MyExpress/`**：复制 `plugin.json`（`path` 指向 express 目录）；`Driver.php` 建议与 logistics 保持一致，避免日后误用旧类。
4. 若需物流查询且 driver_key 不在查询白名单，另走 logistics 分册的核心扩展流程。
5. `php think plugin:manifest-check`；分别安装 `driver:express:my_express` 与（如有）`driver:logistics:my_express`。
6. 后台启用 express 驱动；在快递公司管理绑定 `driver_key`；配置寄件人与模板。
7. 测试订单电子面单发货，确认 `delivery_id` 写入运单号。

## 9. 参考实现

- `backend/driver/logistics/Yihaotong/Driver.php`：`dump()` 完整参数校验、读 `eb_express` 扩展、IMAGE/PRINT 模式。
- `backend/driver/express/Yihaotong/plugin.json`：只读一号通字段、help_doc 步骤。

## 10. 打包（`.plugin-packages/`）

面单交付通常含**两个目录**（或一个 zip 内两层路径）：

```bash
cd backend
php think plugin:manifest-check

zip -r ../.plugin-packages/driver-express-my_express-1.0.0.zip \
  driver/express/MyExpress driver/logistics/MyExpress
```

若 logistics 插件单独安装，另打 `driver-logistics-my_express-1.0.0.zip`。版本首版均为 `1.0.0`。

## 11. 验收清单

- [ ] `express` 与 `logistics` 目录名相同且均等于 `Str::studly(driver_key)`
- [ ] 两个 `plugin.json` 的 `driver_key` 一致；`path` 各自匹配真实目录
- [ ] `createExpressService('your_key')` 能加载类且无 `class not exists`
- [ ] `dump()` 成功返回含 `kuaidinum`
- [ ] 电子面单总开关开启；测试订单发货写入运单号
- [ ] `getPlatExpress()` 能同步（若实现了 `express()`）
- [ ] 未改 `app/`/`crmeb/` 或变更已在交付说明列出

## 12. 常见失败现象

| 现象 | 原因 |
| --- | --- |
| 后台有驱动，发货报 class not exists | 只有 `driver/express/`，缺少 `driver/logistics/{Dir}/Driver.php` |
| 始终走一号通 | 快递公司未绑 `driver_key`；或新驱动未启用 |
| `电子面单已关闭` | 全局 export 开关 off |
| `快递公司编码缺失` / 模板缺失 | 表单字段未选 `delivery_code`、`express_temp_id` |
| `寄件人信息缺失` | `dump()` 必填项与后台表单不一致 |
| 运单号为空 | `dump()` 返回键名不是 `kuaidinum` |
| 模板接口 401 | 密钥在 express 表未保存；或一号通未登录 |

## 13. 与 logistics 查询分册的边界

- 同一 `Yihaotong` 类既面单又查询：logistics 启用驱动影响**轨迹**；express 启用驱动影响**发货与 sync**。
- 纯第三方面单、不用其查询 API：仍建议保留 logistics 副本仅实现 `dump/temp/express`，`query()` 可空实现。
- 物流轨迹白名单仅影响 `ExpressServices::query()`，不阻止 `dump()`。

## 14. 本地验证命令（只读 / 开发库）

确认 logistics 侧类可被门面加载（将 `my_express` 换成你的 key）：

```bash
cd backend && php -r 'require "vendor/autoload.php"; (new think\App(getcwd()."/"))->initialize();
$key = "yihaotong";
echo crmeb\services\driver\DriverRegistry::getDriverClass("logistics", $key), PHP_EOL;
$svc = app()->make(\crmeb\services\logistics\Express::class, [$key, []]);
echo get_class($svc), PHP_EOL;'
```

扫描 express 类型插件是否入库：

```bash
cd backend && php -r 'require "vendor/autoload.php"; (new think\App(getcwd()."/"))->initialize();
foreach (crmeb\services\driver\DriverRegistry::scan("express") as $k => $d) {
  echo $k, " status=", $d["status"] ?? "-", PHP_EOL;
}'
```

`ExpressDriverServices::syncDrivers()` 会在部分后台操作触发目录与 `eb_express_driver` 同步；插件安装路径仍应优先走「插件管理」安装，避免只拷目录未写 `owner_plugin_id`。

## 15. 与 `ExportController` / 模板管理

- 电子面单模板列表：依赖 `temp($com)` 返回；模板 ID 写入发货表单项 `express_temp_id`。
- 云打印机 `siid`：`ExpressDriverServices::getCompanySiid()` 读 `eb_express_company.siid`，再回退 `ExpressConfigServices::getItem('siid')`。
- 关闭电子面单：`ExpressConfigServices::getItem('open', 0) === 0` 时任何 `dump()` 不会被执行。

## 16. 双目录维护策略（推荐）

| 策略 | 做法 |
| --- | --- |
| 最小漂移 | 仅维护 `logistics/{Dir}/Driver.php`；express 目录只保留 `plugin.json`（现役两份代码相同，合并前保持同步） |
| 交付说明 | zip 内必须同时包含两个 path，安装两次或同一包内两个 manifest（若拆两个插件则各有一个 `plugin.json`） |
| 类命名空间 | logistics 内必须是 `namespace driver\logistics\{Dir}`；express 目录若留 `Driver.php`，命名空间为 `driver\express\{Dir}`，**不会被** `createExpressService` 加载 |

## 17. 扩展阅读

- 总览第 2 节：express 与 logistics 命名空间约束。
- `backend/driver/express/DEVELOPMENT.md`：历史草稿，以本篇与代码为准。
- 订单发货：`app/services/order/OrderDeliveryServices.php` 中 `express_record_type == 2` 分支。
