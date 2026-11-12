# 驱动分册 · 短信（sms）

核对日期：2026-11-06。先读 [00-总览](00-总览-类型路由与边界.md) 第 3 节（驱动通用机制），本篇只写短信专属内容。

## 1. 何时用 / 何时不用

**适合做成 sms 驱动：**

- 商城通知、验证码、营销短信等，业务侧只关心「手机号 + 模板编号 + 变量」，规则不变，只是换一家短信网关。
- 调用方已经固定：`SmsChannel`（通知管线 + 登录验证码）、少数业务直调 `SmsChannel::pushSms()`。

**不要做成 sms 驱动：**

- 要在后台做「模板申请 / 签名审核 / 发送记录查询」且希望走统一 UI → 一号通在 `serve/SmsController` 里写死了 `yihaotong`，新驱动没有这套页面（见第 2 节）。
- 需求是改通知编排、变量来源、谁收短信 → 改 `app/services/message/`，不是驱动。
- 国际短信、多签名按店铺切换、同一模板多驱动并行 → 当前模型是**全局互斥单驱动**，需先问用户是否接受产品约束或改核心。

## 2. 开工前必须问用户的核心写死点

短信属于可零侵入接入的类型，但下面几项会直接影响 `Driver.php` 和通知配置，开工前必须确认：

| 写死点 | 为何要问 | 代码依据 |
| --- | --- | --- |
| 模板变量是**命名**还是**位置**参数 | 阿里云/一号通用 JSON 命名参数；腾讯云在 `SmsChannel::doSend()` 里对 `driver_key === 'tencent'` 会 `array_values()` 转成位置数组 | `app/services/message/channel/SmsChannel.php` |
| 模板 ID 由谁维护 | 管理员在「通知设置 → 短信渠道」手工填 `sms_id`；没有驱动内拉模板列表的通用 API（一号通除外） | `NoticeReadiness::reasons()` |
| 切换驱动后旧模板是否作废 | 模板 extra 带 `sms_driver_key`，与当前启用驱动不一致则拒绝发送 | `SmsChannel::doSend()`、`NoticeReadiness` |
| 是否依赖宿主 `vendor/` SDK | 阿里云驱动直接用 `AlibabaCloud\SDK\Dysmsapi`（已在宿主 composer）；腾讯云自签 HTTP。新驱动选 SDK 还是 `extend/` 自封装 | `driver/sms/Aliyun/Driver.php`、`driver/sms/Tencent/Driver.php` |
| 一号通相关能力要不要 | `adminapi/serve/SmsController` 全部 `app()->make(Sms::class, [yihaotong_config()])`，与启用驱动无关 | `app/adminapi/controller/serve/SmsController.php` |

**仍写死在核心、插件改不了的分支（交付时说明即可）：**

- `Sms::invokeClass()` 总会构造 `AccessTokenServeService` 并注入构造函数，即使你的驱动不用一号通。
- 历史种子数据里 `sms_driver_key` 为空时，`NoticeSeedServices` 会默认补成 `yihaotong`，迁移环境切换驱动后要重配通知模板。
- `driver_key === 'yihaotong'` 时，`yihaotong_appid` / `yihaotong_appsecret` 读 `sys_config`，不是 `eb_driver.config_fields` 里保存的值。

## 3. 运行时链路（代码定位）

```text
通知 / 验证码
  → SmsChannel::send() / pushSms()
  → SmsDriverServices::getEnabledDriver()     # eb_driver，driver_type=sms，status=1，互斥仅一条
  → config_fields 摊平为 field => value
  → app()->make(Sms::class, [$driverKey, $config])
  → crmeb\services\sms\Sms::resolveClass()    # Str::studly($driverKey) → driver\sms\{Dir}\Driver
  → Driver::send($phone, $templateId, $data)
```

门面与基类：

