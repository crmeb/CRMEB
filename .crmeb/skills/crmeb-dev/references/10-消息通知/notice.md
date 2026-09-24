# 消息通知系统开发指南（Notice）

> 本文是 `crmeb-dev` 技能的子文档，完整二开流程导航见上级 [../../SKILL.md](../../SKILL.md)。内容基于 v8.1 实际代码整理（`backend/crmeb/services/notice/` + `backend/app/services/message/` + `backend/app/adminapi/controller/setting/` + 各模块 `listener/`）。

## 一、整体架构

消息通知采用**门面直调 + 渠道适配器**设计，严格遵循三层架构依赖方向：

```
业务代码（app/services、modules/*/listener）
    │
    ▼ Notice::to($uid)->channel('is_xxx')->sendMsg($mark, $name, $data)
app/services/message/Notice.php              发送门面（链式 API：选渠道 + 投递 + 日志全在此完成）
    │
    ├──> pipeline/SendNoticeService.php     通知配置查询（带缓存）
    │
    ▼ Notice::selectTargets                 纯过滤：type 匹配 + 渠道开关 + 支持范围 + 归属启用
    │                                        产出待投递清单（channel_key + adapter_class + NoticeMessage）
    ▼ Notice::deliver                       逐渠道投递
    │
    ▼ app()->make($adapter_class)->send($message)   渠道表登记的适配器类名直接实例化
    │
    ▼ Notice::writeLog                      写 eb_notice_log（直接调 NoticeLogServices）
```

渠道不再有运行时注册中心：`eb_notice_channel.adapter_class` 字段登记适配器类名（核心安装 SQL / 插件 Installer 种子写入），`owner_plugin_id` 供发送时校验归属模块启用状态。安装/启用与卸载由插件现有 Installer 分别调用 `pipeline/NoticeSeedServices.php` 的幂等种子与归属清理；种子服务不在发送路径中。

### 1.1 分层职责

| 层 | 位置 | 职责 | 依赖 |
|----|------|------|------|
| 核心契约 | `crmeb/services/notice/` | 接口 + NoticeMessage 值对象 + 适配器基类 | 无 app 依赖 |
| 渠道适配器 | `app/services/message/channel/` 与 modules 渠道模块 | 各渠道发送实现（继承 `AbstractNoticeChannel`） | 可调 app 与 crmeb |
| 发送门面 | `app/services/message/Notice.php` | 链式 API + 选渠道 + 投递 + 日志 | app + crmeb |
| 后台管理 | `app/services/message/admin/` + `app/adminapi/controller/setting/` | 通知配置/渠道/日志 CRUD | app |
| 业务触发 | 各 `modules/*/listener/` 与 `app/listener/` | 监听业务事件 → 调 `Notice::to()` | app + crmeb |

### 1.2 渠道实现为何放在 app 层

`crmeb/` 不可调用 `app/`（架构铁律）。`SystemChannel`/`SmsChannel`/`EmailChannel` 的核心逻辑依赖 app 层服务（写站内信表、读驱动配置），若放进 crmeb 即违规。`DingTalkChannel`/`EntWechatChannel` 虽无 app 依赖，但与三者统一放 app 层更简单一致。app 层调 crmeb 的 `Bot`/`Http`/`Sms`/`Email` 驱动是合法的（app 可调 crmeb）。

## 二、核心契约（crmeb/services/notice/）

### 2.1 NoticeChannelInterface —— 渠道接口

所有渠道必须实现此接口：

```php
interface NoticeChannelInterface
{
    public static function channelKey(): string;      // 渠道标识，如 'is_sms'
    public static function channelName(): string;     // 渠道中文名，如 '短信'
    public static function getExtraFields(): array;   // 额外配置字段 [字段名 => 默认值]
    public static function getSupportScope(): array;  // 支持范围 ['support_user'=>0|1,'support_platform'=>0|1]
    public function send(NoticeMessage $message): void;  // 发送：成功直接返回，失败抛 NoticeSendException
}
```

发送失败统一抛 `crmeb\exceptions\NoticeSendException`（消息即原因），由门面 `deliver()` 捕获写入通知日志，不中断其他渠道。

### 2.2 NoticeMessage —— 待投递消息值对象

接口 `send()` 的唯一入参（readonly 不可变）。扩展字段只需加带默认值的属性并在构造点补参，所有渠道实现零改动：

```php
final class NoticeMessage
{
    public readonly string $mark;      // 消息标识
    public readonly int $uid;          // 接收用户，平台通知为 0
    public readonly string $title;     // 该通知在该渠道的模板标题
    public readonly string $content;   // 模板内容（含 {key} 占位，由渠道自行渲染）
    public readonly array $variables;  // 扁平业务变量
    public readonly array $extra;      // 渠道附加配置（extra JSON：tempid/webhook/key_list/sms_id 等）
}
```

### 2.3 AbstractNoticeChannel —— 适配器基类

提供渠道声明默认实现与占位符渲染，子类只需实现 `send()`：

```php
abstract class AbstractNoticeChannel implements NoticeChannelInterface
{
    public static function channelKey(): string { return ''; }
    public static function channelName(): string { return static::channelKey(); }
    public static function getExtraFields(): array { return []; }
    public static function getSupportScope(): array { return ['support_user' => 1, 'support_platform' => 1]; }

    // 将扁平业务变量渲染进模板文本（值转 string，非标量跳过）
    protected function renderPlaceholders(string $text, array $variables): string;
}
```

### 2.4 Notice 门面 —— 选渠道与投递（app/services/message/Notice.php）

调度全部在门面完成，无独立管线：

