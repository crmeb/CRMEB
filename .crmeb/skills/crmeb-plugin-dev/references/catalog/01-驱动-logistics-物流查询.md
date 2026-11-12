# 驱动分册 · 物流查询（logistics）

核对日期：2026-11-06。先读 [00-总览](00-总览-类型路由与边界.md) 第 3 节（驱动通用机制），本篇只写物流轨迹查询专属内容。

## 1. 选型判定

| 需求 | 落本类型 | 不要落本类型 |
| --- | --- | --- |
| 根据运单号查轨迹、在订单/售后展示物流节点 | `backend/driver/logistics/{Dir}/` | 电子面单打印、同步快递公司列表 → [express 分册](01-驱动-express-电子面单.md) |
| 只换查询 API，业务仍走 `ExpressServices::query()` | 驱动 | 改订单状态机、改缓存 key 规则 → 需改 `app/`，先问用户 |

物流查询与电子面单在业务上相关，但在仓库里是两个 `driver_type`：`logistics` 只管轨迹；面单走 `express` + 同名 `logistics` 实现类（见 express 分册）。

## 2. 开工前必须告诉用户

物流查询驱动可以零侵入安装到 `driver/logistics/`，但**运行时是否被调用**由 `app/services/shipping/ExpressServices.php` 写死：

| 位置 | 行为 |
| --- | --- |
| `resolveEnabledQueryDriver()` | 启用驱动的 `driver_key` 必须在 `['aliyun_express','yihaotong']`，否则抛 `物流查询驱动不支持` |
| `query()` 内 `switch ($driverKey)` | 只解析上述两种第三方返回结构；`default` 同样抛异常 |

因此：只交付驱动目录时，后台可以安装、启用，但**用户端/管理端查轨迹仍失败**，除非用户同意改 `ExpressServices` 两处白名单与解析分支。

同类型启用互斥：`logistics` 属于总览 3.4 中「`sms`/`email`/`copyproduct`/`logistics` 同类型仅一个启用」；新驱动 `is_builtin` 为真时，安装后默认关闭，需管理员手动切换。

## 3. 文件清单

```text
backend/driver/logistics/{Dir}/
├── plugin.json          # driver_type 写入 eb_driver 时为 logistics
├── Driver.php           # namespace driver\logistics\{Dir}; extends BaseExpress
└── extend/              # 可选：第三方 SDK
```

| 基类 / 门面 | 路径 |
| --- | --- |
| `crmeb\services\logistics\BaseExpress` | 构造函数 `(string $name, array $config, string $configFile)` |
| `crmeb\services\logistics\Express` | 命名空间固定 `\driver\logistics\`，`resolveClass()` 用 `Str::studly($driverKey)` |

现役参考：

| 目录 | driver_key | 特点 |
| --- | --- | --- |
| `AliyunExpress/` | `aliyun_express` | 阿里云市场 AppCode，返回 `result.list` + `deliverystatus` |
| `Yihaotong/` | `yihaotong` | 一号通凭证来自 `sys_config`，返回 `content[]` + `ischeck` |

**推荐照抄**：`backend/driver/logistics/AliyunExpress/`（单方法 `query()`、结构稳定、无一号通依赖）。

## 4. plugin.json

```json
{
    "key": "your_logistics",
    "name": "某某物流查询",
    "description": "对接某某物流轨迹 API",
    "version": "1.0.0",
    "author": "作者",
    "type": "driver",
    "path": "driver/logistics/YourLogistics",
    "require": { "crmeb": "^8.0", "php": "^8.0" },
    "driver": {
        "name": "某某物流查询",
        "driver_key": "your_logistics",
        "icon": "",
        "sort": 90,
        "capabilities": ["query"],
        "config_fields": [
            {
                "field": "your_logistics_app_code",
                "label": "AppCode",
                "type": "text",
                "required": true,
                "group": "基础配置",
                "tip": "在服务商控制台获取"
            }
        ],
        "help_doc": {
            "title": "配置说明",
            "steps": ["开通 API", "填写 AppCode", "后台启用本驱动并关闭其他物流查询驱动"],
            "link": ""
        }
    }
}
```

说明：

- 第三方驱动**不要**写 `is_builtin: true`（需自备密钥）；内置一号通/阿里云按产品策略处理。
- `capabilities` 声明 `query` 即可；业务层当前不读该字段做分支，但后台展示会用到。
- 字段名建议带 `driver_key` 前缀，避免与其他驱动混淆。

## 5. Driver.php 与方法契约

```php
<?php
declare(strict_types=1);

namespace driver\logistics\YourLogistics;

use crmeb\services\logistics\BaseExpress;
use crmeb\utils\Http;

