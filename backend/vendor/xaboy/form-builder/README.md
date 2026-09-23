<p align="center">
    <a href="https://github.com/xaboy/form-builder">
        <img width="200" src="https://php.form-create.com/form-builder.png">
    </a>
</p>
<h1 align="center">FormBuilder</h1>
<p align="center">
    <a href="https://www.form-create.com/" target="_blank">官网</a>
    <span>&nbsp;|&nbsp;</span>
    <a href="https://php.form-create.com" target="_blank">帮助文档</a>
    <span>&nbsp;|&nbsp;</span>
    <a href="https://pro.form-create.com/view/" target="_blank">可视化表单设计器</a>
    <span>&nbsp;|&nbsp;</span>
    <a href="https://pro.form-create.com/mobile/" target="_blank">移动端表单设计器</a>
</p>
<p align="center">
  <a href="https://github.com/xaboy/form-builder">
    <img src="https://img.shields.io/badge/License-MIT-yellow.svg" alt="MIT" />
  </a>
  <a href="https://packagist.org/packages/xaboy/form-builder">
    <img src="https://img.shields.io/packagist/php-v/xaboy/form-builder.svg" alt="php version" />
  </a>
</p>

PHP表单生成器，基于 [@form-create/element-ui](https://github.com/xaboy/form-create) 实现快速生成现代化的Form表单。包含复选框、单选框、输入框、下拉选择框等元素以及省市区三级联动、时间选择、日期选择、颜色选择、树型、文件/图片上传等功能。

[![FcDesigner](https://static.form-create.com/file/img/banner-m2.jpg)](https://pro.form-create.com/view)

## 特点
- 使用JSON数据生成表单
- 支持扩展，生成任何Vue组件和HTML标签
- 支持组件之间联动
- 提供丰富的表单操作API
- 支持子表单和分组
- 高性能

## 环境需求

- 2.0版本 `PHP >= 5.4`
- 2.1版本 `PHP >= 7.1`

## 支持 UI

>  - IView
>  - ElementUI

## 安装

使用 [composer](http://getcomposer.org/):

```shell
$ composer require xaboy/form-builder
```

## DEMO
下载项目

```shell
git clone https://github.com/xaboy/form-builder.git
```
开启服务

```shell
cd form-builder
composer install
php -S 127.0.0.1:8112
```
查看 Demo

[127.0.0.1:8112/demo/elm.php](127.0.0.1:8112/demo/elm.php)

## 演示项目
[开源的高品质微信商城](http://github.crmeb.net/u/xaboy)

演示地址: [http://demo25.crmeb.net](http://demo25.crmeb.net) 账号：demo 密码：crmeb.com

## 使用建议
1. 建议将静态资源加载方式从 CDN 加载修改为自己本地资源或自己信任的 CDN
2. 建议根据自己的业务逻辑重写默认的表单生成页 默认表单生成页


## 预览

![https://raw.githubusercontent.com/xaboy/form-builder/2.0/images/components.png](https://raw.githubusercontent.com/xaboy/form-builder/master/images/components.png)

![demo1](https://raw.githubusercontent.com/xaboy/form-create/dev/images/demo-live3.gif)

![demo2](https://github.com/xaboy/form-create/raw/dev/images/demo-group.gif?raw=true)