- **`selectTargets()` 选渠道**（纯过滤，同 mark 同 type 仅一行，由 `uk_mark_type` 唯一键保证）：接收者即类型（`to($uid>0)` 只投 type=1 行、`to(0)` 只投 type=2 行）+ 模板行启用 + 渠道全局开关 + 支持范围（用户通知看 `support_user`、平台通知看 `support_platform`）+ 归属模块启用（`ModuleStateChecker::isOwnerActive(owner_plugin_id)`，禁用/卸载插件的渠道不投递）。产出 `[channel_key => ['adapter','message'=>NoticeMessage,...]]`
- **`deliver()` 投递**：`adapter_class` 为空/类不存在/未实现接口 → 该渠道记失败日志；发送抛 `NoticeSendException` → 捕获记原因；**单渠道异常隔离**：任何一步失败只终结本渠道
- **`writeLog()` 日志**：直接调 `NoticeLogServices`（app 层无跨层问题），日志表故障降级到框架日志，不影响投递结果
- **返回值**：`[channel_key => ['success' => bool, 'error' => string]]`；空数组 = 没有渠道实际执行（mark 未登记/全被过滤），不再有"零投递返回 true"的假成功

## 三、渠道适配器（app/services/message/channel/）

### 3.1 内置渠道一览

| 渠道 key | 类名 | 中文名 | 依赖 | extra 字段 | 说明 |
|---------|------|--------|------|-----------|------|
| `is_system` | `SystemChannel` | 系统站内信 | `NoticeServices`（写 eb_notice） | 无 | type=1 发用户，type=2 发客服（调 `StoreServiceServices::getStoreServiceOrderNotification()` 取客服列表） |
| `is_sms` | `SmsChannel` | 短信 | `SmsDriverServices` + `crmeb\sms\Sms` | `sms_id` | send() 需变量含 `user_phone`/`phone`；`pushSms()` 兼供验证码直接调用 |
| `is_email` | `EmailChannel` | 邮件 | `EmailDriverServices` + `crmeb\email\Email` | 无 | send() 需变量含 `user_email`/`email`；`sendEmail()` 兼供外部直接调用 |
| `is_dingtalk` | `DingTalkChannel` | 钉钉 | `crmeb\bot\Bot` | `webhook` | 管理员通知，uid=0，异常返回 false |
| `is_ent_wechat` | `EntWechatChannel` | 企业微信群机器人 | `crmeb\utils\Http` | `url` | 管理员通知，uid=0，异常返回 false |
| `is_wechat` | `WechatTemplateListService`（modules/channel/wechat_mp） | 微信公众号模板消息 | 公众号 SDK | `tempkey`/`tempid`/`wechat_link`/`wechat_to_routine`/`key_list` | 渠道模块注册 |
| `is_routine` | `RoutineTemplateListService`（modules/channel/wechat_mini） | 小程序订阅消息 | 小程序 SDK | `tempkey`/`tempid`/`routine_link`/`key_list` | 渠道模块注册 |

### 3.2 渠道登记（渠道表 adapter_class）

渠道的唯一登记处是 `eb_notice_channel` 表：`channel_key` + `adapter_class`（适配器类名）+ `owner_plugin_id`（归属）+ 支持范围/全局开关。核心安装 SQL（`public/install/data/notice_channel_data.sql`）写入 5 个内置渠道；插件渠道由所属 Installer `getNoticeChannelDefinitions()` 返回定义（含 `adapter_class`），`NoticeSeedServices::seedChannels()` 幂等补种：

```php
// modules/channel/wechat_mp/Installer.php
protected function getNoticeChannelDefinitions(): array
{
    return [
        [
            'channel_key' => 'is_wechat',
            'channel_name' => '微信公众号',
            'adapter_class' => \modules\channel\wechat_mp\service\WechatTemplateListService::class,
            'support_user' => 1,
            'support_platform' => 0,
            'sort' => 6,
        ],
    ];
}
```

发送时按 `adapter_class` 实例化（`class_exists` 校验，配置错误记失败日志），并按 `owner_plugin_id` 校验归属模块启用状态（`ModuleStateChecker::isOwnerActive`），禁用/卸载插件的渠道不投递。管理端渠道列表与就绪判断同源读取渠道表。

### 3.3 渠道实现示例（钉钉）

```php
class DingTalkChannel extends AbstractNoticeChannel
{
    public static function channelKey(): string { return 'is_dingtalk'; }
    public static function channelName(): string { return '钉钉'; }

    // 声明本渠道额外配置字段（后台表单自动渲染、保存时自动提取）
    public static function getExtraFields(): array
    {
        return ['webhook' => ''];
    }

    // 仅平台端：群机器人只能发到钉钉群
    public static function getSupportScope(): array
    {
        return ['support_user' => 0, 'support_platform' => 1];
    }

    public function send(NoticeMessage $message): void
    {
        // webhook 来自该通知在该渠道的 extra 配置
        $webhook = (string)($message->extra['webhook'] ?? '');
        if ($webhook === '') {
            throw new NoticeSendException('钉钉 webhook 未配置');
        }
        if ($message->content === '') {
            throw new NoticeSendException('钉钉模板内容为空');
        }

        // 模板变量替换（基类 renderPlaceholders）
        $title = $this->renderPlaceholders($message->title !== '' ? $message->title : '系统通知', $message->variables);
        $content = $this->renderPlaceholders($message->content, $message->variables);

        if (!Bot::sendMarkdown('ding_talk', $webhook, $title, $content)) {
            throw new NoticeSendException('钉钉机器人返回发送失败');
        }
    }
}
```

### 3.4 渠道开发约定

