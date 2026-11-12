# 驱动分册 · 邮件（email）

核对日期：2026-11-06。先读 [00-总览](00-总览-类型路由与边界.md) 第 3 节（驱动通用机制），本篇只写邮件专属内容。

## 1. 何时用 / 何时不用

**适合做成 email 驱动：**

- 订单通知、验证码、营销邮件等：业务只投递「收件人 + 主题 + HTML 正文」，换 SMTP 或云邮件 API 即可。
- 调用方已固定：`EmailChannel`（通知管线），以及 `EmailChannel::sendEmail()`（AI 助手、MCP 邮件工具等直调）。

**不要做成 email 驱动：**

- 要改通知标题/正文的变量渲染规则、收件人解析策略 → `EmailChannel::send()` / `renderPlaceholders()` 在 `app/`，不是驱动。
- 需要邮件模板可视化编辑、发送队列独立后台 → 应做模块或走现有通知配置，不是新驱动类型。
- 同一站点要**同时**用两个邮件服务商发不同业务 → 当前 **全局互斥单驱动**，需先问用户是否接受。

## 2. 开工前必须问用户的核心写死点

| 写死点 | 为何要问 | 代码依据 |
| --- | --- | --- |
| 协议型 SMTP 还是 HTTP API | SMTP 照抄 `Smtp`；REST 照抄 `AliyunDm` | `driver/email/Smtp`、`driver/email/AliyunDm` |
| 发件地址从哪来 | SMTP 用 `smtp_username` 作 MAIL FROM；阿里云用 `account_name` | 各驱动 `plugin.json` + `Driver.php` |
| 正文格式 | 通知层按 **HTML** 投递；纯文本需驱动内自行 strip 或 multipart | `EmailChannel::dispatchEmail()` |
| `$options` 要不要用 | 通知把 `$message->variables` 整包透传；**无稳定键契约**，不要依赖 | `EmailChannel::send()` |
| 是否允许自签证书 SMTP | 现役 SMTP 驱动关闭 SSL 校验；生产环境需和用户确认安全策略 | `driver/email/Smtp/Driver.php` |

邮件没有 pay/logistics 那种「必须改核心才能出现入口」的问题；`driver_key` 动态实例化，纯插件可接入。

## 3. 运行时链路（代码定位）

```text
通知 / sendEmail()
  → EmailChannel::dispatchEmail()
  → EmailDriverServices::getEnabledDriver()
  → config_fields → field => value
  → app()->make(Email::class, [$driverKey, $config])
  → crmeb\services\email\Email::resolveClass()
  → driver\email\{Dir}\Driver::send($to, $subject, $content, $options)
```

| 层级 | 路径 |
| --- | --- |
| 门面 | `backend/crmeb/services/email/Email.php` |
| 接口 | `backend/crmeb/services/email/EmailInterface.php` |
| 基类 | `backend/crmeb/services/email/BaseEmail.php`（抽象 `send`） |
| 渠道 | `backend/app/services/message/channel/EmailChannel.php` |
| 管理 | `backend/app/services/driver/email/EmailDriverServices.php` |
| 后台 API | `backend/app/adminapi/route/driver.php` → `email_driver/*` |

`Email::invokeClass()` 构造驱动：

```php
Container::getInstance()->invokeClass($class, [$this->name, $this->config, $this->configFile]);
```

与 `BaseStorage` 一致：`__construct(string $name, array $config = [], ?string $configFile = null)`，**不要覆写构造函数**。

## 4. 目录与命名

```text
backend/driver/email/{Dir}/
├── plugin.json
└── Driver.php    # extends BaseEmail implements EmailInterface
```

四个名字对齐规则见总览 3.2。示例：`aliyun_dm` → 目录 `AliyunDm`；`smtp` → `Smtp`。

```bash
cd backend && php -r 'require "vendor/autoload.php"; echo \think\helper\Str::studly("aliyun_dm"), PHP_EOL;'
# AliyunDm
```

## 5. plugin.json 完整示例与字段表

