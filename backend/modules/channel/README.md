# channel/ — 用户渠道扩展模块

## 功能概述

用户渠道模块解决**用户从哪来、如何识别登录、如何推送消息**的问题。每个端（APP/H5/小程序/公众号）都是一个独立的渠道子模块，提供：

- 登录认证（OAuth 授权）
- 用户身份识别（openid 映射）
- 消息推送（模板消息/订阅消息）
- 端特有业务（小程序二维码等）

## 目录结构

```
channel/
├── app/               APP 端
├── h5/                H5 端
├── wechat_mini/       微信小程序端（含小程序直播内置功能）
└── wechat_mp/         微信公众号端
```

## 各渠道说明

| 渠道 | 目录 | 登录方式 | 特有功能 |
|------|------|---------|---------|
| APP | `app/` | 渠道标识（Form-type 识别），登录由 app 层处理 | — |
| H5 | `h5/` | 渠道标识（Form-type 识别），登录由 app 层处理 | — |
| 微信小程序 | `wechat_mini/` | 小程序登录（service/MiniProgram） | 小程序码、订阅消息（service/RoutineSubscribeServices）、小程序路由、小程序直播（内置功能） |
| 微信公众号 | `wechat_mp/` | 公众号 OAuth（service/WechatOAuth） | 模板消息（service/WechatTemplateServices）、菜单管理、关键词回复 |

## 公共结构

渠道子模块采用轻量结构，OAuth 授权与模板消息发送已简化为普通服务类，统一放在 `service/`：

```
{channel}/
├── controller/         控制器
├── dao/                数据访问层
├── jobs/               队列任务
├── listener/           事件监听器
├── model/              模型层
├── route/              路由
├── sdk/                第三方 SDK 封装
├── service/            服务层（登录认证、消息模板发送等）
├── validate/           验证器
├── App.php             模块注册入口
├── Installer.php       安装/卸载
├── event.php           事件注册
└── plugin.json         插件元数据
```

- 轻量渠道（H5/APP）仅保留插件元数据与渠道标识，无业务目录

## 依赖关系

- 渠道模块之间**不可互相引用**
- 跨渠道通信通过 `crmeb/` 层事件机制
- 渠道码等营销玩法依赖公众号渠道（`channel_code/` 需要 `wechat_mp`）