1. **渠道 key 统一 `is_` 前缀**：对应渠道表 `channel_key` 与模板关联约定
2. **额外配置走 `getExtraFields()`**：声明后后台表单自动渲染、`NoticeConfigServices::saveData()` 自动提取存 `extra` JSON，发送时经 `NoticeMessage::$extra` 传入
3. **通用字段无需声明**：`template_title`/`template_content`/`status` 由框架统一处理，经 `$message->title/$message->content` 传入
4. **`send()` 成功直接返回（void），失败抛 `NoticeSendException`**：消息即原因，由门面捕获写日志；禁止吞异常返回 bool
5. **必填配置缺失/网关拒绝都抛异常**：不做静默跳过，是否投递由门面过滤层决定，渠道只如实报告结果
6. **占位符渲染用基类 `renderPlaceholders()`**：不要手写 str_replace 循环
7. **变量映射类渠道**（公众号/小程序）：用 `$message->extra['key_list']` 把扁平业务变量映射为微信模板字段，调用方永远传扁平业务变量

## 四、数据表结构

消息通知共 6 张表，前缀 `eb_`：

### 4.1 eb_notice_config —— 通知配置主表

每个 `mark`（消息标识）× 每个 `type`（1 用户/2 管理员）一行记录。

| 字段 | 类型 | 说明 |
|------|------|------|
| `id` | int unsigned PK | 主键 |
| `mark` | varchar(50) | 消息标识，如 `order_pay_success` |
| `owner_plugin_id` | varchar(160) | 核心为 `core`，插件为 canonical ID |
| `name` | varchar(50) | 通知类型名称，如 "支付成功" |
| `title` | varchar(100) | 通知场景说明 |
| `type` | tinyint | 1=用户通知 2=管理员通知 |
| `variable` | varchar(256) | 可用变量列表，格式 `{key1}描述,{key2}描述` |
| `add_time` | int unsigned | 创建时间 |

唯一键：`uk_mark_type(mark, type)`。原名 `eb_system_notification`，v8.1.0 更名。

> 全新安装的 `eb_notice_config` 精简结构已直接定义在 `backend/public/install/crmeb_table.sql`，无需额外迁移。

### 4.2 eb_notice_template —— 渠道模板配置表

每个通知 × 每个渠道一行记录，存储渠道消息模板。原表名 `eb_notification_channel`，v8.1.0 更名。

| 字段 | 类型 | 说明 |
|------|------|------|
| `id` | int unsigned PK | 主键 |
| `notification_id` | int unsigned | 关联 `eb_notice_config.id` |
| `channel_key` | varchar(50) | 渠道标识，如 `is_sms` |
| `status` | tinyint | 0=不存在 1=开启 2=关闭（实际只用 1/2，0 由删除记录代替） |
| `template_title` | varchar(200) | 模板标题 |
| `template_content` | text | 模板内容（含 `{变量}` 占位符） |
| `extra` | text | 渠道特有配置（JSON 格式字符串，如 `{"sms_id":"520268"}`/`{"webhook":"..."}`/`{"tempkey":"43216","tempid":"","key_list":{}}`） |
| `created_at` | int unsigned | 创建时间 |
| `updated_at` | int unsigned | 更新时间 |

唯一键：`notice_template(notification_id, channel_key)`，保证同一通知 × 同一渠道只有一条记录。

### 4.3 eb_notice_channel —— 渠道全局配置表

| 字段 | 类型 | 说明 |
|------|------|------|
| `id` | int unsigned PK | 主键 |
| `channel_key` | varchar(50) | 渠道标识（唯一） |
| `owner_plugin_id` | varchar(160) | 核心或 canonical 插件 ID |
| `channel_name` | varchar(100) | 渠道中文名 |
| `adapter_class` | varchar(255) | 渠道适配器类名（实现 NoticeChannelInterface，发送时按此实例化） |
| `support_user` / `support_platform` | tinyint(1) | 分别标识是否支持用户/平台通知 |
| `icon` | varchar(255) | 图标 |
| `status` | tinyint | 0=禁用 1=启用 |
| `sort` | int unsigned | 排序（升序） |
| `config` | text | 渠道全局配置（JSON 格式字符串） |
| `description` | varchar(500) | 渠道描述 |
| `created_at` | int unsigned | 创建时间 |
| `updated_at` | int unsigned | 更新时间 |

唯一键：`channel_key(channel_key)`。`status=0` 的渠道在后台通知列表不展示、发送时跳过。内置定义由 `backend/public/install/data/notice_channel_data.sql` 初始化（含 adapter_class）；可选微信渠道由各自的 Installer `getNoticeChannelDefinitions()` + `seedChannels()` 登记。

### 4.4 eb_notice_log —— 发送日志表

| 字段 | 类型 | 说明 |
|------|------|------|
| `id` | bigint unsigned PK | 主键 |
| `mark` | varchar(100) | 消息标识 |
| `uid` | int unsigned | 接收用户 ID（管理员通知为 0） |
| `channel_key` | varchar(50) | 渠道标识 |
| `channel_name` | varchar(100) | 渠道中文名 |
| `title` | varchar(255) | 标题 |
| `content` | text | 实际发送内容（变量替换后） |
| `status` | tinyint | 0=待发送 1=成功 2=失败 |
| `error_msg` | varchar(500) | 失败原因 |
| `send_time` | int unsigned | 发送时间 |
| `created_at` | int unsigned | 创建时间 |

索引：`mark`/`uid`/`channel_key`/`status`/`send_time`。状态常量定义在 `NoticeLog` model 中：`STATUS_PENDING=0`/`STATUS_SUCCESS=1`/`STATUS_FAILED=2`。

### 4.5 eb_notice —— 站内信内容表

`is_system` 渠道发送时写入，承载站内信正文。

