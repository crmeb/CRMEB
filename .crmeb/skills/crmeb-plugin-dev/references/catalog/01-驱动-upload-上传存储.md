# 驱动分册 · 上传存储（upload）

核对日期：2026-11-06。先读 [00-总览](00-总览-类型路由与边界.md) 第 3 节（驱动通用机制）。本篇按「可照着新增一个驱动」补齐文件清单、契约与验收。

## 1. 何时用 / 何时不用

**适合做成 upload 驱动：**

- 商品图、用户头像、海报/二维码、导入文件等需要 **统一上传 / 删除 / 生成访问 URL**，换 OSS/COS/MinIO 等对象存储。
- 业务入口已统一：`UploadService::init()`、`getOssInit($url)`，素材库写入由 `SystemAttachmentServices` 完成，驱动只返回 `getUploadInfo()` 结构。

**不要做成 upload 驱动：**

- 改附件权限、分类、引用计数 → `app/services/system/attachment/`，不是驱动。
- 同一站点 **同时** 用 OSS 传图、本地传用户文件 → 存储驱动 **全局互斥**，仅一个 `status=1`；多源分流要改产品/核心，先问用户。
- 只在单一模块用一次的文件工具 → 模块内处理，不必新开驱动。

## 2. 开工前必须问用户的核心写死点

| 写死点 | 为何要问 | 代码依据 |
| --- | --- | --- |
| 公有读还是私有桶 + 签名 URL | 现役云驱动默认公有读 URL；私有桶要在驱动内做签名或走 CDN | 各云 `Driver.php` |
| 缩略图 / 水印 | 云存储常只做 URL 参数（Cos）或跳过；Local 用 GD 落盘 | `BaseUpload::getUploadInfo()`、`thumb()` |
| 是否要实现 `stream` | 海报/远程抓取会调 `stream()`；未声明 `capabilities` 含 `stream` 也可实现，但要与用户预期一致 | `UploadService`、Cos `stream` |
| Bucket 管理 API | `bucket_create` 等 capability 影响后台「空间同步」按钮是否可用 | `plugin.json` capabilities |
| 字段命名习惯 | 历史驱动用 `tengxun_accessKey` 等；`normalizeDriverConfig()` 会映射到 `accessKey`/`secretKey` | `UploadDriverServices::normalizeDriverConfig()` |
| SDK 放哪 | 大 SDK 放 `extend/`，禁止改根 `composer.json` | 总览 3.7、`driver/upload/Cos/extend/` |

## 3. 三层结构（先建立心智模型）

| 层 | 位置 | 作用 |
| --- | --- | --- |
| 清单 | `driver/upload/{Dir}/plugin.json` | 表单字段、空间字段、域名模板、能力声明 |
| 字典 | `eb_driver`（`driver_type=upload`） | 运行时真相；`DriverRegistry::scan()` **只读表** |
| 空间 | `eb_upload_storage` | 每个 bucket 的 `name/region/domain/cdn`，启用云驱动前必须有 `status=1` 的空间 |
| 类解析 | `crmeb\services\upload\Upload` | `Str::studly($driver_key)` → `driver\upload\{Dir}\Driver` |

**只拷贝目录不安装插件** → 字典无行、Registry 扫不到 → 后台列表没有新驱动；点「同步存储驱动」**不会新增行**（只更新已有行的元数据）。

## 4. 运行时链路

```text
Controller / Job
  → UploadService::init($typeOrKey|null)
  → UploadDriverServices::getEnabledDriverKey() / resolveDriverKey()
  → loadDriverConfig()：eb_driver.config_fields + eb_upload_storage 当前空间
  → normalizeDriverConfig()
  → new Upload($driverKey, $config)
  → Driver::to($path)->validate()->move('file')
  → getUploadInfo() → SystemAttachmentServices 写 eb_system_attachment
```

关键文件：

- 门面：`backend/crmeb/services/upload/Upload.php`
- 基类：`backend/crmeb/services/upload/BaseUpload.php`
- 编排：`backend/app/services/driver/upload/UploadService.php`
- 管理：`backend/app/services/driver/upload/UploadDriverServices.php`
- 空间：`backend/app/services/driver/upload/UploadStorageServices.php`
- 后台：`backend/app/adminapi/route/driver.php` → `upload_driver/*`、`upload_storage/*`
- 上传接口：`POST /adminapi/file/upload/[:upload_type]`（`0` = 当前启用驱动）