class Driver extends BaseExpress
{
    public function query(string $no = '', string $type = '', string $appCode = '')
    {
        // 签名、HTTP、解析第三方 JSON
        // 失败：返回与 AliyunExpress 同形的空结构，或 setError + false（看参考驱动风格）
    }

    public function open() {}
    public function dump($data) {}
    public function temp(string $com) {}
}
```

### 5.1 `query()` 签名（现役约定）

| 参数 | 含义 |
| --- | --- |
| `$no` | 运单号 |
| `$type` | 快递公司编码（`eb_express.code`）；阿里云可传空由接口识别 |
| `$appCode` | 仅阿里云分支：`ExpressServices` 从 `config_fields` 里读 `app_code` 传入；一号通分支改传 `$com` + 收件手机 |

一号通实现见 `driver/logistics/Yihaotong/Driver.php` 的 `query(string $num, ?string $com, $phone)`——与基类文档签名略有出入，门面按位置传参，新驱动应对齐**调用方**而非 DEVELOPMENT 旧表。

### 5.2 业务层期望的返回形状

`ExpressServices::query()` 最终给前端的是**统一节点数组**：

```php
[
    ['time' => '2026-01-01 10:00:00', 'status' => '节点描述'],
    // ...
]
```

中间转换（必须在核心白名单内或用户同意扩展后实现）：

| driver_key | 驱动原始返回 | 核心提取逻辑 |
| --- | --- | --- |
| `aliyun_express` | `['result' => ['list' => [...], 'deliverystatus' => n]]` | `list` 每项映射为 `time`/`status`；`deliverystatus >= 3` 时长缓存设为永久 |
| `yihaotong` | `['content' => [...], 'ischeck' => 0\|1]` | `content` 映射；`ischeck == 1` 时长缓存永久 |

新驱动若接入核心，需在 `switch` 中增加 case，并把第三方字段映射为上述 `time`/`status`。

### 5.3 可选方法

| 方法 | 用途 |
| --- | --- |
| `open()` | 一号通「开通物流」；纯 AppCode 类 API 可空实现 |
| `express()` | 拉取快递公司列表；`ExpressServices::getPlatExpress()` 通过**默认面单驱动**调用，不是 logistics 启用驱动 |
| `dump()` / `temp()` | 面单能力；纯查询驱动留空 |

## 6. 配置存储

| 存储 | 内容 |
| --- | --- |
| `eb_driver` | `driver_type=logistics`，`config_fields` 含管理员填写的 `value` |
| Redis 缓存 | `logistics:{driver_key}:{cacheName}`，轨迹；空结果另有 `{...}:empty:{md5}` 120 秒 |

禁止把 AppCode、密钥写入 `eb_system_config`（一号通账号除外，那是平台级 `yihaotong_appid` / `yihaotong_appsecret`）。

## 7. 调用链（app 层）

```text
用户端/管理端查物流
  → app/services/shipping/ExpressServices::query($cacheName, $expressNum, $com, $phone)
  → resolveEnabledQueryDriver()  // 白名单 + 插件启用校验 ModuleStateChecker
  → app()->make(Express::class, [$driverKey, ...])
  → driver\logistics\{Studly}\Driver::query(...)
  → 写缓存 → 返回节点数组

订单详情首屏
  → ExpressServices::getCachedTracks()  // 只读缓存，不调第三方
```

相关只读入口：`StoreOrderServices`、售后物流、`api` 订单物流接口（均间接依赖 `ExpressServices`）。

## 8. 逐步开发流程

1. **定 driver_key**（小写蛇形，如 `kuaidi100`），运行 `Str::studly` 得到目录名 `Kuaidi100`。
2. **复制** `AliyunExpress` 目录为模板，改 `plugin.json` 的 `key`、`path`、`driver_key` 一致。
3. **实现 `query()`**：HTTP 用 `crmeb\utils\Http` 或 `extend/` 内 SDK；失败信息用 `$this->setError()` 或返回空结构（与参考驱动一致）。
4. **静态检查**（见第 10 节）。
5. **后台安装并启用**；在「物流查询驱动」页关闭另一个驱动（互斥）。
6. **若用户已授权改核心**：在 `ExpressServices` 增加 `case 'your_logistics':` 与字段映射；否则仅验证 `DriverRegistry::scan('logistics')` 与类加载。
7. **联调**：真实运单 + 正确快递公司编码；核对缓存 key 切换驱动后不会串数据。

## 9. 参考实现要点

**AliyunExpress**（最小查询驱动）：

- `query()` 始终返回数组，含 `result.list`，避免业务对 `false` 取下标。
- AppCode 可从第三参或 `config_fields` 回退读取。

**Yihaotong**（平台账号型）：

- `initialize()` 内用 `sys_config` / 构造参数组装 `AccessTokenServeService`。
- 另含 `dump()`/`temp()`/`express()` 等面单能力；若只做查询，仍建议与 express 分册协同维护 logistics 副本。

## 10. 打包与交付（`.plugin-packages/`）

首版阶段 `version` 固定 `1.0.0`，不写 `upgrades/`（见 [架构规范「当前发布阶段」](../../../../rules/架构规范.md)）。

```bash
cd backend
php -l driver/logistics/YourLogistics/Driver.php
php think plugin:manifest-check

