# 业务模块分册 · 用户渠道（channel）

核对日期：2026-11-06（按 `backend/modules/channel/` 现役代码核对）。代码与本文冲突时以代码为准。

**先读**：[00-总览-类型路由与边界](00-总览-类型路由与边界.md) 第 4 节；领域判定：[domain-selection.md](../domain-selection.md)；代理与事件：[registration-and-contracts.md](../registration-and-contracts.md)。

强制红线：[插件开发规范](../../../../rules/插件开发规范.md)、[架构规范](../../../../rules/架构规范.md)（app 不得 use channel）。

---

## 1. 领域判定：口诀与反例

### 1.1 口诀

**用户从哪个端来、如何识别身份、该端专属 SDK/API/订阅消息** → `backend/modules/channel/{client}/`。

渠道模块封装：登录（code2Session、OAuth）、openid/unionid、小程序码、URL Scheme、端专属支付凭证、端专属通知通道。

### 1.2 应归入 channel

| 能力 | 现役 |
| --- | --- |
| 微信小程序登录与 openid | `channel/wechat_mini`（`Form-type: routine`） |
| 微信公众号 | `channel/wechat_mp` |
| APP 端 | `channel/app` |
| H5 浏览器 | `channel/h5` |

### 1.3 反例

| 误判 | 正确落位 |
| --- | --- |
| 微信支付协议、退款 | `driver/pay/*` |
| 短信验证码 | `driver/sms/*` |
| 全端共用的用户资料 CRUD | `app/services/user/*`（通过事件调渠道） |
| 分销绑粉 | `distribution` |

### 1.4 与 app 的分工

- **app**：订单、用户中台、支付编排；通过 `event('ChannelServiceProxy')`、`event('MiniProgramApiCall')` 等请求渠道能力。  
- **channel**：实现监听，调用 `sdk/` 或微信 API；**渠道之间禁止互引**。  
- **禁止** `app/` 内 `use modules\channel\*`（架构铁律）。

---

## 2. 现役模块对照

| 目录 | plugin key | type | default_install | 备注 |
| --- | --- | --- | --- | --- |
| `channel/wechat_mini` | `channel/wechat_mini` | `channel` | true | `App::detect()`、`getOpenid()` |
| `channel/wechat_mp` | `channel/wechat_mp` | `channel` | 视清单 | 公众号 OAuth |
| `channel/app` | `channel/app` | `channel` | 视清单 | APP Form-type |
| `channel/h5` | `channel/h5` | `channel` | 视清单 | H5 |

key 现役使用 **`channel/{name}` 带斜杠** 形式，与 product 的 `product_*` 不同；新渠道建议延续以免与菜单/权限历史冲突。

---

## 3. 推荐参考

| 场景 | 参考 |
| --- | --- |
| 完整 SDK + 代理 + 多事件 | `channel/wechat_mini` |
| 公众号 OAuth / 模板消息 | `channel/wechat_mp` |
| 最小端 | `channel/h5` |

---

## 4. 目录树

```text
backend/modules/channel/{client}/
├── plugin.json                 # type=channel, key 常为 channel/{client}
├── App.php                     # detect/getFormType/getOpenid；register 常为空
├── Installer.php               # 配置 seed、通知模板等（按需）
├── event.php                   # MiniProgramApiCall、ChannelServiceProxy 等
├── listener/
├── sdk/                        # 封装第三方 API
├── notify/                     # NotifyChannelInterface 实现（订阅消息等）
├── controller/user|admin/      # 登录、绑定、回调（按需）
├── route/
├── service/ dao/ model/
├── jobs/                       # 异步通知
└── validate/

frontend/admin/src/pages/plugin/channel_{client}/  # 或 key 含斜杠时的实际目录名
```

---

## 5. plugin.json

微信小程序真源片段：

```json
{
    "key": "channel/wechat_mini",
    "name": "微信小程序",
    "type": "channel",
    "path": "modules/channel/wechat_mini",
    "default_install": true,
    "diy": {
        "components": [
            { "name": "liveBroadcast", "title": "小程序直播" }
        ]
    }
}
```

支付驱动可通过 `required_credentials` 声明需要 openid；核心 `PayCredentialService` 会按端查找 channel 模块的 `App::getOpenid($uid)`（见 `wechat_mini/App.php` 注释）。

---

## 6. App.php 契约

### 6.1 端识别（wechat_mini 模式）

| 方法 | 作用 |
| --- | --- |
| `detect(): bool` | 当前请求是否属于本端（如 header `Form-type` === `routine`） |
| `getFormType(): string` | 返回约定 header 值 |
| `getName(): string` | 展示名 |
| `getOpenid(int $uid): string` | 支付/授权所需 openid（委托 app UserWechatAuthServices） |

新端渠道应实现同构静态方法，供核心按约定反射/调用（对齐现役 wechat_mini 文档注释）。

### 6.2 register / boot

- `register()` 通常为空（无 Registry）。  
- `boot()` 仅 `loadEvent(event.php)`。

不得在 `AppService` 硬编码渠道类；端路由由 channel 模块 `route/user.php` 提供。

---

## 7. event.php 与监听（方法级职责）

### 7.1 wechat_mini 真源

```php
return [
    'listen' => [
        'MiniProgramApiCall' => [ MiniProgramApiCallListener::class ],
        'MiniProgramCodeGenerate' => [ MiniProgramCodeGenerateListener::class ],
        'ChannelServiceProxy' => [ ChannelServiceProxyListener::class ],
    ],
];
```