| 字段 | 类型 | 说明 |
|------|------|------|
| `id` | bigint unsigned PK | 主键 |
| `type` | varchar(32) | `system`/`order`/`activity`/`user` |
| `title` | varchar(255) | 标题 |
| `content` | text | 内容 |
| `image` | varchar(500) | 封面图 |
| `link_type` | varchar(32) | `none`/`page`/`webview`/`order`/`activity` |
| `link_url` | varchar(500) | 链接地址 |
| `target_type` | varchar(32) | `all`/`user`/`level` |
| `target_ids` | text | 目标 ID 列表（JSON 数组） |
| `module_key` | varchar(50) | 关联模块或事件标识（即 mark） |
| `extra_data` | json | 额外数据（模板变量原文） |
| `status` | tinyint | 0=草稿 1=已发布 2=已撤回 |
| `send_at` | int | 发送时间 |
| `expire_at` | int | 过期时间 |
| `total_count` | int | 目标人数 |
| `read_count` | int | 已读人数 |
| `created_by` | int | 创建人 ID |
| `created_at` | int | 创建时间 |
| `updated_at` | int | 更新时间 |
| `is_del` | tinyint | 是否删除 |

### 4.6 eb_notice_read —— 站内信已读记录表

| 字段 | 类型 | 说明 |
|------|------|------|
| `id` | bigint unsigned PK | 主键 |
| `notice_id` | bigint unsigned | 站内信 ID |
| `uid` | int | 用户 ID |
| `is_read` | tinyint | 0=未读 1=已读 |
| `read_at` | int | 阅读时间 |
| `is_deleted` | tinyint | 用户是否删除 |
| `created_at` | int | 创建时间 |

唯一键：`uk_notice_uid(notice_id, uid)`，幂等防重复创建。

## 五、调用链详解

以订单支付成功通知为例：

```
1. 业务触发
   app/listener/order/OrderPaySuccessListener.php
     event('OrderPaySuccess', [$orderInfo])
        ↓
2. 通知监听器
   modules/product/normal/listener/OrderPaySuccessNoticeListener.php
     Notice::to($uid)->sendMsg('order_pay_success', '支付成功', [
         'order_id' => ...,
         'pay_price' => ...,
         'user_phone' => ...,
     ]);
        ↓
3. Notice 门面 (app/services/message/Notice.php)
   - 核心安装 SQL / 插件 Installer 已登记 (mark,type)，未知 mark 返回空数组
   - SendNoticeService::getNoticeInfo($mark)    带缓存查 eb_notice_config + eb_notice_template
                                                 （channels 子数组含渠道表合并的开关/支持范围/adapter_class/owner_plugin_id）
   - ownerActive()                              归属插件启用校验（module:xxx 未启用不投递）
        ↓
4. Notice::selectTargets() 选渠道
   按 $wantType = $uid > 0 ? 1 : 2 取对应配置行（uk_mark_type 唯一键保证仅一行）
     遍历指定渠道 or 配置中所有渠道：
       - 模板行缺失 / status != 1 / 渠道全局开关关闭 / 支持范围不匹配 / 归属模块禁用 → 跳过
       - 组装 NoticeMessage（mark/uid/title/content/extra）
        ↓
5. Notice::deliver() 逐渠道投递（单渠道异常隔离）
   - adapter_class 为空/类不存在/未实现接口 → recordFailure 写失败日志
   - app()->make($adapter)->send($message)    ← try-catch（NoticeSendException 取消息，其余取类名+消息）
   - writeLog() 写 eb_notice_log（createLog + logSuccess/logFailed）
   返回 [channel_key => ['success' => bool, 'error' => string]]，空数组 = 无渠道实际执行
```

**关键点**：
- `type=1`（用户通知）→ `message->uid = $uid`
- `type=2`（管理员通知）→ `message->uid = 0`；`SystemChannel` 内部经 `AdminNoticeRecipientResolve` 事件解析客服列表逐个发送，`DingTalkChannel`/`EntWechatChannel` 直接发到群机器人 webhook（不依赖客服列表）
- 缓存 key `notice_config_all`，TTL 24 小时（86400 秒），配置变更后 `SendNoticeService::clearCache()` 主动清除

## 六、业务侧使用 Notice 门面

### 6.1 基本用法

```php
use app\services\message\Notice;

// 单渠道发送
Notice::to($uid)->channel('is_wechat')
    ->sendMsg('order_pay_success', '支付成功', [
        'order_id'  => $orderId,
        'pay_price' => $payPrice,
    ]);

// 多渠道同时发送
Notice::to($uid)->channels(['is_system', 'is_sms'])
    ->sendMsg('pink_complete', '拼团成功', [
        'title'    => $title,
        'nickname' => $nickname,
    ]);

// 不指定渠道：遍历后台已开启的所有渠道
Notice::to($uid)->sendMsg('recharge_success', '充值成功', [
    'price'     => $price,
    'now_money' => $balance,
]);
```

### 6.2 模板变量格式

**统一扁平业务变量**（唯一格式，各渠道自行渲染或映射）：

```php
Notice::to($uid)->sendMsg('order_pay_success', '支付成功', [
    'order_id'   => $orderId,      // is_system 模板用 {order_id}
    'pay_price'  => $payPrice,     // is_sms 模板用 {$pay_price}
    'user_phone' => $phone,        // is_sms 渠道需要手机号
    'link'       => '/pages/...',  // 跳转链接
]);
```

微信/小程序模板字段（`thing2`/`character_string2` 等）不需要调用方关心：后台在该通知的渠道模板里配置 `key_list`（[微信模板变量名 => 业务变量名]），渠道发送时自动把扁平业务变量映射为微信字段（见 `WechatTemplateListService`/`RoutineTemplateListService`）。`$data` 中可携带 `link` 指定跳转路径，渠道会取出单独处理。

### 6.3 短信验证码场景（直接调用渠道）

验证码场景不走 Notice 门面，直接调用 `SmsChannel::pushSms()`：