```json
{
    "key": "your_mail",
    "name": "某某邮件",
    "description": "某某邮件推送驱动",
    "version": "1.0.0",
    "author": "作者",
    "type": "driver",
    "path": "driver/email/YourMail",
    "default_install": false,
    "require": { "crmeb": "^8.0", "php": "^8.0" },
    "driver": {
        "name": "某某邮件",
        "driver_key": "your_mail",
        "class_name": "Driver",
        "icon": "icon-email",
        "sort": 50,
        "capabilities": ["send"],
        "config_fields": [
            {
                "field": "your_mail_api_key",
                "label": "API Key",
                "type": "password",
                "required": true,
                "group": "账号"
            },
            {
                "field": "your_mail_from",
                "label": "发件地址",
                "type": "text",
                "required": true,
                "group": "账号",
                "tip": "需在服务商控制台验证过的发信地址"
            },
            {
                "field": "your_mail_from_name",
                "label": "发件人名称",
                "type": "text",
                "required": false,
                "group": "账号",
                "default": "CRMEB"
            }
        ],
        "help_doc": {
            "title": "某某邮件接入说明",
            "steps": [
                "在控制台创建发信域名/地址并完成验证",
                "创建 API 密钥",
                "填入上方配置并启用驱动",
                "在通知设置中开启邮件渠道并配置标题与 HTML 内容"
            ],
            "link": ""
        }
    }
}
```

**顶层字段：** 与总览 3.3 相同（`key`、`name`、`type`、`version`、`path` 必填）。

**`driver` 子对象（落库 `eb_driver`，`driver_type = email`）：**

| 字段 | 说明 |
| --- | --- |
| `driver_key` | 运行时类型名，唯一 |
| `capabilities` | 至少 `["send"]` |
| `config_fields` | 后台表单；保存后写入每项 `value` |
| `help_doc` | 可选，驱动配置页展示 |

邮件**没有** upload 专属的 `domain_template`、`bucket_fields`。

## 6. Driver.php 契约

### 6.1 必须实现（与接口完全一致）

```php
<?php
declare(strict_types=1);

namespace driver\email\YourMail;

use crmeb\services\email\BaseEmail;
use crmeb\services\email\EmailInterface;

class Driver extends BaseEmail implements EmailInterface
{
    protected function initialize(array $config)
    {
        // $config 键 = config_fields 的 field
    }

    public function send(string $to, string $subject, string $content, array $options = []): bool
    {
        if ($to === '') {
            return $this->setError('收件人不能为空');
        }
        if ($subject === '') {
            return $this->setError('邮件主题不能为空');
        }
        // $content 为 HTML；调 SMTP / HTTP API
        // 失败：return $this->setError('原因');
        return true;
    }
}
```

- 返回类型必须是 **`bool`**；`EmailInterface` 与 `BaseEmail` 均声明 `: bool`。
- 失败 **`return $this->setError('...')`**（返回值为 `false`），上层读 `getError()` 抛 `NoticeSendException`。
- `$options` 可忽略，除非用户明确要求支持某扩展（需在交付说明写清键名）。

### 6.2 无其他必选方法

Unlike 支付/上传，邮件没有第二个业务入口方法；不要复制短信一号通的 `open()` / `temps()` 等空方法。

## 7. 配置存在哪张表

| 存储 | 说明 |
| --- | --- |
| `eb_driver`（`driver_type = email`） | 安装插件写入；`EmailDriver` 模型 scope 过滤 |
| `eb_driver.config_fields` | 管理员在「邮件驱动」页保存的密钥、发件信息 |
| 通知模板 | 邮件渠道用 `template_title` + `template_content`（HTML）；就绪检查见 `NoticeReadiness` |

**互斥：** `EmailDriverServices::updateStatus()` 启用时 `disableAllExcept($id)`，全局仅一个启用邮件驱动。

**与短信差异：** 邮件 `syncDrivers()` 对新扫描到的内置驱动可能直接 `status=1`（`is_builtin`）；第三方插件安装默认 `status=0`，需手动启用。

## 8. 通知侧行为（驱动作者需知）

`EmailChannel::send(NoticeMessage $message)` 在调用驱动前会：

1. 解析收件人：变量 `user_email` / `email`，或 `uid>0` 时查用户表 `email`。
2. 校验 `title`、`content` 非空。
3. 对标题与正文做 `{变量}` 替换（`renderPlaceholders`）。
4. 调用 `send($to, $subject, $content, $message->variables)`。

平台通知（`type=2`）就绪检查要求业务变量含 `{user_email}` 或 `{email}`（见 `NoticeReadiness::reasons()`）。

驱动**不负责**拼主题/正文，只负责投递已渲染好的 HTML。

## 9. 从安装到冒烟（逐步）

### 9.1 静态检查

```bash
cd backend
php -l driver/email/YourMail/Driver.php
php think plugin:manifest-check
```

### 9.2 安装、配置、启用

