# 驱动分册 · 商品采集（copyproduct）

核对日期：2026-11-06。先读 [00-总览](00-总览-类型路由与边界.md) 第 3 节（驱动通用机制），本篇只写从电商平台 URL 采集商品专属内容。

## 1. 选型判定

| 需求 | 落本类型 |
| --- | --- |
| 粘贴淘宝/京东/拼多多等链接，拉取标题/图/规格/详情进商品编辑页 | `backend/driver/copyproduct/{Dir}/` |
| 自己写爬虫服务、改商品保存规则 | 驱动只负责 `copy()` 返回结构；保存逻辑在 `CopyProductServices` |
| 一号通「平台服务」账号 | 内置 `yihaotong` 驱动，配置走 `sys_config` |

copyproduct 是少数**可零侵入接入**的类型：`CopyProductServices::invokeCopyDriver()` 经 `DriverRegistry::getDriverClass()` 动态实例化，无 `switch` 白名单。

## 2. 开工前必须告诉用户

| 项 | 说明 |
| --- | --- |
| 一号通特殊配置 | `driver_key === 'yihaotong'` 时配置用 `yihaotong_config()`，不读驱动表 `value` |
| 启用策略 | 同类型互斥（与 logistics 类似）；无启用时业务**回退** `yihaotong` |
| 平台列表 | 后台展示可读 `plugin.json` 的 `platforms`；`Copy99api` 等也在 `getPlatforms()` 返回 |
| 管理端入口 | `CopyProductController` 部分接口仍直接调 `Serve`（一号通账户信息），与采集驱动并行 |

若新驱动要替换一号通成为默认，只需在后台启用并关闭其他 copyproduct 驱动；不必改 `invokeCopyDriver`（除非新 key 也要走全局账号）。

## 3. 文件清单

```text
backend/driver/copyproduct/{Dir}/
├── plugin.json
└── Driver.php    # extends crmeb\services\copyproduct\CopyProduct
```

| 组件 | 路径 |
| --- | --- |
| 抽象基类 | `crmeb\services\copyproduct\CopyProduct` |
| 业务入口 | `app/services/product/product/CopyProductServices.php` |

现役：

| 目录 | driver_key | 说明 |
| --- | --- | --- |
| `Copy99api/` | `copy99api` | 多平台 API Key |
| `Yihaotong/` | `yihaotong` | 官方一号通，需开通采集 |

**推荐照抄**：`Copy99api/`（纯 `config_fields`、返回结构与业务对齐完整）。

## 4. plugin.json

```json
{
    "key": "your_copy",
    "name": "某某商品采集",
    "description": "对接某某采集 API",
    "version": "1.0.0",
    "author": "作者",
    "type": "driver",
    "path": "driver/copyproduct/YourCopy",
    "default_install": false,
    "require": { "crmeb": "^8.0", "php": "^8.0" },
    "driver": {
        "name": "某某商品采集",
        "driver_key": "your_copy",
        "sort": 90,
        "description": "支持淘宝、京东链接采集",
        "capabilities": {
            "open": false,
            "multi_platform": true,
            "cache": true
        },
        "platforms": [
            {"name": "淘宝", "key": "taobao", "domain": "taobao.com", "icon": "icon-taobao"},
            {"name": "京东", "key": "jd", "domain": "jd.com", "icon": "icon-jd"}
        ],
        "config_fields": [
            {
                "field": "your_copy_apikey",
                "label": "API Key",
                "type": "password",
                "required": true,
                "group": "密钥配置",
                "default": ""
            }
        ],
        "help_doc": {
            "title": "使用说明",
            "steps": ["注册采集平台", "填写 API Key", "启用本驱动"],
            "link": ""
        }
    }
}
```

copyproduct **专属** manifest 字段：

| 字段 | 说明 |
| --- | --- |
| `platforms` | 后台展示支持平台；`domain` 用于链接识别提示 |
| `capabilities.open` | 是否需要「开通服务」（一号通 true） |
| `capabilities.multi_platform` | 是否多平台 |
| `capabilities.cache` | 是否使用缓存（驱动内可选实现） |

