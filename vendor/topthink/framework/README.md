![](https://box.kancloud.cn/5a0aaa69a5ff42657b5c4715f3d49221) 

ThinkPHP 6.0
===============

[![Build Status](https://travis-ci.org/top-think/framework.svg?branch=6.0)](https://travis-ci.org/top-think/framework)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/top-think/framework/badges/quality-score.png?b=6.0)](https://scrutinizer-ci.com/g/top-think/framework/?branch=6.0)
[![Code Coverage](https://scrutinizer-ci.com/g/top-think/framework/badges/coverage.png?b=6.0)](https://scrutinizer-ci.com/g/top-think/framework/?branch=6.0)
[![Total Downloads](https://poser.pugx.org/topthink/framework/downloads)](https://packagist.org/packages/topthink/framework)
[![Latest Stable Version](https://poser.pugx.org/topthink/framework/v/stable)](https://packagist.org/packages/topthink/framework)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D7.1-8892BF.svg)](http://www.php.net/)
[![License](https://poser.pugx.org/topthink/framework/license)](https://packagist.org/packages/topthink/framework)

ThinkPHP6.0底层架构采用PHP7.1改写和进一步优化。

## 主要新特性

* 采用`PHP7`强类型（严格模式）
* 支持更多的`PSR`规范
* 原生多应用支持
* 系统服务注入支持
* 更强大和易用的ORM
* 增加Filesystem
* 全新的事件系统
* 模板引擎分离出核心
* 内部功能中间件化
* SESSION机制改进
* 日志多通道支持
* 规范扩展接口
* 更强大的控制台
* 对Swoole以及协程支持改进
* 对IDE更加友好
* 统一和精简大量用法


> ThinkPHP6.0的运行环境要求PHP7.1+。

## 安装

~~~
composer create-project topthink/think tp 6.0.*-dev
~~~

启动服务

~~~
cd tp
php think run
~~~

然后就可以在浏览器中访问

~~~
http://localhost:8000
~~~

如果需要更新框架使用
~~~
composer update topthink/framework
~~~

## 文档

[完全开发手册](https://www.kancloud.cn/manual/thinkphp6_0/content)

## 命名规范

`ThinkPHP6`遵循PSR-2命名规范和PSR-4自动加载规范。

## 参与开发

请参阅 [ThinkPHP核心框架包](https://github.com/top-think/framework)。

## 版权信息

ThinkPHP遵循Apache2开源协议发布，并提供免费使用。

本项目包含的第三方源码和二进制文件之版权信息另行标注。

版权所有Copyright © 2006-2019 by ThinkPHP (http://thinkphp.cn)

All rights reserved。

ThinkPHP® 商标和著作权所有者为上海顶想信息科技有限公司。

更多细节参阅 [LICENSE.txt](LICENSE.txt)