mkdir -p ../.plugin-packages
zip -r ../.plugin-packages/driver-logistics-your_logistics-1.0.0.zip driver/logistics/YourLogistics/
```

交付物：插件 zip + 说明是否已改 `ExpressServices`。`git diff` 不应含未授权的 `app/`、`crmeb/` 改动。

安装验证（开发库）：

```bash
curl -X POST 'http://127.0.0.1:8011/adminapi/plugin/install' \
  -H 'Authori-zation: Bearer <token>' \
  -d 'plugin_key=driver:logistics:your_logistics'
```

## 11. 验收清单

静态（必须）：

- [ ] 目录名 = `Str::studly(driver_key)`；`key` = `driver_key` = `path` 末段
- [ ] `php think plugin:manifest-check` 通过
- [ ] 驱动内无 `use app\`、`use modules\`、无写业务表
- [ ] `query()` 在缺密钥/空单号时行为明确（不抛未捕获异常）
- [ ] 清单与代码无真实密钥

安装后：

- [ ] `DriverRegistry::scan('logistics')` 可见且 `status=1`（启用且插件未停用）
- [ ] 互斥：同类型仅一个启用
- [ ] **已改核心或仍在白名单内**：订单物流页出现节点；切换驱动后缓存前缀含新 `driver_key`
- [ ] **未改核心**：启用非白名单 key 时接口抛「物流查询驱动不支持」（与预期一致）

## 12. 常见失败现象

| 现象 | 可能原因 |
| --- | --- |
| 后台看不到驱动 | 未安装插件；`owner_plugin_id` 为空；插件未启用 |
| `Driver [xxx] not supported.` | 目录名与 `Str::studly(driver_key)` 不一致 |
| 启用后查物流报「物流查询驱动不支持」 | `driver_key` 不在白名单；需改 `ExpressServices` |
| 永远无轨迹、不报错 | 驱动返回结构未被 `switch` 解析；或命中 `:empty:` 短缓存 |
| 切换驱动后轨迹「串台」 | 旧缓存 key 未带 `driver_key`（现役已隔离；勿改回全局 key） |
| 一号通报缺少凭证 | `yihaotong_appid`/`secret` 未在后台一号通配置 |
| 阿里云始终空列表 | AppCode 未保存到 `config_fields.value`；或 `app_code` 字段名不一致 |

## 13. 与 express / 一号通的关系

- 电子面单发货：`ExpressDriverServices::createExpressService()` → 同样走 `crmeb\services\logistics\Express`，类在 `driver/logistics/`。
- 同一 `driver_key`（如 `yihaotong`）可同时在 `eb_driver` 存在 `logistics` 与 `express` 两行（不同 `driver_type`），实现类通常两份目录各一份 `Driver.php`，逻辑高度相似。
- 纯新增「只查轨迹」第三方：通常只需 `driver/logistics/`，不必建 `express`，除非还要面单。

## 14. LogisticsDriverServices 与启用

- 列表/启用/保存配置：`app/services/driver/logistics/LogisticsDriverServices.php`。
- `getEnabledDriver()` 返回单行；与 `resolveEnabledQueryDriver()` 配合校验 `owner_plugin_id` 与 `ModuleStateChecker::isDriverActive()`。
- 插件停用后即使 `status=1` 也不查第三方，缓存读取同样返回空。

## 15. 阿里云与一号通 query 参数对照

| 驱动 | ExpressServices 调用方式 |
| --- | --- |
| aliyun_express | `query($expressNum, '', $appCode)`，第三参从驱动配置读取 |
| yihaotong | `query($expressNum, $com, $phone)`，依赖订单收件手机号与快递公司编码 |

顺丰等「手机后四位」验单：一号通分支传 `$phone`；阿里云分支看 API 是否支持扩展参数，扩展时仍需改 `ExpressServices` case。

## 16. 静态扫描命令汇总

```bash
cd backend
php think plugin:manifest-check
php think arch:check    # 驱动内禁止 use app/modules
```

安装后：

```bash
cd backend && php -r 'require "vendor/autoload.php"; (new think\App(getcwd()."/"))->initialize();
foreach (crmeb\services\driver\DriverRegistry::scan("logistics") as $k => $d) {
  echo $k, " status=", $d["status"] ?? "-", " class=", crmeb\services\driver\DriverRegistry::getDriverClass("logistics", $k), PHP_EOL;
}'
```