```php
// app/services/user/LoginServices.php
use app\services\message\channel\SmsChannel;

public function verify(SmsChannel $services, $phone, $type, $time)
{
    $code = rand(100000, 999999);
    $data = ['code' => $code, 'time' => 5];
    $res = $services->pushSms($phone, $data, 'verify_code');
    // verify_code 的 sms_id 从 eb_notice_template.extra 读取
}
```

核心安装 SQL 为短信验证码预填一号通编号 `538393`、模板文案和字段 `code/time`，短信模板初态为开启。该模板与短信渠道的全局开关、用户端支持不得在后台关闭；未启用对应短信驱动或未配置商户凭据时，直接发送仍返回失败。

## 七、通知监听器规范（事件驱动）

### 7.1 监听器职责

通知监听器**只负责将业务事件转换为 Notice 调用**，不处理业务逻辑。场景注释与变量注释用于阅读和核对；后台展示的数据由核心安装 SQL 或所属插件 Installer `seed()` 登记（详见 [.crmeb/rules/架构规范详解.md](../../../../rules/架构规范详解.md)「消息初值与插件归属」）：

```php
// modules/product/normal/listener/OrderPaySuccessNoticeListener.php
class OrderPaySuccessNoticeListener
{
    public function handle(array $event): void
    {
        [$orderInfo] = $event;

        try {
            $uid = (int)($orderInfo['uid'] ?? 0);
            if (!$uid) return;

            // 场景：订单支付成功后，通知用户查看订单详情
            // 扁平业务数据：各渠道模板直接用业务变量名，新增渠道无需改监听器
            Notice::to($uid)->sendMsg('order_pay_success', '支付成功', [
                'order_id'   => (string)($orderInfo['order_id'] ?? ''),    // 订单号
                'pay_price'  => (string)($orderInfo['pay_price'] ?? 0),    // 支付金额（元）
                'total_num'  => (string)($orderInfo['total_num'] ?? 1),    // 商品数量
                'user_phone' => (string)(...),                             // 收货手机号（短信渠道必需）
                'link'       => '/pages/goods/order_details/index?...',    // 跳转链接
            ]);
        } catch (\Throwable $e) {
            // 通知发送失败不影响主流程
        }
    }
}
```

### 7.2 监听器编写规范

1. **扁平业务数据格式**：只含业务字段，不指定渠道，新增渠道无需改监听器
2. **值统一转 string**：避免 `str_replace` 收到 int 参数触发 TypeError（PHP 8+ 严格类型）
3. **try-catch 包裹**：通知发送失败不阻断主业务流程
4. **uid 校验**：`$uid <= 0` 时直接 return
5. **管理员通知用 `Notice::to(0)`**：如提现申请通知客服
6. **事件触发用字符串 key**：`event('OrderPaySuccess', [$orderInfo])`，禁止类常量
7. **sendMsg 注释协议**：调用语句上方写 `// 场景：xxx`，数组变量行尾写 `// 描述`；Installer 中的场景、变量、模板必须与实际调用逐项核对，不另建消息声明文件

### 7.3 现有通知监听器分布

| 业务域 | 监听器位置 | 示例 |
|--------|-----------|------|
| 用户 | `app/listener/user/*NoticeListener.php` | `UserRegisterNoticeListener`、`UserBalanceChangeNoticeListener`、`UserIntegralChangeNoticeListener`、`UserRevenueNoticeListener` |
| 订单（实物） | `modules/product/normal/listener/*NoticeListener.php` | `OrderCreateAfterNoticeListener`、`OrderPaySuccessNoticeListener`、`OrderDeliveryNoticeListener`、`OrderTakeNoticeListener`、`OrderRefundSuccessNoticeListener`、`OrderRefundRefuseNoticeListener`、`OrderRefundCreateAfterNoticeListener`、`OrderPriceChangeNoticeListener` |
| 交易模式 | `modules/trade/*/listener/*Listener.php` | `PinkListener`（拼团）、`BargainListener`（砍价）、`UserRechargeListener`（充值）、`IntegralOrderChangeNoticeListener`（积分到账） |
| 分销 | `modules/distribution/listener/*NoticeListener.php` | `UserExtractApplyNoticeListener`、`UserBrokerageChangeNoticeListener`、`UserExtractNoticeListener` |
| 渠道 | `modules/channel/wechat_mp/listener/OrderPaySuccessCustomerNoticeListener.php` | 公众号客服消息（独立于 Notice 门面，直接调 `WechatService`） |

## 八、后台管理

### 8.1 控制器与路由

| 功能 | 控制器 | 路由（在 `app/adminapi/route/setting.php`） |
|------|--------|-------------------------------------------|
| 通知配置 | `app\adminapi\controller\setting\NoticeConfigController` | `notification/index`、`notification/info`、`notification/save`、`notification/set_status/:type/:status/:id` |
| 渠道管理 | `app\adminapi\controller\setting\notification\NoticeChannelController` | `notice_channel/list`、`notice_channel/detail/:id`、`notice_channel/:id`（PUT）、`notice_channel/set_status/:id/:status`、`notice_channel/options`、`notice_channel/test/:id` |
| 发送日志 | `app\adminapi\controller\setting\notification\NoticeLogController` | `notice_log/list`、`notice_log/detail/:id`、`notice_log/statistics`、`notice_log/retry/:id`、`notice_log/clean` |

### 8.2 服务层

| 服务 | 位置 | 职责 |
|------|------|------|
| `NoticeConfigServices` | `app/services/message/admin/` | 通知配置 CRUD，列表/详情合并渠道状态、解析 variable 为变量列表 |
| `NoticeChannelServices` | `app/services/message/admin/` | 渠道全局配置 CRUD |
| `NoticeLogServices` | `app/services/message/admin/` | 发送日志 CRUD + 统计 + 重试 + 清理 |