- 门面：`backend/crmeb/services/sms/Sms.php`（`namespace` 前缀 `\driver\sms\`）
- 基类：`backend/crmeb/services/sms/BaseSms.php`（继承 `BaseStorage`，**无**抽象 `send`，但业务只调 `send`）
- 管理：`backend/app/services/driver/sms/SmsDriverServices.php`
- 后台路由：`backend/app/adminapi/route/driver.php` 下 `sms_driver/*`

## 4. 目录与命名

```text
backend/driver/sms/{Dir}/
├── plugin.json          # 必须
├── Driver.php           # 必须：namespace driver\sms\{Dir}; class Driver extends BaseSms
├── Installer.php        # 可选：仅自有表时（参考 yunxin，目录名小写是反例）
└── extend/              # 可选：自封装 HTTP/SDK
```

| 名字 | 规则 | 反例 |
| --- | --- | --- |
| `{Dir}` | 必须等于 `Str::studly(driver_key)`，如 `your_sms` → `YourSms`，`aliyun` → `Aliyun` | `driver/sms/yunxin/` 小写目录，Linux PSR-4 易失败 |
| `plugin.json` → `path` | `driver/sms/{Dir}` 与磁盘一致 | |
| 顶层 `key` 与 `driver.driver_key` | 建议相同 | 不一致时不报错，排查用复合 ID `driver:sms:{key}` |

确认 studly：

```bash
cd backend && php -r 'require "vendor/autoload.php"; echo \think\helper\Str::studly("your_sms"), PHP_EOL;'
```

## 5. plugin.json 完整示例与字段表

第三方驱动：`default_install: false`（或不写），`is_builtin` 不写或 `false`，装完默认 `status=0`。

```json
{
    "key": "your_sms",
    "name": "某某短信",
    "description": "某某短信网关驱动",
    "version": "1.0.0",
    "author": "作者",
    "type": "driver",
    "path": "driver/sms/YourSms",
    "default_install": false,
    "require": { "crmeb": "^8.0", "php": "^8.0" },
    "driver": {
        "name": "某某短信",
        "driver_key": "your_sms",
        "class_name": "Driver",
        "icon": "icon-sms",
        "sort": 50,
        "capabilities": ["send"],
        "config_fields": [
            {
                "field": "your_sms_sign_name",
                "label": "短信签名",
                "type": "text",
                "required": true,
                "group": "基础配置",
                "tip": "在短信控制台审核通过的签名"
            },
            {
                "field": "your_sms_access_key",
                "label": "AccessKey",
                "type": "text",
                "required": true,
                "group": "密钥配置"
            },
            {
                "field": "your_sms_secret",
                "label": "Secret",
                "type": "password",
                "required": true,
                "group": "密钥配置"
            }
        ],
        "help_doc": {
            "title": "某某短信接入说明",
            "steps": [
                "在短信控制台创建签名与模板",
                "获取 API 密钥并填入上方配置",
                "启用驱动后，在通知设置中为每条模板填写服务商模板编号"
            ],
            "link": ""
        }
    }
}
```

**顶层字段（与总览一致，短信无额外顶层字段）：**

| 字段 | 必填 | 说明 |
| --- | --- | --- |
| `key` | 是 | 插件标识，建议与 `driver_key` 相同 |
| `name` | 是 | 后台显示名 |
| `type` | 是 | 固定 `driver` |
| `version` | 是 | 首版阶段固定 `1.0.0` |
| `path` | 是 | 相对 `backend/`，且 realpath 必须存在 |
| `require` | 建议 | 宿主 PHP / CRMEB 版本 |
| `default_install` | 否 | 需用户密钥的第三方写 `false` |

**`driver` 子对象（安装时写入 `eb_driver`，见第 7 节）：**

| 字段 | 必填 | 说明 |
| --- | --- | --- |
| `driver_key` | 是 | 运行时 `$type`，`(driver_type, driver_key)` 唯一 |
| `name` | 否 | 缺省用顶层 `name` |
| `class_name` | 否 | 固定 `Driver` |
| `icon` / `sort` | 否 | 列表展示；`sort` 越大越靠前 |
| `is_builtin` | 否 | 第三方勿写 `true`；为真时安装策略见总览 3.4 |
| `capabilities` | 建议 | 短信至少声明 `["send"]`；一号通还声明 `template`、`record` 等，**业务不会因此调用** |
| `config_fields` | 是 | 后台动态表单；保存后每项多 `value` |
| `help_doc` | 建议 | `title` / `steps` / `link`，后台帮助区展示 |

`config_fields` 单字段：`field`（建议 `{driver_key}_` 前缀）、`label`、`type`（`text`/`password`/…）、`required`、`group`、`tip`、`default`、`show_if`。

## 6. Driver.php 契约

### 6.1 必须实现

```php
<?php
declare(strict_types=1);

namespace driver\sms\YourSms;

use crmeb\services\sms\BaseSms;

class Driver extends BaseSms
{
    protected function initialize(array $config = [])
    {
        parent::initialize($config);
        // $config 键名 = config_fields 的 field
    }

    /**
     * @param string $phone      收件手机号
     * @param string $templateId 通知模板 extra 中的 sms_id（服务商模板编号）
     * @param array  $data       模板变量；SmsChannel 已按模板内容 {var} 过滤
     * @return array|false 成功建议含 id/content/template；失败 return $this->setError('原因')
     */
    public function send(string $phone, string $templateId, array $data = [])
    {
        if ($phone === '') {
            return $this->setError('手机号不能为空');
        }
        // 调网关；失败：return $this->setError('服务商原文或可读原因');
        return [
            'id'       => '',
            'content'  => json_encode($data, JSON_UNESCAPED_UNICODE),
            'template' => $templateId,
        ];
    }
}
```

**构造函数不要覆写。** 门面注入签名为：

```php
// crmeb\services\sms\Sms::invokeClass()
Container::getInstance()->invokeClass($class, [
    $this->name,
    $handleAccessToken,  // AccessTokenServeService
    $this->configFile,
    $this->config,
]);
```

### 6.2 可选方法（仅一号通后台会用）

现役 `driver/sms/Aliyun/Driver.php` 有空实现的 `open()`、`modify()`、`temps()` 等；**通知发送不会调用**。若不做一号通式后台，不必实现。

### 6.3 参数格式要点

- `SmsChannel::parseData()` 按通知模板内容里的 `{变量名}` 从 `$data` 取**命名**键；模板内容为空则原样透传 `$data`。
- `driver_key === 'tencent'` 时，渠道层会把 `$data` 转为**有序位置数组**再交给驱动；新驱动若也是位置参数，应在驱动内自己 `array_values()`，不要指望改核心加分支。
- 腾讯云驱动 `send()` 成功时返回数组而非统一结构；渠道只判断 `!== false`，不解析返回值。

### 6.4 错误处理约定

- 通知路径：`send()` 返回 `false` → `SmsChannel` 读 `getError()` 抛 `NoticeSendException`。
- 验证码路径：`pushSms()` 捕获异常记日志并返回 `false`。
- **不要**像阿里云驱动那样在 `send()` 里抛 `ApiException`（会绕过 `setError`，上层表现不一致）。应 `return $this->setError(...)`。

## 7. 配置存在哪张表

| 存储 | 表 / 字段 | 说明 |
| --- | --- | --- |
| 驱动元数据 | `eb_driver`（`driver_type = 'sms'`） | 安装插件时 `installDriverRecord()` 写入；含 `namespace`、`owner_plugin_id`、`status` |
| 密钥与签名 | `eb_driver.config_fields` JSON | 每项定义 + 管理员保存的 `value` |
| 一号通账号 | `eb_system_config` | 仅 `yihaotong`：`yihaotong_appid`、`yihaotong_appsecret`；`SmsDriverServices::getEnabledDriver()` 运行时回填 |
| 通知模板 ID | 通知模块模板表 `extra` JSON | `sms_id`、`sms_driver_key`（保存通知时由 `NoticeConfigServices` 写入当前启用驱动） |

Dao 模型 `app/model/driver/sms/SmsDriver.php` 仍指向表名 `driver`，全局 scope 过滤 `driver_type=sms` 且 `owner_plugin_id <> ''`。

**互斥：** `SmsDriverServices::updateStatus()` 启用一条时会 `disableAllExcept($id)`，全局同时只有一个 `status=1` 的短信驱动。

## 8. 从安装到冒烟（逐步）

### 8.1 静态检查（不写库）

```bash
cd backend
php -l driver/sms/YourSms/Driver.php
php think plugin:manifest-check
php think arch:check    # 只看本次新增违规
```

### 8.2 安装与启用

1. 后台 **插件管理** 安装，或（管理员 token）：

```bash
curl -X POST 'http://192.168.1.169/adminapi/plugin/install' \
  -H 'Authori-zation: Bearer <token>' \
  -d 'plugin_key=driver:sms:your_sms'
