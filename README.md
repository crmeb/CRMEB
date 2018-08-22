<h1 align="center"> CRMEB电商管理系统</h1> 
<p align="center">
    <a href="http://www.crmeb.com">
        <img src="https://img.shields.io/badge/OfficialWebsite-CRMEB-yellow.svg" />
    </a>
    <a href="http://www.crmeb.com">
        <img src="https://img.shields.io/badge/Edition-2.3-blue.svg" />
    </a>
     <a href="https://github.com/sugar1569/CRMEB">
        <img src="https://img.shields.io/badge/download-103m-red.svg" />
    </a>
</p>

## 本项目还在不断开发完善中,如有建议或问题请[在这里提出](https://gitee.com/ZhongBangKeJi/CRMEB/issues)
## 项目介绍
   基于ThinkPhp5.0+Vue+EasyWeChat 开发的一套CRMEB商城系统
    
   CRMEB系统就是集客户关系管理+营销电商系统，能够真正帮助企业基于微信公众号、小程序，实现会员关系管理、数据分析,精准营销的电子商务管理系统。可满足企业零售、批发、分销等各种业务需求
    
   CRMEB提供了常用工具进行封装,包括日志工具、数据库在线词典、PHPExcel数据导出、layui.Table 快速生成、Vue购物车组件以及其它常用小工具等
    
   CRMEB的优势:快速积累客户 会员数据分析 智能转化客户 有效提高销售