### 8.3 后台自动感知机制

- **渠道列表自动感知**：渠道列按已安装且支持通知类型的渠道定义展示；每条消息只有安装 SQL 或插件 Installer 写入 `eb_notice_template` 的渠道才显示开关，其余显示 `--`
- **渠道详情按模板展示**：`getNotInfo()` 只返回该通知已登记的渠道模板，关闭的模板仍可编辑；短信验证码仅允许短信，历史非短信模板不会显示或发送。未登记渠道不能通过详情、保存或快捷开关创建
- **额外字段自动渲染**：`getNotInfo()` 调用 `Channel::getExtraFields()` 声明的字段，为未从 extra JSON 读到的字段补默认值
- **保存时自动提取**：`saveData()` 根据渠道类的 `getExtraFields()` 声明，从表单数据提取额外配置字段存入 `extra` JSON

### 8.4 通知配置编辑页字段约定

后台保存通知配置时遵循**约定式字段映射**（`NoticeConfigController::save()`）：

```php
// is_xxx 渠道 → xxx_text / xxx_title
$shortKey = substr($data['type'], 3);  // 去掉 'is_' 前缀
$data['title']   = ($data[$shortKey . '_title'] ?? '') ?: ($data['title'] ?? '');
$data['content'] = ($data[$shortKey . '_text'] ?? '') ?: ($data['content'] ?? '');
```

前端表单字段命名遵循此约定（如 `is_system` 渠道对应 `system_text`/`system_title`），新增渠道无需改控制器。

## 九、用户端站内信接口

### 9.1 新接口（推荐）—— `app/api/controller/v1/message/NoticeController.php`

| 方法 | 路由 | 说明 |
|------|------|------|
| GET | `api/notice` | 站内信列表（支持 type 筛选） |
| GET | `api/notice/unread-count` | 未读数量 |
| GET | `api/notice/:id` | 站内信详情（自动标记已读） |
| POST | `api/notice/read/:id` | 标记单条已读 |
| POST | `api/notice/batch-read` | 批量标记已读 |
| POST | `api/notice/read-all` | 全部标记已读 |
| DELETE | `api/notice/:id` | 删除单条 |
| POST | `api/notice/batch-delete` | 批量删除 |

### 9.2 兼容接口 —— `app/api/controller/v1/user/MessageSystemController.php`

兼容旧版 `message_system` 路由，内部委托 `MessageSystemServices` → `NoticeServices`：

| 方法 | 路由 | 说明 |
|------|------|------|
| GET | `api/user/message_system/list` | 站内信列表 |
| GET | `api/user/message_system/detail/:id` | 站内信详情 |
| GET | `api/user/message_system/edit_message` | 标记已读/删除（旧版字段编辑接口） |

### 9.3 用户端未读数获取

```php
// app/api/controller/v1/PublicController.php
'unread_msg_num' => app()->make(NoticeServices::class)->getUnreadCount($userInfo['uid']),
```

## 十、新增消息场景（开发步骤）

### 10.1 业务侧发送通知

业务代码或监听器中直接调用；**调用前必须由核心安装 SQL 或所属插件 Installer 登记**：

```php
use app\services\message\Notice;

// 场景：好友助力砍至底价成功后，引导用户按砍后价下单（写在实际调用处上方紧邻行）
Notice::to($uid)->sendMsg('my_new_event', '我的新通知', [
    'order_id' => $orderId,   // 订单号
    'amount'   => $amount,    // 金额（元）
    'remark'   => $remark,    // 备注说明
]);
```

插件在现有 Installer 覆写 `getNoticeDefinitions(): array` 返回自身 `(mark,type)`、名称、场景、实际 payload 变量与渠道模板，`seed()` 调用 `NoticeSeedServices::seed($this->getOwnerPluginId(), $this->getNoticeDefinitions())` 登记；现有 `cleanData()` 调用同服务的 `clean($this->getOwnerPluginId())`。渠道插件覆写 `getNoticeChannelDefinitions(): array`，在 `seed()` 中通过 `seedChannels()` 登记自身 `eb_notice_channel`。两个基类方法均默认返回空数组，不声明通知的插件无需覆写。用户站内信初始开启，外部渠道初始关闭（核心验证码短信例外），平台站内信在无客服收件人时初始关闭。发送未知 mark 不创建行，打开后台列表也不扫描监听器；不另建 `notice.php`。

### 10.2 实际 payload 与默认数据核对

`variable` 仍用 `{key}描述` 格式供后台插入，但 key 必须与监听器 `sendMsg()` 的实际数组一致；模板 `{key}` 和微信 `key_list` 不得引用不存在的业务字段。`to(0)` 为平台 type=2，`to($uid)` 为用户 type=1，同 mark 双侧分别声明。动态发货两个 mark 要显式登记。短信旧编号只属于一号通（`sms_driver_key=yihaotong`），微信 `tempkey` 是短编号，实际商户 `tempid` 留空并由后台配置。重复启用只补空字段，不覆盖商户修改。同键空归属记录由所属 Installer 条件认领并补齐空字段，不重置已有模板、状态或商户凭据；已有其他插件归属的同键记录仍拒绝抢占。通知种子回归脚本属于本地测试资产（仓库根 `tests` 目录为本地登记处，可缺失），登记后才能作为验收依据。

### 10.3 后台配置模板

1. 进入后台「设置 → 消息通知」
2. 找到安装器已登记的消息（按 `name` 显示）
3. 点击编辑，选择渠道 Tab，填入 `template_title`/`template_content`（含 `{变量}` 占位符）
4. 短信渠道需填 `sms_id`（存 `extra`）；微信/小程序渠道需填 `tempkey`/`tempid`/`key_list`（存 `extra`）
5. 开启渠道开关（`status=1`）
6. 保存后 `SendNoticeService::clearCache()` 自动清除缓存

