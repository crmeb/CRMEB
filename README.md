<p align="center">
    <img src="https://images.gitee.com/uploads/images/2018/1214/151026_2299df23_892944.gif" />
</p>
<h2 align="center"> CRMEB客户管理+电商营销系统v4开源版</h2> 
<p align="center">
    <a href="http://www.crmeb.com">
        <img src="https://img.shields.io/badge/OfficialWebsite-CRMEB-yellow.svg" />
    </a>
<a href="http://www.crmeb.com">
        <img src="https://img.shields.io/badge/Licence-LGPL3.0-green.svg?style=flat" />
    </a>
    <a href="http://www.crmeb.com">
        <img src="https://img.shields.io/badge/Edition-4.0-blue.svg" />
    </a>
     <a href="https://gitee.com/ZhongBangKeJi/CRMEB/repository/archive/master.zip">
        <img src="https://img.shields.io/badge/download-180m-red.svg" />
    </a>
    </p>
<p align="center">    
    <b>如果对您有帮助，您可以点右上角 "Star" 支持一下 谢谢！</b>
</p>
<p align="center">    
    <img src="https://images.gitee.com/uploads/images/2021/0125/103649_af3113ca_1491977.png" />
</p>
## 导航栏目
================================================================

   [使用手册](https://help.crmeb.net)
 | [论坛地址](http://bbs.crmeb.net)
 | [官网地址](https://www.crmeb.com)
 | [TP6开发手册](https://www.kancloud.cn/manual/thinkphp6_0/content)
 | [推荐服务器配置](https://promotion.aliyun.com/ntms/yunparter/invite.html?userCode=dligum2z)
 | [二开文档](https://gitee.com/ZhongBangKeJi/CRMEB-Min/wikis)

================================================================

## 项目介绍

CRMEB Min是CRMEB品牌全新推出的一款轻量级、高性能、前后端分离的开源电商系统，完善的后台权限管理、会员管理、订单管理、产品管理、CMS管理、多端管理、页面DIY、数据统计、系统配置、组合数据管理、日志管理、数据库管理，一键开通短信、产品采集、物流查询等接口，系统采用TP6+Mysql+Uniapp+iView+Redis+workerman+form-builder等最流行热门的技术，支持队列、PHP快速生成表单、长链接、定时任务、事件订阅、图表统计、表格导出、自动接口文档，完善的使用文档、接口文档和二开文档，十几个开发交流群，CRMEB Min是您学习交流和二开项目开发的不二选择。
   
   CRMEB赋能开发者，助力企业发展！

## 页面展示图

![演示1](https://images.gitee.com/uploads/images/2020/1230/162518_97b85596_892944.jpeg "11")
![演示2](https://images.gitee.com/uploads/images/2020/1230/162554_62736ab6_892944.jpeg "页面展示_02.jpg")
![演示3](https://images.gitee.com/uploads/images/2020/1230/162613_8aebb26f_892944.jpeg "页面展示_03.jpg")
![演示4](https://images.gitee.com/uploads/images/2020/1230/162640_6b82fcf4_892944.jpeg "开源版_01.jpg")
![演示5](https://images.gitee.com/uploads/images/2020/1230/162703_5aa76d04_892944.jpeg "开源版_02.jpg")
![演示6](https://images.gitee.com/uploads/images/2020/1230/162716_a285935c_892944.png "houtaishouye.png")
![演示7](https://images.gitee.com/uploads/images/2020/1230/162840_4f4ed1f8_892944.png "houtaiyemiansheji.png")

## QQ群   CRMEB微信开发11群 (824195682) 


##  系统演示

![前端演示](https://images.gitee.com/uploads/images/2020/1103/161837_52d08017_1491977.png "屏幕截图.png")

min演示站： http://demomin.crmeb.net/admin  账号：demo  密码：crmeb.com

## 主要特性

### 开源无加密
源码开源无加密，有详细的代码注释，有完整系统手册
### 系统框架
使用最新的 ThinkPHP6.0 + Mysql + iview + uni-app + Redis + workerman
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
### 强大的后台权限管理
后台多种角色、多重身份权限管理，权限可以控制到每一步操作，每个按钮都可以控制权限

## 安装
一、一键安装

上传你的代码，站点入口目录设置/public
在浏览器中输入你的域名或IP（例如：www.crmeb.com）,
安装程序会自动执行安装。
期间系统会提醒你输入数据库信息以完成安装，安装完成后建议删除install目录下index.php文件或将其改名。

后台访问地址：
1.域名/admin
2.域名/index.php/admin
3.域名/index.php?s=/admin
公众号和H5首页访问地址：
1.域名/
提示：正常访问是第一中模式，第一种访问不了请检测[URL重写](http://help.crmeb.net/895486)是否配置好
安装过程中请牢记您的账号密码！

重新安装

1. 清除数据库

2. 删除/public/install/install.lock 文件

二、 手动安装

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

[REDIS]
REDIS_HOSTNAME = 127.0.0.1 # redis链接地址
PORT = 6379 #端口号
REDIS_PASSWORD = 123456 #密码
SELECT = 0 #数据库
~~~
3.修改目录权限（linux系统）777
/public
/runtime
4.后台登录：
http://域名/admin
默认账号：admin 密码：crmeb.com

三、URLOS一键安装

[URLOS一键安装文档](https://gitee.com/ZhongBangKeJi/CRMEB-Min/wikis/URLOS%E4%B8%80%E9%94%AE%E5%AE%89%E8%A3%85?sort_id=3418661)

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

# 后台管理员通知
php think workerman start admin
```
参数
- status: 状态
    - start: 启动
    - stop: 关闭
    - restart: 重启
- server: 服务 (windows)
    - admin: 后台消息提醒

- --d : 后台执行

## 使用文档

[使用手册](https://help.crmeb.net/crmeb-v4/1863402)

## 开发文档

请参阅 [CRMEB v4开发文档](https://gitee.com/ZhongBangKeJi/CRMEB-Min/wikis/SUMMARY?sort_id=3303636) ｜ [TP6开发手册](https://www.kancloud.cn/manual/thinkphp6_0/content)

## 联系我们
![有问题？联系CRMEB官方客服](https://images.gitee.com/uploads/images/2020/1128/154416_441805ca_1491977.png "屏幕截图.png")


## 版权信息

本项目包含的第三方源码和二进制文件之版权信息另行标注。

版权所有Copyright © 2017-2021 by CRMEB (http://www.crmeb.com)

All rights reserved。

CRMEB® 商标和著作权所有者为西安众邦网络科技有限公司。

## 评论

欢迎大家参与评论，有问题或者bug请 issues，参与开发请 Pull Requests,谢谢大家的支持！
