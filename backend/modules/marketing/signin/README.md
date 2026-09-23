# marketing/signin/ — 签到模块

## 功能概述

签到活动用户运营模块，支持连续签到、累计签到、积分奖励等功能，提升用户活跃度和留存率。

## 核心文件

```
signin/
├── controller/
│   └── admin/
│       └── SignRewardsController.php        签到奖励配置管理
├── crontab/
│   └── SignRemindTask.php                   签到提醒定时任务
├── dao/
│   ├── SystemSignRewardDao.php              签到奖励规则 DAO
│   └── UserSignDao.php                      用户签到记录 DAO
├── listener/
│   └── SignRemindNoticeListener.php         签到提醒通知监听
├── model/
│   ├── SystemSignReward.php                 签到奖励规则模型
│   └── UserSign.php                         用户签到记录模型
├── route/
│   ├── admin.php                            后台路由
│   └── user.php                             用户端路由
├── service/
│   ├── SystemSignRewardServices.php         签到奖励规则服务
│   └── UserSignServices.php                 用户签到服务
├── Installer.php
├── event.php
└── plugin.json
```

## 插件信息

```json
{
    "key": "user_signin",
    "name": "签到活动",
    "type": "marketing"
}
```