## 5. 目录与命名

```text
backend/driver/upload/{Dir}/
├── plugin.json
├── Driver.php              # extends BaseUpload
└── extend/                 # 可选，如 extend/cos/Client.php
```

| driver_key | {Dir} | 说明 |
| --- | --- | --- |
| `local` | `Local` | 内置默认 |
| `oss` | `Oss` | 阿里云 |
| `cos` | `Cos` | 腾讯云，含 extend |
| `minio` | `Minio` | 示例：key 与目录 studly 一致 |

```bash
cd backend && php -r 'require "vendor/autoload.php"; echo \think\helper\Str::studly("s3_minio"), PHP_EOL;'
```

## 6. plugin.json 完整示例与字段表

```json
{
    "key": "your_oss",
    "name": "某某对象存储",
    "description": "S3 兼容 / 私有云对象存储",
    "version": "1.0.0",
    "author": "作者",
    "type": "driver",
    "path": "driver/upload/YourOss",
    "default_install": false,
    "require": { "crmeb": "^8.0", "php": "^8.0" },
    "driver": {
        "name": "某某对象存储",
        "driver_key": "your_oss",
        "class_name": "Driver",
        "icon": "icon-storage",
        "sort": 60,
        "domain_template": "https://{name}.{region}.example.com",
        "capabilities": ["upload", "delete", "stream", "thumb", "water"],
        "config_fields": [
            {
                "field": "your_oss_accessKey",
                "label": "AccessKey",
                "type": "text",
                "required": true,
                "group": "密钥配置"
            },
            {
                "field": "your_oss_secretKey",
                "label": "SecretKey",
                "type": "password",
                "required": true,
                "group": "密钥配置"
            },
            {
                "field": "your_oss_endpoint",
                "label": "Endpoint",
                "type": "text",
                "required": true,
                "group": "服务配置",
                "tip": "不含 bucket 名，如 https://minio.example.com"
            }
        ],
        "bucket_fields": [
            { "field": "name", "label": "空间名称", "type": "text", "required": true },
            { "field": "region", "label": "区域", "type": "text", "required": false }
        ],
        "thumb_watermark_config": {},
        "help_doc": {
            "title": "接入说明",
            "steps": [
                "创建 Bucket 并设置访问权限",
                "填写密钥与 Endpoint",
                "在「空间配置」添加 Bucket 并填写访问域名",
                "保存配置后启用驱动"
            ],
            "link": ""
        }
    }
}
```

**upload 专属 `driver` 字段：**

| 字段 | 说明 |
| --- | --- |
| `domain_template` | 后台添加空间时自动生成 `domain` 的模板；占位符 `{name}` `{region}` `{appid}` |
| `bucket_fields` | 「空间配置」动态表单；与 `eb_upload_storage` 列对应 |
| `capabilities` | 如 `upload`、`delete`、`stream`、`thumb`、`water`、`bucket_list`、`temp_keys` 等；影响后台能力展示与部分同步功能 |
| `thumb_watermark_config` | 安装时可带默认；运行时在 `eb_driver.thumb_watermark_config` 保存缩略图/水印开关 |
| `region_map` | 可选，Cos 等用于区域中文名 |

`config_fields` 保存后写入 `eb_driver.config_fields[].value`。保存接口白名单校验，未声明字段会被拒。

## 7. Driver.php 契约

### 7.1 基类抽象方法（必须实现）

```php
// crmeb\services\upload\BaseUpload
abstract public function upload(string $filePath, string $key);
abstract public function delete(string $key);
abstract public function getFilePath(string $key);
abstract public function getFileInfo();
```

### 7.2 业务实际上传链（必须对齐现役驱动）

管理端与用户端上传走 **`move()`**，不是直接调 `upload()`：

```php
public function to(string $path): self;      // 设置 $this->path（如 image/2026/11）
public function validate(?array $rules = null): self;
public function move(string $file = 'file', $realName = false);  // 读 $_FILES，校验，上传
```

可选但常见：

```php
public function stream($fileContent, ?string $key = null);  // 流式写入
protected function initialize(array $config);  // 读密钥 + uploadUrl/storageName/storageRegion/cdn
```

`initialize()` 内应调用 `parent::initialize($config)`，并赋值：

- 来自 `config_fields`：`accessKey`、`secretKey`、`endpoint` 等（经 `normalizeDriverConfig` 后可能有短名）
- 来自空间表：`uploadUrl`（访问域名前缀）、`storageName`（bucket）、`storageRegion`、`cdn`