| 事件 | Listener 典型职责 |
| --- | --- |
| `MiniProgramApiCall` | 统一入口调 wx API；参数校验；错误转业务异常 |
| `MiniProgramCodeGenerate` | 生成小程序码/海报参数 |
| `ChannelServiceProxy` | 白名单响应 app 层查询（配置、能力开关） |

### 7.2 编写要求

- 请求/响应结构稳定、文档化（app 侧依赖）。  
- SDK 异常捕获：可降级时返回空/false，并打日志；不要泄露 secret 到日志。  
- 未配置 appId/secret 时明确错误信息（后台配置页引导）。

---

## 8. sdk/ 与 notify/

- **sdk/**：access_token、code2Session、订阅消息发送等；仅 channel 模块内部使用。  
- **notify/**：实现核心通知体系的渠道接口（若模块提供订阅消息通道），注册方式对齐 wechat_mini 的 `notify/` 现役实现。

驱动层支付/短信仍在 `driver/`，不要混进 channel sdk。

---

## 9. Installer

- 渠道配置常存自有表或约定配置表（**禁止**乱写系统表 DDL）。  
- seed 通知模板、菜单（渠道配置页）。  
- 卸载清理 token 缓存键（若有 Redis key，在 `cleanData` 按前缀删）。

---

## 10. 路由与 Controller

- 用户端：登录、绑定手机号、授权回调 URL。  
- 管理端：渠道参数、版本发布、订阅模板同步（视端能力）。  
- 回调 URL 需与微信/第三方控制台一致；验签在 channel 模块完成后再调 app 用户服务。

---

## 11. 前端契约

- 管理端：plugin 页配置 appId、secret（字段走后台表单，不入库 plugin.json）。  
- uni-app：**不改**全局登录页硬编码；通过已有登录 API + `Form-type` header 区分端。  
- 新端若需独立登录页：User API + 文档说明 header 约定。

---

## 12. 逐步落地（新端渠道）

1. 确定 **Form-type**（或等价检测规则）与 **plugin key**（建议 `channel/{client}`）。  
2. 复制 **wechat_mini** 或 **h5** 骨架。  
3. 实现 `App::detect`、`getFormType`、支付所需 `getOpenid`（若适用）。  
4. 实现 `sdk/` 最小登录链路。  
5. `event.php`：至少 `ChannelServiceProxy` 白名单。  
6. User 路由：登录/刷新 session。  
7. Installer：配置项 seed + 管理菜单。  
8. 与支付驱动联调：`required_credentials` + openid 获取全链路。  
9. 订阅/模板消息（可选）：`notify/` + 业务事件触发。  
10. 禁用/卸载验收。  
11. 打包。

---

## 13. 验收清单

### 13.1 静态

```bash
cd backend && php think plugin:manifest-check && php think arch:check
```

确认无 `app → modules\channel` 反向依赖新增。

### 13.2 功能

- [ ] 对应 `Form-type` 请求 `detect()` 为 true，其他端为 false  
- [ ] 登录成功：用户绑定 openid/unionid（表字段与 app 服务一致）  
- [ ] 支付：能取到 openid（小程序/公众号场景）  
- [ ] 代理事件：app 调用 ChannelServiceProxy 返回预期  
- [ ] SDK 失败：可理解错误，不 500 整站  
- [ ] 禁用渠道：登录入口不可用或降级，**不影响**其他端登录  
- [ ] 卸载：自有配置与菜单清理（隔离库）  

### 13.3 安全

- [ ] secret 仅后台配置表/加密字段，不在代码与 git  
- [ ] 回调验签、防重放（按平台要求）  

---

## 14. 打包

打包 `modules/channel/{client}/` + 管理端 plugin 资源 → `.plugin-packages/`。

---

## 15. 禁止事项

- 禁止 channel 模块互相 `use`。  
- 禁止 channel 直接改订单/支付状态（应回调 app 服务或触发已有事件）。  
- 禁止在 app 控制器写死某端 SDK 调用（应 event 到 channel）。  
- 禁止把支付 Driver 放进 channel 目录。  

---

## 16. 排错

| 现象 | 查 |
| --- | --- |
| 登录 404 | 插件启用；user 路由；Form-type |
| openid 空 | UserWechatAuth 绑定；getOpenid 的 type 参数 |
| 支付缺 openid | PayCredentialService；驱动 required_credentials |
| 代理无响应 | ChannelServiceProxy 是否注册；方法是否在白名单 |

---

## 17. 架构关系图

```text
uni-app (Form-type: routine)
    → api 登录
        → app UserServices
            → event('MiniProgramApiCall', ...)
                → channel/wechat_mini/listener → sdk → 微信 API

PayServices 需要 openid
    → PayCredentialService
        → channel App::getOpenid(uid)
```

---

## 18. Form-type 与端对照（联调）

联调移动端 H5（`http://localhost:5173/`）时，请求头 `Form-type` 必须与渠道模块约定一致，否则 `detect()` 为 false、openid 解析走错端。

| 渠道模块 | `getFormType()` / detect 比较值 | 典型场景 |
| --- | --- | --- |
| `wechat_mini` | `routine` | 微信小程序 |
| `wechat_mp` | 见模块 `App.php` | 公众号内 H5 |
| `app` | APP 壳 | uni-app 打包 App |
| `h5` | 浏览器 | 纯 H5 |

新增端时在本表登记，并在 `PayCredentialService` 所需端的 `required_credentials` 与 `getOpenid` 实现保持一致。