```bash
# 安装 + 启用（管理员 token，接口基址按本地环境）
curl -X POST 'http://192.168.1.169/adminapi/plugin/install' \
  -H 'Authori-zation: Bearer <token>' -d 'plugin_key=driver:email:your_mail'
curl -X POST 'http://192.168.1.169/adminapi/plugin/enable' \
  -H 'Authori-zation: Bearer <token>' -d 'plugin_key=driver:email:your_mail'
```

后台：**设置 → 邮件驱动** → 保存配置 → 启用（关闭其他邮件驱动）。

通知：**消息管理 → 通知设置** → 对应通知 → 开启邮件渠道 → 填写邮件标题与 HTML 内容。

### 9.3 Registry

```bash
cd backend && php -r 'require "vendor/autoload.php"; (new think\App(getcwd()."/"))->initialize();
foreach (crmeb\services\driver\DriverRegistry::scan("email") as $k => $d)
  echo $k, " => ", crmeb\services\driver\DriverRegistry::getDriverClass("email", $k), PHP_EOL;'
```

### 9.4 冒烟

| 场景 | 期望 |
| --- | --- |
| `EmailChannel::sendEmail('you@example.com', '测试', '<p>hi</p>')` | 返回 `true`，收件箱收到 HTML |
| 触发一条已配邮件渠道的用户通知 | 主题/变量替换正确 |
| 错误 SMTP 密码或 API Key | `false` / 接口报错，含 `getError()` 文案 |

管理后台联调：`http://localhost:1619/admin`（账号见工作区 local-dev 规则）。

## 10. 参考实现对照表

| 目录 | driver_key | 场景 |
| --- | --- | --- |
| `Smtp/` | `smtp` | 企业邮箱 / 腾讯企业邮等 SMTP；465 SSL / 587 TLS；内置重试 |
| `AliyunDm/` | `aliyun_dm` | 阿里云邮件推送 HTTP API |
| `Qq/` | `qq` | QQ 邮箱 SMTP 变体（字段与 Smtp 类似） |

**推荐：** API 型抄 `AliyunDm/Driver.php`；SMTP 抄 `Smtp/Driver.php` 的连接与 AUTH 流程。

## 11. 打包交付

```bash
cd backend/driver/email/YourMail
zip -r "../../../../.plugin-packages/某某邮件-your_mail-1.0.0.zip" . \
  -x "*.DS_Store" -x "__MACOSX/*"
```

zip 解压后根目录应直接看到 `plugin.json`。勿包含 `.env`、真实密码。

## 12. 验收清单

**静态（必须全部通过）：**

- [ ] `php -l`、`php think plugin:manifest-check` 通过
- [ ] 目录名 = `Str::studly(driver_key)`；`path` 与磁盘一致
- [ ] `implements EmailInterface`，`send(...): bool` 签名一致
- [ ] 配置不全、收件人为空、网关失败均 `return $this->setError(...)`，不抛未捕获异常
- [ ] 密钥字段 `type: password`；代码与 JSON 无真实凭据
- [ ] 无 `use app\` / `use modules\`；未改核心目录

**开发库冒烟：**

- [ ] 插件安装启用后 `DriverRegistry::scan('email')` 含你的 key
- [ ] 启用驱动后 `getEnabledDriver()` 正确
- [ ] 至少一封 HTML 邮件送达；失败场景错误信息可读
- [ ] 日志不打印完整 API Key / SMTP 密码

## 13. 常见失败现象对照

| 现象 | 原因 | 处理 |
| --- | --- | --- |
| `Driver [xxx] not supported.` | 目录与 studly 不一致 | 改目录名或 `driver_key` |
| `未配置启用的邮件驱动` | 未启用或插件未 active | 启用驱动 + 插件 |
| `邮件主题或内容为空` | 通知模板未配标题/正文 | 通知设置补全（非驱动问题） |
| `邮件发送缺少收件邮箱` | 用户无 email 且变量未传 | 业务变量或用户资料 |
| `缺少启用的邮件驱动`（就绪） | 与上类似，模板保存前检查 | 先启用驱动 |
| SMTP `Failed to enable crypto` | 网络/端口/证书 | Smtp 驱动已 3 次重试；查防火墙与端口 |
| 配置保存仍发送失败 | `field` 与 `initialize` 键不一致 | 对照 JSON 与 PHP |
| 后台看不到新驱动 | 未安装插件 | 非「只拷文件夹」；走 plugin install |

---

**一句话：** 邮件驱动契约只有 **`send(): bool`**；互斥启用 + **`config_fields` 键名对齐** + 通知层已渲染 **HTML**，三块对了就能通。
