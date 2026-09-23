# channel/h5/ — H5 端渠道

## 功能概述

H5 浏览器端用户渠道，负责 H5 端渠道标识识别（`Form-type: h5` 头判断）与用户身份确认。登录认证由 app 层登录服务统一处理，本模块不承载独立登录实现。

## 核心文件

```
h5/
├── App.php          模块注册入口（渠道标识检测）
├── Installer.php    安装/卸载
├── README.md
└── plugin.json      插件元数据
```

## 插件信息

```json
{
    "key": "channel_h5",
    "name": "H5端",
    "type": "channel"
}
```