## 5. Driver.php 方法契约

基类要求三个方法：

```php
<?php
declare(strict_types=1);

namespace driver\copyproduct\YourCopy;

use crmeb\services\copyproduct\CopyProduct;

class Driver extends CopyProduct
{
    public function copy($url, array $options = [])
    {
        // 解析链接 → 请求 API → 归一化
        return ['status' => true, 'data' => $productInfo];
        // 或 ['status' => false, 'msg' => '原因'];
    }

    public function getPlatforms(): array
    {
        return [/* 与 plugin.json platforms 对齐 */];
    }

    public function getCapabilities(): array
    {
        return ['open' => false, 'multi_platform' => true, 'cache' => true];
    }
}
```

### 5.1 构造与配置

```php
// CopyProductServices::invokeCopyDriver
$config = $driverKey === 'yihaotong' ? yihaotong_config() : ($driver['config_fields'] ?? []);
app()->make($class, [$config])->copy($url);
```

非 yihaotong 时 `$config` 是 **带 `field`/`value` 的列表**或关联数组，驱动内需兼容（参考 `Copy99api::getApikey()`）。

### 5.2 `copy()` 成功时 `$data` 关键字段

业务会使用/补全的字段（须尽量提供）：

| 字段 | 说明 |
| --- | --- |
| `store_name` | 商品名 |
| `store_info` | 简介 |
| `image` | 主图 URL |
| `slider_image` | 轮播图 URL 数组 |
| `price` / `ot_price` | 售价/原价 |
| `description` | 详情 HTML |
| `items` | 规格维度 `[['value'=>'颜色','detail'=>[...], 'add_pic'=>0\|1], ...]` |
| `attrs` / `info.value` | SKU 组合（一号通/99API 结构略有差异，业务会读 `info.value`） |

失败统一：

```php
['status' => false, 'msg' => '可读错误说明']
```

### 5.3 链接识别

`CopyProductServices` 自带 `$host` 关键字列表；驱动内通常自行 `parseLink()`（见 `Copy99api`）。不支持的平台返回明确 `msg`。

### 5.4 可选 `open()`

一号通驱动实现开通接口；`CopyProductController` 开通流程走 `Serve`，不仅限驱动类。

## 6. 配置存储

| 存储 | 内容 |
| --- | --- |
| `eb_driver`（copyproduct） | 第三方 API Key 等 |
| `sys_config` | 一号通 `yihaotong_appid` / `secret` |
| 缓存 | 驱动内可用 `CacheService`（按 capabilities.cache） |

## 7. 调用链

```text
管理端「商品采集」
  → CopyProductController → CopyProductServices::copyProduct($type, $id, $shopid, $url)
  → CopyDriverServices::getEnabledDrivers()[0]
  → driver_key 空则 yihaotong
  → invokeCopyDriver → DriverRegistry → Driver::copy($url)
  → 成功则组装 productInfo（分类树、tempList、items 规格图转换等）
  → 返回前端商品编辑草稿
```

采集成功后图片仍可能走 `uploadDescriptionImage()` 拉远程图到附件库（依赖上传驱动）。

## 8. 逐步开发流程

1. 定 `driver_key`（蛇形）与 `{Dir}`（Studly）。
2. 复制 `Copy99api`，改 API 端点与 `parseLink()`。
3. 编写 `plugin.json`：`platforms` + `capabilities` + `config_fields`。
4. 保证 `getPlatforms()` / `getCapabilities()` 与 manifest 一致。
5. 静态检查；安装；后台**启用**并关闭其他采集驱动（若需切换）。
6. 管理端粘贴各平台测试链接；核对多规格、`items[].detail` 带图场景。
7. 保存商品前检查价格、库存默认值是否符合预期。

## 9. 参考实现

- `driver/copyproduct/Copy99api/Driver.php`：平台 API 表、缓存、SKU 归一化。
- `driver/copyproduct/Yihaotong/Driver.php`：一号通 HTTP、开通逻辑。
- `CopyProductServices` 第 124–153 行：规格 `detail` 字符串与 `{value,pic}` 兼容。