curl -X POST 'http://192.168.1.169/adminapi/plugin/enable' \
  -H 'Authori-zation: Bearer <token>' \
  -d 'plugin_key=driver:sms:your_sms'
```

2. 后台 **设置 → 短信驱动**（`/adminapi/sms_driver/*`）：保存 `config_fields`，**启用**目标驱动（会自动关闭其他短信驱动）。

3. **通知设置**：对需要发短信的通知，在短信渠道填写服务商 **模板编号**（`sms_id`），模板内容占位符 `{code}` 等与服务商模板一致；保存后 extra 会带当前 `sms_driver_key`。

### 8.3 注册中心确认

```bash
cd backend && php -r 'require "vendor/autoload.php"; (new think\App(getcwd()."/"))->initialize();
foreach (crmeb\services\driver\DriverRegistry::scan("sms") as $k => $d)
  echo $k, " status=", $d["status"], " class=", crmeb\services\driver\DriverRegistry::getDriverClass("sms", $k), PHP_EOL;'
```

期望：`your_sms` → `driver\sms\YourSms\Driver`，且对应插件已启用。

### 8.4 冒烟

| 场景 | 操作 | 期望 |
| --- | --- | --- |
| 验证码 | H5 登录页发验证码（`LoginServices::verify` → `pushSms`） | 手机收到短信；失败时接口有可读错误 |
| 业务通知 | 触发一条已配置短信渠道的通知（如发货） | `SmsChannel::send` 成功；日志 `[短信诊断]` 无网关拒绝 |
| 切换驱动 | 启用另一短信驱动后再发 | 旧 `sms_driver_key` 模板应被就绪检查拦截，需重配 `sms_id` |

本地联调账号见工作区规则：H5 `http://localhost:5173/`，接口 `http://192.168.1.169`。

## 9. 参考实现对照表

| 目录 | driver_key | 何时照抄 |
| --- | --- | --- |
| `Aliyun/` | `aliyun` | 宿主 SDK + 命名模板参数；注意应改为 `setError` 而非抛异常 |
| `Tencent/` | `tencent` | 自签 TC3 + **位置参数** + E.164 手机号 |
| `Yihaotong/` | `yihaotong` | 一号通 token；配置在 `sys_config`，勿作第三方模板 |
| `Chuanglan/` | `chuanglan` | 创蓝 HTTP 示例 |
| `yunxin/` | `yunxin` | **反例**：目录小写，仅作 Installer 示例 |

## 10. 打包交付

只打包插件根目录，zip 根即 `plugin.json` 所在目录，输出到仓库根 `.plugin-packages/`（不入库）：

```bash
cd backend/driver/sms/YourSms
zip -r "../../../../.plugin-packages/某某短信-your_sms-1.0.0.zip" . \
  -x "*.DS_Store" -x "__MACOSX/*"
```

文件名：`{中文名}-{key}-{version}.zip`。首版阶段 `version` 保持 `1.0.0`。交付说明中列出：插件包路径、需管理员重配的通知模板、以及第 2 节一号通后台能力范围。

## 11. 验收清单

**静态（必须全部通过）：**

- [ ] `php -l driver/sms/{Dir}/Driver.php` 通过
- [ ] `php think plugin:manifest-check` 通过
- [ ] 目录名等于 `Str::studly(driver_key)`；`path` 与磁盘一致；`key` 与 `driver_key` 一致
- [ ] `initialize()` 读取的键与 `config_fields[].field` 一一对应
- [ ] `send(string $phone, string $templateId, array $data = [])` 存在；失败路径统一 `return $this->setError(...)`，不抛未捕获异常
- [ ] 驱动内无 `use app\`、`use modules\`、无写业务表
- [ ] 清单与代码中无真实密钥；密钥字段 `type` 为 `password`
- [ ] 未修改 `backend/app/`、`backend/crmeb/`、核心前端、安装 SQL

**开发库冒烟（有沙箱号码时）：**

- [ ] 安装并启用插件后 `DriverRegistry::scan('sms')` 含 `your_sms`
- [ ] 后台保存配置、启用驱动后 `getEnabledDriver()` 返回你的 `driver_key`
- [ ] 通知模板填写正确 `sms_id` 后，验证码或通知短信实发成功
- [ ] 故意错误密钥 / 错误模板 ID → 返回明确失败，日志无完整手机号与密钥

## 12. 常见失败现象对照

| 现象 | 最可能原因 | 排查 |
| --- | --- | --- |
| 后台插件列表没有驱动 | 只拷目录未安装；或插件未 enable | 走 `plugin/install` + `enable`；查 `eb_module_install_log` |
| `Driver [xxx] not supported.` | 目录名与 `Str::studly(driver_key)` 不一致 | 第 4 节命令核对 |
| `未配置启用的短信驱动` | 无 `status=1` 或插件未 active | `sms_driver/enabled`；`owner_plugin_id` |
| `短信模板未就绪或不属于当前驱动` | `sms_id` 空或 `sms_driver_key` 与当前驱动不符 | 通知设置重填模板编号；切换驱动后必重配 |
| 模板变量对但网关报参数错 | 命名/位置参数与服务商不一致 | 对照第 6.3 节；腾讯云看变量顺序 |
| 配置保存了但发送仍报未配置 | `config_fields` 无 `value` 或 field 名与 `initialize` 不一致 | 查库 JSON；对照 Aliyun 字段命名 |
| 一号通账号显示只读无法改 | 正常；应走系统配置/一号通登录页 | 非 yihaotong 驱动不应依赖该路径 |
| Linux 装完类找不到 | 目录大小写错误 | 勿模仿 `yunxin/` 小写目录 |

---

**一句话：** 短信驱动只需把 `send()` 对接网关；真正容易踩坑的是 **studly 目录**、**互斥启用**、**通知模板 `sms_id` + `sms_driver_key`**，以及 **腾讯云位置参数** 与 **fail 必须走 `setError`**。