### 7.3 成功上传必须赋值的 `fileInfo`

`BaseUpload::getUploadInfo()` 依赖：

| 属性 | 含义 |
| --- | --- |
| `fileName` | 对象键 / 存储路径（入库 `name`） |
| `realName` | 用户原始文件名 |
| `filePath` | 可访问完整 URL（入库 `dir` / `att_dir`） |
| `filePathMid` | 中图；无则回落 `filePath` |
| `filePathWater` | 水印图；开启水印时可能替换对外 `filePath` |

少赋 `realName` → 素材库文件名列异常。云存储无图片服务时，`thumb()` 可返回原 URL。

### 7.4 最小骨架（签名真实）

```php
<?php
declare(strict_types=1);

namespace driver\upload\YourOss;

use crmeb\services\upload\BaseUpload;

class Driver extends BaseUpload
{
    protected function initialize(array $config)
    {
        parent::initialize($config);
        // $this->accessKey = $config['accessKey'] ?? '';
    }

    public function to(string $path) { $this->path = $path; return $this; }

    public function validate(?array $rules = null)
    {
        $this->validate = $rules ?: [
            'fileExt'  => config('upload.fileExt'),
            'fileMime' => config('upload.fileMime'),
            'filesize' => config('upload.filesize'),
        ];
        return $this;
    }

    public function move(string $file = 'file', $realName = false)
    {
        // 参照 Local/Cos：取 request->file()，校验，put 对象，填充 $this->fileInfo
        return $this->fileInfo;
    }

    public function upload(string $filePath, string $key) { /* ... */ }
    public function delete(string $key) { /* ... */ }
    public function getFilePath(string $key): string { /* ... */ }
    public function getFileInfo() { return $this->fileInfo; }
}
```

完整实现请对照 `backend/driver/upload/Cos/Driver.php` 或 `Local/Driver.php`。

## 8. 配置与表

| 表 | 内容 |
| --- | --- |
| `eb_driver` | `driver_type=upload`；`config_fields`、`thumb_watermark_config`、`status` |
| `eb_upload_storage` | `driver_key`、`name`、`region`、`domain`、`cdn`、`status`、`is_delete` |
| `eb_system_attachment` | 上传结果；`image_type` 由 `getTypeDriverKeyMap()` 按 **驱动 id 升序** 映射，**非稳定枚举** |

`UploadDriverServices::saveDriverConfig()` / `validateDriverConfig()`：只允许 JSON 里声明过的 `field`；敏感字段未改时可传占位符 `********` 保留原值。

启用云驱动时 `UploadDriverServices::update()`：

1. `DriverRegistry::get('upload', $driver_key)` 必须存在（插件已装且 enable）。
2. 非 `local` 必须有 **使用中** 的空间：`upload_storage` 且 `status=1`、`is_delete=0`。
3. 启用成功会 **`disableAllExcept($id)`**，全局仅一个存储驱动开启。

## 9. 从安装到冒烟（严格顺序）

### 9.1 开发与静态检查

```bash
cd backend
php -l driver/upload/YourOss/Driver.php
php think plugin:manifest-check
```

### 9.2 安装插件

```bash
curl -X POST 'http://192.168.1.169/adminapi/plugin/install' \
  -H 'Authori-zation: Bearer <token>' -d 'plugin_key=driver:upload:your_oss'
curl -X POST 'http://192.168.1.169/adminapi/plugin/enable' \
  -H 'Authori-zation: Bearer <token>' -d 'plugin_key=driver:upload:your_oss'
```

查库：

```sql
SELECT driver_key, namespace, owner_plugin_id, status
FROM eb_driver WHERE driver_type = 'upload' AND driver_key = 'your_oss';
```

### 9.3 配置 → 空间 → 启用（顺序不可反）

1. **保存驱动配置**（密钥、Endpoint）→ `POST upload_driver/save_config/:id`
2. **空间配置** → 添加 bucket，`domain` 填浏览器可访问前缀（错误则 `att_dir` 永久 404）
3. **启用驱动** → `PUT upload_driver/set_status/:id`；若报「未有正在使用的存储空间」，先开空间再启用

### 9.4 上传冒烟

```bash
curl -X POST 'http://192.168.1.169/adminapi/file/upload/0' \
  -H 'Authori-zation: Bearer <token>' \
  -F 'file=@./test.png' -F 'pid=0'
```

