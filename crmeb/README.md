CRMEB 3.0
===============

> 运行环境要求PHP7.1+。

## 主要特性

### 开源无加密
源码开源无加密，有详细的代码注释，有完整系统手册
### TP6框架
使用最新的 ThinkPHP 6.0框架开发
### 前端采用Vue CLI框架
前端使用Vue CLI框架nodejs打包，页面加载更流畅，用户体验更好
### 标准接口
标准接口、前后端分离，二次开发更方便
### 支持队列
降低流量高峰，解除耦合，高可用
### 长连接
减少CPU及内存使用及网络堵塞，减少请求响应时长
### 无缝事件机制
行为扩展更方便，方便二次开发
### 后台快速生成表单
后台应用form-builder 无需写页面快速增删改查
### 数据表格导出
PHPExcel数据导出,导出表格更加美观可视；
### 数据统计分析
后台使用ECharts图表统计，实现用户、产品、订单、资金等统计分析
### 强大的后台权限管理
后台多种角色、多重身份权限管理，权限可以控制到每一步操作
### 一件安装
自动检查系统环境一键安装

## 安装

## 一键安装
上传你的代码，站点入口目录设置/public
在浏览器中输入你的域名或IP（例如：www.yourdomain.com）,
安装程序会自动执行安装。期间系统会提醒你输入数据库信息以完成安装，安装完成后建议删除install目录下index.php文件或将其改名。

后台访问地址：
1.域名/admin
2.域名/index.php/admin
3.域名/index.php?s=/admin
公众号和H5首页访问地址：
1.域名/
提示：正常访问是第一中模式，第一种访问不了请检测[URL重写](http://help.crmeb.net/895486)是否配置好
安装过程中请牢记您的账号密码！

## 重新安装
1. 清除数据库
2. 删除/public/install/install.lock 文件

## 手动安装
1.创建数据库，倒入数据库文件
数据库文件目录/public/install/crmeb.sql
2.修改数据库连接文件
配置文件路径/.env
~~~
APP_DEBUG = true

[APP]
DEFAULT_TIMEZONE = Asia/Shanghai

[DATABASE]
TYPE = mysql
HOSTNAME = 127.0.0.1 #数据库连接地址
DATABASE = test #数据库名称
USERNAME = username #数据库登录账号
PASSWORD = password #数据库登录密码
HOSTPORT = 3306 #数据库端口
CHARSET = utf8
DEBUG = true

[LANG]
default_lang = zh-cn
~~~
3.修改目录权限（linux系统）777
/public
/runtime
4.后台登录：
http://域名/admin
默认账号：admin 密码：crmeb.com

## 定时任务
在自动收货,库存预警等功能使用到
```sh
php think timer [ status ] [ --d ]
```
参数
- status: 状态
    - start: 启动
    - stop: 关闭
    - restart: 重启
- --d : 后台执行
## 长连接服务
在h5聊天,后台管理员消息通知等功能使用到
```sh
php think workerman [ status ] [ server ] [ --d ]
```
windows环境下需要分三步执行
```sh
# 内部通讯服务
php think workerman start channel
# h5端聊天服务
php think workerman start chat
# 后台管理员通知
php think workerman start admin
```
参数
- status: 状态
    - start: 启动
    - stop: 关闭
    - restart: 重启
- server: 服务 (windows)
    - channel: 内部通讯
    - chat: h5
    - admin: 后台

- --d : 后台执行

## 文档

[使用手册](https://help.crmeb.net)
[TP6开发手册](https://www.kancloud.cn/manual/thinkphp6_0/content)


## 参与开发

请参阅 [CRMEB](https://github.com/crmeb/CRMEB)。

## 版权信息


本项目包含的第三方源码和二进制文件之版权信息另行标注。

版权所有Copyright © 2017-2019 by CRMEB (http://www.crmeb.com)

All rights reserved。

CRMEB® 商标和著作权所有者为西安众邦网络科技有限公司。
