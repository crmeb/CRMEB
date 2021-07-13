<p align="center">
    <a href="https://github.com/xaboy/form-builder">
        <img width="200" src="https://camo.githubusercontent.com/39b61b302b187171ad49bc0a9305f9e79787e71a/687474703a2f2f66696c652e6c6f746b6b2e636f6d2f666f726d2d6275696c6465722e706e67">
    </a>
</p>
<h1 align="center">form-builder</h1>
<p align="center">
    <img src="https://img.shields.io/badge/License-MIT-yellow.svg" alt="MIT" />
  <a href="https://github.com/xaboy">
    <img src="https://img.shields.io/badge/Author-xaboy-blue.svg" alt="xaboy" />
  </a>
  <a href="https://packagist.org/packages/xaboy/form-builder">
    <img src="https://img.shields.io/packagist/v/xaboy/form-builder.svg" alt="version" />
  </a>
  <a href="https://packagist.org/packages/xaboy/form-builder">
    <img src="https://img.shields.io/packagist/php-v/xaboy/form-builder.svg" alt="php version" />
  </a>
</p>

<p align="center">
PHP表单生成器，快速生成现代化的form表单。包含复选框、单选框、输入框、下拉选择框等元素以及省市区三级联动、时间选择、日期选择、颜色选择、树型、文件/图片上传等功能。
</p>

![demo1](https://raw.githubusercontent.com/xaboy/form-create/dev/images/demo-live3.gif)
![demo2](https://github.com/xaboy/form-create/raw/dev/images/demo-group.gif?raw=true)

## 文档

[文档](http://php.form-create.com)

## 环境需求

>  - PHP >= 5.4

## 支持 UI

>  - IView
>  - ElementUI

## 功能介绍

>  - 内置17种常用的表单组件
>  - 支持表单验证
>  - 支持生成任何 Vue 组件
>  - 支持栅格布局
>  - 支持注解
>  - 可以配合 [form-create](https://github.com/xaboy/form-create) 生成更复杂的表单

## 内置组件

>  - hidden
>  - input
>  - inputNumber
>  - checkbox
>  - radio
>  - switch
>  - select
>  - autoComplete
>  - cascader
>  - colorPicker
>  - datePicker
>  - timePicker
>  - rate
>  - slider
>  - upload
>  - tree
>  - frame

## 安装

使用 [composer](http://getcomposer.org/):

```shell
$ composer require xaboy/form-builder:~2.0
```

## DEMO
下载项目

```shell
git clone https://github.com/xaboy/form-builder.git
```
开启服务

```shell
cd form-builder
php -S 127.0.0.1:8112
```
查看 Demo

- elementUI : [127.0.0.1:8112/demo/elm.php](127.0.0.1:8112/demo/elm.php)
- iview : [127.0.0.1:8112/demo/iview.php](127.0.0.1:8112/demo/iview.php)

## 演示项目
[开源的高品质微信商城](http://github.crmeb.net/u/xaboy)

演示地址: [http://demo25.crmeb.net](http://demo25.crmeb.net) 账号：demo 密码：crmeb.com

## 使用建议
1. 建议将静态资源加载方式从 CDN 加载修改为自己本地资源或自己信任的 CDN
2. 建议根据自己的业务逻辑重写默认的表单生成页 默认表单生成页


## 组件生成效果
![https://raw.githubusercontent.com/xaboy/form-builder/2.0/images/components.png](https://raw.githubusercontent.com/xaboy/form-builder/master/images/components.png)