期望：`data.src` 可打开；库中 `att_dir` 为完整 URL（云存储）或带站点路径（local）。

### 9.5 删除与按 URL 识别

- 删素材：走 attachment 服务 → 驱动 `delete()`
- 海报/迁移：`UploadService::getOssInit($url)` 按 `domain`/`cdn` 匹配空间；多匹配抛「文件地址匹配多个存储空间」

## 10. 参考实现对照表

| 目录 | driver_key | 建议 |
| --- | --- | --- |
| `Local/` | `local` | 最小 `move()`、GD 缩略图与水印 |
| `Oss/` | `oss` | 阿里云 API 字段命名 |
| `Cos/` | `cos` | 云存储 + `extend/cos` + URL 缩略图 |
| `Qiniu/` | `qiniu` | 七牛 |
| `Obs/` | `obs` | 华为，含 extend |
| `Jdoss/` | `jdoss` | 京东云 |
| `Tyoss/` | `tyoss` | 天翼云 |

**新增 S3 兼容：** 结构照 `Cos/`，HTTP 客户端照 `Cos/extend/cos/Client.php`；字段与 `domain_template` 按厂商改。

## 11. 打包交付

```bash
cd backend/driver/upload/YourOss
zip -r "../../../../.plugin-packages/某某存储-your_oss-1.0.0.zip" . \
  -x "*.DS_Store" -x "__MACOSX/*"
```

解压后根目录即 `plugin.json`。勿打整个 `backend/` 或带 `.env`。

## 12. 验收清单

**静态（必须全部通过）：**

- [ ] `php -l`、`php think plugin:manifest-check` 通过
- [ ] 目录名 = `Str::studly(driver_key)`；四个名字对齐
- [ ] 四个抽象方法 + `move()`/`to()`/`validate()` 已实现且签名与基类/现役驱动一致
- [ ] `getUploadInfo()` 所需 `fileInfo` 字段在上传成功路径均已赋值
- [ ] 驱动不写 `eb_system_attachment`；无 `use app\` / `use modules\`
- [ ] 大 SDK 仅在 `extend/`；未改根 `composer.json`
- [ ] `capabilities` 与真实能力一致（声明 `stream` 则 `stream()` 可用）

**开发库冒烟：**

- [ ] 安装启用后 Registry 可见；`owner_plugin_id` 非空
- [ ] 配置保存、空间开启、驱动启用三步完成
- [ ] `file/upload/0` 成功；`att_dir` 浏览器可访问
- [ ] 删除素材后对象存储侧对象消失（或符合私有桶策略）
- [ ] 切换回 `local` 前若卸载云驱动，先切换驱动避免上传断链

## 13. 常见失败现象对照

| 现象 | 原因 | 处理 |
| --- | --- | --- |
| 后台无新驱动 | 未安装插件；Registry 不扫目录 | plugin install + enable |
| 点「同步」仍无新行 | `syncDrivers` 故意不 INSERT | 必须走插件安装 |
| `请先启用对应的驱动插件` | 插件 disable 或 Registry 无记录 | 插件管理启用 |
| `未有正在使用的存储空间` | 未添空间或未 `status=1` | 空间配置 |
| `Driver [x] not supported.` | studly 与目录不一致 | 改目录或 key |
| 上传成功 URL 404 | `domain` / CDN 填错 | 改 `eb_upload_storage.domain` |
| `不支持的驱动配置字段` | POST 了 JSON 未声明字段 | 改 plugin.json 重装或手工改库定义 |
| 改 JSON 后表单无新字段 | `config_fields` 是安装快照 | 重装插件或 sync（upload 仅更新已有行） |
| 海报/二维码域名重复拼接 | `image_type==1` 假定 local；驱动 id 顺序变过 | 保持 local 最小 id；别用 image_type 做迁移键 |
| `文件地址匹配多个存储空间` | 两空间 domain 重叠 | 区分域名或路径前缀 |
| 卸载插件后上传崩 | 字典删了但仍是启用态 | 卸载前先切 local；清空间 |
| 敏感配置明文展示 | 字段名未命中脱敏规则 | 用 `password` 或 `secretKey` 命名 |

---

**一句话：** 上传驱动 = **`plugin.json` 定表单 + 安装写 `eb_driver` + 空间定域名 + `move()` 填好 `fileInfo`**；四者任一错位，表现都是上传失败，但报错各不相同。