## 10. 打包（`.plugin-packages/`）

```bash
cd backend
php -l driver/copyproduct/YourCopy/Driver.php
php think plugin:manifest-check
zip -r ../.plugin-packages/driver-copyproduct-your_copy-1.0.0.zip driver/copyproduct/YourCopy/
```

## 11. 验收清单

- [ ] 四名字对齐；实现基类三个方法
- [ ] `copy()` 失败必带 `msg`；成功 `status===true` 且 `data.store_name` 非空
- [ ] 无 `use app\`；密钥不出现在日志
- [ ] 启用后 `getEnabledDrivers()` 首条为新驱动
- [ ] 淘宝/京东/拼多多等声明平台各测一条链接
- [ ] 多规格商品 `items`/`attrs` 在编辑页展示正常
- [ ] 与一号通切换互斥无「静默回退 yihaotong」（除非未启用）

## 12. 常见失败现象

| 现象 | 原因 |
| --- | --- |
| 总是走一号通 | 无启用驱动；或启用的是 yihaotong |
| `采集驱动不可用` | 类不存在；插件停用；driver_key 与目录不符 |
| `请先配置99API Key` | `config_fields` 未保存 value；驱动未读 `field` 列表 |
| `暂不支持该平台` | URL 域名不在驱动 hosts |
| 规格空白 | 返回 `items`/`info.value` 结构与业务预期不符 |
| 详情图仍外链 | 未点「下载详情图」；上传驱动未配置 |
| 开通按钮无效 | 一号通账户未登录；与驱动 `open` 能力无关 |

## 13. 与一号通其他服务

同一 `yihaotong_appid`/`secret` 可用于短信、物流、面单、发票；采集驱动 `Yihaotong` 仅消费采集 API。第三方采集驱动与之独立，互不影响启用状态。

## 14. 管理端 API 与驱动边界

| 入口 | 行为 |
| --- | --- |
| `CopyProductServices::copyProduct` | 走 `invokeCopyDriver`，**动态驱动** |
| `CopyProductController` 账户/开通 | 常调 `Serve` + `yihaotong_config()`，与是否启用 99API 无关 |
| 复制成功后保存商品 | `CenterProductServices` 等，非驱动职责 |

二开采集驱动一般无需改 Controller；仅当新平台需额外 OAuth 参数时才考虑扩展接口（改 app）。

## 15. 缓存与限流建议

`Copy99api` 在驱动内对详情结果做缓存 key；第三方限流时返回明确 `msg`，避免 `CopyProductServices` 抛空 `AdminException`。capabilities.cache 为 true 时建议在驱动内使用 `CacheService`，key 含 url hash 与 driver_key。

## 16. 本地验证

```bash
cd backend && php -r 'require "vendor/autoload.php"; (new think\App(getcwd()."/"))->initialize();
echo crmeb\services\driver\DriverRegistry::getDriverClass("copyproduct", "copy99api"), PHP_EOL;
foreach (crmeb\services\driver\DriverRegistry::scan("copyproduct") as $k => $d) {
  echo $k, " status=", $d["status"] ?? "-", PHP_EOL;
}'
```

有 API Key 时可在 tinker 式脚本中 `app()->make($class, [$configFields])->copy("https://...")` 看原始返回，再对照第 5.2 节字段表。

## 17. 规格图 `add_pic`

业务层根据 `items[].detail` 是否含 `pic` 自动置 `add_pic=1`；驱动若返回带图规格，应使用 `{value, pic}` 对象数组，与 99API 一致。纯字符串 detail 不会开启规格图上传。

## 18. 互斥与 default_install

内置 `yihaotong` 常 `default_install: true` 且 `is_builtin: true`；第三方驱动安装后默认关闭，管理员启用时会关闭其他 copyproduct 驱动。测试 99API 时确认后台仅启用一条，避免误读「仍走一号通」。