##  演示地址
   演示站后台:[crmeb_v2.5](http://demo25.crmeb.net) 
## 功能

   1.商品: 能够对商品的状态分类管理 (出售中、待上架、库存中、已售馨、库存警戒、回收站)、添加产品、添加商品分类等功能
   
   2.会员:站内会员的管理 (发放优惠劵、发通知、发图文消息、增加余额、会员行为详情)、站内通知 、微信端用户管理 (微信用
        户的管理、分组、行为观察、添加标签) 等功能
        
   3.营销:能够管理优惠的发放和制作、用户积分的统计使用情况、秒杀产品的管理等
   
   4.财务:能够对用户的消费、充值、返佣的记录
   
   5.订单:能够完成用户的订单管理(发货、订单详情、修改订单、订单备注、订单记录、订单退款) 、售后服务 (评论的回复与删除)
   
   6.设置:能够完成管理员对网站的商品资料（添加大类、添加小类、商品添加、属性快速生成、商品审查）、商品交易（外理订单、发
        货查询）、会员管理（会员审查）、操作管理（管理员添加、管理员审查、管理员退出）、系统配置、后台通知等功能
        
   7.内容:管理文章分类 (添加分类、删除分类、修改分类) 、 管理文章
   
   8.维护:查看系统日志、文件变动效验、刷新网站缓存、在线更新系统、清除数据等功能

   9.强大的权限管理系统

## 用法

   下载: 

```
Git clone https://gitee.com/ZhongBangKeJi/CRMEB.git
```
    文档地址:https://gitee.com/ZhongBangKeJi/CRMEB/wikis【[查看](https://gitee.com/ZhongBangKeJi/CRMEB/wikis)】

## 技术亮点
~~~
    1.form-builder PHP快速生成表单 
    2.前台Vue无刷新流式加载 、购物车Vue组件等
    3.PHPExcel数据导出,导出表格更加美观,可视;
    4.EasyWeChat部署微信开发,微信接入更加快捷,简单;
    5.iview ui组件是HTML编写更加便捷
~~~
## 目录结构

目录结构如下：

~~~
www  WEB部署目录（或者子目录）
├─application           应用目录
│  ├─common             公共模块目录（可以更改）
│  ├─admin               后台目录
│  │  ├─controller      控制器目录
│  │  │  ├─system      后台目录
│  │  │  ├─agent       分销商管理目录
│  │  │  ├─article     图文管理目录
│  │  │  ├─finance     资金监控目录
│  │  │  ├─order       订单管理目录
│  │  │  ├─routine     小程序模板目录
│  │  │  ├─setting     系统设置目录
│  │  │  ├─store       产品管理目录
│  │  │  ├─ump         活动目录
│  │  │  ├─widget      图标目录
│  │  │  ├─wechat      微信目录
│  │  │  ├─store       商城目录
│  │  │  ├─user        用户目录
│  │  │  └─AuthController.php        后台基类
│  │  ├─model           模型目录
│  │  │  ├─system      后台目录
│  │  │  ├─agent       分销商管理目录
│  │  │  ├─article     图文管理目录
│  │  │  ├─finance     资金监控目录
│  │  │  ├─order       订单管理目录
│  │  │  ├─routine     小程序模板目录
│  │  │  ├─setting     系统设置目录
│  │  │  ├─store       产品管理目录
│  │  │  ├─ump         活动目录
│  │  │  ├─widget      图标目录
│  │  │  ├─wechat      微信目录
│  │  │  ├─store       商城目录
│  │  │  └─user        用户目录
│  │  ├─view            视图目录
│  │  │  ├─system      后台目录
│  │  │  ├─agent       分销商管理目录
│  │  │  ├─article     图文管理目录
│  │  │  ├─finance     资金监控目录
│  │  │  ├─order       订单管理目录
│  │  │  ├─routine     小程序模板目录
│  │  │  ├─setting     系统设置目录
│  │  │  ├─store       产品管理目录
│  │  │  ├─ump         活动目录
│  │  │  ├─wechat      微信目录
│  │  │  └─widget      图标目录
│  │  └─config.php      模块配置文件
│  ├─routine            小程序目录
│  │  ├─controller      控制器目录
│  │  ├─model           模型目录
│  │  ├─view            视图目录
│  │  └─config.php      模块配置文件
│  ├─wap                 手机端目录
│  │  ├─controller      控制器目录
│  │  │  ├─Article.php      文章控制器
│  │  │  ├─AuthApi.php      异步请求控制器
│  │  │  ├─Index.php        主页控制器
│  │  │  ├─Login.php        登录控制器
│  │  │  ├─Merchant.php     商户控制器
│  │  │  ├─My.php           个人中心控制器
│  │  │  ├─PublicApi.php    公共异步控制器
│  │  │  ├─Service.php      客服控制器
│  │  │  ├─Store.php        商品控制器
│  │  │  ├─Wechat.php       微信验证控制器
│  │  │  └─AuthController.php      wap基类
│  │  ├─model           模型目录
│  │  │  ├─wap         前台目录
│  │  │  ├─store       商城目录
│  │  │  └─user        用户目录
│  │  ├─view            视图目录
│  │  │  └─first 
│  │  │    ├─first 
│  │  │    ├─index       首页目录
│  │  │    ├─login       登录目录
│  │  │    ├─public      公共目录
│  │  │    ├─store       商城目录
│  │  │    ├─merchant    商户目录
│  │  │    ├─article     文章目录
│  │  │    ├─my          用户目录
│  │  │    └─service     客服目录
│  │  └─config.php      模块配置文件
│  │
│  ├─wechat              微信服务目录
│  │  └─controller      控制器目录
│  │
│  ├─command.php        命令行工具配置文件
│  ├─common.php         公共函数文件
│  ├─config.php         公共配置文件
│  ├─route.php          路由配置文件
│  ├─tags.php           应用行为扩展定义文件
│  └─database.php       数据库配置文件
│
├─public                公共目录
│  ├─static               全局静态文件目录
│  ├─system               后台静态文件目录
│  ├─wap                  前台静态文件目录
│  └─uploads              上传文件目录
│
├─thinkphp              框架系统目录
│  ├─lang               语言文件目录
│  ├─library            框架类库目录
│  │  ├─think           Think类库包目录
│  │  └─traits          系统Trait目录
│  │
│  ├─tpl                系统模板目录
│  ├─base.php           基础定义文件
│  ├─console.php        控制台入口文件
│  ├─convention.php     框架惯例配置文件
│  ├─helper.php         助手函数文件
│  ├─phpunit.xml        phpunit配置文件
│  └─start.php          框架入口文件
│
├─extend                扩展类库目录
│  ├─api               公共api目录
│  ├─basic             基础继承类目录
│  ├─behavior          全局行为目录
│  │  ├─system        后台行为
│  │  ├─wechat        微信行为
│  │  ├─merchant      商户行为
│  │  └─wap           wap端行为
│  ├─service           全局服务目录
│  └─traits            公共特性目录
│
├─runtime               应用的运行时目录（可写，可定制）
├─vendor                第三方类库目录（Composer依赖库）
├─index.php             入口文件
├─composer.json         composer 定义文件
├─LICENSE.txt           授权说明文件
├─README.md             README 文件
├─think                 命令行入口文件
│  ├─index.php          入口文件
│  ├─router.php         快速测试文件
│  └─.htaccess          用于apache的重写
~~~

##问题反馈
在使用中有任何问题，请使用以下联系方式联系我们

QQ群: 116279623

Gitee: https://gitee.com/ZhongBangKeJi/CRMEB/issues[提交](http://https://gitee.com/ZhongBangKeJi/CRMEB/issues)
## 特别鸣谢
感谢以下的项目,排名不分先后

ThinkPHP：http://www.thinkphp.cn

Bootstrap：http://getbootstrap.com

jQuery：http://jquery.com

iView：https://www.iviewui.com

formCreate : https://github.com/xaboy/form-create

form-builder : https://github.com/xaboy/form-builder

Vue：https://cn.vuejs.org/

font-awesome： https://fontawesome.com/?from=io

animate：https://www.animate.co.jp/

requirejs： http://requirejs.org/

umeditor：http://ueditor.baidu.com/website/umeditor.html

Php：http://www.php.net/

Mysql：https://www.mysql.com/

微信公众号、微信小程序 https://mp.weixin.qq.com

版权信息
CRMEB v2.3.* 遵循MIT开源协议发布，并提供免费使用。

本项目包含的第三方源码和二进制文件之版权信息另行标注。

版权所有Copyright © 2017-2018 by FastAdmin (https://www.crmeb.com)

All rights reserved。