# channel/wechat_mini/ — 微信小程序端渠道

## 功能概述

微信小程序端用户渠道，提供小程序登录、小程序码生成、订阅消息推送、小程序路由、**小程序直播**（直播间/主播/直播商品管理）等功能。直播功能为渠道内置能力，无独立插件。

## 核心文件

```
wechat_mini/
├── controller/
│   ├── admin/                 后台管理
│   │   ├── RoutineCIController.php       小程序 CI/CD 管理
│   │   ├── RoutineSchemeController.php   小程序路由方案管理
│   │   ├── RoutineTemplateController.php 小程序模板消息管理
│   │   ├── LiveAnchorController.php      直播主播管理
│   │   ├── LiveGoodsController.php       直播商品管理
│   │   └── LiveRoomController.php        直播间管理
│   └── AuthController.php       小程序认证接口
├── crontab/                  定时任务
│   ├── LiveProductStatusTask.php   直播商品状态同步
│   └── LiveRoomStatusTask.php      直播间状态同步
├── dao/                       数据访问层
│   ├── RoutineSchemeDao.php
│   ├── LiveAnchorDao.php
│   ├── LiveGoodsDao.php
│   ├── LiveRoomDao.php
│   └── LiveRoomGoodsDao.php
├── jobs/                      队列任务
│   ├── SyncSubscribeJob.php
│   ├── TemplateJob.php
│   └── LiveJob.php
├── listener/                  事件监听器
│   ├── MiniProgramApiCallListener.php    小程序 API 代理调用
│   └── MiniProgramQrCodeListener.php     小程序码生成
├── model/                     模型层
│   ├── RoutineScheme.php
│   ├── LiveAnchor.php
│   ├── LiveGoods.php
│   ├── LiveRoom.php
│   └── LiveRoomGoods.php
├── notify/                    消息通知
├── route/                     路由
│   ├── admin.php              后台路由（含 live/ 直播分组）
│   └── user.php               用户端路由
├── sdk/                       微信小程序 SDK
│   └── MiniProgramService.php
├── service/                   服务层
│   ├── MiniProgram.php        小程序登录（code 换 session_key/openid、解密用户信息）
│   ├── OAuthException.php     登录异常
│   ├── RoutineCIServices.php
│   ├── RoutineSchemeServices.php
│   ├── RoutineServices.php
│   ├── RoutineSubscribeServices.php  小程序订阅消息（模板ID查询与发送）
│   ├── RoutineTemplateListService.php
│   ├── LiveAnchorServices.php
│   ├── LiveGoodsServices.php
│   ├── LiveRoomServices.php
│   └── LiveRoomGoodsServices.php
├── validate/                  验证器
│   ├── LiveAnchorValidate.php
│   ├── LiveGoodsValidate.php
│   └── LiveRoomValidate.php
├── App.php                    模块注册入口
├── Installer.php              安装/卸载（含直播建表与菜单）
├── event.php                  事件注册
└── plugin.json                插件元数据
```

## 关键事件

| 事件名 | 说明 |
|--------|------|
| `MiniProgramApiCall` | 小程序 API 代理调用 |

## 插件信息

```json
{
    "key": "channel_wechat_mini",
    "name": "微信小程序端",
    "type": "channel"
}
```