### 10.4 完整示例：新增积分到账通知

```php
// modules/trade/integral/listener/IntegralOrderChangeNoticeListener.php
class IntegralOrderChangeNoticeListener
{
    public function handle($event): void
    {
        [$uid, $gainIntegral, $totalIntegral] = $event;

        try {
            // 场景：积分商城兑换订单变更（积分扣减到账）时，通知用户
            Notice::to($uid)->sendMsg('integral_accout', '积分到账通知', [
                'gain_integral' => (string)$gainIntegral,   // 获得积分
                'integral'      => (string)$totalIntegral,  // 变动后剩余积分
            ]);
        } catch (\Throwable $e) {
            // 通知失败不影响主流程
        }
    }
}
```

注册到 `modules/trade/integral/event.php`：

```php
return [
    'IntegralOrderChange' => [
        \modules\trade\integral\listener\IntegralOrderChangeNoticeListener::class,
    ],
];
```

## 十一、新增渠道（开发步骤）

以新增「飞书机器人」渠道为例：

### 11.1 创建渠道实现类

```php
// app/services/message/channel/FeishuChannel.php
namespace app\services\message\channel;

use crmeb\exceptions\NoticeSendException;
use crmeb\services\notice\AbstractNoticeChannel;
use crmeb\services\notice\NoticeMessage;
use crmeb\utils\Http;

class FeishuChannel extends AbstractNoticeChannel
{
    public static function channelKey(): string
    {
        return 'is_feishu';
    }

    public static function channelName(): string
    {
        return '飞书机器人';
    }

    // 声明额外配置字段，后台表单自动渲染、保存时自动提取
    public static function getExtraFields(): array
    {
        return ['feishu_webhook' => ''];
    }

    public function send(NoticeMessage $message): void
    {
        // webhook 来自该通知在该渠道的 extra 配置
        $webhook = (string)($message->extra['feishu_webhook'] ?? '');
        if ($webhook === '') {
            throw new NoticeSendException('飞书 webhook 未配置');
        }
        if ($message->content === '') {
            throw new NoticeSendException('飞书模板内容为空');
        }

        // 模板变量替换（基类 renderPlaceholders，值统一转 string）
        $title = $this->renderPlaceholders($message->title !== '' ? $message->title : '系统通知', $message->variables);
        $content = $this->renderPlaceholders($message->content, $message->variables);

        $response = Http::postRequest($webhook, json_encode([
            'msg_type' => 'text',
            'content'  => ['text' => "【{$title}】\n{$content}"],
        ], JSON_UNESCAPED_UNICODE));
        // 按飞书响应结构判定成功，失败抛异常由门面记日志
        $result = is_string($response) ? (json_decode($response, true)['code'] ?? -1) : -1;
        if ($result !== 0) {
            throw new NoticeSendException('飞书机器人响应失败：' . (is_string($response) ? $response : '空响应'));
        }
    }
}
```

### 11.2 登记渠道（渠道表 adapter_class）

渠道定义必须由核心安装 SQL 或插件 Installer 的 `getNoticeChannelDefinitions()` 返回（含 `adapter_class`），`seed()` 通过 `seedChannels()` 按归属幂等插入；发送时按渠道表登记的类名实例化：

```php
// 插件现有 Installer.php 内，seed() 调用 seedChannels($owner, $this->getNoticeChannelDefinitions())。
protected function getNoticeChannelDefinitions(): array
{
    return [
        ['channel_key' => 'is_feishu', 'channel_name' => '飞书机器人',
         'adapter_class' => \app\services\message\channel\FeishuChannel::class,
         'support_user' => 0, 'support_platform' => 1, 'status' => 1, 'sort' => 100],
    ];
}
```

### 11.4 验证

1. 后台「设置 → 消息通知 → 渠道管理」自动看到「飞书机器人」
2. 通知配置编辑页自动出现「飞书机器人」Tab
3. Tab 内自动出现 `feishu_webhook` 配置字段
4. 测试发送：先开启一条已登记且就绪的通知模板，渠道管理页点「测试」；测试复用已有 mark，不生成 `test` 配置

## 十二、常见 mark 速查表

| mark | 名称 | type | 触发位置 |
|------|------|------|---------|
| `order_pay_success` | 支付成功 | 1+2 | `OrderPaySuccess` 事件 |
| `order_create` | 下单成功 | 1 | `OrderCreateAfter` 事件 |
| `order_postage_success` | 快递发货 | 1 | `OrderDelivery` 事件（`OrderDeliveryNoticeListener`，发货类型=express 时） |
| `order_deliver_success` | 送货上门 | 1 | `OrderDelivery` 事件（`OrderDeliveryNoticeListener`，发货类型=deliver 时） |
| `order_take` | 确认收货 | 1+2 | `OrderTake` 事件 |
| `order_refund` | 退款成功 | 1 | `OrderRefundSuccess` 事件 |
| `send_order_refund_no_status` | 退款拒绝 | 1 | `OrderRefundRefuse` 事件 |
| `order_refund_create` | 退款申请已提交 | 1+2 | `OrderRefundCreateAfter` 事件 |
| `price_revision` | 订单改价 | 1 | `OrderPriceChange` 事件 |
| `order_pay_false` | 催付提醒 | 1 | `UnpaidOrderNotice` 事件 |
| `recharge_success` | 充值成功 | 1 | `UserRecharge` 事件 |
| `recharge_order_refund_status` | 充值退款 | 1 | `UserRechargeRefund` 事件 |
| `user_extract` | 提现成功 | 1 | `UserExtract` 事件 |
| `user_balance_change` | 余额变更 | 1 | `UserBalanceChange` 事件 |
| `user_integral_change` | 积分变更 | 1 | `UserIntegralChange` 事件 |
| `integral_accout` | 积分到账 | 1 | `IntegralOrderChange` 事件 |
| `order_brokerage` | 佣金到账 | 1 | `UserBrokerageChange` 事件 |
| `revenue_received` | 收益到账 | 1 | `UserRevenue` 事件 |
| `bind_spread_uid` | 绑定推广关系通知 | 1 | `UserRegister` 事件（仅存在推广关系时发给上级） |
| `open_pink_success` | 开团成功 | 1 | `PinkListener`（`open_pink_success` 事件） |
| `can_pink_success` | 参团成功 | 1 | ⚠️ 未接入（模板已配置，待监听器触发） |
| `order_user_groups_success` | 拼团成功 | 1 | `PinkListener`（`order_user_groups_success` 事件） |
| `send_order_pink_fial` | 拼团失败 | 1 | `PinkListener`（`send_order_pink_fial` 事件） |
| `send_order_pink_clone` | 取消拼团 | 1 | ⚠️ 未接入（模板已配置，待监听器触发） |
| `bargain_success` | 砍价成功 | 1 | `BargainListener`（`bargain_success` 事件） |
| `sign_remind` | 签到提醒 | 1 | `Sign` 事件（modules/marketing/signin，`UserSignServices::sendSignRemind()` 触发，`SignRemindNoticeListener` 发送） |
| `verify_code` | 验证码 | 1 | `LoginServices::verify()`（直接调 `SmsChannel::send`） |
| `admin_pay_success_code` | 客服-下单提醒 | 2 | ⚠️ 未接入（模板已配置，`SmsChannel::sendAdminPaySuccess()` 为旧版死代码未被调用） |
| `kefu_send_extract_application` | 客服-提现申请 | 2 | `UserExtractApply` 事件（`UserExtractApplyNoticeListener`） |
| `send_admin_confirm_take_over` | 客服-确认收货 | 2 | ⚠️ 未接入（模板已配置，`SmsChannel::sendAdminConfirmTakeOver()` 为旧版死代码未被调用） |
| `send_order_apply_refund` | 客服-申请退款 | 2 | ⚠️ 未接入（模板已配置，待监听器触发） |
| `test` | 测试消息 | – | 后台渠道测试按钮 |

> `type=1` 用户通知、`type=2` 管理员通知。同一 mark 可能同时有 type=1 和 type=2 两条配置记录：业务代码中 `to($uid)->sendMsg(...)` 生成 type=1 行，`to(0)->sendMsg(...)` 生成 type=2 行（双侧通知各调一次）。

## 十三、注意事项与最佳实践

### 13.1 架构铁律

- **`crmeb/services/notice/` 不可依赖 `app/`**：核心层只保留接口/值对象/基类，选渠道、投递、日志全部在 app 层 `Notice` 门面完成，无跨层回调
- **渠道实现统一放 `app/services/message/channel/`**：即便无 app 依赖（如钉钉/企业微信）也放此处，保持一致
- **modules 层渠道经 Installer 种子登记**：app 层不可 `use` modules 类，渠道类名由 Installer 种子写入渠道表 `adapter_class`，发送时动态实例化

### 13.2 缓存与一致性

- 通知配置缓存 key `notice_config_all`，TTL 24 小时
- 后台保存通知配置/渠道状态后，`saveData()` 与 `setStatus()` 均调用 `CacheService::clear()` 或 `SendNoticeService::clearCache()` 主动清除
- 插件 `NoticeSeedServices::seed()` 补种后清除精确通知配置缓存

### 13.3 异常隔离

- **单渠道异常不中断其他渠道**：`Notice::deliver()` try-catch 包裹 `send()`，失败只记本渠道日志
- **日志写入异常不影响投递结果**：`writeLog()` 兜底降级到框架日志
- **Notice 门面整体 try-catch**：配置查询等异常返回空数组并记 Log::error，不抛出
- **监听器内部 try-catch**：通知失败不阻断主业务流程

### 13.4 渠道未配置时的处理

- 渠道模板 `status != 1` / 渠道全局开关关闭 / 支持范围不匹配 / 归属模块禁用：`selectTargets()` 阶段跳过
- `adapter_class` 为空/类不存在/未实现接口：`deliver()` 记失败日志（"渠道适配器类不存在：xxx"）
- 必填配置为空（如 webhook/url/sms_id）或网关拒绝：渠道抛 `NoticeSendException`，失败原因写入通知日志

### 13.5 变量替换兼容性

- **值统一转 string**：`str_replace` 第二、三参数收到 int/float 会触发 TypeError（PHP 8+），基类 `renderPlaceholders()` 已统一处理
- **变量登记幂等**：`recordVariables()` 已存在的 key 不覆盖，保留管理员已填写的描述
- **微信字段映射**：调用方永远传扁平业务变量，公众号/小程序渠道经后台 `key_list` 映射为微信模板字段

### 13.6 旧字段兼容

`NoticeConfigController::save()` 仍兼容前端表单可能传的旧字段（`is_system`/`system_text`/`wechat_tempkey` 等），通过约定式字段映射转换为新结构。新增渠道无需处理旧字段，遵循 `is_xxx` → `xxx_text`/`xxx_title` 约定即可。

---

**版本**：v8.1.xu  
**最后更新**：2026-08-27  
**适用范围**：CRMEB v8 分支  
**相关规则**：[.crmeb/rules/架构规范详解.md](../../../../rules/架构规范详解.md)（消息通知架构章节）
**相关子文档**：[07-编码规范/event.md](../07-编码规范/event.md)（事件触发规范）、[08-数据库/table-structure.md](../08-数据库/table-structure.md)（表结构）
